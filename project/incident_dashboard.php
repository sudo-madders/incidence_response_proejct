<?php
include_once("template.php");
include_once('library/database.php');

if (isset($_FILES['evidence'])) {
	$file = $_FILES['evidence'];
	$allowed_types = ["image/jpeg", "image/png", "image/gif", "application/pdf", "text/plain"];
	
	if (!in_array($file["type"], $allowed_types)) {
		echo "Error: File not allowd" . PHP_EOL;
		echo "Type: " . $file['type'];
		exit;
	}
	
	if ($file['size'] > 5000000) {
		echo "Error: File too large";
		exit;
	}
	
	$file_name = basename($file['name']);
	$dir = "uploads/";
	
	$full_name = $dir . $file_name;
	
	if (move_uploaded_file($file['tmp_name'], $full_name)) {
		$incident_ID = $mysqli->real_escape_string($_POST['incident_ID']);
		
		$query = "SELECT status_ID FROM incident_status WHERE incident_ID = '{$incident_ID}'";
		$result = $mysqli->query($query);
		$status_row = $result->fetch_assoc();
		$status_ID = $status_row['status_ID'];
		
		$query = "
			INSERT INTO incident_status (status_ID, incident_ID, user_ID)
			VALUES (?,?,?)";
		if ($stmt = $mysqli->prepare($query)) {
			$stmt->bind_param("sss", $status_ID, $incident_ID, $_SESSION['user_ID']);
			logError($stmt->error);
			
			if ($stmt->execute()) {
				$i_status_ID = $mysqli->insert_id;
				$query = "INSERT INTO evidence (i_status_ID, path) VALUES (?,?)";
				
				if ($stmt = $mysqli->prepare($query)) {
					$stmt->bind_param("ss", $i_status_ID, $full_name);
					logError($stmt->error);
					if (!$stmt->execute()) {
						echo "failed to insert file path";
					}
				}
			} else {
				echo "Failed to insert into incident_status";
			}
		}
	} else {
		echo "Upload failed";
	}
}

/*
* This if-statment will catch the form submit for comments.
* It will clean the user input and insert the correct information to
* the table incident_status. With the incident_status_ID
* it will then insert the comment into the comment-table.
*/
if (isset($_POST['comment'], $_POST['incident_ID'])) {
	$comment = $mysqli->real_escape_string($_POST['comment']);
	$incident_ID = $mysqli->real_escape_string($_POST['incident_ID']);
	
	$query = "SELECT status_ID FROM incident_status WHERE incident_ID = '{$incident_ID}'";
	$result = $mysqli->query($query);
	$status_row = $result->fetch_assoc();
	$status_ID = $status_row['status_ID'];
	
	$query = "
		INSERT INTO incident_status (status_ID, incident_ID, user_ID)
		VALUES (?,?,?)";
		
	if ($stmt = $mysqli->prepare($query)) {
		$stmt->bind_param("sss", $status_ID, $incident_ID, $_SESSION['user_ID']);
		logError($stmt->error);
		if ($stmt->execute()) {
			$i_status_ID = $mysqli->insert_id;
			$query = "INSERT INTO comment (i_status_ID, comment) VALUES (?,?)";
			
			if ($stmt = $mysqli->prepare($query)) {
				$stmt->bind_param("ss", $i_status_ID, $comment);
				logError($stmt->error);
				if (!$stmt->execute()) {
					echo "failed to insert comment";
				}
			}
		} else {
			echo "Failed to insert into incident_status";
		}
	}
}

/*
* This if-statment will catch the form submit to create a new incident.
* It will clean the user input and insert the correct information to
* the table i. With the incident_status_ID
* it will then insert the comment into the comment-table.
*/
if (isset($_POST['incident_type'], $_POST['severity'], $_POST['description'])) {
    $incident_type = $mysqli->real_escape_string($_POST['incident_type']);
    $severity = $mysqli->real_escape_string($_POST['severity']);
    $description = $mysqli->real_escape_string($_POST['description']);

    $query = "
        INSERT INTO incident (incident_type_ID, severity_ID, description) 
        VALUES (
            (SELECT incident_type_ID FROM incident_type WHERE incident_type = ?),
            (SELECT severity_ID FROM severity WHERE severity = ?),
            ?
        )";

    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("sss", $incident_type, $severity, $description);

        if ($stmt->execute()) {
            $incident_id = $mysqli->insert_id;
			
			$status_query = "SELECT status_ID FROM status WHERE status = 'Pending'";
            $status_result = $mysqli->query($status_query);
            $status_row = $status_result->fetch_assoc();
            $status_ID = $status_row['status_ID'];
			
			$status_query = "
				INSERT INTO incident_status (incident_ID, user_ID, status_ID)
				VALUES (?,?,?)
				";
			$user_ID = $_SESSION['user_ID'];
			
			if ($status_stmt = $mysqli->prepare($status_query)) {
                $status_stmt->bind_param("iii", $incident_id, $user_ID, $status_ID);
				
				 if ($status_stmt->execute()) {
                    
                } else {
                    die("Error: Could not insert incident status: " . $status_stmt->error);
                }
            }
            if (!empty($_POST['affected_assets']) && is_array($_POST['affected_assets'])) {
                foreach ($_POST['affected_assets'] as $asset_value) {
                    $asset_clean = $mysqli->real_escape_string($asset_value);
                    $asset_ids = [
                        'inventory' => 1,
                        'users' => 2,
                        'devices' => 3,
                        'network' => 4,
                        'other' => 5,
                    ];

                    if (array_key_exists($asset_clean, $asset_ids)) {
                        $asset_id = $asset_ids[$asset_clean];
                        $insert_asset = "INSERT INTO affected_assets (asset_ID, incident_ID) VALUES (?, ?)";
                        if ($asset_stmt = $mysqli->prepare($insert_asset)) {
                            $asset_stmt->bind_param("ii", $asset_id, $incident_id);
                            $asset_stmt->execute();
                        }
                    } else {
                        echo "Warning: Asset '$asset_clean' not recognized.<br>";
                    }
                }
            }
            header('Location: incident_dashboard.php');
            exit;
        } else {
            die("Error: Could not insert incident: " . $stmt->error);
        }
        $stmt->close();
    } else {
        die("Error: Could not prepare statement: " . $mysqli->error);
    }
}
?>
				<!-- Main content -->
				<div class="col bg-white">
					<div class="row mb-3">
					<?php 
					if ($_SESSION['role'] == "reporter") {
						$username = $_SESSION['username'];
						$incident_query = "SELECT * FROM reporter_view WHERE username = '{$username}' ORDER BY incident_ID DESC";
					} else {
						$incident_query = "SELECT * FROM all_incidents ORDER BY incident_ID DESC";
					}
					?>
					</div>
					<div class="row mb-3">
						<button type="button" class="btn btn-dark bg-gradient mx-auto" data-bs-toggle="offcanvas" data-bs-target="#addNewIncident" aria-controls="addNewIncident">
							Add new incident
						</button>
						
						<!-- Offcanvas, More selection -->
						<div class="offcanvas offcanvas-end offcanvas-md offcanvas_width" tabindex="-1" id="addNewIncident" aria-labelledby="addNewIncidentLabel">
							<div class="offcanvas-header">
								<h5 class="offcanvas-title" id="addNewIncidentLabel">Add new incident</h5>
								<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
							</div>
							<div class="offcanvas-body ">
								<!-- Här börjar själva panelen -->
								<form method="post" action="incident_dashboard.php">
									<div class="row">
										<div class="col-md-6">
											<label for="incident_type" class="form-label">Incident Type</label>
											<select class="form-select" name="incident_type" required>
												<option selected disabled>Choose incident type</option>
												<?php
												$query = "SELECT incident_type FROM incident_type";
												$result = $mysqli->query($query);
												if ($result && $result->num_rows > 0) {
													while ($row = $result->fetch_assoc()) {
														$incident_type = $row['incident_type'];
														echo '<option value="' . htmlspecialchars($incident_type) . '">' . htmlspecialchars($incident_type) . '</option>';
													}
												}
												?>
											</select>
											<br>
											<p>Affected assets</p>
											<input type="checkbox" name="affected_assets[]" value="inventory" id="affected_inventory">
											<label for="affected_inventory">Inventory</label>
											<br>
											<input type="checkbox" name="affected_assets[]" value="devices" id="affected_devices">
											<label for="affected_devices">Devices</label>
											<br>
											<input type="checkbox" name="affected_assets[]" value="users" id="affected_users">
											<label for="affected_users">Users</label>
											<br>
											<input type="checkbox" name="affected_assets[]" value="network" id="affected_network">
											<label for="affected_network">Network</label>
											<br>
											<input type="checkbox" name="affected_assets[]" value="other" id="affected_other">
											<label for="affected_other">Other</label>
											<br>
										</div>
										<div class="col-md-6">
											<label for="severity" class="form-label">Severity</label>
											<select class="form-select" name="severity" required>
												<option selected disabled>Choose severity</option>
												<option value="Low">Low</option>
												<option value="Medium">Medium</option>
												<option value="High">High</option>
												<option value="Critical">Critical</option>
											</select>
										</div>
									</div>
									<br>
									<label for="description" class="form-label">Description</label>
									<textarea  class="form-control" id="description" name="description" rows="3" required></textarea>
									<br>
								<input type="submit" value="Submit">
								</form>
							</div>
						</div>
					</div>
					
					<div class="row mb-3">
						<div class="col-auto">
							<div class="row">
								<div class="col-auto">
									<label for="incident_type" class="form-label">Incident Type</label>
									<select class="form-select" name="incident_type" id="incident_type">
										<option selected>All</option>
										<?php
										$query = "SELECT incident_type FROM incident_type";
										$result = $mysqli->query($query);
										if ($result && $result->num_rows > 0) {
											while ($row = $result->fetch_assoc()) {
												$incident_type = $row['incident_type'];
												echo '<option value="' . htmlspecialchars($incident_type) . '">' . htmlspecialchars($incident_type) . '</option>';
											}
										}
										?>
									</select>
								</div>
								<div class="col-auto">
									<label for="severity" class="form-label">Severity</label>
									<select class="form-select" name="severity" id="severity">
										<option selected>All</option>
										<option value="Low">Low</option>
										<option value="Medium">Medium</option>
										<option value="High">High</option>
										<option value="Critical">Critical</option>
									</select>
								</div>
							</div>
						</div>
					</div>

<?php
if ($_SESSION['role'] == "reporter") {
	$username = $_SESSION['username'];
	$incident_query = "SELECT * FROM reporter_view WHERE username = '{$username}' ORDER BY incident_ID DESC";
} else {
	$incident_query = "SELECT * FROM all_incidents ORDER BY incident_ID DESC";
}
$incident_result = $mysqli->query($incident_query);
if ($incident_result && $incident_result->num_rows > 0) {
	$incidents = [];
	while ($row = $incident_result->fetch_assoc()) {
		$query = "SELECT i_status_ID, u.username, timestamp, s.status FROM incident_status i_s 
		JOIN user u ON u.user_ID = i_s.user_ID
		JOIN status s ON i_s.status_ID = s.status_ID
		WHERE incident_ID = " . $row['incident_ID'];
		
		$result = $mysqli->query($query);
		$comments = [];
		$evidence = [];
		$events = [];
		if ($result && $result->num_rows > 0) {
			$first = True;
			while ($i_status_ID_row = $result->fetch_assoc()) {
				/*This section of the code get the comment from a certain incident_status_ID*/
				$comment_query = "SELECT comment FROM comment WHERE i_status_ID = '{$i_status_ID_row['i_status_ID']}'";
				$comment_result = $mysqli->query($comment_query);
				if ($first) {
					$status_changed = [];
					$status_changed['username'] = $i_status_ID_row['username'];
					$status_changed['timestamp'] = $i_status_ID_row['timestamp'];
					$status_changed['status'] = $i_status_ID_row['status'];
					$status_changed['type'] = "Incident created";
					$events[] = $status_changed;
					$first = False;
				}
				$evidence_query = "SELECT path FROM evidence WHERE i_status_ID = '{$i_status_ID_row['i_status_ID']}'";
				$evidence_result = $mysqli->query($evidence_query);
				
				if ($comment_result && $comment_result->num_rows > 0) {
					$comment = [];
					$comment_text = $comment_result->fetch_assoc();
					$comment['text'] = $comment_text['comment'];
					$comment['timestamp'] = $i_status_ID_row['timestamp'];
					$comment['username'] = $i_status_ID_row['username'];
					$comments[] = $comment;
					$comment['type'] = "Comment -> {$comment['text']}";
					$events[] = $comment;
				} elseif ($evidence_result && $evidence_result->num_rows > 0) {
					$evidence_data = [];
					$evidence_result = $evidence_result->fetch_assoc();
					$path_array = explode("/", $evidence_result['path']);
					$evidence_data['path'] = end($path_array);
					$evidence_data['timestamp'] = $i_status_ID_row['timestamp'];
					$evidence_data['username'] = $i_status_ID_row['username'];
					$evidence[] = $evidence_data;
					$evidence_data['type'] = "Evidence -> {$evidence_data['path']}";
					$events[] = $evidence_data;
				} else {
					$status_changed = [];
					$status_changed['username'] = $i_status_ID_row['username'];
					$status_changed['timestamp'] = $i_status_ID_row['timestamp'];
					$status_changed['status'] = $i_status_ID_row['status'];
					$status_changed['type'] = "Status change -> {$i_status_ID_row['status']}";
					$events[] = $status_changed;
				}
			}
		}
		$row['events'] = $events;
		$row['evidence'] = $evidence;
		$row['comments'] = $comments;
		$incidents[] = $row;
	}
}
?>
<?php if (!empty($incidents)): ?>
    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
    <table class="table table-striped table-bordered align-middle" id="incident_table">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Description</th>
                <th>Type</th>
                <th>Severity</th>
				<th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($incidents as $incident): ?>
                <tr>
                    <td><?= htmlspecialchars($incident['incident_ID']) ?></td>
                    <td><?= htmlspecialchars($incident['description']) ?></td>
                    <td><?= htmlspecialchars($incident['incident_type']) ?></td>
                    <td><?= htmlspecialchars($incident['severity']) ?></td>
					<td>
						<select class="form-select" name="status" id="select_<?= htmlspecialchars($incident['incident_ID']) ?>">
							<?php
								$pending = False;
								$in_progress = False;
								$resolved = False;
								if ($incident['status'] == "Pending") {
									$pending = True;
								} elseif ($incident['status'] == "Resolved") {
									$resolved = True;
								} else {
									$in_progress = True;
								}
							?>
							<option <?= $pending ? 'selected' : ''?> value="Pending">Pending</option>
							<option <?= $in_progress ? 'selected' : ''?> value="In progress">In progress</option>
							<option <?= $resolved ? 'selected' : ''?> value="Resolved">Resolved</option>
						</select>
					</td>
                    <td>
						<button type="button" class="btn btn-secondary mx-auto" data-bs-toggle="offcanvas" data-bs-target="#incident_<?= htmlspecialchars($incident['incident_ID']) ?>" aria-controls="incident_<?= htmlspecialchars($incident['incident_ID']) ?>">
							Edit
						</button>
						
						<!-- Offcanvas, More selection -->
						<div class="offcanvas offcanvas-end offcanvas-md offcanvas_width" tabindex="-1" id="incident_<?= htmlspecialchars($incident['incident_ID']) ?>" aria-labelledby="addNewIncidentLabel">
							<div class="offcanvas-header">
								<h5 class="offcanvas-title" id="addNewIncidentLabel">Incident <?= htmlspecialchars($incident['incident_ID']) ?></h5>
								<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
							</div>
							<div class="offcanvas-body ">
								<!-- Här börjar själva panelen -->
								<div class="row">
									<div class="col">
										<form method="post" action="incident_dashboard.php">
											<div class="row mb-3">
												<div class="col">
													<label for="comment_<?= $incident['incident_ID']?>" class="form-label">Comment</label>
													<textarea class="form-control" name="comment" id="comment_<?= $incident['incident_ID']?>" rows="3"></textarea>
												</div>
											</div>
											<input type="hidden" name="incident_ID" value="<?= $incident['incident_ID']?>">
											<button type="submit" class="btn btn-secondary">Submit</button>
										</form>
									</div>
									<div class="col">
										<form method="post" action="incident_dashboard.php" enctype="multipart/form-data">
											<div class="row mb-3">
												<div class="col">
													<label for="evidence" class="form-label">Upload evidence</label>
													<input class="form-control" type="file" name="evidence" id="evidence required">
												</div>
											</div>
											<input type="hidden" name="incident_ID" value="<?= $incident['incident_ID']?>">
											<button type="submit" class="btn btn-secondary">Upload</button>
										</form>
									</div>
								</div>
								<div class="row mt-3">
									<div class="col">
										<h5>Comments:</h5>
										<?php if (isset($incident['comments']) && !empty($incident['comments'])): ?>
											<table class="table table-striped table-bordered">
												<thead class="table-light">
													<tr>
														<th>Timestamp</th>
														<th>User</th>
														<th>Comment</th>
													</tr>
												</thead>
												<tbody>
													<?php foreach ($incident['comments'] as $comment): ?>
														<tr>
															<td><?= htmlspecialchars($comment['timestamp'] ?? '') ?></td>
														
															<td><?= htmlspecialchars($comment['username'] ?? '') ?></td>
														
															<td><?= htmlspecialchars($comment['text'] ?? '') ?></td>
														</tr>
													<?php endforeach; ?>
												</tbody>
											</table>
										<?php else: ?>
											<p>No comments yet.</p>
										<?php endif; ?>
									</div>
									<div class="col">
										<h5>Evidence:</h5>
										<?php if (isset($incident['evidence']) && !empty($incident['evidence'])): ?>
											<table class="table table-striped table-bordered">
												<thead class="table-light">
													<tr>
														<th>Timestamp</th>
														<th>User</th>
														<th>Path</th>
													</tr>
												</thead>
												<tbody>
													<?php foreach ($incident['evidence'] as $evidence): ?>
														<tr>
															<td><?= htmlspecialchars($evidence['timestamp'] ?? '') ?></td>
														
															<td><?= htmlspecialchars($evidence['username'] ?? '') ?></td>
														
															<td><a href="uploads/<?= htmlspecialchars($evidence['path'] ?? '') ?>"><?= htmlspecialchars($evidence['path'] ?? '') ?></td>
														</tr>
													<?php endforeach; ?>
												</tbody>
											</table>
										<?php else: ?>
											<p>No evidence yet.</p>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
						<button type="button" class="btn btn-secondary mx-auto" data-bs-toggle="offcanvas" data-bs-target="#incident_event_<?= htmlspecialchars($incident['incident_ID']) ?>" aria-controls="incident_<?= htmlspecialchars($incident['incident_ID']) ?>">
							Show events
						</button>
						
						<!-- Offcanvas, More selection -->
						<div class="offcanvas offcanvas-end offcanvas-md offcanvas_width" tabindex="-1" id="incident_event_<?= htmlspecialchars($incident['incident_ID']) ?>" aria-labelledby="addNewIncidentLabel">
							<div class="offcanvas-header">
								<h5 class="offcanvas-title" id="addNewIncidentLabel">Incident <?= htmlspecialchars($incident['incident_ID']) ?></h5>
								<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
							</div>
							<div class="offcanvas-body ">
								<?php if (isset($incident['events']) && !empty($incident['events'])): ?>
									<table class="table table-striped table-bordered">
										<thead class="table-light">
											<tr>
												<th>Timestamp</th>
												<th>User</th>
												<th>Type</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($incident['events'] as $events): ?>
												<tr>
													<td><?= htmlspecialchars($events['timestamp'] ?? '') ?></td>
												
													<td><?= htmlspecialchars($events['username'] ?? '') ?></td>
												
													<td><?= htmlspecialchars($events['type']  ?? '') ?></td>
												</tr>
											<?php endforeach; ?>
										</tbody>¨
									</table>
								<?php else: ?>
									<p>No events yet.</p>
								<?php endif; ?>
							</div>
						</div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php else: ?>
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filter'])): ?>
        <p class="text-muted">No incidents matched your filter.</p>
    <?php else: ?>
        <p class="text-muted">No incidents available.</p>
    <?php endif; ?>
<?php endif; ?>

<script>
$(document).ready(function() {
    var $incidentFilter = $("#incident_type");
    var $severityFilter = $("#severity");
    var $myTable = $("#incident_table");
    var $tableBody = $myTable.find("tbody");

    function filterTable() {
        var formData = new FormData();
        formData.append("incident_type", $incidentFilter.val());
        formData.append("severity", $severityFilter.val());
        formData.append("filter", "yes");

        $.ajax({
            url: "/project/library/filter_incidents.php",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        })
        .done(function(data) {
            updateTable(data);
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            console.error(`There was an error: ${textStatus}`, errorThrown);
        });
    }

    function updateTable(data) {
        // Clear the existing table rows
        $tableBody.empty();

        // Remake the table with the new data
        $.each(data, function(index, row) {
            var $newRow = $('<tr>');
            
            $newRow.append($('<td>').text(row.incident_ID));
            $newRow.append($('<td>').text(row.description));
            $newRow.append($('<td>').text(row.incident_type));
            $newRow.append($('<td>').text(row.severity));
            $newRow.append($('<td>').text(row.status));
            $newRow.append($('<td>').html(row.edit));
            
            $tableBody.append($newRow);
        });
    }
    // Add event listeners to both select elements
    $incidentFilter.on('change', filterTable);
    $severityFilter.on('change', filterTable);
	
	
	$('select[name="status"]').on('change', function() {
        const incidentId = $(this).attr('id').replace('select_', '');
        const newStatus = $(this).val();
        
        // Send data to endpoint
        $.post('library/update_status.php', {
            incident_id: incidentId,
            new_status: newStatus
        })
        .done(function(response) {
            console.log('Success:', response);
            alert('Status updated successfully!');
        })
        .fail(function(error) {
            console.error('Error:', error.responseText);
            alert('Failed to update status.');
        });
    });
});
</script>
<?php
echo $footer;
?>
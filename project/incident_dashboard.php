<?php
include_once("template.php");
include_once('library/database.php');

if (isset($_FILES['files'])) {
    $allowed_types = ["image/jpeg", "image/png", "image/gif", "application/pdf", "text/plain"];
    $upload_dir = "uploads/";
    $incident_ID = $mysqli->real_escape_string($_POST['incident_ID']);

    $total_files = count($_FILES['files']['name']);

    for ($i = 0; $i < $total_files; $i++) {
        $file_name = basename($_FILES['files']['name'][$i]);
        $file_tmp = $_FILES['files']['tmp_name'][$i];
        $file_type = $_FILES['files']['type'][$i];
        $file_size = $_FILES['files']['size'][$i];
        $file_error = $_FILES['files']['error'][$i];
        $full_path = $upload_dir . $file_name;

        if ($file_error === UPLOAD_ERR_OK) {
            if (!in_array($file_type, $allowed_types)) {
                logError("Error: File '$file_name' is not allowed (type: $file_type).");
                continue; // Skip to the next file
            }

            if ($file_size > 5000000) {
                echo "Error: File '$file_name' is too large." . PHP_EOL;
                continue; // Skip to the next file
            }

            if (move_uploaded_file($file_tmp, $full_path)) {
                // File uploaded successfully, now record it in the database

                // First, get the current status_ID for the incident
                $query_select_status = "SELECT status_ID FROM incident_status WHERE incident_ID = '{$incident_ID}' ORDER BY status_ID DESC LIMIT 1";
                $result = $mysqli->query($query_select_status);
                $status_row = $result->fetch_assoc();
                $status_ID = $status_row ? $status_row['status_ID'] : null;

                // Insert a new entry into incident_status
                $query_insert_status = "
                    INSERT INTO incident_status (status_ID, incident_ID, user_ID)
					VALUES (?,?,?)";
                if ($stmt_insert_status = $mysqli->prepare($query_insert_status)) {
                    $stmt_insert_status->bind_param("sss", $status_ID, $incident_ID, $_SESSION['user_ID']);
                    if ($stmt_insert_status->execute()) {
                        $i_status_ID = $mysqli->insert_id;
                        // Insert the evidence path
                        $query_insert_evidence = "INSERT INTO evidence (i_status_ID, path) VALUES (?,?)";
                        if ($stmt_insert_evidence = $mysqli->prepare($query_insert_evidence)) {
                            $stmt_insert_evidence->bind_param("ss", $i_status_ID, $full_path);
                            if (!$stmt_insert_evidence->execute()) {
                                echo "Error: Failed to insert file path for '$file_name'." . PHP_EOL;
                                logError($stmt_insert_evidence->error);
                            }
                            $stmt_insert_evidence->close();
                        } else {
                            logError($mysqli->error);
                        }
                    } else {
                        logError($stmt_insert_status->error);
                    }
                    $stmt_insert_status->close();
                } else {
                    logError($mysqli->error);
                }
            }
        }
    }
}

/*
* This if-statment will catch the form submit for comments.
* It will clean the user input and insert the correct information to
* the table incident_status. With the incident_status_ID
* it will then insert the comment into the comment-table.
*/

if (isset($_POST['comment'], $_POST['incident_ID'])) {
	$comment = trim($_POST['comment']);  
	$incident_ID = trim($_POST['incident_ID']);

	// Initialize an errors array to collect validation errors
	$errors = [];

	// Validation for comment field
	if (empty($comment)) {
		$errors[] = "Comment cannot be empty.";
	} elseif (strlen($comment) > 1000) {  
		$errors[] = "Comment cannot exceed 1000 characters.";
	}

	// Validate incident_ID (should be numeric)
	if (!is_numeric($incident_ID)) {
		$errors[] = "Invalid incident ID.";
	}

	// If there are validation errors, stop execution
	if (count($errors) > 0) {
		exit; // Optionally, you could send a response with errors
	}

	// If validation passed, proceed to the database operations
	$comment = $mysqli->real_escape_string($comment);
	$incident_ID = $mysqli->real_escape_string($incident_ID);

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
    
    $incident_type = trim($_POST['incident_type']);
    $severity = trim($_POST['severity']);
    $description = trim($_POST['description']);
    $created = $mysqli->real_escape_string($_POST['created']);

    
    $errors = [];

    if (empty($incident_type)) {
        $errors[] = "Incident type cannot be empty.";
    }
    if (empty($severity)) {
        $errors[] = "Severity cannot be empty.";
    }
    if (empty($description)) {
        $errors[] = "Description cannot be empty.";
    }

    
    if (count($errors) > 0) {
        exit; 
    }

    
    $incident_type = $mysqli->real_escape_string($incident_type);
    $severity = $mysqli->real_escape_string($severity);
    $description = $mysqli->real_escape_string($description);
    
    $query = "
        INSERT INTO incident (incident_type_ID, severity_ID, description, created) 
        VALUES (
            (SELECT incident_type_ID FROM incident_type WHERE incident_type = ?),
            (SELECT severity_ID FROM severity WHERE severity = ?),
            ?,
            ?
        )";

    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("ssss", $incident_type, $severity, $description, $created);

        if ($stmt->execute()) {
            $incident_id = $mysqli->insert_id;
			
			$status_query = "SELECT status_ID FROM status WHERE status = 'Pending'";
            $status_result = $mysqli->query($status_query);
            $status_row = $status_result->fetch_assoc();
            $status_ID = $status_row['status_ID'];
			
			$status_query = "
				INSERT INTO incident_status (incident_ID, user_ID, status_ID)
				VALUES (?,?,?);
			";
			$user_ID = $_SESSION['user_ID'];
			
			if ($status_stmt = $mysqli->prepare($status_query)) {
                $status_stmt->bind_param("iii", $incident_id, $user_ID, $status_ID);
				
				 if (!$status_stmt->execute()) {
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
				<div class="col mt-1">
					<div class="row mb-3">
						<div class="col">
							<?php 
								$query = "SELECT s.severity as severity, s.severity_ID, COUNT(*) as 'count' FROM incident i
								JOIN severity s ON s.severity_ID = i.severity_ID
								GROUP BY severity
								ORDER BY s.severity_ID DESC";
								$result = $mysqli->query($query);
								
								
							?>
							
							<?php while ($row = $result->fetch_assoc()): ?>
							<p class="mb-0 <?= ($row['severity'] == "Critical") ? 'text-danger' : '' ?>">
								<?= htmlspecialchars($row['severity']) ?>
							
								:<?= htmlspecialchars($row['count']) ?>
							</p>
							<?php endwhile; ?>
						</div>
					
					
						<div class="col">
							<?php 
								$query = "SELECT s.status, count(*) as 'count'
								FROM incident_status i_s
								JOIN status s ON i_s.status_ID = s.status_ID
								JOIN incident i on i_s.incident_ID = i.incident_ID
								GROUP BY s.status";
								$result = $mysqli->query($query);
							?>
							
							
							<?php while ($row = $result->fetch_assoc()): ?>
							<p class="mb-0 <?= ($row['status'] == "Resolved") ? 'text-success' : '' ?><?= ($row['status'] == "In progress") ? 'text-warning' : '' ?>">
								<?= htmlspecialchars($row['status']) ?>
							
								:<?= htmlspecialchars($row['count']) ?>
							</p>
							<?php endwhile; ?>
						</div>
						<div class="col">
							<?php 
								$query = "SELECT COUNT(*) as 'count'
								FROM (
									SELECT
										incident_ID,
										MIN(timestamp) AS latest_timestamp
									FROM incident_status
									GROUP BY incident_ID
								) AS subquery_count;";
								$result = $mysqli->query($query);
								$row = $result->fetch_assoc()
							?>
							<h4>
								Incidents added last 3 days: <?= $row['count'] ?>
							</h4>
						</div>
					</div>
					<div class="row mb-3">
						<button type="button" class="btn fs-4 btn-accent mx-auto" data-bs-toggle="offcanvas" data-bs-target="#addNewIncident" aria-controls="addNewIncident">
						Add new incident
						</button>
						<!-- Offcanvas, More selection -->
						<div class="offcanvas offcanvas-end offcanvas-md offcanvas_width" tabindex="-1" id="addNewIncident" aria-labelledby="addNewIncidentLabel">
							<div class="offcanvas-header">
								<h3 class="offcanvas-title text-primary-mono fw-bold" id="addNewIncidentLabel">Add new incident</h3>
								<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
							</div>
							<div class="offcanvas-body ">
								<!-- Här börjar själva panelen -->
								<form id="add_incident" method="post" action="incident_dashboard.php">
									<div class="row">
										<div class="col-md-6">
											<label for="incident_type" class="form-label">Incident Type</label>
											<select class="form-select" name="incident_type" required>
												<option value="" selected disabled>Choose incident type</option>
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
												<option value="" selected disabled>Choose severity</option>
												<option value="Low">Low</option>
												<option value="Medium">Medium</option>
												<option value="High">High</option>
												<option value="Critical">Critical</option>
											</select>
											<label for="created">When did it happend:</label>
											<input class="mt-3 form-onrtrol" type="date" id="created" name="created" required>
										</div>
									</div>
									<br>
									<label for="description" class="form-label">Description</label>
									<textarea  class="form-control" id="description" name="description" rows="3" required></textarea>
									<br>
								<input type="submit" class="btn btn-accent" value="Submit">
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
	$incident_query = "SELECT * FROM all_incidents";
}
$incident_result = $mysqli->query($incident_query);
if ($incident_result && $incident_result->num_rows > 0) {
	$incidents = [];
	while ($row = $incident_result->fetch_assoc()) {
		/*Get all the assets associated with an incident*/
		$assets = [];
		$asset_query = "SELECT a.asset
						FROM all_incidents ai
						JOIN affected_assets aa ON ai.incident_ID = aa.incident_ID
						JOIN asset a ON aa.asset_ID = a.asset_ID
						WHERE ai.incident_ID = " . $row['incident_ID'] . "";
		
		$asset_result = $mysqli->query($asset_query);
		if ($asset_result && $asset_result->num_rows > 0) {
			while ($asset_row = $asset_result->fetch_assoc()) {
				$assets[] = $asset_row['asset'];
			}
		}
		
		$query = "SELECT i_status_ID, u.username, timestamp, s.status FROM incident_status i_s 
		JOIN user u ON u.user_ID = i_s.user_ID
		JOIN status s ON i_s.status_ID = s.status_ID
		WHERE incident_ID = " . $row['incident_ID'] . " ORDER BY timestamp";
		
		$comments = [];
		$evidence = [];
		$events = [];
		$created = [];
		$created['assets'] = $assets;
		$result = $mysqli->query($query);
		
		if (!($result && $result->num_rows > 0)) {
			$query = "SELECT i_status_ID, timestamp, s.status FROM incident_status i_s
			JOIN status s ON i_s.status_ID = s.status_ID
			WHERE incident_ID = " . $row['incident_ID'] . " ORDER BY timestamp";
		} else {
			$query = "SELECT i_status_ID, u.username, timestamp, s.status FROM incident_status i_s 
		JOIN user u ON u.user_ID = i_s.user_ID
		JOIN status s ON i_s.status_ID = s.status_ID
		WHERE incident_ID = " . $row['incident_ID'] . " ORDER BY timestamp";
		}
		$result = $mysqli->query($query);
		logError($query);
		if ($result && $result->num_rows > 0) {
			$first = True;
			while ($i_status_ID_row = $result->fetch_assoc()) {
				/*This section of the code get the comment from a certain incident_status_ID*/
				$comment_query = "SELECT comment FROM comment WHERE i_status_ID = '{$i_status_ID_row['i_status_ID']}'";
				$comment_result = $mysqli->query($comment_query);
				if ($first) {
					$status_changed = [];
					if (isset($i_status_ID_row['username'])) {
						$status_changed['username'] = $i_status_ID_row['username'];
						$created['username'] = $i_status_ID_row['username'];
					} else {
						$status_changed['username'] = "Unknown";
						$created['username'] = "Unknown";
					}
					$status_changed['timestamp'] = $i_status_ID_row['timestamp'];
					$status_changed['status'] = $i_status_ID_row['status'];
					$status_changed['type'] = "Incident created";
					$events[] = $status_changed;
					$first = False;
					$created['timestamp'] = $row['created'];
				}
				$evidence_query = "SELECT path FROM evidence WHERE i_status_ID = '{$i_status_ID_row['i_status_ID']}'";
				$evidence_result = $mysqli->query($evidence_query);
				
				if ($comment_result && $comment_result->num_rows > 0) {
					$comment = [];
					$comment_text = $comment_result->fetch_assoc();
					$comment['text'] = $comment_text['comment'];
					$comment['timestamp'] = $i_status_ID_row['timestamp'];
					$comment['username'] = $i_status_ID_row['username'] ?? 'Unknown';
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
					$status_changed['username'] = $i_status_ID_row['username'] ?? 'Unknown';
					$status_changed['timestamp'] = $i_status_ID_row['timestamp'];
					$status_changed['status'] = $i_status_ID_row['status'];
					$status_changed['type'] = "Status change -> {$i_status_ID_row['status']}";
					$events[] = $status_changed;
				}
			}
		}
		$row['created'] = $created;
		$row['assets'] = $assets;
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
				<th>Occurred</th>
				<th>Status</th>
                <th>Actions</th>
				<th>Events</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($incidents as $incident): ?>
                <tr>
                    <td><?= htmlspecialchars($incident['incident_ID']) ?></td>
                    <td><?= htmlspecialchars($incident['description']) ?></td>
                    <td><?= htmlspecialchars($incident['incident_type']) ?></td>
                    <td><?= htmlspecialchars($incident['severity']) ?></td>
					<td><?= htmlspecialchars($incident['created']['timestamp'] ?? 'Unknown') ?></td>
					<td>
						<?php if($_SESSION["role"] == "reporter"): ?>
							<?= htmlspecialchars($incident['status']) ?>
						<?php else: ?>
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
						<?php endif; ?>
					</td>
                    <td>
						<button type="button" class="btn btn-accent mx-auto" data-bs-toggle="offcanvas" data-bs-target="#incident_<?= htmlspecialchars($incident['incident_ID']) ?>" aria-controls="incident_<?= htmlspecialchars($incident['incident_ID']) ?>">
							Edit
						</button>
						
						<!-- Offcanvas, More selection -->
						<div class="offcanvas offcanvas-end offcanvas-md offcanvas_width" tabindex="-1" id="incident_<?= htmlspecialchars($incident['incident_ID']) ?>" aria-labelledby="IncidentLabel_<?= htmlspecialchars($incident['incident_ID']) ?>">
							<div class="offcanvas-header">
								<h3 class="offcanvas-title text-primary-mono fw-bold" id="IncidentLabel_<?= htmlspecialchars($incident['incident_ID']) ?>">Incident <?= htmlspecialchars($incident['incident_ID']) ?></h3>
								<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
							</div>
							<div class="offcanvas-body ">
								<!-- Här börjar själva panelen -->
								<div class="row">
									<div class="col border">
										<h5 class="fw-bold">Created by: </h5> <p><?= htmlspecialchars($incident['created']['username'] ?? 'Unknown') ?></p>
									</div>
									
									<div class="col border">
										<h5 class="fw-bold">Affected Assets:</h5>
										<ul>
										<?php foreach ($incident['created']['assets'] as $asset): ?>
											<li class="mb-0"><?= htmlspecialchars($asset) ?></li>
										<?php endforeach; ?>
										</ul>
									</div>
								</div>
								<div class="row">
									<div class="col">
										<form method="post" action="incident_dashboard.php">
											<div class="row mb-3">
												<div class="col">
													<label for="comment_<?= $incident['incident_ID']?>" class="form-label">Comment</label>
													<textarea class="form-control" name="comment" id="comment_<?= $incident['incident_ID']?>" rows="3" required></textarea>
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
													<label for="evidence_<?= $incident['incident_ID']?>" class="form-label">Upload evidence</label>
													<input class="form-control" type="file" name="files[]" id="evidence_<?= $incident['incident_ID']?>" multiple required>
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
													<?php asort($evidence); ?>
														<tr>
															<td><?= htmlspecialchars($evidence['timestamp'] ?? '') ?></td>
														
															<td><?= htmlspecialchars($evidence['username'] ?? '') ?></td>
														
															<td><a href="uploads/<?= htmlspecialchars($evidence['path'] ?? '') ?>"><?= htmlspecialchars($evidence['path'] ?? '') ?></a></td>
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
                    </td>
					<td>
						<button type="button" class="btn btn-accent mx-auto" data-bs-toggle="offcanvas" data-bs-target="#incident_event_<?= htmlspecialchars($incident['incident_ID']) ?>" aria-controls="incident_event_<?= htmlspecialchars($incident['incident_ID']) ?>">
							Show events
						</button>
						
						<!-- Offcanvas, More selection -->
						<div class="offcanvas offcanvas-end offcanvas-md offcanvas_width" tabindex="-1" id="incident_event_<?= htmlspecialchars($incident['incident_ID']) ?>" aria-labelledby="incidentLabel_<?= htmlspecialchars($incident['incident_ID']) ?>">
							<div class="offcanvas-header">
								<h3 class="offcanvas-title text-primary-mono fw-bold" id="incidentLabel_<?= htmlspecialchars($incident['incident_ID']) ?>">Incident <?= htmlspecialchars($incident['incident_ID']) ?></h3>
								<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
							</div>
							<div class="offcanvas-body">
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
										</tbody>
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
			$newRow.append($('<td>').text(row.occurred));
            $newRow.append($('<td>').html(row.select));
            $newRow.append($('<td>').html(row.edit));
			$newRow.append($('<td>').html(row.event));
            
            $tableBody.append($newRow);
        });
    }
    // Add event listeners to both select elements
    $incidentFilter.on('change', filterTable);
    $severityFilter.on('change', filterTable);
	
	
	$(document).on('change', 'select[name="status"]', function() {
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
	
	$('#checkBtn').click(function() {
        checked = $("input[type=checkbox]:checked").length;

        if(!checked) {
            alert("You must check at least one checkbox.");
            return false;
        }
    });
});
</script>
<?php
echo $footer;
?>
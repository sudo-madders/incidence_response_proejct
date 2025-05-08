<?php
include_once("template.php");
include_once('library/database.php');

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
				INSERT INTO incident_status (incident_ID, user_ID, timestamp, status_ID)
				VALUES (?,?, NOW(), ?)
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
				<div class="col">
					<div class="row mb-3 border">
						<button type="button" class="btn btn-primary mx-auto" data-bs-toggle="offcanvas" data-bs-target="#addNewIncident" aria-controls="addNewIncident">
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
						
						<form method="post" action="incident_dashboard.php" class="d-flex flex-wrap align-items-end gap-3">
		<input type="hidden" name="filter" value="1">

		<div class="col-md-2">
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

		<div class="col-md-2">
			<label for="severity" class="form-label">Severity</label>
			<select class="form-select" name="severity" id="severity">
				<option selected>All</option>
				<option value="Low">Low</option>
				<option value="Medium">Medium</option>
				<option value="High">High</option>
				<option value="Critical">Critical</option>
			</select>
		</div>
	</form>
</div>

<?php
if ($_SESSION['role'] == "reporter") {
	$user_ID = $_SESSION['user_ID'];
	$incident_query = "SELECT * FROM all_incidents WHERE user_ID = '{$user_ID}'";
	logError($incident_query);
} else {
	$incident_query = "SELECT * FROM all_incidents";
}
$incident_result = $mysqli->query($incident_query);
if ($incident_result && $incident_result->num_rows > 0) {
	$incidents = [];
	while ($row = $incident_result->fetch_assoc()) {
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
					<td><?= htmlspecialchars($incident['status']) ?></td>
                    <td>
						<button type="button" class="btn btn-primary mx-auto" data-bs-toggle="offcanvas" data-bs-target="incident_<?= htmlspecialchars($incident['incident_ID']) ?>" aria-controls="incident_<?= htmlspecialchars($incident['incident_ID']) ?>">
							Edit
						</button>
						
						<!-- Offcanvas, More selection -->
						<div class="offcanvas offcanvas-end offcanvas-md offcanvas_width" tabindex="-1" id="incident_<?= htmlspecialchars($incident['incident_ID']) ?>" aria-labelledby="addNewIncidentLabel">
							<div class="offcanvas-header">
								<h5 class="offcanvas-title" id="addNewIncidentLabel">incident_<?= htmlspecialchars($incident['incident_ID']) ?></h5>
								<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
							</div>
							<div class="offcanvas-body ">
								<!-- Här börjar själva panelen -->
								
								<form method="post" enctype="multipart/form-data">
								<label for="comment">Comment:</label>
								<textarea name="comment" id="comment"><?= htmlspecialchars($incident['comment'] ?? '') ?></textarea><br>
								<br>
								<label for="evidence_file">Evidence (upload a file):</label>
								<input type="file" name="evidence_file" id="evidence_file"><br>
								<br>
								<button type="submit">Update Incident</button>
								</form>
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
document.addEventListener('DOMContentLoaded', function() {
var incident_filter = document.getElementById("incident_type");
var severity_filter = document.getElementById("severity");
var myTable = document.getElementById("incident_table");
var tableRows = myTable.getElementsByTagName("tr");

function filterTable() {
console.log("Update");
var formData = new FormData();
formData.append("incident_type", incident_filter.value);
formData.append("severity", severity_filter.value);
formData.append("filter", "yes");

fetch("/project/library/filter_incidents.php",{
method: 'POST',
body: formData
})
.then(response => {
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }
  return response.json();
})
.then(data => {
 console.log(data);
 updateTable(data);
})
.catch(error => {
  console.error(`There was an error sending the request or processing the response: ${error.message}`);
});
}

function updateTable(data) {
  // Clear the existing table rows (except the header)
  var tbody = myTable.getElementsByTagName('tbody')[0];
  while (tbody.firstChild) {
    tbody.removeChild(tbody.firstChild);
  }

  // Populate the table with the new data
  data.forEach(row => {
    var newRow = tbody.insertRow();

    var cell1 = newRow.insertCell();
    cell1.textContent = row.incident_ID;

    var cell2 = newRow.insertCell();
    cell2.textContent = row.description;

    var cell3 = newRow.insertCell();
    cell3.textContent = row.incident_type;

    var cell4 = newRow.insertCell();
    cell4.textContent = row.severity;
	
	var cell5 = newRow.insertCell();
    cell5.textContent = "Status";
	
	var cell6 = newRow.insertCell();
    cell6.innerHTML = row.edit;
  });
}
 
  // Add event listeners to both select elements
 incident_filter.addEventListener('change', filterTable);
 severity_filter.addEventListener('change', filterTable);

document.addEventListener('click', function(event) {
  if (event.target.matches('[data-bs-toggle="offcanvas"]')) {
    const targetId = event.target.getAttribute('data-bs-target');
    const offcanvasElement = document.getElementById(targetId.startsWith('#') ? targetId.substring(1) : targetId);
    if (offcanvasElement) {
      const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement) || new bootstrap.Offcanvas(offcanvasElement);
      offcanvas.show();
    }
  }
});
});
</script>
<?php
echo $footer;
?>
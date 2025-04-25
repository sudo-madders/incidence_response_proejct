<?php
include("template.php");
include('library/database.php');
include('filter_incidents.php');

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
												<option value="Unauthorized access attacks">Unauthorized access attacks</option>
												<option value="Man-in-the-middle">Man-in-the-middle</option>
												<option value="Theft">Theft</option>
												<option value="Denial of service">Denial of service</option>
												<option value="Insider threats">Insider threats</option>
												<option value="Ransomware">Ransomware</option>
												<option value="Privilege escalation">Privilege escalation</option>
												<option value="Phishing attack">Phishing attack</option>
												<option value="Password attack">Password attack</option>
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
			<select class="form-select" name="incident_type">
				<option selected disabled>Choose incident type</option>
				<option value="Unauthorized access attacks">Unauthorized access attacks</option>
				<option value="Man-in-the-middle">Man-in-the-middle</option>
				<option value="Theft">Theft</option>
				<option value="Denial of service">Denial of service</option>
				<option value="Insider threats">Insider threats</option>
				<option value="Ransomware">Ransomware</option>
				<option value="Privilege escalation">Privilege escalation</option>
				<option value="Phishing attack">Phishing attack</option>
				<option value="Password attack">Password attack</option>
			</select>
		</div>

		<div class="col-md-2">
			<label for="severity" class="form-label">Severity</label>
			<select class="form-select" name="severity">
				<option selected disabled>Choose severity</option>
				<option value="Low">Low</option>
				<option value="Medium">Medium</option>
				<option value="High">High</option>
				<option value="Critical">Critical</option>
			</select>
		</div>

		<div class="col-auto">
			<button type="submit" class="btn btn-primary">Filter</button>
			<a href="incident_dashboard.php" class="btn btn-primary">Clear Filter</a>
		</div>
	</form>
</div>
							
						
						
						<?php if (!empty($incidents)): ?>
    <div class="table-responsive">
    <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Description</th>
                <th>Type</th>
                <th>Severity</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($incidents as $incident): ?>
                <?php
                    $incident_type_id = (int)$incident['incident_type_ID'];
                    $severity_id = (int)$incident['severity_ID'];

                    $type_result = $mysqli->query("SELECT incident_type FROM incident_type WHERE incident_type_ID = $incident_type_id");
                    $severity_result = $mysqli->query("SELECT severity FROM severity WHERE severity_ID = $severity_id");

                    $incident_type = $type_result->fetch_assoc()['incident_type'] ?? 'Unknown';
                    $severity = $severity_result->fetch_assoc()['severity'] ?? 'Unknown';
                ?>
                <tr>
                    <td><?= htmlspecialchars($incident['incident_ID']) ?></td>
                    <td><?= htmlspecialchars($incident['description']) ?></td>
                    <td><?= htmlspecialchars($incident_type) ?></td>
                    <td><?= htmlspecialchars($severity) ?></td>
                    <td>
                        <a href="edit_incident.php?incident_id=<?= $incident['incident_ID'] ?>" class="btn btn-sm btn-primary">Edit</a>
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

					

					
					</div>
					
					
		
		

<?php
echo $footer;
?>
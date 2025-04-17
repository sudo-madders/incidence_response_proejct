<?php 

include('library/database.php');

if (isset($_POST['incident_type'], $_POST['severity'], $_POST['description'])) {
    $incident_type = $mysqli->real_escape_string($_POST['incident_type']);
    $severity = $mysqli->real_escape_string($_POST['severity']);
    $description = $mysqli->real_escape_string($_POST['description']);

   




    $query = "INSERT INTO incident (incident_type_ID, severity_ID, description) 
              VALUES ((SELECT incident_type_ID FROM incident_type WHERE incident_type = '{$incident_type}'),(SELECT severity_ID FROM severity WHERE severity = '{$severity}'), '$description')";

    
    if ($mysqli->query($query) === TRUE) {
        $incident_id = $mysqli->insert_id;

        
        if (!empty($_POST['affected_assets']) && is_array($_POST['affected_assets'])) {
            
            foreach ($_POST['affected_assets'] as $asset_value) {
                $asset_clean = $mysqli->real_escape_string($asset_value);

               
                $asset_ids = [
                    'inventory' => 1,
                    'users' => 2,
                    'devices' => 3,
                    'network' => 4,
                ];

                
                if (array_key_exists($asset_clean, $asset_ids)) {
                    $asset_id = $asset_ids[$asset_clean];

                    
                    $insert_asset = "INSERT INTO affected_assets (asset_ID, incident_ID) 
                                     VALUES ($asset_id, $incident_id)";
                    $mysqli->query($insert_asset);
                } else {
                    echo "Warning: Asset '$asset_clean' not recognized.<br>";
                }
            }
        }

        
        header('Location: incident_dashboard.php');
        exit;
    } else {
        die("Could not insert incident: " . $mysqli->errno . " - " . $mysqli->error);
    }
}
?>

<?php 
include("template.php");
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
					
					<div class="row mb-3 border">
						<div class="row">
							<div class="col"><p>Incident ID: 1</p></div>
							<div class="col"><p>Incident Type: DDoS</p></div>
							<div class="col"><p>Severity: Critical</p></div>
						</div>
						<button type="button" class="btn btn-primary mx-auto" style="width: 150px" data-bs-toggle="offcanvas" data-bs-target="#newIncident" aria-controls="newIncident">
							Show more
						</button>
							
						<!-- Offcanvas, More selection -->
						<div class="offcanvas offcanvas-end offcanvas-md offcanvas_width" tabindex="-1" id="newIncident" aria-labelledby="newIncidentLabel">
							<div class="offcanvas-header">
								<h5 class="offcanvas-title" id="newIncidentLabel">Incident 1</h5>
								<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
							</div>
							<div class="offcanvas-body ">
								<!-- Här börjar själva panelen -->
								<form>
									<div class="row">
										<div class="col-md-6">
											<label for="incident_type" class="form-label">Incident Type</label>
											<select id="incident_type" class="form-select">
												<option selected>Unauthorized access</option>
												<option>Data breache</option>
												<option>Malware infection</option>
												<option>Denial-of-service</option>
												<option>Insider threat</option>
												<option>Social engineering attack</option>
												<option>Physical security breache</option>
												<option>Compliance violation</option>
											</select>
										</div>
										<div class="col-md-6">
											<label for="severity" class="form-label">Severity</label>
											<select id="severity" class="form-select">
												<option selected>Low</option>
												<option>Medium</option>
												<option>High</option>
												<option>Critical</option>
											</select>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
					
					<!--Here the row ends -->
					<div class="row my-3 border border-dark">
						<h3>Incident ID: </h3>
						<p>Incident type: </p>
						<strong><p>Severity: </p></strong>
					</div>
					<div class="row my-3 border border-danger">
						<h3>Incident ID: </h3>
						<p>Incident type: </p>
						<strong><p>Severity: </p></strong>
					</div>
					<div class="row my-3 border border-black">
						<h3>Incident ID: </h3>
						<p>Incident type: </p>
						<strong><p>Severity: </p></strong>
					</div>
				</div>
			</div>
		</div>
		
		

<?php
echo $footer;
?>

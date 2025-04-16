<?php 
include("template.php");
?>
<!-- Main content -->
				<div class="col">
					<div class="row mb-3 border">
						<button type="button" class="btn btn-primary mx-auto" data-bs-toggle="offcanvas" data-bs-target="#addNewUser" aria-controls="addNewUser">
							Add new user
						</button>
						
						<!-- Offcanvas, More selection -->
						<div class="offcanvas offcanvas-end offcanvas-md offcanvas_width" tabindex="-1" id="addNewUser" aria-labelledby="addNewUserLabel">
							<div class="offcanvas-header">
								<h5 class="offcanvas-title" id="addNewUserLabel">Add new user</h5>
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
					
					<div class="row mb-3 border">
						<div class="row">
							<div class="col"><p>User ID: 1</p></div>
							<div class="col"><p>Incident Type: DDoS</p></div>
							<div class="col"><p>Severity: Critical</p></div>
						</div>
						<button type="button" class="btn btn-primary mx-auto" style="width: 150px" data-bs-toggle="offcanvas" data-bs-target="#newIncident" aria-controls="newIncident">
							Show more
						</button>
							
						<!-- Offcanvas, More selection -->
						<div class="offcanvas offcanvas-end offcanvas-md offcanvas_width" tabindex="-1" id="newIncident" aria-labelledby="newIncidentLabel">
							<div class="offcanvas-header">
								<h5 class="offcanvas-title" id="newIncidentLabel">User 1</h5>
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
					
<?php 
echo $footer;
?>
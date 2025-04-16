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

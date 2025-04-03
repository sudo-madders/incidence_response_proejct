https://mahhus24.ddi.hh.se/a4/test.html

<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Bootstrap demo</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
		<style>
			/* Custom CSS for responsive behavior */
			@media (min-width: 576px) {
				/* Show sidebar normally on screens wider than sm */
				#sidebarMenu {
					position: static;
					height: auto;
					transform: none;
					visibility: visible !important;
					width: auto;
				}
				
				.offcanvas-body {
					overflow: visible;
				}
			}
			.col-auto {
			  flex: 0 0 auto;  /* Prevent growing/shrinking */
			  width: auto;     /* Reset Bootstrap's column width */
			  min-width: min-content; /* Prevent content collapse */
			}
			
			@media (max-width: 575.98px) {
				/* Adjust main content margin when sidebar is open */
				.offcanvas-start {
					width: 250px;
				}
				
				.col.border {
					margin-left: 0 !important;
				}
			}
		</style>
	</head>
<body>




<?php 
session_name('Website'); 
session_start(); 
$host       = "localhost"; 
$user       = "user_name"; // e.g. wagner24 
$pwd        = "user_pwd"; // e.g takeAbath@06h30 
$db         = "user_name"; // e.g wagner24 
$mysqli     = new mysqli($host, $user, $pwd, $db); 
$navigation = <<<END 
 <nav> 
      <a href="index.php">Incident Dashboard</a> 
      <a href="usermanagement.php">User Managment</a> 
	<a href="pagetrafficlogg.php">Page traffic logg</a>
	<a href="incidentanalytics.php">Incident Analytics</a>
  </nav>     
END; 
?> 

	
    <div class="container-fluid border">
		<div class="row align-items-center">
			<!-- Hamburger Button (visible only on small screens) -->
			<button class="col-auto d-sm-none btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
				☰
			</button>
			<div class="col">
				<h1>Incident response Portal</h1>
			</div>
			
		</div>
		<div class="row gx-6 border">
			<div class="col-auto offcanvas offcanvas-start border" id="sidebarMenu" data-bs-backdrop="false">
				<div class="offcanvas-header d-block d-sm-none">
					<!-- Close Button -->
					<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
				</div>
				<div class="offcanvas-body">
					<ul class="nav nav-pills flex-column">
					  <li class="nav-item">
						<a class="nav-link active" aria-current="page" href="#">Incident Dashboard</a>
					  </li>
					  <li class="nav-item">
						<a class="nav-link" href="#">User Managment</a>
					  </li>
					  <li class="nav-item">
						<a class="nav-link" href="#">Page traffic logg</a>
					  </li>
					  <li class="nav-item">
						<a class="nav-link" href="#">Incident Analytics</a>
					  </li>
					</ul>
				</div>
			</div>
			<div class="col border">
				<div class="row border>">
					<p>ROW</p>
				</div>
			</div>
		</div>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</html>

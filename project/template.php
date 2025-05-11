<?php 
session_name('project');
session_start();

// Check if the user is logged in (session variable is set)
if (!isset($_SESSION['user_ID'])) {
    header("Location:index.php");
    exit;
}

$currentPage = $_SERVER['PHP_SELF'];
$currentPage = explode("/", $currentPage)[2];
?>
<!doctype html>
<html lang="en" class="bg-secondary-mono">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Bootstrap demo</title>
		<link href="css/stylesheet.css" rel="stylesheet">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
		
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
		<script src="https://www.gstatic.com/charts/loader.js"></script>
	</head>
	<body>
		<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
		<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
		<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
		<div class="container-fluid border bg-secondary-mono">
			<div class="row align-items-center justify-content-end bg-primary-mono">
				<!-- Hamburger Button (visible only on small screens) -->
				
				<button class="col-auto d-md-none btn btn-lg" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
					☰
				</button>
				<div class="col">
					<h1>Incident Response Portal</h1>
				</div>
				
				<?php if (isset($_SESSION["user_ID"])): ?>
				<div class="col-auto pt-3">
					<div class="row">
						<div class="col">
							<p>Current user: <?= $_SESSION['username'] ?></p>
						</div>
						<div class="col">
							<p>Role: <?= $_SESSION['role'] ?></p>
						</div>
						<div class="col">
							<a href="logout.php" class="btn btn-accent" role="button">Log out</a>
						</div>
					</div>
				</div>
				<?php endif; ?>
				
				<!-- Offcanvas meny-->
				<div class="offcanvas offcanvas-start d-md-none bg-light" id="sidebarMenu" data-bs-scroll="true" data-bs-backdrop="true">
					<div class="offcanvas-header">
						<!-- Close Button -->
						<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
					</div>
					<div class="offcanvas-body">
						<ul class="nav nav-pills flex-column gap-2 p-3">
							<li class="nav-item">
								<a class="nav-link rounded-pill d-flex align-items-center py-2 px-3 <?= ($currentPage == 'incident_dashboard.php') ? 'active bg-secondary text-white' : 'text-dark bg-light' ?> hover-effect" 
								   href="incident_dashboard.php">
								   <i class="bi bi-clipboard-data me-2"></i>
								   Incident Dashboard
								</a>
							</li>
							
							<?php if ($_SESSION["role"] == "administrator"): ?>
							<li class="nav-item">
								<a class="nav-link rounded-pill d-flex align-items-center py-2 px-3 <?= ($currentPage == 'user_management.php') ? 'active bg-secondary text-white' : 'text-dark bg-light' ?> hover-effect" 
								   href="user_management.php">
								   <i class="bi bi-people me-2"></i>
								   User Management
								</a>
							</li>
							
							<li class="nav-item">
								<a class="nav-link rounded-pill d-flex align-items-center py-2 px-3 <?= ($currentPage == 'page_analytics.php') ? 'active bg-secondary text-white' : 'text-dark bg-light' ?> hover-effect" 
								   href="page_analytics.php">
								   <i class="bi bi-bar-chart me-2"></i>
								   Page Analytics
								</a>
							</li>
							<?php endif; ?>
							
							<li class="nav-item">
								<a class="nav-link rounded-pill d-flex align-items-center py-2 px-3 <?= ($currentPage == 'incident_analytics.php') ? 'active bg-secondary text-white' : 'text-dark bg-light' ?> hover-effect" 
								   href="incident_analytics.php">
								   <i class="bi bi-graph-up me-2"></i>
								   Incident Analytics
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
				
				<div class="row gx-4 vh-80 border border-dark">
					<!-- Sidebar navigation -->
					<div class="col-auto border-end border-secondary d-none d-md-block bg-secondary-mono">
						<ul class="nav nav-pills flex-column gap-2 p-3">
							<li class="nav-item">
								<a class="nav-link rounded-pill d-flex align-items-center py-2 px-3 <?= ($currentPage == 'incident_dashboard.php') ? 'active bg-primary-mono text-white' : 'text-dark bg-light' ?> hover-effect" 
								   href="incident_dashboard.php">
								   <i class="bi bi-clipboard-data me-2"></i>
								   Incident Dashboard
								</a>
							</li>
							
							<?php if ($_SESSION["role"] == "administrator"): ?>
							<li class="nav-item">
								<a class="nav-link rounded-pill d-flex align-items-center py-2 px-3 <?= ($currentPage == 'user_management.php') ? 'active bg-primary-mono text-white' : 'text-dark bg-light' ?> hover-effect"
								   href="user_management.php">
								   <i class="bi bi-people me-2"></i>
								   User Management
								</a>
							</li>
							
							<li class="nav-item">
								<a class="nav-link rounded-pill d-flex align-items-center py-2 px-3 <?= ($currentPage == 'page_analytics.php') ? 'active bg-primary-mono text-white' : 'text-dark bg-light' ?> hover-effect" 
								   href="page_analytics.php">
								   <i class="bi bi-bar-chart me-2"></i>
								   Page Analytics
								</a>
							</li>
							<?php endif; ?>
							
							<li class="nav-item">
								<a class="nav-link rounded-pill d-flex align-items-center py-2 px-3 <?= ($currentPage == 'incident_analytics.php') ? 'active bg-primary-mono text-white' : 'text-dark bg-light' ?> hover-effect" 
								   href="incident_analytics.php">
								   <i class="bi bi-graph-up me-2"></i>
								   Incident Analytics
								</a>
							</li>
						</ul>
					</div>


					<!-- Main content -->

<?php 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once("library/tracking.php");
$footer = <<<END
			</div>
		</div>
		</div>
		<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
	</body>
</html>
END;
?>

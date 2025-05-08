<?php 
session_name('project');
session_start();

// Check if the user is logged in (session variable is set)
if (!isset($_SESSION['user_ID'])) {
    header('HTTP/1.0 401 Unauthorized');
    echo 'You must be logged in to access this page.';
    exit;
}

$currentPage = $_SERVER['PHP_SELF'];
$currentPage = explode("/", $currentPage)[2];
?>
<!doctype html>
<html lang="en">
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
		<div class="container-fluid border">
			<div class="row align-items-center justify-content-end">
				<!-- Hamburger Button (visible only on small screens) -->
				
				<button class="col-auto d-md-none btn btn-lg" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
					☰
				</button>
				<div class="col">
					<h1>Incident response Portal</h1>
				</div>
				
				<?php if (isset($_SESSION["user_ID"])): ?>
				<div class="col-auto">
					<h5>Current user: <?= $_SESSION['username'] ?></h5>
				</div>
				<div class="col-auto">
					<h5>Role: <?= $_SESSION['role'] ?></h5>
				</div>
				<div class="col-auto">
					<a href="logout.php" class="btn btn-secondary" role="button">Log out</a>
				</div>
				<?php endif; ?>
				
				<!-- Offcanvas meny-->
				<div class="offcanvas offcanvas-start d-md-none border" id="sidebarMenu" data-bs-scroll="true" data-bs-backdrop="true">
					<div class="offcanvas-header">
						<!-- Close Button -->
						<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
					</div>
					<div class="offcanvas-body">
						<ul class="nav nav-pills flex-column">
						  <li class="nav-item">
							<a class="nav-link <?php if ($currentPage == 'incident_dashboard.php') { echo 'active'; }?>" href="incident_dashboard.php">Incident Dashboard</a>
						  </li>
						  <?php if ($_SESSION["role"] == "administrator"): ?>
						  <li class="nav-item">
							<a class="nav-link <?php if ($currentPage == 'user_management.php') { echo 'active'; }?>" href="user_management.php">User Managment</a>
						  </li>
						  <?php endif; ?>
						  <li class="nav-item">
							<a class="nav-link <?php if ($currentPage == 'page_analytics.php') { echo 'active'; }?>" href="page_analytics.php">Page analytics</a>
						  </li>
						  <li class="nav-item">
							<a class="nav-link <?php if ($currentPage == 'incident_analytics.php') { echo 'active'; }?>" href="incident_analytics.php">Incident Analytics</a>
						  </li>
						</ul>
					</div>
				</div>
			</div>
				
				<div class="row gx-4 mt-3 vh-80 border border-danger">
					<!-- Sidebar navigation -->
					<div class="col-auto border border-secondary d-none d-md-block">
						<ul class="nav nav-pills flex-column">
						  <li class="nav-item">
							<a class="nav-link <?php if ($currentPage == 'incident_dashboard.php') { echo 'active'; }?>" href="incident_dashboard.php">Incident Dashboard</a>
						  </li>
						  <?php if ($_SESSION["role"] == "administrator"): ?>
						  <li class="nav-item">
							<a class="nav-link <?php if ($currentPage == 'user_management.php') { echo 'active'; }?>" href="user_management.php">User Managment</a>
						  </li>
						  <?php endif; ?>
						  <li class="nav-item">
							<a class="nav-link <?php if ($currentPage == 'page_analytics.php') { echo 'active'; }?>" href="page_analytics.php">Page analytics</a>
						  </li>
						  <li class="nav-item">
							<a class="nav-link <?php if ($currentPage == 'incident_analytics.php') { echo 'active'; }?>" href="incident_analytics.php">Incident Analytics</a>
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

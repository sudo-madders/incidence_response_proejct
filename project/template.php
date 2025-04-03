<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Bootstrap demo</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
		<link href="css/stylesheet.css" rel="stylesheet">
	</head>
	<body>
		<div class="container-fluid border">
			<!-- Header + Hambuger -->
			<div class="row align-items-center">
				<!-- Hamburger Button (visible only on small screens) -->
				<button class="col-auto d-sm-none btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
					☰
				</button>
				<div class="col">
					<h1>Incident response Portal</h1>
				</div>
			</div>
			<!-- Offcanvas meny-->
			<div class="offcanvas offcanvas-start d-sm-none border" id="sidebarMenu" data-bs-scroll="true" data-bs-backdrop="true">
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
			
			
			<div class="row gx-4">
				<!-- Sidebar navigation -->
				<div class="col-auto d-none d-sm-block">
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
				<!-- Main content -->
<?php 
$footer = <<<END
			</div>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
	</body>
</html>
END;
?>
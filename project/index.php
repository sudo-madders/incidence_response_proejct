<?php
session_name('project');
session_start();
include_once('library/tracking.php');
include_once('library/database.php');
$failed_login = False;

if (isset($_SESSION['user_ID'])) {
	header("Location:incident_dashboard.php");
}

if (isset($_POST['username']) and isset($_POST['password'])) {
	
	$name = $mysqli->real_escape_string($_POST['username']);
	$pwd = $mysqli->real_escape_string($_POST['password']);
	
	$query = "SELECT username, user_ID, password, role FROM user u JOIN user_role ur ON u.user_role_ID = ur.user_role_ID WHERE username ='$name'";
	
	$result = $mysqli->query($query);
	if ($result->num_rows > 0) {
		$row = $result->fetch_object();
		if (password_verify($pwd, $row->password)) {
			$_SESSION["username"] = $row->username;
			$_SESSION["user_ID"] = $row->user_ID;
			$_SESSION["role"] = $row->role;
			header("Location:incident_dashboard.php");
		} else {
			$failed_login = True;
		}
	} else {
		$failed_login = True;
	}
	
}
?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Bootstrap demo</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
	</head>
	<body>
		<div class="bg-light">
		<section class="vh-100 gradient-custom">
		  <div class="container py-5 h-100">
			<div class="row d-flex justify-content-center align-items-center h-100">
			  <div class="col-12 col-md-8 col-lg-6 col-xl-5">
				<div class="card bg-dark text-white" style="border-radius: 1rem;">
				  <div class="card-body p-5 text-center">
					<div class="mb-md-5 mt-md-4 pb-5">
					<form action="index.php" method="POST">
					  <h2 class="fw-bold mb-2 text-uppercase">Login</h2>
					  <p class="text-white-50 mb-5">Please enter your login and password!</p>

					  <div data-mdb-input-init class="form-outline form-white mb-4">
						<input id="typeEmailX" name="username" class="form-control form-control-lg" />
						<label class="form-label" for="typeEmailX">Username</label>
					  </div>

					  <div data-mdb-input-init class="form-outline form-white mb-4">
						<input type="password" id="typePasswordX" name="password" class="form-control form-control-lg" />
						<label class="form-label" for="typePasswordX">Password</label>
					  </div>

					  <button data-mdb-button-init data-mdb-ripple-init class="btn btn-outline-light btn-lg px-5" type="submit">Login</button>
					</form>
					</div>
					<?php if ($failed_login): ?>
					<h4 class="text-white-100 mb-5" style="font-size: 20;">Wrong username or password</h4>
					<?php endif; ?>
				  </div>
				</div>
			  </div>
			</div>
		  </div>
		</section>
		</div>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
	</body>
</html>

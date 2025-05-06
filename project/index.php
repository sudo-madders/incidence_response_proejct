<?php
session_name('project');
session_start();
include_once('library/tracking.php');
include_once('library/loging.php');
include_once('library/database.php');

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
		}
	} else {
		echo "Wrong username or password123";
	}
	
}
$content = <<<END
<form action="index.php" method="POST">
<input type="text" name="username" placeholder="username">
<input type="password" name="password" placeholder="password">
<input type="submit" value="Login">
</form>
END;
echo $content;

$footer = <<<END
			</div>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
	</body>
</html>
END;
echo $footer;
?>

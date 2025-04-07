<?php
include('library/database.php');
if (isset($_POST['username']) and isset($_POST['password'])) {
	$name = $mysqli->real_escape_string($_POST['username']);
	$pwd = $mysqli->real_escape_string($_POST['password']);
	$query = <<<END
	SELECT username, password, id FROM users
	WHERE username = '{$name}'
	AND password = '{$pwd}'
	END;
	$result = $mysqli->query($query);
	if ($result->num_rows > 0) {
		$row = $result->fetch_object();
		$_SESSION["username"] = $row->username;
		$_SESSION["userId"] = $row->id;
		header("Location:incident_dashboard.php");
	} else {
	echo "Wrong username or password. Try again";
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

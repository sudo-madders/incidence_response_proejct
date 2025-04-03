<?php 
	echo "<h1>What we are tracking</h1>";
	echo "IP: {$_SERVER['REMOTE_ADDR']}";
	echo "<br>";
	echo "Browser: {$_SERVER['HTTP_USER_AGENT']}";
	echo "<br>";
	echo "Time(?): {$_SERVER['REQUEST_TIME']}";
	echo "<br>";
	$page = end(explode('/', $_SERVER['SCRIPT_FILENAME']));
	echo "Page: {$page}";
	echo "Still needs to be connected to";
?>

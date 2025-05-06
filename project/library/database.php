<?php 
/*
* This is our database connection. It will be included in 
* files where we need to connect to the database.
*/
require_once("loging.php");

$host       = "localhost"; 
$user       = "isacli24";
$pwd        = "FV0t2Wgb0b";
$db         = "isacli24";
$mysqli     = new mysqli($host, $user, $pwd, $db);
if ($mysqli->connect_error) {
	lorError($mysqli->connect_error);
    die("Connection failed: " . $mysqli->connect_error);
}
?>
<?php
session_name('project');
session_start();
/*
* This file acts as and endpoint, it provides the page analytics page with data from the database.
*/
// Check if the user is logged in (session variable is set)
if (!isset($_SESSION["username"])) {
    header('Location:index.php');
    exit;
}

require_once("database.php"); 
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$query = "SELECT visit_ID, page, browser, ip, username, timestamp FROM page_analytics";
$result = $mysqli->query($query);

$data = array();
while ($row = $result->fetch_assoc()) {
	if (empty($row['username'])) {
		$row['username'] = "Unknown";
	}
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode(array("data" => $data));

$mysqli->close();
?>
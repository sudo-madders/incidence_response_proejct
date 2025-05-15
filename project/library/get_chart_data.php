<?php
session_name('project');
session_start();
/*
* This file acts as an endpoint to the piechart.
*/
// Check if the user is logged in (session variable is set)
if (!isset($_SESSION["username"])) {
    header('Location:index.php');
    exit;
}

require("database.php");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$query = "SELECT incident_type, count(*) AS count FROM incident_report GROUP BY incident_type";
$result = $mysqli->query($query);

$data = array();
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json'); 
echo json_encode(array("data" => $data));

$mysqli->close();
?>
<?php
session_name('project');
session_start();

// Check if the user is logged in (session variable is set)
if (!isset($_SESSION["username"])) {
    header('Location:index.php');
    exit;
}

require("database.php"); //  Ensure this path is correct!
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

//$query = "SELECT visit_ID, page_ID, browser_ID, ip, timestamp FROM visit_tracking"; //  Correct and specific
$query = "SELECT incident_type, count(*) AS count FROM incident_report GROUP BY incident_type";
$result = $mysqli->query($query);

$data = array();
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json'); //  Explicit JSON header
echo json_encode(array("data" => $data));

$mysqli->close(); //  Close the connection
?>
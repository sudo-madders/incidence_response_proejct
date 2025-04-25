<?php
require("database.php"); //  Ensure this path is correct!
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

//$query = "SELECT visit_ID, page_ID, browser_ID, ip, timestamp FROM visit_tracking"; //  Correct and specific
$query = "SELECT visit_ID, page, browser, ip, username, timestamp FROM page_analytics";
$result = $mysqli->query($query);

$data = array();
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json'); //  Explicit JSON header
echo json_encode(array("data" => $data));

$mysqli->close(); //  Close the connection
?>
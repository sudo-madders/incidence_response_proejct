<?php
session_name('project');
session_start();

// Check if the user is logged in (session variable is set)
if (!isset($_SESSION['user_ID'])) {
    header('HTTP/1.0 401 Unauthorized');
    echo 'You must be logged in to access this API.';
    exit;
}

require("database.php"); //  Ensure this path is correct!
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

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
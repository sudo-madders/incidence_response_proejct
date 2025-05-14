<?php
session_name('project');
session_start();

// Check if the user is logged in (session variable is set)
if (!isset($_SESSION["username"])) {
    header('Location:index.php');
    exit;
}

require_once("database.php"); //  Ensure this path is correct!
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

header('Content-Type: application/json'); //  Explicit JSON header
echo json_encode(array("data" => $data));

$mysqli->close(); //  Close the connection
?>
<?php
session_name('project');
session_start();
include_once('database.php');
include_once('loging.php');


// Check if user is logged in
if (!isset($_SESSION["username"])) {
    header('Location:index.php');
    exit;
}

// Sanitize inputs
$incident_id = filter_input(INPUT_POST, 'incident_id', FILTER_SANITIZE_NUMBER_INT);
$new_status = filter_input(INPUT_POST, 'new_status', FILTER_SANITIZE_STRING);

$allowed_statuses = ['Pending', 'In progress', 'Resolved'];
if (!in_array($new_status, $allowed_statuses)) {
    http_response_code(400); // Bad Request
    die("Invalid status.");
}

// Update database
$query = "INSERT INTO incident_status (status_ID, incident_ID, user_ID)
          VALUES ((SELECT status_ID FROM status WHERE status = ?),?,?)";

if ($stmt = $mysqli->prepare($query)) {
    $stmt->bind_param("sss", $new_status, $incident_id, $_SESSION['user_ID']);
    if ($stmt->execute()) {
        echo "Status updated successfully.";
    } else {
        http_response_code(500); // Server Error
        echo "Database error: " . $stmt->error;
    }
    
    $stmt->close();
} else {
    http_response_code(500); // Server Error
    echo "Database error: " . $mysqli->error;
}
?>
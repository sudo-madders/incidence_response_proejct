<?php

$incident_type_filter = '';
$severity_filter = '';
$incidents = [];

// Check if form is submitted for filtering
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filter'])) {
    $incident_type_filter = isset($_POST['incident_type']) ? $mysqli->real_escape_string($_POST['incident_type']) : '';
    $severity_filter = isset($_POST['severity']) ? $mysqli->real_escape_string($_POST['severity']) : '';
   

    $query = "SELECT i.incident_ID, i.incident_type_ID, i.severity_ID, i.description 
              FROM incident i 
              WHERE 1=1";

    // Apply filters to query
    if (!empty($incident_type_filter)) {
        $query .= " AND i.incident_type_ID = (SELECT incident_type_ID FROM incident_type WHERE incident_type = '$incident_type_filter')";
    }

    if (!empty($severity_filter)) {
        $query .= " AND i.severity_ID = (SELECT severity_ID FROM severity WHERE severity = '$severity_filter')";
    }

   

    // Run the query for filtered incidents
    $result = $mysqli->query($query);
} else {
    // No filter, load all incidents
    $query = "SELECT i.incident_ID, i.incident_type_ID, i.severity_ID, i.description 
              FROM incident i";

    // Run the query for all incidents
    $result = $mysqli->query($query);
}

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $incidents[] = $row;
    }
}
?>

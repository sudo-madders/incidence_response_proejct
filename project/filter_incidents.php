<?php
include('library/database.php');

$incident_type_filter = '';
$severity_filter = '';
$affected_assets_filter = [];


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!empty($_POST['incident_type'])) {
        $incident_type_filter = $mysqli->real_escape_string($_POST['incident_type']);
    }

 
    if (!empty($_POST['severity'])) {
        $severity_filter = $mysqli->real_escape_string($_POST['severity']);
    }

   
    if (!empty($_POST['affected_assets']) && is_array($_POST['affected_assets'])) {
        $affected_assets_filter = $_POST['affected_assets'];
    }
}


$query = "SELECT i.incident_ID, i.incident_type_ID, i.severity_ID, i.description 
          FROM incident i 
          LEFT JOIN affected_assets aa ON i.incident_ID = aa.incident_ID 
          WHERE 1=1";


if (!empty($incident_type_filter)) {
    $query .= " AND i.incident_type_ID = (SELECT incident_type_ID FROM incident_type WHERE incident_type = '$incident_type_filter')";
}


if (!empty($severity_filter)) {
    $query .= " AND i.severity_ID = (SELECT severity_ID FROM severity WHERE severity = '$severity_filter')";
}


if (!empty($affected_assets_filter)) {
    $assets_conditions = [];
    foreach ($affected_assets_filter as $asset) {
        $asset_clean = $mysqli->real_escape_string($asset);
        $assets_conditions[] = "aa.asset_ID = (SELECT asset_ID FROM assets WHERE asset_name = '$asset_clean')";
    }
    if (!empty($assets_conditions)) {
        $query .= " AND (" . implode(' OR ', $assets_conditions) . ")";
    }
}


$result = $mysqli->query($query);


if ($result->num_rows > 0) {
   
    while ($row = $result->fetch_assoc()) {
        echo "<div class='incident'>";
        echo "<h3>Incident ID: " . $row['incident_ID'] . "</h3>";
        echo "<p>Incident Type: " . getIncidentTypeName($row['incident_type_ID'], $mysqli) . "</p>";
        echo "<p>Severity: " . getSeverityName($row['severity_ID'], $mysqli) . "</p>";
        echo "<p>Description: " . $row['description'] . "</p>";
        echo "</div>";
    }
} else {
    echo "<p>No incidents found matching your criteria.</p>";
}


function getIncidentTypeName($incident_type_ID, $mysqli) {
    $query = "SELECT incident_type FROM incident_type WHERE incident_type_ID = '$incident_type_ID'";
    $result = $mysqli->query($query);
    $row = $result->fetch_assoc();
    return $row['incident_type'];
}


function getSeverityName($severity_ID, $mysqli) {
    $query = "SELECT severity FROM severity WHERE severity_ID = '$severity_ID'";
    $result = $mysqli->query($query);
    $row = $result->fetch_assoc();
    return $row['severity'];
}
?>

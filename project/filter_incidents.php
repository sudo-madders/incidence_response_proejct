<?php
$incident_type_filter = '';
$severity_filter = '';
$affected_assets_filter = [];
$incidents = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filter'])) {
    $incident_type_filter = $mysqli->real_escape_string($_POST['incident_type']);
	//kolla
    $severity_filter = $mysqli->real_escape_string($_POST['severity']);
	//kolla - edit knapp för varje
    $affected_assets_filter = $_POST['affected_assets'] ?? [];

    $query = "SELECT i.incident_ID, i.incident_type_ID, i.severity_ID, i.description 
              FROM incident i 
              WHERE 1=1";

    if (!empty($incident_type_filter)) {
        $query .= " AND i.incident_type_ID = (SELECT incident_type_ID FROM incident_type WHERE incident_type = '$incident_type_filter')";
    }

    if (!empty($severity_filter)) {
        $query .= " AND i.severity_ID = (SELECT severity_ID FROM severity WHERE severity = '$severity_filter')";
    }

    if (!empty($affected_assets_filter)) {
    $asset_conditions = [];
    foreach ($affected_assets_filter as $asset) {
        $asset_clean = $mysqli->real_escape_string($asset);
        $asset_conditions[] = "EXISTS (
            SELECT 1 FROM affected_assets aa 
            JOIN assets a ON aa.asset_ID = a.asset_ID 
            WHERE aa.incident_ID = i.incident_ID AND a.asset_name = '$asset_clean'
        )";
    }
    if (!empty($asset_conditions)) {
        $query .= " AND (" . implode(' OR ', $asset_conditions) . ")";
    }
}

    $result = $mysqli->query($query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $incidents[] = $row;
        }
    }
}
?>
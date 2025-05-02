<?php
include("library/database.php");
include("library/loging.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$incident_type_filter = '';
$severity_filter = '';
$incidents = [];

// Check if form is submitted for filtering
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filter'])) {
    $incident_type_filter = isset($_POST['incident_type']) ? $mysqli->real_escape_string($_POST['incident_type']) : '';
    $severity_filter = isset($_POST['severity']) ? $mysqli->real_escape_string($_POST['severity']) : '';
	
	$query = "SELECT * FROM incident_report";
	$whereAdded = False;
	// Apply filters to query
	if ($severity_filter != "All") {
		$query .= " WHERE severity = '$severity_filter'";
		$whereAdded = True;
	}
	
	if ($incident_type_filter != "All") {
		if($whereAdded) {
			$query .= " AND incident_type = '$incident_type_filter'";
		} else {
			$query .= " WHERE incident_type = '$incident_type_filter'";
		}
	}
	logError("Executing filtered query: " . $query); // Add this line
    // Run the query for filtered incidents
    $result = $mysqli->query($query);
} else {
    // No filter, load all incidents
    $query = "SELECT * FROM incident_report";

    // Run the query for all incidents
    $result = $mysqli->query($query);
}

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
		$edit = <<<END
		<button type="button" class="btn btn-primary mx-auto" data-bs-toggle="offcanvas" data-bs-target="incident_{$row['incident_ID']}" aria-controls="incident_{$row['incident_ID']}">
							Edit
						</button>
						
						<!-- Offcanvas, More selection -->
						<div class="offcanvas offcanvas-end offcanvas-md offcanvas_width" tabindex="-1" id="incident_{$row['incident_ID']}" aria-labelledby="addNewIncidentLabel">
							<div class="offcanvas-header">
								<h5 class="offcanvas-title" id="addNewIncidentLabel">incident_{$row['incident_ID']}</h5>
								<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
							</div>
							<div class="offcanvas-body ">
								<!-- Här börjar själva panelen -->
								
							</div>
						</div>
END;
		$row["edit"] = $edit;
        $incidents[] = $row;
    }
}

echo json_encode($incidents);
?>

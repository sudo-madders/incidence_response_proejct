<?php
session_name('project');
session_start();

include_once("database.php");
include_once("loging.php");

$incident_type_filter = '';
$severity_filter = '';
$incidents = [];

// Check if form is submitted for filtering
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filter'])) {
    $incident_type_filter = isset($_POST['incident_type']) ? $mysqli->real_escape_string($_POST['incident_type']) : '';
    $severity_filter = isset($_POST['severity']) ? $mysqli->real_escape_string($_POST['severity']) : '';
	$whereAdded = False;
	if ($_SESSION['role'] == "reporter") {
		$user_ID = $_SESSION['username'];
		$query = "SELECT * FROM reporter_view WHERE username = '{$user_ID}'";
		$whereAdded = True;
	} else {
		$query = "SELECT * FROM all_incidents";
	}
	
	// Apply filters to query
	if ($severity_filter != "All" && $whereAdded) {
		$query .= " AND severity = '$severity_filter'";
	} elseif ($severity_filter != "All") {
		$query .= " WHERE severity = '$severity_filter'";
		$whereAdded = True;
	}
	// Apply filters to query
	if ($incident_type_filter != "All" && $whereAdded) {
		$query .= " AND incident_type = '$incident_type_filter'";
	} elseif ($incident_type_filter != "All") {
		if ($whereAdded) {
			$query .= " AND incident_type = '$incident_type_filter'";
		}
		$query .= " WHERE incident_type = '$incident_type_filter'";
	}
    // Run the query for filtered incidents
    $incident_result = $mysqli->query($query);
} else {
    // No filter, load all incidents
    $query = "SELECT * FROM all_incidents";
	
    // Run the query for all incidents
    $incident_result = $mysqli->query($query);
}
logError($query);

if ($incident_result && $incident_result->num_rows > 0) {
	$incidents = [];
	while ($row = $incident_result->fetch_assoc()) {
		$query = "SELECT i_status_ID, u.username, timestamp FROM incident_status i_s 
		JOIN user u ON u.user_ID = i_s.user_ID
		WHERE incident_ID = " . $row['incident_ID'] . " ORDER BY timestamp";
		
		$result = $mysqli->query($query);
		$comments = [];
		$evidences = [];
		if ($result && $result->num_rows > 0) {
			while ($i_status_ID_row = $result->fetch_assoc()) {
				//This section of the code get the comment from a certain incident_status_ID
				$query = "SELECT comment FROM comment WHERE i_status_ID = '{$i_status_ID_row['i_status_ID']}'";
				
				$comment_result = $mysqli->query($query);
				if ($comment_result && $comment_result->num_rows > 0) {
					$comment = [];
					$comment_text = $comment_result->fetch_assoc();
					$comment['text'] = $comment_text['comment'];
					$comment['timestamp'] = $i_status_ID_row['timestamp'];
					$comment['username'] = $i_status_ID_row['username'];
					$comments[] = $comment;
				}
				//This section of the code gets the evidence
				$query = "SELECT path FROM evidence WHERE i_status_ID = '{$i_status_ID_row['i_status_ID']}'";
				$evidence_result = $mysqli->query($query);
				if ($evidence_result && $evidence_result->num_rows > 0) {
					$evidence_data = [];
					$evidence_result = $evidence_result->fetch_assoc();
					$path_array = explode("/", $evidence_result['path']);
					$evidence_data['path'] = end($path_array);
					$evidence_data['timestamp'] = $i_status_ID_row['timestamp'];
					$evidence_data['username'] = $i_status_ID_row['username'];
					$evidences[] = $evidence_data;
				}
			}
		}
		
		// Generate HTML for comments table
		$comments_html = '';
		if (!empty($comments)) {
			$comments_html = '<table class="table table-striped table-bordered">
				<thead class="table-light">
					<tr>
						<th>Timestamp</th>
						<th>User</th>
						<th>Comment</th>
					</tr>
				</thead>
				<tbody>';
			
			foreach ($comments as $comment) {
				$comments_html .= '<tr>
					<td>'.htmlspecialchars($comment['timestamp'] ?? '').'</td>
					<td>'.htmlspecialchars($comment['username'] ?? '').'</td>
					<td>'.htmlspecialchars($comment['text'] ?? '').'</td>
				</tr>';
			}
			
			$comments_html .= '</tbody></table>';
		} else {
			$comments_html = '<p>No comments yet.</p>';
		}
		
		// Generate HTML for evidence table
		$evidence_html = '';
		if (!empty($evidences)) {
			$evidence_html = '<table class="table table-striped table-bordered">
				<thead class="table-light">
					<tr>
						<th>Timestamp</th>
						<th>User</th>
						<th>Path</th>
					</tr>
				</thead>
				<tbody>';
			
			foreach ($evidences as $evidence) {
				$evidence_html .= '<tr>
					<td>'.htmlspecialchars($evidence['timestamp'] ?? '').'</td>
					<td>'.htmlspecialchars($evidence['username'] ?? '').'</td>
					<td><a href="uploads/'.htmlspecialchars($evidence['path'] ?? '').'">'.htmlspecialchars($evidence['path'] ?? '').'</a></td>
				</tr>';
			}
			
			$evidence_html .= '</tbody></table>';
		} else {
			$evidence_html = '<p>No evidence yet.</p>';
		}
		
		$edit = <<<END
		<button type="button" class="btn btn-secondary mx-auto" data-bs-toggle="offcanvas" data-bs-target="#incident_{$row['incident_ID']}" aria-controls="incident_{$row['incident_ID']}">
			Edit
		</button>
		
		<!-- Offcanvas, More selection -->
		<div class="offcanvas offcanvas-end offcanvas-md offcanvas_width" tabindex="-1" id="incident_{$row['incident_ID']}" aria-labelledby="addNewIncidentLabel">
			<div class="offcanvas-header">
				<h5 class="offcanvas-title" id="addNewIncidentLabel">Incident {$row['incident_ID']}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
			</div>
			<div class="offcanvas-body ">
				<!-- Här börjar själva panelen -->
				<div class="row">
					<div class="col">
						<form method="post" action="incident_dashboard.php">
							<div class="row mb-3">
								<div class="col">
									<label for="comment_{$row['incident_ID']}" class="form-label">Comment</label>
									<textarea class="form-control" name="comment" id="comment_{$row['incident_ID']}" rows="3"></textarea>
								</div>
							</div>
							<input type="hidden" name="incident_ID" value="{$row['incident_ID']}">
							<button type="submit" class="btn btn-secondary">Submit</button>
						</form>
					</div>
					<div class="col">
						<form method="post" action="incident_dashboard.php" enctype="multipart/form-data">
							<div class="row mb-3">
								<div class="col">
									<label for="evidence" class="form-label">Upload evidence</label>
									<input class="form-control" type="file" name="evidence" id="evidence required">
								</div>
							</div>
							<input type="hidden" name="incident_ID" value="{$row['incident_ID']}">
							<button type="submit" class="btn btn-secondary">Upload</button>
						</form>
					</div>
				</div>
				<div class="row mt-3">
					<div class="col">
						<h5>Comments:</h5>
						{$comments_html}
					</div>
					<div class="col">
						<h5>Evidence:</h5>
						{$evidence_html}
					</div>
				</div>
			</div>
		</div>
END;

		$select = '<select class="form-select" name="status" id="select_' . $row['incident_ID'] . '">';
		$pending = '';
		$in_progress = '';
		$resolved = '';
		if ($row['status'] == "Pending") {
			$pending = 'selected';
		} elseif ($row['status'] == "Resolved") {
			$resolved = 'selected';
		} else {
			$in_progress = 'selected';
		}
		$select = <<<END
		<select class="form-select" name="status" id="select_{$row['incident_ID']}">
			<option {$pending} value="Pending">Pending</option>
			<option {$in_progress} value="In progress">In progress</option>
			<option {$resolved} value="Resolved">Resolved</option>
		</select>
		END;
		$row['select'] = $select;
		$row["edit"] = $edit;
		$incidents[] = $row;
	}
}
echo json_encode($incidents);
?>

<?php 
require_once("library/database.php");

$ip = $_SERVER['REMOTE_ADDR'];
$browser = $_SERVER['HTTP_USER_AGENT'];
$browsers = ["Firefox", "Chrome", "Edge", "Safari"]; // Browsers Im looking for

$path_parts = explode('/', $_SERVER['SCRIPT_FILENAME']);
$page = end($path_parts);

// Get page_ID
$page_id_query = "SELECT page_ID FROM page WHERE page = '$page'";
$page_id_result = $mysqli->query($page_id_query);

if ($page_id_result && $page_id_result->num_rows > 0) {
	$page_id_row = $page_id_result->fetch_assoc();
	$page_id = $page_id_row['page_ID'];
	// Looping over the $browsers array to see if any of them match
	foreach ($browsers as $b) {
        if (strpos($browser, $b) !== false) {
			// Get browser_ID
			$browser_id_query = "SELECT browser_ID FROM browser WHERE browser = '$b'";
			$browser_id_result = $mysqli->query($browser_id_query);
			
			if ($browser_id_result && $browser_id_result->num_rows > 0) {
				$browser_id_row = $browser_id_result->fetch_assoc();
				$browser_id = $browser_id_row['browser_ID'];
			} else {
				echo 'Error inserting browser: ' . $mysqli->error;
				exit;
			}

			// Insert visit tracking
			$query = "INSERT INTO visit_tracking (page_ID, browser_ID, ip) VALUES ($page_id, $browser_id, '$ip')";
			if ($mysqli->query($query)) {
				// Checks to see if a user is logged in
				if ($_SESSION['user_ID']) {
					// Inserts into logged user
					$visit_ID = $mysqli->insert_id;
					$user_ID = $_SESSION["user_ID"];
					$query = "INSERT INTO logged_user (user_ID, visit_ID) VALUES ({$user_ID}, {$visit_ID})";
					if ($mysqli->query($query)) {
					} else {
						echo 'Error' . $mysqli->error;
					}
				}
			} else {
				echo 'Error: ' . $mysqli->error;
			}
		}
	}

} else {
	echo 'Error: Page not found.';
	exit;
}



?>

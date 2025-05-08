<?php
/*
* This file contains code that will insert each page visit to the database.
* It uses a pre-built library that will extract the browser that makes the
* request.
*/
require_once("database.php");
require_once("loging.php");
require_once("BrowserDetection.php"); // Source: https://github.com/foroco/php-browser-detection

$browserDetection = new foroco\BrowserDetection();
$userAgent = $_SERVER['HTTP_USER_AGENT'];

// Gets browser information
$browserInfo = $browserDetection->getBrowser($userAgent);
$browser = $browserInfo['browser_name'];

// Gets the IP-address
$ip = $_SERVER['REMOTE_ADDR'];

//Gets the current path
$path_parts = explode('/', $_SERVER['SCRIPT_FILENAME']);
$page = end($path_parts);

// Get page_ID
$page_id_query = "SELECT page_ID FROM page WHERE page = '$page'";
$page_id_result = $mysqli->query($page_id_query);

if ($page_id_result && $page_id_result->num_rows > 0) {
	$page_id_row = $page_id_result->fetch_assoc();
	$page_id = $page_id_row['page_ID'];

} else {
	// Page doesn't exist, inserting to the database
	$insert_query = "INSERT INTO page (page) VALUES ('$page')";
	if ($mysqli->query($insert_query)) {
		$page_ID = $mysqli->insert_id; // Get the newly inserted ID
	} else {
		logError($mysqli->error);
		exit;
	}
}

// Get browser_ID
$browser_id_query = "SELECT browser_ID FROM browser WHERE browser = '$browser'";
$browser_id_result = $mysqli->query($browser_id_query);

if ($browser_id_result && $browser_id_result->num_rows > 0) {
	$browser_id_row = $browser_id_result->fetch_assoc();
	$browser_id = $browser_id_row['browser_ID'];
} else {
	// Browser doesn't exist, inserting to the database
	$insert_query = "INSERT INTO browser (browser) VALUES ('$browser')";
	if ($mysqli->query($insert_query)) {
		$browser_id = $mysqli->insert_id; // Get the newly inserted ID
	} else {
		logError($mysqli->error);
		exit;
	}
}

// Insert visit tracking
$query = "INSERT INTO visit_tracking (page_ID, browser_ID, ip) VALUES ($page_id, $browser_id, '$ip')";
if ($mysqli->query($query)) {
	// Checks to see if a user is logged in
	if (isset($_SESSION['user_ID'])) {
		// Inserts into logged user
		$visit_ID = $mysqli->insert_id; // Get the newly inserted ID
		$user_ID = $_SESSION["user_ID"];
		$query = "INSERT INTO logged_user (user_ID, visit_ID) VALUES ({$user_ID}, {$visit_ID})";
		if ($mysqli->query($query)) {
		} else {
			logError($mysqli->error);
		}
	} else {
		logError("User session isnt set");
	}
} else {
	logError($mysqli->error);
}
?>

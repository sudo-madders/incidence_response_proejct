<?php
/*
* Function that will write a string to file.
* Been very useful for debugging 
*/
function logError(string $errorMessage) {
	$timestamp = date('Y-m-d H:i:s');
	$file = 'log.txt';
	$logEntry = "[{$timestamp}] ERROR: {$errorMessage}." . PHP_EOL;
	
	$result = @file_put_contents($file, $logEntry, FILE_APPEND);
	
	return $result !== false;
}
?>
<?php
function logError(string $errorMessage) {
	$timestamp = date('Y-m-d H:i:s');
	$file = 'library/log.txt';
	$logEntry = "[{$timestamp}] ERROR: {$errorMessage} . PHP_EOL";
	
	$result = @file_put_contents($file, $logEntry, FILE_APPEND);
	
	return $result !== false;
}
?>
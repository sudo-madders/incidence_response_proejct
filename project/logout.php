<?php
session_name('project');
session_start();
$_SESSION = array();
session_destroy();
header("Location:index.php");
?>

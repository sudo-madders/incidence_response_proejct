<?php
ob_start();
include("template.php");
$mysqli = new mysqli("localhost", "isacli24", "FV0t2Wgb0b", "isacli24");

$id = intval($_GET['id']);
$mysqli->query("DELETE FROM user WHERE user_id = $id");
header("Location: user_management.php?success=delete");
exit();
ob_end_flush();
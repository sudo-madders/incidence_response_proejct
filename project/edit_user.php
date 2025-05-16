<?php
include("template.php");
$mysqli = new mysqli("localhost", "isacli24", "FV0t2Wgb0b", "isacli24");

$id = intval($_GET['id']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_role = $_POST['role'];
    $stmt = $mysqli->prepare("SELECT user_role_ID FROM user_role WHERE role = ? LIMIT 1");
    $stmt->bind_param("s", $new_role);
    $stmt->execute();
    $res = $stmt->get_result();
    $role = $res->fetch_assoc();
    $role_id = $role['user_role_ID'];

    $update = $mysqli->prepare("UPDATE user SET user_role_ID = ? WHERE user_id = ?");
    $update->bind_param("ii", $role_id, $id);
    $update->execute();

    header("Location: user_management.php?success=edit");
    exit();
}

$user = $mysqli->query("SELECT * FROM user WHERE user_id = $id")->fetch_assoc();
?>

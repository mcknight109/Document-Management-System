<?php
session_start();
include '../../db.php';
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid access");
}

$user_id = intval($_POST['id']);
$username = $_POST['username'];
$role     = $_POST['role'];
$status   = $_POST['status'];

// Permissions
$permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
$permissions_json = json_encode($permissions);

// If password changed
if (!empty($_POST['password'])) {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        UPDATE users SET 
            username = ?, 
            password = ?, 
            role = ?, 
            status = ?, 
            permissions = ?
        WHERE id = ?
    ");
    $stmt->bind_param("sssssi", $username, $password, $role, $status, $permissions_json, $user_id);

} else {
    $stmt = $conn->prepare("
        UPDATE users SET 
            username = ?, 
            role = ?, 
            status = ?, 
            permissions = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ssssi", $username, $role, $status, $permissions_json, $user_id);
}

if ($stmt->execute()) {
    header("Location: ../edit.php?id=$user_id&msg=updated");
    exit();
} else {
    echo "Error updating user: " . $conn->error;
}
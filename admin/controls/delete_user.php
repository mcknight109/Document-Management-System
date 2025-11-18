<?php
session_start();
include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    // Get user data before deleting
    $sql = "SELECT * FROM users WHERE id=$user_id LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Archive user
        $stmt = $conn->prepare("INSERT INTO archived_users (original_user_id, username, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user['id'], $user['username'], $user['password'], $user['role'], $user['status'], $user['created_at']);
        $stmt->execute();
        $stmt->close();

        // Delete from main users
        $conn->query("DELETE FROM users WHERE id=$user_id");
    }
}

header("Location: ../users.php?msg=deleted");
exit();

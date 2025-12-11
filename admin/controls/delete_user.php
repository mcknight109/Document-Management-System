<?php
session_start();
include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    // Get user data before deleting
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Archive user
        $stmt_insert = $conn->prepare("
            INSERT INTO archived_users 
            (original_user_id, first_name, middle_initial, last_name, username, email, password, role, status, permissions, transmittal_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt_insert->bind_param(
            "isssssssssis",
            $user['id'],
            $user['first_name'],
            $user['middle_initial'],
            $user['last_name'],
            $user['username'],
            $user['email'],
            $user['password'],
            $user['role'],
            $user['status'],
            $user['permissions'],
            $user['transmittal_id'],
            $user['created_at']
        );
        $stmt_insert->execute();
        $stmt_insert->close();

        // Delete from main users
        $stmt_delete = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt_delete->bind_param("i", $user_id);
        $stmt_delete->execute();
        $stmt_delete->close();
    }

    $stmt->close();
}

// Redirect with a success flag
header("Location: ../user_manage.php?msg=deleted");
exit();

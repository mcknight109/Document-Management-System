<?php
session_start();
include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archived_id'])) {
    $archived_id = intval($_POST['archived_id']);

    // Get archived user data
    $sql = "SELECT * FROM archived_users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $archived_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Insert back to users table
        $stmt_insert = $conn->prepare("
            INSERT INTO users 
            (first_name, middle_initial, last_name, username, email, password, role, status, permissions, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt_insert->bind_param(
            "ssssssssis",
            $user['first_name'],
            $user['middle_initial'],
            $user['last_name'],
            $user['username'],
            $user['email'],
            $user['password'],
            $user['role'],
            $user['status'],
            $user['permissions'],
            $user['created_at']
        );
        $stmt_insert->execute();
        $stmt_insert->close();

        // Remove from archive
        $stmt_delete = $conn->prepare("DELETE FROM archived_users WHERE id = ?");
        $stmt_delete->bind_param("i", $archived_id);
        $stmt_delete->execute();
        $stmt_delete->close();
    }

    $stmt->close();
}

// Back to archive view
header("Location: ../user_manage.php?view=archive&msg=recovered");
exit;

<?php
include '../../db.php';
session_start();
date_default_timezone_set('Asia/Manila');

if (isset($_POST['update_profile'])) {
    $user_id = $_POST['user_id'];
    $first_name = trim($_POST['first_name']);
    $middle_initial = trim($_POST['middle_initial']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check duplicate email (except own account)
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check->bind_param("si", $email, $user_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['success_message'] = "Email already exists!";
        header("Location: ../profile.php");
        exit;
    }

    if (!empty($password)) {
        // Hash new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = $conn->prepare("
            UPDATE users 
            SET first_name=?, middle_initial=?, last_name=?, username=?, email=?, password=? 
            WHERE id=?
        ");
        $query->bind_param("ssssssi", $first_name, $middle_initial, $last_name, $username, $email, $hashed_password, $user_id);
    } else {
        // Update without password
        $query = $conn->prepare("
            UPDATE users 
            SET first_name=?, middle_initial=?, last_name=?, username=?, email=? 
            WHERE id=?
        ");
        $query->bind_param("sssssi", $first_name, $middle_initial, $last_name, $username, $email, $user_id);
    }

    if ($query->execute()) {
        $_SESSION['success_message'] = "Profile updated successfully!";
        header("Location: ../profile.php");
        exit;
    } else {
        echo "Error updating profile: " . $conn->error;
    }
}
?>

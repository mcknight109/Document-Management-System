<?php
session_start();
include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $first_name     = trim($_POST['first_name']);
    $middle_initial = trim($_POST['middle_initial']);
    $last_name      = trim($_POST['last_name']);
    $email          = trim($_POST['email']);
    $username       = trim($_POST['username']);
    $password       = $_POST['password'];
    $role           = $_POST['role'];
    $status         = $_POST['status'];


    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($password)) {
        $_SESSION['error'] = "All required fields must be filled out.";
        header("Location: ../create.php");
        exit;
    }

    // Get latest transmittal ID
    $getLatest = $conn->query("SELECT transmittal_id FROM users ORDER BY transmittal_id DESC LIMIT 1");

    if ($getLatest->num_rows > 0) {
        $row = $getLatest->fetch_assoc();
        $new_transmittal_id = $row['transmittal_id'] + 1;
    } else {
        // If no users yet, start at 1111
        $new_transmittal_id = 1111;
    }

    $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
    $permissions_json = json_encode($permissions); // Store as JSON in DB

    // Insert user with transmittal_id
    $stmt = $conn->prepare("
    INSERT INTO users 
        (first_name, middle_initial, last_name, username, email, password, role, status, permissions, transmittal_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt->bind_param("sssssssssi",
    $first_name,
    $middle_initial,
    $last_name,
    $username,
    $email,
    $password_hashed,
    $role,
    $status,
    $permissions_json,
    $new_transmittal_id
    );

    if ($stmt->execute()) {
        header("Location: ../create.php?msg=success");
        exit();
    } else {
        $_SESSION['error'] = "Error creating user: " . $conn->error;
        header("Location: ../create.php");
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: ../create.php");
    exit;
}


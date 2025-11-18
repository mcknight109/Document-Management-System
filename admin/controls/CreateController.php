<?php
session_start();
include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form inputs safely
    $first_name     = trim($_POST['first_name']);
    $middle_initial = trim($_POST['middle_initial']);
    $last_name      = trim($_POST['last_name']);
    $username       = trim($_POST['username']);
    $password       = $_POST['password'];
    $role           = $_POST['role'];
    $status         = $_POST['status'];

    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($username) || empty($password) || empty($role) || empty($status)) {
        $_SESSION['error'] = "All required fields must be filled out.";
        header("Location: ../create.php");
        exit;
    }

    // Insert into database using prepared statement
    $stmt = $conn->prepare("INSERT INTO users (first_name, middle_initial, last_name, username, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $first_name, $middle_initial, $last_name, $username, $password, $role, $status);

    if ($stmt->execute()) {
        $_SESSION['success'] = "User account created successfully!";
        header("Location: ../users.php");
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

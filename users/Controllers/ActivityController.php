<?php
session_start();
include '../../db.php';

// Ensure user logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $user_id = $_SESSION['user_id'];
    $control_no = trim($_POST['control_no']);
    $department = trim($_POST['department']);
    $activity_title = trim($_POST['activity_title']);
    $budget = trim($_POST['budget']);
    $date_out = trim($_POST['date_out']);

    if (!empty($control_no) && !empty($department) && !empty($activity_title) && !empty($budget) && !empty($date_out)) {
        $stmt = $conn->prepare("INSERT INTO activity_designs (user_id, control_no, department, activity_title, budget, date_out) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssds", $user_id, $control_no, $department, $activity_title, $budget, $date_out);

        if ($stmt->execute()) {
            $_SESSION['save_success'] = true;
        }
        $stmt->close();
    }
    header("Location: ../activity_design.php");
    exit;
}

// ✅ Delete selected records
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ids'])) {
    $user_id = $_SESSION['user_id'];
    $ids = explode(',', $_POST['delete_ids']);

    // Prepare statement with placeholders
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmt = $conn->prepare("DELETE FROM activity_designs WHERE user_id = ? AND id IN ($placeholders)");

    // Bind parameters dynamically
    $stmt->bind_param('i' . $types, ...array_merge([$user_id], $ids));
    $stmt->execute();
    $stmt->close();
    exit('success'); // respond to AJAX
}
?>

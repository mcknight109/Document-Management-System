<?php
session_start();
include '../../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $user_id = $_SESSION['user_id'];
    $control_no = trim($_POST['control_no']);
    $department = trim($_POST['department']);
    $activity_title = trim($_POST['activity_title']);
    $budget = floatval($_POST['budget']);
    $date_out = trim($_POST['date_out']);

    if ($control_no !== "" && $department !== "" && $activity_title !== "" && $budget > 0 && $date_out !== "") {

        $stmt = $conn->prepare("
            INSERT INTO activity_designs (user_id, control_no, department, activity_title, budget, date_out) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param("isssds", 
            $user_id, 
            $control_no, 
            $department, 
            $activity_title, 
            $budget, 
            $date_out
        );

        if ($stmt->execute()) {
            $_SESSION['save_success'] = true;
        }

        $stmt->close();
    }

    header("Location: ../activity_design.php");
    exit;
}

// Delete selected records
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ids']) && is_array($_POST['delete_ids'])) {
    $user_id = $_SESSION['user_id'];
    $ids = array_map('intval', $_POST['delete_ids']); // sanitize IDs

    if (count($ids) > 0) {
        // Create placeholders for prepared statement
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Prepare statement
        $stmt = $conn->prepare("DELETE FROM activity_designs WHERE user_id = ? AND id IN ($placeholders)");

        // Bind parameters dynamically
        $types = str_repeat('i', count($ids) + 1); // +1 for user_id
        $params = array_merge([$user_id], $ids);

        // PHP 7+ dynamic binding
        $tmp = [];
        foreach ($params as $key => $value) {
            $tmp[$key] = &$params[$key];
        }

        array_unshift($tmp, $types); // add types as first parameter
        call_user_func_array([$stmt, 'bind_param'], $tmp);

        if ($stmt->execute()) {
            $_SESSION['delete_success'] = true;
        } else {
            $_SESSION['delete_error'] = true;
        }

        $stmt->close();
    }

    header("Location: ../activity_design.php");
    exit;
}
?>

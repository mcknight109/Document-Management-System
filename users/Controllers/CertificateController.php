<?php
session_start();
include '../../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ===== DELETE SELECTED RECORDS =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ids'])) {
    $ids = array_map('intval', $_POST['delete_ids']); // sanitize
    if (count($ids) > 0) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids)) . 'i'; // add one more 'i' for user_id

        $stmt = $conn->prepare("DELETE FROM certificate_records WHERE id IN ($placeholders) AND user_id = ?");
        if ($stmt) {
            // Merge $ids and $user_id into a single array
            $params = array_merge($ids, [$user_id]);

            // Build arguments for bind_param
            $bind_names[] = $types;
            foreach ($params as $key => $value) {
                $bind_name = 'bind' . $key;
                $$bind_name = $value;
                $bind_names[] = &$$bind_name; // pass by reference
            }

            // Call bind_param with dynamic arguments
            call_user_func_array([$stmt, 'bind_param'], $bind_names);

            if ($stmt->execute()) {
                $_SESSION['delete_success'] = true;
            } else {
                $_SESSION['delete_error'] = true;
            }
        } else {
            $_SESSION['delete_error'] = true;
        }
    }
    header("Location: ../certificate.php");
    exit;
}

// ===== SAVE NEW RECORD =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $control_number = trim($_POST['control_number']);
    $project = trim($_POST['project']);
    $office = trim($_POST['office']);
    $date_out = !empty($_POST['date_out']) ? $_POST['date_out'] : NULL;
    $claimed_by = trim($_POST['claimed_by']);

    $insert = $conn->prepare("INSERT INTO certificate_records 
        (user_id, control_number, project, office, date_out, claimed_by)
        VALUES (?, ?, ?, ?, ?, ?)");
    $insert->bind_param("isssss", $user_id, $control_number, $project, $office, $date_out, $claimed_by);

    if ($insert->execute()) {
        $_SESSION['save_success'] = true;
    }
    header("Location: ../certificate.php");
    exit;
}

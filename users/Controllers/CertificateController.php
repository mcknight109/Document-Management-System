<?php
session_start();
include '../../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ===== DELETE SELECTED RECORDS =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['delete_ids'][0])) {

    $ids = array_map('intval', $_POST['delete_ids']); // sanitize IDs

    if (count($ids) > 0) {

        // Build placeholders (?, ?, ?, ...)
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Types = one "i" per ID + one "i" for user_id
        $types = str_repeat('i', count($ids)) . 'i';

        // SQL Delete
        $sql = "DELETE FROM certificate_records 
                WHERE id IN ($placeholders) AND user_id = ?";

        $stmt = $conn->prepare($sql);

        if ($stmt) {

            // Build parameters list: all IDs + user_id
            $params = array_merge($ids, [$user_id]);

            // bind_param requires references
            $bind_params = [];
            $bind_params[] = $types;

            foreach ($params as $key => $value) {
                $bind_params[] = &$params[$key];
            }

            // Bind dynamically
            call_user_func_array([$stmt, 'bind_param'], $bind_params);

            // Execute
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

    $control_no = intval($_POST['control_no']);
    $project = trim($_POST['project']);
    $office = trim($_POST['office']);
    $date_out = !empty($_POST['date_out']) ? $_POST['date_out'] : NULL;
    $claimed_by = trim($_POST['claimed_by']);

    $insert = $conn->prepare("INSERT INTO certificate_records 
        (user_id, control_no, project, office, date_out, claimed_by)
        VALUES (?, ?, ?, ?, ?, ?)");
        
    $insert->bind_param("iissss", $user_id, $control_no, $project, $office, $date_out, $claimed_by);

    if ($insert->execute()) {
        $_SESSION['save_success'] = true;
    }

    header("Location: ../certificate.php");
    exit;
}


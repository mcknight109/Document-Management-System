<?php
include '../../db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

// ADD NEW COMMUNICATION RECORD
if (isset($_POST['save']) && empty($_POST['id'])) {
    $user_id = $_SESSION['user_id'];
    $com_id = $_POST['com_id'];
    $date_received = $_POST['date_received'];
    $sender = $_POST['sender'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO communications 
        (user_id, com_id, date_received, sender, description) 
        VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $com_id, $date_received, $sender, $description);

    if ($stmt->execute()) {
        $_SESSION['save_success'] = true;
    }
    header("Location: ../communication.php");
    exit;
}

// UPDATE OUT FORM DETAILS
if (isset($_POST['save_edit']) && !empty($_POST['id'])) {
    $id = $_POST['id'];
    $indorse_to = $_POST['indorse_to'];
    $date_routed = $_POST['date_routed'];
    $routed_by = $_POST['routed_by']; // added
    $action_taken = $_POST['action'];
    $remarks = $_POST['remarks'];

    $stmt = $conn->prepare("UPDATE communications 
        SET indorse_to = ?, date_routed = ?, routed_by = ?, action_taken = ?, remarks = ? 
        WHERE id = ?");
    $stmt->bind_param("sssssi", $indorse_to, $date_routed, $routed_by, $action_taken, $remarks, $id);

    if ($stmt->execute()) {
        $_SESSION['edit_success'] = true;
    }
    header("Location: ../communication.php");
    exit;
}

// DELETE SELECTED RECORDS
if (isset($_POST['delete']) && isset($_POST['delete_ids']) && is_array($_POST['delete_ids'])) {
    $ids = array_map('intval', $_POST['delete_ids']);

    if (count($ids) > 0) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));

        $stmt = $conn->prepare("DELETE FROM communications WHERE id IN ($placeholders)");
        $stmt->bind_param($types, ...$ids);

        if ($stmt->execute()) {
            $_SESSION['delete_success'] = true;
        } else {
            $_SESSION['delete_error'] = true;
        }
    } else {
        $_SESSION['delete_error'] = true;
    }

    header("Location: ../communication.php");
    exit;
}
?>

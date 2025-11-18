<?php
session_start();
include "../../db.php";
date_default_timezone_set('Asia/Manila');

// --- Delete Multiple Records ---
if (isset($_POST['delete_ids'])) {
    $ids = json_decode($_POST['delete_ids'], true);
    if (empty($ids)) {
        echo "No records selected.";
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $stmt = $conn->prepare("DELETE FROM documents WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();

    echo $stmt->affected_rows . " record(s) deleted.";
    exit;
}

// --- Mark as Date Out 2 ---
if (isset($_GET['action']) && $_GET['action'] === 'dateout2' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $dateOut2 = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("UPDATE documents SET date_out_2 = ? WHERE id = ?");
    $stmt->bind_param("si", $dateOut2, $id);
    $stmt->execute();

    echo "Updated successfully.";
    exit;
}

echo "No valid action provided.";

// --- Save Check Record ---
if (isset($_GET['action']) && $_GET['action'] === 'checkrecord') {
    $docId = intval($_POST['docId']);
    $fundType = trim($_POST['fundType']);
    $bankChannel = trim($_POST['bankChannel']);
    $checkDate = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("UPDATE documents SET fund_type=?, bank_channel=?, check_date=? WHERE id=?");
    $stmt->bind_param("sssi", $fundType, $bankChannel, $checkDate, $docId);
    $stmt->execute();
    echo "Record checked successfully.";
    exit;
}
?>

<?php
session_start();
include "../../db.php";
date_default_timezone_set('Asia/Manila');

// --- GET DOCUMENT INFO ---
if (isset($_GET['action']) && $_GET['action'] === 'getdoc' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("
        SELECT id, control_no, payee, bank_channel, 
               DATE_FORMAT(date_in, '%M %d, %Y %h:%i %p') AS date_in_formatted 
        FROM documents 
        WHERE id=?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode($stmt->get_result()->fetch_assoc());
    exit;
}

// --- SAVE CHECK RECORD ---
if (isset($_POST['action']) && $_POST['action'] === 'save_check') {
    $docId = intval($_POST['docId']);
    $checkNumber = $_POST['checkNumber'];
    $checkDate = !empty($_POST['checkDate']) ? $_POST['checkDate'] : NULL;
    $bankChannel = $_POST['bankChannel'];

    $stmt = $conn->prepare("
        UPDATE documents 
        SET check_number = ?, check_date = ?, bank_channel = ? 
        WHERE id = ?
    ");
    $stmt->bind_param("sssi", $checkNumber, $checkDate, $bankChannel, $docId);
    echo ($stmt->execute()) ? "success" : "error";
    exit;
}

// --- CHECK OUT ---
if (isset($_GET['action']) && $_GET['action'] === 'check_out' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Check if status already exists
    $stmt = $conn->prepare("SELECT status FROM documents WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $statusResult = $stmt->get_result()->fetch_assoc();

    if (!empty($statusResult['status'])) {
        echo "exists"; // status already exists
        exit;
    }

    $dateOut = date("Y-m-d H:i:s");
    $stmt = $conn->prepare("UPDATE documents SET date_out = ?, status = 'Check Out' WHERE id = ?");
    $stmt->bind_param("si", $dateOut, $id);
    echo ($stmt->execute()) ? "success" : "error";
    exit;
}

// --- CHECK RELEASE ---
if (isset($_GET['action']) && $_GET['action'] === 'check_release' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Check if status already exists
    $stmt = $conn->prepare("SELECT status FROM documents WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $statusResult = $stmt->get_result()->fetch_assoc();

    if (!empty($statusResult['status'])) {
        echo "exists"; // status already exists
        exit;
    }

    $dateRelease = date("Y-m-d H:i:s");
    $stmt = $conn->prepare("UPDATE documents SET date_out = ?, status = 'Check Release' WHERE id = ?");
    $stmt->bind_param("si", $dateRelease, $id);
    echo ($stmt->execute()) ? "success" : "error";
    exit;
}

// --- DEFAULT ---
echo "No valid action provided.";
exit;
?>

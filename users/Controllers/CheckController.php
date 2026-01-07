<?php
session_start();
include "../../db.php";
date_default_timezone_set('Asia/Manila');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    echo "User not logged in";
    exit;
}

// Fetch user's full name
$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT first_name, middle_initial, last_name FROM users WHERE id = $user_id LIMIT 1");
$user = $result->fetch_assoc();
$full_name = trim(
    ($user['first_name'] ?? '') . ' ' .
    (!empty($user['middle_initial']) ? strtoupper(substr($user['middle_initial'],0,1)) . '. ' : '') .
    ($user['last_name'] ?? '')
);

// Activity logging function
function logUserActivity($conn, $user_id, $full_name, $action, $module, $reference_id = null, $reference_no = null, $description = null) {
    $stmt = $conn->prepare("
        INSERT INTO user_activity_logs
        (user_id, full_name, action, module, reference_id, reference_no, description)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("isssiss", $user_id, $full_name, $action, $module, $reference_id, $reference_no, $description);
    $stmt->execute();
    $stmt->close();
}

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

// --- GET CHECK PRINT DATA ---
if (isset($_GET['action']) && $_GET['action'] === 'get_check_print' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("
        SELECT 
            control_no,
            check_no,
            payee,
            description,
            amount,
            fund_type,
            bank_channel,
            check_date,
            date_out,
            status
        FROM documents
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode($stmt->get_result()->fetch_assoc());
    exit;
}

// --- SAVE CHECK RECORD ---
if (isset($_POST['action']) && $_POST['action'] === 'save_check') {
    $docId = intval($_POST['docId']);
    $checkNo = trim($_POST['checkNo'] ?? '');
    $checkDate = !empty($_POST['checkDate']) ? $_POST['checkDate'] : NULL;
    $bankChannel = $_POST['bankChannel'];

    // If empty, force 8 zeros
    if ($checkNo === '') {
        $checkNo = '00000000';
    }

    // Must be exactly 8 digits
    if (!preg_match('/^\d{8}$/', $checkNo)) {
        echo "invalid_check_no";
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE documents 
        SET check_no = ?, check_date = ?, bank_channel = ? 
        WHERE id = ?
    ");
    $stmt->bind_param("sssi", $checkNo, $checkDate, $bankChannel, $docId);

    if ($stmt->execute()) {
        // Log activity
        logUserActivity(
            $conn,
            $user_id,
            $full_name,
            "Updated Check Record",
            "Check Document Records",
            $docId,
            $checkNo,
            "Check record updated, Document ID"
        );
        echo "success";
    } else {
        echo "error";
    }
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

if (isset($input['action']) && $input['action'] === 'delete') {
    $ids = $input['ids'] ?? [];
    if (empty($ids)) {
        echo json_encode(["success" => false, "error" => "No IDs provided"]);
        exit;
    }

    $idList = implode(",", array_map('intval', $ids));

    // Fetch records for logging before deletion
    $res = $conn->query("SELECT * FROM documents WHERE id IN ($idList)");
    $documents = [];
    while ($row = $res->fetch_assoc()) {
        $documents[] = $row;
    }

    if ($conn->query("DELETE FROM documents WHERE id IN ($idList)")) {
        foreach ($documents as $doc) {
            logUserActivity(
                $conn,
                $user_id,
                $full_name,
                "Deleted Voucher Record",
                "Document Voucher Records",
                $doc['id'],
                $doc['control_no'],
                "Deleted Voucher Record, Control No: " . $doc['control_no']
            );
        }
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $conn->error]);
    }
    exit;
}

// --- CHECK OUT ---
if (isset($_GET['action']) && $_GET['action'] === 'check_out' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT status, control_no FROM documents WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if (!empty($result['status'])) { echo "exists"; exit; }

    $dateOut = date("Y-m-d H:i:s");
    $stmt = $conn->prepare("UPDATE documents SET date_out = ?, status = 'Check Out' WHERE id = ?");
    $stmt->bind_param("si", $dateOut, $id);

    if ($stmt->execute()) {
        logUserActivity(
            $conn,
            $user_id,
            $full_name,
            "Checked Out Document",
            "Check Document Records",
            $id,
            $result['control_no'],
            "Document marked as checked out, Check Num"
        );
        echo "success";
    } else {
        echo "error";
    }
    exit;
}

// --- CHECK RELEASE ---
if (isset($_GET['action']) && $_GET['action'] === 'check_release' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT status, control_no FROM documents WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $statusResult = $stmt->get_result()->fetch_assoc();

    if (!empty($statusResult['status'])) {
        echo "exists";
        exit;
    }

    $dateRelease = date("Y-m-d H:i:s");
    $stmt = $conn->prepare("UPDATE documents SET date_out = ?, status = 'Check Release' WHERE id = ?");
    $stmt->bind_param("si", $dateRelease, $id);

    if ($stmt->execute()) {
        logUserActivity(
            $conn,
            $user_id,
            $full_name,
            "Released Document",
            "Check Document Records",
            $id,
            $statusResult['control_no'],
            "Document marked as check released, Check Num"
        );
        echo "success";
    } else {
        echo "error";
    }
    exit;
}

echo "No valid action provided.";
exit;
?>

<?php
session_start();
include "../../db.php";
date_default_timezone_set('Asia/Manila');
header('Content-Type: application/json');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "User not logged in"]);
    exit;
}

// Get user ID and full name
$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT first_name, middle_initial, last_name FROM users WHERE id = $user_id LIMIT 1");
$user = $result->fetch_assoc();
$full_name = trim($user['first_name'] . ' ' . (!empty($user['middle_initial']) ? strtoupper(substr($user['middle_initial'],0,1)) . '. ' : '') . $user['last_name']);

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

// Decode incoming JSON
$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'] ?? null;
$response = ["success" => false];

// ===== HANDLE ACTIONS =====
switch ($action) {

    // SAVE new voucher
    case "save":
        $control = $data['control_no'] ?? null;
        $payee = $data['payee'] ?? null;
        $desc = $data['description'] ?? null;
        $fundType = $data['fund_type'] ?? null;
        $amount = $data['amount'] ?? null;

        if (!$control || !$payee || !$desc || !$amount || !$fundType) {
            echo json_encode(["success" => false, "error" => "Missing required fields"]);
            exit;
        }

        $dateIn = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("
            INSERT INTO documents (user_id, control_no, payee, description, fund_type, amount, date_in)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issssds", $user_id, $control, $payee, $desc, $fundType, $amount, $dateIn);

        if ($stmt->execute()) {
            // Log activity
            logUserActivity(
                $conn,
                $user_id,
                $full_name,
                "Added Voucher Record",
                "Document Voucher Records",
                $conn->insert_id,
                $control,
                "Document Voucher record added, Control No" . $doc['control_no']
            );
            $response = ["success" => true];
        } else {
            $response = ["success" => false, "error" => $stmt->error];
        }
        $stmt->close();
        break;

    // MARK AS DATE OUT
    case "mark_out":
        $ids = $data['ids'] ?? [];
        if (empty($ids)) {
            echo json_encode(["success" => false, "error" => "No IDs provided"]);
            exit;
        }

        $idList = implode(",", array_map('intval', $ids));
        $now = date('Y-m-d H:i:s');

        // Fetch documents before updating
        $res = $conn->query("SELECT * FROM documents WHERE id IN ($idList)");
        $documents = [];
        while ($row = $res->fetch_assoc()) {
            $documents[] = $row;
        }

        // Update Date Out
        if ($conn->query("UPDATE documents SET date_out = '$now' WHERE id IN ($idList)")) {
            foreach ($documents as $doc) {
                logUserActivity(
                    $conn,
                    $user_id,
                    $full_name,
                    "Marked Date Out",
                    "Document Voucher Records",
                    $doc['id'],
                    $doc['control_no'],
                    "Marked as Date Out, Control No: " . $doc['control_no']
                );
            }
            $response = ["success" => true, "message" => "Marked as Date Out"];
        } else {
            $response = ["success" => false, "error" => $conn->error];
        }
        break;

    default:
        $response = ["success" => false, "error" => "Invalid action"];
        break;
}

$conn->close();
echo json_encode($response);
?>

<?php
session_start();
include "../../db.php";
date_default_timezone_set('Asia/Manila');
header('Content-Type: application/json');

// Decode incoming JSON
$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'] ?? null;

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "User not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$response = ["success" => false];

// Main action handler
switch ($action) {

    // SAVE new voucher record
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

        $dateIn = date('Y-m-d H:i:s'); // Automatically set Date In

        $sql = "INSERT INTO documents (user_id, control_no, payee, description, fund_type, amount, date_in)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssds", $user_id, $control, $payee, $desc, $fundType, $amount, $dateIn);

        if ($stmt->execute()) {
            $response = ["success" => true];
        } else {
            $response = ["success" => false, "error" => $stmt->error];
        }
    break;

    // Mark as Date Out
    case "mark_out":
        $ids = $data['ids'] ?? [];
        if (empty($ids)) {
            echo json_encode(["success" => false, "error" => "No IDs provided"]);
            exit;
        }

        $idList = implode(",", array_map('intval', $ids));
        $now = date('Y-m-d H:i:s');
        $sql = "UPDATE documents SET date_out = '$now' WHERE id IN ($idList)";
        if ($conn->query($sql)) {
            $response = ["success" => true, "message" => "Marked as Date Out"];
        } else {
            $response = ["success" => false, "error" => $conn->error];
        }
        break;

    // 🗑️ Delete selected records
    case "delete":
        $ids = $data['ids'] ?? [];
        if (empty($ids)) {
            echo json_encode(["success" => false, "error" => "No IDs provided"]);
            exit;
        }

        $idList = implode(",", array_map('intval', $ids));
        $sql = "DELETE FROM documents WHERE id IN ($idList)";
        if ($conn->query($sql)) {
            $response = ["success" => true, "message" => "Records deleted"];
        } else {
            $response = ["success" => false, "error" => $conn->error];
        }
        break;

    default:
        $response = ["success" => false, "error" => "Invalid action"];
}

$conn->close();
echo json_encode($response);
?>

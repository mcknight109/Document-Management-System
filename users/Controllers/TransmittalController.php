<?php
session_start();
include "../../db.php";
date_default_timezone_set('Asia/Manila');

header("Content-Type: application/json");

// Validate request
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['ids']) || empty($data['ids'])) {
    echo json_encode(["success" => false, "error" => "No IDs provided"]);
    exit;
}

// Generate new 5-digit Transmittal ID
$newTrans = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

// Update all selected rows
$ids = $data['ids'];
$placeholders = implode(",", array_fill(0, count($ids), "?"));
$types = str_repeat("i", count($ids));

$sql = "UPDATE documents SET transmittal_id = ? WHERE id IN ($placeholders)";
$stmt = $conn->prepare($sql);

$params = array_merge([$newTrans], $ids);
$stmt->bind_param("s$types", ...$params);
$stmt->execute();

echo json_encode([
    "success" => true,
    "transmittal_id" => $newTrans
]);

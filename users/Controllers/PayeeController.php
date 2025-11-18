<?php
session_start();
include '../../db.php';

$action = $_GET['action'] ?? '';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($action == "search") {
    $query = $_GET['query'] ?? '';
    $stmt = $conn->prepare("
        SELECT p.*, u.first_name AS added_by_name, u.middle_initial AS added_by_mid, u.last_name AS added_by_last
        FROM payees p
        JOIN users u ON p.added_by = u.id
        WHERE p.first_name LIKE ? OR p.middle_initial LIKE ? OR p.last_name LIKE ?
        ORDER BY p.created_at DESC
    ");
    $like = "%$query%";
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
    $payees = [];
    while ($row = $result->fetch_assoc()) {
        $row['added_by_name'] = trim($row['added_by_name'] . ' ' . ($row['added_by_mid'] ? strtoupper($row['added_by_mid']) . '. ' : '') . $row['added_by_last']);
        $payees[] = $row;
    }
    echo json_encode($payees);
    exit;
}

if ($action == "add") {
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_initial = trim($_POST['middle_initial'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');

    if ($first_name == '' || $last_name == '') {
        echo json_encode(['success' => false, 'message' => 'First and last name are required.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO payees (first_name, middle_initial, last_name, added_by) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $first_name, $middle_initial, $last_name, $user_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add payee.']);
    }
    exit;
}
?>

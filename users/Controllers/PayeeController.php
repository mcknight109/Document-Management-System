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
        SELECT d.payee, u.first_name AS added_by_name, u.middle_initial AS added_by_mid, u.last_name AS added_by_last
        FROM documents d
        JOIN users u ON d.user_id = u.id
        WHERE d.payee LIKE ?
        ORDER BY d.date_in DESC
        LIMIT 50
    ");

    $like = "%$query%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
    $payees = [];

    while ($row = $result->fetch_assoc()) {
        $row['added_by_name'] = trim(
            $row['added_by_name'] . ' ' .
            ($row['added_by_mid'] ? strtoupper($row['added_by_mid']) . '. ' : '') .
            $row['added_by_last']
        );
        $payees[] = $row;
    }

    echo json_encode($payees);
    exit;
}
?>

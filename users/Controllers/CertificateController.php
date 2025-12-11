<?php
session_start();
include '../../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

function logUserActivity($conn, $user_id, $full_name, $action, $module, $reference_id = null, $reference_no = null, $description = null)
{
    $stmt = $conn->prepare("
        INSERT INTO user_activity_logs 
        (user_id, full_name, action, module, reference_id, reference_no, description) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("isssiss", $user_id, $full_name, $action, $module, $reference_id, $reference_no, $description);
    $stmt->execute();
    $stmt->close();
}

$user_id = $_SESSION['user_id'];

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

        // Construct full name
        $result = $conn->query("SELECT first_name, middle_initial, last_name FROM users WHERE id = $user_id LIMIT 1");
        $user = $result->fetch_assoc();
        $full_name = trim($user['first_name'] . ' ' . ($user['middle_initial'] ?? '') . ' ' . $user['last_name']);

        // Log activity
        logUserActivity(
            $conn,
            $user_id,
            $full_name,
            "Added Certificate Record",
            "Certificate Records",
            $conn->insert_id,
            $control_no,
            "New Certificate record added, Control No: $control_no."
        );
    }

    header("Location: ../certificate.php");
    exit;
}

<?php
session_start();
include '../../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $user_id = $_SESSION['user_id'];
    $control_no = trim($_POST['control_no']);
    $department = trim($_POST['department']);
    $activity_title = trim($_POST['activity_title']);
    $budget = floatval($_POST['budget']);
    $date_out = trim($_POST['date_out']);

    if ($control_no !== "" && $department !== "" && $activity_title !== "" && $budget > 0 && $date_out !== "") {

        $stmt = $conn->prepare("
            INSERT INTO activity_designs (user_id, control_no, department, activity_title, budget, date_out) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param("isssds", 
            $user_id, 
            $control_no, 
            $department, 
            $activity_title, 
            $budget, 
            $date_out
        );

        if ($stmt->execute()) {
            $_SESSION['save_success'] = true;

            $user_id = $_SESSION['user_id'];
            $result = $conn->query("SELECT first_name, middle_initial, last_name FROM users WHERE id = $user_id LIMIT 1");
            $user = $result->fetch_assoc();
            $full_name = trim($user['first_name'] . ' ' . ($user['middle_initial'] ?? '') . ' ' . $user['last_name']);

            // Log activity
            logUserActivity(
                $conn,
                $user_id,
                $full_name,
                "Added Activity Record",
                "Activity Records",
                $conn->insert_id,
                $control_no,
                "New Activity record added, Control No: $control_no. "
            );
        }

        $stmt->close();
    }

    header("Location: ../activity_design.php");
    exit;
}
?>

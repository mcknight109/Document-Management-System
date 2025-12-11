<?php
include '../../db.php';
session_start();

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

// ADD IN FORM DETAILS 
if (isset($_POST['save']) && empty($_POST['id'])) {
    $user_id = $_SESSION['user_id'];
    $com_id = $_POST['com_id'];
    $date_received = $_POST['date_received'];
    $sender = $_POST['sender'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO communications 
        (user_id, com_id, date_received, sender, description) 
        VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $com_id, $date_received, $sender, $description);

    if ($stmt->execute()) {
        $_SESSION['save_success'] = true;

        $user_id = $_SESSION['user_id'];
        $result = $conn->query("SELECT first_name, middle_initial, last_name FROM users WHERE id = $user_id LIMIT 1");
        $user = $result->fetch_assoc();
        $full_name = trim($user['first_name'] . ' ' . ($user['middle_initial'] ?? '') . ' ' . $user['last_name']);

        logUserActivity(
            $conn,
            $user_id,
            $full_name,
            "Added Communication Record",
            "Communication Records",
            $conn->insert_id,
            $com_id,
            "New communication record added, Communication ID: $com_id."
        );
    }
    header("Location: ../communication.php");
    exit;
}

// UPDATE OUT FORM DETAILS
if (isset($_POST['save_edit']) && !empty($_POST['id'])) {
    $id = $_POST['id'];
    $indorse_to = $_POST['indorse_to'];
    $date_routed = $_POST['date_routed'];
    $routed_by = $_POST['routed_by']; // added
    $action_taken = $_POST['action'];
    $remarks = $_POST['remarks'];

    $stmt = $conn->prepare("UPDATE communications 
        SET indorse_to = ?, date_routed = ?, routed_by = ?, action_taken = ?, remarks = ? 
        WHERE id = ?");
    $stmt->bind_param("sssssi", $indorse_to, $date_routed, $routed_by, $action_taken, $remarks, $id);

    if ($stmt->execute()) {
        $_SESSION['edit_success'] = true;

        // --- Log user activity ---
        $user_id = $_SESSION['user_id'];
        $result = $conn->query("SELECT first_name, middle_initial, last_name, com_id FROM users u JOIN communications c ON c.id = $id WHERE u.id = $user_id LIMIT 1");
        $user = $result->fetch_assoc();
        $full_name = trim(
            ($user['first_name'] ?? '') . ' ' .
            (!empty($user['middle_initial']) ? strtoupper(substr($user['middle_initial'], 0, 1)) . '. ' : '') .
            ($user['last_name'] ?? '')
        );

        logUserActivity(
            $conn,
            $user_id,
            $full_name,
            "Updated Out Form Details",
            "Communication Records",
            $id,
            $user['com_id'] ?? null,
            "Out form details updated, Communication ID: $com_id"
        );
    }

    header("Location: ../communication.php");
    exit;
}
?>

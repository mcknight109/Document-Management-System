<?php
include '../../db.php';
session_start();
date_default_timezone_set('Asia/Manila');

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

if (isset($_POST['save']) && empty($_POST['id'])) {
    $user_id = $_SESSION['user_id'];
    $com_id = $_POST['com_id'];
    $date_received = $_POST['date_received'];
    $sender = $_POST['sender'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("
        INSERT INTO communications 
        (user_id, com_id, date_received, sender, description) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issss", $user_id, $com_id, $date_received, $sender, $description);

    if ($stmt->execute()) {
        $_SESSION['save_success'] = true;

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

if (isset($_POST['save_edit']) && !empty($_POST['id'])) {
    $id = $_POST['id'];
    $indorse_to = $_POST['indorse_to'];
    $date_routed = $_POST['date_routed'];
    $routed_by = $_POST['routed_by'];
    $action_taken = $_POST['action'];
    $remarks = $_POST['remarks'];
    
    // Check if this is coming from Show Indorsed mode
    $is_from_indorsed = isset($_POST['is_from_indorsed']) && $_POST['is_from_indorsed'] == '1';
    
    // Prepare base SQL
    $sql = "UPDATE communications 
            SET indorse_to = ?,
                date_routed = ?,
                routed_by = ?,
                action_taken = ?,
                remarks = ?";
    
    // Calculate duration only when action is filled from Show Indorsed mode
    if ($is_from_indorsed && !empty($action_taken) && !empty($date_routed)) {
        // Get current timestamp (when action is taken)
        $action_time = date('Y-m-d H:i:s');
        
        // Calculate time difference between date_routed and now
        $date_routed_obj = new DateTime($date_routed . ' 00:00:00'); // Add time if only date provided
        $action_time_obj = new DateTime($action_time);
        $interval = $date_routed_obj->diff($action_time_obj);
        
        // Format the duration
        $duration_parts = [];
        if ($interval->y > 0) $duration_parts[] = $interval->y . ' year' . ($interval->y > 1 ? 's' : '');
        if ($interval->m > 0) $duration_parts[] = $interval->m . ' month' . ($interval->m > 1 ? 's' : '');
        if ($interval->d > 0) $duration_parts[] = $interval->d . ' day' . ($interval->d > 1 ? 's' : '');
        if ($interval->h > 0) $duration_parts[] = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '');
        if ($interval->i > 0) $duration_parts[] = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '');
        if ($interval->s > 0 && empty($duration_parts)) $duration_parts[] = $interval->s . ' second' . ($interval->s > 1 ? 's' : '');
        
        $duration = implode(' ', $duration_parts);
        if (empty($duration)) {
            $duration = '0 minutes';
        }
        
        $sql .= ", action_duration = ?";
    }
    
    $sql .= " WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    
    if ($is_from_indorsed && !empty($action_taken) && !empty($date_routed)) {
        $stmt->bind_param("ssssssi", $indorse_to, $date_routed, $routed_by, $action_taken, $remarks, $duration, $id);
    } else {
        $stmt->bind_param("sssssi", $indorse_to, $date_routed, $routed_by, $action_taken, $remarks, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['edit_success'] = true;

        $user_id = $_SESSION['user_id'];
        $result = $conn->query("
            SELECT u.first_name, u.middle_initial, u.last_name, c.com_id
            FROM users u
            JOIN communications c ON c.id = $id
            WHERE u.id = $user_id
            LIMIT 1
        ");
        $user = $result->fetch_assoc();

        $full_name = trim(
            ($user['first_name'] ?? '') . ' ' .
            (!empty($user['middle_initial']) ? strtoupper(substr($user['middle_initial'], 0, 1)) . '. ' : '') .
            ($user['last_name'] ?? '')
        );

        // Log appropriate activity
        if ($is_from_indorsed && !empty($action_taken)) {
            $action_desc = "Took action on indorsed record";
            $action_details = "Action taken on indorsed record, duration: " . ($duration ?? 'N/A');
        } else {
            $action_desc = "Updated Out Form details";
            $action_details = "Out form details updated";
        }
        
        logUserActivity(
            $conn,
            $user_id,
            $full_name,
            $action_desc,
            "Communication Records",
            $id,
            $user['com_id'] ?? null,
            $action_details . ", ComID: " . ($user['com_id'] ?? '')
        );
    }

    header("Location: ../communication.php");
    exit;
}

if (isset($_POST['delete_selected']) && !empty($_POST['delete_ids'])) {
    $ids = $_POST['delete_ids'];
    $user_id = $_SESSION['user_id'];

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $stmt = $conn->prepare("SELECT com_id FROM communications WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $res = $stmt->get_result();

    $comIds = [];
    while ($row = $res->fetch_assoc()) {
        $comIds[] = $row['com_id'];
    }
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM communications WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);

    if ($stmt->execute()) {
        $_SESSION['delete_success'] = true;

        $result = $conn->query("SELECT first_name, middle_initial, last_name FROM users WHERE id = $user_id LIMIT 1");
        $user = $result->fetch_assoc();
        $full_name = trim(
            ($user['first_name'] ?? '') . ' ' .
            (!empty($user['middle_initial']) ? strtoupper(substr($user['middle_initial'],0,1)) . '. ' : '') .
            ($user['last_name'] ?? '')
        );

        foreach ($ids as $index => $id) {
            logUserActivity(
                $conn,
                $user_id,
                $full_name,
                "Deleted Communication Record",
                "Communication Records",
                $id,
                $comIds[$index] ?? null,
                "Deleted communication record, ComID: " . ($comIds[$index] ?? 'N/A')
            );
        }
    } else {
        $_SESSION['delete_error'] = true;
    }

    $stmt->close();
    header("Location: ../communication.php");
    exit;
}

if (isset($_GET['fetch_my_indorse'])) {
    $user_id = $_SESSION['user_id'];

    $stmtUser = $conn->prepare("SELECT first_name, middle_initial, last_name FROM users WHERE id=?");
    $stmtUser->bind_param("i", $user_id);
    $stmtUser->execute();
    $u = $stmtUser->get_result()->fetch_assoc();

    $full_name = trim(
        ($u['first_name'] ?? '') . ' ' .
        (!empty($u['middle_initial']) ? strtoupper(substr($u['middle_initial'], 0, 1)) . '. ' : '') .
        ($u['last_name'] ?? '')
    );

    $stmt = $conn->prepare("
        SELECT 
            id,
            com_id,
            date_received,
            sender,
            description,
            indorse_to,
            date_routed,
            routed_by,
            action_taken,
            remarks,
            action_duration,
            CASE
                WHEN date_routed IS NOT NULL AND action_duration IS NOT NULL
                THEN CONCAT(TIMESTAMPDIFF(HOUR, date_routed, action_duration), ' hrs')
                ELSE '-'
            END AS action_time
        FROM communications
        WHERE indorse_to = ?
        ORDER BY date_routed DESC
    ");
    $stmt->bind_param("s", $full_name);
    $stmt->execute();

    $data = [];
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);
    exit;
}
?>

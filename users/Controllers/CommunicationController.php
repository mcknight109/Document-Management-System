<?php
include '../../db.php';
session_start();
date_default_timezone_set('Asia/Manila');

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
    $date_routed = $_POST['date_routed'] ?? null;
    $routed_by = $_POST['routed_by'];
    $action_taken = $_POST['action'];
    $remarks = $_POST['remarks'];

    // Check if this is coming from Show Indorsed mode
    $is_from_indorsed = isset($_POST['is_from_indorsed']) && $_POST['is_from_indorsed'] == '1';

    // Prepare base SQL - different for indorsed mode vs normal mode
    if ($is_from_indorsed) {
        // In Show Indorsed mode: Update action_taken, remarks, and SET indorsed_action_at timestamp
        // Also calculate duration between date_routed and now

        $current_time = date('Y-m-d H:i:s');
        $action_duration = null;

        // Get the date_routed value from database to calculate duration
        $getDateStmt = $conn->prepare("SELECT date_routed FROM communications WHERE id = ?");
        $getDateStmt->bind_param("i", $id);
        $getDateStmt->execute();
        $getDateStmt->bind_result($db_date_routed);
        $getDateStmt->fetch();
        $getDateStmt->close();

        // Calculate duration if date_routed exists
        if ($db_date_routed) {
            $date_routed_obj = new DateTime($db_date_routed);
            $action_time_obj = new DateTime($current_time);
            $interval = $date_routed_obj->diff($action_time_obj);

            // Format duration in a human-readable way
            $duration_parts = [];

            if ($interval->y > 0) {
                $duration_parts[] = $interval->y . ' year' . ($interval->y > 1 ? 's' : '');
            }
            if ($interval->m > 0) {
                $duration_parts[] = $interval->m . ' month' . ($interval->m > 1 ? 's' : '');
            }
            if ($interval->d > 0) {
                $duration_parts[] = $interval->d . ' day' . ($interval->d > 1 ? 's' : '');
            }
            if ($interval->h > 0) {
                $duration_parts[] = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '');
            }
            if ($interval->i > 0) {
                $duration_parts[] = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '');
            }
            if ($interval->s > 0 && empty($duration_parts)) {
                $duration_parts[] = $interval->s . ' second' . ($interval->s > 1 ? 's' : '');
            }

            $action_duration = implode(', ', $duration_parts);

            if (empty($action_duration)) {
                $action_duration = '0 seconds';
            }

            // Add "ago" at the end for better readability
            $action_duration .= ' ago';
        }

        $sql = "UPDATE communications 
                SET routed_by = ?,
                    action_taken = ?,
                    remarks = ?,
                    indorsed_action_at = ?,
                    action_duration = ?";
    } else {
        // In normal mode: Update all fields including date_routed
        // If date_routed is provided in form, add current time to it
        if (!empty($date_routed)) {
            // If it's just a date (YYYY-MM-DD), add current time
            if (strlen($date_routed) == 10) {
                $date_routed .= ' ' . date('H:i:s');
            }
            // If it's datetime-local format (YYYY-MM-DDTHH:MM), convert to MySQL format
            else if (strpos($date_routed, 'T') !== false) {
                $date_routed = str_replace('T', ' ', $date_routed) . ':00';
            }
        } else {
            // If no date_routed provided, set to current timestamp
            $date_routed = date('Y-m-d H:i:s');
        }

        $sql = "UPDATE communications 
                SET indorse_to = ?,
                    date_routed = ?,
                    routed_by = ?,
                    action_taken = ?,
                    remarks = ?";
    }

    $sql .= " WHERE id = ?";

    $stmt = $conn->prepare($sql);

    if ($is_from_indorsed) {
        // Bind for indorsed mode (same as before)
        $stmt->bind_param("sssssi", $routed_by, $action_taken, $remarks, $current_time, $action_duration, $id);
    } else {
        // Bind for normal mode with date_routed
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
            $action_details = "Action taken on indorsed record at " . date('Y-m-d H:i:s') . " (Duration: " . ($action_duration ?? 'N/A') . ")";
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
                (!empty($user['middle_initial']) ? strtoupper(substr($user['middle_initial'], 0, 1)) . '. ' : '') .
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
            indorsed_action_at,
            CASE
                WHEN indorsed_action_at IS NOT NULL
                THEN DATE_FORMAT(indorsed_action_at, '%M %d, %Y %h:%i %p')
                ELSE '-'
            END AS action_taken_formatted
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

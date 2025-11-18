<?php
session_start();
include "db.php"; // database connection

if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    // ✅ Set user status to offline
    $conn->query("UPDATE users SET status='offline' WHERE id='$uid'");
}

// Destroy all session data
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
?>

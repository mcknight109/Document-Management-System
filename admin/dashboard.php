<?php
session_start();
include '../db.php';
date_default_timezone_set('Asia/Manila');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$admin_id = $_SESSION['user_id'];

// Fetch admin details
$sql = "SELECT first_name, middle_initial, last_name, role FROM users WHERE id = $admin_id LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    $admin_fullname = $admin['first_name'] . 
                      (!empty($admin['middle_initial']) ? " " . $admin['middle_initial'] : "") . 
                      " " . $admin['last_name'];
} else {
    $admin_fullname = "Admin User";
}

// Get counts
$users_count = 0;
$active_sessions = 0;
$total_accounts = 0;

$sql = "SELECT COUNT(*) AS total FROM users";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $users_count = $row['total'];
}

$sql = "SELECT COUNT(*) AS active FROM users WHERE status='active'";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $active_sessions = $row['active'];
}

$sql = "SELECT COUNT(*) AS accounts FROM users";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $total_accounts = $row['accounts'];
}

// Fetch latest login logs
$logs = $conn->query("SELECT * FROM login_logs ORDER BY login_time DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin/dashboard.css" rel="stylesheet">
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div>
            <div class="brand">ADMIN DASHBOARD</div>
            <div class="profile">
                <div class="circle">A</div>
                <p>
                    <?= htmlspecialchars($admin_fullname) ?><br>
                    <small><?= ucfirst($admin['role']) ?></small>
                </p>
            </div>
            <div class="nav-menu">
                <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
                <a href="users.php"><i class="fas fa-users"></i> Users</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="footer">© Document Record by ACLC Students</div>
    </div>

    <!-- Content -->
    <div class="content">
        <div class="welcome mb-4">
            <h5>Welcome back, <?= htmlspecialchars($admin_fullname) ?></h5>
            <small><?= date("l, F j, Y - g:i A"); ?></small>
        </div>

        <!-- Dashboard Cards -->
        <div class="row g-4">
            <div class="col-md-4">
                <div class="dashboard-card">
                    <div>
                        <h6>Users</h6>
                        <p><?= $users_count ?></p>
                    </div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card">
                    <div>
                        <h6>Active Sessions</h6>
                        <p><?= $active_sessions ?></p>
                    </div>
                    <div class="icon"><i class="fas fa-signal"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card">
                    <div>
                        <h6>Total Accounts</h6>
                        <p><?= $total_accounts ?></p>
                    </div>
                    <div class="icon"><i class="fas fa-id-card"></i></div>
                </div>
            </div>
        </div>

        <!-- Recent Login Logs -->
        <div class="mt-4">
            <h5>Recent Login Logs</h5>
            <div class="card p-3">
                <table class="table align-middle text-center">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Login Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($logs && $logs->num_rows > 0): ?>
                            <?php while($log = $logs->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($log['username']) ?></td>
                                    <td><span class="role-badge"><?= ucfirst($log['role']) ?></span></td>
                                    <td><?= date("M d, Y h:i A", strtotime($log['login_time'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-muted">No login logs yet</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

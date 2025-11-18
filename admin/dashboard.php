<?php
session_start();
include '../db.php';

// Admin name from session
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : "Admin Elmer";

// Get counts
$users_count = 0;
$active_sessions = 0;
$total_accounts = 0;

$sql = "SELECT COUNT(*) AS total FROM users";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) $users_count = $row['total'];

$sql = "SELECT COUNT(*) AS active FROM users WHERE status='active'";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) $active_sessions = $row['active'];

$sql = "SELECT COUNT(*) AS accounts FROM users";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) $total_accounts = $row['accounts'];

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
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: #f5f6fa; }
    /* Sidebar */
    .sidebar {
      height: 100vh; width: 250px; background: #1e1e2d; color: #fff;
      position: fixed; top: 0; left: 0; display: flex; flex-direction: column; justify-content: space-between;
    }
    .sidebar .brand { font-size: 20px; font-weight: bold; padding: 20px; color: #0d6efd; text-align: center;}
    .profile { text-align: center; padding: 10px 0; }
    .profile .circle {
      width: 60px; height: 60px; border-radius: 50%; background: #0d6efd;
      display: flex; justify-content: center; align-items: center; font-size: 22px; font-weight: bold; margin: auto;
    }
    .profile p { margin: 5px 0 0; font-weight: 500; }
    .nav-menu a {
      display: flex; align-items: center; padding: 12px 20px; color: #cfcfe0; text-decoration: none;
      border-radius: 8px; margin: 5px 15px; transition: 0.3s;
    }
    .nav-menu a i { margin-right: 10px; }
    .nav-menu a.active, .nav-menu a:hover { background: #0d6efd; color: #fff; }
    .sidebar .footer { font-size: 12px; text-align: center; padding: 15px; color: #aaa; }

    /* Content */
    .content { margin-left: 250px; padding: 30px; }
    .welcome h5 { font-weight: 600; margin-bottom: 5px; }
    .welcome small { color: #666; }

    /* Dashboard Cards */
    .dashboard-card {
      background: #fff; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.05);
      padding: 20px; display: flex; justify-content: space-between; align-items: center;
      transition: transform 0.2s ease;
    }
    .dashboard-card:hover { transform: translateY(-3px); }
    .dashboard-card h6 { font-size: 14px; color: #6c757d; margin-bottom: 5px; }
    .dashboard-card p { font-size: 22px; font-weight: bold; margin: 0; }
    .dashboard-card .icon {
      background: #0d6efd; color: #fff; padding: 12px;
      border-radius: 10px; font-size: 20px;
    }

    /* Table */
    .card { border-radius: 12px; border: none; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
    table th { background: #f5f5f9; font-weight: 600; color: #555; }
    table td { vertical-align: middle; }
    .badge { border-radius: 20px; padding: 5px 12px; font-size: 12px; }
    .role-badge {
      background: #e0f0ff; color: #0d6efd; border-radius: 20px; padding: 5px 12px; font-size: 12px;
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <div>
      <div class="brand">Admin Dashboard</div>
      <div class="profile">
        <div class="circle">A</div>
        <p><?= htmlspecialchars($admin_name) ?><br><small>Admin</small></p>
      </div>
      <div class="nav-menu">
        <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
        <a href="users.php"><i class="fas fa-users"></i> Users</a>
        <a href="#"><i class="fas fa-cog"></i> Accounts</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </div>
    <div class="footer">© Document Record by ACLC Students</div>
  </div>

  <!-- Content -->
  <div class="content">
    <div class="welcome mb-4">
      <h5>Welcome back, <?= htmlspecialchars($admin_name) ?> 😊</h5>
      <small><?= date("l, F j, Y - g:i A"); ?></small>
    </div>

    <!-- Cards -->
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
                  <td><?= date("F j, Y - g:i A", strtotime($log['login_time'])) ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="3" class="text-muted">No login logs yet</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</body>
</html>

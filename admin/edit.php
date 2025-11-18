<?php
session_start();
include '../db.php';

// Example: store admin name from session
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : "Admin Elmer";

// Get user ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid request");
}
$user_id = intval($_GET['id']);

// Fetch user
$sql = "SELECT * FROM users WHERE id = $user_id LIMIT 1";
$result = $conn->query($sql);
if (!$result || $result->num_rows === 0) {
    die("User not found");
}
$user = $result->fetch_assoc();

// Update on submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $role = $conn->real_escape_string($_POST['role']);
    $status = $conn->real_escape_string($_POST['status']);
    
    // If password provided, update it
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET username='$username', password='$password', role='$role', status='$status' WHERE id=$user_id";
    } else {
        $update_sql = "UPDATE users SET username='$username', role='$role', status='$status' WHERE id=$user_id";
    }

    if ($conn->query($update_sql)) {
        header("Location: users.php?msg=updated");
        exit();
    } else {
        $error = "Error updating user: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit User</title>
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

    /* Card form */
    .card { border-radius: 12px; border: none; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
    .form-label { font-weight: 500; }
    .form-control, .form-select {
      border-radius: 10px; padding: 10px 15px; border: 1px solid #ddd;
    }
    .btn-primary {
      border-radius: 10px; padding: 10px 20px; font-weight: 500;
      background: #0d6efd; border: none;
    }
    .btn-primary:hover { background: #0b5ed7; }
    .btn-secondary {
      border-radius: 10px; padding: 10px 20px; font-weight: 500;
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
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="users.php" class="active"><i class="fas fa-users"></i> Users</a>
        <a href="#"><i class="fas fa-cog"></i> Settings</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </div>
    <div class="footer">
      © Document Record by ACLC Students
    </div>
  </div>

  <!-- Content -->
  <div class="content">
    <div class="welcome mb-4">
      <h5>Edit User: <?= htmlspecialchars($user['username']) ?></h5>
      <small><?= date("l, F j, Y - g:i A"); ?></small>
    </div>

    <?php if (isset($error)): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="card p-4">
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password (leave blank if not changing)</label>
          <input type="password" name="password" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">Role</label>
          <select name="role" class="form-select" required>
            <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
            <option value="encoder" <?= $user['role']=='encoder'?'selected':'' ?>>Encoder</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Status</label>
          <select name="status" class="form-select" required>
            <option value="active" <?= $user['status']=='active'?'selected':'' ?>>Active</option>
            <option value="offline" <?= $user['status']=='offline'?'selected':'' ?>>Offline</option>
          </select>
        </div>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update</button>
          <a href="users.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Cancel</a>
        </div>
      </form>
    </div>
  </div>

</body>
</html>

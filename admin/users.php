<?php
session_start();
include '../db.php';

// Example: admin name from session
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : "Admin Elmer";

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Search and filter
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$filter = isset($_GET['filter']) ? $conn->real_escape_string($_GET['filter']) : '';

// Base query
$where = "WHERE 1=1";
if (!empty($search)) {
    $where .= " AND username LIKE '%$search%'";
}
if ($filter === 'admin' || $filter === 'encoder') {
    $where .= " AND role='$filter'";
}
if ($filter === 'active' || $filter === 'offline') {
    $where .= " AND status='$filter'";
}

// Get users with limit
$sql = "SELECT * FROM users $where ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Get total for pagination
$total_sql = "SELECT COUNT(*) AS total FROM users $where";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_users = $total_row['total'];
$total_pages = ceil($total_users / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Users Management</title>
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

    /* Top controls */
    .controls { display: flex; gap: 10px; margin-bottom: 20px; }
    .controls .search { flex: unset; }
    .controls input {
      border-radius: 10px; padding: 10px 15px; border: 1px solid #ddd;
      width: 220px; /* smaller width */
    }
    .controls .form-select {
      border-radius: 10px; padding: 10px; border: 1px solid #ddd; width: 180px;
    }
    .controls .btn-create {
      border-radius: 10px; padding: 10px 20px; font-weight: 500;
      background: #0d6efd; color: #fff; border: none;
    }
    .controls .btn-create:hover { background: #0b5ed7; }

    /* Table */
    .card { border-radius: 12px; border: none; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
    table th { background: #f5f5f9; font-weight: 600; color: #555; }
    table td { vertical-align: middle; }
    .badge { border-radius: 20px; padding: 5px 12px; font-size: 12px; }
    .badge.bg-success { background: #22c55e !important; }
    .badge.bg-secondary { background: #6b7280 !important; }
    .role-badge {
      background: #e0f0ff; color: #0d6efd; border-radius: 20px; padding: 5px 12px; font-size: 12px;
    }
    /* Action buttons */
    .btn-action {
      border-radius: 8px; border: none; width: 35px; height: 35px; display: inline-flex; justify-content: center; align-items: center;
    }
    .btn-warning { background: #fef3c7; color: #d97706; }
    .btn-danger { background: #fee2e2; color: #dc2626; }
    .btn-warning:hover { background: #fcd34d; }
    .btn-danger:hover { background: #ef4444; color: #fff; }

    /* Pagination */
    .pagination .page-link { border: none; background: transparent; color: #555; }
    .pagination .page-link:hover { color: #0d6efd; }
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
        <a href="#"><i class="fas fa-cog"></i> Accounts</a>
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
      <h5>Users Account Management</h5>
      <small><?= date("l, F j, Y - g:i A"); ?></small>
    </div>

    <!-- Controls -->
    <form method="get" class="controls d-flex justify-content-between align-items-center">
      <!-- Left side: Search -->
      <div class="search">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
              class="form-control" placeholder="Search username...">
      </div>

      <!-- Right side: Filter + Create -->
      <div class="d-flex gap-2">
        <select name="filter" class="form-select">
          <option value="">All Users</option>
          <option value="admin" <?= $filter=='admin'?'selected':'' ?>>Admin</option>
          <option value="encoder" <?= $filter=='encoder'?'selected':'' ?>>Encoder</option>
          <option value="active" <?= $filter=='active'?'selected':'' ?>>Active</option>
          <option value="offline" <?= $filter=='offline'?'selected':'' ?>>Offline</option>
        </select>
        <a href="create.php" class="btn btn-create">
          <i class="fas fa-user-plus me-1"></i> Create User
        </a>
      </div>
    </form>

    <!-- Users Table -->
    <div class="card p-3">
      <div class="d-flex justify-content-end mb-2">
        <span class="badge role-badge">Total Users: <?= $total_users ?></span>
      </div>
      <table class="table align-middle">
        <thead>
          <tr>
            <th>Select</th>
            <th>Username</th>
            <th>Role</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
              <tr>
                <td><input type="radio" name="selected_user" value="<?= $row['id'] ?>"></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><span class="role-badge"><?= ucfirst($row['role']) ?></span></td>
                <td>
                  <span class="badge <?= $row['status']=='active'?'bg-success':'bg-secondary' ?>">
                    <?= ucfirst($row['status']) ?>
                  </span>
                </td>
                <td><?= date("F j, Y \a\\t g:i A", strtotime($row['created_at'])) ?></td>
                <td>
                  <a href="edit.php?id=<?= $row['id'] ?>" class="btn-action btn-warning"><i class="fas fa-pen"></i></a>
                  <button type="button" class="btn-action btn-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteModal"
                    data-id="<?= $row['id'] ?>"
                    data-username="<?= htmlspecialchars($row['username']) ?>">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center text-muted">No users found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- Pagination -->
      <nav class="d-flex justify-content-end">
        <ul class="pagination">
          <?php if ($page > 1): ?>
            <li class="page-item"><a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>">&lt;</a></li>
          <?php endif; ?>
          <?php if ($page < $total_pages): ?>
            <li class="page-item"><a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>">&gt;</a></li>
          <?php endif; ?>
        </ul>
      </nav>
    </div>
  </div>

  <!-- Delete Modal -->
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="post" action="controls/delete_user.php">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title">Confirm Delete</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete <strong id="deleteUsername"></strong>?</p>
            <input type="hidden" name="user_id" id="deleteUserId">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Delete</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      document.getElementById('deleteUserId').value = button.getAttribute('data-id');
      document.getElementById('deleteUsername').textContent = button.getAttribute('data-username');
    });
  </script>
</body>
</html>

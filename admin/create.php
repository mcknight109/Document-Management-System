<?php
session_start();
include '../db.php';

// Example: admin name from session
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : "Admin Elmer";
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Create User</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../assets/css/admin/create.css">
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
				<a href="users.php"><i class="fas fa-users"></i> Users</a>
				<a href="create.php" class="active"><i class="fas fa-user-plus"></i> Create User</a>
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
		<h5>Create New User</h5>
		<small><?= date("l, F j, Y - g:i A"); ?></small>
		</div>

		<!-- Create User Form -->
		<div class="card p-4">
		<form method="post" action="controls/CreateController.php">
				<div class="row">
					<div class="col-md-4 mb-3">
						<label class="form-label">First Name</label>
						<input type="text" name="first_name" class="form-control" placeholder="Enter the first name" required >
					</div>
					<div class="col-md-2 mb-3">
						<label class="form-label">M.I.</label>
						<input type="text" name="middle_initial" class="form-control" maxlength="1" placeholder="Null">
					</div>
					<div class="col-md-6 mb-3">
						<label class="form-label">Last Name</label>
						<input type="text" name="last_name" class="form-control" placeholder="Enter the last name" required>
					</div>
				</div>

				<div class="mb-3">
					<label class="form-label">Username</label>
					<input type="text" name="username" class="form-control" placeholder="Enter the user name" required>
				</div>
				<div class="mb-3">
					<label class="form-label">Password</label>
					<input type="password" name="password" class="form-control" placeholder="Enter the password" required>
				</div>
				<div class="mb-3">
					<label class="form-label">Role</label>
					<select name="role" class="form-select" required>
					<option value="admin">Admin</option>
					<option value="encoder">Encoder</option>
					</select>
				</div>
				<div class="mb-3">
					<label class="form-label">Status</label>
					<select name="status" class="form-select" required>
					<option value="Active">Active</option>
					<option value="Inactive">Inactive</option>
					</select>
				</div>
				<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save User</button>
				<a href="users.php" class="btn btn-secondary ms-2">Cancel</a>
			</form>
		</div>
	</div>
</body>
</html>

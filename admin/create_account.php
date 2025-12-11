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
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Create User</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../assets/css/admin/create_account.css">
</head>
<body>

	<!-- Sidebar -->
	<div class="sidebar">
		<div>
			<div class="brand">ADMIN DASHBOARD</div>
			<div class="profile">
				<img src="../assets/images/office-of-treasurer.png" alt="">
				<p>
                    <?= htmlspecialchars($admin_fullname) ?><br>
                    <small><?= ucfirst($admin['role']) ?></small>
                </p>
			</div>
			<div class="nav-menu">
				<a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
				<a href="user_manage.php" class="active"><i class="fas fa-users"></i> User Management</a>
                <a href="record_logs.php"><i class="fa-solid fa-file"></i> Record Logs</a>
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
				<div class="row">
					<div class="col-md-6 mb-3">
						<label class="form-label">Email</label>
						<input type="email" name="email" class="form-control" placeholder="Enter email address" required>
					</div>
					<div class="col-md-6 mb-3">
						<label class="form-label">Username</label>
						<input type="text" name="username" class="form-control" placeholder="Enter the user name" required>
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label">Password</label>
					<input type="password" name="password" class="form-control" placeholder="Enter the password" required>
				</div>
				<div class="row">
					<div class="col-md-6 mb-3">
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
					</div>
					<div class="col-md-6 mb-3">
						<label class="form-label">Permissions</label>
						<div class="form-check">
							<input class="form-check-input" type="checkbox" name="permissions[]" value="voucher_records" id="permVoucher">
							<label class="form-check-label" for="permVoucher">Voucher Records</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="checkbox" name="permissions[]" value="check_records" id="permCheck">
							<label class="form-check-label" for="permCheck">Check Records</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="checkbox" name="permissions[]" value="communications_records" id="permCommunications">
							<label class="form-check-label" for="permCommunications">Communications Records</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="checkbox" name="permissions[]" value="activity_records" id="permActivity">
							<label class="form-check-label" for="permActivity">Activity Records</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="checkbox" name="permissions[]" value="certificate_records" id="permCertificate">
							<label class="form-check-label" for="permCertificate">Certificate Records</label>
						</div>
					</div>
				</div>
				<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save User</button>
				<a href="user_manage.php" class="btn btn-secondary ms-2">Cancel</a>
			</form>
		</div>
	</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'success'): ?>
<script>
	Swal.fire({
		icon: 'success',
		title: 'User Created Successfully!',
		text: 'The new user account has been saved.',
		confirmButtonColor: '#3085d6'
	}).then(() => {
		window.location.href = "user_manage.php";
	});
</script>
<?php endif; ?>
</body>
</html>

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

// Fetch admin info
$stmt = $conn->prepare("SELECT first_name, middle_initial, last_name, role FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

$admin_fullname = $admin 
    ? trim($admin['first_name'] . " " . $admin['middle_initial'] . " " . $admin['last_name'])
    : "Admin User";

// Get user ID to edit
if (!isset($_GET['id'])) {
    die("Invalid request");
}

$user_id = intval($_GET['id']);

// Fetch user to edit
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("User not found");
}

$user_permissions = json_decode($user['permissions'], true);
$user_permissions = is_array($user_permissions) ? $user_permissions : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin/update_user.css" rel="stylesheet">
</head>
<body>

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
    <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
    <a href="user_manage.php"><i class="fas fa-users"></i> User Management</a>
    <a href="website_settings.php"><i class="fas fa-cog"></i> Website Settings</a>
    <a href="record_logs.php"><i class="fa-solid fa-file"></i> Record Logs</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

        </div>
        <div class="footer">© Document Record by ACLC Students</div>
    </div>

    <div class="content">
        <div class="welcome mb-4">
            <h5>Users Account Management</h5>
            <small><?= date("l, F j, Y - g:i A"); ?></small>
        </div>

        <div class="card p-4">
            <form method="post" action="controls/UpdateController.php">
                <input type="hidden" name="id" value="<?= $user['id'] ?>">

                <!-- USER INFO -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" value="<?= htmlspecialchars($user['first_name']) ?>" class="form-control" readonly style="background:#e9ecef; cursor:not-allowed;">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">M.I.</label>
                        <input type="text" value="<?= htmlspecialchars($user['middle_initial']) ?>" class="form-control" readonly style="background:#e9ecef; cursor:not-allowed;">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" value="<?= htmlspecialchars($user['last_name']) ?>" class="form-control" readonly style="background:#e9ecef; cursor:not-allowed;">
                    </div>
                </div>

                <!-- EMAIL & USERNAME -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="text" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" readonly style="background:#e9ecef; cursor:not-allowed;">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" class="form-control" readonly style="background:#e9ecef; cursor:not-allowed;">
                    </div>
                </div>

                <!-- PASSWORD -->
                <div class="mb-3">
                    <label class="form-label">Password (leave blank if not changing)</label>
                    <input type="password" name="password" class="form-control" placeholder="***************">
                </div>

                <!-- ROLE & STATUS -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
                            <option value="encoder" <?= $user['role']=='encoder'?'selected':'' ?>>Encoder</option>
                        </select>

                        <label class="form-label mt-3">Status</label>
                        <select name="status" class="form-select">
                            <option value="Active" <?= $user['status']=='Active'?'selected':'' ?>>Active</option>
                            <option value="Inactive" <?= $user['status']=='Inactive'?'selected':'' ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Permissions</label>

                        <?php
                        $perm_list = [
                            "voucher_records" => "Voucher Records",
                            "check_records" => "Check Records",
                            "communications_records" => "Communications Records",
                            "activity_records" => "Activity Records",
                            "certificate_records" => "Certificate Records",
                        ];

                        foreach ($perm_list as $val => $label): ?>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input"
                                    name="permissions[]" value="<?= $val ?>"
                                    <?= in_array($val, $user_permissions) ? 'checked' : '' ?>>
                                <label class="form-check-label"><?= $label ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update</button>
                <a href="user_manage.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Cancel</a>
            </form>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Account Updated Successfully!',
            text: 'The user account details have been saved.',
            confirmButtonColor: '#3085d6'
        }).then(() => {
            window.location.href = "user_manage.php";
        });
    </script>
<?php endif; ?>

</body>
</html>

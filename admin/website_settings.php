<?php
session_start();
include '../db.php';
date_default_timezone_set('Asia/Manila');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// Fetch admin details for sidebar profile
$admin_id = $_SESSION['user_id'];
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

// Fetch current settings
$sql = "SELECT * FROM website_settings WHERE id = 1 LIMIT 1";
$result = $conn->query($sql);
$settings = $result->fetch_assoc();

// Handle Logo Upload
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_FILES["logo"]["name"])) {

        // Upload target OUTSIDE admin/
        $target_dir = "../assets/images/";
        $file_name = time() . "_" . basename($_FILES["logo"]["name"]);
        $target_file = $target_dir . $file_name;

        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($file_type, $allowed)) {
            $error = "Invalid file type. Only JPG, PNG, GIF allowed.";
        } else {
            if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {

                // Delete old file if not default
                if ($settings['logo'] != "assets/images/default-logo.png") {
                    $old_path = "../" . $settings['logo'];
                    if (file_exists($old_path)) unlink($old_path);
                }

                // Save path WITHOUT ../
                $path_db = "assets/images/" . $file_name;

                $update = "UPDATE website_settings SET logo='$path_db' WHERE id=1";
                if ($conn->query($update)) {
                    $success = "Logo updated successfully!";
                    $settings['logo'] = $path_db;
                } else {
                    $error = "Database error: " . $conn->error;
                }

            } else {
                $error = "Failed to upload logo.";
            }
        }
    }
}

// fetch sidebar logo
$ws = $conn->query("SELECT logo FROM website_settings WHERE id=1")->fetch_assoc();
$site_logo = $ws ? $ws['logo'] : 'assets/images/default-logo.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Website Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin/dashboard.css" rel="stylesheet">
</head>
<body>

    <!-- Sidebar (Copied from Dashboard Layout) -->
    <div class="sidebar">
        <div>
            <div class="brand">ADMIN DASHBOARD</div>

            <div class="profile">
                <img src="../<?= $site_logo ?>" alt="Website Logo">
                <p>
                    <?= htmlspecialchars($admin_fullname) ?><br>
                    <small><?= ucfirst($admin['role']) ?></small>
                </p>
            </div>

            <div class="nav-menu">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="user_manage.php"><i class="fas fa-users"></i> User Management</a>
                <a href="website_settings.php" class="active"><i class="fas fa-cog"></i> Website Settings</a>
                <a href="record_logs.php"><i class="fa-solid fa-file"></i> Record Logs</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="footer">© Document Record by ACLC Students</div>
    </div>


    <!-- Content Area -->
    <div class="content">
        <div class="welcome mb-4">
            <h5>Website Settings</h5>
            <small><?= date("l, F j, Y - g:i A"); ?></small>
        </div>

        <div class="card p-4">

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <!-- Show current logo -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Current Logo:</label><br>
                    <img src="../<?= $settings['logo'] ?>" 
                         style="max-width:150px; border:1px solid #ccc; padding:5px; border-radius:5px;">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Upload New Logo</label>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                </div>

                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="dashboard.php" class="btn btn-secondary">Back</a>

            </form>
        </div>
    </div>

</body>
</html>

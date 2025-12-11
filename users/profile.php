<?php
session_start();
include '../db.php'; // make sure path is correct

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// Fetch user's full name from the database
$user_id = $_SESSION['user_id'];
$query = $conn->prepare("SELECT first_name, middle_initial, last_name, username, email FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Combine name properly (handle if middle initial is null)
    $full_name = trim(
        ($user['first_name'] ?? '') . ' ' .
        (!empty($user['middle_initial']) ? strtoupper(substr($user['middle_initial'], 0, 1)) . '. ' : '') .
        ($user['last_name'] ?? '')
    );

    // Fallback to username if full name is empty
    if (empty($full_name)) {
        $full_name = $user['username'] ?? "User";
    }
} else {
    $full_name = $_SESSION['username'] ?? "User";
}

date_default_timezone_set('Asia/Manila');
$currentDate = date("M d, Y");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Activity Design Records</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/users/profile.css">

</head>
<body>

<!-- HEADER -->
<div class="header">
<?php
$ws = $conn->query("SELECT logo FROM website_settings WHERE id=1")->fetch_assoc();
$site_logo = $ws ? $ws['logo'] : 'assets/images/default-logo.png';
?>
<img src="../<?= $site_logo ?>" 
     alt="Website Logo" class="logo" >      <h1>Document Records Management System</h1>

    <div class="header-right">
        <div class="header-user">
            <strong><?php echo htmlspecialchars($full_name); ?></strong>
            <span id="dateTime"><?php echo $currentDate; ?> <span id="liveTime"></span></span>
        </div>

        <!-- Profile Image Container -->
        <div class="profile-container" style="position: relative;">
            <img src="../assets/images/profile.png" alt="Profile" class="header-profile" id="profileBtn">

            <!-- Improved Floating Profile Menu -->
            <div class="profile-menu" id="profileMenu">
                <div class="profile-menu-arrow"></div>
                <ul>
                    <li><a href="../document/users/profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Split -->
<div class="row g-0 justify-content-center align-items-center" style="min-height: 80vh;">

    <div class="col-md-6 col-lg-5">
        <div class="card border-0 p-5 rounded-4" style="background: #ffffff;">
            
            <div class="text-center mb-4">
                <div class="rounded-circle bg-primary bg-gradient d-inline-flex align-items-center justify-content-center" 
                     style="width: 120px; height: 120px; color: white; font-size: 2.5rem;">
                    <i class="fas fa-user-edit"></i>
                </div>
                <h3 class="mt-3 fw-bold text-primary">Edit Profile</h3>
                <p class="text-muted mb-0">Update your personal information below</p>
            </div>

            <form action="Controllers/ProfileController.php" method="POST" class="mt-4">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

                <div class="mb-3">
                    <label for="first_name" class="form-label fw-semibold">
                        <i class="fa-solid fa-user text-primary me-2"></i>First Name
                    </label>
                    <input type="text" class="form-control form-control-lg rounded-3 shadow-sm border-0" id="first_name" 
                        name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="middle_initial" class="form-label fw-semibold">
                        <i class="fa-solid fa-font text-primary me-2"></i>Middle Initial
                    </label>
                    <input type="text" class="form-control form-control-lg rounded-3 shadow-sm border-0" id="middle_initial" 
                        name="middle_initial" value="<?php echo htmlspecialchars($user['middle_initial']); ?>">
                </div>

                <div class="mb-3">
                    <label for="last_name" class="form-label fw-semibold">
                        <i class="fa-solid fa-user text-primary me-2"></i>Last Name
                    </label>
                    <input type="text" class="form-control form-control-lg rounded-3 shadow-sm border-0" id="last_name" 
                        name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">
                        <i class="fa-solid fa-envelope text-primary me-2"></i>Email
                    </label>
                    <input type="email" class="form-control form-control-lg rounded-3 shadow-sm border-0" 
                        id="email" name="email" 
                        value="<?php echo htmlspecialchars($user['email']); ?>" 
                        required>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold">
                        <i class="fa-solid fa-at text-primary me-2"></i>Username
                    </label>
                    <input type="text" class="form-control form-control-lg rounded-3 shadow-sm border-0" id="username" 
                        name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">
                        <i class="fa-solid fa-lock text-primary me-2"></i>New Password
                    </label>
                    <input type="password" class="form-control form-control-lg rounded-3 shadow-sm border-0" id="password" 
                        name="password" placeholder="Leave blank to keep current password">
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <button type="submit" name="update_profile" 
                            class="btn btn-lg px-4 rounded-3 shadow-sm d-flex align-items-center gap-2" style="background-color: darkblue; color:#ffffff;">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>

                    <a href="../index.php" class="btn btn-outline-secondary btn-lg px-4 rounded-3 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
    <script>
        Swal.fire({
            icon: "success",
            title: "Profile Updated!",
            text: "<?php echo $_SESSION['success_message']; ?>",
            confirmButtonColor: "#1e3a8a"
        });
    </script>
<?php unset($_SESSION['success_message']); endif; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../date.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const profileBtn = document.getElementById("profileBtn");
        const profileMenu = document.getElementById("profileMenu");

        // Toggle menu on click
        profileBtn.addEventListener("click", function(e) {
            e.stopPropagation();
            profileMenu.style.display = profileMenu.style.display === "block" ? "none" : "block";
        });

        // Close menu when clicking outside
        document.addEventListener("click", function() {
            profileMenu.style.display = "none";
        });

        // Keep menu open if clicking inside
        profileMenu.addEventListener("click", function(e) {
            e.stopPropagation();
        });
    });
</script>

</body>
</html>

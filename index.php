<?php
session_start();
include "db.php"; // ✅ Make sure this sets up $conn

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// Fetch user's full name from the database
$user_id = $_SESSION['user_id'];
$query = $conn->prepare("SELECT first_name, middle_initial, last_name, username FROM users WHERE id = ?");
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

// Example: Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // If logged in, you don’t need to query again
} else {
    // Example query (adjust table/column names to match your DB)
    $username = $_POST['username'] ?? null;
    $password = $_POST['password'] ?? null;

    if ($username && $password) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username=? AND password=? LIMIT 1");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows == 1) {
            $user = $result->fetch_assoc();

            $_SESSION['user_id']  = $user['id'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['username'] = $user['username'];

            // ✅ Insert log
            $uid   = $user['id'];
            $uname = $conn->real_escape_string($user['username']);
            $conn->query("INSERT INTO login_logs (user_id, username) VALUES ('$uid', '$uname')");

            // Redirect based on role
            if ($user['role'] == 'admin') {
                header("Location: admin/index.php");
            } elseif ($user['role'] == 'encoder') {
                header("Location: index.php");
            }
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Administrative Division</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="index.css">

</head>
<body>
<!-- HEADER -->
<div class="header">
    <img src="assets/images/office-of-treasurer.png" alt="Logo" class="logo">
    <h1>Document Records Management System</h1>

    <div class="header-right">
        <div class="header-user">
            <strong><?php echo htmlspecialchars($full_name); ?></strong>
            <span id="dateTime"><?php echo $currentDate; ?> <span id="liveTime"></span></span>
        </div>

        <!-- Profile Image Container -->
        <div class="profile-container" style="position: relative;">
            <img src="assets/images/profile.png" alt="Profile" class="header-profile" id="profileBtn">

            <!-- Improved Floating Profile Menu -->
            <div class="profile-menu" id="profileMenu">
                <div class="profile-menu-arrow"></div>
                <ul>
                    <li><a href="../document/users/profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="main-container">
    <div class="left-side">
        <img src="assets/images/office-of-treasurer.png" alt="Logo">
        <p>Administrative</p>
        <p>Division</p>
    </div>

    <div class="right-side">
        <a href="users/document_voucher.php" class="button"><i class="fas fa-folder-open"></i>Document Voucher</a>
        <a href="users/check_voucher.php" class="button"><i class="fas fa-check-circle"></i>Check</a>
        <a href="users/communication.php" class="button"><i class="fas fa-comments"></i>Communication</a>
        <a href="users/activity_design.php" class="button"><i class="fas fa-tasks"></i>Activity Design</a>
        <a href="users/certificate.php" class="button"><i class="fas fa-certificate"></i>Certificate</a>
        <a href="#" class="button" data-bs-toggle="modal" data-bs-target="#payeeModal"><i class="fas fa-search"></i>Search Payee</a>
    </div>
</div>

<!-- Modal HTML -->
<div class="modal fade" id="welcomeModal" tabindex="-1" aria-labelledby="welcomeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-2">
            <div class="modal-body">
                <!-- Logo above text -->
                <img src="assets/images/office-of-treasurer.png" alt="Logo" style="width:80px; height:auto; margin-bottom:15px;">
                
                <!-- Title -->
                <h5 class="modal-title mb-2" id="welcomeModalLabel">Welcome to the System!</h5>
            </div>
            <!-- Centered Button -->
            <div class="modal-footer justify-content-center border-0">
                <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">Got it!</button>
            </div>
        </div>
    </div>
</div>

<!-- Profile Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-3">
            <div class="modal-header border-0 justify-content-center">
                <img src="assets/images/profile.png" alt="Profile" style="width:80px; height:80px; border-radius:50%; border:2px solid #1e3a8a;">
            </div>
            <div class="modal-body">
                <h5 class="modal-title mb-3" id="profileModalLabel"><?php echo htmlspecialchars($full_name); ?></h5>
                <div class="d-grid gap-2">
                <a href="users/profile.php" class="btn btn-primary btn-lg"><i class="fas fa-user me-2"></i> Profile</a>
                <a href="logout.php" class="btn btn-danger btn-lg"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Payee Modal -->
<div class="modal fade" id="payeeModal" tabindex="-1" aria-labelledby="payeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-3">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="payeeModalLabel"><i class="fas fa-user"></i> Search Payee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Search Input -->
                <input type="text" id="payeeSearch" class="form-control mb-3" placeholder="Search by first, middle, or last name">

                <!-- Results Table -->
                <div class="table-responsive" style="max-height:250px; overflow-y:auto;">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Added By</th>
                        </tr>
                        </thead>
                        <tbody id="payeeResults">
                        <!-- Results inserted via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="date.js"></script>
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (!localStorage.getItem("welcomeShown")) {
            var welcomeModal = new bootstrap.Modal(document.getElementById('welcomeModal'));
            welcomeModal.show();
            localStorage.setItem("welcomeShown", "true");
        }
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const payeeSearch = document.getElementById("payeeSearch");
        const payeeResults = document.getElementById("payeeResults");
        let debounceTimeout = null;

        payeeSearch.addEventListener("input", function() {
        const query = this.value.trim();

        if (debounceTimeout) clearTimeout(debounceTimeout);

        if (query.length === 0) {
            payeeResults.innerHTML = "";
            return;
        }

        debounceTimeout = setTimeout(() => {
            fetch(`users/Controllers/PayeeController.php?action=search&query=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    payeeResults.innerHTML = "";
                    if (data.length > 0) {
                        data.forEach(payee => {
                            const row = document.createElement("tr");
                            row.innerHTML = `
                                <td>${payee.payee}</td>
                                <td>${payee.added_by_name}</td>
                            `;
                            payeeResults.appendChild(row);
                        });
                    } else {
                        payeeResults.innerHTML = `<tr><td colspan="2" class="text-center text-muted">No results found</td></tr>`;
                    }
                })
                .catch(err => console.error("Search error:", err));
        }, 300);
    });
});
</script>

</body>
</html>

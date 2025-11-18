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

// Pagination setup
$limit = 10; // records per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Count total certificate records
$countQuery = $conn->prepare("SELECT COUNT(*) AS total FROM certificate_records WHERE user_id = ?");
$countQuery->bind_param("i", $user_id);
$countQuery->execute();
$totalResult = $countQuery->get_result()->fetch_assoc();
$totalRecords = $totalResult['total'];
$totalPages = ceil($totalRecords / $limit);

// Fetch paginated certificate records
$recordsQuery = $conn->prepare("SELECT * FROM certificate_records WHERE user_id = ? ORDER BY id DESC");
$recordsQuery->bind_param("i", $user_id);
$recordsQuery->execute();
$records = $recordsQuery->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Certificate Records</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/users/certificate.css">
<style>
    .profile-menu {
        position: absolute;
        top: 55px;
        right: 0;
        width: 180px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        display: none;
        z-index: 1000;
        overflow: hidden;
        animation: fadeIn 0.2s ease-out;
        font-family: "Segoe UI", sans-serif;
    }

    .profile-menu-arrow {
        position: absolute;
        top: -8px;
        right: 14px;
        width: 0;
        height: 0;
        border-left: 8px solid transparent;
        border-right: 8px solid transparent;
        border-bottom: 8px solid #fff;
    }

    .profile-menu ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .profile-menu ul li {
        border-bottom: 1px solid #eee;
    }

    .profile-menu ul li:last-child {
        border-bottom: none;
    }

    .profile-menu ul li a {
    display: flex;
    align-items: center;
    color: #1e3a8a;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    }   

    .profile-menu ul li a i {
        margin-right: 10px;
        font-size: 1rem;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s, color 0.2s;
    }

    .profile-menu ul li a:hover i {
        background: darkblue;
        color: #fff;
    }

    .profile-menu ul li a:hover {
        color: darkblue;
        background-color: #d6d6d6ff;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
</head>
<body>

<!-- HEADER -->
<div class="header">
    <img src="../assets/images/office-of-treasurer.png" alt="Logo" class="logo">
    <h1>Document Records Management System</h1>

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

<div class="row g-0">
    <!-- LEFT SIDE: Table -->
    <div class="col-md-8 d-flex">
        <div class="card flex-fill">
            <div class="card-header">
                    <i class="bi bi-table"></i>
                    Certificate Records
                </div>
            <div class="card-body d-flex flex-column" style="min-height: 500px;">
                <!-- Pagination Controls -->
                <div class="pagination-controls">
                    <div>
                        <a class="btn btn-outline-secondary btn-sm" href="">
                            <i class="bi bi-chevron-left"></i> Previous
                        </a>
                        <a class="btn btn-outline-secondary btn-sm" href="">
                            Next <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                    <div class="page-info">
                        Page <strong>1</strong> of <strong>1</strong> (0 records)
                    </div>
                </div>
                <!-- Table Section -->
                <div class="table-responsive flex-grow-1" style="overflow-y: auto;">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th class="checkbox-cell"><input type="checkbox" id="selectAll"></th>
                                <th>Control No.</th>
                                <th>Project</th>
                                <th>Office</th>
                                <th>Date Out</th>
                                <th>Claimed By</th>
                            </tr>
                        </thead>
                    <tbody id="recordsTableBody">
                        <?php if ($records->num_rows > 0): ?>
                            <?php while ($row = $records->fetch_assoc()): ?>
                            <tr data-id="<?php echo $row['id']; ?>">
                                <td><input type="checkbox" class="rowCheckbox"></td>
                                <td><?php echo htmlspecialchars($row['control_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['project']); ?></td>
                                <td><?php echo htmlspecialchars($row['office']); ?></td>
                                <td><?php echo htmlspecialchars(date("M d, Y", strtotime($row['date_out']))); ?></td>
                                <td><?php echo htmlspecialchars($row['claimed_by']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan='6' class='no-records'>
                                <i class='bi bi-inbox'></i>
                                <div>No records found</div>
                                </td>
                            </tr>;
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT SIDE: Form -->
    <div class="col-md-4 d-flex">
        <div class="card flex-fill">
            <div class="card-header">
                    <i class="bi bi-inbox-fill"></i>
                    New Certificate Record
                </div>
            <div class="card-body" style="min-height: 500px;">
                <form method="POST" id="certificateForm" action="Controllers/CertificateController.php">
                    <div class="mb-2">
                        <label class="form-label">Control No.</label>
                        <input type="text" name="control_number" id="control_number" class="form-control" placeholder="Enter the control no."  >
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Project</label>
                        <input type="text" name="project" id="project" class="form-control" placeholder="Enter the project name" >
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Office</label>
                        <input type="text" name="office" id="office" class="form-control" placeholder="Enter the Office" >
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Date Out</label>
                        <input type="date" name="date_out" id="date_out" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Claimed By</label>
                        <input type="text" name="claimed_by" id="claimed_by" class="form-control" placeholder="Claimed By.">
                    </div>

                    <div class="form-buttons">
                        <button type="reset" class="btn btn-custom">
                            <i class="bi bi-plus-circle"></i> New
                        </button>
                        <button type="submit" name="save" class="btn btn-custom">
                            <i class="bi bi-save"></i> Save
                        </button>
                        <button type="button" id="deleteBtn" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                        <button onclick="window.location.href='../index.php'" type="button" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
    // Success popup after save
    <?php if (isset($_SESSION['save_success']) && $_SESSION['save_success'] === true): ?>
        Swal.fire({
            icon: 'success',
            title: 'Saved Successfully!',
            text: 'The new activity has been added.',
            confirmButtonColor: '#1e3a8a'
        });
        <?php unset($_SESSION['save_success']); ?>
    <?php endif; ?>

    // Show "Enter a new activity" message when New is clicked
    const newBtn = document.querySelector('.btn-custom[type="reset"]');
    newBtn.addEventListener('click', function() {
        const existing = document.getElementById('newActivityMsg');
        if (existing) existing.remove();

        const msg = document.createElement('div');
        msg.id = 'newActivityMsg';
        msg.textContent = 'Enter a new certificate';
        msg.style.fontWeight = '600';
        msg.style.color = 'darkblue';
        msg.style.marginBottom = '8px';
        msg.style.textAlign = 'center';

        const controlLabel = document.querySelector('label[for="control_no"]') || document.querySelector('label.form-label');
        controlLabel.parentNode.insertBefore(msg, controlLabel);
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const rows = Array.from(document.querySelectorAll("#recordsTableBody tr"))
        .filter(row => !row.classList.contains("no-records"));
    const selectAllCheckbox = document.getElementById("selectAll");
    const pageInfo = document.querySelector(".page-info");
    const prevBtn = document.querySelector(".pagination-controls .btn-outline-secondary:first-child");
    const nextBtn = document.querySelector(".pagination-controls .btn-outline-secondary:last-child");
    const rowsPerPage = 10; // number of rows per page
    let currentPage = 1;

    function renderTablePage() {
        const totalRecords = rows.length;
        const totalPages = Math.ceil(totalRecords / rowsPerPage) || 1;
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        rows.forEach((row, index) => {
            row.style.display = index >= start && index < end ? "" : "none";
        });

        pageInfo.innerHTML = `Page <strong>${currentPage}</strong> of <strong>${totalPages}</strong> (${totalRecords} record${totalRecords !== 1 ? "s" : ""})`;

        prevBtn.classList.toggle("disabled", currentPage === 1);
        nextBtn.classList.toggle("disabled", currentPage === totalPages);
    }

    prevBtn.addEventListener("click", function(e) {
        e.preventDefault();
        if (currentPage > 1) {
            currentPage--;
            renderTablePage();
        }
    });

    nextBtn.addEventListener("click", function(e) {
        e.preventDefault();
        const totalPages = Math.ceil(rows.length / rowsPerPage) || 1;
        if (currentPage < totalPages) {
            currentPage++;
            renderTablePage();
        }
    });

    renderTablePage();

    // Select All checkbox
    selectAllCheckbox.addEventListener("change", function () {
        const visibleRows = rows.filter(row => row.style.display !== "none");
        visibleRows.forEach(row => {
            const cb = row.querySelector(".rowCheckbox");
            if (cb) cb.checked = this.checked;
        });
    });

    rows.forEach(row => {
        const checkbox = row.querySelector(".rowCheckbox");
        if (!checkbox) return;

        row.addEventListener("click", function (e) {
            if (e.target.type === "checkbox") return;
            checkbox.checked = !checkbox.checked;

            const visibleRows = rows.filter(r => r.style.display !== "none");
            const allChecked = visibleRows.every(r => r.querySelector(".rowCheckbox").checked);
            selectAllCheckbox.checked = allChecked;
        });
    });
});
</script>

<script>
    // Delete button confirmation showing Control No.
    const deleteBtn = document.getElementById('deleteBtn');
    deleteBtn.addEventListener('click', function (e) {
        e.preventDefault();

        const checked = document.querySelectorAll('.rowCheckbox:checked');
        if (checked.length === 0) {
            Swal.fire('No Selection', 'Please select at least one record to delete.', 'warning');
            return;
        }

        // Get Control No. from the table (2nd column)
        const controlNos = Array.from(checked).map(cb => cb.closest('tr').children[1].textContent.trim());

        Swal.fire({
            title: 'Confirm Delete',
            html: `<p>Are you sure you want to delete the following Control No(s)?</p>
                <strong>${controlNos.join(', ')}</strong>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Delete!'
        }).then(result => {
            if (result.isConfirmed) {
                const form = deleteBtn.closest('form');
                form.submit();
            }
        });

        // Success popup after delete
        <?php if (isset($_SESSION['delete_success']) && $_SESSION['delete_success'] === true): ?>
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: 'Selected certificate record(s) have been deleted.',
                confirmButtonColor: '#1e3a8a'
            });
            <?php unset($_SESSION['delete_success']); ?>
        <?php endif; ?>
    });
</script>

<script>
    // ✅ Show success message after saving
    <?php if (isset($_SESSION['save_success']) && $_SESSION['save_success'] === true): ?>
        Swal.fire({
        icon: 'success',
        title: 'Saved Successfully!',
        text: 'The certificate record has been added.',
        confirmButtonColor: '#1e3a8a'
        });
        <?php unset($_SESSION['save_success']); ?>
    <?php endif; ?>
</script>
</body>
</html>

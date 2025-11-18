<?php
session_start();
include '../db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$query = $conn->prepare("SELECT first_name, middle_initial, last_name, username FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    $full_name = trim(
        ($user['first_name'] ?? '') . ' ' .
        (!empty($user['middle_initial']) ? strtoupper(substr($user['middle_initial'], 0, 1)) . '. ' : '') .
        ($user['last_name'] ?? '')
    );

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

// Count total records
$countQuery = $conn->prepare("SELECT COUNT(*) AS total FROM activity_designs WHERE user_id = ?");
$countQuery->bind_param("i", $user_id);
$countQuery->execute();
$totalResult = $countQuery->get_result()->fetch_assoc();
$totalRecords = $totalResult['total'];
$totalPages = ceil($totalRecords / $limit);

// Fetch paginated records
$activitiesQuery = $conn->prepare("SELECT * FROM activity_designs WHERE user_id = ? ORDER BY id DESC");
$activitiesQuery->bind_param("i", $user_id);
$activitiesQuery->execute();
$activities = $activitiesQuery->get_result();
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
<link rel="stylesheet" href="../assets/css/users/activity_design.css">
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

<!-- Main Content Split -->
    <div class="row g-0">
        <!-- LEFT SIDE: Table -->
        <div class="col-md-8 d-flex">
            <div class="card flex-fill">
                <div class="card-header">
                    <i class="bi bi-table"></i>
                    Activity Design Records
                </div>
                <div class="card-body d-flex flex-column">
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
                                    <th>Department</th>
                                    <th>Title</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="recordsTableBody">
                                <?php
                                $activitiesQuery = $conn->prepare("SELECT * FROM activity_designs WHERE user_id = ? ORDER BY id DESC");
                                $activitiesQuery->bind_param("i", $user_id);
                                $activitiesQuery->execute();
                                $activities = $activitiesQuery->get_result();

                                if ($activities->num_rows > 0) {
                                    while ($row = $activities->fetch_assoc()) {
                                        echo "<tr>
                                            <td><input type='checkbox' class='rowCheckbox' data-id='{$row['id']}'></td>
                                            <td>" . htmlspecialchars($row['control_no']) . "</td>
                                            <td>" . htmlspecialchars($row['department']) . "</td>
                                            <td>" . htmlspecialchars($row['activity_title']) . "</td>
                                            <td>₱" . number_format($row['budget'], 2) . "</td>
                                            <td>" . date("M d, Y", strtotime($row['date_out'])) . "</td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr class='no-records'><td colspan='6' class='text-center text-muted'>No records found.</td></tr>";
                                }   
                                ?>
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
                    New Activity Record
                </div>
                <div class="card-body" style="min-height: 500px;">

                    <form method="POST" action="Controllers/ActivityController.php" id="activityForm">
                        <div class="mb-2">
                            <label class="form-label">Control No.</label>
                            <input type="text" name="control_no" id="control_no" class="form-control" placeholder="Enter the Control No.">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Department</label>
                            <select name="department" id="department" class="form-select">
                                <option value="CITY MAYOR OFFICE">CITY MAYOR OFFICE</option>
                                <option value="Other Department">Other Department</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Activity Title</label>
                            <input type="text" name="activity_title" id="activity_title" class="form-control" placeholder="Enter the activity title.">
                        </div>
                        <div class="mb-2">
                        <label class="form-label">Budget</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="text" name="budget" id="budget" class="form-control" placeholder="Enter the budget">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Date Out</label>
                            <input type="date" name="date_out" id="date_out" class="form-control">
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
document.addEventListener("DOMContentLoaded", () => {
    const selectAll = document.getElementById("selectAll");
    const checkboxes = document.querySelectorAll(".rowCheckbox");

    selectAll.addEventListener("change", function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    checkboxes.forEach(cb => {
        cb.addEventListener("change", function() {
            const allChecked = Array.from(checkboxes).every(box => box.checked);
            selectAll.checked = allChecked;
        });
    });
});
</script>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const budgetInput = document.getElementById("budget");

        // Format while typing (allow numbers and decimal)
        budgetInput.addEventListener("input", function() {
            this.value = this.value.replace(/[^\d.]/g, ''); // Only numbers & dot
        });

        // Add .00 automatically on blur
        budgetInput.addEventListener("blur", function() {
            if (this.value === "") return;
            let val = parseFloat(this.value);
            if (!isNaN(val)) this.value = val.toFixed(2);
        });
    });
</script>

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
    // ✅ Success popup after save
    <?php if (isset($_SESSION['save_success']) && $_SESSION['save_success'] === true): ?>
        Swal.fire({
            icon: 'success',
            title: 'Saved Successfully!',
            text: 'The new activity has been added.',
            confirmButtonColor: '#1e3a8a'
        });
        <?php unset($_SESSION['save_success']); ?>
    <?php endif; ?>

    // ✅ Show "Enter a new activity" message when New is clicked
    const newBtn = document.querySelector('.btn-custom[type="reset"]');
    newBtn.addEventListener('click', function() {
        const existing = document.getElementById('newActivityMsg');
        if (existing) existing.remove();

        const msg = document.createElement('div');
        msg.id = 'newActivityMsg';
        msg.textContent = 'Enter a new activity';
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

    // Render the current page
    function renderTablePage() {
        const totalRecords = rows.length;
        const totalPages = Math.ceil(totalRecords / rowsPerPage) || 1;
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        rows.forEach((row, index) => {
            row.style.display = index >= start && index < end ? "" : "none";
        });

        // Update page info dynamically
        pageInfo.innerHTML = `Page <strong>${currentPage}</strong> of <strong>${totalPages}</strong> (${totalRecords} record${totalRecords !== 1 ? "s" : ""})`;

        // Enable/disable Previous/Next buttons
        prevBtn.classList.toggle("disabled", currentPage === 1);
        nextBtn.classList.toggle("disabled", currentPage === totalPages);
    }

    // Previous button click
    prevBtn.addEventListener("click", function (e) {
        e.preventDefault();
        if (currentPage > 1) {
            currentPage--;
            renderTablePage();
        }
    });

    // Next button click
    nextBtn.addEventListener("click", function (e) {
        e.preventDefault();
        const totalPages = Math.ceil(rows.length / rowsPerPage) || 1;
        if (currentPage < totalPages) {
            currentPage++;
            renderTablePage();
        }
    });

    renderTablePage(); // initial render

    // ✅ Checkbox logic
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

            // Auto uncheck "select all" if any unchecked
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
    });
</script>

<script>
    // Show success message after saving
    <?php if (isset($_SESSION['save_success']) && $_SESSION['save_success'] === true): ?>
        Swal.fire({
        icon: 'success',
        title: 'Saved Successfully!',
        text: 'The communication record has been added.',
        confirmButtonColor: '#1e3a8a'
        });
        <?php unset($_SESSION['save_success']); ?>
    <?php endif; ?>
</script>
</body>
</html>

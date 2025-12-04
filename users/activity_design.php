<?php
session_start();
include '../db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$query = $conn->prepare("SELECT first_name, middle_initial, last_name, username, permissions FROM users WHERE id = ?");
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

// Decode permissions JSON into array
$user_permissions = [];
if (!empty($user['permissions'])) {
    $user_permissions = json_decode($user['permissions'], true);
}

// Check if user has voucher_records permission
$canAccessActivity = in_array("activity_records", $user_permissions);

// Pagination setup
$limit = 10; // records per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Count total records
$countQuery = $conn->prepare("SELECT COUNT(*) AS total FROM activity_designs");
$countQuery->execute();
$totalResult = $countQuery->get_result()->fetch_assoc();
$totalRecords = $totalResult['total'];
$totalPages = ceil($totalRecords / $limit);

// Fetch paginated records
$activitiesQuery = $conn->prepare("SELECT * FROM activity_designs ORDER BY id DESC");
$activitiesQuery->execute();
$activities = $activitiesQuery->get_result();

// Get the next ConID
$nextConID = 1; // default if table is empty
$conQuery = $conn->prepare("SELECT control_no FROM activity_designs ORDER BY id DESC LIMIT 1");
$conQuery->execute();
$conResult = $conQuery->get_result();
if ($conResult->num_rows > 0) {
    $lastCon = $conResult->fetch_assoc();
    $nextConID = intval($lastCon['control_no']) + 1;
}
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
<style>

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

<!-- Main Content Split -->
    <div class="row g-0">
        <!-- LEFT SIDE: Table -->
        <div class="col-md-8 d-flex">
            <div class="card flex-fill">
                <div class="card-header ok-card">
                    <div class="left">
                        <i class="bi bi-table"></i>
                        Activity Design Records
                    </div>
                    <div class="right">
                        <div class="search-container">
                            <input type="text" id="tableSearch" placeholder="Search records..." />
                            <button id="searchBtn"><i class="bi bi-search"></i></button>
                        </div>
                    </div>
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
                                    echo "<tr>
                                            <td colspan='6' class='no-records'>
                                            <i class='bi bi-inbox'></i>
                                            <div>No records found</div>
                                            </td>
                                        </tr>;";
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
                        <input type="hidden" name="delete_ids" id="delete_ids">
                        <div class="mb-2">
                            <label class="form-label">Control No.</label>
                            <input type="text" name="control_no" id="control_no" class="form-control" readonly 
                                style="background:#e9ecef; cursor:not-allowed;" required
                                value="<?php echo $nextConID; ?>">
                        </div>
                        <div class="mb-2 position-relative">
                            <label class="form-label">Department</label>
                            <input type="text" name="department" id="department" class="form-control" placeholder="Enter or select a department" autocomplete="off">
                            <div id="departmentSuggestions" class="suggestions-dropdown"></div>
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
                            <button type="reset" class="btn btn-custom" <?= !$canAccessActivity ? 'disabled style="background:darkblue;color:#ffffff;opacity:0.8;cursor:not-allowed;"' : '' ?>>
                                <i class="bi bi-plus-circle"></i> New
                            </button>
                            <button type="submit" name="save" class="btn btn-custom" <?= !$canAccessActivity ? 'disabled style="background:darkblue;color:#ffffff;opacity:0.8;cursor:not-allowed;"' : '' ?>>
                                <i class="bi bi-save"></i> Save
                            </button>
                            <!-- <button type="button" id="deleteBtn" class="btn btn-danger" <?= !$canAccessActivity ? 'disabled style="background:red;color:#ffffff;opacity:0.8;cursor:not-allowed;"' : '' ?>>
                                <i class="bi bi-trash"></i> Delete
                            </button> -->

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
    document.querySelector('.btn-custom[type="reset"]').addEventListener('click', function() {
        // Reset form
        document.getElementById('id').value = '';
        document.getElementById('date_received').value = '';
        document.getElementById('sender').value = '';
        document.getElementById('description').value = '';
        document.getElementById('indorse_to').value = '';
        document.getElementById('date_routed').value = '';
        document.getElementById('action_taken').value = '';
        document.getElementById('remarks').value = '';

        // Set ComID to next value
        const comIdInput = document.getElementById('com_id');
        const lastComId = <?php echo $nextComID - 1; ?>; // last used ComID
        comIdInput.value = lastComId + 1;

        // Optional: Show message
        if (!document.getElementById('newFormMsg')) {
            const label = document.createElement('div');
            label.id = 'newFormMsg';
            label.textContent = 'Enter a new form.';
            label.style.color = 'darkblue';
            label.style.fontWeight = '600';
            label.style.textAlign = 'center';
            label.style.marginBottom = '8px';
            comIdInput.parentNode.insertBefore(label, comIdInput);
        }
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

        // Get IDs and Control No.
        const ids = Array.from(checked).map(cb => cb.dataset.id);
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


                // Clear old hidden inputs
                const form = document.getElementById("activityForm");
                form.querySelectorAll('input[name="delete_ids[]"]').forEach(el => el.remove());

                // Add a hidden input for each selected ID
                ids.forEach(id => {
                    const input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "delete_ids[]";
                    input.value = id;
                    form.appendChild(input);
                });

                // Submit the form
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

        // Show success message after deletion
    <?php if (isset($_SESSION['delete_success']) && $_SESSION['delete_success'] === true): ?>
        Swal.fire({
            icon: 'success',
            title: 'Deleted Successfully!',
            html: 'The selected Control No(s) have been deleted.',
            confirmButtonColor: '#1e3a8a'
        });
        <?php unset($_SESSION['delete_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['delete_error']) && $_SESSION['delete_error'] === true): ?>
        Swal.fire({
            icon: 'error',
            title: 'Delete Failed!',
            text: 'Something went wrong while deleting records.',
            confirmButtonColor: '#d33'
        });
        <?php unset($_SESSION['delete_error']); ?>
    <?php endif; ?>
</script>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const departmentInput = document.getElementById("department");
        const suggestionsContainer = document.getElementById("departmentSuggestions");

        // Fetch all departments from PHP
        const departments = [
            <?php
            $deptQuery = $conn->query("SELECT DISTINCT department FROM activity_designs ORDER BY department ASC");
            $deptArr = [];
            while ($dept = $deptQuery->fetch_assoc()) {
                $deptArr[] = '"' . addslashes($dept['department']) . '"';
            }
            echo implode(',', $deptArr);
            ?>
        ];

        function showSuggestions(filtered) {
            suggestionsContainer.innerHTML = '';
            if (filtered.length === 0) {
                suggestionsContainer.style.display = 'none';
                return;
            }
            filtered.forEach(dept => {
                const div = document.createElement('div');
                div.textContent = dept;
                div.addEventListener('click', () => {
                    departmentInput.value = dept;
                    suggestionsContainer.style.display = 'none';
                });
                suggestionsContainer.appendChild(div);
            });
            suggestionsContainer.style.display = 'block';
        }

        function filterAndShow() {
            const value = departmentInput.value.toLowerCase();
            const filtered = departments.filter(d => d.toLowerCase().includes(value));
            showSuggestions(filtered);
        }

        // Show suggestions when typing
        departmentInput.addEventListener('input', filterAndShow);

        // ✅ Show suggestions immediately on focus
        departmentInput.addEventListener('focus', filterAndShow);

        // Hide suggestions on outside click
        document.addEventListener('click', (e) => {
            if (!departmentInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                suggestionsContainer.style.display = 'none';
            }
        });
    });
</script>

<script>
    // Table search functionality
    const searchInput = document.getElementById("tableSearch");
    const searchBtn = document.getElementById("searchBtn");
    const table = document.getElementById("recordsTable");
    const tableRows = table.querySelectorAll("tbody tr");

    function filterTable() {
        const query = searchInput.value.toLowerCase();
        tableRows.forEach(row => {
            const rowText = row.textContent.toLowerCase();
            row.style.display = rowText.includes(query) ? "" : "none";
        });
    }

    // Trigger search on button click
    searchBtn.addEventListener("click", filterTable);

    // Trigger search on Enter key
    searchInput.addEventListener("keyup", (e) => {
        if (e.key === "Enter") {
            filterTable();
        }
    });
</script>
</body>
</html>

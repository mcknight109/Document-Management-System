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
$query = $conn->prepare("SELECT first_name, middle_initial, last_name, username, permissions FROM users WHERE id = ?");
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

// Decode permissions JSON into array
$user_permissions = [];
if (!empty($user['permissions'])) {
    $user_permissions = json_decode($user['permissions'], true);
}

// Check if user has voucher_records permission
$canAccessCertificate = in_array("certificate_records", $user_permissions);

// Pagination setup
$limit = 10; // records per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Count total certificate records
$countQuery = $conn->prepare("SELECT COUNT(*) AS total FROM certificate_records");

$countQuery->execute();
$totalResult = $countQuery->get_result()->fetch_assoc();
$totalRecords = $totalResult['total'];
$totalPages = ceil($totalRecords / $limit);

// Fetch paginated certificate records
$recordsQuery = $conn->prepare("SELECT * FROM certificate_records ORDER BY CAST(control_no AS UNSIGNED) ASC");
$recordsQuery->execute();
$records = $recordsQuery->get_result();

// Get the next ConID
$nextConID = 1; // default if table is empty
$conQuery = $conn->prepare("SELECT control_no FROM certificate_records ORDER BY CAST(control_no AS UNSIGNED) DESC LIMIT 1");
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/users/certificate.css">
<style>

</style>
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

<div class="row g-0">
    <!-- LEFT SIDE: Table -->
    <div class="col-md-8 d-flex">
        <div class="card flex-fill">
            <div class="card-header ok-card">
                <div class="left">
                    <i class="bi bi-table"></i>
                    Certificate Records
                </div>
                <div class="right">
                    <div class="search-container">
                        <input type="text" id="tableSearch" placeholder="Search records..." />
                        <button id="searchBtn"><i class="bi bi-search"></i></button>
                    </div>
                </div>
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
                                <td><?php echo htmlspecialchars($row['control_no']); ?></td>
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
                    <input type="hidden" name="delete_ids[]" id="delete_ids">
                    <div class="mb-2">
                        <label class="form-label">Control No.</label>
                        <input type="text" name="control_no" id="control_no" class="form-control" readonly 
                            style="background:#e9ecef; cursor:not-allowed;" required
                            value="<?php echo $nextConID; ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Project</label>
                        <input type="text" name="project" id="project" class="form-control" placeholder="Enter the project name" >
                    </div>
                    <div class="mb-2 position-relative">
                        <label class="form-label">Office</label>
                        <input type="text" name="office" id="office" class="form-control" placeholder="Enter or select a office" autocomplete="off">
                        <div id="officeSuggestions" class="suggestions-dropdown"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Date Out</label>
                        <input type="date" name="date_out" id="date_out" class="form-control">
                    </div>
                    <div class="mb-2 position-relative">
                        <label class="form-label">Claimed By</label>
                        <input type="text" name="claimed_by" id="claimed_by" class="form-control" placeholder="Claimed By." autocomplete="off">
                        <div id="claimedBySuggestions" class="suggestions-dropdown"></div>
                    </div>

                    <div class="form-buttons">
                        <button type="reset" class="btn btn-custom" <?= !$canAccessCertificate ? 'disabled style="background:darkblue;color:#ffffff;opacity:0.8;cursor:not-allowed;"' : '' ?>>
                            <i class="bi bi-plus-circle"></i> New
                        </button>
                        <button type="submit" name="save" class="btn btn-custom" <?= !$canAccessCertificate ? 'disabled style="background:darkblue;color:#ffffff;opacity:0.8;cursor:not-allowed;"' : '' ?>>
                            <i class="bi bi-save"></i> Save
                        </button>
                        <!-- <button type="button" id="deleteBtn" class="btn btn-danger" <?= !$canAccessCertificate ? 'disabled style="background:red;color:#ffffff;opacity:0.8;cursor:not-allowed;"' : '' ?>>
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
    document.addEventListener("DOMContentLoaded", () => {
        const certificateForm = document.getElementById("certificateForm");

        certificateForm.addEventListener("submit", function(e) {
            // Check if any row checkboxes are selected
            const selectedRows = document.querySelectorAll('.rowCheckbox:checked');

            if (selectedRows.length > 0) {
                // Prevent form submission
                e.preventDefault();

                // Show warning popup
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Action',
                    text: 'You cannot create a new certificate record while selecting existing records in the table.',
                    confirmButtonColor: '#1e3a8a'
                });
            }
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
    document.addEventListener("DOMContentLoaded", () => {
        const officeInput = document.getElementById("office");
        const officeSuggestions = document.getElementById("officeSuggestions");

        const claimedByInput = document.getElementById("claimed_by");
        const claimedBySuggestions = document.getElementById("claimedBySuggestions");

        // Fetch DISTINCT values from certificate_records
        const officeList = [
            <?php
            $res = $conn->query("SELECT DISTINCT office FROM certificate_records WHERE office IS NOT NULL ORDER BY office ASC");
            $arr = [];
            while ($row = $res->fetch_assoc()) {
                $arr[] = '"' . addslashes($row['office']) . '"';
            }
            echo implode(',', $arr);
            ?>
        ];

        const claimedByList = [
            <?php
            $res = $conn->query("SELECT DISTINCT claimed_by FROM certificate_records WHERE claimed_by IS NOT NULL ORDER BY claimed_by ASC");
            $arr = [];
            while ($row = $res->fetch_assoc()) {
                $arr[] = '"' . addslashes($row['claimed_by']) . '"';
            }
            echo implode(',', $arr);
            ?>
        ];

        // Reusable Autocomplete Function
        function setupAutocomplete(input, suggestions, list) {
            function show(filtered) {
                suggestions.innerHTML = '';
                if (filtered.length === 0) {
                    suggestions.style.display = 'none';
                    return;
                }
                filtered.forEach(item => {
                    const div = document.createElement('div');
                    div.textContent = item;
                    div.addEventListener('click', () => {
                        input.value = item;
                        suggestions.style.display = 'none';
                    });
                    suggestions.appendChild(div);
                });
                suggestions.style.display = 'block';
            }

            function filterAndShow() {
                const value = input.value.toLowerCase().trim();
                const filtered = list.filter(d => d.toLowerCase().includes(value));
                show(filtered);
            }

            input.addEventListener('input', filterAndShow);
            input.addEventListener('focus', filterAndShow);

            document.addEventListener('click', e => {
                if (!input.contains(e.target) && !suggestions.contains(e.target)) {
                    suggestions.style.display = 'none';
                }
            });
        }

        // Apply Autocomplete
        setupAutocomplete(officeInput, officeSuggestions, officeList);
        setupAutocomplete(claimedByInput, claimedBySuggestions, claimedByList);
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

    // Show success message after DELETE
    <?php if (isset($_SESSION['delete_success']) && $_SESSION['delete_success'] === true): ?>
        Swal.fire({
            icon: 'success',
            title: 'Deleted Successfully!',
            text: 'Selected record(s) have been removed.',
            confirmButtonColor: '#d33'
        });
        <?php unset($_SESSION['delete_success']); ?>
    <?php endif; ?>

    // ❌ If delete failed
    <?php if (isset($_SESSION['delete_error']) && $_SESSION['delete_error'] === true): ?>
        Swal.fire({
            icon: 'error',
            title: 'Delete Failed!',
            text: 'Something went wrong while deleting.',
            confirmButtonColor: '#1e3a8a'
        });
        <?php unset($_SESSION['delete_error']); ?>
    <?php endif; ?>
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const payeeInput = document.getElementById("payee");
    const suggestionsContainer = document.getElementById("payeeSuggestions");

    // Fetch all payee from PHP
    const payees = [
        <?php
        $payeeQuery = $conn->query("SELECT DISTINCT payee FROM voucher_records ORDER BY payee ASC");
        $payeeArr = [];
        while ($row = $payeeQuery->fetch_assoc()) {
            $payee = trim($row['payee']);
            if (!empty($payee)) {
                $payeeArr[] = '"' . addslashes($payee) . '"';
            }
        }
        echo implode(',', $payeeArr);
        ?>
    ];

    // Function: Build dropdown items
    function showSuggestions(filtered) {
        suggestionsContainer.innerHTML = "";

        if (filtered.length === 0) {
            suggestionsContainer.style.display = "none";
            return;
        }

        filtered.forEach(name => {
            const div = document.createElement("div");
            div.textContent = name;
            div.addEventListener("click", () => {
                payeeInput.value = name;
                suggestionsContainer.style.display = "none";
            });
            suggestionsContainer.appendChild(div);
        });

        suggestionsContainer.style.display = "block";
    }

    // Event: Typing
    payeeInput.addEventListener("input", () => {
        const val = payeeInput.value.toLowerCase();
        const filtered = payees.filter(name => name.toLowerCase().includes(val));
        showSuggestions(filtered);
    });

    // Event: When input is clicked
    payeeInput.addEventListener("focus", () => {
        showSuggestions(payees);
    });

    // Close if clicked outside
    document.addEventListener("click", e => {
        if (!suggestionsContainer.contains(e.target) && e.target !== payeeInput) {
            suggestionsContainer.style.display = "none";
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

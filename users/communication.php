<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$ws = $conn->query("SELECT logo FROM website_settings WHERE id=1")->fetch_assoc();
$site_logo = $ws ? $ws['logo'] : 'assets/images/default-logo.png';

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

$indorseCount = 0;

$indorseStmt = $conn->prepare("
    SELECT COUNT(*) AS total 
    FROM communications 
    WHERE indorse_to = ?
    AND (action_taken IS NULL OR action_taken = '')
");
$indorseStmt->bind_param("s", $full_name);
$indorseStmt->execute();
$indorseCount = $indorseStmt->get_result()->fetch_assoc()['total'] ?? 0;

// Fetch indorsed records for this user
$indorsedRecordsQuery = $conn->prepare("
    SELECT *,
           DATE_FORMAT(indorsed_action_at, '%M %d, %Y %h:%i %p') as action_taken_formatted
    FROM communications
    WHERE indorse_to = ?
    ORDER BY date_routed DESC
");
$indorsedRecordsQuery->bind_param("s", $full_name);
$indorsedRecordsQuery->execute();
$indorsedRecords = $indorsedRecordsQuery->get_result();

// Decode permissions JSON into array
$user_permissions = [];
if (!empty($user['permissions'])) {
    $user_permissions = json_decode($user['permissions'], true);
}

// Check if user has voucher_records permission
$canAccessCommunication = in_array("communications_records", $user_permissions);
$canDeleteCommunication = in_array("delete_records", $user_permissions);

// Pagination setup
$limit = 10; // records per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Count total records
$countQuery = $conn->prepare("SELECT COUNT(*) AS total FROM communications WHERE user_id = ?");
$countQuery->bind_param("i", $user_id);
$countQuery->execute();
$totalResult = $countQuery->get_result()->fetch_assoc();
$totalRecords = $totalResult['total'];
$totalPages = ceil($totalRecords / $limit);

// Fetch paginated records
$recordsQuery = $conn->prepare("SELECT * FROM communications WHERE user_id = ? ORDER BY id DESC LIMIT ? OFFSET ?");
$recordsQuery->bind_param("iii", $user_id, $limit, $offset);
$recordsQuery->execute();
$records = $recordsQuery->get_result();

// Get the next ComID
$nextComID = 1; // default if table is empty
$comQuery = $conn->prepare("SELECT com_id FROM communications WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$comQuery->bind_param("i", $user_id);
$comQuery->execute();
$comResult = $comQuery->get_result();
if ($comResult->num_rows > 0) {
    $lastCom = $comResult->fetch_assoc();
    $nextComID = intval($lastCom['com_id']) + 1;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Communication Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/users/communication.css">
</head>

<body>

    <!-- HEADER -->
    <div class="header">
        <?php
        $ws = $conn->query("SELECT logo FROM website_settings WHERE id=1")->fetch_assoc();
        $site_logo = $ws ? $ws['logo'] : 'assets/images/default-logo.png';
        ?>
        <img src="../<?= $site_logo ?>"
            alt="Website Logo" class="logo">
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
                        <li><a href="../users/profile.php"><i class="fas fa-user"></i> Profile</a></li>
                        <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="main-container">
        <div class="row g-4">

            <!-- LEFT SIDE: Table -->
            <div class="col-lg-8 left-panel">
                <div class="card h-100">
                    <div class="card-header ok-card">
                        <div class="left">
                            <i class="bi bi-table"></i>
                            Communication Records
                        </div>
                        <div class="right">
                            <div class="tools-column">
                                <div class="search-container">
                                    <input type="text" id="tableSearch" placeholder="Search records..." />
                                    <button id="searchBtn">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>

                                <button id="showIndorseBtn" class="btn-indorse position-relative">
                                    <i class="bi bi-send-check"></i>
                                    <span>Show Indorsed</span>

                                    <?php if ($indorseCount > 0): ?>
                                        <span class="indorse-badge">
                                            <?= $indorseCount ?>
                                        </span>
                                    <?php endif; ?>
                                </button>
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

                        <!-- Table -->
                        <div id="mainTableContainer">
                            <form method="POST" action="Controllers/CommunicationController.php">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="recordsTable">
                                        <thead>
                                            <tr>
                                                <th class="checkbox-cell"><input type="checkbox" id="selectAll"></th>
                                                <th>ComID</th>
                                                <th>Date Received</th>
                                                <th>Sender</th>
                                                <th>Description</th>
                                                <th>Indorse To</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            include '../db.php';
                                            $records = $conn->query("SELECT * FROM communications");

                                            if ($records->num_rows > 0) {
                                                while ($row = $records->fetch_assoc()) {
                                                    $data = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                                                    echo "<tr class='text-center' data-row='{$data}'>
                                                <td><input type='checkbox' class='rowCheckbox' name='delete_ids[]' value='{$row['id']}'></td>
                                                <td>{$row['com_id']}</td>
                                                <td>" . date("M d, Y", strtotime($row['date_received'])) . "</td>
                                                <td>{$row['sender']}</td>
                                                <td>{$row['description']}</td>
                                                <td>{$row['indorse_to']}</td>
                                            </tr>";
                                                }
                                            } else {
                                                echo "<tr>
                                            <td colspan='6' class='no-records'>
                                            <i class='bi bi-inbox'></i>
                                            <div>No records found</div>
                                            </td>
                                        </tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                        </div>
                        <!-- SHOW INDORSED TABLE  -->
                        <!-- SHOW INDORSED TABLE -->
                        <div id="indorsedTableContainer" style="display: none;">
                            <table class="table table-hover align-middle text-center">
                                <thead>
                                    <tr>
                                        <th class="checkbox-cell"><input type="checkbox" id="selectAll"></th>
                                        <th>ComID</th>
                                        <th>Indorse To</th>
                                        <th>Date Routed</th>
                                        <th>Routed By</th>
                                        <th>Action</th>
                                        <th>Remarks</th>
                                        <th>Action Duration</th> <!-- NEW COLUMN -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($indorsedRecords->num_rows > 0): ?>
                                        <?php
                                        // Re-fetch with duration calculation
                                        $indorsedWithDurationQuery = $conn->prepare("
                    SELECT *,
                           DATE_FORMAT(indorsed_action_at, '%M %d, %Y %h:%i %p') as action_taken_formatted,
                           action_duration
                    FROM communications
                    WHERE indorse_to = ?
                    ORDER BY date_routed DESC
                ");
                                        $indorsedWithDurationQuery->bind_param("s", $full_name);
                                        $indorsedWithDurationQuery->execute();
                                        $indorsedRecordsWithDuration = $indorsedWithDurationQuery->get_result();

                                        while ($row = $indorsedRecordsWithDuration->fetch_assoc()):
                                        ?>
                                            <tr class="text-center" data-row='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>'>
                                                <td><input type='checkbox' class='rowCheckbox' name='delete_ids[]' value='<?= $row['id'] ?>'></td>
                                                <td><?= htmlspecialchars($row['com_id']) ?></td>
                                                <td><?= htmlspecialchars($row['indorse_to']) ?></td>
                                                <td>
                                                    <?php
                                                    if (!empty($row['date_routed'])) {
                                                        echo date("M d, Y", strtotime($row['date_routed']));
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?= htmlspecialchars($row['routed_by'] ?: '-') ?></td>
                                                <td><?= htmlspecialchars($row['action_taken'] ?: '-') ?></td>
                                                <td><?= htmlspecialchars($row['remarks'] ?: '-') ?></td>
                                                <td>
                                                    <?php
                                                    if (!empty($row['action_duration'])) {
                                                        echo htmlspecialchars($row['action_duration']);
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted"> <!-- Updated colspan to 9 -->
                                                <i class="bi bi-inbox"></i> No indorsed records found
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>



            <!-- RIGHT SIDE: Forms -->
            <div class="col-lg-4 right-panel">
                <form method="POST" action="Controllers/CommunicationController.php">
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="is_from_indorsed" id="is_from_indorsed" value="0">

                    <!-- IN Form -->
                    <div class="card form-section">
                        <div class="card-header">
                            <i class="bi bi-inbox-fill"></i>
                            IN Form
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">ComID</label>
                                <input type="text" name="com_id" id="com_id" class="form-control" readonly
                                    style="background:#e9ecef; cursor:not-allowed;" value="<?php echo $nextComID; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date Received</label>
                                <input type="date" name="date_received" id="date_received" class="form-control" required>
                            </div>
                            <div class="mb-3 position-relative">
                                <label class="form-label">Sender</label>
                                <input type="text" name="sender" id="sender" class="form-control" placeholder="Enter the sender name" autocomplete="off" required>
                                <div id="senderSuggestions" class="suggestions-dropdown"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="3" placeholder="Enter description" required></textarea>
                            </div>
                            <div class="form-buttons" style="flex-wrap: nowrap;">
                                <button type="reset" class="btn btn-custom" <?= !$canAccessCommunication ? 'disabled style="background:darkblue;color:#ffffff;opacity:0.8;cursor:not-allowed;"' : '' ?>>
                                    <i class="bi bi-plus-circle"></i> New
                                </button>
                                <button type="submit" name="save" class="btn btn-custom" <?= !$canAccessCommunication ? 'disabled style="background:darkblue;color:#ffffff;opacity:0.8;cursor:not-allowed;"' : '' ?>>
                                    <i class="bi bi-save"></i> Save
                                </button>
                                <button type="button" onclick="printSlip()" class="btn btn-custom" <?= !$canAccessCommunication ? 'disabled style="background:darkblue;color:#ffffff;opacity:0.8;cursor:not-allowed;"' : '' ?>>
                                    <i class="bi bi-printer"></i> Print
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- OUT Form -->
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-send-fill"></i>
                            OUT Form
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label href="" style="font-weight: bold; color: darkblue;">ComID:</label>
                            </div>
                            <div class="mb-3 position-relative">
                                <label class="form-label">Indorse To</label>
                                <input type="text" name="indorse_to" id="indorse_to" class="form-control" placeholder="Enter recipient" autocomplete="off">
                                <div id="indorseToSuggestions" class="suggestions-dropdown"></div>
                            </div>

                            <input type="hidden" name="date_routed" id="date_routed_hidden">
                            <div class="mb-3">
                                <label class="form-label">Date Routed</label>
                                <input type="date" name="date_routed" id="date_routed" class="form-control">
                            </div>
                            <div class="mb-2 position-relative">
                                <label class="form-label">Routed by</label>
                                <input type="text" name="routed_by" id="routed_by" class="form-control" placeholder="Enter or select routed by" autocomplete="off">
                                <div id="routedBySuggestions" class="suggestions-dropdown"></div>
                            </div>
                            <div class="mb-2 position-relative">
                                <label class="form-label">Action</label>
                                <input type="text" name="action" id="action" class="form-control" placeholder="Enter or select actions" autocomplete="off">
                                <div id="actionSuggestions" class="suggestions-dropdown"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" id="remarks" class="form-control" rows="3" placeholder="Enter remarks"></textarea>
                            </div>
                            <div class="form-buttons">
                                <button type="submit" name="save_edit" class="btn btn-custom" <?= !$canAccessCommunication ? 'disabled style="background:darkblue;color:#ffffff;opacity:0.8;"' : '' ?>>
                                    <i class="bi bi-pencil-square"></i> Save Edit
                                </button>

                                <button type="button" onclick="deleteSelected()" class="btn btn-danger delete-btn" <?= !$canDeleteCommunication ? 'disabled' : '' ?>>
                                    <i class="bi bi-trash"></i> Delete
                                </button>

                                <button onclick="window.location.href='../index.php'" type="button" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Close
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/users/communication.js"></script>
    <script src="../date.js"></script>
    <script>
        const siteLogo = "<?= $site_logo ?>";
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
            const rows = Array.from(document.querySelectorAll("#recordsTable tbody tr"))
                .filter(row => !row.classList.contains("no-records"));
            const selectAllCheckbox = document.getElementById("selectAll");
            const pageInfo = document.querySelector(".page-info");
            const prevBtn = document.querySelector(".pagination-controls .btn-outline-secondary:first-child");
            const nextBtn = document.querySelector(".pagination-controls .btn-outline-secondary:last-child");
            const rowsPerPage = 20; // ✅ You can change this value
            let currentPage = 1;

            // ===== PAGINATION LOGIC =====
            function renderTablePage() {
                const totalRecords = rows.length;
                const totalPages = Math.ceil(totalRecords / rowsPerPage) || 1;
                const start = (currentPage - 1) * rowsPerPage;
                const end = start + rowsPerPage;

                rows.forEach((row, index) => {
                    row.style.display = index >= start && index < end ? "" : "none";
                });

                // Update dynamic pagination info
                pageInfo.innerHTML = `Page <strong>${currentPage}</strong> of <strong>${totalPages}</strong> (${totalRecords} record${totalRecords !== 1 ? "s" : ""})`;

                // Enable/disable buttons
                prevBtn.classList.toggle("control-disabled", currentPage === 1);
                nextBtn.classList.toggle("control-disabled", currentPage === totalPages);
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

            renderTablePage(); // ✅ Initial render

            // ===== CHECKBOX LOGIC =====
            selectAllCheckbox.addEventListener("change", function() {
                const visibleRows = rows.filter(row => row.style.display !== "none");
                visibleRows.forEach(row => {
                    const cb = row.querySelector(".rowCheckbox");
                    if (cb) cb.checked = this.checked;
                });
            });

            // ===== ROW CLICK CHECKBOX SELECT =====
            rows.forEach(row => {
                const checkbox = row.querySelector(".rowCheckbox");
                if (!checkbox) return;

                row.addEventListener("click", function(e) {

                    if (e.target.type === "checkbox") return;
                    checkbox.checked = !checkbox.checked;
                });
            });

            // ===== AUTO-UNCHECK "SELECT ALL" IF ANY UNCHECKED =====
            document.querySelectorAll(".rowCheckbox").forEach(cb => {
                cb.addEventListener("change", () => {
                    const visibleRows = rows.filter(row => row.style.display !== "none");
                    const allChecked = visibleRows.every(r => r.querySelector(".rowCheckbox").checked);
                    selectAllCheckbox.checked = allChecked;
                });
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // ✅ Success alerts
            <?php if (isset($_SESSION['save_success'])): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: 'New communication record added.'
                });
                <?php unset($_SESSION['save_success']); ?>
            <?php elseif (isset($_SESSION['edit_success'])): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: 'OUT Form details saved successfully.'
                });
                <?php unset($_SESSION['edit_success']); ?>
            <?php elseif (isset($_SESSION['delete_success'])): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: 'Selected record(s) deleted successfully.'
                });
                <?php unset($_SESSION['delete_success']); ?>
            <?php elseif (isset($_SESSION['delete_error'])): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Delete Failed',
                    text: 'No records were deleted. Please try again.'
                });
                <?php unset($_SESSION['delete_error']); ?>
            <?php endif; ?>

            // ---------------------------
            // Function to setup table checkboxes + row click selection
            // ---------------------------
            function setupTable(tableId) {
                const table = document.getElementById(tableId);
                if (!table) return;

                const selectAll = table.querySelector("thead input[type='checkbox']");
                const rows = Array.from(table.querySelectorAll("tbody tr"))
                    .filter(row => !row.classList.contains("no-records"));

                // Helper: update select-all checkbox
                function updateSelectAll() {
                    if (!selectAll) return;
                    const allChecked = rows.every(r => r.querySelector(".rowCheckbox")?.checked);
                    selectAll.checked = allChecked;
                }

                // Row click + checkbox logic
                rows.forEach(row => {
                    const cb = row.querySelector(".rowCheckbox");

                    // Row click
                    row.addEventListener("click", e => {
                        if (e.target.type === "checkbox") return;
                        if (cb) cb.checked = !cb.checked;
                        updateSelectAll();

                        if (row.dataset.row) {
                            const data = JSON.parse(row.dataset.row);
                            const comIdLabel = document.querySelector('.card-body label[href=""]');

                            document.getElementById("id").value = data.id || '';
                            document.getElementById("com_id").value = data.com_id || '';
                            document.getElementById("date_received").value = data.date_received || '';
                            document.getElementById("sender").value = data.sender || '';
                            document.getElementById("description").value = data.description || '';
                            document.getElementById("indorse_to").value = data.indorse_to || '';
                            document.getElementById("date_routed").value = data.date_routed || '';
                            document.getElementById("routed_by").value = data.routed_by || '';
                            document.getElementById("action").value = data.action_taken || '';
                            document.getElementById("remarks").value = data.remarks || '';

                            // Highlight selected row
                            rows.forEach(r => r.classList.remove("selected"));
                            row.classList.add("selected");

                            if (comIdLabel) comIdLabel.textContent = 'ComID: ' + (data.com_id || '');
                        }
                    });

                    if (cb) {
                        cb.addEventListener("change", updateSelectAll);
                    }
                });

                // Select/unselect all checkboxes
                if (selectAll) {
                    selectAll.addEventListener("change", () => {
                        rows.forEach(r => {
                            const cb = r.querySelector(".rowCheckbox");
                            if (cb) cb.checked = selectAll.checked;
                        });
                    });
                }
            }

            // Apply to both tables
            setupTable("recordsTable");
            setupTable("indorsedTableContainer");
        });
    </script>

    <script>
        function deleteSelected() {
            // Check if user has delete permission (PHP variable)
            <?php if (!$canDeleteCommunication): ?>
                Swal.fire({
                    icon: "warning",
                    title: "Permission Denied",
                    text: "You don't have permission to delete communication records.",
                    confirmButtonColor: "#3085d6"
                });
                return;
            <?php endif; ?>

            const selectedCheckboxes = document.querySelectorAll(".rowCheckbox:checked");
            if (selectedCheckboxes.length === 0) {
                Swal.fire("No record selected", "Please select at least one row to delete.", "info");
                return;
            }

            let ids = [];
            let controlNums = [];

            selectedCheckboxes.forEach(cb => {
                ids.push(cb.value);
                let controlNum = cb.closest("tr").children[1].textContent.trim();
                controlNums.push(controlNum);
            });

            Swal.fire({
                title: 'Confirm Delete',
                html: `<p>Control No.</p><b>${controlNums.join(", ")}</b>`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#d33"
            }).then(result => {
                if (result.isConfirmed) {
                    // Create a FormData to send array properly
                    const formData = new FormData();
                    formData.append('delete_selected', 1);
                    ids.forEach(id => formData.append('delete_ids[]', id));

                    fetch("Controllers/CommunicationController.php", {
                            method: "POST",
                            body: formData
                        })
                        .then(res => res.text())
                        .then(() => location.reload())
                        .catch(() => Swal.fire("Error", "Request failed.", "error"));
                }
            });
        }
    </script>

    <script>
        // ✅ Show success message after saving
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

    <script>
        document.querySelector('.btn-custom[type="reset"]').addEventListener('click', function(e) {
            e.preventDefault(); // Prevent form reset / page reload

            // Reset form fields
            document.getElementById('id').value = '';
            document.getElementById('date_received').value = '';
            document.getElementById('sender').value = '';
            document.getElementById('description').value = '';
            document.getElementById('indorse_to').value = '';
            document.getElementById('date_routed').value = '';
            document.getElementById('action').value = ''; // <-- Fixed ID
            document.getElementById('remarks').value = '';

            // Set ComID to next value
            const comIdInput = document.getElementById('com_id');
            const lastComId = <?php echo $nextComID - 1; ?>;
            comIdInput.value = lastComId + 1;

            // Show message
            let existingMsg = document.getElementById('newFormMsg');
            if (!existingMsg) {
                const label = document.createElement('div');
                label.id = 'newFormMsg';
                label.textContent = 'Enter a new communication record.';
                label.style.color = 'darkblue';
                label.style.fontWeight = '600';
                label.style.textAlign = 'center';
                label.style.marginBottom = '8px';

                // Insert above the ComID div
                const comDiv = comIdInput.closest('.mb-3');
                comDiv.parentNode.insertBefore(label, comDiv);
            }
        });
    </script>

    <script>
        function attachCheckboxAndRowLogic() {
            const rows = document.querySelectorAll("#recordsTable tbody tr");
            const selectAll = document.getElementById("selectAll");

            // Select All
            if (selectAll) {
                selectAll.addEventListener("change", function() {
                    rows.forEach(r => {
                        const cb = r.querySelector(".rowCheckbox");
                        if (cb) cb.checked = this.checked;
                    });
                });
            }

            rows.forEach(row => {
                const cb = row.querySelector(".rowCheckbox");

                row.addEventListener("click", function(e) {
                    if (e.target.type === "checkbox") return;

                    if (cb) cb.checked = !cb.checked;

                    rows.forEach(r => r.classList.remove("selected"));
                    row.classList.add("selected");

                    // Fill OUT Form
                    let data = {};
                    if (row.dataset.row) {
                        try {
                            data = JSON.parse(row.dataset.row);
                        } catch (err) {}
                    }

                    document.getElementById("id").value = data.id || '';
                    document.getElementById("com_id").value = data.com_id || '';
                    document.getElementById("date_received").value = data.date_received || '';
                    document.getElementById("sender").value = data.sender || '';
                    document.getElementById("description").value = data.description || '';
                    document.getElementById("indorse_to").value = data.indorse_to || '';
                    document.getElementById("date_routed").value = data.date_routed || '';
                    document.getElementById("routed_by").value = data.routed_by || '';
                    document.getElementById("action").value = data.action_taken || '';
                    document.getElementById("remarks").value = data.remarks || '';
                });
            });
        }

        document.addEventListener("DOMContentLoaded", attachCheckboxAndRowLogic);
    </script>


    <script>
        // Select all checkboxes
        document.getElementById('selectAll').addEventListener('change', function() {
            document.querySelectorAll('.rowCheckbox').forEach(cb => cb.checked = this.checked);
        });

        // Fill form when row clicked
        document.querySelectorAll('#recordsTable tbody tr').forEach(row => {
            row.addEventListener('click', function(e) {
                if (e.target.tagName.toLowerCase() === 'input') return;
                if (!this.dataset.row) return;

                // Remove previous selection
                document.querySelectorAll('#recordsTable tbody tr').forEach(r => r.classList.remove('selected'));
                this.classList.add('selected');

                let data = JSON.parse(this.dataset.row);
                document.getElementById('id').value = data.id;
                document.getElementById('com_id').value = data.com_id;
                document.getElementById('date_received').value = data.date_received;
                document.getElementById('sender').value = data.sender;
                document.getElementById('description').value = data.description;
                document.getElementById('indorse_to').value = data.indorse_to;
                document.getElementById('date_routed').value = data.date_routed;
                document.getElementById('action_taken').value = data.action_taken;
                document.getElementById('remarks').value = data.remarks;
            });
        });

        document.addEventListener("DOMContentLoaded", () => {
            const indorsedRows = document.querySelectorAll("#indorsedTableContainer tbody tr");
            const comIdLabel = document.querySelector('.card-body label[href=""]');

            indorsedRows.forEach(row => {
                row.addEventListener("click", (e) => {
                    // Ignore clicks on checkbox
                    if (e.target.type === "checkbox") return;

                    // Remove previous selection
                    document.querySelectorAll("#indorsedTableContainer tbody tr").forEach(r => r.classList.remove("selected"));
                    row.classList.add("selected");

                    // Parse row data
                    if (!row.dataset.row) return;
                    const data = JSON.parse(row.dataset.row);

                    // Fill IN Form
                    document.getElementById("id").value = data.id || '';
                    document.getElementById("com_id").value = data.com_id || '';
                    document.getElementById("date_received").value = data.date_received || '';
                    document.getElementById("sender").value = data.sender || '';
                    document.getElementById("description").value = data.description || '';

                    // Fill OUT Form
                    document.getElementById("indorse_to").value = data.indorse_to || '';
                    document.getElementById("date_routed").value = data.date_routed || '';
                    document.getElementById("routed_by").value = data.routed_by || '';
                    document.getElementById("action").value = data.action_taken || '';
                    document.getElementById("remarks").value = data.remarks || '';

                    // Show ComID on OUT form
                    comIdLabel.textContent = 'ComID: ' + (data.com_id || '');
                });
            });
        });

        const indorseSelectAll = document.querySelector("#indorsedTableContainer #selectAll");
        if (indorseSelectAll) {
            indorseSelectAll.addEventListener("change", function() {
                indorsedRows.forEach(row => {
                    const cb = row.querySelector(".rowCheckbox");
                    if (cb) cb.checked = this.checked;
                });
            });
        }
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const saveEditBtn = document.querySelector('button[name="save_edit"]');
            const saveBtn = document.querySelector('button[name="save"]');
            const rowCheckboxes = document.querySelectorAll('.rowCheckbox');

            saveEditBtn.addEventListener('click', function(e) {
                // Count how many rows are checked
                const checkedCount = Array.from(rowCheckboxes).filter(cb => cb.checked).length;

                if (checkedCount !== 1) {
                    e.preventDefault(); // stop form submission
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Selection',
                        text: 'Please select exactly one record to edit in the OUT Form.',
                    });
                }
            });

            // Prevent IN Form save if any row is selected
            saveBtn.addEventListener('click', function(e) {
                const checkedCount = Array.from(rowCheckboxes).filter(cb => cb.checked).length;
                if (checkedCount > 0) {
                    e.preventDefault(); // stop form submission
                    Swal.fire({
                        icon: 'warning',
                        title: 'Cannot Save',
                        text: 'Please deselect all records before adding a new IN record.',
                    });
                }
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const routedByInput = document.getElementById("routed_by");
            const routedBySuggestions = document.getElementById("routedBySuggestions");

            const actionInput = document.getElementById("action");
            const actionSuggestions = document.getElementById("actionSuggestions");

            const senderInput = document.getElementById("sender");
            const senderSuggestions = document.getElementById("senderSuggestions");

            const indorseInput = document.getElementById("indorse_to");
            const indorseSuggestions = document.getElementById("indorseToSuggestions");

            const remarksInput = document.getElementById("remarks");
            const showIndorseBtn = document.getElementById("showIndorseBtn");
            const mainTable = document.getElementById("mainTableContainer");
            const indorsedTable = document.getElementById("indorsedTableContainer");
            const isFromIndorsedInput = document.getElementById("is_from_indorsed"); // Get the hidden input

            // Track current mode
            let isIndorseMode = false;

            // Function to enable OUT form editing (for Show Indorsed mode)
            function enableOutForm() {
                [routedByInput, actionInput, remarksInput].forEach(input => {
                    input.removeAttribute("readonly");
                    input.style.backgroundColor = "#fff";
                    input.style.cursor = "text";
                    input.style.opacity = "1";
                    input.style.pointerEvents = "auto";
                    input.classList.remove("out-disabled");
                });

                // Update hidden input
                if (isFromIndorsedInput) {
                    isFromIndorsedInput.value = "1";
                }
            }

            // Function to disable OUT form editing (for Main Table mode)
            function disableOutForm() {
                [routedByInput, actionInput, remarksInput].forEach(input => {
                    input.setAttribute("readonly", "readonly");
                    input.style.backgroundColor = "#e9ecef";
                    input.style.cursor = "not-allowed";
                    input.style.opacity = "0.7";
                    input.style.pointerEvents = "none";
                    input.classList.add("out-disabled");
                });

                // Update hidden input
                if (isFromIndorsedInput) {
                    isFromIndorsedInput.value = "0";
                }
            }

            // Initialize as disabled (Main Table mode)
            disableOutForm();

            showIndorseBtn.addEventListener("click", () => {
                const isHidden = indorsedTable.style.display === "none";

                if (isHidden) {
                    // Switch to Show Indorsed mode
                    mainTable.style.display = "none";
                    indorsedTable.style.display = "block";
                    showIndorseBtn.innerHTML = `<i class="bi bi-arrow-left"></i> <span>Go Back</span>`;
                    isIndorseMode = true;
                    enableOutForm(); // Enable editing

                } else {
                    // Switch back to Main Table mode
                    mainTable.style.display = "block";
                    indorsedTable.style.display = "none";
                    showIndorseBtn.innerHTML = `<i class="bi bi-send-check"></i> <span>Show Indorsed</span>`;
                    isIndorseMode = false;
                    disableOutForm(); // Disable editing
                }
            });

            // Fetch DISTINCT values from DB
            const routedByList = [
                <?php
                $res = $conn->query("SELECT DISTINCT routed_by FROM communications WHERE routed_by IS NOT NULL ORDER BY routed_by ASC");
                $arr = [];
                while ($row = $res->fetch_assoc()) {
                    $arr[] = '"' . addslashes($row['routed_by']) . '"';
                }
                echo implode(',', $arr);
                ?>
            ];

            const actionList = [
                <?php
                $res = $conn->query("SELECT DISTINCT action_taken FROM communications WHERE action_taken IS NOT NULL ORDER BY action_taken ASC");
                $arr = [];
                while ($row = $res->fetch_assoc()) {
                    $arr[] = '"' . addslashes($row['action_taken']) . '"';
                }
                echo implode(',', $arr);
                ?>
            ];

            const senderList = [
                <?php
                $res = $conn->query("SELECT DISTINCT sender FROM communications WHERE sender IS NOT NULL ORDER BY sender ASC");
                $arr = [];
                while ($row = $res->fetch_assoc()) {
                    $arr[] = '"' . addslashes($row['sender']) . '"';
                }
                echo implode(',', $arr);
                ?>
            ];

            const indorseToList = [
                <?php
                $res = $conn->query("SELECT first_name, middle_initial, last_name FROM users WHERE role != 'admin' ORDER BY first_name ASC");
                $arr = [];
                while ($row = $res->fetch_assoc()) {
                    $full_name = trim(
                        $row['first_name'] . ' ' .
                            (!empty($row['middle_initial']) ? strtoupper(substr($row['middle_initial'], 0, 1)) . '. ' : '') .
                            $row['last_name']
                    );
                    $arr[] = '"' . addslashes($full_name) . '"';
                }
                echo implode(',', $arr);
                ?>
            ];

            // Reusable Autocomplete Function
            function setupAutocomplete(input, suggestions, list) {
                function show(filtered) {
                    // Hide suggestions if field is disabled AND not in Indorse mode
                    if (!isIndorseMode && (input.id === "routed_by" || input.id === "action")) {
                        suggestions.style.display = 'none';
                        return;
                    }

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
                    // Don't show suggestions if field is disabled AND not in Indorse mode
                    if (!isIndorseMode && (input.id === "routed_by" || input.id === "action")) {
                        suggestions.style.display = 'none';
                        return;
                    }

                    const value = input.value.toLowerCase().trim();
                    const filtered = list.filter(d => d.toLowerCase().includes(value));
                    show(filtered);
                }

                input.addEventListener('input', filterAndShow);

                input.addEventListener('focus', function() {
                    // Only show on focus if in Indorse mode for routed_by and action fields
                    if (!isIndorseMode && (input.id === "routed_by" || input.id === "action")) {
                        return;
                    }
                    filterAndShow();
                });

                document.addEventListener('click', e => {
                    if (!input.contains(e.target) && !suggestions.contains(e.target)) {
                        suggestions.style.display = 'none';
                    }
                });
            }

            document.addEventListener("click", function(e) {
                if (e.target.closest("#indorsedTableContainer tbody tr")) {
                    if (isFromIndorsedInput) {
                        isFromIndorsedInput.value = "1";
                    }
                }

                if (e.target.closest("#recordsTable tbody tr")) {
                    if (isFromIndorsedInput) {
                        isFromIndorsedInput.value = "0";
                    }
                }
            });

            // Apply Autocomplete
            setupAutocomplete(routedByInput, routedBySuggestions, routedByList);
            setupAutocomplete(actionInput, actionSuggestions, actionList);
            setupAutocomplete(senderInput, senderSuggestions, senderList);
            setupAutocomplete(indorseInput, indorseSuggestions, indorseToList);

            // Update the autocomplete behavior when mode changes
            const originalBtnClick = showIndorseBtn.onclick;
            showIndorseBtn.addEventListener("click", function() {
                // Clear any open suggestions when switching modes
                [routedBySuggestions, actionSuggestions, senderSuggestions, indorseSuggestions].forEach(suggestions => {
                    if (suggestions) suggestions.style.display = 'none';
                });
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

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const dateRoutedDisplay = document.getElementById('date_routed_display');
            const dateRoutedHidden = document.getElementById('date_routed_hidden');

            // When a row is clicked from main table
            document.querySelectorAll('#recordsTable tbody tr').forEach(row => {
                row.addEventListener('click', function() {
                    if (!this.dataset.row) return;

                    const data = JSON.parse(this.dataset.row);
                    const routedDate = data.date_routed;

                    if (routedDate) {
                        // Format for display
                        dateRoutedDisplay.value = routedDate.includes(' ') ? routedDate.replace(' ', 'T') : routedDate + 'T00:00';
                        dateRoutedHidden.value = routedDate;
                    } else {
                        dateRoutedDisplay.value = '';
                        dateRoutedHidden.value = '';
                    }
                });
            });

            // When a row is clicked from indorsed table
            document.querySelectorAll('#indorsedTableContainer tbody tr').forEach(row => {
                row.addEventListener('click', function() {
                    if (!this.dataset.row) return;

                    const data = JSON.parse(this.dataset.row);
                    const routedDate = data.date_routed;

                    if (routedDate) {
                        // Format for display
                        dateRoutedDisplay.value = routedDate.includes(' ') ? routedDate.replace(' ', 'T') : routedDate + 'T00:00';
                        dateRoutedHidden.value = routedDate;
                    } else {
                        dateRoutedDisplay.value = '';
                        dateRoutedHidden.value = '';
                    }
                });
            });
        });
    </script>

    <script>
        let isIndorseMode = false;

        const LOCK_FIELDS = ["routed_by", "action", "remarks"];

        function applyOutFormMode() {
            LOCK_FIELDS.forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;

                if (isIndorseMode) {
                    el.removeAttribute("readonly");
                    el.classList.remove("out-disabled");
                    el.style.pointerEvents = "auto";
                } else {
                    el.setAttribute("readonly", true);
                    el.classList.add("out-disabled");
                    el.style.pointerEvents = "none";
                }
            });
        }

        function enterNormalMode() {
            isIndorseMode = false;
            applyOutFormMode();
        }

        function enterIndorseMode() {
            isIndorseMode = true;
            applyOutFormMode();
        }

        document.addEventListener("DOMContentLoaded", enterNormalMode);
    </script>
</body>

</html>
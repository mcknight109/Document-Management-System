<?php
session_start();
include "../db.php";
date_default_timezone_set('Asia/Manila');

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
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

// Decode permissions JSON into array
$user_permissions = [];
if (!empty($user['permissions'])) {
    $user_permissions = json_decode($user['permissions'], true);
}

// Check if user has voucher_records permission
$canAccessCheck = in_array("check_records", $user_permissions);

date_default_timezone_set('Asia/Manila');
$currentDate = date("M d, Y");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Records Management</title>
    <link rel="stylesheet" href="../assets/css/users/check_voucher.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
     alt="Website Logo" class="logo" >          <h1>Document Records Management System</h1>

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

    <div class="navbar">
        <div class="left">
            Check Records Management
        </div>
        <div class="right">
            <div class="search-container">
                <input type="text" id="tableSearch" placeholder="Search records..." />
                <button id="searchBtn"><i class="bi bi-search"></i></button>
            </div>
        </div>
    </div>

    <div class="main-content">
        <?php
        $query = $conn->prepare("SELECT * FROM documents WHERE user_id = ?");
        $query->bind_param("i", $userId);
        $query->execute();
        $result = $query->get_result();
        ?>
        <div class="table-container">
            <table id="recordsTable">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>Control No.</th>
                        <th>Payee</th>
                        <th>Bank Channel</th>
                        <th>Check Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>

                    <?php
                    $result = $conn->query("SELECT * FROM documents WHERE date_out IS NOT NULL");
                    while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><input type="checkbox" class="record-checkbox" value="<?= $row['id'] ?>"></td>
                            <td><?= htmlspecialchars($row['control_no']) ?></td>
                            <td><?= htmlspecialchars($row['payee']) ?></td>
                            <td><?= htmlspecialchars($row['bank_channel'] ?? '') ?></td>
                            <td><?= $row['check_date'] ? date('M d, Y h:i A', strtotime($row['check_date'])) : '-' ?></td>
                            <td><?= htmlspecialchars($row['status']) ?></td>
                            <td>
                                <?php if (!empty($row['check_date'])): ?>
                                    <button class="action-btn dateOutBtn" data-id="<?= $row['id'] ?>" title="Actions">
                                        <i class="bi bi-list-check"></i>
                                    </button>
                                <?php else: ?>
                                    <span style="color:#999; font-size:13px;">No actions</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div id="actionModal" class="action-modal">
                <ul>
                    <li id="checkoutOption"><i class="bi bi-box-arrow-right"></i> Check Out</li>
                    <li id="checkReleaseOption"><i class="bi bi-file-earmark-check"></i> Check Release</li>
                </ul>
            </div>
            <div class="footer">
                <button>&laquo;</button>
                <button>&lsaquo;</button>
                <button>&rsaquo;</button>
                <button>&raquo;</button>
            </div>
        </div>

        <div class="button-panel">
            <button onclick="openCheckRecord()" <?= !$canAccessCheck ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : '' ?>><i class="fa-solid fa-plus"></i> Check Record</button>
            <button onclick="printCheck()"><i class="fa-solid fa-print"></i> Check Print</button>
            <button onclick="printSelectedCheckTransmittal()">
                <i class="fa-solid fa-print"></i> Transmittal Print
            </button>
            <button onclick="window.location.href='../index.php'" style="background-color: #4b5563;"><i class="fa-solid fa-xmark"></i> Exit</button>
        </div>
    </div>

    <!-- Check Record Modal -->
    <div id="checkRecordModal" class="modern-modal">
        <div class="modern-modal-content">
            <h2><i class="fa-solid fa-clipboard-check"></i> Check Record</h2>
            <form id="checkForm" autocomplete="off">
                <input type="hidden" id="docId" name="docId">

                <div class="form-group">
                    <label>Control No.</label>
                    <input type="text" name="control_no" id="control_no" class="form-control" readonly
                        style="background:#e9ecef; cursor:not-allowed;" required
                        value="<?php echo $nextConID; ?>">
                </div>

                <div class="form-group">
                    <label>Payee</label>
                    <input type="text" id="payee" readonly style="background:#e9ecef; cursor:not-allowed;">
                </div>

                <div class="form-group">
                    <label>Date In</label>
                    <input type="text" id="dateIn" readonly style="background:#e9ecef; cursor:not-allowed;">
                </div>

                <hr>

                <div class="form-group">
                    <label>Check Number</label>
                    <input type="number" id="checkNo" name="checkNo" readonly>
                </div>

                <div class="form-group">
                    <label>Check Date</label>
                    <input type="date" id="checkDate" name="checkDate">
                </div>

                <div class="form-group position-relative">
                    <label>Bank Channel</label>
                    <input type="text" name="bankChannel" id="bankChannel" placeholder="Enter or select a bank channel" autocomplete="off" required>
                    <div id="bankChannelSuggestions" class="suggestions-dropdown"></div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="save-btn"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
    <script src="../assets/js/users/checked_print.js"></script>
    <script src="../assets/js/users/check_print.js"></script>
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
        // Select All Checkbox
        document.getElementById("selectAll").addEventListener("change", e => {
            document.querySelectorAll(".record-checkbox").forEach(cb => cb.checked = e.target.checked);
        });

        document.querySelectorAll("#recordsTable tbody tr").forEach(row => {
            row.addEventListener("click", e => {
                // Avoid toggling checkbox if the click is on a button or input
                if (e.target.tagName === "BUTTON" || e.target.tagName === "INPUT") return;

                const checkbox = row.querySelector(".record-checkbox");
                if (checkbox) {
                    checkbox.checked = !checkbox.checked; // toggle checkbox
                }
            });
        });

        // Open Check Record Modal
        function openCheckRecord() {

            const selected = document.querySelectorAll(".record-checkbox:checked");

            // No selection
            if (selected.length === 0) {
                Swal.fire("No Selection", "Please select one record.", "info");
                return;
            }

            // More than 1 selection
            if (selected.length > 1) {
                Swal.fire({
                    icon: "error",
                    title: "Multiple Records Selected",
                    text: "You can only check one record at a time.",
                });
                return;
            }

            // Only one selected → OK
            const docId = selected[0].value;

            fetch(`Controllers/CheckController.php?action=getdoc&id=` + docId)
                .then(res => res.json())
                .then(data => {

                    document.getElementById("docId").value = data.id;
                    document.getElementById("control_no").value = data.control_no;
                    document.getElementById("payee").value = data.payee;
                    document.getElementById("dateIn").value = data.date_in_formatted;

                    // AUTO RANDOM 8-DIGIT CHECK NUMBER
                    let randomCheck = Math.floor(10000000 + Math.random() * 90000000);
                    document.getElementById("checkNo").value = randomCheck;

                    document.getElementById("checkRecordModal").style.display = "flex";
                });
        }

        function closeModal() {
            document.getElementById("checkRecordModal").style.display = "none";
        }
    </script>

    <script>
        document.getElementById("checkForm").addEventListener("submit", function(e) {
            e.preventDefault();

            let formData = new FormData(this);
            formData.append("action", "save_check");

            fetch("Controllers/CheckController.php", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.text())
                .then(resp => {

                    if (resp.trim() === "success") {
                        Swal.fire({
                            icon: "success",
                            title: "Saved!",
                            text: "Check record saved successfully."
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Failed to save check record."
                        });
                    }
                });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const actionModal = document.getElementById("actionModal");
            let currentButton = null;

            document.querySelectorAll(".action-btn.dateOutBtn").forEach(btn => {
                btn.addEventListener("click", function(e) {
                    e.stopPropagation();

                    // Toggle modal if the same button is clicked
                    if (currentButton === this && actionModal.style.display === "block") {
                        actionModal.style.display = "none";
                        currentButton = null;
                        return;
                    }

                    currentButton = this;

                    const container = this.closest(".table-container");
                    const btnRect = this.getBoundingClientRect();
                    const containerRect = container.getBoundingClientRect();

                    let top = btnRect.bottom - containerRect.top + container.scrollTop;
                    const modalHeight = actionModal.offsetHeight;
                    if (top + modalHeight > container.scrollHeight) {
                        top = container.scrollHeight - modalHeight - 5;
                    }

                    let left = container.offsetWidth - 215;
                    if (left < 0) left = 5;

                    actionModal.style.top = top + "px";
                    actionModal.style.left = left + "px";
                    actionModal.style.display = "block";
                });
            });

            document.addEventListener("click", function() {
                actionModal.style.display = "none";
                currentButton = null;
            });

            actionModal.addEventListener("click", function(e) {
                e.stopPropagation();
            });

            function updateStatus(action) {
                if (!currentButton) return;

                const docId = currentButton.dataset.id;

                fetch(`Controllers/CheckController.php?action=${action}&id=${docId}`)
                    .then(res => res.text())
                    .then(resp => {
                        resp = resp.trim();
                        if (resp === "success") {
                            Swal.fire({
                                icon: "success",
                                title: "Success!",
                                text: action === "check_out" ? "Check Out completed." : "Check Released successfully."
                            }).then(() => location.reload());
                        } else if (resp === "exists") {
                            Swal.fire({
                                icon: "warning",
                                title: "Action Not Allowed",
                                text: "This document already has a status and cannot be updated."
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "Failed to update status."
                            });
                        }
                    });

                actionModal.style.display = "none";
                currentButton = null;
            }

            document.getElementById("checkoutOption").addEventListener("click", function() {
                updateStatus("check_out");
            });

            document.getElementById("checkReleaseOption").addEventListener("click", function() {
                updateStatus("check_release");
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {

            function setupDropdown(inputId, dropdownId, items) {
                const input = document.getElementById(inputId);
                const container = document.getElementById(dropdownId);

                function showSuggestions(list) {
                    container.innerHTML = "";

                    if (list.length === 0) {
                        container.style.display = "none";
                        return;
                    }

                    list.forEach(item => {
                        const div = document.createElement("div");
                        div.textContent = item;
                        div.onclick = () => {
                            input.value = item;
                            container.style.display = "none";
                        };
                        container.appendChild(div);
                    });

                    container.style.display = "block";
                }

                function filterSuggestions() {
                    const text = input.value.toLowerCase();
                    const filtered = items.filter(i => i.toLowerCase().includes(text));
                    showSuggestions(filtered);
                }

                input.addEventListener("input", filterSuggestions);
                input.addEventListener("focus", () => showSuggestions(items));

                document.addEventListener("click", (e) => {
                    if (!input.contains(e.target) && !container.contains(e.target)) {
                        container.style.display = "none";
                    }
                });
            }

            // Bank Channel suggestions
            const bankChannels = [
                <?php
                $result = $conn->query("SELECT DISTINCT bank_channel FROM documents WHERE bank_channel IS NOT NULL ORDER BY bank_channel ASC");
                $items = [];
                while ($row = $result->fetch_assoc()) {
                    if (!empty($row['bank_channel'])) {
                        $items[] = '"' . addslashes($row['bank_channel']) . '"';
                    }
                }
                echo implode(",", $items);
                ?>
            ];
            setupDropdown("bankChannel", "bankChannelSuggestions", bankChannels);

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
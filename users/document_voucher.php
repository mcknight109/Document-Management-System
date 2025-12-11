<?php
session_start();
include "../db.php";
date_default_timezone_set('Asia/Manila');

// If not logged in, redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Logged in user info
$userId = $_SESSION['user_id'];
$recordedBy = $_SESSION['username'];

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
$canAccessVoucher = in_array("voucher_records", $user_permissions);

// Fetch next control number
$controlQuery = $conn->query("SELECT control_no FROM documents ORDER BY id DESC LIMIT 1");

if ($controlQuery->num_rows > 0) {
    $lastControl = (int)$controlQuery->fetch_assoc()['control_no'];
    $nextControlNo = $lastControl + 1;
} else {
    $nextControlNo = 1; // if table is empty, start at 1
}

date_default_timezone_set('Asia/Manila');
$currentDate = date("M d, Y");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Document Records Management</title>
<link rel="stylesheet" href="../assets/css/users/document_voucher.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .print-modal {
        width: 1000px;
        max-height: 70vh;
        overflow-y: auto;
    }

    .print-preview-area {
        background: #ffffffff;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: flex-start;
    }

    .paper {
        width: 210mm;            /* A4 width */
        min-height: 297mm;       /* A4 height */
        padding: 20mm;
        margin: auto;
        background: white;
        box-shadow: 0 0 5px rgba(0,0,0,0.1);
        color: #000;
        font-family: "Times New Roman", serif;
    }

    .paper-content {
        font-size: 13px;
        white-space: pre-wrap;
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

<div class="navbar">
    <div class="left">
        Voucher Records Management
    </div>
    <div class="right">
        <div class="search-container">
            <input type="text" id="tableSearch" placeholder="Search records..." />
            <button id="searchBtn"><i class="bi bi-search"></i></button>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="table-container">
        <table id="recordsTable">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>Control No.</th>
                    <th>Payee</th>
                    <th>Description</th>
                    <th>Fund Type</th>
                    <th>Amount</th>
                    <th>Date In</th>
                    <th>Date Out</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM documents WHERE date_out IS NULL");
                while ($row = $result->fetch_assoc()) {
                    $dateIn = $row['date_in'] ? date('M d, Y h:i A', strtotime($row['date_in'])) : '-';
                    $dateOut = $row['date_out'] ? date('M d, Y h:i A', strtotime($row['date_out'])) : '-';

                    echo "<tr>
                        <td><input type='checkbox' class='rowCheckbox' value='{$row['id']}' onclick='event.stopPropagation();'></td>
                        <td>{$row['control_no']}</td>
                        <td>{$row['payee']}</td>
                        <td>{$row['description']}</td>
                        <td>{$row['fund_type']}</td>
                        <td>₱" . number_format($row['amount'], 2) . "</td>
                        <td>{$dateIn}</td>
                        <td>{$dateOut}</td>
                        <td onclick='event.stopPropagation()'>
                            <button class='action-btn' onclick='showActionOptions({$row['id']})'>
                                <i class='bi bi-pencil-square'></i>
                            </button>
                        </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
        <div class="footer">
            <button>&laquo;</button>
            <button>&lsaquo;</button>
            <button>&rsaquo;</button>
            <button>&raquo;</button>
        </div>
    </div>
    <div class="button-panel">
        <button onclick="addRow()" <?= !$canAccessVoucher ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : '' ?>>
            <i class="fa-solid fa-plus"></i> Add Record
        </button>

        <button onclick="printSelectedTransmittal()">
            <i class="fa-solid fa-print"></i> Transmittal Print
        </button>

        <button onclick="window.location.href='../index.php'" style="background-color: #4b5563;">
            <i class="fa-solid fa-xmark"></i> Exit
        </button>
    </div>
</div>

<!-- Add Record Modal -->
<div id="addRecordModal" class="modern-modal">
    <div class="modern-modal-content">
        <h2><i class="fa-solid fa-file-circle-plus"></i> Add New Document</h2>
        <form id="addRecordForm" autocomplete="off">
            <div class="form-group">
                <label>Control No.</label>
                <input type="number" 
                    name="control_no" 
                    value="<?php echo $nextControlNo; ?>" 
                    readonly 
                    style="background:#e9ecef; cursor:not-allowed;">
            </div>

            <div class="form-group position-relative">
                <label>Payee</label>
                <input type="text" name="payee" id="payee" 
                    class="form-control" placeholder="Enter or select a payee" 
                    autocomplete="off">
                <div id="payeeSuggestions" class="suggestions-dropdown"></div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" placeholder="Enter description" required>
            </div>
            
            <div class="form-group position-relative">
                <label>Fund Type</label>
                <input type="text" name="fundType" id="fundType" placeholder="Enter or select a fund type" autocomplete="off" required>
                <div id="fundTypeSuggestions" class="suggestions-dropdown"></div>
            </div>

            <div class="form-group">
                <label>Amount</label>
                <div class="input-group" style="width:100%;">
                    <span class="input-group-text">₱</span>
                    <input type="text" id="amountInput" name="amount" placeholder="0.00" required>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
                <button type="submit" class="save-btn"><i class="fa-solid fa-check"></i> Save Record</button>
            </div>
        </form>
    </div>
</div>
<script src="../assets/js/users/voucher_print.js"></script>
<script src="../date.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const amountField = document.getElementById("amountInput");

        amountField.addEventListener("input", function () {
            let value = this.value;

            // Remove all characters except digits and dot
            let clean = value.replace(/[^0-9.]/g, "");

            // Allow only one dot
            const parts = clean.split(".");
            if (parts.length > 2) {
                clean = parts[0] + "." + parts[1];
            }

            // Split integer and decimal parts
            let [integerPart, decimalPart] = clean.split(".");
            
            // Format integer part with commas
            if (integerPart) {
                integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }

            // Rejoin decimal part if user has typed dot
            this.value = decimalPart !== undefined ? integerPart + "." + decimalPart : integerPart;
        });

        // Before submitting: remove commas
        document.getElementById("addRecordForm").addEventListener("submit", () => {
            amountField.value = amountField.value.replace(/,/g, "");
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
    function showActionOptions(id) {
        Swal.fire({
            title: "Choose Action",
            text: "Select what you want to mark for this record.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Mark as Date Out",
            cancelButtonText: "Cancel",
            confirmButtonColor: "#0ea5e9",
        }).then(result => {
            if (result.isConfirmed) markDate("out", [id]);
        });
    }

    function markDate(type, ids = null) {
        // If no IDs were passed (multi-select mode)
        if (!ids) {
            ids = Array.from(document.querySelectorAll(".rowCheckbox:checked"))
                    .map(cb => cb.value);
        }

        if (ids.length === 0) {
            Swal.fire("No record selected", "Please select at least one row.", "info");
            return;
        }

        // Send request to VoucherController.php
        fetch("Controllers/VoucherController.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: type === "in" ? "mark_in" : "mark_out",
                ids: ids
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: "success",
                    title: data.message,
                    timer: 1200,
                    showConfirmButton: false
                }).then(() => {
                    // Dynamically remove rows marked as Date Out
                    ids.forEach(id => {
                        const row = document.querySelector(`.rowCheckbox[value='${id}']`)?.closest('tr');
                        if (row) row.remove();
                    });
                });
            } else {
                Swal.fire("Error", data.error || "Failed to update record.", "error");
            }
        })
        .catch(err => Swal.fire("Error", "Request failed.", "error"));
    }

    function deleteSelected() {
        const selectedCheckboxes = document.querySelectorAll(".rowCheckbox:checked");

        if (selectedCheckboxes.length === 0) {
            Swal.fire("No record selected", "Please select at least one row to delete.", "info");
            return;
        }

        // Collect IDs and Control Numbers
        let ids = [];
        let controlNums = [];

        selectedCheckboxes.forEach(cb => {
            ids.push(cb.value);

            // control number is in the next <td>
            let controlNum = cb.closest("tr").children[1].textContent.trim();
            controlNums.push(controlNum);
        });

        Swal.fire({
            title: 'Confirm Delete',
            html: `
                <p>Are you sure you want to delete the following Control No.?</p>
                <b>${controlNums.join(", ")}</b>
            `,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete",
            cancelButtonText: "Cancel",
            confirmButtonColor: "#d33"
        }).then(result => {
            if (result.isConfirmed) {
                fetch("Controllers/VoucherController.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ action: "delete", ids })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Deleted successfully!",
                            timer: 1200,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire("Error", data.error || "Failed to delete records.", "error");
                    }
                })
                .catch(() => Swal.fire("Error", "Request failed.", "error"));
            }
        });
    }

    document.getElementById("selectAll").addEventListener("change", function () {
        document.querySelectorAll(".rowCheckbox").forEach(cb => cb.checked = this.checked);
    });

    document.querySelectorAll("#recordsTable tbody tr").forEach(row => {
        row.addEventListener("click", e => {
            // Avoid toggling checkbox if the click is on a button or input
            if (e.target.tagName === "BUTTON" || e.target.tagName === "INPUT") return;

            const checkbox = row.querySelector(".rowCheckbox"); // <-- use correct class
            if (checkbox) {
                checkbox.checked = !checkbox.checked; // toggle checkbox
            }
        });
    });

    const modal = document.getElementById("addRecordModal");
    const form = document.getElementById("addRecordForm");

    function addRow() { modal.style.display = "flex"; }
    function closeModal() { modal.style.display = "none"; form.reset(); }

    window.onclick = (e) => {
        if (e.target === modal) closeModal();
        if (e.target === checkModal) closeCheckModal();
    };

    form.addEventListener("submit", (e) => {
        e.preventDefault();

        const formData = {
            action: "save",
            control_no: document.querySelector("input[name='control_no']").value,
            payee: document.querySelector("input[name='payee']").value,
            description: document.querySelector("input[name='description']").value,
            fund_type: document.querySelector("input[name='fundType']").value,
            amount: document.getElementById("amountInput").value.replace(/,/g, "")
        };

        fetch("Controllers/VoucherController.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(formData)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: "success",
                    title: "Record added successfully!",
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire("Error", data.error || "Failed to add record.", "error");
            }
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

        // Payee suggestions
        const payees = [
            <?php
            $result = $conn->query("SELECT DISTINCT payee FROM documents ORDER BY payee ASC");
            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = '"' . addslashes($row['payee']) . '"';
            }
            echo implode(",", $items);
            ?>
        ];
        setupDropdown("payee", "payeeSuggestions", payees);

        // Fund Type suggestions
        const fundTypes = [
            <?php
            $result = $conn->query("SELECT DISTINCT fund_type FROM documents WHERE fund_type IS NOT NULL ORDER BY fund_type ASC");
            $items = [];
            while ($row = $result->fetch_assoc()) {
                if (!empty($row['fund_type'])) {
                    $items[] = '"' . addslashes($row['fund_type']) . '"';
                }
            }
            echo implode(",", $items);
            ?>
        ];
        setupDropdown("fundType", "fundTypeSuggestions", fundTypes);

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

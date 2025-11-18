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
.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 8px;
    color: #000000ff;
    background-color: #f0f0f0ff;
    border: none;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    }

    .action-btn i {
        font-size: 1rem;
    }

    .action-btn:hover {
        background-color: darkblue;
        transform: translateY(-1px);
        box-shadow: 0 3px 6px rgba(0,0,0,0.15);
        color: #ffffff;
    }

    .action-btn:active {
        transform: translateY(0);
        box-shadow: none;
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
        <i class="fa-solid fa-folder-open" style="color:var(--primary-blue);"></i>
        <label>Transmittal ID:</label><span>2294</span>
    </div>
    <div class="right">
        <label for="">Voucher Type:</label>
        <input type="text" id="voucherType" value="Disbursement Voucher">
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
                    <th>Amount</th>
                    <th>Date In</th>
                    <th>Date Out</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM documents WHERE user_id = $userId");
                while ($row = $result->fetch_assoc()) {
                    $dateIn = $row['date_in'] ? date('M d, Y h:i A', strtotime($row['date_in'])) : '-';
                    $dateOut = $row['date_out'] ? date('M d, Y h:i A', strtotime($row['date_out'])) : '-';

                    echo "<tr onclick='openCheckModal(" . json_encode($row) . ")'>
                        <td><input type='checkbox' class='rowCheckbox' value='{$row['id']}' onclick='event.stopPropagation();'></td>
                        <td>{$row['control_num']}</td>
                        <td>{$row['payee']}</td>
                        <td>{$row['description']}</td>
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
        <button onclick="addRow()"><i class="fa-solid fa-plus"></i> Add Record</button>
        <button onclick="deleteSelected()"><i class="fa-solid fa-trash"></i> Delete Record</button>
        <button onclick="printDoc()"><i class="fa-solid fa-print"></i> Print Preview</button>
        <button onclick="window.location.href='../index.php'"><i class="fa-solid fa-xmark"></i> Exit</button>
    </div>
</div>

<!-- Add Record Modal -->
<div id="addRecordModal" class="modern-modal">
    <div class="modern-modal-content">
        <h2><i class="fa-solid fa-file-circle-plus"></i> Add New Document</h2>
        <form id="addRecordForm" autocomplete="off">
        <div class="form-group">
                <label>Control No.</label>
                <input type="number" name="control_num" placeholder="Enter control number" required>
            </div>

            <div class="form-group">
                <label>Payee</label>
                <input type="text" name="payee" placeholder="Enter payee name" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" placeholder="Enter description" required>
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

<script src="../date.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const amountField = document.getElementById("amountInput");
    let typingTimer;

    amountField.addEventListener("input", function () {
        clearTimeout(typingTimer);
        
        // Save cursor position
        let cursorPos = this.selectionStart;

        // Remove invalid characters (letters, symbols except dot)
        let clean = this.value.replace(/[^0-9.]/g, "");

        // Allow only one dot
        let parts = clean.split(".");
        if (parts.length > 2) {
            clean = parts[0] + "." + parts[1];
        }

        this.value = clean;

        // Restore cursor position
        this.setSelectionRange(cursorPos, cursorPos);

        // Apply final formatting when typing stops
        typingTimer = setTimeout(() => {
            formatMoney(amountField);
        }, 450);
    });

    function formatMoney(input) {
        if (!input.value) {
            input.value = "0.00";
            return;
        }

        let value = parseFloat(input.value);
        if (isNaN(value)) {
            input.value = "0.00";
            return;
        }

        input.value = value.toLocaleString("en-US", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

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
            showDenyButton: true,
            confirmButtonText: "Mark as Date In",
            denyButtonText: "Mark as Date Out",
            cancelButtonText: "Cancel",
            confirmButtonColor: "#1e3a8a",
            denyButtonColor: "#0ea5e9",
        }).then(result => {
            if (result.isConfirmed) markDate("in", [id]);
            else if (result.isDenied) markDate("out", [id]);
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

        // Send request to merged VoucherController.php
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
                }).then(() => location.reload());
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
            control_num: document.querySelector("input[name='control_num']").value,
            payee: document.querySelector("input[name='payee']").value,
            description: document.querySelector("input[name='description']").value,
            amount: document.querySelector("input[name='amount']").value
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
</body>
</html>

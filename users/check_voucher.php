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

$recordedBy = $_SESSION['username'];

// Fetch user ID
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $recordedBy);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userId = $user['id'] ?? 0;
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
        <label for="">Check Type:</label>
        <input type="text" id="voucherType" value="Check">
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
                <th>Check Date</th>
                <th>Fund Type</th>
                <th>Bank Channel</th>
                <th>Date Out 2</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><input type="checkbox" class="record-checkbox" value="<?= $row['id'] ?>"></td>
                <td><?= htmlspecialchars($row['control_num']) ?></td>
                <td><?= htmlspecialchars($row['payee']) ?></td>
                <td><?= $row['date_in'] ? date('M d, Y h:i A', strtotime($row['date_in'])) : '-' ?></td>
                <td><?= htmlspecialchars($row['fund_type'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['bank_channel'] ?? '') ?></td>
                <td><?= $row['date_out_2'] ? date('M d, Y h:i A', strtotime($row['date_out_2'])) : '-' ?></td>
                <td>
                    <button class="action-btn dateOutBtn" data-id="<?= $row['id'] ?>" title="Actions">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="button-panel">
    <button onclick="openCheckRecord()"><i class="fa-solid fa-plus"></i> Check Record</button>
    <button onclick="deleteSelected()"><i class="fa-solid fa-trash"></i> Delete Record</button>
    <button onclick="printDoc()"><i class="fa-solid fa-print"></i> Print Preview</button>
    <button onclick="window.location.href='../index.php'"><i class="fa-solid fa-xmark"></i> Exit</button>
</div>
</div>

<!-- ✅ Check Record Modal -->
<div id="checkRecordModal" class="modern-modal">
  <div class="modern-modal-content">
    <h2><i class="fa-solid fa-clipboard-check"></i> Check Record</h2>
    <form id="checkForm" autocomplete="off">
      <input type="hidden" id="docId" name="docId">

      <div class="form-group">
        <label>Control No.</label>
        <input type="text" id="controlNum" readonly>
      </div>

      <div class="form-group">
        <label>Payee</label>
        <input type="text" id="payee" readonly>
      </div>

      <div class="form-group">
        <label>Date In</label>
        <input type="text" id="dateIn" readonly>
      </div>

      <div class="form-group">
        <label>Fund Type</label>
        <input type="text" id="fundType" name="fundType" placeholder="Enter fund type" required>
      </div>

      <div class="form-group">
        <label>Bank Channel</label>
        <input type="text" id="bankChannel" name="bankChannel" placeholder="Enter bank channel" required>
      </div>

      <div class="modal-actions">
        <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
        <button type="submit" class="save-btn"><i class="fa-solid fa-floppy-disk"></i> Save</button>
      </div>
    </form>
  </div>
</div>

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

    // Delete Selected
    function deleteSelected() {
        const selected = document.querySelectorAll(".record-checkbox:checked");

        if (selected.length === 0) {
            Swal.fire("No Selection", "Please select at least one record to delete.", "info");
            return;
        }

        let ids = [];
        let controlNumbers = [];

        selected.forEach(cb => {
            ids.push(cb.value);
            const row = cb.closest("tr");
            controlNumbers.push(row.cells[1].textContent.trim());
        });

        // Join all Control Numbers as a string
        const displayText = `<b>${controlNumbers.join(", ")}</b>`;

        Swal.fire({
            title: "Confirm Deletion",
            html: `Are you sure you want to delete the following record(s)?<br>${displayText}`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete"
        }).then(result => {
            if (result.isConfirmed) {
                fetch("Controllers/CheckController.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "delete_ids=" + encodeURIComponent(JSON.stringify(ids))
                })
                .then(res => res.text())
                .then(resp => {
                    Swal.fire("Deleted!", "Record(s) deleted successfully.", "success")
                        .then(() => location.reload());
                });
            }
        });
    }


    // Open Check Record Modal
    function openCheckRecord() {
        const selected = document.querySelector(".record-checkbox:checked");
        if (!selected) {
            Swal.fire("Select a record", "Please select one record to check.", "info");
            return;
        }

        const row = selected.closest("tr");
        document.getElementById("docId").value = selected.value;
        document.getElementById("controlNum").value = row.cells[1].textContent;
        document.getElementById("payee").value = row.cells[2].textContent;

        document.getElementById("checkRecordModal").style.display = "flex";
    }

    // Close Modal
    function closeModal() {
        document.getElementById("checkRecordModal").style.display = "none";
    }

    // Save Check Record (AJAX)
    document.getElementById("checkForm").addEventListener("submit", e => {
        e.preventDefault();
        const data = new FormData(e.target);
        fetch("Controllers/CheckController.php?action=checkrecord", {
            method: "POST",
            body: data
        })
        .then(res => res.text())
        .then(resp => {
            Swal.fire("Success", "Record successfully checked!", "success").then(() => location.reload());
        });
    });

    // Date Out 2 Button
    document.querySelectorAll(".dateOutBtn").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.dataset.id;
            Swal.fire({
                title: "Choose Action",
                icon: "warning",
                text: "Mark this as Date Out?",
                showDenyButton: true,
                confirmButtonText: "Mark as Date Out 2",
                denyButtonText: "Cancel"
            }).then(result => {
                if (result.isConfirmed) {
                    fetch(`Controllers/CheckController.php?action=dateout2&id=${id}`)
                    .then(() => Swal.fire("Success", "Marked as Date Out 2", "success").then(() => location.reload()));
                } else if (result.isDenied) {
                    Swal.fire("Cancelled", "No changes made", "info");
                }
            });
        });
    });
</script>
</body>
</html>

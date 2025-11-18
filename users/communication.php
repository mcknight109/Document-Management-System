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

<div class="main-container">
    <div class="row g-4">
        
        <!-- LEFT SIDE: Table -->
        <div class="col-lg-8 left-panel">
            <div class="card h-100">
                <div class="card-header">
                    <i class="bi bi-table"></i>
                    Communication Records
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
                                $records = $conn->query("SELECT * FROM communications WHERE user_id = $user_id ORDER BY id DESC");

                                if ($records->num_rows > 0) {
                                    while ($row = $records->fetch_assoc()) {
                                        $data = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                                        echo "<tr class='text-center' data-row='{$data}'>
                                                <td><input type='checkbox' class='rowCheckbox' name='delete_ids[]' value='{$row['id']}'></td>
                                                <td>{$row['com_id']}</td>
                                                <td>{$row['date_received']}</td>
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
            </div>
        </div>

        <!-- RIGHT SIDE: Forms -->
        <div class="col-lg-4 right-panel">
            <form method="POST" action="Controllers/CommunicationController.php">
                <input type="hidden" name="id" id="id">
                
                <!-- IN Form -->
                <div class="card form-section">
                    <div class="card-header">
                        <i class="bi bi-inbox-fill"></i>
                        IN Form
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">ComID</label>
                            <input type="text" name="com_id" id="com_id" class="form-control" value="COM-" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date Received</label>
                            <input type="date" name="date_received" id="date_received" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sender</label>
                            <input type="text" name="sender" id="sender" class="form-control" placeholder="Enter the sender name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Enter description" required></textarea>
                        </div>
                        <div class="form-buttons" style="flex-wrap: nowrap;">
                            <button type="reset" class="btn btn-custom">
                                <i class="bi bi-plus-circle"></i> New
                            </button>
                            <button type="submit" name="save" class="btn btn-custom">
                                <i class="bi bi-save"></i> Save
                            </button>
                            <button type="button" onclick="printSlip()" class="btn btn-custom">
                                <i class="bi bi-printer"></i> Print
                            </button>
                            <!-- <button onclick="window.location.href='../index.php'" type="button" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Close
                            </button> -->
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
                            <label href="" style="font-weight: bold; color: darkblue;">ComID:</label> <!-- echo the comid of the selected data in table-->
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Indorse To</label>
                            <input type="text" name="indorse_to" id="indorse_to" class="form-control" placeholder="Enter recipient">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date Routed</label>
                            <input type="date" name="date_routed" id="date_routed" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Action</label>
                            <input type="text" name="action_taken" id="action_taken" class="form-control" placeholder="Enter action taken">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" id="remarks" class="form-control" rows="3" placeholder="Enter remarks"></textarea>
                        </div>
                        <div class="form-buttons">
                            <button type="submit" name="save_edit" class="btn btn-custom">
                                <i class="bi bi-pencil-square"></i> Save Edit
                            </button>
                            <button type="button" id="deleteBtn" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                            <button onclick="window.location.href='../index.php'" type="button" class="btn btn-secondary" style="grid-column: 1 / -1;">
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

<!-- <script>
    document.addEventListener("DOMContentLoaded", function () {
    const deleteBtn = document.getElementById('deleteBtn');

    deleteBtn.addEventListener('click', function (e) {
        e.preventDefault();

        // Collect checked rows
        const checked = document.querySelectorAll('.rowCheckbox:checked');
        if (checked.length === 0) {
            Swal.fire('No Selection', 'Please select at least one record to delete.', 'warning');
            return;
        }

        const ids = Array.from(checked).map(cb => cb.closest('tr').dataset.id);

        Swal.fire({
            title: 'Confirm Delete',
            html: `<p>Are you sure you want to delete the following records?</p>
                   <strong>${ids.join(', ')}</strong>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Delete!'
        }).then(result => {
            if (result.isConfirmed) {
                // Create a hidden form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'Controllers/CertificateController.php';

                ids.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'delete_ids[]';
                    input.value = id;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script> -->

<script>
document.addEventListener("DOMContentLoaded", function () {
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

  prevBtn.addEventListener("click", function (e) {
    e.preventDefault();
    if (currentPage > 1) {
      currentPage--;
      renderTablePage();
    }
  });

  nextBtn.addEventListener("click", function (e) {
    e.preventDefault();
    const totalPages = Math.ceil(rows.length / rowsPerPage) || 1;
    if (currentPage < totalPages) {
      currentPage++;
      renderTablePage();
    }
  });

  renderTablePage(); // ✅ Initial render

  // ===== CHECKBOX LOGIC =====
  selectAllCheckbox.addEventListener("change", function () {
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

    row.addEventListener("click", function (e) {
      // Ignore clicks directly on checkbox to prevent double toggling
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
document.addEventListener('DOMContentLoaded', () => {
    // ✅ Success alerts
    <?php if (isset($_SESSION['save_success'])): ?>
        Swal.fire({icon:'success',title:'Saved!',text:'New communication record added.'});
        <?php unset($_SESSION['save_success']); ?>
    <?php elseif (isset($_SESSION['edit_success'])): ?>
        Swal.fire({icon:'success',title:'Updated!',text:'OUT Form details saved successfully.'});
        <?php unset($_SESSION['edit_success']); ?>
    <?php elseif (isset($_SESSION['delete_success'])): ?>
        Swal.fire({icon:'success',title:'Deleted!',text:'Selected record(s) deleted successfully.'});
        <?php unset($_SESSION['delete_success']); ?>
    <?php elseif (isset($_SESSION['delete_error'])): ?>
        Swal.fire({icon:'error',title:'Delete Failed',text:'No records were deleted. Please try again.'});
        <?php unset($_SESSION['delete_error']); ?>
    <?php endif; ?>


    // ✅ Handle row selection
    const rows = document.querySelectorAll('#recordsTable tbody tr');
    const comIdLabel = document.querySelector('.card-body label[href=""]');
    let selectedId = null;

    rows.forEach(row => {
        row.addEventListener('click', () => {
            rows.forEach(r => r.classList.remove('selected'));
            row.classList.add('selected');

            const data = JSON.parse(row.dataset.row);
            selectedId = data.id;

            document.getElementById('id').value = data.id;
            document.getElementById('com_id').value = data.com_id;
            document.getElementById('date_received').value = data.date_received;
            document.getElementById('sender').value = data.sender;
            document.getElementById('description').value = data.description;
            document.getElementById('indorse_to').value = data.indorse_to || '';
            document.getElementById('date_routed').value = data.date_routed || '';
            document.getElementById('action_taken').value = data.action_taken || '';
            document.getElementById('remarks').value = data.remarks || '';

            // ✅ Show selected ComID in OUT Form
            comIdLabel.textContent = 'ComID: ' + data.com_id;
        });
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const deleteBtn = document.getElementById("deleteBtn");

    deleteBtn.addEventListener("click", (e) => {
        e.preventDefault();

        // 1. Get all checked checkboxes from table
        const checkboxes = document.querySelectorAll(".rowCheckbox:checked");
        if (checkboxes.length === 0) {
            Swal.fire({
                icon: "warning",
                title: "No Selection",
                text: "Please select at least one record to delete."
            });
            return;
        }

        // 2. Collect their IDs
        const selectedIds = Array.from(checkboxes).map(cb => cb.value);
        const comIds = Array.from(checkboxes).map(cb => cb.closest("tr").children[1].textContent);

        // 3. Confirm with SweetAlert2
        Swal.fire({
            title: "Confirm Deletion",
            html: `Are you sure you want to delete the selected record(s): <b>${comIds.join(", ")}</b>?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete them!"
        }).then((result) => {
            if (result.isConfirmed) {
                // 4. Create a temporary form to submit to controller
                const form = document.createElement("form");
                form.method = "POST";
                form.action = "Controllers/CommunicationController.php";

                // Add hidden input to trigger delete block
                const deleteInput = document.createElement("input");
                deleteInput.type = "hidden";
                deleteInput.name = "delete";
                deleteInput.value = "1";
                form.appendChild(deleteInput);

                // Add hidden inputs for each selected ID
                selectedIds.forEach(id => {
                    const input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "delete_ids[]";
                    input.value = id;
                    form.appendChild(input);
                });

                // Submit the form
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
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

    // ✅ Show "Enter a new form" message when New is clicked
    document.querySelector('.btn-custom[type="reset"]').addEventListener('click', function() {
        let label = document.createElement('div');
        label.textContent = 'Enter a new form.';
        label.style.color = 'darkblue';
        label.style.fontWeight = '600';
        label.style.marginBottom = '8px';
        label.id = 'newFormMsg';
        label.style.textAlign = 'center';

        const comIdLabel = document.querySelector('label[for="com_id"]') || document.querySelector('.form-label');
        if (!document.getElementById('newFormMsg')) {
            comIdLabel.parentNode.insertBefore(label, comIdLabel);
        }
    });
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
</script>
</body>
</html>
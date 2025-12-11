<?php
session_start();
include '../db.php';
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$admin_id = $_SESSION['user_id'];

// Fetch admin details
$sql = "SELECT first_name, middle_initial, last_name, role FROM users WHERE id = $admin_id LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    $admin_fullname = $admin['first_name'] . 
                      (!empty($admin['middle_initial']) ? " " . $admin['middle_initial'] : "") . 
                      " " . $admin['last_name'];
} else {
    $admin_fullname = "Admin User";
}

// Search filter
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Pagination
$limit = 10; // Pagination limit set to 10
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Base query
$where = "WHERE 1=1";
if (!empty($search)) {
    $where .= " AND (full_name LIKE '%$search%' OR action LIKE '%$search%' OR module LIKE '%$search%' OR description LIKE '%$search%')";
}

$moduleFilter = isset($_GET['module']) ? $conn->real_escape_string($_GET['module']) : '';

if (!empty($moduleFilter)) {
    $where .= " AND module = '$moduleFilter'";
}

// Fetch logs
$sql = "SELECT * FROM user_activity_logs $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Total logs for pagination
$total_sql = "SELECT COUNT(*) AS total FROM user_activity_logs $where";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_logs = $total_row['total'];
$total_pages = ceil($total_logs / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Document Records Activity Logs</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="../assets/css/admin/record_logs.css" rel="stylesheet">
</head>
<body>

<div class="sidebar">
    <div>
        <div class="brand">ADMIN DASHBOARD</div>
        <div class="profile">
            <img src="../assets/images/office-of-treasurer.png" alt="">
            <p>
                <?= htmlspecialchars($admin_fullname) ?><br>
                <small><?= ucfirst($admin['role']) ?></small>
            </p>
        </div>
        <div class="nav-menu">
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="user_manage.php"><i class="fas fa-users"></i> User Management</a>
            <a href="record_logs.php" class="active"><i class="fa-solid fa-file"></i> Record Logs</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <div class="footer">© Document Record by ACLC Students</div>
</div>

<div class="content">
    <div class="welcome mb-4">
        <h5>Document Records Activity Logs</h5>
        <small><?= date("l, F j, Y - g:i A"); ?></small>
    </div>

    <!-- Controls -->
    <div class="controls d-flex justify-content-between align-items-center mb-3">
        <!-- Left: Search input + button -->
        <div class="d-flex align-items-center gap-2">
            <input type="text" id="searchInput" class="form-control" placeholder="Search logs..." value="<?= htmlspecialchars($search) ?>">
            <button id="searchBtn" class="btn btn-primary">Search</button>
        </div>

        <!-- Right: Module filter -->
        <div>
            <select id="moduleFilter" class="form-select">
                <option value="">All Modules</option>
                <option value="Communication Records">Communication Records</option>
                <option value="Document Voucher Records">Document Voucher Records</option>
                <option value="Check Document Records">Check Document Records</option>
                <option value="Certificate Records">Certificate Records</option>
                <option value="Activity Records">Activity Records</option>
            </select>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card p-3">
        <div class="pagination-controls d-flex justify-content-between mb-2">
            <div>
                <button class="btn btn-outline-secondary btn-sm" id="prevBtn">Previous</button>
                <button class="btn btn-outline-secondary btn-sm" id="nextBtn">Next</button>
            </div>
            <div>
                <span class="badge role-badge">Total Logs: <?= $total_logs ?></span>
            </div>
        </div>

        <table class="table table-striped align-middle text-center">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Action</th>
                    <th>Module</th>
                    <th>Description</th>
                    <th>Date & Time</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['action']) ?></td>
                            <td><?= htmlspecialchars($row['module']) ?></td>
                            <td style="font-size: 14px;"><?= htmlspecialchars($row['description']) ?></td>
                            <td><?= date("M d, Y - h:i A", strtotime($row['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-info btn-sm btn-view-details" 
                                        data-fullname="<?= htmlspecialchars($row['full_name']) ?>"
                                        data-action="<?= htmlspecialchars($row['action']) ?>"
                                        data-module="<?= htmlspecialchars($row['module']) ?>"
                                        data-description="<?= htmlspecialchars($row['description']) ?>"
                                        data-refid="<?= htmlspecialchars($row['reference_id']) ?>"
                                        data-refno="<?= htmlspecialchars($row['reference_no']) ?>"
                                        data-date="<?= date("M d, Y - h:i A", strtotime($row['created_at'])) ?>"
                                        >
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center text-muted">No logs found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div id="detailsModal" class="modal-overlay">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <h5>Activity Log Details</h5>
        <div class="modal-body">
            <p><strong>Full Name:</strong> <span id="modalFullName"></span></p>
            <p><strong>Action:</strong> <span id="modalAction"></span></p>
            <p><strong>Module:</strong> <span id="modalModule"></span></p>
            <p><strong>Reference ID:</strong> <span id="modalRefID"></span></p>
            <p><strong>Reference No:</strong> <span id="modalRefNo"></span></p>
            <p><strong>Description:</strong> <span id="modalDescription"></span></p>
            <p><strong>Date & Time:</strong> <span id="modalDate"></span></p>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const currentPage = <?= $page ?>;
    const totalPages = <?= $total_pages ?>;

    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const moduleFilter = document.getElementById('moduleFilter');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');

    // --- Utility function to go to page with search & filter ---
    function goToPage(page = 1) {
        const search = encodeURIComponent(searchInput.value.trim());
        const module = encodeURIComponent(moduleFilter.value);
        let url = `?page=${page}`;
        if(search) url += `&search=${search}`;
        if(module) url += `&module=${module}`;
        window.location.href = url;
    }

    // --- Pagination ---
    if(currentPage > 1){
        prevBtn.addEventListener('click', () => goToPage(currentPage - 1));
    } else { prevBtn.disabled = true; }

    if(currentPage < totalPages){
        nextBtn.addEventListener('click', () => goToPage(currentPage + 1));
    } else { nextBtn.disabled = true; }

    // --- Filter & Search Events ---
    moduleFilter.addEventListener('change', () => goToPage(1));
    searchBtn.addEventListener('click', () => goToPage(1));
    searchInput.addEventListener('keypress', (e) => {
        if(e.key === 'Enter') goToPage(1);
    });

    // --- Pre-fill filter & search from GET ---
    <?php if(!empty($moduleFilter)): ?>
        moduleFilter.value = "<?= htmlspecialchars($moduleFilter) ?>";
    <?php endif; ?>
    <?php if(!empty($search)): ?>
        searchInput.value = "<?= htmlspecialchars($search) ?>";
    <?php endif; ?>
</script>

<script>
    const modal = document.getElementById('detailsModal');
    const modalClose = modal.querySelector('.modal-close');

    document.querySelectorAll('.btn-view-details').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('modalFullName').textContent = btn.dataset.fullname;
            document.getElementById('modalAction').textContent = btn.dataset.action;
            document.getElementById('modalModule').textContent = btn.dataset.module;
            document.getElementById('modalRefID').textContent = btn.dataset.refid || '-';
            document.getElementById('modalRefNo').textContent = btn.dataset.refno || '-';
            document.getElementById('modalDescription').textContent = btn.dataset.description;
            document.getElementById('modalDate').textContent = btn.dataset.date;
            modal.style.display = 'flex';
        });
    });

    modalClose.addEventListener('click', () => modal.style.display = 'none');

    window.addEventListener('click', e => {
        if(e.target === modal) modal.style.display = 'none';
    });
</script>
</body>
</html>

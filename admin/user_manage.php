<?php
session_start();
include '../db.php';
date_default_timezone_set('Asia/Manila');

// Ensure user is logged in
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

// --- SEARCH & FILTER ---
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$filter = isset($_GET['filter']) ? $conn->real_escape_string($_GET['filter']) : '';

// --- PAGINATION ---
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// --- BUILD WHERE CLAUSE ---
$where = "WHERE 1=1";

if (!empty($search)) {
    $where .= " AND (first_name LIKE '%$search%' OR middle_initial LIKE '%$search%' OR last_name LIKE '%$search%' OR username LIKE '%$search%' OR email LIKE '%$search%')";
}

if (!empty($filter)) {
    if($filter === "Admin" || $filter === "Encoder") {
        $where .= " AND role = '$filter'";
    } elseif($filter === "Active" || $filter === "Inactive") {
        $where .= " AND status = '$filter'";
    }
}

// --- FETCH USERS ---
$sql = "SELECT * FROM users $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// --- TOTAL USERS FOR PAGINATION ---
$total_sql = "SELECT COUNT(*) AS total FROM users $where";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_users = $total_row['total'];
$total_pages = ceil($total_users / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin/user_manage.css" rel="stylesheet">
</head>
<body>

    <!-- Sidebar -->
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
                <a href="user_manage.php" class="active"><i class="fas fa-users"></i> User Management</a>
                <a href="record_logs.php"><i class="fa-solid fa-file"></i> Record Logs</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="footer">© Document Record by ACLC Students</div>
    </div>

    <!-- Content -->
    <div class="content">
        <div class="welcome mb-4">
            <h5>Users Account Management</h5>
            <small><?= date("l, F j, Y - g:i A"); ?></small>
        </div>


        <!-- Controls -->
        <div class="controls d-flex justify-content-between align-items-center mb-3">
            <!-- Left: Search input + button -->
            <div class="d-flex align-items-center gap-2">
                <input type="text" id="searchInput" class="form-control" placeholder="Search users..." value="<?= htmlspecialchars($search) ?>">
                <button id="searchBtn" class="btn btn-primary">Search</button>
            </div>

            <!-- Right: Module filter -->
            <div class="d-flex align-items-center gap-2">
                <select id="moduleFilter" class="form-select">
                    <option value="">All Users</option>
                    <option value="Admin">Admin</option>
                    <option value="Encoder">Encoder</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>

                <a href="create_account.php" class="btn-create">+ Create Account</a>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card p-3">
            <div class="pagination-controls d-flex justify-content-between mb-2">
                <div>
                    <button class="btn btn-outline-secondary btn-sm" id="prevBtn">Previous</button>
                    <button class="btn btn-outline-secondary btn-sm" id="nextBtn">Next</button>
                </div>
                <div>
                    <span class="badge role-badge">Total Logs: <?= $total_users ?></span>
                </div>
            </div>
            <table class="table table-striped align-middle text-center">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars(
                                        trim(
                                            $row['first_name'] . 
                                            (!empty($row['middle_initial']) ? " " . $row['middle_initial'] : "") . 
                                            " " . $row['last_name']
                                        )
                                    ) ?>
                                </td>
                                <td><span class="role-badge"><?= ucfirst($row['role']) ?></span></td>
                                <td>
                                    <span class="badge <?= $row['status'] === 'Active' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="update_user.php?id=<?= $row['id'] ?>" class="btn-action btn-warning"><i class="fas fa-pen"></i></a>
                                    <button type="button" class="btn-action btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteModal"
                                        data-id="<?= $row['id'] ?>"
                                        data-username="<?= htmlspecialchars(
                                            trim(
                                                $row['first_name'] . 
                                                (!empty($row['middle_initial']) ? " " . $row['middle_initial'] : "") . 
                                                " " . $row['last_name']
                                            )
                                        ) ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted">No users found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <nav class="d-flex justify-content-end">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>">&lt;</a></li>
                    <?php endif; ?>
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>">&gt;</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post" action="controls/delete_user.php">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete <strong id="deleteUsername"></strong>?</p>
                        <input type="hidden" name="user_id" id="deleteUserId">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'User Deleted Successfully!',
            text: 'The user account has been archived.',
            confirmButtonColor: '#3085d6'
        });
    </script>
<?php endif; ?>

<script>
    const deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        document.getElementById('deleteUserId').value = button.getAttribute('data-id');
        document.getElementById('deleteUsername').textContent = button.getAttribute('data-username');
    });
</script>

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
        const filter = encodeURIComponent(moduleFilter.value);
        let url = `?page=${page}`;
        if(search) url += `&search=${search}`;
        if(filter) url += `&filter=${filter}`;
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
    <?php if(!empty($filter)): ?>
        moduleFilter.value = "<?= htmlspecialchars($filter) ?>";
    <?php endif; ?>
    <?php if(!empty($search)): ?>
        searchInput.value = "<?= htmlspecialchars($search) ?>";
    <?php endif; ?>
</script>
</body>
</html>

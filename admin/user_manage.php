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

// --- VIEW MODE (active or archive) ---
$is_archive = isset($_GET['view']) && $_GET['view'] === 'archive';

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

// --- Decide table based on view ---
if ($is_archive) {
    $table_name = "archived_users";
} else {
    $table_name = "users";
}

// --- FETCH USERS / ARCHIVED USERS ---
$sql = "SELECT * FROM $table_name $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// --- TOTAL COUNT FOR PAGINATION ---
$total_sql = "SELECT COUNT(*) AS total FROM $table_name $where";
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
            <?php
                $ws = $conn->query("SELECT logo FROM website_settings WHERE id=1")->fetch_assoc();
                $site_logo = $ws ? $ws['logo'] : 'assets/images/default-logo.png';
            ?>
            <img src="../<?= $site_logo ?>" alt="Website Logo">
                <p>
                    <?= htmlspecialchars($admin_fullname) ?><br>
                    <small><?= ucfirst($admin['role']) ?></small>
                </p>
            </div>
            <div class="nav-menu">
    <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
    <a href="user_manage.php"><i class="fas fa-users"></i> User Management</a>
    <a href="website_settings.php"><i class="fas fa-cog"></i> Website Settings</a>
    <a href="record_logs.php"><i class="fa-solid fa-file"></i> Record Logs</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

        </div>
        <div class="footer">© Document Record by ACLC Students</div>
    </div>

    <!-- Content -->
    <div class="content">
        <div class="welcome mb-4">
            <h5><?= $is_archive ? 'Archived Users' : 'Users Account Management' ?></h5>
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

                <?php if (!$is_archive): ?>
                    <a href="create_account.php" class="btn-create">+ Create Account</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card p-3">
            <div class="pagination-controls d-flex justify-content-between mb-2">
                <div>
                    <button class="btn btn-outline-secondary btn-sm" id="prevBtn">Previous</button>
                    <button class="btn btn-outline-secondary btn-sm" id="nextBtn">Next</button>
                </div>
                
                <div class="d-flex align-items-center gap-2">
                <?php if ($is_archive): ?>
                        <button class="btn btn-outline-primary btn-sm"
                            onclick="window.location.href='user_manage.php'">
                            View Active Users
                        </button>
                    <?php else: ?>
                        <button class="btn btn-outline-secondary btn-sm"
                            onclick="window.location.href='user_manage.php?view=archive'">
                            View Archive
                        </button>
                    <?php endif; ?>
                    <span class="badge role-badge">
                        <?= $is_archive ? 'Total Archived: ' : 'Total Logs: ' ?><?= $total_users ?>
                    </span>

                
                </div>
            </div>

            <table class="table table-striped align-middle text-center">
                <thead>
                    <tr>
                        <th>Full Name</th>
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
                                    <?php if (!$is_archive): ?>
                                        <!-- Active view: Edit + Archive -->
                                        <a href="update_user.php?id=<?= $row['id'] ?>" class="btn-action btn-warning">
                                            <i class="fas fa-pen"></i>
                                        </a>
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
                                            <i class="fas fa-archive"></i>
                                        </button>
                                    <?php else: ?>
                                        <!-- Archive view: Recover + Permanent Delete -->
                                        <button type="button" class="btn-action btn-success"
                                            data-bs-toggle="modal"
                                            data-bs-target="#recoverModal"
                                            data-id="<?= $row['id'] ?>"
                                            data-username="<?= htmlspecialchars(
                                                trim(
                                                    $row['first_name'] . 
                                                    (!empty($row['middle_initial']) ? " " . $row['middle_initial'] : "") . 
                                                    " " . $row['last_name']
                                                )
                                            ) ?>">
                                            <i class="fas fa-undo"></i>
                                        </button>

                                        <button type="button" class="btn-action btn-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#permanentDeleteModal"
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
                                    <?php endif; ?>
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
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?><?= $is_archive ? '&view=archive' : '' ?>">&lt;</a>
                        </li>
                    <?php endif; ?>
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?><?= $is_archive ? '&view=archive' : '' ?>">&gt;</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Archive (Delete) Modal - moves user to archived_users -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post" action="controls/delete_user.php">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Archive User</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to archive <strong id="deleteUsername"></strong>?</p>
                        <input type="hidden" name="user_id" id="deleteUserId">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Archive</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Recover Modal -->
    <div class="modal fade" id="recoverModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post" action="controls/recover_user.php">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Recover User</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to recover <strong id="recoverUsername"></strong>?</p>
                        <input type="hidden" name="archived_id" id="recoverUserId">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Recover</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Permanent Delete Modal -->
    <div class="modal fade" id="permanentDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post" action="controls/delete_archived_user.php">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title">Permanent Delete</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>This action cannot be undone.</p>
                        <p>Are you sure you want to permanently delete <strong id="permanentDeleteUsername"></strong>?</p>
                        <input type="hidden" name="archived_id" id="permanentDeleteUserId">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Permanently</button>
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
            title: 'User Archived Successfully!',
            text: 'The user account has been moved to archive.',
            confirmButtonColor: '#3085d6'
        });
    </script>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'recovered'): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'User Recovered!',
            text: 'The user has been moved back to active users.',
            confirmButtonColor: '#3085d6'
        });
    </script>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'permadeleted'): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'User Permanently Deleted!',
            text: 'The archived record has been removed permanently.',
            confirmButtonColor: '#3085d6'
        });
    </script>
<?php endif; ?>

<script>
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('deleteUserId').value = button.getAttribute('data-id');
            document.getElementById('deleteUsername').textContent = button.getAttribute('data-username');
        });
    }

    const recoverModal = document.getElementById('recoverModal');
    if (recoverModal) {
        recoverModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('recoverUserId').value = button.getAttribute('data-id');
            document.getElementById('recoverUsername').textContent = button.getAttribute('data-username');
        });
    }

    const permanentDeleteModal = document.getElementById('permanentDeleteModal');
    if (permanentDeleteModal) {
        permanentDeleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('permanentDeleteUserId').value = button.getAttribute('data-id');
            document.getElementById('permanentDeleteUsername').textContent = button.getAttribute('data-username');
        });
    }
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
        <?php if ($is_archive): ?>
            url += '&view=archive';
        <?php endif; ?>
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

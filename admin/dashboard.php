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

// Fetch latest 10 login logs with full name
$logs_sql = "
    SELECT l.*, 
           u.first_name, 
           u.middle_initial, 
           u.last_name, 
           u.role AS user_role
    FROM login_logs l
    LEFT JOIN users u ON l.username = u.username
    ORDER BY l.login_time DESC
    LIMIT 10
";
$logs = $conn->query($logs_sql); // execute query

// Total Accounts
$sql = "SELECT COUNT(*) AS total FROM users";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $total_accounts = $row['total'];
}

// Active Accounts
$sql = "SELECT COUNT(*) AS active FROM users WHERE status='Active'";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $users_active = $row['active'];
}

// Inactive Accounts
$sql = "SELECT COUNT(*) AS inactive FROM users WHERE status='Inactive'";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $users_inactive = $row['inactive'];
}

// --- Monthly Records Query for Bar Chart ---
$monthly_sql = "
SELECT 
    DATE_FORMAT(record_date, '%b %Y') AS month,
    SUM(documents) AS documents,
    SUM(communications) AS communications,
    SUM(certificates) AS certificates,
    SUM(activities) AS activities
FROM (
    SELECT date_in AS record_date, 1 AS documents, 0 AS communications, 0 AS certificates, 0 AS activities FROM documents WHERE date_in IS NOT NULL
    UNION ALL
    SELECT created_at AS record_date, 0 AS documents, 1 AS communications, 0 AS certificates, 0 AS activities FROM communications
    UNION ALL
    SELECT created_at AS record_date, 0 AS documents, 0 AS communications, 1 AS certificates, 0 AS activities FROM certificate_records
    UNION ALL
    SELECT created_at AS record_date, 0 AS documents, 0 AS communications, 0 AS certificates, 1 AS activities FROM activity_designs
) AS combined
GROUP BY YEAR(record_date), MONTH(record_date)
ORDER BY record_date ASC
";

$monthly_result = $conn->query($monthly_sql);

// Prepare arrays
$months = [];
$monthly_totals = [];

while($row = $monthly_result->fetch_assoc()){
    $months[] = $row['month'];
    $monthly_totals[] = 
        intval($row['documents']) +
        intval($row['communications']) +
        intval($row['certificates']) +
        intval($row['activities']);
}

// --- Total Pie Chart Counts ---
$total_documents = $conn->query("SELECT COUNT(*) AS count FROM documents")->fetch_assoc()['count'];
$total_communication = $conn->query("SELECT COUNT(*) AS count FROM communications")->fetch_assoc()['count'];
$total_certificates = $conn->query("SELECT COUNT(*) AS count FROM certificate_records")->fetch_assoc()['count'];
$total_activities = $conn->query("SELECT COUNT(*) AS count FROM activity_designs")->fetch_assoc()['count'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin/dashboard.css" rel="stylesheet">
    <style>
    .card {
        min-height: 400px;
        display: flex;
        flex-direction: column;
    }

    .card h6 {
        margin-bottom: 15px;
    }

    #barChart, #pieChart {
        flex-grow: 1;
    }
    </style>
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
                <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
                <a href="user_manage.php"><i class="fas fa-users"></i> User Management</a>
                <a href="record_logs.php"><i class="fa-solid fa-file"></i> Record Logs</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="footer">© Document Record by ACLC Students</div>
    </div>



    <!-- Content -->
    <div class="content">
        <div class="welcome mb-4">
            <h5>Welcome back, <?= htmlspecialchars($admin_fullname) ?></h5>
            <small><?= date("l, F j, Y - g:i A"); ?></small>
        </div>

        <!-- Dashboard Cards -->
        <div class="row g-4">
            <div class="col-md-4">
                <div class="dashboard-card">
                    <div>
                        <h6>Users</h6>
                        <p><?= $users_active ?></p>
                    </div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card">
                    <div>
                        <h6>Active Sessions</h6>
                        <p><?= $users_inactive ?></p>
                    </div>
                    <div class="icon"><i class="fas fa-signal"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card">
                    <div>
                        <h6>Total Accounts</h6>
                        <p><?= $total_accounts ?></p>
                    </div>
                    <div class="icon"><i class="fas fa-id-card"></i></div>
                </div>
            </div>
        </div>

            <!-- Charts Section -->
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card p-3">
                    <h6 class="mb-3">Monthly Record Volume</h6>
                    <div id="barChart"></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3">
                    <h6 class="mb-3">Record Category Distribution</h6>
                    <div id="pieChart"></div>
                </div>
            </div>
        </div>

        <!-- Recent Login Logs -->
        <div class="mt-4">
            <h5>Recent Login Logs</h5>
            <div class="card p-3">
                <table class="table align-middle text-center">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Role</th>
                            <th>Login Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($logs && $logs->num_rows > 0): ?>
                        <?php while($log = $logs->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars(
                                        $log['first_name'] . 
                                        (!empty($log['middle_initial']) ? " " . $log['middle_initial'] : "") . 
                                        " " . $log['last_name']
                                    ) ?>
                                </td>
                                <td><span class="role-badge"><?= ucfirst($log['user_role'] ?? $log['role']) ?></span></td>
                                <td><?= date("M d, Y h:i A", strtotime($log['login_time'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-muted">No login logs yet</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    var months = <?= json_encode($months) ?>;
    var monthTotals = <?= json_encode($monthly_totals) ?>;

    // === Bar Chart ===
    var optionsBar = {
        chart: {
            type: 'bar',
            height: 300,
            toolbar: { show: false }
        },
        series: [{
            name: 'Total Records',
            data: monthTotals
        }],
        xaxis: { categories: months }
    };
    var barChart = new ApexCharts(document.querySelector("#barChart"), optionsBar);
    barChart.render();

    var optionsPie = {
        chart: { 
            type: 'pie', 
            height: 330,
            toolbar: { show: false }
        },

        series: [
            <?= $total_documents ?>,
            <?= $total_communication ?>,
            <?= $total_certificates ?>,
            <?= $total_activities ?>
        ],

        labels: ['Documents','Communications','Certificates','Activity Designs'],

        dataLabels: {
            enabled: true,
            formatter: function (val, opts) {
                return val.toFixed(1) + "%";
            },
            dropShadow: {
                enabled: false
            },
            style: {
                fontSize: '10px',
                fontWeight: 'bold',
                colors: ['#fff']
            }
        },

        plotOptions: {
            pie: {
                dataLabels: {
                    offset: -15,
                    minAngleToShowLabel: 10
                }
            }
        }
    };
    var pieChart = new ApexCharts(document.querySelector("#pieChart"), optionsPie);
    pieChart.render();
</script>
</body>
</html>

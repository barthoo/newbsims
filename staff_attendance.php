<?php


session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Get active academic session
$active_session = mysqli_fetch_assoc(mysqli_query($conn, "SELECT academic_year, term FROM academic_sessions WHERE is_active=1 LIMIT 1"));
$active_academic = $active_session
    ? $active_session['academic_year'] . ' - ' . $active_session['term']
    : 'No active academic session';

// Prevent attendance if no active session
$attendance_disabled = !$active_session;

// Handle attendance submission (real time: always uses current date/time)
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance'])) {
    if ($attendance_disabled) {
        $alert = "Attendance cannot be taken. No active academic session!";
    } else {
        $date = date('Y-m-d');
        $academic_year = mysqli_real_escape_string($conn, $active_session['academic_year']);
        $term = mysqli_real_escape_string($conn, $active_session['term']);
        foreach ($_POST['attendance'] as $staffid => $status) {
            $status = mysqli_real_escape_string($conn, $status);
            $staffid = mysqli_real_escape_string($conn, $staffid);
            // Prevent duplicate for the same day, academic year, and term
            $check = mysqli_query($conn, "SELECT * FROM staff_attendance WHERE staffid='$staffid' AND date='$date' AND academic_year='$academic_year' AND term='$term'");
            if (mysqli_num_rows($check) == 0) {
                mysqli_query($conn, "INSERT INTO staff_attendance (staffid, date, status, academic_year, term) VALUES ('$staffid', '$date', '$status', '$academic_year', '$term')");
                $alert = "Attendance saved!";
            } else {
                $alert = "This staff has already been marked for today!";
            }
        }
    }
}

// Export to Excel
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=staff_attendance_" . date('Ymd') . ".xls");
    echo "Staff Name\tStatus\tDate\tAcademic Year\tTerm\n";
    $filter_staff = isset($_GET['filter_staff']) ? $_GET['filter_staff'] : '';
    $filter_term = isset($_GET['filter_term']) ? $_GET['filter_term'] : '';
    $from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
    $to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';
    $where = "1=1";
    if ($filter_staff) {
        $filter_staff = mysqli_real_escape_string($conn, $filter_staff);
        $where .= " AND sa.staffid = '$filter_staff'";
    }
    if ($filter_term) {
        $filter_term = mysqli_real_escape_string($conn, $filter_term);
        $where .= " AND sa.term = '$filter_term'";
    }
    if ($from_date && $to_date) {
        $where .= " AND sa.date BETWEEN '$from_date' AND '$to_date'";
    } elseif ($from_date) {
        $where .= " AND sa.date >= '$from_date'";
    } elseif ($to_date) {
        $where .= " AND sa.date <= '$to_date'";
    }
    $attendance_query = "
        SELECT sa.*, s.name 
        FROM staff_attendance sa 
        LEFT JOIN stafftb s ON sa.staffid = s.staffid 
        WHERE $where
        ORDER BY sa.date ASC, s.name ASC
    ";
    $attendance_result = mysqli_query($conn, $attendance_query);
    while ($row = mysqli_fetch_assoc($attendance_result)) {
        echo $row['name'] . "\t" . $row['status'] . "\t" . $row['date'] . "\t" . $row['academic_year'] . "\t" . $row['term'] . "\n";
    }
    exit();
}

// --- SEARCH LOGIC FOR STAFF LIST ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_sql = $search ? "WHERE name LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'" : '';
$staff_result = mysqli_query($conn, "SELECT staffid, name FROM stafftb $search_sql ORDER BY name ASC");
$staff_list = [];
while ($row = mysqli_fetch_assoc($staff_result)) {
    $staff_list[] = $row;
}

// --- FILTER LOGIC FOR VIEW MARKED ATTENDANCE ---
$filter_staff = isset($_GET['filter_staff']) ? $_GET['filter_staff'] : '';
$filter_term = isset($_GET['filter_term']) ? $_GET['filter_term'] : '';
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';
$where = "1=1";
if ($filter_staff) {
    $filter_staff = mysqli_real_escape_string($conn, $filter_staff);
    $where .= " AND sa.staffid = '$filter_staff'";
}
if ($filter_term) {
    $filter_term = mysqli_real_escape_string($conn, $filter_term);
    $where .= " AND sa.term = '$filter_term'";
}
if ($from_date && $to_date) {
    $where .= " AND sa.date BETWEEN '$from_date' AND '$to_date'";
} elseif ($from_date) {
    $where .= " AND sa.date >= '$from_date'";
} elseif ($to_date) {
    $where .= " AND sa.date <= '$to_date'";
}
$attendance_records = [];
$attendance_query = "
    SELECT sa.*, s.name 
    FROM staff_attendance sa 
    LEFT JOIN stafftb s ON sa.staffid = s.staffid 
    WHERE $where
    ORDER BY sa.date ASC, s.name ASC
";
$attendance_result = mysqli_query($conn, $attendance_query);
while ($row = mysqli_fetch_assoc($attendance_result)) {
    $attendance_records[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Staff Attendance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .dashboard-title { font-size: 22px; font-weight: bold; color: #0e7490; margin: 10px 0; }
        .main-flex-container {
            display: flex;
            max-width: 1100px;
            margin: 30px auto 0 auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 12px #e0e0e0;
            min-height: 70vh;
            width: 100%;
        }
        .dashboard-container {
            flex: 1;
            padding: 30px;
            min-width: 0;
        }
        .attendance-table { width: 100%; margin-top: 20px; }
        .attendance-table th, .attendance-table td { padding: 8px 12px; }
        .attendance-table th { background: #38bdf8; color: #fff; }
        .attendance-table tr:nth-child(even) { background: #f9fafb; }
        .alert { margin: 10px 0; }
        .filter-form, .search-form { margin: 20px 0 10px 0; display: flex; gap: 10px; align-items: center; }
        .search-form { margin-bottom: 0; }
        @media (max-width: 1200px) {
            .main-flex-container {
                max-width: 98vw;
            }
        }
        @media (max-width: 900px) {
            .main-flex-container {
                flex-direction: column;
                max-width: 99vw;
                margin: 10px auto;
                box-shadow: none;
                border-radius: 0;
            }
            .dashboard-container {
                padding: 15px;
            }
        }
        @media (max-width: 600px) {
            .main-flex-container {
                max-width: 100vw;
                margin: 0;
            }
        }
    </style>
    <script>
    // Live search for staff name in attendance marking
    function filterStaff() {
        var input = document.getElementById('searchInput');
        var filter = input.value.toLowerCase();
        var rows = document.querySelectorAll('.attendance-table.marking tbody tr');
        rows.forEach(function(row) {
            var nameCell = row.querySelector('td');
            if (nameCell) {
                var name = nameCell.textContent.toLowerCase();
                row.style.display = name.indexOf(filter) > -1 ? '' : 'none';
            }
        });
    }
    </script>
</head>
<body>
<link rel="stylesheet" href="admin-panel.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<header class="header">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle admin panel">
        <i class="fa fa-bars"></i>
    </button>
    
    <span class="dashboard-title"><i class="fa fa-gauge"></i> Administrator Dashboard</span>
    <div class="logout">
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</header>
<div style="display: flex;">
    <nav class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <i class="fa fa-user-shield"></i> Admin Panel
        </div>
        <ul>
            <li class="active"><a href="adminhome.php"><i class="fa fa-home"></i> Dashboard</a></li>
            <li><a href="add_staff.php"><i class="fa fa-user-plus"></i> Add Staff</a></li>
            <li><a href="view_staff.php"><i class="fa fa-users"></i> Staff List</a></li>
        </ul>
    </nav>
    <div class="main-flex-container">

        <div class="dashboard-container">
            <!-- Show active academic session -->
            <div style="margin-bottom:15px; font-size:16px; color:#0e7490;">
                <b>Active Academic Session:</b>
                <?php echo htmlspecialchars($active_academic); ?>
            </div>
            <?php if ($alert): ?>
                <div class="alert alert-<?php echo ($alert == "Attendance saved!") ? "success" : "danger"; ?>" id="alertBox"><?php echo htmlspecialchars($alert); ?></div>
                <script>
                    setTimeout(function(){
                        var alertBox = document.getElementById('alertBox');
                        if(alertBox) alertBox.style.display='none';
                    }, 3000);
                </script>
            <?php endif; ?>

            <!-- Search Form (live filter) -->
            <form method="get" class="search-form" onsubmit="return false;">
                <input type="text" id="searchInput" name="search" placeholder="Search staff name..." value="<?php echo htmlspecialchars($search); ?>" class="form-control" style="width:200px;" onkeyup="filterStaff()">
            </form>

            <!-- Attendance Form -->
            <form method="post">
                <fieldset <?php if ($attendance_disabled) echo 'disabled'; ?>>
                <table class="attendance-table marking table table-bordered">
                    <thead>
                        <tr>
                            <th>Staff Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($staff_list as $staff): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($staff['name']); ?></td>
                            <td>
                                <select name="attendance[<?php echo $staff['staffid']; ?>]" class="form-control" required>
                                    <option value="">Select</option>
                                    <option value="Present">Present</option>
                                    <option value="Absent">Absent</option>
                                    <option value="Permission">Permission</option>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-success" style="margin-top:10px;">
                    <i class="fa fa-save"></i> Save Attendance
                </button>
                </fieldset>
            </form>
            <?php if ($attendance_disabled): ?>
                <div class="alert alert-warning" style="margin-top:10px;">
                    Attendance cannot be taken. No active academic session!
                </div>
            <?php endif; ?>

            <!-- View Attendance Records -->
            <h4 style="margin-top:40px;">Staff Attendance List</h4>
            <form method="get" class="filter-form" style="gap:15px;">
                <label for="filter_staff"><b>Staff:</b></label>
                <select name="filter_staff" id="filter_staff" class="form-control" style="width:180px;">
                    <option value="">All Staff</option>
                    <?php
                    $all_staff = mysqli_query($conn, "SELECT staffid, name FROM stafftb ORDER BY name ASC");
                    while ($s = mysqli_fetch_assoc($all_staff)) {
                        $selected = ($filter_staff == $s['staffid']) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($s['staffid']) . '" ' . $selected . '>' . htmlspecialchars($s['name']) . '</option>';
                    }
                    ?>
                </select>
                <label for="filter_term"><b>Term:</b></label>
                <select name="filter_term" id="filter_term" class="form-control" style="width:120px;">
                    <option value="">All Terms</option>
                    <?php
                    $terms = mysqli_query($conn, "SELECT DISTINCT term FROM staff_attendance ORDER BY term ASC");
                    while ($t = mysqli_fetch_assoc($terms)) {
                        $selected = ($filter_term == $t['term']) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($t['term']) . '" ' . $selected . '>' . htmlspecialchars($t['term']) . '</option>';
                    }
                    ?>
                </select>
                <label for="from_date"><b>From:</b></label>
                <input type="date" id="from_date" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>" class="form-control">
                <label for="to_date"><b>To:</b></label>
                <input type="date" id="to_date" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>" class="form-control">
                <button type="submit" class="btn btn-info btn-sm"><i class="fa fa-filter"></i> Filter</button>
                <a href="?filter_staff=<?php echo htmlspecialchars($filter_staff); ?>&filter_term=<?php echo htmlspecialchars($filter_term); ?>&from_date=<?php echo htmlspecialchars($from_date); ?>&to_date=<?php echo htmlspecialchars($to_date); ?>&export=excel" class="btn btn-success btn-sm" style="margin-left:10px;">
                    <i class="fa fa-file-excel"></i> Export to Excel
                </a>
            </form>
            <table class="attendance-table table table-bordered">
                <thead>
                    <tr>
                        <th>Staff Name</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($attendance_records) > 0): ?>
                    <?php foreach ($attendance_records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['name']); ?></td>
                            <td><?php echo htmlspecialchars($record['status']); ?></td>
                            <td><?php echo htmlspecialchars($record['date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align:center;">No attendance records found for this filter.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
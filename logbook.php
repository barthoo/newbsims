<?php
// filepath: c:\xampp\htdocs\bsims\logbook.php

session_start();
$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Handle new activity submission
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_activity'])) {
    $activity_date = mysqli_real_escape_string($conn, $_POST['activity_date']);
    $activity_title = mysqli_real_escape_string($conn, $_POST['activity_title']);
    $activity_details = mysqli_real_escape_string($conn, $_POST['activity_details']);
    if ($activity_date && $activity_title && $activity_details) {
        mysqli_query($conn, "INSERT INTO logbook (activity_date, activity_title, activity_details) VALUES ('$activity_date', '$activity_title', '$activity_details')");
        $alert = '<div class="alert alert-success">Activity added successfully!</div>';
    } else {
        $alert = '<div class="alert alert-danger">All fields are required.</div>';
    }
}

// Handle filter
$from = isset($_GET['from']) ? $_GET['from'] : '';
$to = isset($_GET['to']) ? $_GET['to'] : '';
$where = [];
if ($from) $where[] = "activity_date >= '" . mysqli_real_escape_string($conn, $from) . "'";
if ($to) $where[] = "activity_date <= '" . mysqli_real_escape_string($conn, $to) . "'";
$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

// Fetch activities
$activities = [];
if (isset($_GET['show']) || $from || $to) {
    $result = mysqli_query($conn, "SELECT * FROM logbook $where_sql ORDER BY activity_date DESC, id DESC");
    while ($row = mysqli_fetch_assoc($result)) {
        $activities[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>School Log Book</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="admin-panel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body { background: #f8f9fa; }
        .main-layout {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            min-height: 100vh;
            width: 100%;
        }
        .admin-sidebar {
            min-width: 220px;
            background: #0e7490;
            color: #fff;
            min-height: 100vh;
            box-shadow: 2px 0 8px rgba(0,0,0,0.04);
            padding-top: 0;
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 10;
            transition: all 0.3s;
        }
        .sidebar-header {
            padding: 24px 16px 18px 16px;
            font-size: 1.3em;
            font-weight: bold;
            background: #155e75;
            text-align: center;
            letter-spacing: 1px;
        }
        .admin-sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .admin-sidebar ul li {
            border-bottom: 1px solid #19788e;
        }
        .admin-sidebar ul li a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            text-decoration: none;
            padding: 14px 20px;
            transition: background 0.2s;
            font-size: 1.08em;
        }
        .admin-sidebar ul li a i {
            min-width: 22px;
            text-align: center;
            font-size: 1.1em;
        }
        .admin-sidebar ul li a:hover,
        .admin-sidebar ul li.active a {
            background: #38bdf8;
            color: #0e7490;
            font-weight: bold;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px #ddd;
            padding: 30px;
            flex: 1 1 0;
            min-width: 0;
        }
        .logbook-title { font-size: 2em; font-weight: bold; margin-bottom: 20px; text-align: center; }
        .form-inline .form-group { margin-right: 10px; }
        .table th, .table td { vertical-align: middle !important; }
        .alert { max-width: 400px; margin: 10px auto; }
        @media (max-width: 900px) {
            .main-layout { flex-direction: column; }
            .admin-sidebar { width: 100vw; min-width: 100vw; height: auto; position: static; }
            .container { margin: 10px auto; max-width: 98vw; }
        }
        @media (max-width: 700px) {
            .container { padding: 10px 2vw; }
            .logbook-title { font-size: 1.3em; }
        }
    </style>
</head>
<body>
<header class="header">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle admin panel">
        <i class="fa fa-bars"></i>
    </button>
        <span class="dashboard-title" style="margin: 0 auto; display: flex; align-items: center; justify-content: center; flex: 1; text-align: center;">
        <i class="fa fa-book"></i>
        <span style="margin-left: 8px;">School Log Book</span>
    </span>
    <div class="logout">
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</header>
<div class="main-layout">
    <nav class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <i class="fa fa-user-shield"></i> Admin Panel
        </div>
        <ul>
            <li class="active"><a href="adminhome.php"><i class="fa fa-home"></i> Dashboard</a></li>
           
        </ul>
    </nav>
    <div class="container">
      
        <?php if ($alert) echo $alert; ?>
        <form method="post" class="form-horizontal" style="margin-bottom:30px;">
            <div class="form-group">
                <label class="col-sm-2 control-label">Date:</label>
                <div class="col-sm-4">
                    <input type="date" name="activity_date" class="form-control" required>
                </div>
                <label class="col-sm-2 control-label">Title:</label>
                <div class="col-sm-4">
                    <input type="text" name="activity_title" class="form-control" placeholder="Activity Title" required>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Details:</label>
                <div class="col-sm-10">
                    <textarea name="activity_details" class="form-control" rows="3" placeholder="Describe the activity..." required></textarea>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" name="add_activity" class="btn btn-success"><i class="fa fa-plus"></i> Add Activity</button>
                </div>
            </div>
        </form>
        <form method="get" class="form-inline" style="margin-bottom:20px; text-align:center;">
            <div class="form-group">
                <label>From:</label>
                <input type="date" name="from" class="form-control" value="<?php echo htmlspecialchars($from); ?>">
            </div>
            <div class="form-group">
                <label>To:</label>
                <input type="date" name="to" class="form-control" value="<?php echo htmlspecialchars($to); ?>">
            </div>
            <button type="submit" name="show" class="btn btn-info"><i class="fa fa-search"></i> Check Previous Activities</button>
            <a href="logbook.php" class="btn btn-default"><i class="fa fa-refresh"></i> Reset</a>
        </form>
        <?php if ($activities): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Title</th>
                        <th>Details</th>
                        <th>Recorded At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activities as $act): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($act['activity_date']); ?></td>
                        <td><?php echo htmlspecialchars($act['activity_title']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($act['activity_details'])); ?></td>
                        <td><?php echo htmlspecialchars($act['created_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php elseif (isset($_GET['show']) || $from || $to): ?>
            <div class="alert alert-warning text-center">No activities found for the selected period.</div>
        <?php endif; ?>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var sidebarToggle = document.getElementById('sidebarToggle');
    var adminSidebar = document.getElementById('adminSidebar');
    if (sidebarToggle && adminSidebar) {
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            adminSidebar.classList.toggle('show');
        });
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 1000 &&
                !adminSidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                adminSidebar.classList.remove('show');
            }
        });
        window.addEventListener('resize', function() {
            if (window.innerWidth > 1000) {
                adminSidebar.classList.remove('show');
            }
        });
    }
});
</script>
</body>
</html>
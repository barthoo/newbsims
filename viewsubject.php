<?php


session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Fetch all subjects
$subjects = mysqli_query($conn, "SELECT * FROM subjecttb ORDER BY subjectid ASC");

// Alert message
$alert = '';
if (isset($_SESSION['alert'])) {
    $alert = $_SESSION['alert'];
    unset($_SESSION['alert']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Subjects</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background: #f7f7fa;
        }
        .main-flex-container {
            display: inline-block;
            align-items: flex-start;
            justify-content: center;
            min-height: 100vh;
            width: 100%;
            margin-top: 30px;
            position: relative;
            left: 50%;
            transform: translateX(-50%);
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
        .header {
            background: #0e7490;
            color: #fff;
            padding: 18px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 1.3em;
            font-weight: 600;
            letter-spacing: 1px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header a {
            color: #fff;
            text-decoration: none;
        }
        .logout a.btn, .logout-btn {
            background: linear-gradient(90deg, #e74c3c 60%, #ff7675 100%);
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            padding: 8px 22px;
            color: #fff !important;
            box-shadow: 0 2px 8px rgba(231,76,60,0.08);
            transition: background 0.2s;
            margin-left: 10px;
            text-decoration: none;
            display: inline-block;
        }
        .logout a.btn:hover, .logout-btn:hover {
            background: linear-gradient(90deg, #c0392b 60%, #ff7675 100%);
            color: #fff !important;
        }
        .dashboard-container {
            max-width: 900px;
            margin: 40px auto 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(60,60,100,0.12);
            padding: 40px 40px 30px 40px;
            position: relative;
            flex: 1;
        }
        .subject-header {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 20px;
            letter-spacing: 2px;
            color: #007bff;
            text-align: center;
        }
        .alert-success, .alert-danger {
            font-size: 13px;
            padding: 6px 12px;
            margin-bottom: 10px;
            display: inline-block;
            width: auto;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        @media (max-width: 900px) {
            .main-flex-container {
                flex-direction: column;
                align-items: center;
                display: inline-block;
                width: 100vw;
                margin-top: 10px;
                left: 50%;
                transform: translateX(-50%);
            }
            .dashboard-container { margin: 20px 2vw 0 2vw; padding: 20px 5vw; }
            .admin-sidebar { min-width: 100vw; height: auto; position: static; }
            .subject-header { font-size: 1.3em; }
        }
        @media (max-width: 600px) {
            .admin-sidebar {
                min-width: 100px !important;
                width: 60vw !important;
                max-width: 180px !important;
                display: none !important;
                position: absolute;
                left: 0;
                top: 60px;
                height: auto;
                z-index: 999;
                background: #0e7490;
                box-shadow: 2px 0 8px rgba(0,0,0,0.08);
            }
            .admin-sidebar.show {
                display: block !important;
            }
            .sidebar-toggle {
                display: inline-block;
            }
            .main-flex-container {
                flex-direction: column;
                align-items: center;
                display: inline-block;
                width: 100vw;
                margin-top: 0;
                left: 50%;
                transform: translateX(-50%);
            }
            .dashboard-container { 
                padding: 8px 1vw; 
                margin: 20px auto 0 auto !important;
                float: none !important;
                left: 0 !important;
                right: 0 !important;
                position: relative !important;
            }
            .header { flex-direction: row; padding: 10px 5px; font-size: 1em; }
            .subject-header { font-size: 1.1em; }
            table.table th, table.table td { font-size: 12px; padding: 4px; }
        }
        @media (max-width: 500px) {
            .dashboard-container { padding: 4px 1vw; }
            table.table th, table.table td { font-size: 10px; padding: 2px; }
        }
        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            color: #fff;
            font-size: 2em;
            margin-right: 10px;
            cursor: pointer;
        }
        @media (max-width: 600px) {
            .sidebar-toggle {
                display: inline-block;
            }
        }
    </style>
</head>
<body>
<header class="header">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle admin panel">
        <i class="fa fa-bars"></i>
    </button>
    <a href="adminhome.php" style="flex:1;">Administrator Dashboard</a>
    <div class="logout">
        <a href="logout.php" class="btn btn-danger"><i class="fa fa-sign-out-alt"></i> Logout</a>
    </div>
</header>
<div class="main-flex-container">
    <nav class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <i class="fa fa-user-shield"></i> Admin Panel
        </div>
        <ul>
            <li><a href="adminhome.php"><i class="fa fa-home"></i> Dashboard</a></li>
            <li><a href="add_staff.php"><i class="fa fa-user-plus"></i> Add Staff</a></li>
            <li><a href="view_staff.php"><i class="fa fa-users"></i> Staff List</a></li>
            <li><a href="staff_attendance.php"><i class="fa fa-calendar-check"></i> Staff Attendance</a></li>
            <li><a href="students.php"><i class="fa fa-user-graduate"></i> Students</a></li>
            <li><a href="tuition_fees.php"><i class="fa fa-money-bill"></i> Tuition Fees</a></li>
            <li><a href="settings.php"><i class="fa fa-cogs"></i> Settings</a></li>
        </ul>
    </nav>
    <div class="dashboard-container">
        <div class="subject-header">SUBJECTS</div>
        <?php if ($alert): ?>
            <div class="alert alert-success" id="alertBox"><?php echo htmlspecialchars($alert); ?></div>
            <script>
                setTimeout(function(){
                    var alertBox = document.getElementById('alertBox');
                    if(alertBox) alertBox.style.display='none';
                }, 2000);
            </script>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Subject ID</th>
                        <th>Subject Name</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($subjects) == 0) {
                        echo "<tr><td colspan='3' style='text-align:center;'>No subjects found.</td></tr>";
                    }
                    while ($row = mysqli_fetch_assoc($subjects)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['subjectid']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['subjectname']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
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
        // Hide sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 600) {
                if (!adminSidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    adminSidebar.classList.remove('show');
                }
            }
        });
    }
});
</script>
</body>
</html>
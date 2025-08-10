<?php


session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
} elseif ($_SESSION['username'] == 'student') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Administrator Dashboard</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            background: #f7f7fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            box-sizing: border-box;
        }
        body {
            min-height: 100vh;
        }
        .header {
            background: #0e7490;
            color: #fff;
            padding: 18px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 1.2em;
            font-weight: 600;
            letter-spacing: 1px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(14,116,144,0.08);
        }
        .header a {
            color: #fff;
            text-decoration: none;
        }
        .header .dashboard-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.2em;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .logout a.btn, .logout-btn {
            background: linear-gradient(90deg, #e74c3c 60%, #ff7675 100%);
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            padding: 8px 18px;
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
        .main-flex-container {
            display: flex;
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
            display: block;
            color: #fff;
            text-decoration: none;
            padding: 14px 20px;
            transition: background 0.2s;
            font-size: 1.08em;
        }
        .admin-sidebar ul li a:hover,
        .admin-sidebar ul li.active a {
            background: #38bdf8;
            color: #0e7490;
            font-weight: bold;
        }
        .icon-navbar {
            display: flex;
            gap: 40px;
            margin-left:120px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .icon-navbar a {
            color: #333;
            text-align: center;
            text-decoration: none;
            font-size: 18px;
            transition: color 0.2s;
            min-width: 90px;
        }
        .icon-navbar a:hover {
            color: #007bff;
            text-decoration: none;
        }
        .icon-navbar .fa {
            font-size: 2.5em;
            display: block;
            margin-bottom: 8px;
        }
        /* Hamburger menu styles */
        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            color: #fff;
            font-size: 2em;
            margin-right: 10px;
            cursor: pointer;
        }
        @media (max-width: 1100px) {
            .icon-navbar { margin-left: 0; justify-content: center; }
        }
        @media (max-width: 900px) {
            .main-flex-container { flex-direction: column; }
            .dashboard-container { margin: 20px 2vw 0 2vw; padding: 20px 5vw; }
            .admin-sidebar { min-width: 100vw; height: auto; position: static; }
            .icon-navbar { margin-left: 0; justify-content: center; }
        }
        @media (max-width: 600px) {
            .header {
                flex-direction: row;
                padding: 10px 5px;
                font-size: 1em;
            }
            .dashboard-title {
                font-size: 1em !important;
            }
            .icon-navbar { 
                gap: 10px;
                margin: 10px 0 0 0;
                flex-wrap: wrap;
                justify-content: center;
            }
            .icon-navbar .fa { font-size: 1.2em; }
            .sidebar-header { font-size: 1em; }
            .admin-sidebar ul li a { font-size: 1em; padding: 10px 10px; }
            .admin-sidebar {
                min-width: 70px !important;
                width: 55vw !important;
                max-width: 120px !important;
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
                width: 100vw;
            }
        }
        @media (max-width: 400px) {
            .icon-navbar a {
                font-size: 14px;
                min-width: 70px;
            }
            .icon-navbar .fa {
                font-size: 1em;
            }
        }
    </style>
</head>
<body>
<header class="header">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle admin panel">
        <i class="fa fa-bars"></i>
    </button>
    
           <span class="dashboard-title" style="margin: 0 auto; display: flex; align-items: center; justify-content: center; flex: 1; text-align: center;">
        <i class="fa fa-user"></i>
        <span style="margin-left: 8px;">Staff Management</span>
    </span>
   
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
            <li class="active"><a href="staff_management.php"><i class="fa fa-users-cog"></i> Staff Management</a></li>
            
        </ul>
    </nav>
    <div style="flex:1; min-width:0;">
        <nav class="icon-navbar">
            <a href="add_staff.php"><i class="fa fa-user-plus"></i>Add Staff</a>
            <a href="view_staff.php"><i class="fa fa-users"></i>View Staff</a>
            <a href="staff_attendance.php"><i class="fa fa-calendar-check"></i>Staff Attendance</a>
            <a href="staff_performance.php"><i class="fa fa-chart-line"></i>Staff Performance</a>
        </nav>
        <!-- Add your Add Staff form or content here -->
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
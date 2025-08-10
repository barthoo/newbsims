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
    <title>Administrator dashboard</title>
    <link rel="stylesheet" type="text/css" href="admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden; /* Prevent scrolling */
        }
        body {
            min-height: 100vh;
            height: 100vh;
            background: #f8fafc;
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            flex-direction: column;
        }
        .header {
            background: linear-gradient(90deg, #3b82f6 0%, #6366f1 100%);
            color: #fff;
            
            font-size: 1.6em;
            font-weight: 700;
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 56px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
        }
        .header a {
            color: #fff;
            text-decoration: none;
        }
        .logout {
            margin-left: auto;
        }
        .admin-sidebar {
            position: fixed;
            top: 56px;
            left: 0;
            height: calc(100vh - 56px);
            z-index: 99;
            background: #fff; 
            
        }
         



        .main-content {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 0 20px;
            min-height: 0;
            width: 100vw;
            margin-top: 80px;
            overflow: auto;
            height: calc(100vh - 56px);
        }
        .dashboard-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            justify-content: center;
            align-items: center;
            margin-top: 24px;
            width: 100%;
            max-width: 1200px;
        }
        .dashboard-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 16px #e0e7ff;
            padding: 48px 24px 36px 24px;
            text-align: center;
            width: 240px;
            min-width: 180px;
            cursor: pointer;
            border: 2px solid #e0e7ff;
            text-decoration: none;
            color: #3b5998;
            font-size: 1.15em;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .dashboard-card:hover {
            box-shadow: 0 16px 40px rgba(60,60,120,0.18);
            border-color: #6366f1;
            color: #6366f1;
            text-decoration: none;
        }
        .dashboard-card i {
            font-size: 54px;
            margin-bottom: 18px;
        }
        .dashboard-card .title {
            font-size: 1.25em;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .dashboard-card div:last-child {
            font-size: 1em;
        }
        h2 {
            margin-bottom: 18px;
            font-size: 2em;
        }
        @media (max-width: 1200px) {
            .dashboard-grid {
                gap: 24px;
                max-width: 100vw;
            }
            .dashboard-card {
                width: 38vw;
                min-width: 160px;
                max-width: 320px;
                padding: 32px 10px 24px 10px;
            }
        }
        @media (max-width: 900px) {
            .dashboard-grid {
                flex-direction: column;
                gap: 18px;
                align-items: center;
            }
            .dashboard-card {
                width: 90vw;
                max-width: 350px;
            }
            .main-content {
                padding: 0 2vw;
            }
            h2 {
                font-size: 1.3em;
            }
        }
        @media (max-width: 500px) {
            .dashboard-card {
                padding: 18px 4px 12px 4px;
                font-size: 1em;
            }
            h2 {
                font-size: 1.1em;
            }
            .header {
                font-size: 1em;
                padding: 8px 10px;
            }
        }


    </style>
</head>
<body>
<div class="admin-sidebar">
    <?php include 'admin_sidebar.php'; ?>
</div>
<header class="header ">
    <a href="adminhome.php">Administrator Dashboard</a>
    <div class="logout">
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
</header>
<div class="main-content" style="overflow:auto;">
    <h2>Welcome, Administrator</h2>
    <div class="dashboard-grid">
        <a href="staff_management.php" class="dashboard-card">
            <i class="fa fa-users"></i>
            <div class="title">Staff</div>
            <div>Manage staff</div>
        </a>
        <a href="student_management.php" class="dashboard-card">
            <i class="fa fa-user-graduate"></i>
            <div class="title">Students</div>
            <div>Manage students</div>
        </a>
        <a href="class_management.php" class="dashboard-card">
            <i class="fa fa-school"></i>
            <div class="title">Class</div>
            <div>Manage classes</div>
        </a>
        <a href="subject_management.php" class="dashboard-card">
            <i class="fa fa-book"></i>
            <div class="title">Subject</div>
            <div>Manage subjects</div>
        </a>
        <a href="academics.php" class="dashboard-card">
            <i class="fa fa-calendar-alt"></i>
            <div class="title">Academics</div>
            <div>Academic years</div>
        </a>
        <a href="inventory.php" class="dashboard-card">
            <i class="fa fa-boxes-stacked"></i>
            <div class="title">Inventory</div>
            <div>School assets</div>
        </a>
    
        <a href="sendnotice.php" class="dashboard-card">
            <i class="fa fa-bell"></i>
            <div class="title">Announcements</div>
            <div>Announcements</div>
        </a>
        <a href="fees.php" class="dashboard-card">
            <i class="fa fa-money-bill-wave-alt"></i>
            <div class="title">Fees</div>
            <div>Fees</div>
        </a>
         <a href="logbook.php" class="dashboard-card">
            <i class="fa fa-book"></i>
            <div class="title">Look Book</div>
            <div>School Activities</div>
        </a>
        <a href="audit_trail.php" class="dashboard-card">
            <i class="fa fa-book"></i>
            <div class="title">Audit Trail</div>
            <div>System logs</div>
        </a>
          <a href="management.php" class="dashboard-card">
            <i class="fa fa-book"></i>
            <div class="title">Management</div>
            <div>Managment</div>
        </a>
      
     

    </div>
</div>
</body>
</html>
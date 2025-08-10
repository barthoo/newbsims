<?php


?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="admin-panel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>Student Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            box-sizing: border-box;
        }
        body {
            min-height: 100vh;
            width: 100vw;
        }
        .header {
            width: 100%;
            background: #0e7490;
            box-shadow: 0 2px 8px #e0e0e0;
            padding: 18px 30px 18px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 20;
        }
    ;
        
        .logout {
            margin-left: auto;
        }
        .main-flex-container {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            width: 100vw;
            margin: 0;
        }
       
        .main-container {
            max-width: 700px;
            margin: 0 auto; /* Center the container horizontally */
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 12px #ddd;
            padding: 40px 0;
            width: 95vw;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .icon-menu {
            display: flex;
            gap: 40px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .icon-box {
            background: #f1f1f1;
            border-radius: 12px;
            padding: 35px 45px;
            text-align: center;
            transition: box-shadow 0.2s, background 0.2s;
            min-width: 150px;
            text-decoration: none !important;
            color: #333;
            flex: 1 1 180px;
            max-width: 220px;
        }
        .icon-box:hover {
            background: #e9ecef;
            box-shadow: 0 2px 8px #bbb;
            color: #007bff;
        }
        .icon-box i {
            font-size: 2.8em;
            margin-bottom: 12px;
            display: block;
        }
        .icon-box span {
            display: block;
            font-size: 1.2em;
            font-weight: 600;
            margin-top: 8px;
        }
        @media (max-width: 1100px) {
            .main-container {
                padding: 20px 5vw;
                margin: 20px auto 0 auto;
                max-width: 98vw;
            }
            .icon-menu {
                gap: 20px;
            }
            .main-flex-container {
                flex-direction: column;
                align-items: stretch;
            }
            
            
        }
        @media (max-width: 900px) {
            .main-container { padding: 15px 2vw; margin: 0 auto; }
            .icon-menu { gap: 18px; }
        }
        @media (max-width: 700px) {
            .main-container {
                padding: 10px 0;
                margin: 10px auto 0 auto;
            }
            .icon-menu {
                flex-direction: column;
                gap: 18px;
                align-items: center;
            }
            .icon-box {
                padding: 25px 10vw;
                min-width: 120px;
                max-width: 90vw;
                width: 90vw;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-align: center;
            }
            .icon-box i {
                font-size: 2em;
                margin-bottom: 10px;
                margin-left: 0;
                margin-right: 0;
                display: block;
                text-align: center;
                width: 100%;
            }
            .icon-box span {
                margin-top: 8px;
                width: 100%;
                text-align: center;
                display: block;
            }
        }
        @media (max-width: 600px) {
            .main-flex-container {
                flex-direction: column;
                align-items: center;
                width: 100vw;
                margin-top: 10px;
            }
            .main-container {
                padding: 4px 1vw;
                margin: 10px auto 0 auto;
                border-radius: 0;
            }
            .icon-box {
                padding: 10px 1vw;
                font-size: 0.9em;
            }
            .icon-box i {
                font-size: 2em;
            }
           
            
        }
        .main-container h2 {
           position: inline-block;
        }
    </style>
</head>
<body>
<header class="header">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle admin panel">
        <i class="fa fa-bars"></i>
    </button>
        <span class="dashboard-title" style="margin: 0 auto; display: flex; align-items: center; justify-content: center; flex: 1; text-align: center;">
        <i class="fa fa-user-plus"></i>
        <span style="margin-left: 8px;">Student Management</span>
    </span>
    <div class="logout">
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</header>
<div class="main-flex-container">
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
    <div class="main-container">
        <div class="icon-menu">
            <a href="add_student.php" class="icon-box">
                <i class="fa fa-user-plus text-primary"></i>
                <span>Add Student</span>
            </a>
            <a href="view_students.php" class="icon-box">
                <i class="fa fa-list text-success"></i>
                <span>View Students</span>
            </a>
            <a href="student_assessment.php" class="icon-box">
                <i class="fa fa-chart-bar text-warning"></i>
                <span>Student Assessment</span>
            </a>
            <a href="student_attendance.php" class="icon-box">
                <i class="fa fa-calendar-check text-danger"></i>
                <span>Student Attendance</span>
            </a>
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
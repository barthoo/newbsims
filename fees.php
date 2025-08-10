<!DOCTYPE html>
<html>
<head>
    <title>Student Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; margin: 0; padding: 0; }
        .dashboard-flex {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            width: 100%;
            min-height: 100vh;
        }
        .admin-sidebar {
            position: relative;
            top: 0;
            left: 0;
            height: 100vh;
            min-width: 200px;
            max-width: 240px;
            background: #0e7490;
            color: #fff;
            border-radius: 0 10px 10px 0;
            box-shadow: 2px 0 8px #e0e0e0;
            z-index: 10;
            padding-top: 30px;
            padding-bottom: 30px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            transition: transform 0.3s;
        }
        .admin-sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            width: 100%;
        }
        .admin-sidebar li {
            width: 100%;
        }
        .admin-sidebar a {
            display: flex;
            align-items: center;
            color: #fff;
            padding: 12px 24px;
            text-decoration: none;
            transition: background 0.2s;
            width: 100%;
            font-size: 1em;
        }
        .admin-sidebar a i {
            margin-right: 10px;
        }
        .admin-sidebar a:hover, .admin-sidebar .active > a {
            background: #155e75;
            color: #fff;
        }
        .sidebar-header {
            font-size: 1.2em;
            font-weight: bold;
            padding: 0 24px 18px 24px;
            margin-bottom: 10px;
            width: 100%;
            border-bottom: 1px solid #38bdf8;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0 auto;
            padding: 0 20px;
        }
        .main-container { 
            max-width: 700px; 
            margin: 60px auto; 
            background: #fff; 
            border-radius: 10px; 
            box-shadow: 0 2px 12px #ddd; 
            padding: 40px 0; 
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
            text-decoration: none; 
            color: #333; 
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
        header.header {
            width: 100%;
            background: #0e7490;
            box-shadow: 0 2px 8px #e0e0e0;
            padding: 18px 30px 18px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 20;
        }
        .dashboard-title {
            font-size: 1.4em;
            font-weight: bold;
            color: #fff;
            margin-left: 10px;
        }
        .logout {
            margin-left: auto;
        }
        @media (max-width: 900px) {
            .dashboard-flex { flex-direction: column; }
            .main-container { margin: 20px auto; }
            .main-content { margin-left: 0; }
            .admin-sidebar {
                position: fixed;
                left: 0;
                top: 0;
                height: 100vh;
                transform: translateX(-100%);
                width: 220px;
                max-width: 90vw;
                box-shadow: 2px 0 8px #e0e0e0;
                background: #0e7490;
                z-index: 100;
            }
            .admin-sidebar.show {
                transform: translateX(0);
            }
        }
        @media (max-width: 600px) {
            .admin-sidebar {
                width: 90vw;
                min-width: unset;
                max-width: unset;
                padding-top: 10px;
                padding-bottom: 10px;
            }
            .sidebar-header {
                font-size: 1em;
                padding: 0 12px 10px 12px;
            }
            .admin-sidebar a {
                padding: 10px 12px;
                font-size: 0.98em;
            }
            .main-container { padding: 10px 0; }
            .icon-menu { flex-direction: column; gap: 18px; }
            .icon-box { padding: 25px; }
        }
    </style>
</head>
<body>
<header class="header">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle admin panel">
        <i class="fa fa-bars"></i>
    </button>
        <span class="dashboard-title" style="margin: 0 auto; display: flex; align-items: center; justify-content: center; flex: 1; text-align: center;">
        <i class="fa fa-money"></i>
        <span style="margin-left: 8px;">Finance</span>
    </span>
    <div class="logout">
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</header>
<div class="dashboard-flex">
    <nav class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <i class="fa fa-user-shield"></i> Admin Panel
        </div>
        <ul>
            <li class="active"><a href="adminhome.php"><i class="fa fa-home"></i> Dashboard</a></li>
            <li><a href="management.php"><i class="fa fa-user-cog"></i> Management</a></li>
        </ul>
    </nav>
    <div class="main-content">
        <div class="main-container">
            <div class="icon-menu">
                <a href="tuition_fees.php" class="icon-box">
                    <i class="fa fa-money-bill-wave text-primary"></i>
                    <span>Tuition fees</span>
                </a>
                <a href="pta_levy.php" class="icon-box">
                    <i class="fa fa-users text-success"></i>
                    <span>PTA Levy</span>
                </a>
                <a href="examination_fees.php" class="icon-box">
                    <i class="fa fa-file-alt text-warning"></i>
                    <span>Examination Fees</span>
                </a>
                <a href="sports_levy.php" class="icon-box">
                    <i class="fa fa-futbol text-danger"></i>
                    <span>Sports Levy</span>
                </a>
                <a href="classes_fees.php" class="icon-box">
                    <i class="fa fa-chalkboard-teacher text-info"></i>
                    <span>Classes Fees</span>
                </a>
            </div>
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
            if (window.innerWidth <= 900 &&
                !adminSidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                adminSidebar.classList.remove('show');
            }
        });
        window.addEventListener('resize', function() {
            if (window.innerWidth > 900) {
                adminSidebar.classList.remove('show');
            }
        });
    }
});
</script>
</body>
</html>
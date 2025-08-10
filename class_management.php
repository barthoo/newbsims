<?php


session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
} elseif ($_SESSION['username'] == 'student') {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Class name options
$class_name_options = [
    'Nursery', 'KG 1', 'KG 2', 'BS 1', 'BS 2', 'BS 3', 'BS 4', 'BS 5', 'BS 6', 'BS 7', 'BS 8', 'BS 9'
];

// Fetch staff for teacher dropdown
$staffs = mysqli_query($conn, "SELECT staffid, name FROM stafftb ORDER BY name ASC");

// Handle search
$search = '';
$where = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where = "WHERE classname LIKE '%$search%' OR teacher LIKE '%$search%'";
}

// Handle add class
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_class'])) {
    $classname = mysqli_real_escape_string($conn, $_POST['classname']);
    $teacher = mysqli_real_escape_string($conn, $_POST['teacher']);
    // Prevent duplicate class name
    $dup_check = mysqli_query($conn, "SELECT id FROM classes WHERE classname='$classname' LIMIT 1");
    if (mysqli_num_rows($dup_check) > 0) {
        $alert = '<div class="alert alert-warning"><span class="glyphicon glyphicon-warning-sign"></span> Class with this name already exists!</div>';
    } else {
        $result = mysqli_query($conn, "INSERT INTO classes (classname, teacher) VALUES ('$classname', '$teacher')");
        if ($result) {
            $alert = '<div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> Class added successfully!</div>';
        } else {
            $alert = '<div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span> Failed to add class.</div>';
        }
    }
}

// Handle edit class
$edit_class = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM classes WHERE id=$edit_id");
    if ($result && mysqli_num_rows($result) > 0) {
        $edit_class = mysqli_fetch_assoc($result);
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_class'])) {
    $id = (int)$_POST['class_id'];
    $classname = mysqli_real_escape_string($conn, $_POST['classname']);
    $teacher = mysqli_real_escape_string($conn, $_POST['teacher']);
    // Prevent duplicate class name for other records
    $dup_check = mysqli_query($conn, "SELECT id FROM classes WHERE classname='$classname' AND id!=$id LIMIT 1");
    if (mysqli_num_rows($dup_check) > 0) {
        $alert = '<div class="alert alert-warning"><span class="glyphicon glyphicon-warning-sign"></span> Another class with this name already exists!</div>';
    } else {
        $result = mysqli_query($conn, "UPDATE classes SET classname='$classname', teacher='$teacher' WHERE id=$id");
        if ($result) {
            $alert = '<div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> Class updated successfully!</div>';
        } else {
            $alert = '<div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span> Failed to update class.</div>';
        }
        $edit_class = null;
    }
}

// Handle delete class
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM classes WHERE id=$id");
    header("Location: class_management.php");
    exit();
}

// Fetch classes (with search)
$classes = mysqli_query($conn, "SELECT * FROM classes $where ORDER BY classname ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Class Management - Administrator Dashboard</title>
    <link rel="stylesheet" type="text/css" href="admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; margin: 0; }
        .main-flex-container {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            width: 100vw;
            min-height: 100vh;
            background: #f7f7fa;
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
        .main-container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px #ddd;
            padding: 30px;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .panel.panel-primary,
        .panel.panel-success {
            width: 100%;
            max-width: 100%;
        }
        .search-bar {
            margin-bottom: 20px;
            width: 100%;
            max-width: 100%;
            float: none;
        }
        .table-responsive,
        table.table {
            width: 100%;
            max-width: 100%;
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
        .table th, .table td { vertical-align: middle !important; }
        .table th { background: #f1f1f1; }
        .icon-btn { border: none; background: none; color: #007bff; font-size: 1.2em; }
        .icon-btn:hover { color: #0056b3; }
        .panel-title { font-size: 1.2em; font-weight: bold; }
        .fa-chalkboard-teacher { color: #007bff; }
        .fa-trash { color: #dc3545; }
        .fa-edit { color: #28a745; }
        @media (max-width: 900px) {
            .main-flex-container { flex-direction: column; }
            .main-container { max-width: 99vw; margin: 20px auto 0 auto; }
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
            .main-container { padding: 10px; }
            .search-bar { width: 100%; margin-bottom: 15px; }
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
        }
    </style>
</head>
<body>
    <link rel="stylesheet" href="admin-panel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<header class="header">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle admin panel">
        <i class="fa fa-bars"></i>
    </button>
       <span class="dashboard-title" style="margin: 0 auto; display: flex; align-items: center; justify-content: center; flex: 1; text-align: center;">
        <i class="fa fa-edit"></i>
        <span style="margin-left: 8px;">Class Management</span>
    </span>/span>
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
        </ul>
    </nav>
    <div class="main-container">
        <?php if ($alert) echo $alert; ?>

        <?php if ($edit_class): ?>
        <div class="panel panel-success">
            <div class="panel-heading">
                <span class="panel-title"><i class="fa fa-edit"></i> Edit Class</span>
            </div>
            <div class="panel-body">
                <form method="post" action="class_management.php">
                    <input type="hidden" name="class_id" value="<?php echo $edit_class['id']; ?>">
                    <div class="row" style="margin-bottom:10px;">
                        <div class="col-md-6 col-xs-12">
                            <label for="classname"><i class="fa fa-chalkboard"></i> Class Name</label>
                            <select class="form-control" name="classname" id="classname" required>
                                <option value="">Select Class</option>
                                <?php foreach ($class_name_options as $option): ?>
                                    <option value="<?php echo $option; ?>" <?php if($edit_class['classname'] == $option) echo 'selected'; ?>><?php echo $option; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 col-xs-12">
                            <label for="teacher"><i class="fa fa-user-tie"></i> Teacher</label>
                            <select class="form-control" name="teacher" id="teacher" required>
                                <option value="">Select Teacher</option>
                                <?php
                                mysqli_data_seek($staffs, 0);
                                while ($staff = mysqli_fetch_assoc($staffs)) {
                                    $selected = ($edit_class['teacher'] == $staff['name']) ? 'selected' : '';
                                    echo "<option value=\"" . htmlspecialchars($staff['name']) . "\" $selected>" . htmlspecialchars($staff['name']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="update_class" class="btn btn-success"><i class="fa fa-save"></i> Update</button>
                    <a href="class_management.php" class="btn btn-default"><i class="fa fa-times"></i> Cancel</a>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div class="panel panel-primary">
            <div class="panel-heading">
                <span class="panel-title"><i class="fa fa-plus-circle"></i> Add New Class</span>
            </div>
            <div class="panel-body">
                <form method="post" action="class_management.php">
                    <div class="row" style="margin-bottom:10px;">
                        <div class="col-md-6 col-xs-12">
                            <label for="classname"><i class="fa fa-chalkboard"></i> Class Name</label>
                            <select class="form-control" name="classname" id="classname" required>
                                <option value="">Select Class</option>
                                <?php foreach ($class_name_options as $option): ?>
                                    <option value="<?php echo $option; ?>"><?php echo $option; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 col-xs-12">
                            <label for="teacher"><i class="fa fa-user-tie"></i> Teacher</label>
                            <select class="form-control" name="teacher" id="teacher" required>
                                <option value="">Select Teacher</option>
                                <?php
                                mysqli_data_seek($staffs, 0);
                                while ($staff = mysqli_fetch_assoc($staffs)) {
                                    echo "<option value=\"" . htmlspecialchars($staff['name']) . "\">" . htmlspecialchars($staff['name']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="add_class" class="btn btn-success"><i class="fa fa-plus"></i> Add</button>
                </form>
            </div>
        </div>

        <!-- Search Bar -->
        <form class="search-bar" method="get" action="class_management.php">
            <div class="input-group" style="width:100%;">
                <input type="text" name="search" class="form-control" placeholder="Search class..." value="<?php echo htmlspecialchars($search); ?>">
                <span class="input-group-btn">
                    <button class="btn btn-primary" type="submit"><i class="glyphicon glyphicon-search"></i> Search</button>
                </span>
            </div>
        </form>
        <div style="clear:both;"></div>

        <h4><i class="fa fa-list"></i> All Classes</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th><i class="fa fa-hashtag"></i> #</th>
                        <th><i class="fa fa-chalkboard"></i> Class Name</th>
                        <th><i class="fa fa-user-tie"></i> Teacher</th>
                        <th><i class="fa fa-cogs"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    while ($row = mysqli_fetch_assoc($classes)) {
                        echo "<tr>";
                        echo "<td>{$i}</td>";
                        echo "<td><i class='fa fa-chalkboard-teacher'></i> " . htmlspecialchars($row['classname']) . "</td>";
                        echo "<td><i class='fa fa-user'></i> " . htmlspecialchars($row['teacher']) . "</td>";
                        echo "<td class='text-center'>";
                        echo "<a href='class_management.php?edit=" . $row['id'] . "' class='icon-btn' title='Edit'><i class='fa fa-edit'></i></a> ";
                        echo "<a href='class_management.php?delete=" . $row['id'] . "' class='icon-btn' title='Delete' onclick=\"return confirm('Delete this class?');\"><i class='fa fa-trash'></i></a>";
                        echo "</td>";
                        echo "</tr>";
                        $i++;
                    }
                    if ($i == 1) {
                        echo "<tr><td colspan='4' class='text-center'>No classes found.</td></tr>";
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
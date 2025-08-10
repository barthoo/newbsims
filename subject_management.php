<?php

// Ensure intval exists (for environments where it might be missing)
if (!function_exists('intval')) {
    function intval($value, $base = 10) {
        return (int)$value;
    }
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Curriculum options
$curriculum_options = [
    'Pre School',
    'Lower Primary',
    'Upper Primary',
    'Junior High School'
];

// Add subject (prevent duplicates)
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    $subject_name = mysqli_real_escape_string($conn, $_POST['subject_name']);
    $subject_code = mysqli_real_escape_string($conn, $_POST['subject_code']);
    $curriculum = mysqli_real_escape_string($conn, $_POST['curriculum']);
    // Check for duplicates
    $check = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_name='$subject_name' OR subject_code='$subject_code'");
    if (mysqli_num_rows($check) > 0) {
        $alert = '<div class="alert alert-danger" id="alertBox">Subject name or code already exists!</div>';
    } else {
        $result = mysqli_query($conn, "INSERT INTO subjects (subject_name, subject_code, curriculum) VALUES ('$subject_name', '$subject_code', '$curriculum')");
        $alert = $result ? '<div class="alert alert-success" id="alertBox">Subject added!</div>' : '<div class="alert alert-danger" id="alertBox">Failed to add subject.</div>';
    }
}

// Edit subject
$edit_subject = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $result = mysqli_query($conn, "SELECT * FROM subjects WHERE id=$edit_id");
    if ($result && mysqli_num_rows($result) > 0) $edit_subject = mysqli_fetch_assoc($result);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_subject'])) {
    $id = intval($_POST['subject_id']);
    $subject_name = mysqli_real_escape_string($conn, $_POST['subject_name']);
    $subject_code = mysqli_real_escape_string($conn, $_POST['subject_code']);
    $curriculum = mysqli_real_escape_string($conn, $_POST['curriculum']);
    // Check for duplicates (excluding current record)
    $check = mysqli_query($conn, "SELECT * FROM subjects WHERE (subject_name='$subject_name' OR subject_code='$subject_code') AND id!=$id");
    if (mysqli_num_rows($check) > 0) {
        $alert = '<div class="alert alert-danger" id="alertBox">Subject name or code already exists!</div>';
    } else {
        $result = mysqli_query($conn, "UPDATE subjects SET subject_name='$subject_name', subject_code='$subject_code', curriculum='$curriculum' WHERE id=$id");
        $alert = $result ? '<div class="alert alert-success" id="alertBox">Subject updated!</div>' : '<div class="alert alert-danger" id="alertBox">Failed to update subject.</div>';
        $edit_subject = null;
    }
}

// Delete subject
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM subjects WHERE id=$id");
    header("Location: subject_management.php");
    exit();
}

// Handle search
$search = '';
$where = '';
if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where = "WHERE subject_name LIKE '%$search%' OR subject_code LIKE '%$search%' OR curriculum LIKE '%$search%'";
}

// Fetch subjects
$subjects = mysqli_query($conn, "SELECT * FROM subjects $where ORDER BY curriculum, subject_name ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="admin-panel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <title>Subject Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
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
            max-width: 700px;
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
        .header h2 { margin: 0; font-size: 2em; }
        .table-responsive { margin-top: 0; margin-bottom: 20px; }
        .table { margin-bottom: 0; }
        .table th, .table td { vertical-align: middle !important; }
        .table th { background: #f1f1f1; }
        .btn-xs { margin-right: 3px; }
        .form-inline input, .form-inline select { margin-right: 10px; }
        .panel-title { font-size: 1.2em; font-weight: bold; }
        .fa-book { color: #007bff; }
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
            .header { padding: 10px; font-size: 1em; }
            .form-inline input, .form-inline select, .form-inline button { width: 100%; margin-bottom: 10px; }
            .table-responsive { overflow-x: auto; }
            .table th, .table td { font-size: 11px; }
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
        .search-bar { margin-bottom: 20px; }
    </style>
</head>
<body>
<header class="header">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle admin panel">
        <i class="fa fa-bars"></i>
    </button>
        <span class="dashboard-title" style="margin: 0 auto; display: flex; align-items: center; justify-content: center; flex: 1; text-align: center;">
        <i class="fa fa-list"></i>
        <span style="margin-left: 8px;">Subject Management</span>
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
        </ul>
    </nav>
    <div class="main-container">
        <?php if ($alert) echo $alert; ?>

        <script>
            // Hide alert after 10 seconds
            setTimeout(function() {
                var alertBox = document.getElementById('alertBox');
                if (alertBox) alertBox.style.display = 'none';
            }, 10000);
        </script>

        <!-- Responsive Table at the Top -->
        <h4 style="margin-top:0;"><i class="fa fa-list"></i> Subjects List</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Subject Name</th>
                        <th>Subject Code</th>
                        <th>Curriculum</th>
                        <th style="width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $i = 1;
                if ($subjects) {
                    while ($row = mysqli_fetch_assoc($subjects)) {
                        echo "<tr>";
                        echo "<td>{$i}</td>";
                        echo "<td>" . htmlspecialchars($row['subject_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['subject_code']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['curriculum']) . "</td>";
                        echo "<td>
                            <a href='subject_management.php?edit=" . $row['id'] . "' class='btn btn-xs btn-success' title='Edit'><i class='fa fa-edit'></i></a>
                            <a href='subject_management.php?delete=" . $row['id'] . "' class='btn btn-xs btn-danger' title='Delete' onclick=\"return confirm('Delete this subject?');\"><i class='fa fa-trash'></i></a>
                        </td>";
                        echo "</tr>";
                        $i++;
                    }
                }
                if ($i == 1) {
                    echo "<tr><td colspan='5' class='text-center'>No subjects found.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

        <!-- Search Bar -->
        <form method="get" action="subject_management.php" class="form-inline search-bar text-right">
            <div class="form-group">
                <input type="text" name="search" class="form-control" placeholder="Search subject, code or curriculum..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Search</button>
            <?php if ($search): ?>
                <a href="subject_management.php" class="btn btn-default">Reset</a>
            <?php endif; ?>
        </form>

        <?php if ($edit_subject): ?>
        <div class="panel panel-success">
            <div class="panel-heading">
                <span class="panel-title"><i class="fa fa-edit"></i> Edit Subject</span>
            </div>
            <div class="panel-body">
                <form method="post" action="subject_management.php" class="form-inline" style="margin-bottom:20px;">
                    <input type="hidden" name="subject_id" value="<?php echo $edit_subject['id']; ?>">
                    <input type="text" name="subject_name" class="form-control" value="<?php echo htmlspecialchars($edit_subject['subject_name']); ?>" required placeholder="Subject Name">
                    <input type="text" name="subject_code" class="form-control" value="<?php echo htmlspecialchars($edit_subject['subject_code']); ?>" required placeholder="Subject Code">
                    <select name="curriculum" class="form-control" required>
                        <option value="">Select Curriculum</option>
                        <?php foreach ($curriculum_options as $curr): ?>
                            <option value="<?php echo $curr; ?>" <?php if ($edit_subject['curriculum'] == $curr) echo 'selected'; ?>><?php echo $curr; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="update_subject" class="btn btn-success btn-sm"><i class="fa fa-save"></i> Update</button>
                    <a href="subject_management.php" class="btn btn-default btn-sm"><i class="fa fa-times"></i> Cancel</a>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div class="panel panel-primary">
            <div class="panel-heading">
                <span class="panel-title"><i class="fa fa-plus-circle"></i> Add New Subject</span>
            </div>
            <div class="panel-body">
                <form method="post" action="subject_management.php" class="form-inline" style="margin-bottom:20px; display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end;">
                    <input type="text" name="subject_name" class="form-control input-sm" required placeholder="Subject Name" style="margin-right:10px; min-width:150px;">
                    <input type="text" name="subject_code" class="form-control input-sm" required placeholder="Subject Code" style="margin-right:10px; min-width:100px;">
                    <select name="curriculum" class="form-control input-sm" required style="margin-right:10px; min-width:150px;">
                        <option value="">Select Curriculum</option>
                        <?php foreach ($curriculum_options as $curr): ?>
                            <option value="<?php echo $curr; ?>"><?php echo $curr; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="add_subject" class="btn btn-primary btn-sm" style="margin-left:auto; margin-top:10px;"><i class="fa fa-plus"></i> Add Subject</button>
                </form>
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
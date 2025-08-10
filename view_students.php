<?php


$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Fetch filter options
$class_options = [
    'Nursery', 'KG 1', 'KG 2', 'BS 1', 'BS 2', 'BS 3', 'BS 4', 'BS 5', 'BS 6', 'JHS 1', 'JHS 2', 'JHS 3'
];
$curriculum_options = [];
$curriculum_query = mysqli_query($conn, "SELECT DISTINCT curriculum FROM subjects ORDER BY curriculum ASC");
while ($row = mysqli_fetch_assoc($curriculum_query)) {
    $curriculum_options[] = $row['curriculum'];
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM students WHERE id=$id");
    header("Location: view_students.php");
    exit;
}

// Export to Excel
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=students_export.xls");
    echo "Name\tAdmission No.\tGender\tClass\tCurriculum\tParent/Guardian\tPhone\tEmail\tAddress\tCity/Town\n";
    $where = [];
    if (!empty($_GET['search'])) {
        $search = mysqli_real_escape_string($conn, $_GET['search']);
        $where[] = "(first_name LIKE '%$search%' OR other_names LIKE '%$search%' OR admission_no LIKE '%$search%')";
    }
    if (!empty($_GET['class'])) {
        $class = mysqli_real_escape_string($conn, $_GET['class']);
        $where[] = "class='$class'";
    }
    if (!empty($_GET['curriculum'])) {
        $curriculum = mysqli_real_escape_string($conn, $_GET['curriculum']);
        $where[] = "curriculum='$curriculum'";
    }
    $where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";
    $students = mysqli_query($conn, "SELECT * FROM students $where_sql ORDER BY id DESC");
    while ($row = mysqli_fetch_assoc($students)) {
        $name = $row['first_name'] . ' ' . $row['other_names'];
        echo "{$name}\t{$row['admission_no']}\t{$row['gender']}\t{$row['class']}\t{$row['curriculum']}\t{$row['parent_guardian']}\t{$row['phone']}\t{$row['email']}\t{$row['address']}\t{$row['city']}\n";
    }
    exit;
}

// Handle search and filter
$where = [];
if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where[] = "(first_name LIKE '%$search%' OR other_names LIKE '%$search%' OR admission_no LIKE '%$search%')";
}
if (!empty($_GET['class'])) {
    $class = mysqli_real_escape_string($conn, $_GET['class']);
    $where[] = "class='$class'";
}
if (!empty($_GET['curriculum'])) {
    $curriculum = mysqli_real_escape_string($conn, $_GET['curriculum']);
    $where[] = "curriculum='$curriculum'";
}
$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

$students = mysqli_query($conn, "SELECT * FROM students $where_sql ORDER BY id DESC");

// Fetch student for editing if edit is set
$edit_student = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_query = mysqli_query($conn, "SELECT * FROM students WHERE id=$edit_id LIMIT 1");
    $edit_student = mysqli_fetch_assoc($edit_query);
}

// Handle update
$update_alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_student'])) {
    $id = intval($_POST['id']);
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $other_names = mysqli_real_escape_string($conn, $_POST['other_names']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $class = mysqli_real_escape_string($conn, $_POST['class']);
    $curriculum = mysqli_real_escape_string($conn, $_POST['curriculum']);
    $parent_guardian = mysqli_real_escape_string($conn, $_POST['parent_guardian']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);

    // Handle profile picture update
    $profile_pic_sql = "";
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('student_', true) . '.' . $ext;
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $target = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target)) {
            $profile_pic_sql = ", profile_pic='" . mysqli_real_escape_string($conn, $target) . "'";
        }
    }

    $update = mysqli_query($conn, "UPDATE students SET 
        first_name='$first_name',
        other_names='$other_names',
        gender='$gender',
        class='$class',
        curriculum='$curriculum',
        parent_guardian='$parent_guardian',
        phone='$phone',
        email='$email',
        address='$address',
        city='$city'
        $profile_pic_sql
        WHERE id=$id
    ");
    if ($update) {
        $update_alert = '<div class="alert alert-success" style="max-width:350px;margin:10px auto;">Student updated successfully.</div>';
        // Refresh to remove edit mode
        echo "<script>window.location='view_students.php';</script>";
        exit;
    } else {
        $update_alert = '<div class="alert alert-danger" style="max-width:350px;margin:10px auto;">Failed to update student.</div>';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="admin-panel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>View Students</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .main-layout {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            width: 100%;
            min-height: 100vh;
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
        .main-flex-container {
            display: inline-block;
            width: 100%;
            min-height: 100vh;
            margin-top: 40px;
            position: relative;
            left: 0;
            transform: none;
            margin-left: 40px;
        }
        .main-container {
            max-width: 1200px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px #ddd;
            padding: 30px;
            width: 95vw;
            display: inline-block;
            vertical-align: top;
            margin-left: 0;
        }
        .students-table th, .students-table td { vertical-align: middle !important; }
        .students-table img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .filter-bar { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; justify-content: center; }
        .filter-bar input[type="text"] { min-width: 80px; max-width: 160px; width: 120px; }
        .filter-bar select { min-width: 80px; max-width: 120px; width: 100px; }
        .action-btns a { margin-right: 8px; }
        .export-btn { margin-left: auto; }
        .edit-form-row { background: #f7f7f7; }
        .edit-form-row td { padding: 10px 8px !important; }
        .edit-form-row input, .edit-form-row select { width: 100%; }
        .student-list-title {
            margin-bottom: 18px;
            font-weight: bold;
            letter-spacing: 1px;
            text-align: center;
            font-size: 1.7em;
        }
        @media (max-width: 1200px) {
            .main-container { padding: 10px; }
            .filter-bar { flex-direction: column; gap: 8px; }
            .export-btn { margin-left: 0; margin-top: 10px; }
        }
        @media (max-width: 900px) {
            .main-layout {
                flex-direction: column;
                align-items: stretch;
            }
            .admin-sidebar {
                min-width: 100vw;
                width: 100vw;
                height: auto;
                position: static;
            }
            .main-flex-container {
                margin-top: 10px;
                left: 50%;
                transform: translateX(-50%);
                width: 100vw;
                margin-left: 0;
            }
            .main-container { padding: 15px 2vw; }
        }
        @media (max-width: 700px) {
            .main-layout {
                flex-direction: column;
                align-items: stretch;
            }
            .main-flex-container {
                margin-top: 0;
                left: 50%;
                transform: translateX(-50%);
                width: 100vw;
                margin-left: 0;
            }
            .main-container {
                padding: 10px 0;
                margin: 10px 0 0 0;
            }
            .filter-bar {
                flex-direction: column;
                gap: 8px;
            }
        }
        @media (max-width: 600px) {
            .main-layout {
                flex-direction: column;
                align-items: stretch;
            }
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
            .main-flex-container {
                margin-top: 0;
                left: 50%;
                transform: translateX(-50%);
                width: 100vw;
                margin-left: 0;
            }
            .main-container {
                padding: 4px 1vw;
                margin: 5px 0 0 0;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
<header class="header">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle admin panel">
        <i class="fa fa-bars"></i>
    </button>
    <span class="dashboard-title"><i class="fa fa-gauge"></i> Administrator Dashboard</span>
    <div class="logout">
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</header>
<div class="main-layout">
    <nav class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <i class="fa fa-user-shield"></i> Control Panel
        </div>
        <ul>
            <li class="active"><a href="adminhome.php"><i class="fa fa-home"></i> Dashboard</a></li>
            <li><a href="add_student.php"><i class="fa fa-user-plus"></i> Add Student</a></li>
  
                </ul>
    </nav>
    <div class="main-flex-container">
        <div class="main-container">
            <div class="student-list-title">
                <i class="fa fa-list"></i> Student List
            </div>
            <form method="get" class="filter-bar">
                <input type="text" name="search" class="form-control input-sm" placeholder="Search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <select name="class" class="form-control input-sm">
                    <option value="">Class</option>
                    <?php foreach ($class_options as $class): ?>
                        <option value="<?php echo $class; ?>" <?php if(isset($_GET['class']) && $_GET['class']==$class) echo 'selected'; ?>><?php echo $class; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="curriculum" class="form-control input-sm">
                    <option value="">Curriculum</option>
                    <?php foreach ($curriculum_options as $curr): ?>
                        <option value="<?php echo $curr; ?>" <?php if(isset($_GET['curriculum']) && $_GET['curriculum']==$curr) echo 'selected'; ?>><?php echo $curr; ?></option>
                    <?php endforeach; ?>
                </select>
                <!-- Removed the search button -->
                <a href="view_students.php" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i></a>
                <a href="?<?php
                    $params = $_GET;
                    $params['export'] = 'excel';
                    echo http_build_query($params);
                ?>" class="btn btn-success btn-sm export-btn"><i class="fa fa-file-excel"></i> Export</a>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered table-hover students-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Admission No.</th>
                            <th>Gender</th>
                            <th>Class</th>
                            <th>Curriculum</th>
                            <th>Parent/Guardian</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>City/Town</th>
                            <th>Profile</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $i = 1;
                    if ($students && mysqli_num_rows($students) > 0) {
                        while ($row = mysqli_fetch_assoc($students)) {
                            // Edit row
                            if ($edit_student && $edit_student['id'] == $row['id']) {
                                ?>
                                <tr class="edit-form-row">
                                    <form method="post" action="view_students.php" enctype="multipart/form-data">
                                        <td><?php echo $i; ?></td>
                                        <td>
                                            <input type="text" name="first_name" value="<?php echo htmlspecialchars($edit_student['first_name']); ?>" required>
                                            <input type="text" name="other_names" value="<?php echo htmlspecialchars($edit_student['other_names']); ?>" required>
                                        </td>
                                        <td><?php echo htmlspecialchars($edit_student['admission_no']); ?></td>
                                        <td>
                                            <select name="gender" required>
                                                <option value="Male" <?php if($edit_student['gender']=='Male') echo 'selected'; ?>>Male</option>
                                                <option value="Female" <?php if($edit_student['gender']=='Female') echo 'selected'; ?>>Female</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="class" required>
                                                <?php foreach ($class_options as $class): ?>
                                                    <option value="<?php echo $class; ?>" <?php if($edit_student['class']==$class) echo 'selected'; ?>><?php echo $class; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="curriculum" required>
                                                <?php foreach ($curriculum_options as $curr): ?>
                                                    <option value="<?php echo $curr; ?>" <?php if($edit_student['curriculum']==$curr) echo 'selected'; ?>><?php echo $curr; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td><input type="text" name="parent_guardian" value="<?php echo htmlspecialchars($edit_student['parent_guardian']); ?>" required></td>
                                        <td><input type="text" name="phone" value="<?php echo htmlspecialchars($edit_student['phone']); ?>" required></td>
                                        <td><input type="email" name="email" value="<?php echo htmlspecialchars($edit_student['email']); ?>"></td>
                                        <td><input type="text" name="address" value="<?php echo htmlspecialchars($edit_student['address']); ?>"></td>
                                        <td><input type="text" name="city" value="<?php echo htmlspecialchars($edit_student['city']); ?>"></td>
                                        <td>
                                            <?php if ($edit_student['profile_pic']): ?>
                                                <img src="<?php echo htmlspecialchars($edit_student['profile_pic']); ?>" alt="Profile"><br>
                                            <?php else: ?>
                                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode(trim($edit_student['first_name'].' '.$edit_student['other_names'])); ?>&background=007bff&color=fff&size=40" alt="Profile"><br>
                                            <?php endif; ?>
                                            <input type="file" name="profile_pic" accept="image/*">
                                        </td>
                                        <td>
                                            <input type="hidden" name="id" value="<?php echo $edit_student['id']; ?>">
                                            <button type="submit" name="update_student" class="btn btn-xs btn-success"><i class="fa fa-save"></i> Save</button>
                                            <a href="view_students.php" class="btn btn-xs btn-default"><i class="fa fa-times"></i> Cancel</a>
                                        </td>
                                    </form>
                                </tr>
                                <?php
                                $i++;
                                continue;
                            }
                            // Normal row
                            echo "<tr>";
                            echo "<td>{$i}</td>";
                            echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['other_names']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['admission_no']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['gender']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['class']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['curriculum']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['parent_guardian']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['city']) . "</td>";
                            // Profile before actions
                            echo "<td>";
                            if ($row['profile_pic']) {
                                echo "<img src='" . htmlspecialchars($row['profile_pic']) . "' alt='Profile'>";
                            } else {
                                $name = trim($row['first_name'] . ' ' . $row['other_names']);
                                echo "<img src='https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=007bff&color=fff&size=40' alt='Profile'>";
                            }
                            echo "</td>";
                            echo "<td class='action-btns'>
                                <a href='view_students.php?edit={$row['id']}' class='btn btn-xs btn-info'><i class='fa fa-edit'></i> Edit</a>
                                <a href='view_students.php?delete={$row['id']}' class='btn btn-xs btn-danger' onclick=\"return confirm('Delete this student?');\"><i class='fa fa-trash'></i> Delete</a>
                            </td>";
                            echo "</tr>";
                            $i++;
                        }
                    } else {
                        echo "<tr><td colspan='13' class='text-center'>No students found.</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
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
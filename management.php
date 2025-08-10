<?php

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// --- BILLING SECTION ---
$bill_msg = '';
if (isset($_POST['bill_students'])) {
    $term = mysqli_real_escape_string($conn, $_POST['term']);
    $amount = (float)$_POST['amount'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $class = mysqli_real_escape_string($conn, $_POST['class']);
    $students = mysqli_query($conn, "SELECT id FROM students WHERE class='$class'");
    $count = 0;
    while ($stu = mysqli_fetch_assoc($students)) {
        $sid = $stu['id'];
        mysqli_query($conn, "INSERT INTO student_fees (student_id, term, amount, description, status) VALUES ($sid, '$term', $amount, '$description', 'unpaid')");
        $count++;
    }
    $bill_msg = "$count students billed for $class ($term)!";
}

// --- USER MANAGEMENT SECTION ---
$user_msg = '';
if (isset($_POST['reset_password'])) {
    $user_id = (int)$_POST['user_id'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    if ($new_password !== $confirm_password) {
        $user_msg = "Passwords do not match!";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE id=$user_id");
        $user_msg = "Password updated successfully!";
    }
}

// Fetch all users
$users = [];
$result = mysqli_query($conn, "SELECT id, username, usertype FROM user ORDER BY username ASC");
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

// Fetch all classes for billing
$classes = [];
$class_result = mysqli_query($conn, "SELECT DISTINCT class FROM students ORDER BY class ASC");
while ($row = mysqli_fetch_assoc($class_result)) {
    $classes[] = $row['class'];
}

// Fetch all terms from academic_sessions table
$terms = [];
$term_result = mysqli_query($conn, "SELECT DISTINCT term FROM academic_sessions ORDER BY term ASC");
while ($row = mysqli_fetch_assoc($term_result)) {
    $terms[] = $row['term'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Management Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background: #f7f7fa; margin: 0; }
        .dashboard-flex {
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
        .container {
            max-width: 700px;
            margin: 30px auto 0 auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px #ddd;
            padding: 30px;
            position: relative;
            top: 0;
            z-index: 1;
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
        h2 { color: #0e7490; }
        .msg { color: #16a34a; margin-bottom: 10px; }
        .msg-danger { color: #dc2626; margin-bottom: 10px; }
        .section { margin-bottom: 40px; }
        th { background: #0e7490; color: #fff; }
        .pass-form { display: inline-block; margin: 0 5px; }
        .management-btns {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .management-btns button, .management-btns a {
            min-width: 150px;
        }
        #bill-section, #users-section { display: none; }
        @media (max-width: 900px) {
            .dashboard-flex { flex-direction: column; }
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
            .container { margin: 20px auto 0 auto; }
        }
        @media (max-width: 600px) {
            .container { padding: 3vw; max-width: 100vw; }
            .section { margin-bottom: 20px; }
            .management-btns button, .management-btns a { min-width: 100%; }
            table.table, thead, tbody, th, td, tr {
                display: block;
                width: 100%;
            }
            table.table { border: none; }
            thead { display: none; }
            tr { margin-bottom: 15px; border-bottom: 1px solid #eee; }
            td {
                padding: 8px 0;
                border: none;
                position: relative;
            }
            td:before {
                content: attr(data-label);
                font-weight: bold;
                display: block;
                color: #0e7490;
                margin-bottom: 2px;
            }
            .pass-form input[type="password"] {
                width: 100% !important;
                margin-bottom: 5px;
            }
        }
        /* Overlay for sidebar on small screens */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.3);
            z-index: 99;
        }
        .sidebar-overlay.active {
            display: block;
        }
    </style>
    <script>
        function showSection(section) {
            document.getElementById('bill-section').style.display = 'none';
            document.getElementById('users-section').style.display = 'none';
            document.getElementById(section).style.display = 'block';
        }
        window.onload = function() {
            // Optionally, show nothing or default to one section
        };
        function validatePasswordForm(form) {
            var pass = form.new_password.value;
            var conf = form.confirm_password.value;
            if (pass !== conf) {
                alert("Passwords do not match!");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
<header class="header">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle admin panel">
        <i class="fa fa-bars"></i>
    </button>
        <span class="dashboard-title" style="margin: 0 auto; display: flex; align-items: center; justify-content: center; flex: 1; text-align: center;">
        <i class="fa fa-user"></i>
        <span style="margin-left: 8px;">Management</span>
    </span>
    <div class="logout">
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</header>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="dashboard-flex">
    <nav class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <i class="fa fa-user-shield"></i>
            <span>Admin Panel</span>
        </div>
        <ul>
            <li class="active"><a href="adminhome.php"><i class="fa fa-home"></i> Dashboard</a></li>
            <li><a href="view_users.php"><i class="fa fa-users"></i> View Users</a></li>
            <li><a href="add_users.php"><i class="fa fa-user-plus"></i> Add Users</a></li>
        </ul>
    </nav>
    <div class="container">
        <h2>Management Dashboard</h2>
        <div class="management-btns">
            <button class="btn btn-primary btn-lg" onclick="showSection('bill-section')">Student Bill</button>
            <button class="btn btn-info btn-lg" onclick="showSection('users-section')">Manage Passwords</button>
            <a href="users.php" class="btn btn-success btn-lg">Add User</a>
            <a href="viewusers.php" class="btn btn-default btn-lg">View Users</a>
        </div>

        <!-- Student Billing Section -->
        <div id="bill-section" class="section">
            <h4>Bill Students for Term Fees</h4>
            <?php if ($bill_msg): ?>
                <div class="msg"><?php echo htmlspecialchars($bill_msg); ?></div>
            <?php endif; ?>
            <form method="post" class="form-inline">
                <label>Class:
                    <select name="class" class="form-control" required>
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo htmlspecialchars($class); ?>"><?php echo htmlspecialchars($class); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Term:
                    <select name="term" class="form-control" required>
                        <option value="">Select Term</option>
                        <?php foreach ($terms as $term): ?>
                            <option value="<?php echo htmlspecialchars($term); ?>"><?php echo htmlspecialchars($term); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Amount:
                    <input type="number" name="amount" class="form-control" min="0" step="0.01" required>
                </label>
                <label>Description:
                    <input type="text" name="description" class="form-control" placeholder="e.g. Tuition Fee" required>
                </label>
                <button type="submit" name="bill_students" class="btn btn-primary">Bill Students</button>
            </form>
        </div>

        <!-- User Management Section -->
        <div id="users-section" class="section">
            <h4>Manage Users Password</h4>
            <?php if ($user_msg): ?>
                <div class="<?php echo ($user_msg == "Password updated successfully!") ? 'msg' : 'msg-danger'; ?>"><?php echo htmlspecialchars($user_msg); ?></div>
            <?php endif; ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Reset Password</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td data-label="Username"><?php echo htmlspecialchars($user['username']); ?></td>
                        <td data-label="Role"><?php echo htmlspecialchars($user['usertype']); ?></td>
                        <td data-label="Reset Password">
                            <form method="post" class="pass-form" onsubmit="return validatePasswordForm(this);">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="password" name="new_password" placeholder="New Password" required class="form-control input-sm" style="width:120px;display:inline;">
                                <input type="password" name="confirm_password" placeholder="Confirm Password" required class="form-control input-sm" style="width:140px;display:inline;">
                                <button type="submit" name="reset_password" class="btn btn-xs btn-warning">Reset</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var sidebarToggle = document.getElementById('sidebarToggle');
    var adminSidebar = document.getElementById('adminSidebar');
    var sidebarOverlay = document.getElementById('sidebarOverlay');
    function showSidebar() {
        adminSidebar.classList.add('show');
        sidebarOverlay.classList.add('active');
    }
    function hideSidebar() {
        adminSidebar.classList.remove('show');
        sidebarOverlay.classList.remove('active');
    }
    if (sidebarToggle && adminSidebar && sidebarOverlay) {
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            showSidebar();
        });
        sidebarOverlay.addEventListener('click', function() {
            hideSidebar();
        });
        window.addEventListener('resize', function() {
            if (window.innerWidth > 900) {
                hideSidebar();
            }
        });
    }
});
</script>
</body>
</html>
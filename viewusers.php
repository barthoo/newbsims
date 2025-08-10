<?php


session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
} elseif ($_SESSION['username'] == 'student') {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$db = "bsmsdb";

$conn = mysqli_connect($host, $user, $password, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle delete
if (isset($_POST['delete_user']) && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $stmt = mysqli_prepare($conn, "DELETE FROM user WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "s", $delete_id);
    mysqli_stmt_execute($stmt);
    header("Location: viewusers.php");
    exit();
}

// Handle update (inline edit) - REMOVE password update
if (isset($_POST['save_user'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $usertype = $_POST['usertype'];

    $stmt = mysqli_prepare($conn, "UPDATE user SET username=?, usertype=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "sss", $username, $usertype, $id);
    mysqli_stmt_execute($stmt);
    header("Location: viewusers.php");
    exit();
}

// Detect which row is being edited
$edit_row_id = isset($_GET['edit_row_id']) ? $_GET['edit_row_id'] : null;

// Handle search and filter
$search = '';
$usertype_filter = '';
if (isset($_GET['search']) || isset($_GET['usertype_filter'])) {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $usertype_filter = isset($_GET['usertype_filter']) ? $_GET['usertype_filter'] : '';
    $search_sql = "%" . $search . "%";
    $query = "SELECT id, username, usertype FROM user WHERE (id LIKE ? OR username LIKE ? OR usertype LIKE ?)";
    $params = [$search_sql, $search_sql, $search_sql];
    $types = "sss";
    if ($usertype_filter !== '') {
        $query .= " AND usertype = ?";
        $params[] = $usertype_filter;
        $types .= "s";
    }
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, "SELECT id, username, usertype FROM user");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Administrator dashboard</title>
    <link rel="stylesheet" type="text/css" href="admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap-theme.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js"></script>
    <style>
        .main-flex-container {
            display: flex;
            align-items: flex-start;
        }
        .small-table-container {
            max-width: 900px;
            margin: 40px auto 0 auto;
        }
        .small-table-container table {
            width: 100%;
            font-size: 15px;
        }
        .small-table-container th:last-child,
        .small-table-container td:last-child {
            width: 120px;
            white-space: nowrap;
        }
        .search-bar {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        @media (max-width: 600px) {
            .header {
                justify-content: flex-start;
            }
            .sidebar-toggle {
                margin-right: 10px;
            }
        }
        .admin-sidebar {
            background: #0e7490;
            color: #fff;
            border-radius: 0 10px 10px 0;
            box-shadow: 2px 0 8px #e0e0e0;
            margin: 0;
            padding-top: 30px;
            padding-bottom: 30px;
            min-width: 200px;
            max-width: 240px;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            position: relative;
            z-index: 10;
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
        @media (max-width: 900px) {
            .admin-sidebar {
                position: static;
                width: 100vw;
                max-width: 100vw;
                height: auto;
                border-radius: 0;
                box-shadow: none;
                margin-bottom: 20px;
            }
        }
        @media (max-width: 600px) {
            .admin-sidebar {
                width: 100vw;
                min-width: unset;
                max-width: unset;
                padding-top: 10px;
                padding-bottom: 10px;
                border-radius: 0;
                box-shadow: none;
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
        <i class="fa fa-user"></i>
        <span style="margin-left: 8px;">Users</span>
    </span>
    <div class="logout">
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</header>
<nav class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <i class="fa fa-user-shield"></i> Admin Panel
    </div>
    <ul>
        <li class="active"><a href="adminhome.php"><i class="fa fa-home"></i> Dashboard</a></li>
        <li><a href="management.php"><i class="fa fa-user-cog"></i> User Management</a></li>
        <li><a href="add_users.php"><i class="fa fa-user-plus"></i> Add Users</a></li>
    </ul>
</nav>

<div class="small-table-container">
    <h3>All Users</h3>
    <form method="get" class="search-bar">
        <input type="text" name="search" class="form-control" placeholder="Search by ID, Username, or Type" value="<?php echo htmlspecialchars($search); ?>">
        <select name="usertype_filter" class="form-control">
            <option value="">All Types</option>
            <option value="admin" <?php if($usertype_filter=='admin') echo 'selected'; ?>>Admin</option>
            <option value="teacher" <?php if($usertype_filter=='teacher') echo 'selected'; ?>>Teacher</option>
            <option value="accountant" <?php if($usertype_filter=='accountant') echo 'selected'; ?>>Accountant</option>
            <option value="parents" <?php if($usertype_filter=='parents') echo 'selected'; ?>>Parents</option>
        </select>
        <button type="submit" class="btn btn-info">Search</button>
        <a href="viewusers.php" class="btn btn-default">Reset</a>
    </form>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Staff ID</th>
                <th>Username</th>
                <th>User Type</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <?php if ($edit_row_id == $row['id']): ?>
            <tr>
                <form method="post" action="">
                    <td>
                        <input type="text" name="id" value="<?php echo htmlspecialchars($row['id']); ?>" readonly class="form-control" style="width:80px;">
                    </td>
                    <td>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($row['username']); ?>" class="form-control" required>
                    </td>
                    <td>
                        <select name="usertype" class="form-control" required>
                            <option value="admin" <?php if($row['usertype']=='admin') echo 'selected'; ?>>Admin</option>
                            <option value="teacher" <?php if($row['usertype']=='teacher') echo 'selected'; ?>>Teacher</option>
                        </select>
                    </td>
                    <td>
                        <button type="submit" name="save_user" class="btn btn-success btn-xs">Save</button>
                        <a href="viewusers.php" class="btn btn-default btn-xs">Cancel</a>
                    </td>
                </form>
            </tr>
            <?php else: ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['usertype']); ?></td>
                <td>
                    <form method="post" action="" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="delete_user" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
                    </form>
                    <span style="display:inline-block; width:8px;"></span>
                    <form method="get" action="viewusers.php" style="display:inline;">
                        <input type="hidden" name="edit_row_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" class="btn btn-warning btn-xs">Update</button>
                    </form>
                </td>
            </tr>
            <?php endif; ?>
            <?php endwhile; ?>
        </tbody>
    </table>
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
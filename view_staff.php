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

// Handle search
$search = '';
$where = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where = "WHERE staffid LIKE '%$search%' OR name LIKE '%$search%' OR phone LIKE '%$search%' OR email LIKE '%$search%'";
}

// Handle delete
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete']);
    $result = mysqli_query($conn, "DELETE FROM stafftb WHERE staffid='$delete_id'");
    if ($result) {
        $_SESSION['alert'] = "Staff deleted successfully!";
    } else {
        $_SESSION['alert'] = "Delete failed. Please try again.";
    }
    header("Location: view_staff.php");
    exit();
}

// If editing, get staff data
$edit_staff = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit']);
    $result = mysqli_query($conn, "SELECT * FROM stafftb WHERE staffid='$edit_id' LIMIT 1");
    if ($result && mysqli_num_rows($result) > 0) {
        $edit_staff = mysqli_fetch_assoc($result);
    }
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $staffid = mysqli_real_escape_string($conn, $_POST['staffid']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $licenseno = mysqli_real_escape_string($conn, $_POST['licenseno']);
    $registeredno = mysqli_real_escape_string($conn, $_POST['registeredno']);
    $rank = mysqli_real_escape_string($conn, $_POST['rank']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $ssnitno = mysqli_real_escape_string($conn, $_POST['ssnitno']);
    $niano = mysqli_real_escape_string($conn, $_POST['niano']);
    $bank = mysqli_real_escape_string($conn, $_POST['bank']);
    $acno = mysqli_real_escape_string($conn, $_POST['acno']);
    $branch = mysqli_real_escape_string($conn, $_POST['branch']);
    $religion = mysqli_real_escape_string($conn, $_POST['religion']);
    $denomination = mysqli_real_escape_string($conn, $_POST['denomination']);
    $emergencyno = mysqli_real_escape_string($conn, $_POST['emergencyno']);

    // Handle profile picture upload
    $profilepicture = $_POST['current_profilepicture'];
    if (isset($_FILES['profilepicture']) && $_FILES['profilepicture']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $target_file = $target_dir . basename($_FILES["profilepicture"]["name"]);
        move_uploaded_file($_FILES["profilepicture"]["tmp_name"], $target_file);
        $profilepicture = $target_file;
    }

    $sql = "UPDATE stafftb SET 
        name='$name', dob='$dob', licenseno='$licenseno', registeredno='$registeredno', rank='$rank',
        phone='$phone', email='$email', ssnitno='$ssnitno', niano='$niano', bank='$bank', acno='$acno',
        branch='$branch', religion='$religion', denomination='$denomination', emergencyno='$emergencyno',
        profilepicture='$profilepicture'
        WHERE staffid='$staffid'";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['alert'] = "Staff updated successfully!";
        header("Location: view_staff.php");
        exit();
    } else {
        $_SESSION['alert'] = "Update failed. Please try again.";
        header("Location: view_staff.php?edit=" . urlencode($staffid));
        exit();
    }
}

// Show alert if set in session
$alert = '';
if (isset($_SESSION['alert'])) {
    $alert = $_SESSION['alert'];
    unset($_SESSION['alert']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="admin-panel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <meta charset="utf-8">
    <title>Administrator Dashboard - Staff List</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap and Font Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .main-flex-container {
            display: flex;
            align-items: flex-start;
            width: 100vw;
            min-height: 100vh;
            background: #f7f7fa;
            flex-direction: row;
        }
        .dashboard-container {
            flex: 1;
            padding: 20px 10px 30px 10px;
            min-width: 0;
        }
        .staff-header {
            text-align: center;
            font-size: 1.2rem;
            font-weight: bold;
            margin: 20px auto 30px auto;
            padding: 8px 0;
            width: 35%;
            background: #e5e5e5;
            color: #222;
            border-radius: 30px;
            box-shadow: 0 2px 12px rgba(14, 165, 233, 0.08);
            letter-spacing: 2px;
        }
        .search-bar {
            display: flex;
            justify-content: center;
            margin-bottom: 18px;
            width: 100%;
        }
        .search-bar .input-group {
            width: 100%;
            max-width: 320px;
        }
        .table-responsive {
            margin-top: 0 !important;
            background: #fff;
            width: 100%;
            overflow-x: auto;
        }
        .table {
            width: 100%;
            min-width: 900px;
        }
        .table thead tr {
            background: #f9f9f9;
            color: #222;
        }
        .table tbody tr:nth-child(even),
        .table tbody tr:hover {
            background: #fff;
        }
        .table th, .table td {
            vertical-align: middle !important;
            text-align: center;
            white-space: nowrap;
        }
        .btn-info, .btn-danger {
            margin-bottom: 3px;
        }
        /* Responsive styles */
        @media (max-width: 1200px) {
            .staff-header {
                width: 60%;
                font-size: 1rem;
            }
        }
        @media (max-width: 900px) {
            .main-flex-container {
                flex-direction: column;
                align-items: center;
            }
            .dashboard-container {
                width: 100vw;
                padding: 10px 2vw 30px 2vw;
            }
            .staff-header {
                width: 90vw;
                font-size: 1rem;
            }
        }
        @media (max-width: 700px) {
            .main-flex-container {
                flex-direction: column;
                align-items: stretch;
            }
            .dashboard-container {
                padding: 8px 0 20px 0;
            }
            .staff-header {
                width: 98vw;
                font-size: 0.98rem;
                padding: 6px 0;
            }
            .search-bar .input-group {
                max-width: 98vw;
            }
            .table {
                min-width: 700px;
            }
        }
        @media (max-width: 600px) {
            .main-flex-container {
                flex-direction: column;
                align-items: stretch;
            }
            .dashboard-container {
                padding: 4px 0 10px 0;
            }
            .staff-header {
                width: 100vw;
                font-size: 0.95rem;
                padding: 5px 0;
                margin: 10px 0 18px 0;
            }
            .search-bar {
                margin-bottom: 10px;
            }
            .search-bar .input-group {
                max-width: 99vw;
            }
            .table {
                min-width: 500px;
            }
            .table-responsive {
                padding: 0;
            }
        }
    </style>
</head>
<body>
<header class="header">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle admin panel">
        <i class="fa fa-bars"></i>
    </button>
    <div class="dashboard-title">
            <span class="dashboard-title" style="margin: 0 auto; display: flex; align-items: center; justify-content: center; flex: 1; text-align: center;">
        <i class="fa fa-user"></i>
        <span style="margin-left: 8px;">Staff List</span>
    </span>
    </div>
    <div class="logout">
        <a href="logout.php" class="btn btn-danger"><i class="fa fa-sign-out-alt"></i> Logout</a>
    </div>
</header>
<div class="main-flex-container">
    <nav class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <i class="fa fa-user-shield"></i>
            <span>Admin Panel</span>
        </div>
        <ul>
            <li><a href="adminhome.php"><i class="fa fa-home"></i><span>Dashboard</span></a></li>
            <li><a href="add_staff.php"><i class="fa fa-user-plus"></i><span>Add Staff</span></a></li>
            <li class="active"><a href="view_staff.php"><i class="fa fa-users"></i><span>Staff List</span></a></li>
            <li><a href="students.php"><i class="fa fa-user-graduate"></i><span>Students</span></a></li>
            <li><a href="tuition_fees.php"><i class="fa fa-money-bill"></i><span>Tuition Fees</span></a></li>
            <li><a href="settings.php"><i class="fa fa-cogs"></i><span>Settings</span></a></li>
        </ul>
    </nav>
    <div class="dashboard-container">
        
        <?php if (!empty($alert)): ?>
            <div class="alert alert-success" id="alertBox"><?php echo htmlspecialchars($alert); ?></div>
            <script>
                setTimeout(function(){
                    var alertBox = document.getElementById('alertBox');
                    if(alertBox) alertBox.style.display='none';
                }, 2500);
            </script>
        <?php endif; ?>
        <form class="search-bar" method="get" action="view_staff.php">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search staff..." value="<?php echo htmlspecialchars($search); ?>">
                <span class="input-group-btn">
                    <button class="btn btn-primary" type="submit"><i class="glyphicon glyphicon-search"></i> Search</button>
                </span>
            </div>
        </form>
        <div style="clear:both;"></div>

        <?php if ($edit_staff): ?>
        <!-- Edit Form for selected staff -->
        <form method="post" action="view_staff.php" enctype="multipart/form-data">
            <input type="hidden" name="staffid" value="<?php echo htmlspecialchars($edit_staff['staffid']); ?>">
            <input type="hidden" name="current_profilepicture" value="<?php echo htmlspecialchars($edit_staff['profilepicture']); ?>">
            <table class="table table-bordered">
                <tr>
                    <th>Staff ID</th>
                    <td><input type="text" class="form-control" value="<?php echo htmlspecialchars($edit_staff['staffid']); ?>" readonly></td>
                    <th>Name</th>
                    <td><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($edit_staff['name']); ?>" required></td>
                </tr>
                <tr>
                    <th>DOB</th>
                    <td><input type="date" name="dob" class="form-control" value="<?php echo htmlspecialchars($edit_staff['dob']); ?>" required></td>
                    <th>License No</th>
                    <td><input type="text" name="licenseno" class="form-control" value="<?php echo htmlspecialchars($edit_staff['licenseno']); ?>" required></td>
                </tr>
                <tr>
                    <th>Registered No</th>
                    <td><input type="text" name="registeredno" class="form-control" value="<?php echo htmlspecialchars($edit_staff['registeredno']); ?>" required></td>
                    <th>Rank</th>
                    <td><input type="text" name="rank" class="form-control" value="<?php echo htmlspecialchars($edit_staff['rank']); ?>" required></td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td><input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($edit_staff['phone']); ?>" required></td>
                    <th>Email</th>
                    <td><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($edit_staff['email']); ?>" required></td>
                </tr>
                <tr>
                    <th>SSNIT No</th>
                    <td><input type="text" name="ssnitno" class="form-control" value="<?php echo htmlspecialchars($edit_staff['ssnitno']); ?>" required></td>
                    <th>NIA No</th>
                    <td><input type="text" name="niano" class="form-control" value="<?php echo htmlspecialchars($edit_staff['niano']); ?>" required></td>
                </tr>
                <tr>
                    <th>Bank</th>
                    <td><input type="text" name="bank" class="form-control" value="<?php echo htmlspecialchars($edit_staff['bank']); ?>" required></td>
                    <th>Account No</th>
                    <td><input type="text" name="acno" class="form-control" value="<?php echo htmlspecialchars($edit_staff['acno']); ?>" required></td>
                </tr>
                <tr>
                    <th>Branch</th>
                    <td><input type="text" name="branch" class="form-control" value="<?php echo htmlspecialchars($edit_staff['branch']); ?>" required></td>
                    <th>Religion</th>
                    <td><input type="text" name="religion" class="form-control" value="<?php echo htmlspecialchars($edit_staff['religion']); ?>" required></td>
                </tr>
                <tr>
                    <th>Denomination</th>
                    <td><input type="text" name="denomination" class="form-control" value="<?php echo htmlspecialchars($edit_staff['denomination']); ?>" required></td>
                    <th>Emergency No</th>
                    <td><input type="text" name="emergencyno" class="form-control" value="<?php echo htmlspecialchars($edit_staff['emergencyno']); ?>" required></td>
                </tr>
                <tr>
                    <th>Profile Picture</th>
                    <td colspan="3">
                        <?php if (!empty($edit_staff['profilepicture'])): ?>
                            <img src="<?php echo htmlspecialchars($edit_staff['profilepicture']); ?>" width="50" height="50" style="object-fit:cover;border-radius:50%;">
                        <?php endif; ?>
                        <input type="file" name="profilepicture" class="form-control" accept="image/*" style="margin-top:10px;">
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="text-align:right;">
                        <button type="submit" name="update" class="btn btn-success"><i class="glyphicon glyphicon-save"></i> Save Changes</button>
                        <a href="view_staff.php" class="btn btn-default">Cancel</a>
                    </td>
                </tr>
            </table>
        </form>
        <?php endif; ?>

        <div class="table-responsive" style="margin-top:0;">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Staff ID</th>
                    <th>Name</th>
                    <th>DOB</th>
                    <th>License No</th>
                    <th>Registered No</th>
                    <th>Rank</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>SSNIT No</th>
                    <th>NIA No</th>
                    <th>Bank</th>
                    <th>Account No</th>
                    <th>Branch</th>
                    <th>Religion</th>
                    <th>Denomination</th>
                    <th>Emergency No</th>
                    <th>Profile Picture</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = mysqli_query($conn, "SELECT * FROM stafftb $where");
                if (mysqli_num_rows($result) == 0) {
                    echo "<tr><td colspan='18' style='text-align:center;'>No staff found.</td></tr>";
                }
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['staffid']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['dob']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['licenseno']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['registeredno']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['rank']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['ssnitno']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['niano']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['bank']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['acno']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['branch']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['religion']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['denomination']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['emergencyno']) . "</td>";
                    echo "<td>";
                    if (!empty($row['profilepicture'])) {
                        echo "<img src='" . htmlspecialchars($row['profilepicture']) . "' width='50' height='50' style='object-fit:cover;border-radius:50%;'>";
                    }
                    echo "</td>";
                    echo "<td class='action-btns'>";
                    echo "<a href='view_staff.php?edit=" . urlencode($row['staffid']) . "' class='btn btn-xs btn-info' onclick=\"return confirm('Are you sure you want to edit this staff?');\"><i class='glyphicon glyphicon-edit'></i> Edit</a> ";
                    echo "<a href='view_staff.php?delete=" . urlencode($row['staffid']) . "' class='btn btn-xs btn-danger' onclick=\"return confirm('Are you sure you want to delete this staff?');\"><i class='glyphicon glyphicon-trash'></i> Delete</a>";
                    echo "</td>";
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
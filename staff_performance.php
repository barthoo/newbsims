<?php


$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Fetch all staff from stafftb
$staff_query = mysqli_query($conn, "SELECT staffid, name, staffid FROM stafftb ORDER BY name");
$staff_list = [];
while ($row = mysqli_fetch_assoc($staff_query)) {
    $staff_list[] = $row;
}

// For each staff, calculate performance
$performance = [];
foreach ($staff_list as $staff) {
    $staff_id = $staff['staffid'];

    // Count lesson notes submitted from jhs_lesson_note
    $lesson_note_count = 0;
    $ln_q = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM jhs_lesson_note WHERE id=$staff_id");
    if ($ln_row = mysqli_fetch_assoc($ln_q)) {
        $lesson_note_count = $ln_row['cnt'];
    }

    // Count assessments given (assuming assessments table uses staff_id)
    $assessment_count = 0;
    $as_q = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM assessments WHERE id=$staff_id");
    if ($as_row = mysqli_fetch_assoc($as_q)) {
        $assessment_count = $as_row['cnt'];
    }

    $performance[] = [
        'staff_no' => $staff['staffid'],
        'name' => $staff['name'],
        'lesson_notes' => $lesson_note_count,
        'assessments' => $assessment_count
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Staff Performance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <style>
        body { background: #f7f7fa; }
        .main-flex-container {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            width: 100vw;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            width: 100%;
            margin: 40px auto 40px auto; /* Center horizontally */
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px #ddd;
            padding: 30px;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        th, td { font-size: 14px; text-align: center; }
        thead th { background: #0e7490; color: #fff; }
        h3 { color: #0e7490; }
        @media (max-width: 900px) {
            .main-flex-container {
                flex-direction: column;
                align-items: stretch;
            }
            .container {
                margin: 20px auto;
                padding: 10px 2vw;
            }
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
    </style>
</head>
<body>
<link rel="stylesheet" href="admin-panel.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<header class="header">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle admin panel">
        <i class="fa fa-bars"></i>
    </button>
    <span class="dashboard-title"><i class="fa fa-gauge"></i> Administrator Dashboard</span>
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
    <div class="container">
        <h3 class="text-center">Staff Performance</h3>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Staff No.</th>
                    <th>Name</th>
                    <th>Lesson Notes Submitted</th>
                    <th>Assessments Given</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($performance) > 0): ?>
                    <?php $i=1; foreach ($performance as $row): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo htmlspecialchars($row['staff_no']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['lesson_notes']); ?></td>
                        <td><?php echo htmlspecialchars($row['assessments']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">No staff records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
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
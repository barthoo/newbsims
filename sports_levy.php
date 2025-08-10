<?php


$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Fetch students for selection
$students = [];
$res = mysqli_query($conn, "SELECT admission_no, first_name, other_names, class FROM students ORDER BY class, first_name, other_names");
while ($row = mysqli_fetch_assoc($res)) {
    $students[] = $row;
}

$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sports_levy'])) {
    $admission_no = mysqli_real_escape_string($conn, $_POST['admission_no']);
    $amount = (float)$_POST['amount'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $date_paid = date('Y-m-d H:i:s');

    // Get student info
    $stu_res = mysqli_query($conn, "SELECT class FROM students WHERE admission_no='$admission_no' LIMIT 1");
    $stu = mysqli_fetch_assoc($stu_res);
    $class = $stu ? $stu['class'] : '';

    if ($admission_no && $class) {
        $sql = "INSERT INTO sports_levy (student_id, class, amount, description, date_paid) VALUES ('$admission_no', '$class', '$amount', '$description', '$date_paid')";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $alert = "success";
        } else {
            $alert = "error";
            $sql_error = mysqli_error($conn);
        }
    } else {
        $alert = "error";
        $sql_error = "Student not found.";
    }
}

// For displaying student info in iframe
if (isset($_GET['student_info'])) {
    $admission_no = mysqli_real_escape_string($conn, $_GET['student_info']);
    $res = mysqli_query($conn, "SELECT * FROM students WHERE admission_no='$admission_no' LIMIT 1");
    $info = mysqli_fetch_assoc($res);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; font-size: 14px; background: #f8fafc; margin: 0; padding: 10px; }
            .student-info { background: #fff; border-radius: 8px; padding: 10px 16px; box-shadow: 0 1px 4px #ddd; }
            .student-info b { color: #dc2626; }
            .student-info div { margin-bottom: 4px; }
        </style>
    </head>
    <body>
    <?php if ($info): ?>
        <div class="student-info">
            <div><b>Admission No:</b> <?php echo htmlspecialchars($info['admission_no']); ?></div>
            <div><b>Name:</b> <?php echo htmlspecialchars($info['first_name'] . ' ' . $info['other_names']); ?></div>
            <div><b>Class:</b> <?php echo htmlspecialchars($info['class']); ?></div>
            <div><b>Email:</b> <?php echo htmlspecialchars($info['email']); ?></div>
            <div><b>Phone:</b> <?php echo htmlspecialchars($info['phone']); ?></div>
        </div>
    <?php else: ?>
        <div class="student-info">Student information not found.</div>
    <?php endif; ?>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sports Levy Payment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #f8fafc; }
        .main-flex-container {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            width: 100vw;
            min-height: 100vh;
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
        .main-container {
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 12px #ddd;
            padding: 30px 24px;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
        }
        h2 { color: #dc2626; font-weight: 700; margin-bottom: 24px; }
        .form-group label { font-weight: 600; }
        .btn-success { background: #dc2626; border: none; color: #fff; }
        .btn-success:hover { background: #b91c1c; }
        .alert { margin-top: 18px; }
        .student-info-iframe { margin-bottom: 18px; }
        .btn-group-custom { margin-bottom: 24px; }
        .btn-group-custom .btn { min-width: 180px; }
        @media (max-width: 900px) {
            .main-flex-container {
                flex-direction: column;
                align-items: stretch;
            }
            .main-container {
                margin: 20px auto;
                padding: 10px 2vw;
            }
            .btn-group-custom .btn { min-width: 100px; font-size: 0.95em; }
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
        @media (max-width: 700px) {
            .main-container { padding: 10px 2vw; }
            .btn-group-custom .btn { min-width: 100px; font-size: 0.95em; }
        }
    </style>
    <script>
    function updateClassAndInfo() {
        var select = document.getElementById('admission_no');
        var selected = select.options[select.selectedIndex];
        var classInput = document.getElementById('class');
        var iframe = document.getElementById('studentInfoFrame');
        if (selected && selected.value !== "") {
            classInput.value = selected.getAttribute('data-class');
            iframe.src = "sports_levy.php?student_info=" + encodeURIComponent(selected.value);
            document.getElementById('iframeWrap').style.display = "block";
        } else {
            classInput.value = "";
            iframe.src = "";
            document.getElementById('iframeWrap').style.display = "none";
        }
    }
    function openPaid() {
        window.location.href = "paid_sports_levy.php";
    }
    function openUnpaid() {
        window.location.href = "unpaid_sports_levy.php";
    }
    </script>
</head>
<body>
   
<link rel="stylesheet" href="admin-panel.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<header class="header">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle admin panel">
        <i class="fa fa-bars"></i>
    </button>
       <span class="dashboard-title" style="margin: 0 auto; display: flex; align-items: center; justify-content: center; flex: 1; text-align: center;">
        <i class="fa fa-futbol"></i>
        <span style="margin-left: 8px;">Sports Levy</span>
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
            <li><a href="tuition_fees.php"><i class="fa fa-money-bill"></i> Tuition Fees</a></li>
            <li><a href="pta_levy.php"><i class="fa fa-users"></i> PTA Levy</a></li>
            <li><a href="classes_fees.php"><i class="fa fa-file-alt"></i> Classes Fees</a></li>
            <li><a href="examination_fees.php"><i class="fa fa-file-alt"></i> Examination Fees</a></li>
        </ul>
    </nav>
    <div class="main-container">
        <h2><i class="fa fa-futbol"></i> Sports Levy Payment</h2>
        <div class="btn-group btn-group-custom">
            <button type="button" class="btn btn-success" onclick="openPaid()">
                <i class="fa fa-check-circle"></i> Paid Students
            </button>
            <button type="button" class="btn btn-danger" onclick="openUnpaid()">
                <i class="fa fa-times-circle"></i> Unpaid Students
            </button>
        </div>
        <form method="post" action="sports_levy.php">
            <div class="form-group">
                <label for="admission_no">Student Name:</label>
                <select name="admission_no" id="admission_no" class="form-control" required onchange="updateClassAndInfo()">
                    <option value="">Select Name</option>
                    <?php foreach ($students as $stu): ?>
                        <option value="<?php echo htmlspecialchars($stu['admission_no']); ?>" data-class="<?php echo htmlspecialchars($stu['class']); ?>">
                            <?php echo htmlspecialchars($stu['first_name'] . ' ' . $stu['other_names']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="class">Class:</label>
                <input type="text" name="class" id="class" class="form-control" readonly>
            </div>
            <div class="student-info-iframe" id="iframeWrap" style="display:none;">
                <iframe id="studentInfoFrame" src="" width="100%" height="150" style="border:1px solid #ccc;border-radius:6px;"></iframe>
            </div>
            <div class="form-group">
                <label for="amount">Amount (GHC):</label>
                <input type="number" name="amount" id="amount" class="form-control" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <input type="text" name="description" id="description" class="form-control" required placeholder="E.g. Sports Levy 2024">
            </div>
            <div class="form-group">
                <label for="date_paid">Date Paid:</label>
                <input type="text" name="date_paid" id="date_paid" class="form-control" value="<?php echo date('Y-m-d H:i:s'); ?>" readonly>
            </div>
            <button type="submit" name="add_sports_levy" class="btn btn-success">
                <i class="fa fa-plus"></i> Add Sports Levy
            </button>
        </form>
        <?php if ($alert === "success"): ?>
            <div class="alert alert-success">Sports levy payment added successfully!</div>
        <?php elseif ($alert === "error"): ?>
            <div class="alert alert-danger">
                Failed to add sports levy payment.
                <?php if (isset($sql_error)) echo "<br><b>Error:</b> " . htmlspecialchars($sql_error); ?>
            </div>
        <?php endif; ?>
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
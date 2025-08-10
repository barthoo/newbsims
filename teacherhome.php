<?php


session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
} elseif ($_SESSION['username'] == 'admin') {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Simulate teacher_id from session (replace with your actual logic)
$teacher_id = isset($_SESSION['teacher_id']) ? $_SESSION['teacher_id'] : 1;

// Count unread announcements for this teacher
$unread_count = 0;
if ($teacher_id) {
    $unread_sql = "
        SELECT COUNT(a.id) as cnt
        FROM announcements a
        LEFT JOIN announcement_reads r
            ON a.id = r.announcement_id AND r.teacher_id = $teacher_id
        WHERE (a.recipient_type = 'teachers' OR a.recipient_type = 'all')
          AND r.id IS NULL
    ";
    $unread_res = mysqli_query($conn, $unread_sql);
    if ($row = mysqli_fetch_assoc($unread_res)) {
        $unread_count = (int)$row['cnt'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .header {
            background: #007bff;
            color: #fff;
            padding: 18px 30px;
            border-radius: 0 0 8px 8px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header a { color: #fff; font-size: 1.5em; text-decoration: none; }
        .main-container {
            max-width: 700px;
            margin: 40px auto;
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
            position: relative;
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
            transition: color 0.2s;
        }
        .icon-box span {
            display: block;
            font-size: 1.2em;
            font-weight: 600;
            margin-top: 8px;
        }
        /* Bell shake animation */
        .fa-bell.shake {
            animation: shake-bell 0.7s cubic-bezier(.36,.07,.19,.97) both infinite;
            color: #f59e42;
        }
        @keyframes shake-bell {
            10%, 90% { transform: translateX(-2px); }
            20%, 80% { transform: translateX(4px); }
            30%, 50%, 70% { transform: translateX(-8px); }
            40%, 60% { transform: translateX(8px); }
        }
        .bell-badge {
            position: absolute;
            top: 18px;
            right: 32px;
            background: #e11d48;
            color: #fff;
            font-size: 13px;
            font-weight: bold;
            border-radius: 50%;
            padding: 2px 7px;
            min-width: 24px;
            text-align: center;
            border: 2px solid #fff;
            box-shadow: 0 1px 4px #e0e0e0;
            z-index: 2;
        }
        @media (max-width: 700px) {
            .main-container { padding: 10px 0; }
            .icon-menu { flex-direction: column; gap: 18px; }
            .icon-box { padding: 25px; }
            .bell-badge { top: 10px; right: 18px; }
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="#"><i class="fa fa-chalkboard-teacher"></i> Teacher Dashboard</a>
        <div class="logout">
            <a href="logout.php" class="btn btn-danger btn-sm"><i class="fa fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>
    <div class="main-container">
        <div class="icon-menu">
            <a href="mark_student_attendance.php" class="icon-box">
                <i class="fa fa-calendar-check text-primary"></i>
                <span>Mark Attendance</span>
            </a>
            <a href="student_assessment.php" class="icon-box">
                <i class="fa fa-chart-bar text-success"></i>
                <span>Assessment</span>
            </a>
            <a href="lesson_note.php" class="icon-box">
                <i class="fa fa-book text-warning"></i>
                <span>Lesson Notes</span>
            </a>
            <a href="requisition.php" class="icon-box">
                <i class="fa fa-file-invoice text-danger"></i>
                <span>Requisition</span>
            </a>
            <a href="viewnotice.php" class="icon-box" style="position:relative;">
                <i class="fa fa-bell<?php echo ($unread_count > 0) ? ' shake' : ''; ?>"></i>
                <?php if ($unread_count > 0): ?>
                    <span class="bell-badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
                <span>Announcements</span>
            </a>
        </div>
    </div>
</body>
</html>
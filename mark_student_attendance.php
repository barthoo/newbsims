<?php


session_start();
$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Fetch unique class and teacher/guardian from students table for dropdown
$class_options = [];
$class_query = mysqli_query($conn, "SELECT DISTINCT class, parent_guardian FROM students ORDER BY class ASC, parent_guardian ASC");
while ($row = mysqli_fetch_assoc($class_query)) {
    $class_options[] = [
        'class' => $row['class'],
        'teacher' => $row['parent_guardian']
    ];
}

// Get active academic session
$active_session = mysqli_fetch_assoc(mysqli_query($conn, "SELECT academic_year, term FROM academic_sessions WHERE is_active=1 LIMIT 1"));
$active_academic = $active_session
    ? $active_session['academic_year'] . ' - ' . $active_session['term']
    : 'No active academic session';

// Handle class selection and fetch students
$selected_class = isset($_GET['class']) ? $_GET['class'] : '';
$students = [];
if ($selected_class) {
    $safe_class = mysqli_real_escape_string($conn, $selected_class);
    $result = mysqli_query($conn, "SELECT id, admission_no, first_name, other_names FROM students WHERE class='$safe_class' ORDER BY first_name, other_names");
    while ($row = mysqli_fetch_assoc($result)) {
        $students[] = $row;
    }
}

// Handle attendance submission
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance_mark'])) {
    if (!$active_session) {
        $alert = '<div class="alert alert-danger" style="max-width:350px;margin:10px auto;">Attendance cannot be taken. No active academic session!</div>';
    } else {
        $class = mysqli_real_escape_string($conn, $_POST['class']);
        $date = mysqli_real_escape_string($conn, $_POST['date']);
        $academic_year = mysqli_real_escape_string($conn, $active_session['academic_year']);
        $term = mysqli_real_escape_string($conn, $active_session['term']);

        // Check for duplicates before marking
        $already_marked = [];
        foreach ($_POST['attendance'] as $student_id => $status) {
            $student_id = (int)$student_id;
            $check = mysqli_query($conn, "SELECT id FROM attendance WHERE student_id=$student_id AND date='$date' AND academic_year='$academic_year' AND term='$term' LIMIT 1");
            if (mysqli_num_rows($check) > 0) {
                $already_marked[] = $student_id;
            }
        }

        if (count($already_marked) > 0) {
            // Fetch names for alert
            $ids = implode(',', $already_marked);
            $names = [];
            $name_query = mysqli_query($conn, "SELECT first_name, other_names FROM students WHERE id IN ($ids)");
            while ($row = mysqli_fetch_assoc($name_query)) {
                $names[] = $row['first_name'] . ' ' . $row['other_names'];
            }
            $alert = '<div class="alert alert-danger" style="max-width:350px;margin:10px auto;">Attendance already marked for <b>' . htmlspecialchars(implode(', ', $names)) . '</b> today.</div>';
        } else {
            foreach ($_POST['attendance'] as $student_id => $status) {
                $student_id = (int)$student_id;
                $status = mysqli_real_escape_string($conn, $status);
                mysqli_query($conn, "INSERT INTO attendance (student_id, class, date, status, academic_year, term) VALUES ($student_id, '$class', '$date', '$status', '$academic_year', '$term')");
            }
            $alert = '<div class="alert alert-success" style="max-width:350px;margin:10px auto;">Attendance saved!</div>';
        }
    }
}

// --- FILTER LOGIC FOR VIEW MARKED STUDENT ATTENDANCE ---
$filter_class = isset($_GET['filter_class']) ? $_GET['filter_class'] : '';
$filter_student = isset($_GET['filter_student']) ? $_GET['filter_student'] : '';
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';
$where = "1=1";
if ($filter_class) {
    $safe_filter_class = mysqli_real_escape_string($conn, $filter_class);
    $where .= " AND a.class = '$safe_filter_class'";
}
if ($filter_student) {
    $safe_filter_student = (int)$filter_student;
    $where .= " AND a.student_id = $safe_filter_student";
}
if ($active_session) {
    $safe_term = mysqli_real_escape_string($conn, $active_session['term']);
    $safe_year = mysqli_real_escape_string($conn, $active_session['academic_year']);
    $where .= " AND a.term = '$safe_term' AND a.academic_year = '$safe_year'";
}
if ($from_date && $to_date) {
    $where .= " AND a.date BETWEEN '$from_date' AND '$to_date'";
} elseif ($from_date) {
    $where .= " AND a.date >= '$from_date'";
} elseif ($to_date) {
    $where .= " AND a.date <= '$to_date'";
}
$attendance_records = [];
$attendance_query = "
    SELECT a.*, s.first_name, s.other_names, s.admission_no
    FROM attendance a
    LEFT JOIN students s ON a.student_id = s.id
    WHERE $where
    ORDER BY a.date ASC, s.first_name ASC, s.other_names ASC
";
$attendance_result = mysqli_query($conn, $attendance_query);
while ($row = mysqli_fetch_assoc($attendance_result)) {
    $attendance_records[] = $row;
}

// Fetch all students for filter dropdown
$student_filter_list = [];
$student_filter_query = mysqli_query($conn, "SELECT id, admission_no, first_name, other_names, class FROM students ORDER BY first_name, other_names");
while ($row = mysqli_fetch_assoc($student_filter_query)) {
    $student_filter_list[] = $row;
}

// Export to Excel
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=student_attendance_" . date('Ymd') . ".xls");
    echo "Admission No.\tName\tClass\tStatus\tDate\n";
    foreach ($attendance_records as $record) {
        echo $record['admission_no'] . "\t" .
             $record['first_name'] . ' ' . $record['other_names'] . "\t" .
             $record['class'] . "\t" .
             $record['status'] . "\t" .
             $record['date'] . "\n";
    }
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mark Student Attendance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .main-container { max-width: 800px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #ddd; padding: 30px; }
        .header { background: #007bff; color: #fff; padding: 18px 30px; border-radius: 8px 8px 0 0; margin-bottom: 30px; }
        .header h2 { margin: 0; font-size: 2em; }
        .attendance-table th, .attendance-table td { vertical-align: middle !important; }
        .attendance-table select { min-width: 120px; }
        .small-alert { max-width: 350px; margin: 0 auto 20px auto; font-size: 1em; padding: 8px 16px; }
        .filter-form { margin: 30px 0 10px 0; display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
        @media (max-width: 900px) {
            .main-container { padding: 10px; }
            .filter-form { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body>
<div class="header text-center">
    <h2><i class="fa fa-calendar-check"></i> Mark Student Attendance</h2>
</div>
<div class="main-container">
    <div style="margin-bottom:15px; font-size:16px; color:#0e7490;">
        <b>Active Academic Session:</b>
        <?php echo htmlspecialchars($active_academic); ?>
    </div>
    <?php if ($alert) echo '<div id="alertBox" class="small-alert">'.$alert.'</div>'; ?>

    <!-- Mark Attendance Form -->
    <form method="get" class="form-inline" style="margin-bottom:25px;">
        <label for="class"><b>Class:</b></label>
        <select name="class" id="class" class="form-control" style="width:120px;" onchange="this.form.submit()">
            <option value="">Select Class</option>
            <?php foreach ($class_options as $ct): ?>
                <option value="<?php echo htmlspecialchars($ct['class']); ?>" <?php if($selected_class==$ct['class']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($ct['class']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <?php if ($selected_class && count($students) > 0): ?>
    <form method="post" style="margin-bottom:30px; background:#f1f5f9; padding:18px 12px; border-radius:8px;">
        <input type="hidden" name="class" value="<?php echo htmlspecialchars($selected_class); ?>">
        <input type="hidden" name="date" value="<?php echo date('Y-m-d'); ?>">
        <h4 style="margin-top:0;">Mark Attendance for <?php echo htmlspecialchars($selected_class); ?> (<?php echo htmlspecialchars($active_academic); ?>) - <?php echo date('jS M Y'); ?></h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Admission No.</th>
                        <th>Name</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($students as $stu): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($stu['admission_no']); ?></td>
                        <td><?php echo htmlspecialchars($stu['first_name'] . ' ' . $stu['other_names']); ?></td>
                        <td>
                            <select name="attendance[<?php echo $stu['id']; ?>]" class="form-control input-sm" required>
                                <option value="Present">Present</option>
                                <option value="Absent">Absent</option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <button type="submit" name="attendance_mark" class="btn btn-success"><i class="fa fa-check"></i> Submit Attendance</button>
    </form>
    <?php elseif ($selected_class): ?>
        <div class="alert alert-warning" style="margin-top:10px;">No students found for the selected class.</div>
    <?php endif; ?>

    <form method="get" class="filter-form">
        <label for="filter_class"><b>Class:</b></label>
        <select name="filter_class" id="filter_class" class="form-control" style="width:120px;">
            <option value="">All Classes</option>
            <?php foreach ($class_options as $ct): ?>
                <option value="<?php echo htmlspecialchars($ct['class']); ?>" <?php if($filter_class==$ct['class']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($ct['class']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label for="filter_student"><b>Student:</b></label>
        <select name="filter_student" id="filter_student" class="form-control" style="width:180px;">
            <option value="">All Students</option>
            <?php foreach ($student_filter_list as $stu): ?>
                <option value="<?php echo $stu['id']; ?>" <?php if($filter_student==$stu['id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($stu['first_name'] . ' ' . $stu['other_names'] . ' (' . $stu['admission_no'] . ')'); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label for="from_date"><b>From:</b></label>
        <input type="date" id="from_date" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>" class="form-control" style="width:120px;">
        <label for="to_date"><b>To:</b></label>
        <input type="date" id="to_date" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>" class="form-control" style="width:120px;">
        <button type="submit" class="btn btn-info btn-sm" style="margin-left:5px;"><i class="fa fa-filter"></i> Filter</button>
        <a href="?filter_class=<?php echo htmlspecialchars($filter_class); ?>&filter_student=<?php echo htmlspecialchars($filter_student); ?>&from_date=<?php echo htmlspecialchars($from_date); ?>&to_date=<?php echo htmlspecialchars($to_date); ?>&export=excel" class="btn btn-success btn-sm" style="margin-left:5px;">
            <i class="fa fa-file-excel"></i> Export to Excel
        </a>
    </form>
    <div class="table-responsive">
        <table class="table table-bordered table-hover attendance-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Admission No.</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Status</th>
                    <th style="width:110px;">Date</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($attendance_records) > 0): ?>
                <?php foreach ($attendance_records as $i => $record): ?>
                    <tr>
                        <td><?php echo $i+1; ?></td>
                        <td><?php echo htmlspecialchars($record['admission_no']); ?></td>
                        <td><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['other_names']); ?></td>
                        <td><?php echo htmlspecialchars($record['class']); ?></td>
                        <td><?php echo htmlspecialchars($record['status']); ?></td>
                        <td><?php echo htmlspecialchars($record['date']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center;">No attendance records found for this filter.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
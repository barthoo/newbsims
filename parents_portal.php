<?php

session_start();

$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

$show_form = true;
$show_results = false;
$error = '';
$student = null;
$academic_year_options = [];
$term_options = [];
$selected_academic_year = '';
$selected_term = '';
$subjects = [];
$positions = [];
$overall_position = '-';
$total_students = 0;
$attendance_total = '-';
$attendance_days = '-';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admission_no'], $_POST['student_class'])) {
    $admission_no = trim($_POST['admission_no']);
    $student_class = trim($_POST['student_class']);
    $selected_academic_year = isset($_POST['academic_year']) ? $_POST['academic_year'] : '';
    $selected_term = isset($_POST['term']) ? $_POST['term'] : '';

    // Find student
    $stu_q = mysqli_query($conn, "SELECT * FROM students WHERE admission_no='" . mysqli_real_escape_string($conn, $admission_no) . "' AND class='" . mysqli_real_escape_string($conn, $student_class) . "' LIMIT 1");
    if ($student = mysqli_fetch_assoc($stu_q)) {
        $student_id = $student['id'];
        $show_form = false;
        $show_results = true;

        // Academic year options for this student
        $year_q = mysqli_query($conn, "SELECT DISTINCT academic_year FROM assessments WHERE student_id=$student_id ORDER BY academic_year DESC");
        while ($row = mysqli_fetch_assoc($year_q)) {
            $academic_year_options[] = $row['academic_year'];
        }
        // Term options for this student
        $term_q = mysqli_query($conn, "SELECT DISTINCT term FROM assessments WHERE student_id=$student_id ORDER BY term ASC");
        while ($row = mysqli_fetch_assoc($term_q)) {
            $term_options[] = $row['term'];
        }

        // If academic year or term not selected, use latest
        if (!$selected_academic_year && count($academic_year_options)) {
            $selected_academic_year = $academic_year_options[0];
        }
        if (!$selected_term && count($term_options)) {
            $selected_term = $term_options[0];
        }

        // Get all subjects for the class (not just those the student has)
        $all_subjects = [];
        $subj_q = mysqli_query($conn, "SELECT DISTINCT subject FROM assessments WHERE class='" . mysqli_real_escape_string($conn, $student_class) . "'");
        while ($row = mysqli_fetch_assoc($subj_q)) {
            $all_subjects[] = $row['subject'];
        }

        // For each subject, get the student's assessment (if any)
        foreach ($all_subjects as $subject) {
            $assess_q = mysqli_query($conn, "
                SELECT scaled_50, exam_scaled_50, final_total, grade
                FROM assessments
                WHERE student_id=$student_id
                  AND subject='" . mysqli_real_escape_string($conn, $subject) . "'
                  " . ($selected_academic_year ? "AND academic_year='" . mysqli_real_escape_string($conn, $selected_academic_year) . "'" : "") . "
                  " . ($selected_term ? "AND term='" . mysqli_real_escape_string($conn, $selected_term) . "'" : "") . "
                LIMIT 1
            ");
            if ($row = mysqli_fetch_assoc($assess_q)) {
                $subjects[] = array_merge(['subject' => $subject], $row);
            } else {
                // No record for this subject, show empty
                $subjects[] = [
                    'subject' => $subject,
                    'scaled_50' => '',
                    'exam_scaled_50' => '',
                    'final_total' => '',
                    'grade' => ''
                ];
            }
        }

        // Calculate subject positions for each subject
        foreach ($all_subjects as $subject) {
            $subject_esc = mysqli_real_escape_string($conn, $subject);
            $score_row = mysqli_query($conn, "
                SELECT final_total
                FROM assessments
                WHERE student_id=$student_id
                  AND subject='$subject_esc'
                  " . ($selected_academic_year ? "AND academic_year='" . mysqli_real_escape_string($conn, $selected_academic_year) . "'" : "") . "
                  " . ($selected_term ? "AND term='" . mysqli_real_escape_string($conn, $selected_term) . "'" : "") . "
                LIMIT 1
            ");
            $student_score = '';
            if ($row = mysqli_fetch_assoc($score_row)) {
                $student_score = $row['final_total'];
            }
            $scores = [];
            $score_q = mysqli_query($conn, "
                SELECT final_total
                FROM assessments
                WHERE class='" . mysqli_real_escape_string($conn, $student_class) . "'
                  AND subject='$subject_esc'
                  " . ($selected_academic_year ? "AND academic_year='" . mysqli_real_escape_string($conn, $selected_academic_year) . "'" : "") . "
                  " . ($selected_term ? "AND term='" . mysqli_real_escape_string($conn, $selected_term) . "'" : "") . "
            ");
            while ($row2 = mysqli_fetch_assoc($score_q)) {
                $scores[] = $row2['final_total'];
            }
            rsort($scores);
            $position = ($student_score !== '') ? (array_search($student_score, $scores) + 1) : '-';
            $positions[$subject] = $position > 0 ? $position : '-';
        }

        // Calculate overall position in class
        $totals = [];
        $students_q = mysqli_query($conn, "SELECT id FROM students WHERE class='" . mysqli_real_escape_string($conn, $student_class) . "'");
        while ($stu = mysqli_fetch_assoc($students_q)) {
            $sid = $stu['id'];
            $sum = 0;
            $count = 0;
            $latest_q = mysqli_query($conn, "
                SELECT final_total
                FROM assessments
                WHERE student_id=$sid
                " . ($selected_academic_year ? "AND academic_year='" . mysqli_real_escape_string($conn, $selected_academic_year) . "'" : "") . "
                " . ($selected_term ? "AND term='" . mysqli_real_escape_string($conn, $selected_term) . "'" : "") . "
            ");
            while ($row = mysqli_fetch_assoc($latest_q)) {
                $sum += (float)$row['final_total'];
                $count++;
            }
            if ($count > 0) {
                $totals[$sid] = $sum;
            }
        }
        arsort($totals);
        $total_students = count($totals);
        $last_score = null;
        $rank = 0;
        $same = 1;
        $student_positions = [];
        foreach ($totals as $sid => $score) {
            if ($score !== $last_score) {
                $rank += $same;
                $same = 1;
            } else {
                $same++;
            }
            $student_positions[$sid] = $rank;
            $last_score = $score;
        }
        $overall_position = isset($student_positions[$student_id]) ? $student_positions[$student_id] : '-';

        // Fetch total attendance for the student and total days attendance was taken
        $attendance_total = '-';
        $attendance_days = '-';
        $att_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM attendance WHERE student_id=$student_id AND status='Present'");
        if ($att_row = mysqli_fetch_assoc($att_q)) {
            $attendance_total = $att_row['total'];
        }
        $days_q = mysqli_query($conn, "SELECT COUNT(DISTINCT date) as days FROM attendance");
        if ($days_row = mysqli_fetch_assoc($days_q)) {
            $attendance_days = $days_row['days'];
        }

    } else {
        $error = "No student found with that Admission Number and Class.";
    }
}

// Fetch all classes for dropdown
$class_options = [];
$class_query = mysqli_query($conn, "SELECT DISTINCT class FROM students ORDER BY class ASC");
while ($row = mysqli_fetch_assoc($class_query)) {
    $class_options[] = $row['class'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Parent Portal - Check Ward Results</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #f7f7fa; }
        .container { max-width: 700px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #ddd; padding: 30px; }
        .btn-main { background: #0e7490; color: #fff; border: none; }
        .btn-main:hover { background: #155e75; }
        .form-section { margin-bottom: 30px; }
        .error-msg { color: #e11d48; margin-bottom: 15px; }
        .student-info { background: #f1f5f9; border-radius: 8px; padding: 10px 18px; margin-bottom: 18px; font-size: 1.1em; }
        .student-info strong { color: #0e7490; }
        th, td { text-align: center; }
        thead th { background: #0e7490; color: #fff; }
        .school-header {
            text-align:center;
            margin-bottom:18px;
            padding:24px 10px 10px 10px;
        }
        .school-header h2 { margin:0; color:#0e7490; font-weight:bold; }
        .school-header h4 { margin:0; color:#555; }
        .footer-section {
            margin-top: 32px;
            font-size: 1.05em;
            background: #f1f5f9;
            padding: 14px 18px;
            border-radius: 8px;
        }
        .footer-section label { font-weight: normal; margin-right: 10px; }
        .footer-section input[type="text"], .footer-section select { border: 1px solid #ccc; border-radius: 4px; padding: 2px 8px; width: 100%; max-width: 300px; }
        .footer-row {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 10px;
        }
        .footer-row > div {
            flex: 1 1 200px;
            min-width: 180px;
        }
        @media (max-width: 700px) {
            .container { padding: 10px; }
            .footer-row { flex-direction: column; gap: 8px; }
            .footer-section input[type="text"], .footer-section select { max-width: 100%; }
        }
    </style>
    <script>
        function showForm() {
            document.getElementById('check-form').style.display = 'block';
            document.getElementById('start-btn').style.display = 'none';
        }
        function submitAcademicForm() {
            document.getElementById('academic-form').submit();
        }
        function printDiv(divId) {
            var printContents = document.getElementById(divId).innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        }
    </script>
</head>
<body>
<div class="container">
    <div class="school-header">
        <img src="logo.png" alt="School Logo" style="height:60px;margin-bottom:8px;">
        <h2>GHANA EDUCATION SERVICE</h2>
        <h4>BASIC SCHOOL INFORMATION MANAGEMENT SYSTEM</h4>
        <h4 style="color:#0e7490;font-weight:bold;">STUDENT REPORT CARD</h4>
    </div>
    <?php if ($show_form): ?>
        <div class="form-section text-center">
            <button id="start-btn" class="btn btn-main btn-lg" onclick="showForm()">Click here to check your ward results</button>
            <form id="check-form" method="post" style="display:none; margin-top:20px;">
                <?php if ($error): ?><div class="error-msg"><?php echo $error; ?></div><?php endif; ?>
                <div class="form-group">
                    <label>Admission Number:</label>
                    <input type="text" name="admission_no" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Class:</label>
                    <select name="student_class" class="form-control" required>
                        <option value="">Select Class</option>
                        <?php foreach ($class_options as $class): ?>
                            <option value="<?php echo htmlspecialchars($class); ?>"><?php echo htmlspecialchars($class); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-main">Check Result</button>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($show_results && $student): ?>
        <form method="post" id="academic-form" style="margin-bottom:18px;">
            <input type="hidden" name="admission_no" value="<?php echo htmlspecialchars($student['admission_no']); ?>">
            <input type="hidden" name="student_class" value="<?php echo htmlspecialchars($student['class']); ?>">
            <div class="row" style="margin-bottom:18px;">
                <div class="col-xs-6">
                    <label>Academic Year:</label>
                    <select name="academic_year" class="form-control" onchange="submitAcademicForm()">
                        <?php foreach ($academic_year_options as $year): ?>
                            <option value="<?php echo htmlspecialchars($year); ?>" <?php if($selected_academic_year==$year) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($year); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-xs-6">
                    <label>Term:</label>
                    <select name="term" class="form-control" onchange="submitAcademicForm()">
                        <?php foreach ($term_options as $term): ?>
                            <option value="<?php echo htmlspecialchars($term); ?>" <?php if($selected_term==$term) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($term); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>
        <div id="print-area">
            <div class="student-info">
                <strong>Name:</strong> <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['other_names']); ?> &nbsp;
                <strong>Admission No:</strong> <?php echo htmlspecialchars($student['admission_no']); ?> &nbsp;
                <strong>Class:</strong> <?php echo htmlspecialchars($student['class']); ?>
            </div>
            <div style="overflow-x:auto;">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Class 60<br><span style="font-size:0.9em;">(Scaled 50)</span></th>
                        <th>Exam 100<br><span style="font-size:0.9em;">(Scaled 50)</span></th>
                        <th>Final Total<br>(/100)</th>
                        <th>Grade</th>
                        <th>Position</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($subjects) > 0): ?>
                        <?php foreach ($subjects as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                <td><?php echo htmlspecialchars($row['scaled_50']); ?></td>
                                <td><?php echo htmlspecialchars($row['exam_scaled_50']); ?></td>
                                <td><?php echo htmlspecialchars($row['final_total']); ?></td>
                                <td><?php echo htmlspecialchars($row['grade']); ?></td>
                                <td><?php echo isset($positions[$row['subject']]) ? $positions[$row['subject']] : '-'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;">No assessment records found for this student.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
            <div class="text-right" style="margin-top:10px;">
                <strong>Overall Position:</strong> <?php echo $overall_position; ?> &nbsp;
                <strong>No. on Roll:</strong> <?php echo $total_students; ?>
            </div>
            <div class="footer-section">
                <div class="footer-row">
                    <div>
                        <label>Attendance:</label>
                        <input type="text" name="attendance" value="<?php echo htmlspecialchars($attendance_total) . ' Out of ' . htmlspecialchars($attendance_days); ?>" readonly>
                    </div>
                    <div>
                        <label>Conduct:</label>
                        <input type="text" name="conduct" value="" readonly>
                    </div>
                    <div>
                        <label>Interest:</label>
                        <input type="text" name="interest" value="" readonly>
                    </div>
                </div>
                <div class="footer-row">
                    <div>
                        <label>Teacher’s Remark:</label>
                        <input type="text" name="teacher_remark" value="" readonly>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center" style="margin-top:20px;">
            <button class="btn btn-success" onclick="printDiv('print-area')"><i class="fa fa-print"></i> Print Report Card</button>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
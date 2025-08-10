<?php


session_start();
$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Get student_id from GET or POST
$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

// Fetch student info
$student = ['first_name'=>'', 'other_names'=>'', 'class'=>''];
$academic_year = '';
$term = '';
if ($student_id) {
    $q = mysqli_query($conn, "SELECT first_name, other_names, class FROM students WHERE id=$student_id LIMIT 1");
    if ($row = mysqli_fetch_assoc($q)) $student = $row;

    // Fetch latest academic year and term for this student
    $acad_q = mysqli_query($conn, "SELECT academic_year, term FROM assessments WHERE student_id=$student_id ORDER BY academic_year DESC, term DESC LIMIT 1");
    if ($row = mysqli_fetch_assoc($acad_q)) {
        $academic_year = $row['academic_year'];
        $term = $row['term'];
    }
}

// Fetch all assessments for this student (all subjects, all years/terms)
$subjects = [];
if ($student_id) {
    $assess_q = mysqli_query($conn, "SELECT subject, scaled_50, exam_scaled_50, final_total, grade FROM assessments WHERE student_id=$student_id ORDER BY subject ASC");
    while ($row = mysqli_fetch_assoc($assess_q)) {
        $subjects[] = [
            'subject' => $row['subject'],
            'scaled_50' => $row['scaled_50'],
            'exam_scaled_50' => $row['exam_scaled_50'],
            'final_total' => $row['final_total'],
            'grade' => $row['grade']
        ];
    }
}

// Fetch all class options for promoted to dropdown
$class_options = [];
$class_query = mysqli_query($conn, "SELECT DISTINCT class FROM students ORDER BY class ASC");
while ($row = mysqli_fetch_assoc($class_query)) {
    $class_options[] = $row['class'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Report Card</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
        }
        body {
            min-height: 100vh;
        }
        .report-card-container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px #bbb;
            padding: 0;
            width: 100%;
        }
        .school-header {
            text-align: center;
            margin-bottom: 18px;
            padding: 24px 10px 10px 10px;
        }
        .school-header h2 { margin: 0; color: #0e7490; font-weight: bold; }
        .school-header h4 { margin: 0; color: #555; }
        .student-info {
            margin-bottom: 18px;
            font-size: 1.1em;
            background: #f1f5f9;
            padding: 12px 18px;
            border-radius: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            align-items: center;
        }
        .student-info strong { color: #0e7490; }
        .student-info .info-block { margin-right: 24px; }
        .report-table th, .report-table td {
            text-align: center;
            vertical-align: middle !important;
        }
        .report-table th {
            background: #0e7490;
            color: #fff;
            font-size: 1.05em;
        }
        .report-table tr:nth-child(even) { background: #f8fafc; }
        .report-table tr:nth-child(odd) { background: #fff; }
        .grade-A { color: #22c55e; font-weight: regular; }
        .grade-B { color: #38bdf8; font-weight: regular; }
        .grade-C { color: #eab308; font-weight: regular; }
        .grade-D, .grade-E, .grade-F { color: #e11d48; font-weight: regular; }
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
        .print-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 24px auto 0 auto;
            font-size: 1.2em;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #22c55e;
            color: #fff;
            border: none;
            box-shadow: 0 2px 8px #bbb;
            transition: background 0.2s;
        }
        .print-btn:hover, .print-btn:focus {
            background: #16a34a;
            color: #fff;
            outline: none;
        }
        @media (max-width: 1000px) {
            .report-card-container {
                max-width: 99vw;
                padding: 0;
            }
            .school-header, .student-info, .footer-section {
                padding-left: 2vw;
                padding-right: 2vw;
            }
        }
        @media (max-width: 700px) {
            .report-card-container {
                padding: 0;
            }
            .school-header, .student-info, .footer-section {
                padding-left: 1vw;
                padding-right: 1vw;
            }
            .footer-row {
                flex-direction: column;
                gap: 8px;
            }
            .footer-section input[type="text"], .footer-section select {
                max-width: 100%;
            }
            .student-info { flex-direction: column; gap: 8px; }
        }
        @media (max-width: 500px) {
            .school-header img { height: 40px !important; }
            .school-header h2 { font-size: 1.2em; }
            .school-header h4 { font-size: 1em; }
            .student-info { font-size: 1em; }
            .report-table th, .report-table td { font-size: 12px; }
            .print-btn { width: 44px; height: 44px; font-size: 1em; }
        }
    </style>
    <script>
        function printReportCard() {
            window.print();
        }
    </script>
</head>
<body>
<div class="report-card-container">
    <div class="school-header">
        <img src="logo.png" alt="School Logo" style="height:60px;margin-bottom:8px;">
        <h2>GHANA EDUCATION SERVICE</h2>
        <h4>BASIC SCHOOL INFORMATION MANAGEMENT SYSTEM</h4>
        <h4 style="color:#0e7490;font-weight:bold;">STUDENT REPORT CARD</h4>
    </div>
    <div class="student-info">
        <div class="info-block"><strong>Name:</strong> <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['other_names']); ?></div>
        <div class="info-block"><strong>Class:</strong> <?php echo htmlspecialchars($student['class']); ?></div>
        <?php if($academic_year): ?>
            <div class="info-block"><strong>Academic Year:</strong> <?php echo htmlspecialchars($academic_year); ?></div>
        <?php endif; ?>
        <?php if($term): ?>
            <div class="info-block"><strong>Term:</strong> <?php echo htmlspecialchars($term); ?></div>
        <?php endif; ?>
    </div>
    <div style="overflow-x:auto;">
    <table class="table table-bordered report-table" style="margin-bottom:0;">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Class 60<br><span style="font-size:0.9em;">(Scaled 50)</span></th>
                <th>Exam 100<br><span style="font-size:0.9em;">(Scaled 50)</span></th>
                <th>Final Total<br>(/100)</th>
                <th>Grade</th>
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
                    <td class="grade-<?php echo strtoupper(substr($row['grade'],0,1)); ?>">
                        <?php echo htmlspecialchars($row['grade']); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center;">No assessment records found for this student.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
    <div class="footer-section">
        <div class="footer-row">
            <div>
                <label>Promoted To:</label>
                <select name="promoted_to">
                    <option value="">Select Class</option>
                    <?php foreach($class_options as $class): ?>
                        <option value="<?php echo htmlspecialchars($class); ?>"><?php echo htmlspecialchars($class); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Interest:</label>
                <input type="text" name="interest">
            </div>
        </div>
        <div class="footer-row">
            <div>
                <label>Conduct:</label>
                <input type="text" name="conduct">
            </div>
        </div>
        <div class="footer-row">
            <div>
                <label>Teacher’s Remark:</label>
                <input type="text" name="teacher_remark">
            </div>
        </div>
    </div>
    <button class="print-btn" onclick="printReportCard()" title="Print Report Card">
        <i class="fa fa-print"></i>
    </button>
</div>
</body>
</html>
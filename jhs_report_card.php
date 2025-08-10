<?php
// filepath: c:\xampp\htdocs\bsims\jhs_report_card.php

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
} elseif ($_SESSION['username'] == 'student') {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Get student_id and class from URL
$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$class = isset($_GET['class']) ? $_GET['class'] : '';

// Fetch student info
$student_name = '';
$student_class = '';
if ($student_id) {
    $student_q = mysqli_query($conn, "SELECT first_name, other_names, class FROM students WHERE id=$student_id LIMIT 1");
    if ($row = mysqli_fetch_assoc($student_q)) {
        $student_name = $row['first_name'] . ' ' . $row['other_names'];
        $student_class = $row['class'];
    }
}

// Fetch active academic year and term
$year = '';
$term = '';
$date = '';
$next_term_date = '';
$session_q = mysqli_query($conn, "SELECT academic_year, term, start_date, stop_date FROM academic_sessions WHERE is_active=1 LIMIT 1");
if ($row = mysqli_fetch_assoc($session_q)) {
    $year = $row['academic_year'];
    $term = $row['term'];
    $date = $row['stop_date'] ? date('jS F Y', strtotime($row['stop_date'])) : date('jS F Y');
}

// Fetch next term begin date (next session for same year)
$next_q = mysqli_query($conn, "SELECT start_date FROM academic_sessions WHERE academic_year='$year' AND id > (SELECT id FROM academic_sessions WHERE is_active=1 LIMIT 1) ORDER BY id ASC LIMIT 1");
if ($row = mysqli_fetch_assoc($next_q)) {
    $next_term_date = $row['start_date'] ? date('jS F Y', strtotime($row['start_date'])) : '';
}

// Get number on roll (total students in class)
$no_on_roll = 0;
if ($class) {
    $count_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM students WHERE class='" . mysqli_real_escape_string($conn, $class) . "'");
    if ($row = mysqli_fetch_assoc($count_q)) {
        $no_on_roll = $row['total'];
    }
}

// Fetch assessments for student
$subjects = [
    "ENGLISH LANGUAGE", "MATHEMATICS", "SCIENCE", "RME", "CREATIVE ARTS",
    "SOCIAL STUDIES", "GHANAIAN LANGUAGE", "FRENCH", "CAREER TECHNOLOGY", "COMPUTING"
];
$assessments = [];
foreach ($subjects as $subject) {
    $assess_q = mysqli_query($conn, "SELECT scaled_50, exam_scaled_50, final_total, grade FROM assessments WHERE student_id=$student_id AND class='" . mysqli_real_escape_string($conn, $class) . "' AND subject='" . mysqli_real_escape_string($conn, $subject) . "' LIMIT 1");
    if ($row = mysqli_fetch_assoc($assess_q)) {
        $assessments[$subject] = $row;
    } else {
        $assessments[$subject] = ['scaled_50'=>'', 'exam_scaled_50'=>'', 'final_total'=>'', 'grade'=>''];
    }
}

// Calculate aggregate (sum of total scores)
$aggregate = 0;
foreach ($assessments as $a) {
    $aggregate += is_numeric($a['final_total']) ? $a['final_total'] : 0;
}

// Fetch attendance for student in active term/year
$attendance_present = 0;
$attendance_total = 0;
if ($student_id && $class && $term && $year) {
    $present_q = mysqli_query($conn, "SELECT COUNT(*) as present FROM attendance WHERE student_id=$student_id AND status='present' AND term='$term' AND academic_year='$year'");
    if ($row = mysqli_fetch_assoc($present_q)) {
        $attendance_present = $row['present'];
    }
    $days_q = mysqli_query($conn, "SELECT COUNT(DISTINCT date) as total_days FROM attendance WHERE class='" . mysqli_real_escape_string($conn, $class) . "' AND term='$term' AND academic_year='$year'");
    if ($row = mysqli_fetch_assoc($days_q)) {
        $attendance_total = $row['total_days'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>JHS Terminal Report</title>
  <link rel="stylesheet" href="report_card.css">
</head>
<body>
  <div class="report">
    <header>
      <img src="logo.png" alt="School Logo" class="logo">
      <h1>GHANA EDUCATION SERVICE</h1>
      <h2>BASIC SCHOOL INFORMATION MANAGEMENT SYSTEM</h2>
      <p>THE INVINCIBLE TEAM</p>
      <p>TEL: 0530770774 WHATSAPP: 0246049420</p>
      <div class="title-bar">JHS TERMINAL REPORT</div>
    </header>

    <section class="details">
      <p><strong>NAME :</strong> <?php echo htmlspecialchars($student_name); ?> <strong>YEAR:</strong> <?php echo htmlspecialchars($year); ?></p>
      <p><strong>CLASS:</strong> <?php echo htmlspecialchars($student_class); ?> <strong>TERM:</strong> <?php echo htmlspecialchars($term); ?> </p>
      <p><strong>NO. ON ROLL:</strong> <?php echo htmlspecialchars($no_on_roll); ?> <strong>AGGREGATE:</strong> <?php echo htmlspecialchars($aggregate); ?></p>
      <p><strong>DATE:</strong> <?php echo htmlspecialchars($date); ?>  <strong>NEXT TERM BEGINS:</strong> <?php echo htmlspecialchars($next_term_date); ?></p>
    </section>

    <table>
      <thead>
        <tr>
          <th>SUBJECT</th>
          <th>CLASS SCORE<br>50%</th>
          <th>EXAMS SCORE<br>50%</th>
          <th>TOTAL SCORE<br>100%</th>
          <th>GRADE IN SUBJECT</th>
          <th>REMARKS & AREAS OF STRENGTHS<br>AND WEAKNESS</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $remarks = [
            "ENGLISH LANGUAGE" => "90-100 Grade 1 = Highest",
            "MATHEMATICS" => "80-89 Grade 2 = Higher",
            "SCIENCE" => "70-79 Grade 3 = High",
            "RME" => "60-69 Grade 4 = High Average",
            "CREATIVE ARTS" => "55-59 Grade 5 = Average",
            "SOCIAL STUDIES" => "50-54 Grade 6 = Low Average",
            "GHANAIAN LANGUAGE" => "40-49 Grade 7 = Low",
            "FRENCH" => "35-39 Grade 8 = Lower",
            "CAREER TECHNOLOGY" => "0-39 Grade 9 = Lowest",
            "COMPUTING" => ""
        ];
        foreach ($subjects as $subject): ?>
        <tr>
          <td><?php echo htmlspecialchars($subject); ?></td>
          <td><?php echo htmlspecialchars($assessments[$subject]['scaled_50']); ?></td>
          <td><?php echo htmlspecialchars($assessments[$subject]['exam_scaled_50']); ?></td>
          <td><?php echo htmlspecialchars($assessments[$subject]['final_total']); ?></td>
          <td><?php echo htmlspecialchars($assessments[$subject]['grade']); ?></td>
          <td><?php echo $remarks[$subject]; ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <section class="footer-section">
      <p>Attendance: <?php echo $attendance_present . ' Out Of ' . $attendance_total; ?> &nbsp; Promoted To: <input type="text" name="promoted_to" style="width:120px;"></p>
      <p>Interest: <input type="text" name="interest" style="width:180px;"> Repeated in: <input type="text" name="repeated_in" style="width:120px;"></p>
      <p>Conduct: <input type="text" name="conduct" style="width:300px;"></p>
      <p>Class Teacher’s Remarks: <input type="text" name="teacher_remark" style="width:400px;"></p>
    </section>

    <div class="footer-right">
      <div class="recommended-books">
        <p style="margin-bottom: 5px;">Recommended books for further reading:</p>
        <ul>
          <li>Mathematics Made Easy</li>
          <li>Understanding Science</li>
          <li>Creative Arts by Kwame</li>
          <li>Social Studies: A Comprehensive Guide</li>
        </ul>
      </div>
    </div>
  </div>
</body>
</html>
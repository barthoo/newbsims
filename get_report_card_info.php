<?php
// filepath: c:\xampp\htdocs\bsims\get_report_card_info.php

// This file should be used as a PHP page to display student info for the report card.
// It fetches the student info from the database using the student_id and class passed in the URL.

$conn = mysqli_connect("localhost", "root", "", "bsmsdb");
if (!$conn) {
    die("Database connection failed");
}

$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
$class = isset($_GET['class']) ? $_GET['class'] : '';

// Get active academic year
$year = '';
$term = '';
$year_q = mysqli_query($conn, "SELECT academic_year, term FROM academic_sessions WHERE is_active=1 LIMIT 1");
if ($row = mysqli_fetch_assoc($year_q)) {
    $year = $row['academic_year'];
    $term = $row['term'];
}

// Get number on roll (total students in class)
$no_on_roll = 0;
if ($class) {
    $count_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM students WHERE class='" . mysqli_real_escape_string($conn, $class) . "'");
    if ($row = mysqli_fetch_assoc($count_q)) {
        $no_on_roll = $row['total'];
    }
}

// Get student info
$student_name = '';
$student_class = '';
if ($student_id) {
    $student_q = mysqli_query($conn, "SELECT first_name, other_names, class FROM students WHERE id=$student_id LIMIT 1");
    if ($row = mysqli_fetch_assoc($student_q)) {
        $student_name = $row['first_name'] . ' ' . $row['other_names'];
        $student_class = $row['class'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Report Card Info</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container" style="max-width:600px;margin:40px auto;background:#fff;padding:30px;border-radius:8px;">
    <h3 class="text-center">Student Information</h3>
    <table class="table table-bordered" style="margin-bottom:30px;">
        <tr>
            <th>Name</th>
            <td><?php echo htmlspecialchars($student_name); ?></td>
        </tr>
        <tr>
            <th>Class</th>
            <td><?php echo htmlspecialchars($student_class); ?></td>
        </tr>
        <tr>
            <th>No. on Roll</th>
            <td><?php echo htmlspecialchars($no_on_roll); ?></td>
        </tr>
        <tr>
            <th>Academic Year</th>
            <td><?php echo htmlspecialchars($year); ?></td>
        </tr>
        <tr>
            <th>Term</th>
            <td><?php echo htmlspecialchars($term); ?></td>
        </tr>
    </table>
</div>
</body>
</html>
<?php
// filepath: c:\xampp\htdocs\bsims\student_report.php

session_start();
$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Fetch all students
$students = [];
$student_q = mysqli_query($conn, "SELECT id, first_name, other_names, class, admission_no FROM students ORDER BY class, first_name");
while ($row = mysqli_fetch_assoc($student_q)) {
    $students[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <style>
        body { background: #f7f7fa; }
        .container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #ddd; padding: 30px; }
        table { width: 100%; }
        th, td { text-align: center; }
        .pointer { cursor: pointer; color: #0e7490; text-decoration: none; }
        .pointer:hover { color: #38bdf8; text-decoration: underline; }
    </style>
    <script>
    function openReportCard(studentId, studentClass) {
        // You can add logic here to choose the correct report card page based on class
        var page = 'jhs_report_card.php'; // Change as needed for other classes
        var url = page + '?student_id=' + encodeURIComponent(studentId) + '&class=' + encodeURIComponent(studentClass);
        window.open(url, '_blank');
    }
    </script>
</head>
<body>
<div class="container">
    <h3 class="text-center">Student Report List</h3>
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Admission No.</th>
                <th>Name</th>
                <th>Class</th>
            </tr>
        </thead>
        <tbody>
            <?php $i=1; foreach ($students as $student): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars($student['admission_no']); ?></td>
                <td>
                    <a href="#"
                       class="pointer"
                       onclick="openReportCard('<?php echo $student['id']; ?>', '<?php echo addslashes($student['class']); ?>'); return false;">
                        <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['other_names']); ?>
                    </a>
                </td>
                <td><?php echo htmlspecialchars($student['class']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
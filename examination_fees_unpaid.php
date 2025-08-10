<?php

$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Get all students
$students = [];
$res = mysqli_query($conn, "SELECT admission_no, first_name, other_names, class FROM students ORDER BY class, first_name, other_names");
while ($row = mysqli_fetch_assoc($res)) {
    $students[$row['admission_no']] = $row;
}

// Get all students who have paid examination fees
$paid_ids = [];
$paid_res = mysqli_query($conn, "SELECT DISTINCT student_id FROM examination_fees");
while ($row = mysqli_fetch_assoc($paid_res)) {
    $paid_ids[] = $row['student_id'];
}

// Filter unpaid students
$unpaid_students = [];
foreach ($students as $admission_no => $stu) {
    if (!in_array($admission_no, $paid_ids)) {
        $unpaid_students[] = $stu;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Unpaid Examination Fees Students</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #f8fafc; }
        .main-container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 12px #ddd; padding: 30px 24px; }
        h2 { color: #dc2626; font-weight: 700; margin-bottom: 24px; }
        .table th { background: #dc2626; color: #fff; }
        .btn-back { margin-bottom: 18px; }
        @media (max-width: 700px) {
            .main-container { padding: 10px 2vw; }
        }
    </style>
</head>
<body>
<div class="main-container">
    <h2><i class="fa fa-times-circle"></i> Unpaid Examination Fees Students</h2>
    <a href="examination_fees.php" class="btn btn-default btn-back"><i class="fa fa-arrow-left"></i> Back</a>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Admission No</th>
                    <th>Name</th>
                    <th>Class</th>
                </tr>
            </thead>
            <tbody>
            <?php $sn = 1; ?>
            <?php foreach ($unpaid_students as $stu): ?>
                <tr>
                    <td><?php echo $sn++; ?></td>
                    <td><?php echo htmlspecialchars($stu['admission_no']); ?></td>
                    <td><?php echo htmlspecialchars($stu['first_name'] . ' ' . $stu['other_names']); ?></td>
                    <td><?php echo htmlspecialchars($stu['class']); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($sn === 1): ?>
                <tr><td colspan="4" class="text-center text-muted">All students have paid.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
<?php

$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Get all students who have paid examination fees (distinct by student_id)
$query = "
    SELECT s.admission_no, s.first_name, s.other_names, s.class, e.amount, e.description, e.date_paid
    FROM examination_fees e
    LEFT JOIN students s ON e.student_id = s.admission_no
    ORDER BY s.class, s.first_name, s.other_names
";
$res = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Paid Examination Fees Students</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #f8fafc; }
        .main-container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 12px #ddd; padding: 30px 24px; }
        h2 { color: #f59e42; font-weight: 700; margin-bottom: 24px; }
        .table th { background: #f59e42; color: #fff; }
        .btn-back { margin-bottom: 18px; }
        @media (max-width: 700px) {
            .main-container { padding: 10px 2vw; }
        }
    </style>
</head>
<body>
<div class="main-container">
    <h2><i class="fa fa-check-circle"></i> Paid Examination Fees Students</h2>
    <a href="examination_fees.php" class="btn btn-default btn-back"><i class="fa fa-arrow-left"></i> Back</a>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Admission No</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Amount (GHC)</th>
                    <th>Description</th>
                    <th>Date Paid</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $sn = 1;
            $seen = [];
            while ($row = mysqli_fetch_assoc($res)):
                // Only show each student once (latest payment)
                if (in_array($row['admission_no'], $seen)) continue;
                $seen[] = $row['admission_no'];
            ?>
                <tr>
                    <td><?php echo $sn++; ?></td>
                    <td><?php echo htmlspecialchars($row['admission_no']); ?></td>
                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['other_names']); ?></td>
                    <td><?php echo htmlspecialchars($row['class']); ?></td>
                    <td><?php echo number_format($row['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td><?php echo htmlspecialchars($row['date_paid']); ?></td>
                </tr>
            <?php endwhile; ?>
            <?php if ($sn === 1): ?>
                <tr><td colspan="7" class="text-center text-muted">No paid students found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
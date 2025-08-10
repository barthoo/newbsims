<<?php


$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Fetch all classes for filter
$class_options = [];
$class_res = mysqli_query($conn, "SELECT DISTINCT class FROM students ORDER BY class ASC");
while ($row = mysqli_fetch_assoc($class_res)) {
    if ($row['class']) $class_options[] = $row['class'];
}

// Get filter values
$filter_class = isset($_GET['filter_class']) ? mysqli_real_escape_string($conn, $_GET['filter_class']) : '';
$filter_name = isset($_GET['filter_name']) ? trim($_GET['filter_name']) : '';

// Get all students who have paid PTA levy (distinct by student_id)
$query = "
    SELECT s.admission_no, s.first_name, s.other_names, s.class, p.amount, p.description, p.date_paid
    FROM pta_levy p
    LEFT JOIN students s ON p.student_id = s.admission_no
";
$conditions = [];
if ($filter_class) {
    $conditions[] = "s.class = '$filter_class'";
}
if ($filter_name) {
    $filter_name_sql = mysqli_real_escape_string($conn, $filter_name);
    $conditions[] = "(s.first_name LIKE '%$filter_name_sql%' OR s.other_names LIKE '%$filter_name_sql%')";
}
if ($conditions) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}
$query .= " ORDER BY s.class, s.first_name, s.other_names";
$res = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Paid PTA Levy Students</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #f8fafc; }
        .main-container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 12px #ddd; padding: 30px 24px; }
        h2 { color: #16a34a; font-weight: 700; margin-bottom: 24px; }
        .table th { background: #16a34a; color: #fff; }
        .btn-back { margin-bottom: 18px; }
        .filter-form { margin-bottom: 18px; display: flex; gap: 10px; align-items: center; }
        .filter-form select, .filter-form input { min-width: 120px; }
        @media (max-width: 700px) {
            .main-container { padding: 10px 2vw; }
            .filter-form { flex-direction: column; gap: 6px; }
        }
    </style>
</head>
<body>
<div class="main-container">
    <h2><i class="fa fa-check-circle"></i> Paid PTA Levy Students</h2>
    <a href="pta_levy.php" class="btn btn-default btn-back"><i class="fa fa-arrow-left"></i> Back</a>
    <form class="filter-form" method="get" action="pta_levy_paid.php">
        <label for="filter_class">Class:</label>
        <select name="filter_class" id="filter_class" class="form-control input-sm" onchange="this.form.submit()">
            <option value="">All Classes</option>
            <?php foreach ($class_options as $class): ?>
                <option value="<?php echo htmlspecialchars($class); ?>" <?php if ($filter_class == $class) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($class); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label for="filter_name">Name:</label>
        <input type="text" name="filter_name" id="filter_name" class="form-control input-sm"
               value="<?php echo htmlspecialchars($filter_name); ?>" placeholder="Enter name"
               onkeydown="if(event.key==='Enter'){this.form.submit();}">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> Filter</button>
        <?php if ($filter_class || $filter_name): ?>
            <a href="pta_levy_paid.php" class="btn btn-default btn-xs">Clear</a>
        <?php endif; ?>
    </form>
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
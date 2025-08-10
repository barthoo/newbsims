<?php

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

// Get all students
$students = [];
$res = mysqli_query($conn, "SELECT admission_no, first_name, other_names, class FROM students ORDER BY class, first_name, other_names");
while ($row = mysqli_fetch_assoc($res)) {
    $students[$row['admission_no']] = $row;
}

// Get all students who have paid tuition fees
$paid_ids = [];
$paid_res = mysqli_query($conn, "SELECT DISTINCT student_id FROM tuition_fees");
while ($row = mysqli_fetch_assoc($paid_res)) {
    $paid_ids[] = $row['student_id'];
}

// Filter unpaid students
$unpaid_students = [];
foreach ($students as $admission_no => $stu) {
    if (!in_array($admission_no, $paid_ids)) {
        // Filter by class
        if ($filter_class && $stu['class'] !== $filter_class) continue;
        // Filter by name (first or other names, case-insensitive)
        $full_name = strtolower($stu['first_name'] . ' ' . $stu['other_names']);
        if ($filter_name && strpos($full_name, strtolower($filter_name)) === false) continue;
        $unpaid_students[] = $stu;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Unpaid Tuition Fees Students</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .filter-form { margin-bottom: 18px; display: flex; gap: 10px; align-items: center; }
        .filter-form select, .filter-form input { min-width: 120px; }
        @media (max-width: 700px) {
            .filter-form { flex-direction: column; gap: 6px; }
        }
    </style>
</head>
<body>
<div class="container" style="margin-top:40px;">
    <h2><i class="fa fa-times-circle"></i> Unpaid Tuition Fees Students</h2>
    <a href="tuition_fees.php" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back</a>
    <form class="filter-form" method="get" action="unpaid_tution_fees.php">
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
            <a href="unpaid_tution_fees.php" class="btn btn-default btn-xs">Clear</a>
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
                <tr><td colspan="4" class="text-center text-muted">No unpaid students found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
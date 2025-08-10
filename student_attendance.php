<?php


session_start();
$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Fetch classes for dropdown
$class_options = [];
$class_query = mysqli_query($conn, "SELECT DISTINCT class FROM students ORDER BY class ASC");
while ($row = mysqli_fetch_assoc($class_query)) {
    $class_options[] = $row['class'];
}

// Fetch academic years and terms from academic_sessions
$academic_years = [];
$terms = [];
$year_query = mysqli_query($conn, "SELECT DISTINCT academic_year FROM academic_sessions ORDER BY academic_year DESC");
while ($row = mysqli_fetch_assoc($year_query)) {
    if (!empty($row['academic_year'])) $academic_years[] = $row['academic_year'];
}
$term_query = mysqli_query($conn, "SELECT DISTINCT term FROM academic_sessions ORDER BY term ASC");
while ($row = mysqli_fetch_assoc($term_query)) {
    if (!empty($row['term'])) $terms[] = $row['term'];
}

// Get selected filters
$selected_class = isset($_GET['class']) ? $_GET['class'] : '';
$selected_academic_year = isset($_GET['academic_year']) ? $_GET['academic_year'] : '';
$selected_term = isset($_GET['term']) ? $_GET['term'] : '';
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';
$view_all = isset($_GET['view_all']) ? true : false;
$attendance_data = [];

$where = [];
if ($selected_class) {
    $safe_class = mysqli_real_escape_string($conn, $selected_class);
    $where[] = "a.class = '$safe_class'";
}
if ($selected_academic_year) {
    $safe_academic_year = mysqli_real_escape_string($conn, $selected_academic_year);
    $where[] = "a.academic_year = '$safe_academic_year'";
}
if ($selected_term) {
    $safe_term = mysqli_real_escape_string($conn, $selected_term);
    $where[] = "a.term = '$safe_term'";
}
if ($from_date && $to_date) {
    $safe_from_date = mysqli_real_escape_string($conn, $from_date);
    $safe_to_date = mysqli_real_escape_string($conn, $to_date);
    $where[] = "a.date BETWEEN '$safe_from_date' AND '$safe_to_date'";
} elseif ($from_date) {
    $safe_from_date = mysqli_real_escape_string($conn, $from_date);
    $where[] = "a.date >= '$safe_from_date'";
} elseif ($to_date) {
    $safe_to_date = mysqli_real_escape_string($conn, $to_date);
    $where[] = "a.date <= '$safe_to_date'";
}
if ($view_all) {
    $where = [];
}

$where_sql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$query = mysqli_query($conn, "
    SELECT a.*, s.admission_no, s.first_name, s.other_names
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    $where_sql
    ORDER BY a.date DESC, s.first_name, s.other_names
");
while ($row = mysqli_fetch_assoc($query)) {
    $attendance_data[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Student Attendance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .main-container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #ddd; padding: 30px; }
        .header { background: #007bff; color: #fff; padding: 18px 30px; border-radius: 8px 8px 0 0; margin-bottom: 30px; }
        .header h2 { margin: 0; font-size: 2em; }
        .attendance-table th, .attendance-table td { vertical-align: middle !important; }
        .attendance-table { margin-bottom: 0; }
        @media (max-width: 900px) {
            .main-container { padding: 10px; }
        }
    </style>
</head>
<body>
<div class="header text-center">
    <h2><i class="fa fa-eye"></i> View Student Attendance</h2>
</div>
<div class="main-container">
    <form method="get" class="form-inline" style="margin-bottom:20px;">
        <label for="class" style="margin-right:10px;">Class:</label>
        <select name="class" id="class" class="form-control input-sm">
            <option value="">-- All Classes --</option>
            <?php foreach ($class_options as $class): ?>
                <option value="<?php echo htmlspecialchars($class); ?>" <?php if($selected_class==$class) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($class); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label for="academic_year" style="margin:0 10px 0 20px;">Academic Year:</label>
        <select name="academic_year" id="academic_year" class="form-control input-sm" style="width:140px;">
            <option value="">All Years</option>
            <?php foreach ($academic_years as $ay): ?>
                <option value="<?php echo htmlspecialchars($ay); ?>" <?php if($selected_academic_year==$ay) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($ay); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label for="term" style="margin:0 10px 0 20px;">Term:</label>
        <select name="term" id="term" class="form-control input-sm" style="width:120px;">
            <option value="">All Terms</option>
            <?php foreach ($terms as $t): ?>
                <option value="<?php echo htmlspecialchars($t); ?>" <?php if($selected_term==$t) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($t); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label for="from_date" style="margin:0 10px 0 20px;">From:</label>
        <input type="date" name="from_date" id="from_date" class="form-control input-sm" value="<?php echo htmlspecialchars($from_date); ?>" style="width:160px;display:inline-block;">
        <label for="to_date" style="margin:0 10px 0 20px;">To:</label>
        <input type="date" name="to_date" id="to_date" class="form-control input-sm" value="<?php echo htmlspecialchars($to_date); ?>" style="width:160px;display:inline-block;">
        <label style="margin-left:15px;">
            <input type="checkbox" name="view_all" value="1" <?php if($view_all) echo 'checked'; ?>> View All
        </label>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> Filter</button>
    </form>
    <div class="table-responsive">
        <table class="table table-bordered table-hover attendance-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Academic Year</th>
                    <th>Term</th>
                    <th>Class</th>
                    <th>Admission No.</th>
                    <th>Name</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($attendance_data) > 0): ?>
                <?php foreach ($attendance_data as $i => $row): ?>
                    <tr>
                        <td><?php echo $i+1; ?></td>
                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                        <td><?php echo htmlspecialchars($row['academic_year']); ?></td>
                        <td><?php echo htmlspecialchars($row['term']); ?></td>
                        <td><?php echo htmlspecialchars($row['class']); ?></td>
                        <td><?php echo htmlspecialchars($row['admission_no']); ?></td>
                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['other_names']); ?></td>
                        <td>
                            <?php
                                if ($row['status'] == 'Present') echo '<span class="label label-success">Present</span>';
                                elseif ($row['status'] == 'Absent') echo '<span class="label label-danger">Absent</span>';
                                else echo '<span class="label label-warning">' . htmlspecialchars($row['status']) . '</span>';
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" class="text-center">No attendance records found for the selected filter.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
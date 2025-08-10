<?php


$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Get filter values
$filter_class = isset($_GET['class']) ? $_GET['class'] : '';
$filter_subject = isset($_GET['subject']) ? $_GET['subject'] : '';
$filter_academic_year = isset($_GET['academic_year']) ? $_GET['academic_year'] : '';
$filter_term = isset($_GET['term']) ? $_GET['term'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$selected_student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

// Fetch class and subject options
$class_options = [];
$class_query = mysqli_query($conn, "SELECT DISTINCT class FROM students ORDER BY class ASC");
while ($row = mysqli_fetch_assoc($class_query)) {
    $class_options[] = $row['class'];
}
$subject_options = [];
$subject_query = mysqli_query($conn, "SELECT DISTINCT subject FROM assessments ORDER BY subject ASC");
while ($row = mysqli_fetch_assoc($subject_query)) {
    $subject_options[] = $row['subject'];
}
// Fetch academic year and term options
$academic_year_options = [];
$year_query = mysqli_query($conn, "SELECT DISTINCT academic_year FROM assessments ORDER BY academic_year DESC");
while ($row = mysqli_fetch_assoc($year_query)) {
    $academic_year_options[] = $row['academic_year'];
}
$term_options = [];
$term_query = mysqli_query($conn, "SELECT DISTINCT term FROM assessments ORDER BY term ASC");
while ($row = mysqli_fetch_assoc($term_query)) {
    $term_options[] = $row['term'];
}

// Build WHERE clause for filters
$where = [];
if ($selected_student_id) {
    $where[] = "a.student_id = $selected_student_id";
} else {
    if ($filter_class) {
        $where[] = "a.class = '" . mysqli_real_escape_string($conn, $filter_class) . "'";
    }
    if ($filter_subject) {
        $where[] = "a.subject = '" . mysqli_real_escape_string($conn, $filter_subject) . "'";
    }
    if ($filter_academic_year) {
        $where[] = "a.academic_year = '" . mysqli_real_escape_string($conn, $filter_academic_year) . "'";
    }
    if ($filter_term) {
        $where[] = "a.term = '" . mysqli_real_escape_string($conn, $filter_term) . "'";
    }
    if ($search) {
        $search_safe = mysqli_real_escape_string($conn, $search);
        $where[] = "(s.first_name LIKE '%$search_safe%' OR s.other_names LIKE '%$search_safe%' OR s.admission_no LIKE '%$search_safe%')";
    }
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Fetch all assessments with student info, including admission_no
// If a student is selected, only show latest assessment per subject (no duplicates)
if ($selected_student_id) {
    $query = "
        SELECT 
            a.id,
            a.student_id,
            s.first_name,
            s.other_names,
            s.class AS student_class,
            s.admission_no,
            a.subject,
            a.ind_test,
            a.group_work,
            a.class_work,
            a.project_work,
            a.total_60,
            a.scaled_50,
            a.exam,
            a.exam_scaled_50,
            a.final_total,
            a.grade
        FROM assessments a
        LEFT JOIN students s ON a.student_id = s.id
        INNER JOIN (
            SELECT subject, MAX(id) as max_id
            FROM assessments
            WHERE student_id = $selected_student_id
            " . ($filter_academic_year ? "AND academic_year = '" . mysqli_real_escape_string($conn, $filter_academic_year) . "'" : "") . "
            " . ($filter_term ? "AND term = '" . mysqli_real_escape_string($conn, $filter_term) . "'" : "") . "
            GROUP BY subject
        ) b ON a.id = b.max_id
        $where_sql
        ORDER BY a.subject
    ";
} else {
    $query = "
        SELECT 
            a.id,
            a.student_id,
            s.first_name,
            s.other_names,
            s.class AS student_class,
            s.admission_no,
            a.subject,
            a.ind_test,
            a.group_work,
            a.class_work,
            a.project_work,
            a.total_60,
            a.scaled_50,
            a.exam,
            a.exam_scaled_50,
            a.final_total,
            a.grade
        FROM assessments a
        LEFT JOIN students s ON a.student_id = s.id
        $where_sql
        ORDER BY a.class, s.first_name, s.other_names, a.subject
    ";
}
$result = mysqli_query($conn, $query);

// If a student is selected, fetch their info for a heading
$student_info = null;
if ($selected_student_id) {
    $stu_q = mysqli_query($conn, "SELECT first_name, other_names, class, admission_no FROM students WHERE id=$selected_student_id LIMIT 1");
    $student_info = mysqli_fetch_assoc($stu_q);

    // Fetch latest academic year and term for this student
    $acad_q = mysqli_query($conn, "SELECT academic_year, term FROM assessments WHERE student_id=$selected_student_id ORDER BY academic_year DESC, term DESC LIMIT 1");
    $acad_row = mysqli_fetch_assoc($acad_q);
    $academic_year = $acad_row ? $acad_row['academic_year'] : '';
    $term = $acad_row ? $acad_row['term'] : '';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Entered Student Assessments</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <style>
        body { background: #f7f7fa; }
        .container { max-width: 1200px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #ddd; padding: 30px; }
        th, td { font-size: 13px; text-align: center; }
        thead th { background: #0e7490; color: #fff; }
        .filter-form { 
            margin-bottom: 20px; 
            display: flex; 
            gap: 8px; 
            flex-wrap: nowrap; 
            align-items: center; 
            justify-content: flex-start;
        }
        .filter-form .form-control { min-width: 90px; max-width: 140px; padding: 4px 8px; font-size: 13px; }
        .filter-form .search-input { min-width: 120px; max-width: 180px; }
        .filter-form .btn { min-width: 80px; font-size: 13px; padding: 4px 12px; }
        .student-heading { background: #f1f5f9; border-radius: 8px; padding: 10px 18px; margin-bottom: 18px; font-size: 1.1em; }
        .student-heading strong { color: #0e7490; }
        .print-btn { float:right; margin-left:10px; }
        .no-underline { text-decoration: none !important; color: inherit !important; }
        .filter-label { font-weight: bold; margin-right: 4px; }
        @media (max-width: 900px) {
            .container { padding: 10px; }
            .filter-form { flex-direction: column; align-items: stretch; gap: 8px; }
        }
    </style>
</head>
<body>
    <!-- Place this at the top of your page -->
<link rel="stylesheet" href="admin-panel.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<header class="header">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle admin panel">
        <i class="fa fa-bars"></i>
    </button>
    <span class="dashboard-title"><i class="fa fa-gauge"></i> Administrator Dashboard</span>
    <div class="logout">
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</header>
<nav class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <i class="fa fa-user-shield"></i> Admin Panel
    </div>
    <ul>
        <li class="active"><a href="adminhome.php"><i class="fa fa-home"></i> Dashboard</a></li>
        <li><a href="academics.php"><i class="fa fa-book"></i> Academics</a></li>
        <li><a href="recommended_books.php"><i class="fa fa-book"></i> Recommended Books</a></li>
    </ul>
</nav>
<div class="container">
    <h3 class="text-center">Entered Student Assessments</h3>
    <form method="get" class="filter-form" <?php if($selected_student_id) echo 'style="pointer-events:none;opacity:0.7;"'; ?>>
        <select name="class" class="form-control" <?php if($selected_student_id) echo 'disabled'; ?>>
            <option value="">All Classes</option>
            <?php foreach ($class_options as $class): ?>
                <option value="<?php echo htmlspecialchars($class); ?>" <?php if($filter_class==$class) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($class); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="subject" class="form-control" <?php if($selected_student_id) echo 'disabled'; ?>>
            <option value="">All Subjects</option>
            <?php foreach ($subject_options as $subject): ?>
                <option value="<?php echo htmlspecialchars($subject); ?>" <?php if($filter_subject==$subject) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($subject); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <span class="filter-label">Academic Year:</span>
        <select name="academic_year" class="form-control" <?php if($selected_student_id) echo 'disabled'; ?>>
            <option value="">All Academic Years</option>
            <?php foreach ($academic_year_options as $year): ?>
                <option value="<?php echo htmlspecialchars($year); ?>" <?php if($filter_academic_year==$year) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($year); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <span class="filter-label">Term:</span>
        <select name="term" class="form-control" <?php if($selected_student_id) echo 'disabled'; ?>>
            <option value="">All Terms</option>
            <?php foreach ($term_options as $term_opt): ?>
                <option value="<?php echo htmlspecialchars($term_opt); ?>" <?php if($filter_term==$term_opt) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($term_opt); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="search" class="form-control search-input" placeholder="Search name/admission no." value="<?php echo htmlspecialchars($search); ?>" <?php if($selected_student_id) echo 'disabled'; ?>>
        <button type="submit" class="btn btn-primary" <?php if($selected_student_id) echo 'disabled'; ?>><i class="fa fa-search"></i> Filter</button>
        <?php if (($filter_class || $filter_subject || $filter_academic_year || $filter_term || $search) && !$selected_student_id): ?>
            <a href="view_student_assessment.php" class="btn btn-default">Reset</a>
        <?php endif; ?>
    </form>
    <?php if ($student_info): ?>
        <div class="student-heading">
            <strong>Admission No:</strong> <?php echo htmlspecialchars($student_info['admission_no']); ?> &nbsp;
            <strong>Name:</strong> <?php echo htmlspecialchars($student_info['first_name'] . ' ' . $student_info['other_names']); ?> &nbsp;
            <strong>Class:</strong> <?php echo htmlspecialchars($student_info['class']); ?> &nbsp;
            <?php if(!empty($academic_year)): ?>
                <strong>Academic Year:</strong> <?php echo htmlspecialchars($academic_year); ?> &nbsp;
            <?php endif; ?>
            <?php if(!empty($term)): ?>
                <strong>Term:</strong> <?php echo htmlspecialchars($term); ?> &nbsp;
            <?php endif; ?>
            <a href="view_student_assessment.php" class="btn btn-xs btn-default" style="float:right;">Show All Students</a>
            <a href="report_card.php?student_id=<?php echo $selected_student_id; ?>" class="btn btn-xs btn-success print-btn" target="_blank">
                <i class="fa fa-print"></i> Print Report Card
            </a>
            <a href="edit_report_card.php?student_id=<?php echo $selected_student_id; ?>" class="btn btn-xs btn-warning print-btn" target="_blank">
                <i class="fa fa-edit"></i> Edit Report Card
            </a>
        </div>
    <?php endif; ?>
    <div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <?php if(!$selected_student_id): ?>
                    <th>Admission No.</th>
                    <th>Name</th>
                <?php endif; ?>
                <th>Subject</th>
                <th>Individual Test</th>
                <th>Group Work</th>
                <th>Class Work</th>
                <th>Project Work</th>
                <th>Total (60)</th>
                <th>60 Scaled (50)</th>
                <th>Exam (100)</th>
                <th>Exam Scaled (50)</th>
                <th>Final Total</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            <?php $i=1; while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <?php if(!$selected_student_id): ?>
                <td>
                    <a href="view_student_assessment.php?student_id=<?php echo $row['student_id']; ?>" class="no-underline">
                        <?php echo isset($row['admission_no']) ? htmlspecialchars($row['admission_no']) : '-'; ?>
                    </a>
                </td>
                <td>
                    <a href="view_student_assessment.php?student_id=<?php echo $row['student_id']; ?>" class="no-underline">
                        <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['other_names']); ?>
                    </a>
                </td>
                <?php endif; ?>
                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                <td><?php echo htmlspecialchars($row['ind_test']); ?></td>
                <td><?php echo htmlspecialchars($row['group_work']); ?></td>
                <td><?php echo htmlspecialchars($row['class_work']); ?></td>
                <td><?php echo htmlspecialchars($row['project_work']); ?></td>
                <td><?php echo htmlspecialchars($row['total_60']); ?></td>
                <td><?php echo htmlspecialchars($row['scaled_50']); ?></td>
                <td><?php echo htmlspecialchars($row['exam']); ?></td>
                <td><?php echo htmlspecialchars($row['exam_scaled_50']); ?></td>
                <td><?php echo htmlspecialchars($row['final_total']); ?></td>
                <td><?php echo htmlspecialchars($row['grade']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var sidebarToggle = document.getElementById('sidebarToggle');
    var adminSidebar = document.getElementById('adminSidebar');
    if (sidebarToggle && adminSidebar) {
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            adminSidebar.classList.toggle('show');
        });
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 1000 &&
                !adminSidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                adminSidebar.classList.remove('show');
            }
        });
        window.addEventListener('resize', function() {
            if (window.innerWidth > 1000) {
                adminSidebar.classList.remove('show');
            }
        });
    }
});
</script>
</body>
</html>
<?php
// filepath: c:\xampp\htdocs\bsims\student_assessment.php

session_start();
$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Fetch classes for dropdown
$class_options = [];
$class_query = mysqli_query($conn, "SELECT DISTINCT class FROM students ORDER BY class ASC");
while ($row = mysqli_fetch_assoc($class_query)) {
    $class_options[] = $row['class'];
}

// Fetch subjects for dropdown
$subject_options = [];
$subject_query = mysqli_query($conn, "SELECT DISTINCT subject_name FROM subjects ORDER BY subject_name ASC");
while ($row = mysqli_fetch_assoc($subject_query)) {
    $subject_options[] = $row['subject_name'];
}

// Get active academic session
$active_session = mysqli_fetch_assoc(mysqli_query($conn, "SELECT academic_year, term FROM academic_sessions WHERE is_active=1 LIMIT 1"));
$active_academic = $active_session
    ? $active_session['academic_year'] . ' - ' . $active_session['term']
    : 'No active academic session';

// Handle class and subject selection
$selected_class = isset($_GET['class']) ? $_GET['class'] : '';
$selected_subject = isset($_GET['subject']) ? $_GET['subject'] : '';
$students = [];
if ($selected_class) {
    $safe_class = mysqli_real_escape_string($conn, $selected_class);
    $students_query = mysqli_query($conn, "SELECT id, first_name, other_names FROM students WHERE class='$safe_class' ORDER BY first_name, other_names");
    while ($row = mysqli_fetch_assoc($students_query)) {
        $students[] = $row;
    }
}

// Handle assessment submission
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assessments'])) {
    if (!$active_session) {
        $alert = '<div class="alert alert-danger">Assessment cannot be taken. No active academic session!</div>';
    } else {
        $selected_subject = isset($_POST['subject']) ? mysqli_real_escape_string($conn, $_POST['subject']) : '';
        $academic_year = mysqli_real_escape_string($conn, $active_session['academic_year']);
        $term = mysqli_real_escape_string($conn, $active_session['term']);
        foreach ($_POST['assessments'] as $student_id => $data) {
            $ind_test = (float)$data['ind_test'];
            $group_work = (float)$data['group_work'];
            $class_work = (float)$data['class_work'];
            $project_work = (float)$data['project_work'];
            $exam = (float)$data['exam'];

            $total_60 = $ind_test + $group_work + $class_work + $project_work;
            $scaled_50 = round(($total_60 / 60) * 50, 2);
            $exam_scaled_50 = round(($exam / 100) * 50, 2);
            $final_total = $scaled_50 + $exam_scaled_50;

            // Calculate grade (simple example)
            if ($final_total >= 90) $grade = 'Highest';
            elseif ($final_total >= 89) $grade = 'Higher';
            elseif ($final_total >= 79) $grade = 'High';
            elseif ($final_total >= 69) $grade = 'High Average';
            elseif ($final_total >= 59) $grade = 'Average';
            elseif ($final_total >= 54) $grade = 'Low Average';
            elseif ($final_total >= 49) $grade = 'Low';
            elseif ($final_total >= 39) $grade = 'Lower';
            else $grade = 'Lowest';

            // Save to database (example table: assessments)
            mysqli_query($conn, "REPLACE INTO assessments 
                (student_id, class, subject, ind_test, group_work, class_work, project_work, total_60, scaled_50, exam, exam_scaled_50, final_total, grade, academic_year, term)
                VALUES (
                    $student_id, 
                    '$safe_class',
                    '$selected_subject',
                    $ind_test, $group_work, $class_work, $project_work, 
                    $total_60, $scaled_50, $exam, $exam_scaled_50, $final_total, '$grade',
                    '$academic_year', '$term'
                )
            ");
        }
        $alert = '<div class="alert alert-success">Assessments saved successfully!</div>';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="admin-panel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>Administrator Dashboard - Student Assessments Entry</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .main-flex-container {
            display: flex;
            align-items: flex-start;
            gap: 24px;
            margin-top: 0;
            justify-content: flex-start;
            width: 100%;
        }
        .admin-sidebar {
            min-width: 220px;
            max-width: 260px;
            flex-shrink: 0;
            background: #0e7490;
            color: #fff;
            min-height: 100vh;
            box-shadow: 2px 0 8px rgba(0,0,0,0.04);
            padding-top: 0;
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 10;
            transition: all 0.3s;
        }
        .main-container {
            flex: 1 1 0;
            max-width: 100vw;
            width: 100%;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px #ddd;
            padding: 30px;
            margin: 0;
            min-width: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        @media (max-width: 1000px) {
            .main-flex-container { flex-direction: column; align-items: stretch; }
            .admin-sidebar { margin-bottom: 20px; }
            .main-container { margin-top: 20px; max-width: 98vw; }
        }
        @media (max-width: 700px) {
            .main-container { padding: 10px 2vw; }
        }
    </style>
</head>
<body>
<header class="header">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle admin panel">
        <i class="fa fa-bars"></i>
    </button>
    <div class="dashboard-title">
        <i class="fa fa-gauge"></i> Student Assessment
    </div>
    <div class="logout">
        <a href="logout.php" class="btn btn-danger"><i class="fa fa-sign-out-alt"></i> Logout</a>
    </div>
</header>
<div class="main-flex-container">
    <nav class="admin-sidebar" id="adminSidebar">
        <!-- ...sidebar code... -->
    </nav>
    <div class="main-container">
        <div style="margin-bottom:15px; font-size:16px; color:#0e7490;">
            <b>Active Academic Session:</b>
            <?php echo htmlspecialchars($active_academic); ?>
        </div>
        <?php if ($alert) echo $alert; ?>
        <form method="get" class="form-inline" style="margin-bottom:20px;">
            <label for="class">Select Class:</label>
            <select name="class" id="class" class="form-control" required>
                <option value="">-- Select Class --</option>
                <?php foreach ($class_options as $class): ?>
                    <option value="<?php echo htmlspecialchars($class); ?>" <?php if($selected_class==$class) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($class); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="subject" style="margin-left:10px;">Select Subject:</label>
            <select name="subject" id="subject" class="form-control" required>
                <option value="">-- Select Subject --</option>
                <?php foreach ($subject_options as $subject): ?>
                    <option value="<?php echo htmlspecialchars($subject); ?>" <?php if($selected_subject==$subject) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($subject); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">View Students</button>
        </form>
        <form method="get" action="view_student_assessment.php" style="margin-bottom:20px;">
            <button type="submit" class="btn btn-info">
                <i class="fa fa-eye"></i> View Entered Student Assessment
            </button>
        </form>
        <?php if ($selected_class && $selected_subject && count($students) > 0): ?>
        <form method="post">
            <input type="hidden" name="subject" value="<?php echo htmlspecialchars($selected_subject); ?>">
            <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Class</th>
                        <th>Name</th>
                        <th>Individual Test<br>(15)</th>
                        <th>Group Work<br>(15)</th>
                        <th>Class Work<br>(15)</th>
                        <th>Project Work<br>(15)</th>
                        <th>Total<br>(60)</th>
                        <th>60 Scaled<br>50</th>
                        <th>End of Term Exam<br>(100)</th>
                        <th>100 Scaled<br>50</th>
                        <th>50+50</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $i => $student): ?>
                    <tr>
                        <td><?php echo $i+1; ?></td>
                        <td><?php echo htmlspecialchars($selected_class); ?></td>
                        <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['other_names']); ?></td>
                        <td><input type="number" name="assessments[<?php echo $student['id']; ?>][ind_test]" min="0" max="15" step="0.01" class="form-control" required></td>
                        <td><input type="number" name="assessments[<?php echo $student['id']; ?>][group_work]" min="0" max="15" step="0.01" class="form-control" required></td>
                        <td><input type="number" name="assessments[<?php echo $student['id']; ?>][class_work]" min="0" max="15" step="0.01" class="form-control" required></td>
                        <td><input type="number" name="assessments[<?php echo $student['id']; ?>][project_work]" min="0" max="15" step="0.01" class="form-control" required></td>
                        <td>
                            <input type="number" class="form-control" readonly 
                                id="total60_<?php echo $student['id']; ?>" 
                                style="background:#f9f9f9;">
                        </td>
                        <td>
                            <input type="number" class="form-control" readonly 
                                id="scaled50_<?php echo $student['id']; ?>" 
                                style="background:#f9f9f9;">
                        </td>
                        <td>
                            <input type="number" name="assessments[<?php echo $student['id']; ?>][exam]" min="0" max="100" step="0.01" class="form-control" required>
                        </td>
                        <td>
                            <input type="number" class="form-control" readonly 
                                id="examscaled50_<?php echo $student['id']; ?>" 
                                style="background:#f9f9f9;">
                        </td>
                        <td>
                            <input type="number" class="form-control" readonly 
                                id="finaltotal_<?php echo $student['id']; ?>" 
                                style="background:#f9f9f9;">
                        </td>
                        <td>
                            <input type="text" class="form-control" readonly 
                                id="grade_<?php echo $student['id']; ?>" 
                                style="background:#f9f9f9;">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save Assessments</button>
        </form>
        <script>
        document.querySelectorAll('tr').forEach(function(row) {
            var inputs = row.querySelectorAll('input[type="number"]');
            if (inputs.length > 0) {
                inputs.forEach(function(input) {
                    input.addEventListener('input', function() {
                        var id = input.name.match(/\[(\d+)\]/);
                        if (!id) return;
                        id = id[1];
                        var ind = parseFloat(document.querySelector('[name="assessments['+id+'][ind_test]"]').value) || 0;
                        var grp = parseFloat(document.querySelector('[name="assessments['+id+'][group_work]"]').value) || 0;
                        var cls = parseFloat(document.querySelector('[name="assessments['+id+'][class_work]"]').value) || 0;
                        var proj = parseFloat(document.querySelector('[name="assessments['+id+'][project_work]"]').value) || 0;
                        var exam = parseFloat(document.querySelector('[name="assessments['+id+'][exam]"]').value) || 0;
                        var total60 = ind + grp + cls + proj;
                        var scaled50 = Math.round((total60/60)*50*100)/100;
                        var examscaled50 = Math.round((exam/100)*50*100)/100;
                        var finaltotal = Math.round((scaled50 + examscaled50)*100)/100;
                        var grade = '';
                        if (finaltotal >= 90) grade = 'Highest';
                        else if (finaltotal >= 89) grade = 'Higher';
                        else if (finaltotal >= 79) grade = 'High';
                        else if (finaltotal >= 69) grade = 'High Average';
                        else if (finaltotal >= 59) grade = 'Average';
                        else if (finaltotal >= 54) grade = 'Low Average';
                        else if (finaltotal >= 49) grade = 'Low';
                        else if (finaltotal >= 39) grade = 'Lower';
                        else grade = 'Lowest';
                        document.getElementById('total60_'+id).value = total60;
                        document.getElementById('scaled50_'+id).value = scaled50;
                        document.getElementById('examscaled50_'+id).value = examscaled50;
                        document.getElementById('finaltotal_'+id).value = finaltotal;
                        document.getElementById('grade_'+id).value = grade;
                    });
                });
            }
        });
        </script>
        <?php elseif ($selected_class || $selected_subject): ?>
            <div class="alert alert-warning">No students found for this class or subject not selected.</div>
        <?php endif; ?>
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
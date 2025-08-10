<?php

session_start();
$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Fetch classes for JHS and Primary
$classes = [];
$class_query = mysqli_query($conn, "SELECT id, classname FROM classes WHERE classname LIKE 'JHS%' OR classname LIKE 'Primary%' ORDER BY classname ASC");
while ($row = mysqli_fetch_assoc($class_query)) {
    $classes[] = $row;
}

// Fetch academic years and terms
$years = [];
$terms = [];
$year_query = mysqli_query($conn, "SELECT DISTINCT academic_year FROM academic_sessions ORDER BY academic_year DESC");
while ($row = mysqli_fetch_assoc($year_query)) {
    $years[] = $row['academic_year'];
}
$term_query = mysqli_query($conn, "SELECT DISTINCT term FROM academic_sessions ORDER BY term ASC");
while ($row = mysqli_fetch_assoc($term_query)) {
    $terms[] = $row['term'];
}

// Fetch subjects
$subjects = [];
$subject_query = mysqli_query($conn, "SELECT id, subject_name FROM subjects ORDER BY subject_name ASC");
while ($row = mysqli_fetch_assoc($subject_query)) {
    $subjects[] = $row;
}

// Handle form submission
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_note'])) {
    $class_id = intval($_POST['class_id']);
    $academic_year = mysqli_real_escape_string($conn, $_POST['academic_year']);
    $term = mysqli_real_escape_string($conn, $_POST['term']);
    $subject_id = intval($_POST['subject_id']);
    $week_start = mysqli_real_escape_string($conn, $_POST['week_start']);
    $week_end = mysqli_real_escape_string($conn, $_POST['week_end']);
    $strand = mysqli_real_escape_string($conn, $_POST['strand']);
    $sub_strand = mysqli_real_escape_string($conn, $_POST['sub_strand']);
    $content_standard = mysqli_real_escape_string($conn, $_POST['content_standard']);
    $indicators = mysqli_real_escape_string($conn, $_POST['indicators']);
    $resources = mysqli_real_escape_string($conn, $_POST['resources']);
    $send_for_approval = isset($_POST['send_for_approval']) ? 1 : 0;

    $insert = mysqli_query($conn, "INSERT INTO jhs_lesson_note 
        (class_id, academic_year, term, subject_id, week_start, week_end, strand, sub_strand, content_standard, indicators, resources, send_for_approval, approval_status) VALUES
        ($class_id, '$academic_year', '$term', $subject_id, '$week_start', '$week_end', '$strand', '$sub_strand', '$content_standard', '$indicators', '$resources', $send_for_approval, 'Pending')
    ");
    if ($insert) {
        $alert = '<div class="alert alert-success">Lesson note saved successfully!</div>';
    } else {
        $alert = '<div class="alert alert-danger">Failed to save lesson note. Please try again.</div>';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Lesson Note (JHS & Primary)</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #f8fafc 100%);
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .container {
            max-width: 1200px;
            margin-top: 40px;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(60,60,120,0.12);
            padding: 40px 30px 30px 30px;
        }
        h3 {
            color: #3b5998;
            font-weight: 700;
            margin-bottom: 30px;
            letter-spacing: 1px;
        }
        .well {
            background: #f1f5fb;
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 8px #e0e7ff;
            padding: 30px 20px 20px 20px;
        }
        .row-flex {
            display: flex;
            flex-wrap: nowrap;
            gap: 16px;
            align-items: stretch;
            margin-bottom: 18px;
        }
        .row-flex > .form-group {
            flex: 1 1 0;
            min-width: 120px;
            margin-bottom: 0;
        }
        .row-flex textarea, .row-flex input[type="date"], .row-flex input[type="text"] {
            min-height: 70px;
            resize: vertical;
            border-radius: 8px;
            border: 1px solid #bfc9d9;
            background: #f8fafc;
            font-size: 15px;
        }
        .form-group label {
            font-weight: 600;
            color: #3b5998;
            margin-bottom: 5px;
        }
        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px #c7d2fe;
        }
        .btn-primary {
            background: linear-gradient(90deg, #3b82f6 0%, #6366f1 100%);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            padding: 10px 28px;
            font-size: 17px;
            box-shadow: 0 2px 8px #c7d2fe;
            transition: background 0.2s;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #6366f1 0%, #3b82f6 100%);
        }
        .alert {
            border-radius: 8px;
            font-size: 16px;
        }
        .checkbox label {
            font-weight: 500;
            color: #6366f1;
            font-size: 16px;
        }
        @media (max-width: 1400px) {
            .row-flex { flex-direction: column; }
            .row-flex > .form-group { min-width: 100%; }
        }
    </style>
</head>
<body>
<div class="container">
    <h3 class="text-center"><i class="fa fa-book-open"></i> Create Lesson Note <span style="font-size:18px;color:#6366f1;">(JHS & Primary)</span></h3>
    <?php if ($alert) echo $alert; ?>
    <form method="post" class="well">
        <!-- First row: Class, Academic Year, Term, Subject -->
        <div class="form-group row-flex" style="margin-bottom:28px;">
            <div class="form-group">
                <label><i class="fa fa-school"></i> Class</label>
                <select name="class_id" class="form-control" required>
                    <option value="">Select Class</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['classname']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fa fa-calendar"></i> Academic Year</label>
                <select name="academic_year" class="form-control" required>
                    <option value="">Select Year</option>
                    <?php foreach ($years as $y): ?>
                        <option value="<?php echo htmlspecialchars($y); ?>"><?php echo htmlspecialchars($y); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fa fa-clock"></i> Term</label>
                <select name="term" class="form-control" required>
                    <option value="">Select Term</option>
                    <?php foreach ($terms as $t): ?>
                        <option value="<?php echo htmlspecialchars($t); ?>"><?php echo htmlspecialchars($t); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fa fa-book"></i> Subject</label>
                <select name="subject_id" class="form-control" required>
                    <option value="">Select Subject</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['subject_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <!-- Second row: Week Start, Week End, Strand, Sub-Strand, Content Standard, Indicators, Resources -->
        <div class="form-group row-flex" style="margin-bottom:28px;">
            <div class="form-group">
                <label><i class="fa fa-calendar-day"></i> Week Start</label>
                <input type="date" name="week_start" class="form-control" required>
            </div>
            <div class="form-group">
                <label><i class="fa fa-calendar-day"></i> Week End</label>
                <input type="date" name="week_end" class="form-control" required>
            </div>
            <div class="form-group">
                <label><i class="fa fa-layer-group"></i> Strand</label>
                <textarea name="strand" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label><i class="fa fa-stream"></i> Sub-Strand</label>
                <textarea name="sub_strand" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label><i class="fa fa-bullseye"></i> Content Standard</label>
                <textarea name="content_standard" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label><i class="fa fa-check-circle"></i> Indicators</label>
                <textarea name="indicators" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label><i class="fa fa-toolbox"></i> Resources</label>
                <textarea name="resources" class="form-control" required></textarea>
            </div>
        </div>
        <div class="checkbox" style="margin-bottom:18px;">
            <label>
                <input type="checkbox" name="send_for_approval" value="1">
                <i class="fa fa-paper-plane"></i> Send to Admin for Approval
            </label>
        </div>
        <div class="text-center">
            <button type="submit" name="save_note" class="btn btn-primary"><i class="fa fa-save"></i> Save Lesson Note</button>
        </div>
    </form>
</div>
</body>
</html>
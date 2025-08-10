<?php
// filepath: c:\xampp\htdocs\bsims\academics.php

session_start();
$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Handle start/stop/edit/delete academic session
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['start_academic'])) {
        $session_id = (int)$_POST['session_id'];
        $start_date = $_POST['start_date'];
        $check = mysqli_query($conn, "SELECT stop_date FROM academic_sessions WHERE id=$session_id");
        $row = mysqli_fetch_assoc($check);
        if (empty($row['stop_date'])) {
            mysqli_query($conn, "UPDATE academic_sessions SET is_active=0");
            mysqli_query($conn, "UPDATE academic_sessions SET start_date='$start_date', stop_date=NULL, is_active=1 WHERE id=$session_id");
            $alert = '<div class="alert alert-success">Academic session started!</div>';
        } else {
            $alert = '<div class="alert alert-danger">Cannot start term. This session has already ended.</div>';
        }
    }
    if (isset($_POST['stop_academic'])) {
        $session_id = (int)$_POST['session_id'];
        $stop_date = date('Y-m-d');
        $check = mysqli_query($conn, "SELECT stop_date FROM academic_sessions WHERE id=$session_id");
        $row = mysqli_fetch_assoc($check);
        if (empty($row['stop_date'])) {
            mysqli_query($conn, "UPDATE academic_sessions SET is_active=0, stop_date='$stop_date' WHERE id=$session_id");
            $alert = '<div class="alert alert-warning">This term has ended</div>';
        } else {
            $alert = '<div class="alert alert-danger">This term is already ended.</div>';
        }
    }
    if (isset($_POST['add_academic'])) {
        $term = mysqli_real_escape_string($conn, $_POST['term']);
        $academic_year = mysqli_real_escape_string($conn, $_POST['academic_year']);
        $date = $_POST['date'];
        $check = mysqli_query($conn, "SELECT id FROM academic_sessions WHERE academic_year='$academic_year' AND term='$term'");
        if (mysqli_num_rows($check) > 0) {
            $alert = '<div class="alert alert-danger">This academic year and term already exists!</div>';
        } else {
            mysqli_query($conn, "INSERT INTO academic_sessions (academic_year, term, start_date, is_active) VALUES ('$academic_year', '$term', '$date', 0)");
            $alert = '<div class="alert alert-success">New academic session added!</div>';
        }
    }
    if (isset($_POST['edit_academic'])) {
        $session_id = (int)$_POST['session_id'];
        $academic_year = mysqli_real_escape_string($conn, $_POST['academic_year']);
        $term = mysqli_real_escape_string($conn, $_POST['term']);
        $start_date = $_POST['edit_start_date'];
        $end_date = $_POST['edit_end_date'];
        $check = mysqli_query($conn, "SELECT id FROM academic_sessions WHERE academic_year='$academic_year' AND term='$term' AND id!=$session_id");
        if (mysqli_num_rows($check) > 0) {
            $alert = '<div class="alert alert-danger">This academic year and term already exists!</div>';
        } else {
            $end_date_sql = $end_date ? "'$end_date'" : "NULL";
            mysqli_query($conn, "UPDATE academic_sessions SET academic_year='$academic_year', term='$term', start_date='$start_date', stop_date=$end_date_sql WHERE id=$session_id");
            $alert = '<div class="alert alert-info">Academic session updated!</div>';
        }
    }
    if (isset($_POST['delete_academic'])) {
        $session_id = (int)$_POST['session_id'];
        mysqli_query($conn, "DELETE FROM academic_sessions WHERE id=$session_id");
        $alert = '<div class="alert alert-danger">Academic session deleted!</div>';
    }
}

// Fetch all academic sessions
$sessions = [];
$result = mysqli_query($conn, "SELECT * FROM academic_sessions ORDER BY id DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $sessions[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Academic Sessions</title>
    <link rel="stylesheet" href="admin-panel.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .main-flex-container {
            display: flex;
            flex-direction: row;
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
        .academic-btns { display: flex; gap: 18px; margin-bottom: 18px; justify-content: center; }
        .academic-btns input[type="date"], .academic-btns select, .academic-btns input[type="text"] { padding: 6px 10px; border-radius: 5px; border: 1px solid #ccc; }
        .academic-btns button { min-width: 120px; }
        .alert { max-width: 400px; margin: 10px auto; }
        .session-list { max-width: 700px; margin: 30px auto; }
        .session-item { background: #f1f5f9; border-radius: 6px; margin-bottom: 10px; box-shadow: 0 2px 6px #eee; }
        .session-header { padding: 16px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; }
        .session-header.active { background: #38bdf8; color: #fff; }
        .session-details { display: none; padding: 16px; border-top: 1px solid #ddd; }
        .session-details.active { display: block; }
        .session-term, .session-year { font-weight: bold; color: #0e7490; cursor: pointer; text-decoration: none; }
        .session-term:hover, .session-year:hover { color: #38bdf8; }
        .session-status { font-size: 0.95em; padding: 2px 10px; border-radius: 12px; margin-left: 10px; }
        .session-status.active { background: #22c55e; color: #fff; }
        .session-status.inactive { background: #e11d48; color: #fff; }
        .session-dates { font-size: 0.97em; color: #555; margin-left: 18px; }
        .session-dates i { color: #0e7490; margin-right: 2px; }
        .session-meta { font-size: 0.97em; color: #555; margin-left: 10px; }
        .session-meta i { color: #0e7490; margin-right: 2px; }
        .edit-form { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-top: 10px; }
        .edit-form input[type="text"] { width: 120px; min-width: 0; }
        .edit-form-row { width: 100%; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .edit-form-actions { width: 100%; display: flex; gap: 10px; margin-top: 10px; }
        .edit-form label { margin-bottom: 0; margin-right: 5px; font-weight: normal; }
        @media (max-width: 1000px) {
            .main-flex-container { flex-direction: column; align-items: stretch; }
            .admin-sidebar { margin-bottom: 20px; height: auto; min-height: 0; }
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
            <span class="dashboard-title" style="margin: 0 auto; display: flex; align-items: center; justify-content: center; flex: 1; text-align: center;">
        <i class="fa fa-graduation-cap"></i>
        <span style="margin-left: 8px;">Academics</span>
    </span>
        <div class="logout">
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </header>
    <div class="main-flex-container">
        <nav class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <i class="fa fa-user-shield"></i> Admin Panel
            </div>
            <ul>
                <li class="active"><a href="adminhome.php"><i class="fa fa-home"></i> Dashboard</a></li>
                <li><a href="recommended_books.php"><i class="fa fa-book"></i> Recommended Books</a></li>
                <li><a href="view_student_assessment.php"><i class="fa fa-graduation-cap"></i> Students Report</a></li>
            </ul>
        </nav>
        <div class="main-container">
            <?php if ($alert) echo $alert; ?>
            <form method="post" class="academic-btns" style="margin-bottom:30px;">
                <input type="text" name="academic_year" placeholder="Academic Year (e.g. 2024/2025)" required>
                <select name="term" required>
                    <option value="">Select Term</option>
                    <option value="1st Term">1st Term</option>
                    <option value="2nd Term">2nd Term</option>
                    <option value="3rd Term">3rd Term</option>
                </select>
                <input type="date" name="date" required>
                <button type="submit" name="add_academic" class="btn btn-info">
                    <i class="fa fa-plus"></i> Add Academic Session
                </button>
            </form>
            <div class="session-list">
                <?php foreach ($sessions as $session): ?>
                <div class="session-item">
                    <div class="session-header<?php if($session['is_active']) echo ' active'; ?>">
                        <span class="session-year" onclick="toggleSession(<?php echo $session['id']; ?>)">
                            <?php echo !empty($session['academic_year']) ? htmlspecialchars($session['academic_year']) : 'Year'; ?>
                            <?php if (!empty($session['start_date'])): ?>
                                <span class="session-meta">
                                    <i class="fa fa-calendar"></i> <?php echo htmlspecialchars($session['start_date']); ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($session['stop_date'])): ?>
                                <span class="session-meta">
                                    <i class="fa fa-stop"></i> <?php echo htmlspecialchars($session['stop_date']); ?>
                                </span>
                            <?php endif; ?>
                        </span>
                        <span class="session-term" onclick="toggleSession(<?php echo $session['id']; ?>)">
                            <?php echo htmlspecialchars($session['term']); ?>
                        </span>
                        <span>
                            <span class="session-status <?php echo $session['is_active'] ? 'active' : 'inactive'; ?>">
                                <?php echo $session['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                            <i class="fa fa-chevron-down"></i>
                        </span>
                    </div>
                    <div class="session-details" id="session-details-<?php echo $session['id']; ?>">
                        <form method="post" class="academic-btns" style="justify-content:flex-start;">
                            <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                            <label style="margin-bottom:0; margin-right:5px;">Start Term:</label>
                            <input type="date" name="start_date" value="<?php echo !empty($session['start_date']) ? $session['start_date'] : ''; ?>" required>
                            <label style="margin-bottom:0; margin-right:5px;">End Term:</label>
                            <input type="date" name="stop_date" value="<?php echo !empty($session['stop_date']) ? $session['stop_date'] : ''; ?>" disabled>
                            <button type="submit" name="start_academic" class="btn btn-success"
                                <?php
                                    if($session['is_active'] || !empty($session['stop_date'])) echo 'disabled';
                                ?>>
                                <i class="fa fa-play"></i> Start Term
                            </button>
                            <button type="submit" name="stop_academic" class="btn btn-danger"
                                <?php
                                    if(!$session['is_active'] || !empty($session['stop_date'])) echo 'disabled';
                                ?>>
                                <i class="fa fa-stop"></i> End Term
                            </button>
                        </form>
                        <div style="margin-top:10px;">
                            <button class="btn btn-warning btn-xs" type="button" onclick="showEditForm(<?php echo $session['id']; ?>)">
                                <i class="fa fa-edit"></i> Edit
                            </button>
                            <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this session?');">
                                <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                                <button type="submit" name="delete_academic" class="btn btn-danger btn-xs">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                        <form method="post" class="edit-form" id="edit-form-<?php echo $session['id']; ?>" style="display:none;">
                            <div class="edit-form-row">
                                <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                                <input type="text" name="academic_year" value="<?php echo htmlspecialchars($session['academic_year']); ?>" required placeholder="Academic Year">
                                <select name="term" required>
                                    <option value="1st Term" <?php if($session['term']=='1st Term') echo 'selected'; ?>>1st Term</option>
                                    <option value="2nd Term" <?php if($session['term']=='2nd Term') echo 'selected'; ?>>2nd Term</option>
                                    <option value="3rd Term" <?php if($session['term']=='3rd Term') echo 'selected'; ?>>3rd Term</option>
                                </select>
                                <label style="margin-bottom:0; margin-right:5px;">Start Term:</label>
                                <input type="date" name="edit_start_date" value="<?php echo htmlspecialchars($session['start_date']); ?>" required>
                                <label style="margin-bottom:0; margin-right:5px;">End Term:</label>
                                <input type="date" name="edit_end_date" value="<?php echo htmlspecialchars($session['stop_date']); ?>">
                            </div>
                            <div class="edit-form-actions">
                                <button type="submit" name="edit_academic" class="btn btn-primary btn-xs">
                                    <i class="fa fa-save"></i> Save
                                </button>
                                <button type="button" class="btn btn-default btn-xs" onclick="hideEditForm(<?php echo $session['id']; ?>)">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <script>
    function toggleSession(id) {
        var details = document.getElementById('session-details-' + id);
        var allDetails = document.querySelectorAll('.session-details');
        allDetails.forEach(function(d) { if (d !== details) d.classList.remove('active'); });
        details.classList.toggle('active');
        hideEditForm(id);
    }
    function showEditForm(id) {
        document.getElementById('edit-form-' + id).style.display = 'flex';
    }
    function hideEditForm(id) {
        var form = document.getElementById('edit-form-' + id);
        if(form) form.style.display = 'none';
    }
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
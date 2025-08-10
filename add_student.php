<?php

// Database connection
$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Fetch unique curriculums from subjects table
$curriculum_options = [];
$curriculum_query = mysqli_query($conn, "SELECT DISTINCT curriculum FROM subjects ORDER BY curriculum ASC");
while ($row = mysqli_fetch_assoc($curriculum_query)) {
    $curriculum_options[] = $row['curriculum'];
}

// Class options
$class_options = [
    'Nursery', 'KG 1', 'KG 2', 'BS 1', 'BS 2', 'BS 3', 'BS 4', 'BS 5', 'BS 6', 'JHS 1', 'JHS 2', 'JHS 3'
];

// Handle AJAX request for subjects by curriculum (for iframe)
if (isset($_GET['subjects_iframe']) && isset($_GET['curriculum'])) {
    $curriculum = mysqli_real_escape_string($conn, $_GET['curriculum']);
    $result = mysqli_query($conn, "SELECT subject_name FROM subjects WHERE curriculum='$curriculum' ORDER BY subject_name ASC");
    echo "<ul style='list-style:none;padding-left:0;'>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<li style='padding:4px 0;'><i class='fa fa-book text-primary'></i> " . htmlspecialchars($row['subject_name']) . "</li>";
    }
    echo "</ul>";
    exit;
}

// Add student
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $admission_no = mysqli_real_escape_string($conn, $_POST['admission_no']);
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $other_names = mysqli_real_escape_string($conn, $_POST['other_names']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $class = mysqli_real_escape_string($conn, $_POST['class']);
    $curriculum = mysqli_real_escape_string($conn, $_POST['curriculum']);
    $parent_guardian = mysqli_real_escape_string($conn, $_POST['parent_guardian']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);

    // Check for duplicate admission number
    $check = mysqli_query($conn, "SELECT id FROM students WHERE admission_no='$admission_no' LIMIT 1");
    if (mysqli_num_rows($check) > 0) {
        $alert = '<div class="alert alert-danger small-alert" id="alertBox">Admission number already exists.</div>';
    } else {
        // Handle profile picture upload
        $profile_pic = null;
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('student_', true) . '.' . $ext;
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $target = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target)) {
                $profile_pic = $target;
            }
        }

        $profile_pic_sql = $profile_pic ? "'$profile_pic'" : "NULL";
        $result = mysqli_query($conn, "INSERT INTO students (admission_no, first_name, other_names, gender, class, curriculum, parent_guardian, phone, email, address, city, profile_pic) VALUES ('$admission_no', '$first_name', '$other_names', '$gender', '$class', '$curriculum', '$parent_guardian', '$phone', '$email', '$address', '$city', $profile_pic_sql)");
        $alert = $result ? '<div class="alert alert-success small-alert" id="alertBox">Student added!</div>' : '<div class="alert alert-danger small-alert" id="alertBox">Failed to add student.</div>';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="admin-panel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>Add Student</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            box-sizing: border-box;
        }
        body {
            min-height: 100vh;}
        
        .main-flex-container {
            display: flex;
            align-items: flex-start;
            min-height: 100vh;
            width: 100%;
            position: relative;
        }
    
        
        .main-container {
            max-width: 1100px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px #ddd;
            padding: 40px 30px 30px 30px;
            width: 98vw;
            position: relative;
            left: 0;
            right: 0;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .panel.panel-primary {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            transition: max-width 0.3s, font-size 0.3s;
        }
        .form-flex {
            display: flex;
            gap: 40px;
            align-items: flex-start;
            justify-content: center;
            flex-wrap: wrap;
        }
        .profile-pic-col {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 120px;
            max-width: 200px;
            margin-right: 0;
            margin-bottom: 0;
            background: none;
        }
        .profile-pic-preview {
            width: 110px;
            height: 110px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #007bff;
            margin-bottom: 10px;
            background: #f1f1f1;
        }
        .form-fields-col {
            flex: 2;
            min-width: 260px;
            max-width: 600px;
            width: 100%;
        }
        .form-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        .form-row > * {
            flex: 1;
            min-width: 120px;
        }
        .subjects-iframe-side {
            flex: 1;
            min-width: 220px;
            margin-left: 40px;
            max-width: 300px;
        }
        .subjects-iframe {
            width: 100%;
            min-height: 180px;
            border: 1px solid #e1e1e1;
            border-radius: 6px;
            background: #f9f9f9;
            padding: 10px;
        }
        .small-alert {
            max-width: 350px;
            margin: 0 auto 20px auto;
            font-size: 1em;
            padding: 8px 16px;
        }
        @media (max-width: 1100px) {
            .main-container { padding: 15px 2vw; }
            .panel.panel-primary { max-width: 98vw; }
            .form-flex { flex-direction: column; gap: 20px; }
            .subjects-iframe-side { margin-left: 0; margin-top: 20px; max-width: 100%; }
            .profile-pic-col { flex-direction: row; margin-bottom: 20px; }
        }
        @media (max-width: 900px) {
            .main-flex-container { flex-direction: column; align-items: stretch; }
            .main-container { max-width: 98vw; }
            .panel.panel-primary { max-width: 98vw; }
        }
        @media (max-width: 700px) {
            .main-container { padding: 10px 0; }
            .form-flex { flex-direction: column; gap: 18px; }
            .subjects-iframe-side { margin-left: 0; margin-top: 15px; min-width: 0; max-width: 100%; }
            .form-fields-col { min-width: 0; }
        }
        @media (max-width: 600px) {
            .header {
                flex-direction: row;
                padding: 10px 5px;
                font-size: 1em;
            }
            .dashboard-title {
                font-size: 1em !important;
            }
            .main-container {
                padding: 4px 1vw;
                margin: 10px 0 0 0;
                border-radius: 0;
                width: 100vw;
            }
            .panel.panel-primary {
                max-width: 99vw;
                font-size: 0.95em;
                padding: 0 2px;
            }
            
           
            .main-flex-container {
                flex-direction: column;
                width: 100vw;
            }
            .form-row > * {
                min-width: 90px;
                font-size: 0.95em;
                padding: 4px 6px;
            }
            .form-fields-col, .subjects-iframe-side {
                min-width: 0;
                max-width: 100%;
            }
            .profile-pic-col {
                min-width: 70px;
                max-width: 100%;
                margin-bottom: 10px;
            }
            .profile-pic-preview {
                width: 70px;
                height: 70px;
            }
        }
        @media (max-width: 400px) {
            .dashboard-title {
                font-size: 0.9em !important;
            }
           
            .profile-pic-preview {
                width: 50px;
                height: 50px;
            }
            .panel.panel-primary {
                font-size: 0.9em;
            }
        }
        
    </style>
    <script>
    function showSubjectsIframe() {
        var curriculum = document.getElementById('curriculum').value;
        var iframe = document.getElementById('subjects-iframe');
        if (curriculum) {
            iframe.src = "add_student.php?subjects_iframe=1&curriculum=" + encodeURIComponent(curriculum);
            iframe.style.display = "block";
        } else {
            iframe.src = "";
            iframe.style.display = "none";
        }
    }
    function previewProfilePic(input) {
        var preview = document.getElementById('profile-pic-preview');
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = "block";
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = "";
            preview.style.display = "none";
        }
    }
    // Hide alert after 10 seconds
    window.onload = function() {
        var alertBox = document.getElementById('alertBox');
        if (alertBox) {
            setTimeout(function() {
                alertBox.style.display = 'none';
            }, 10000);
        }
        // Sidebar toggle for mobile
        var sidebarToggle = document.getElementById('sidebarToggle');
        var adminSidebar = document.getElementById('adminSidebar');
        if (sidebarToggle && adminSidebar) {
            sidebarToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                adminSidebar.classList.toggle('show');
            });
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 600) {
                    if (!adminSidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                        adminSidebar.classList.remove('show');
                    }
                }
            });
        }
    };
    </script>
</head>
<body>
<header class="header">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle admin panel">
        <i class="fa fa-bars"></i>
    </button>
    <div class="dashboard-title">
        <i class="fa fa-gauge"></i> Administrator Dashboard
    </div>
    <div class="logout">
        <a href="logout.php" class="btn btn-danger"><i class="fa fa-sign-out-alt"></i> Logout</a>
    </div>
</header>
<div class="main-flex-container">
    <nav class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <i class="fa fa-user-shield"></i>
            <span>Admin Panel</span>
        </div>
        <ul>
            <li><a href="adminhome.php"><i class="fa fa-home"></i><span>Dashboard</span></a></li>


            <li><a href="view_students.php"><i class="fa fa-user-graduate"></i><span>Students</span></a></li>

        </ul>
    </nav>
    <div class="main-container">
        <?php if ($alert) echo $alert; ?>

        <div class="panel panel-primary">
            <div class="panel-heading text-center">
                <span class="panel-title"><i class="fa fa-user-plus"></i> Add Student</span>
            </div>
            <div class="panel-body">
                <div class="form-flex">
                    <form method="post" action="add_student.php" enctype="multipart/form-data" style="margin-bottom:20px;display:flex;flex:1;gap:40px;align-items:flex-start;justify-content:center;width:100%;flex-wrap:wrap;">
                        <div class="profile-pic-col">
                            <img id="profile-pic-preview" class="profile-pic-preview" alt="Profile Preview">
                            <input type="file" name="profile_pic" accept="image/*" class="form-control input-sm" onchange="previewProfilePic(this)">
                            <small>Profile Picture</small>
                        </div>
                        <div class="form-fields-col">
                            <div class="form-row">
                                <input type="text" name="admission_no" class="form-control input-sm" required placeholder="Admission Number">
                                <input type="text" name="first_name" class="form-control input-sm" required placeholder="First Name">
                                <input type="text" name="other_names" class="form-control input-sm" required placeholder="Other Names">
                            </div>
                            <div class="form-row">
                                <select name="gender" class="form-control input-sm" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                                <select name="class" class="form-control input-sm" required>
                                    <option value="">Select Class</option>
                                    <?php foreach ($class_options as $class): ?>
                                        <option value="<?php echo $class; ?>"><?php echo $class; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="curriculum" id="curriculum" class="form-control input-sm" required onchange="showSubjectsIframe()">
                                    <option value="">Select Curriculum</option>
                                    <?php foreach ($curriculum_options as $curr): ?>
                                        <option value="<?php echo $curr; ?>"><?php echo $curr; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-row">
                                <input type="text" name="parent_guardian" class="form-control input-sm" required placeholder="Parent/Guardian Name">
                                <input type="text" name="phone" class="form-control input-sm" required placeholder="Phone">
                            </div>
                            <div class="form-row">
                                <input type="email" name="email" class="form-control input-sm" placeholder="Email">
                                <input type="text" name="address" class="form-control input-sm" placeholder="Address">
                                <input type="text" name="city" class="form-control input-sm" placeholder="City/Town">
                            </div>
                            <button type="submit" name="add_student" class="btn btn-primary btn-sm" style="margin-top:10px;"><i class="fa fa-plus"></i> Add Student</button>
                        </div>
                    </form>
                    <!-- Subjects Iframe -->
                    <div class="subjects-iframe-side">
                        <label><i class="fa fa-book"></i> Subjects for Curriculum</label>
                        <iframe id="subjects-iframe" class="subjects-iframe" style="display:none;" frameborder="0"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<?php


session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
} elseif ($_SESSION['username'] == 'student') {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Fetch staff data if editing
$staff = [
    'staffid' => '',
    'name' => '',
    'dob' => '',
    'licenseno' => '',
    'registeredno' => '',
    'rank' => '',
    'phone' => '',
    'email' => '',
    'ssnitno' => '',
    'niano' => '',
    'bank' => '',
    'acno' => '',
    'branch' => '',
    'religion' => '',
    'denomination' => '',
    'emergencyno' => '',
    'profilepicture' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staffid = mysqli_real_escape_string($conn, $_POST['staffid']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $licenseno = mysqli_real_escape_string($conn, $_POST['licenseno']);
    $registeredno = mysqli_real_escape_string($conn, $_POST['registeredno']);
    $rank = mysqli_real_escape_string($conn, $_POST['rank']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $ssnitno = mysqli_real_escape_string($conn, $_POST['ssnitno']);
    $niano = mysqli_real_escape_string($conn, $_POST['niano']);
    $bank = mysqli_real_escape_string($conn, $_POST['bank']);
    $acno = mysqli_real_escape_string($conn, $_POST['acno']);
    $branch = mysqli_real_escape_string($conn, $_POST['branch']);
    $religion = mysqli_real_escape_string($conn, $_POST['religion']);
    $denomination = mysqli_real_escape_string($conn, $_POST['denomination']);
    $emergencyno = mysqli_real_escape_string($conn, $_POST['emergencyno']);

    // Handle profile picture upload
    $profilepicture = '';
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $target_file = $target_dir . basename($_FILES["profile_pic"]["name"]);
        move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file);
        $profilepicture = $target_file;
    }

    // Check for duplicate staffid
    $result = mysqli_query($conn, "SELECT staffid FROM stafftb WHERE staffid='$staffid' LIMIT 1");
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['staff_error'] = "Staff ID already registered!";
        header("Location: add_staff.php");
        exit();
    }

    // Insert into database (add `email` field)
    $sql = "INSERT INTO stafftb (staffid, name, dob, licenseno, registeredno, rank, phone, email, ssnitno, niano, bank, acno, branch, religion, denomination, emergencyno, profilepicture)
            VALUES ('$staffid', '$name', '$dob', '$licenseno', '$registeredno', '$rank', '$phone', '$email', '$ssnitno', '$niano', '$bank', '$acno', '$branch', '$religion', '$denomination', '$emergencyno', '$profilepicture')";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['staff_registered'] = true;
        header("Location: add_staff.php");
        exit();
    } else {
        $_SESSION['staff_error'] = "Registration failed. Please try again.";
        header("Location: add_staff.php");
        exit();
    }
}

// If editing, fetch staff data
if (isset($_GET['staffid'])) {
    $staffid = mysqli_real_escape_string($conn, $_GET['staffid']);
    $result = mysqli_query($conn, "SELECT * FROM stafftb WHERE staffid='$staffid' LIMIT 1");
    if ($row = mysqli_fetch_assoc($result)) {
        $staff = $row;
    }
}

// Show alert if staff registered successfully
$success_message = '';
if (isset($_SESSION['staff_registered']) && $_SESSION['staff_registered'] === true) {
    $success_message = "Staff successfully registered!";
    unset($_SESSION['staff_registered']);
}

// Show alert if staff id already exists
$error_message = '';
if (isset($_SESSION['staff_error'])) {
    $error_message = $_SESSION['staff_error'];
    unset($_SESSION['staff_error']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="admin-panel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="bootrap" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" crossorigin="anonymous">
    
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff Registration</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/all.min.css">
   <link rel="stylesheet" href="add_staff.css">
    
   <style>
    
       
    </style>
</head>
<body>

<header class="header">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle admin panel">
        <i class="fa fa-bars"></i>
    </button>
    <span class="dashboard-title"><i class="fa fa-gauge"></i> Add Staff</span>
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
            <li><a href="adminhome.php"><i class="fa fa-home"></i> Dashboard</a></li>
            <li class="active"><a href="add_staff.php"><i class="fa fa-user-plus"></i> Add Staff</a></li>
            <li><a href="view_staff.php"><i class="fa fa-users"></i> Staff List</a></li>
        </ul>
    </nav>
    <div style="flex:1;display:flex;justify-content:center;">
        <div class="registration-container">
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fa fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            <div class="registration-title">
                <i class="fa fa-user-plus"></i> Staff Registration Form
            </div>
            <div class="profile-pic-container">
                <img id="profilePreview" class="profile-pic-preview" src="<?php echo !empty($staff['profilepicture']) ? htmlspecialchars($staff['profilepicture']) : 'https://via.placeholder.com/110x110.png?text=Profile'; ?>" alt="Profile Preview">
                <input type="file" class="form-control" name="profile_pic" accept="image/*" style="margin-top:10px;width:110px;" onchange="previewProfilePic(this)">
            </div>
            <form method="post" action="" enctype="multipart/form-data" autocomplete="off">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group input-group">
                            <span class="input-icon"><i class="fa fa-id-card"></i></span>
                            <input type="text" class="form-control" name="staffid" placeholder="Staff ID" value="<?php echo htmlspecialchars($staff['staffid']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group input-group">
                            <span class="input-icon"><i class="fa fa-user"></i></span>
                            <input type="text" class="form-control" name="name" placeholder="Full Name" value="<?php echo htmlspecialchars($staff['name']); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group input-group">
                            <span class="input-icon"><i class="fa fa-calendar"></i></span>
                            <input type="date" class="form-control" name="dob" placeholder="Date of Birth" value="<?php echo htmlspecialchars($staff['dob']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group input-group">
                            <span class="input-icon"><i class="fa fa-id-badge"></i></span>
                            <input type="text" class="form-control" name="licenseno" placeholder="License No." value="<?php echo htmlspecialchars($staff['licenseno']); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group input-group">
                            <span class="input-icon"><i class="fa fa-registered"></i></span>
                            <input type="text" class="form-control" name="registeredno" placeholder="Registered No." value="<?php echo htmlspecialchars($staff['registeredno']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group input-group">
                            <span class="input-icon"><i class="fa fa-user-tie"></i></span>
                            <input type="text" class="form-control" name="rank" placeholder="Rank" value="<?php echo htmlspecialchars($staff['rank']); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="form-section-title"><i class="fa fa-phone"></i> Contact & IDs</div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group input-group">
                            <span class="input-icon"><i class="fa fa-phone"></i></span>
                            <input type="text" class="form-control" name="phone" placeholder="Phone No." value="<?php echo htmlspecialchars($staff['phone']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group input-group">
                            <span class="input-icon"><i class="fa fa-envelope"></i></span>
                            <input type="email" class="form-control" name="email" placeholder="Email" value="<?php echo htmlspecialchars($staff['email']); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group input-group">
                            <span class="input-icon"><i class="fa fa-id-card-clip"></i></span>
                            <input type="text" class="form-control" name="ssnitno" placeholder="SSNIT No." value="<?php echo htmlspecialchars($staff['ssnitno']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group input-group">
                            <span class="input-icon"><i class="fa fa-credit-card"></i></span>
                            <input type="text" class="form-control" name="acno" placeholder="Account No." value="<?php echo htmlspecialchars($staff['acno']); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group input-group">
                            <span class="input-icon"><i class="fa fa-id-card"></i></span>
                            <input type="text" class="form-control" name="niano" placeholder="NIA No." value="<?php echo htmlspecialchars($staff['niano']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group input-group">
                            <span class="input-icon"><i class="fa fa-building-columns"></i></span>
                            <input type="text" class="form-control" name="branch" placeholder="Branch" value="<?php echo htmlspecialchars($staff['branch']); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group input-group">
                            <span class="input-icon"><i class="fa fa-university"></i></span>
                            <input type="text" class="form-control" name="bank" placeholder="Bank" value="<?php echo htmlspecialchars($staff['bank']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group input-group">
                            <span class="input-icon"><i class="fa fa-phone-volume"></i></span>
                            <input type="text" class="form-control" name="emergencyno" placeholder="Emergency No." value="<?php echo htmlspecialchars($staff['emergencyno']); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="form-section-title"><i class="fa fa-praying-hands"></i> Religion & Denomination</div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group input-group">
                            <span class="input-icon"><i class="fa fa-praying-hands"></i></span>
                            <input type="text" class="form-control" name="religion" placeholder="Religion" value="<?php echo htmlspecialchars($staff['religion']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group input-group">
                            <span class="input-icon"><i class="fa fa-church"></i></span>
                            <input type="text" class="form-control" name="denomination" placeholder="Denomination" value="<?php echo htmlspecialchars($staff['denomination']); ?>" required>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Register Staff</button>
            </form>
        </div>
    </div>
</div>
<script>
function previewProfilePic(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
// Sidebar toggle for mobile and landscape
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
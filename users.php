<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

elseif($_SESSION['username']== 'student') {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$db = "bsmsdb";

$data = mysqli_connect($host, $user, $password, $db);
 if (isset($_POST['add_student'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $usertype = $_POST['usertype'];
    $password = $_POST['password'];

    if (empty($id) || empty($username) || empty($usertype) || empty($password)) {
        $alert = "All fields are required.";
    } else {
        // Check if staff ID already exists
        $check = mysqli_prepare($data, "SELECT id FROM user WHERE id = ?");
        mysqli_stmt_bind_param($check, "s", $id);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);
        if (mysqli_stmt_num_rows($check) > 0) {
            $alert = "Staff ID already exists.";
        } else {
            $stmt = mysqli_prepare($data, "INSERT INTO user (id, username, usertype, password) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $id, $username, $usertype, $password);
            if (mysqli_stmt_execute($stmt)) {
                $alert = "User added successfully.";
            } else {
                $alert = "Error adding user: " . mysqli_error($data);
            }
        }
    }
}




?>


<!DOCTYPE html>
<html>
<head>

<style type="text/css">
 
    label
    {
        display: inline-block;
        text-align: right;
        width: 100px;
        padding-top: 10px;
        padding-bottom: 10px;
        color: #FFFFFF;
        font-weight: bold;
    }
    .newuserform
    {
        display: inline-block;
        width: 400px;
        margin: 0 auto;
        padding: 20px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color:rgb(58, 172, 217);
        margin-left:28%;
        margin-top: 50px;
    }
    .adduserbtn
    {
    
    display: flex;
    justify-content: center;
    margin-top: 30px;

    display: flex;
    justify-content: center;
    margin-top: 30px;
}
.form-alert {
    color: #d32f2f;
    margin-top: 10px;
    margin-bottom: 0;
    text-align: center;
    font-weight: bold;
}



</style>

    
  



    <link rel="stylesheet" type="text/css" href="admin.css">
       <!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfHhZ9lFQ775/xQ1rZcF5Q6g9g6r5P6G6r5P6G6r5" crossorigin="anonymous"></script>

 <body>

</head>

<body>
    <!-- Place this at the top of your page -->
<link rel="stylesheet" href="admin-panel.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<header class="header">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle admin panel">
        <i class="fa fa-bars"></i>
    </button>
        <span class="dashboard-title" style="margin: 0 auto; display: flex; align-items: center; justify-content: center; flex: 1; text-align: center;">
        <i class="fa fa-user"></i>
        <span style="margin-left: 8px;">Add Users</span>
    </span>
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
        <li><a href="management.php"><i class="fa fa-cogs"></i> User Management</a></li>
        <li><a href="viewusers.php"><i class="fa fa-user-plus"></i> View Users</a></li>
        
    </ul>
</nav>

<div class="newuserform">
    <form method="post" action="">
    <div>
        <label>Staff ID.:</label>
        <input type="text" name="id" required>
    </div>
    <div>
        <label>Username:</label>
        <input type="text" name="username" required>
    </div>
    <div>
        <label>User Type:</label>
        <select name="usertype" required>
            <option value="">Select type</option>
            <option value="admin">Admin</option>
            <option value="teacher">Teacher</option>
            <option value="accountant">Accountant</option>
            <option value="parents">parents</option>
        </select>
    </div>
    <div>
        <label>Password:</label>
        <input type="password" name="password" required>
    </div>
    <?php if (!empty($_SESSION['alert'])): ?>
    <div class="form-alert"><?php echo htmlspecialchars($_SESSION['alert']); ?></div>
    <?php unset($_SESSION['alert']); ?>
<?php endif; ?>
    <div class="adduserbtn">
        <input type="submit" name="add_student" value="Add User" class="btn btn-primary">
    </div>
      
</form>

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
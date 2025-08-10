<!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8">
    <title>login form</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfHhZ9lFQ775/xQ1rZcF5Q6g9g6r5P6G6r5P6G6r5" crossorigin="anonymous"></script>
    </head>
<body background="https://images.unsplash.com/photo-1506748686214-e9df14d4d9d0?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80"; class="body_deg">
     

<center>
    <img class="loginlogo" src="loginlogo.jpg">
</center>



</div>
   
        <div class="form_deg">

        <h4 class="error">
            <?php
            error_reporting(0);
            session_start();
            session_destroy();
            echo $_SESSION['LoginMessage'];

            ?>
        </h4>
     
            <form action="login_check.php" method="POST" class="login_form"> 
    <div>
        <label class="label_deg">Staff ID</label>
        <input type="text" name="id" placeholder="Enter your staff ID" required>
    </div>
    <div>
        <label class="label_deg">Password</label>
        <input type="password" name="password" placeholder="Enter your password" required>
    </div>
    <div>
        <input class="btn btn-primary" type="submit" name="submit" value="login">
    </div>
</form>

          </div>
    

   
   









    </body>
    </html>
<?php

error_reporting(0);
session_start();

$host = "localhost";
$user = "root";
$password = "";
$db = "bsmsdb";

$data = mysqli_connect($host, $user, $password, $db);

if ($data === false) {
    die("Check database connection");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $pass = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($id) || empty($pass)) {
        $_SESSION['LoginMessage'] = "Staff ID or password is missing.";
        header("Location: login.php");
        exit();
    }

    $stmt = mysqli_prepare($data, "SELECT * FROM user WHERE id = ? AND password = ?");
    mysqli_stmt_bind_param($stmt, "ss", $id, $pass);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $row = mysqli_fetch_array($result);

    if ($row) {
        if ($row["usertype"] == "teacher") {
            $_SESSION['username'] = $id; 
            $_SESSION['usertype'] = $row["usertype"];
            header("Location: teacherhome.php");
            exit();
        } elseif ($row["usertype"] == "admin") {
            $_SESSION['username'] = $id;
            $_SESSION['usertype'] = $row["usertype"];
            header("Location: adminhome.php");
            exit();

        } elseif ($row["usertype"] == "accountant") {
            $_SESSION['username'] = $id;
            $_SESSION['usertype'] = $row["usertype"];
            header("Location: fees.php");
            exit();
             } elseif ($row["usertype"] == "parents") {
            $_SESSION['username'] = $id;
            $_SESSION['usertype'] = $row["usertype"];
            header("Location: parents_portal.php");
            exit();
        } else {
            $_SESSION['LoginMessage'] = "Wrong user type.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['LoginMessage'] = "Wrong Staff ID or password.";
        header("Location: login.php");
        exit();
    }
}
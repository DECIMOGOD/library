<?php
session_start();
error_reporting(0);
include('includes/config.php');

// Clear session if already logged in
if ($_SESSION['login'] != '') {
    $_SESSION['login'] = '';
}

// Check if login button is pressed
if (isset($_POST['login'])) {

    // Get input data
    $lrn = $_POST['lrn'];
    $password = md5($_POST['password']);

    // SQL query to check login credentials
    $sql = "SELECT LRN, Password, id, Status FROM tblstudents WHERE LRN=:lrn and Password=:password";
    $query = $dbh->prepare($sql);
    $query->bindParam(':lrn', $lrn, PDO::PARAM_STR);
    $query->bindParam(':password', $password, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    // Check if user exists
    if ($query->rowCount() > 0) {
        foreach ($results as $result) {
            $_SESSION['stdid'] = $result->id;
            if ($result->Status == 1) {
                $_SESSION['login'] = $_POST['lrn'];
                echo "<script type='text/javascript'> document.location ='dashboard.php'; </script>";
            } else {
                echo "<script>alert('Your Account Has been blocked. Please contact admin');</script>";
            }
        }
    } else {
        echo "<script>alert('Invalid Details');</script>";
    }
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>User Login | Library Management System</title>
    <!-- BOOTSTRAP CORE STYLE -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FONT AWESOME STYLE -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- CUSTOM STYLE -->
    <link href="assets/css/style.css" rel="stylesheet" />
    <!-- LOGIN PAGE SPECIFIC STYLE -->
    <link href="assets/css/login-style.css" rel="stylesheet" />
    <!-- GOOGLE FONT -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
</head>
<body class="login-page-background">
    <div class="login-wrapper">
        <div class="container">
            <div class="back-link">
                <a href="index.php"><i class="fa fa-arrow-left"></i> Back to Home</a>
            </div>
            
            <div class="login-container">
                <div class="logo-container">
                    <img src="assets/img/logo.png" alt="Library Management System Logo" class="login-logo">
                </div>
                
                <div class="login-header">
                    <h2>Library Management System</h2>
                    <h4>User Login</h4>
                </div>
                
                <div class="login-form">
                    <form role="form" method="post">
                        <div class="form-group">
                            <label for="lrn"><i class="fa fa-user"></i> Enter LRN</label>
                            <input class="form-control" type="text" name="lrn" id="lrn" required autocomplete="off" placeholder="Enter your LRN number" />
                        </div>
                        <div class="form-group">
                            <label for="password"><i class="fa fa-lock"></i> Password</label>
                            <input class="form-control" type="password" name="password" id="password" required autocomplete="off" placeholder="Enter your password" />
                        </div>
                        
                        <div class="login-footer">
                            <button type="submit" name="login" class="btn login-btn">LOGIN</button>
                            <div class="login-links">
                                <a href="user-forgot-password.php">Forgot Password?</a>
                                <span class="divider">|</span>
                                <a href="signup.php">Register New Account</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- SCRIPTS -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.js"></script>
    <script src="assets/js/custom.js"></script>
</body>
</html>
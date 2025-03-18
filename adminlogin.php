<?php
session_start();
error_reporting(0);
include('includes/config.php');
if ($_SESSION['alogin'] != '') {
    $_SESSION['alogin'] = '';
}
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    $sql = "SELECT UserName, Password FROM admin WHERE UserName=:username and Password=:password";
    $query = $dbh->prepare($sql);
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->bindParam(':password', $password, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    if ($query->rowCount() > 0) {
        $_SESSION['alogin'] = $_POST['username'];
        echo "<script type='text/javascript'> document.location ='admin/dashboard.php'; </script>";
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
    <title>Admin Login | Library Management System</title>
    <!-- BOOTSTRAP CORE STYLE -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FONT AWESOME STYLE -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- CUSTOM STYLE -->
    <link href="assets/css/style.css" rel="stylesheet" />
    <!-- GOOGLE FONT -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
</head>
<body class="admin-login-page-background">
    <div class="admin-login-wrapper">
        <div class="container">
            <div class="admin-login-back-link">
                <a href="index.php"><i class="fa fa-arrow-left"></i> Back to Home</a>
            </div>
            
            <div class="admin-login-container">
                <div class="admin-login-logo-container">
                    <img src="assets/img/logo.png" alt="Library Management System Logo" class="admin-login-logo">
                </div>
                
                <div class="admin-login-header">
                    <h2>Library Management System</h2>
                    <h4>Admin Login</h4>
                </div>
                
                <div class="admin-login-form">
                    <form role="form" method="post">
                        <div class="admin-login-form-group">
                            <label for="username"><i class="fa fa-user"></i> Enter Username</label>
                            <input class="admin-login-form-control" type="text" name="username" id="username" required autocomplete="off" placeholder="Enter your username" />
                        </div>
                        
                        <div class="admin-login-form-group">
                            <label for="password"><i class="fa fa-lock"></i> Password</label>
                            <input class="admin-login-form-control" type="password" name="password" id="password" required autocomplete="off" placeholder="Enter your password" />
                        </div>
                        
                        <div class="admin-login-footer">
                            <button type="submit" name="login" class="btn admin-login-btn">LOGIN</button>
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
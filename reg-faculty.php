<?php 
session_start();
include('includes/config.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['signup'])) {
    $role = isset($_POST['role']) ? trim($_POST['role']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $faculty_id = isset($_POST['faculty_id']) ? trim($_POST['faculty_id']) : '';
    $password = md5($_POST['password']);
    $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
    $contact_number = isset($_POST['contact_number']) ? trim($_POST['contact_number']) : '';
    $department = isset($_POST['department']) ? trim($_POST['department']) : '';
    $date_hired = isset($_POST['date_hired']) ? trim($_POST['date_hired']) : '';
    $status = 0; // Set status to 0 (pending) for new faculty registrations

    if ($role === 'Faculty') {
        // Check if username or faculty ID already exists
        $sql_check = "SELECT username, faculty_id FROM tblfaculty WHERE username = :username OR faculty_id = :faculty_id";
        $query_check = $dbh->prepare($sql_check);
        $query_check->bindParam(':username', $username, PDO::PARAM_STR);
        $query_check->bindParam(':faculty_id', $faculty_id, PDO::PARAM_STR);
        $query_check->execute();

        if ($query_check->rowCount() > 0) {
            $_SESSION['sweetalert'] = [
                'icon' => 'error',
                'title' => 'Registration Failed',
                'text' => 'Username or Faculty ID already exists! Please use different credentials.'
            ];
            header("Location: reg-faculty.php");
            exit();
        } else {
            $sql = "INSERT INTO tblfaculty (username, faculty_id, password, fullname, contact_number, department, date_hired, status) 
                    VALUES (:username, :faculty_id, :password, :fullname, :contact_number, :department, :date_hired, :status)";

            $query = $dbh->prepare($sql);
            $query->bindParam(':username', $username, PDO::PARAM_STR);
            $query->bindParam(':faculty_id', $faculty_id, PDO::PARAM_STR);
            $query->bindParam(':password', $password, PDO::PARAM_STR);
            $query->bindParam(':fullname', $fullname, PDO::PARAM_STR);
            $query->bindParam(':contact_number', $contact_number, PDO::PARAM_STR);
            $query->bindParam(':department', $department, PDO::PARAM_STR);
            $query->bindParam(':date_hired', $date_hired, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_INT);

            if ($query->execute()) {
                $_SESSION['sweetalert'] = [
                    'icon' => 'success',
                    'title' => 'Registration Successful!',
                    'text' => 'Your registration was successful!',
                    'redirect' => 'login.php'
                ];
                header("Location: reg-faculty.php");
                exit();
            } else {
                $_SESSION['sweetalert'] = [
                    'icon' => 'error',
                    'title' => 'Registration Failed',
                    'text' => 'Something went wrong. Please try again.'
                ];
                header("Location: reg-faculty.php");
                exit();
            }
        }
    } else {
        $_SESSION['sweetalert'] = [
            'icon' => 'error',
            'title' => 'Invalid Role',
            'text' => 'Only Faculty registration is allowed on this page.'
        ];
        header("Location: reg-faculty.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Faculty Registration | Library Management System</title>
    <!-- BOOTSTRAP CORE STYLE -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FONT AWESOME STYLE -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- CUSTOM STYLE -->
    <link href="assets/css/style.css" rel="stylesheet" />
    <!-- GOOGLE FONT -->
    <link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .swal2-popup {
            font-family: 'Open Sans', sans-serif;
        }
    </style>
</head>
<body class="signup-page-background">
    <?php
    if (isset($_SESSION['sweetalert'])) {
        $alert = $_SESSION['sweetalert'];
        unset($_SESSION['sweetalert']);
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: "'.$alert['icon'].'",
                    title: "'.$alert['title'].'",
                    text: "'.$alert['text'].'",
                    confirmButtonColor: "#3085d6",
                })'.(isset($alert['redirect']) ? '.then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "'.$alert['redirect'].'";
                    }
                })' : '').';
            });
        </script>';
    }
    ?>
    
    <div class="signup-wrapper">
        <div class="container">
            <div class="signup-back-link">
                <a href="index.php"><i class="fa fa-arrow-left"></i> Back to Home</a>
            </div>
            
            <div class="signup-container">
                <div class="signup-logo-container">
                    <img src="assets/img/logo.png" alt="Library Management System Logo" class="signup-logo">
                </div>
                
                <div class="signup-header">
                    <h2>Library Management System</h2>
                    <h4>Facuty Registration</h4>
                </div>
                
                <div class="signup-form">
                    <form role="form" method="post" id="signupForm">
                        <div class="signup-form-group">
                            <label for="role"><i class="fa fa-user-tag"></i> Role</label>
                            <select class="signup-form-control" name="role" id="role" required>
                                <option value="">Select Role</option>
                                <option value="Faculty">Faculty</option>
                                <option value="Student">Student</option>
                            </select>
                        </div>
                        
                        <div class="signup-form-group">
                            <label for="username"><i class="fa fa-user"></i> Username</label>
                            <input class="signup-form-control" type="text" name="username" id="username" required autocomplete="off" placeholder="Enter your username" />
                        </div>
                        
                        <div class="signup-form-group">
                            <label for="faculty_id"><i class="fa fa-id-card"></i> Faculty ID</label>
                            <input class="signup-form-control" type="text" name="faculty_id" id="faculty_id" required autocomplete="off" placeholder="Enter your faculty ID" />
                        </div>
                        
                        <div class="signup-form-group">
                            <label for="password"><i class="fa fa-lock"></i> Password</label>
                            <input class="signup-form-control" type="password" name="password" id="password" required autocomplete="off" placeholder="Create a strong password" minlength="8" />
                            <small class="form-text text-muted">Minimum 8 characters</small>
                        </div>
                        
                        <div class="signup-form-group">
                            <label for="fullname"><i class="fa fa-user"></i> Full Name</label>
                            <input class="signup-form-control" type="text" name="fullname" id="fullname" required autocomplete="off" placeholder="Enter your full name" />
                        </div>
                        
                        <div class="signup-form-group">
                            <label for="contact_number"><i class="fa fa-phone"></i> Contact Number</label>
                            <input class="signup-form-control" type="text" name="contact_number" id="contact_number" required autocomplete="off" placeholder="Enter your contact number" />
                        </div>
                        
                        <div class="signup-form-group">
                            <label for="department"><i class="fa fa-building"></i> Department</label>
                            <select class="signup-form-control" name="department" id="department" required>
                                <option value="">Select Department</option>
                                <option value="Junior High">Junior High</option>
                                <option value="Senior High">Senior High</option>
                            </select>
                        </div>
                        
                        <div class="signup-form-group">
                            <label for="date_hired"><i class="fa fa-calendar"></i> Date Hired</label>
                            <input class="signup-form-control" type="date" name="date_hired" id="date_hired" required />
                        </div>
                        
                        <div class="signup-footer">
                            <button type="submit" name="signup" class="btn signup-btn">REGISTER</button>
                            <div class="signup-links">
                                <span>Already have an account?</span>
                                <a href="login.php">Login here</a>
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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>

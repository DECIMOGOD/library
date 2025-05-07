<?php 
session_start();
include('includes/config.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(isset($_POST['signup']))
{
    $lrn = isset($_POST['lrn']) ? trim($_POST['lrn']) : '';  
    $fname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';  
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $department = isset($_POST['department']) ? trim($_POST['department']) : '';
    $grade_level = isset($_POST['grade_level']) ? trim($_POST['grade_level']) : '';
    $section = isset($_POST['section']) ? trim($_POST['section']) : '';
    $strand = ($department == "Senior High") ? trim($_POST['strand']) : '';
    $password = md5($_POST['password']); 
    $status = 0; // Set status to 0 (pending) for new student registrations

    if (!preg_match('/^\d{12}$/', $lrn)) {
        $_SESSION['sweetalert'] = [
            'icon' => 'error',
            'title' => 'Invalid LRN!',
            'text' => 'LRN must be exactly 12 digits and contain only numbers.'
        ];
        header("Location: signup.php");
        exit();
    }

    $sql_check = "SELECT LRN FROM tblstudents WHERE LRN = :lrn";
    $query_check = $dbh->prepare($sql_check);
    $query_check->bindParam(':lrn', $lrn, PDO::PARAM_STR);
    $query_check->execute();

    if ($query_check->rowCount() > 0) {
        $_SESSION['sweetalert'] = [
            'icon' => 'error',
            'title' => 'Registration Failed',
            'text' => 'This LRN is already registered! Please use a different LRN.'
        ];
        header("Location: signup.php");
        exit();
    } else {
        $sql = "INSERT INTO tblstudents (LRN, Name, Address, Department, Grade_Level, Section, Strand, Password, Status) 
                VALUES (:lrn, :fname, :address, :department, :grade_level, :section, :strand, :password, :status)";

        $query = $dbh->prepare($sql);
        $query->bindParam(':lrn', $lrn, PDO::PARAM_STR);
        $query->bindParam(':fname', $fname, PDO::PARAM_STR);
        $query->bindParam(':address', $address, PDO::PARAM_STR);
        $query->bindParam(':department', $department, PDO::PARAM_STR);
        $query->bindParam(':grade_level', $grade_level, PDO::PARAM_STR);
        $query->bindParam(':section', $section, PDO::PARAM_STR);
        $query->bindParam(':strand', $strand, PDO::PARAM_STR);
        $query->bindParam(':password', $password, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_INT);

        if ($query->execute()) {
            $_SESSION['sweetalert'] = [
                'icon' => 'success',
                'title' => 'Registration Successful!',
                'html' => 'Your registration is pending approval by the admin.<br><br>Your LRN is: <strong>'.$lrn.'</strong>',
                'redirect' => 'login.php'
            ];
            header("Location: signup.php");
            exit();
        } else {
            $_SESSION['sweetalert'] = [
                'icon' => 'error',
                'title' => 'Registration Failed',
                'text' => 'Something went wrong. Please try again.'
            ];
            header("Location: signup.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>User Signup | Library Management System</title>
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
    if(isset($_SESSION['sweetalert'])) {
        $alert = $_SESSION['sweetalert'];
        unset($_SESSION['sweetalert']);
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: "'.$alert['icon'].'",
                    title: "'.$alert['title'].'",
                    '. (isset($alert['html']) ? 'html: `'.$alert['html'].'`' : 'text: "'.$alert['text'].'"') .',
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
                    <h4>User Signup</h4>
                </div>
                
                <div class="signup-form">
                    <form role="form" method="post" id="signupForm">
                        <div class="signup-form-group">
                            <label for="lrn"><i class="fa fa-id-card"></i> Enter LRN</label>
                            <input class="signup-form-control" type="text" name="lrn" id="lrn" required autocomplete="off" 
                                   placeholder="Enter your LRN number" pattern="\d{12}" 
                                   title="LRN must be exactly 12 digits and contain only numbers" 
                                   maxlength="12" oninput="this.value = this.value.replace(/\D/g, '')" />
                        </div>
                        
                        <div class="signup-form-group">
                            <label for="fullname"><i class="fa fa-user"></i> Full Name</label>
                            <input class="signup-form-control" type="text" name="fullname" id="fullname" required autocomplete="off" placeholder="Enter your full name" />
                        </div>
                        
                        <div class="signup-form-group">
                            <label for="address"><i class="fa fa-home"></i> Address</label>
                            <input class="signup-form-control" type="text" name="address" id="address" autocomplete="off" placeholder="Enter your address" />
                        </div>
                        
                        <div class="signup-form-group">
                            <label for="department"><i class="fa fa-building"></i> Department</label>
                            <select class="signup-form-control" name="department" id="department" onchange="toggleStrand(); updateGradeLevels();" required>
                                <option value="">Select Department</option>
                                <option value="Junior High">Junior High</option>
                                <option value="Senior High">Senior High</option>
                            </select>
                        </div>
                        
                        <div class="signup-form-group" id="strandField" style="display: none;">
                            <label for="strand"><i class="fa fa-graduation-cap"></i> Strand</label>
                            <select class="signup-form-control" name="strand" id="strand">
                                <option value="">Select Strand</option>
                                <option value="STEM">STEM</option>
                                <option value="HUMSS">HUMSS</option>
                                <option value="ABM">ABM</option>
                                <option value="ICT">ICT</option>
                                <option value="GAS">HE</option>
                            </select>
                        </div>
                        
                        <div class="signup-form-group">
                            <label for="grade_level"><i class="fa fa-book"></i> Grade Level</label>
                            <select class="signup-form-control" name="grade_level" id="grade_level" required>
                                <option value="">Select Grade Level</option>
                            </select>
                        </div>
                        
                        <div class="signup-form-group">
                            <label for="section"><i class="fa fa-users"></i> Section</label>
                            <input class="signup-form-control" type="text" name="section" id="section" required autocomplete="off" placeholder="Enter your section" />
                        </div>
                        
                        <div class="signup-form-group">
                            <label for="password"><i class="fa fa-lock"></i> Password</label>
                            <input class="signup-form-control" type="password" name="password" id="password" required autocomplete="off" placeholder="Create a strong password" minlength="8" />
                            <small class="form-text text-muted">Minimum 8 characters</small>
                        </div>
                        
                        <div class="signup-form-group">
                            <label for="confirmpassword"><i class="fa fa-lock"></i> Confirm Password</label>
                            <input class="signup-form-control" type="password" name="confirmpassword" id="confirmpassword" required autocomplete="off" placeholder="Re-enter your password" minlength="8" />
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
    <script>
        // Password matching validation
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            var password = document.getElementById('password').value;
            var confirmPassword = document.getElementById('confirmpassword').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Password Mismatch',
                    text: 'Your passwords do not match. Please try again.',
                    confirmButtonColor: '#3085d6',
                });
                return false;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Weak Password',
                    text: 'Password must be at least 8 characters long.',
                    confirmButtonColor: '#3085d6',
                });
                return false;
            }
            
            return true;
        });

        function toggleStrand() {
            var department = document.getElementById("department").value;
            var strandField = document.getElementById("strandField");
            var strandSelect = document.getElementById("strand");

            if (department === "Senior High") {
                strandField.style.display = "block";
                strandSelect.setAttribute("required", "required");
            } else {
                strandField.style.display = "none";
                strandSelect.removeAttribute("required");
            }
        }

        function updateGradeLevels() {
            var department = document.getElementById("department").value;
            var gradeLevelDropdown = document.getElementById("grade_level");

            gradeLevelDropdown.innerHTML = '<option value="">Select Grade Level</option>';

            var grades = [];
            if (department === "Junior High") {
                grades = [7, 8, 9, 10];
            } else if (department === "Senior High") {
                grades = [11, 12];
            }

            grades.forEach(function(grade) {
                var option = document.createElement("option");
                option.value = "Grade " + grade;
                option.textContent = "Grade " + grade;
                gradeLevelDropdown.appendChild(option);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateGradeLevels();
            
            // Check if there's a hash in the URL indicating a redirect from form submission
            if(window.location.hash === '#submitted') {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>
</body>
</html>
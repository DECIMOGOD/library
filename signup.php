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
    $strand = ($department == "Senior High") ? trim($_POST['strand']) : ''; // Only required for Senior High
    $password = md5($_POST['password']); 
    $status = 1;

    // Backend Validation: Ensure LRN is exactly 12 digits
    if (!preg_match('/^\d{12}$/', $lrn)) {
        echo '<script>alert("Invalid LRN! It must be exactly 12 digits and contain only numbers.");</script>';
        exit(); // Stop execution if LRN is invalid
    }

    // Check if LRN already exists
    $sql_check = "SELECT LRN FROM tblstudents WHERE LRN = :lrn";
    $query_check = $dbh->prepare($sql_check);
    $query_check->bindParam(':lrn', $lrn, PDO::PARAM_STR);
    $query_check->execute();

    if ($query_check->rowCount() > 0) {
        echo '<script>alert("This LRN is already registered! Please use a different LRN.");</script>';
    } else {
        // Insert the new student record
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
            echo '<script>alert("Your registration was successful! Your LRN is ' . $lrn . '");</script>';
        } else {
            echo '<script>alert("Something went wrong. Please try again.");</script>';
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
    <link href='http://fonts.googleapis.com/css?familys=Open+Sans' rel='stylesheet' type='text/css' />
</head>
<body class="signup-page-background">
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
                    <form role="form" method="post" onSubmit="return valid();">
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
                            <label for="password"><i class="fa fa-lock"></i> Password</label>
                            <input class="signup-form-control" type="password" name="password" id="password" required autocomplete="off" placeholder="Create a strong password" />
                        </div>
                        
                        <div class="signup-form-group">
                            <label for="confirmpassword"><i class="fa fa-lock"></i> Confirm Password</label>
                            <input class="signup-form-control" type="password" name="confirmpassword" id="confirmpassword" required autocomplete="off" placeholder="Re-enter your password" />
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
    <script>
        function toggleStrand() {
            var department = document.getElementById("department").value;
            var strandField = document.getElementById("strandField");

            if (department === "Senior High") {
                strandField.style.display = "block";
            } else {
                strandField.style.display = "none";
            }
        }

        function updateGradeLevels() {
            var department = document.getElementById("department").value;
            var gradeLevelDropdown = document.getElementById("grade_level");

            // Clear existing options
            gradeLevelDropdown.innerHTML = '<option value="">Select Grade Level</option>';

            var grades = [];
            if (department === "Junior High") {
                grades = [7, 8, 9, 10];
            } else if (department === "Senior High") {
                grades = [11, 12];
            }

            // Add new options dynamically
            grades.forEach(function(grade) {
                var option = document.createElement("option");
                option.value = "Grade " + grade;
                option.textContent = "Grade " + grade;
                gradeLevelDropdown.appendChild(option);
            });
        }
    </script>
</body>
</html>
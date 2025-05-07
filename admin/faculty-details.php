<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database configuration file
include('includes/config.php');

// Check if the user is logged in
if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit;
}

// Get the faculty ID from the query string
if (isset($_GET['fid'])) {
    $faculty_id = intval($_GET['fid']);

    // Fetch faculty details
    $sql = "SELECT * FROM tblfaculty WHERE id = :faculty_id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':faculty_id', $faculty_id, PDO::PARAM_INT);
    $query->execute();
    $faculty = $query->fetch(PDO::FETCH_OBJ);

    // If no faculty found, redirect back
    if (!$faculty) {
        echo "<script>alert('Faculty not found.'); window.location.href='reg-students.php';</script>";
        exit;
    }
} else {
    header('location:reg-students.php');
    exit;
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Faculty Details | Library Management System</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
</head>
<body>
    <?php include('includes/header.php'); ?>
    <div class="content-wrapper">
        <div class="container">
            <div class="row pad-botm">
                <div class="col-md-12">
                    <h4 class="header-line">Faculty Details</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="panel panel-info">
                        <div class="panel-heading" style="color: white; background-color: #007bff;">Faculty Information</div>
                        <div class="panel-body" style="color: white; background-color: #1e293b;"> <!-- Changed background color -->
                            <table class="table table-striped" style="color: white;">
                                <tr>
                                    <th style="color: white;">Faculty ID</th>
                                    <td style="color: white;"><?php echo htmlentities($faculty->faculty_id ?? 'Not Provided'); ?></td>
                                </tr>
                                <tr>
                                    <th style="color: white;">Full Name</th>
                                    <td style="color: white;"><?php echo htmlentities($faculty->fullname ?? 'Not Provided'); ?></td>
                                </tr>
                                <tr>
                                    <th style="color: white;">Department</th>
                                    <td style="color: white;"><?php echo htmlentities($faculty->department ?? 'Not Provided'); ?></td>
                                </tr>
                                <tr>
                                    <th style="color: white;">Email</th>
                                    <td style="color: white;"><?php echo htmlentities($faculty->email ?? 'Not Provided'); ?></td>
                                </tr>
                                <tr>
                                    <th style="color: white;">Contact Number</th>
                                    <td style="color: white;"><?php echo htmlentities($faculty->contact_number ?? 'Not Provided'); ?></td>
                                </tr>
                                <tr>
                                    <th style="color: white;">Registration Date</th>
                                    <td style="color: white;"><?php echo htmlentities($faculty->reg_date ?? 'Not Provided'); ?></td>
                                </tr>
                                <tr>
                                    <th style="color: white;">Status</th>
                                    <td style="color: white;"><?php echo $faculty->Status == 1 ? "Active" : "Inactive"; ?></td>
                                </tr>
                            </table>
                            <a href="reg-students.php" class="btn btn-primary">Back to Reg Faculty</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include('includes/footer.php'); ?>
    <script src="assets/js/bootstrap.js"></script>
    <script src="assets/js/jquery-1.10.2.js"></script>
</body>
</html>

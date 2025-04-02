<?php
// MUST be the very first line - no whitespace before!
session_start();

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('includes/config.php');

// 1. Validate session and LRN
if (!isset($_SESSION['LRN']) || empty($_SESSION['LRN'])) {
    header("Location: index.php?error=no_session");
    exit();
}

$lrn = $_SESSION['LRN'];

// 2. Fetch student data with error handling
try {
    $sql = "SELECT Name, RegDate, UpdationDate, Status FROM tblstudents WHERE LRN = :lrn";
    $query = $dbh->prepare($sql);
    $query->bindParam(':lrn', $lrn, PDO::PARAM_STR);
    $query->execute();
    
    $student = $query->fetch(PDO::FETCH_OBJ);
    
    if (!$student) {
        throw new Exception("No student found with LRN: $lrn");
    }
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
} catch (Exception $e) {
    die($e->getMessage());
}

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $fullname = trim($_POST['fullname']);
    
    if (empty($fullname)) {
        $error = "Full name cannot be empty";
    } else {
        try {
            $update_sql = "UPDATE tblstudents SET 
                          Name = :fullname,
                          UpdationDate = CURRENT_TIMESTAMP
                          WHERE LRN = :lrn";
            
            $update_query = $dbh->prepare($update_sql);
            $update_query->bindParam(':fullname', $fullname, PDO::PARAM_STR);
            $update_query->bindParam(':lrn', $lrn, PDO::PARAM_STR);
            
            if ($update_query->execute()) {
                $success = "Profile updated successfully!";
                // Refresh student data
                $query->execute();
                $student = $query->fetch(PDO::FETCH_OBJ);
            }
        } catch (PDOException $e) {
            $error = "Update failed: " . $e->getMessage();
        }
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
    <title>Online Library Management System | My Profile</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
    <style>
        .form-control-static {
            padding-top: 7px;
            margin-bottom: 0;
        }
        .panel-body {
            padding: 20px;
        }
    </style>
</head>
<body>
    <?php include('includes/header.php');?>

    <div class="content-wrapper">
        <div class="container">
            <div class="row pad-botm">
                <div class="col-md-12">
                    <h4 class="header-line">My Profile</h4>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-9 col-md-offset-1">
                    <div class="panel panel-danger">
                        <div class="panel-heading">
                            <i class="fa fa-user"></i> My Profile Information
                        </div>
                        <div class="panel-body">
                            <form method="post" action="">
                                <div class="form-group">
                                    <label>LRN:</label>
                                    <p class="form-control-static">
                                        <?php echo htmlspecialchars($lrn); ?>
                                    </p>
                                </div>

                                <div class="form-group">
                                    <label>Full Name:</label>
                                    <input type="text" class="form-control" name="fullname" 
                                           value="<?php echo htmlspecialchars($student->Name); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label>Registration Date & Time:</label>
                                    <p class="form-control-static">
                                        <?php 
                                        if (!empty($student->RegDate)) {
                                            echo date('F j, Y \a\t g:i A', strtotime($student->RegDate));
                                        } else {
                                            echo 'Not available';
                                        }
                                        ?>
                                    </p>
                                </div>

                                <?php if(!empty($student->UpdationDate)): ?>
                                <div class="form-group">
                                    <label>Last Updated:</label>
                                    <p class="form-control-static">
                                        <?php echo date('F j, Y \a\t g:i A', strtotime($student->UpdationDate)); ?>
                                    </p>
                                </div>
                                <?php endif; ?>

                                <div class="form-group">
                                    <label>Account Status:</label>
                                    <p class="form-control-static">
                                        <?php echo ($student->Status == 1) ? 
                                            '<span class="text-success"><i class="fa fa-check-circle"></i> Active</span>' : 
                                            '<span class="text-danger"><i class="fa fa-times-circle"></i> Blocked</span>'; ?>
                                    </p>
                                </div>

                                <div class="form-group">
                                    <button type="submit" name="update" class="btn btn-primary">
                                        <i class="fa fa-save"></i> Update Profile
                                    </button>
                                    <a href="dashboard.php" class="btn btn-default">
                                        <i class="fa fa-arrow-left"></i> Back to Dashboard
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.js"></script>
    <script src="assets/js/custom.js"></script>
</body>
</html>
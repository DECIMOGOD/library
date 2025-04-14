<?php
session_start();
error_reporting(E_ALL); // Fix 1: Change error reporting to show all errors for debugging
include('includes/config.php');

if(strlen($_SESSION['login'])==0) { 
    header('location:index.php');
    exit(); // Fix 2: Add exit after header to prevent further script execution
} else { 
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Online Library Management System | User Dashboard</title>
    <!-- BOOTSTRAP CORE STYLE -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FONT AWESOME STYLE -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- CUSTOM STYLE -->
    <link href="assets/css/style.css" rel="stylesheet" />
    <!-- GOOGLE FONT -->
    <link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' /> <!-- Fix 3: Change http to https -->
</head>
<body>
    <!-- MENU SECTION START -->
    <?php include('includes/header.php'); ?>
    <!-- MENU SECTION END -->

    <div class="content-wrapper">
        <div class="container">
            <div class="row pad-botm">
                <div class="col-md-12">
                    <h4 class="header-line">User DASHBOARD</h4>
                </div>
            </div>

            <div class="row">
                <a href="listed-books.php">
                    <div class="col-md-4 col-sm-4 col-xs-6">
                        <div class="alert alert-success back-widget-set text-center">
                            <i class="fa fa-book fa-5x"></i>
                            <?php 
                            $sql = "SELECT id FROM tblbooks";
                            $query = $dbh->prepare($sql);
                            $query->execute();
                            $listdbooks = $query->rowCount();
                            ?>
                            <h3><?php echo htmlentities($listdbooks); ?></h3>
                            Books Listed
                        </div>
                    </div>
                </a>

                <div class="col-md-4 col-sm-4 col-xs-6">
                    <div class="alert alert-warning back-widget-set text-center">
                        <i class="fa fa-recycle fa-5x"></i>
                        <?php 
                        $rsts = 0;
                        // Fix 4: Use proper session variable for LRN
                        $lrn = isset($_SESSION['login']) ? $_SESSION['login'] : '';
                        $sql2 = "SELECT id FROM tblissuedbookdetails WHERE LRN = :lrn AND (ReturnStatus = :rsts OR ReturnStatus IS NULL OR ReturnStatus = '')";
                        $query2 = $dbh->prepare($sql2);
                        $query2->bindParam(':lrn', $lrn, PDO::PARAM_STR);
                        $query2->bindParam(':rsts', $rsts, PDO::PARAM_INT);
                        $query2->execute();
                        $returnedbooks = $query2->rowCount();
                        ?>
                        <h3><?php echo htmlentities($returnedbooks); ?></h3>
                        Books Not Returned Yet
                    </div>
                </div>

                <?php 
                // Fix 5: Add error handling for database query
                try {
                    $ret = $dbh->prepare("SELECT id FROM tblissuedbookdetails WHERE LRN = :lrn");
                    $ret->bindParam(':lrn', $lrn, PDO::PARAM_STR);
                    $ret->execute();
                    $totalissuedbook = $ret->rowCount();
                } catch(PDOException $e) {
                    // Log error or handle it appropriately
                    $totalissuedbook = 0;
                    error_log("Database error: " . $e->getMessage());
                }
                ?>

                <a href="issued-books.php">
                    <div class="col-md-4 col-sm-4 col-xs-6">
                        <div class="alert alert-success back-widget-set text-center">
                            <i class="fa fa-book fa-5x"></i>
                            <h3><?php echo htmlentities($totalissuedbook); ?></h3>
                            Total Issued Books
                        </div>
                    </div>
                </a>
            </div>    
        </div>
    </div>
    <!-- CONTENT-WRAPPER SECTION END -->

   
    <!-- FOOTER SECTION END -->

    <!-- JAVASCRIPT FILES PLACED AT THE BOTTOM TO REDUCE LOADING TIME -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.js"></script>
    <script src="assets/js/custom.js"></script>
</body>
</html>
<?php } ?>
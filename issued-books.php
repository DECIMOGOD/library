<?php
session_start();
error_reporting(0);
include('includes/config.php');

if(strlen($_SESSION['login'])==0) {   
    header('location:index.php');
} else { 
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Online Library Management System | Issued Books</title>
    <!-- BOOTSTRAP CORE STYLE  -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FONT AWESOME STYLE  -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- DATATABLE STYLE  -->
    <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <!-- CUSTOM STYLE  -->
    <link href="assets/css/style.css" rel="stylesheet" />
    <!-- GOOGLE FONT -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
</head>
<body>
<?php include('includes/header.php'); ?>
    <div class="content-wrapper">
        <div class="container">
            <div class="row pad-botm">
                <div class="col-md-12">
                    <h4 class="header-line">Manage Issued Books</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">Issued Books</div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Book Name</th>
                                            <th>ISBN</th>
                                            <th>Issued Date</th>
                                            <th>Return Date</th>
                                            <th>Fine (USD)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
<?php 
$lrn = $_SESSION['login']; // Using 'login' session to store LRN

$sql = "SELECT tblbooks.BookName, tblbooks.ISBNNumber, tblissuedbookdetails.IssuesDate, 
               tblissuedbookdetails.ReturnDate, tblissuedbookdetails.id as rid, tblissuedbookdetails.fine 
        FROM tblissuedbookdetails 
        JOIN tblstudents ON tblstudents.LRN = tblissuedbookdetails.LRN 
        JOIN tblbooks ON tblbooks.id = tblissuedbookdetails.BookId 
        WHERE tblstudents.LRN = :lrn 
        ORDER BY tblissuedbookdetails.id DESC";

$query = $dbh->prepare($sql);
$query->bindParam(':lrn', $lrn, PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);
$cnt = 1;

if ($query->rowCount() > 0) {
    foreach ($results as $result) { ?>                                      
        <tr class="odd gradeX">
            <td class="center"><?php echo htmlentities($cnt); ?></td>
            <td class="center"><?php echo htmlentities($result->BookName); ?></td>
            <td class="center"><?php echo htmlentities($result->ISBNNumber); ?></td>
            <td class="center"><?php echo htmlentities($result->IssuesDate); ?></td>
            <td class="center">
                <?php if ($result->ReturnDate == "") { ?>
                    <span style="color:red">Not Returned Yet</span>
                <?php } else {
                    echo htmlentities($result->ReturnDate);
                } ?>
            </td>
            <td class="center"><?php echo htmlentities($result->fine); ?></td>
        </tr>
<?php 
        $cnt++;
    }
} else {
    echo "<tr><td colspan='6' class='text-center'>No issued books found</td></tr>";
}
?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php include('includes/footer.php'); ?>
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.js"></script>
    <script src="assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="assets/js/dataTables/dataTables.bootstrap.js"></script>
    <script src="assets/js/custom.js"></script>
</body>
</html>
<?php } ?>

<?php
session_start();
error_reporting(E_ALL); // Enable error reporting for debugging
ini_set('display_errors', 1);
include('includes/config.php');

if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit;
} else {
    // Debugging: Check if $dbh is set
    if (!isset($dbh)) {
        die("Database connection is not established. Check your config.php file.");
    }

    // Debugging: Check if stdid is passed
    if (!isset($_GET['stdid'])) {
        die("Student ID (stdid) is missing in the URL.");
    }

    $sid = $_GET['stdid'];

    // Debugging: Check if stdid is valid
    if (empty($sid)) {
        die("Student ID (stdid) is empty.");
    }
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Library Management System | Student History</title>
    <!-- BOOTSTRAP CORE STYLE -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FONT AWESOME STYLE -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- DATATABLE STYLE -->
    <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <!-- CUSTOM STYLE -->
    <link href="assets/css/style.css" rel="stylesheet" />
    <!-- GOOGLE FONT -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
    <style>
        body {
            background-color: rgba(26, 26, 46, 0.95) !important; /* Updated background color */
            color: white; /* Ensure text is white for readability */
        }
        .panel {
            background-color: rgba(26, 26, 46, 0.98); /* Keep panel background consistent */
            color: white;
        }
        .panel-heading {
            background-color: rgba(26, 26, 46, 0.98);
            color: white;
        }
        table {
            background-color: rgba(26, 26, 46, 0.98);
            color: white;
        }
        table tr.odd {
            background-color: rgba(26, 26, 46, 0.98) !important; /* Ensure odd rows have the same background */
            color: white !important; /* Ensure text in odd rows is white */
        }
        table tr.even {
            background-color: rgba(30, 30, 46, 0.95) !important; /* Slightly different background for even rows */
            color: white !important; /* Ensure text in even rows is white */
        }
        table tr:hover {
            background-color: #4a5568 !important; /* Add a hover effect */
            color: white !important; /* Ensure text remains white on hover */
        }
    </style>
</head>
<body>
    <!------MENU SECTION START-->
    <?php include('includes/header.php');?>
    <!-- MENU SECTION END-->
    <div class="content-wrapper">
        <div class="container">
            <div class="row pad-botm">
                <div class="col-md-12">
                    <h4 class="header-line">#<?php echo htmlentities($sid); ?> Book Issued History</h4>
                    <a href="javascript:history.back()" class="btn btn-primary">Back</a>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <!-- Advanced Tables -->
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <?php echo htmlentities($sid); ?> Details
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table  table-bordered table-hover" id="issueHistory">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>LRN</th>
                                            <th>Student Name</th>
                                            <th>Issued Book</th>
                                            <th>Issued Date</th>
                                            <th>Returned Date</th>
                                            <th>Fine (if any)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
<?php
$sql = "SELECT tblstudents.LRN, tblstudents.Name, tblbooks.BookName, tblissuedbookdetails.IssuesDate, tblissuedbookdetails.ReturnDate, tblissuedbookdetails.fine 
        FROM tblissuedbookdetails
        JOIN tblstudents ON tblstudents.LRN = tblissuedbookdetails.LRN
        JOIN tblbooks ON tblbooks.id = tblissuedbookdetails.BookId 
        WHERE tblstudents.LRN = :sid";
$query = $dbh->prepare($sql);
$query->bindParam(':sid', $sid, PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);
$cnt = 1;

if ($query->rowCount() > 0) {
    foreach ($results as $result) {
?>
                                        <tr class="odd gradeX">
                                            <td class="center"><?php echo htmlentities($cnt); ?></td>
                                            <td class="center"><?php echo htmlentities($result->LRN); ?></td>
                                            <td class="center"><?php echo htmlentities($result->Name); ?></td>
                                            <td class="center"><?php echo htmlentities($result->BookName); ?></td>
                                            <td class="center"><?php echo htmlentities($result->IssuesDate); ?></td>
                                            <td class="center"><?php echo ($result->ReturnDate == '') ? "Not returned yet" : htmlentities($result->ReturnDate); ?></td>
                                            <td class="center"><?php echo ($result->ReturnDate == '') ? "Not returned yet" : htmlentities($result->fine); ?></td>
                                        </tr>
<?php
        $cnt++;
    }
} else {
    echo "<tr><td colspan='7' class='center'>No records found for Student ID: " . htmlentities($sid) . "</td></tr>";
}
?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!--End Advanced Tables -->
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENT-WRAPPER SECTION END-->
    <?php include('includes/footer.php');?>
    <!-- FOOTER SECTION END-->
    <!-- JAVASCRIPT FILES PLACED AT THE BOTTOM TO REDUCE THE LOADING TIME -->
    <!-- CORE JQUERY -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <!-- BOOTSTRAP SCRIPTS -->
    <script src="assets/js/bootstrap.js"></script>
    <!-- DATATABLE SCRIPTS -->
    <script src="assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="assets/js/dataTables/dataTables.bootstrap.js"></script>
    <!-- CUSTOM SCRIPTS -->
    <script src="assets/js/custom.js"></script>
    
    <!-- Custom DataTables initialization script -->
    <script>
    $(document).ready(function() {
        // Prevent any automatic initialization from custom.js
        $.fn.dataTable.ext.errMode = 'none';
        
        // If a DataTable with this ID already exists, destroy it
        if ($.fn.DataTable.isDataTable('#issueHistory')) {
            $('#issueHistory').DataTable().destroy();
        }
        
        // Initialize with minimal configuration
        $('#issueHistory').DataTable({
            "paging": true,
            "ordering": true,
            "info": true,
            "searching": true,
            "autoWidth": false,
            "responsive": true
        });
    });
    </script>
</body>
</html>
<?php } ?>
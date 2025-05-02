<?php
session_start();
error_reporting(0);
include('includes/config.php');

// Check if the admin is logged in
if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit();
}

// Handle update action
if (isset($_POST['update'])) {
    $pubid = intval($_GET['pubid']);
    $publisherName = $_POST['publisherName'];
    $sql = "UPDATE tblpublishers SET PublisherName = :publisherName, UpdationDate = CURRENT_TIMESTAMP WHERE id = :pubid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':publisherName', $publisherName, PDO::PARAM_STR);
    $query->bindParam(':pubid', $pubid, PDO::PARAM_INT);
    $query->execute();
    $_SESSION['updatemsg'] = "Publisher updated successfully!";
    header('location:manage-publishers.php');
    exit();
}

// Fetch publisher details
$pubid = intval($_GET['pubid']);
$sql = "SELECT * FROM tblpublishers WHERE id = :pubid";
$query = $dbh->prepare($sql);
$query->bindParam(':pubid', $pubid, PDO::PARAM_INT);
$query->execute();
$result = $query->fetch(PDO::FETCH_OBJ);
if (!$result) {
    $_SESSION['error'] = "Invalid Publisher ID!";
    header('location:manage-publishers.php');
    exit();
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Online Library Management System | Edit Publisher</title>
    <!-- BOOTSTRAP CORE STYLE -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FONT AWESOME STYLE -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- CUSTOM STYLE -->
    <link href="assets/css/manage-publishers-style.css" rel="stylesheet" />
    <!-- GOOGLE FONT -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
</head>
<body>
    <!------MENU SECTION START-->
    <?php include('includes/header.php'); ?>
    <!-- MENU SECTION END-->
    <div class="content-wrapper">
        <div class="container">
            <div class="row pad-botm">
                <div class="col-md-12">
                    <h4 class="header-line" style="margin-left: 200px">Edit Publisher</h4>
                </div>
            </div>
            <div class="row">
                <?php if ($_SESSION['error'] != "") { ?>
                    <div class="col-md-6">
                        <div class="alert alert-danger">
                            <strong>Error :</strong>
                            <?php echo htmlentities($_SESSION['error']); ?>
                            <?php echo htmlentities($_SESSION['error'] = ""); ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="row">
                <div class="col-md-6" style="margin-left: 300px;">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            Publisher Info
                        </div>
                        <div class="panel-body">
                            <form method="post">
                                <div class="form-group">
                                    <label>Publisher Name</label>
                                    <input class="form-control" type="text" name="publisherName" value="<?php echo htmlentities($result->PublisherName); ?>" required />
                                </div>
                                <button type="submit" name="update" class="btn btn-info">Update</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- CONTENT-WRAPPER SECTION END-->
    <?php include('includes/footer.php'); ?>
    <!-- FOOTER SECTION END-->
    <!-- JAVASCRIPT FILES PLACED AT THE BOTTOM TO REDUCE THE LOADING TIME -->
    <!-- CORE JQUERY -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <!-- BOOTSTRAP SCRIPTS -->
    <script src="assets/js/bootstrap.js"></script>
    <!-- CUSTOM SCRIPTS -->
    <script src="assets/js/custom.js"></script>
</body>
</html>

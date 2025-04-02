<?php
session_start();
error_reporting(0);
include('includes/config.php');

if(isset($_POST['change'])) {
    $lrn = $_POST['lrn'];
    $newpassword = md5($_POST['newpassword']);

    // Check if LRN exists in the database
    $sql = "SELECT LRN FROM tblstudents WHERE LRN = :lrn";
    $query = $dbh->prepare($sql);
    $query->bindParam(':lrn', $lrn, PDO::PARAM_STR);
    $query->execute();

    if ($query->rowCount() > 0) {
        // Update password
        $updateSql = "UPDATE tblstudents SET Password = :newpassword WHERE LRN = :lrn";
        $updateQuery = $dbh->prepare($updateSql);
        $updateQuery->bindParam(':lrn', $lrn, PDO::PARAM_STR);
        $updateQuery->bindParam(':newpassword', $newpassword, PDO::PARAM_STR);
        $updateQuery->execute();
        echo "<script>alert('Your password has been successfully changed');</script>";
    } else {
        echo "<script>alert('Invalid LRN');</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Password Recovery</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <script type="text/javascript">
        function valid() {
            if (document.chngpwd.newpassword.value != document.chngpwd.confirmpassword.value) {
                alert("New Password and Confirm Password do not match!");
                document.chngpwd.confirmpassword.focus();
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
<?php include('includes/header.php'); ?>
<div class="content-wrapper">
    <div class="container">
        <div class="row pad-botm">
            <div class="col-md-12">
                <h4 class="header-line">User Password Recovery</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                <div class="panel panel-info">
                    <div class="panel-heading">RESET PASSWORD</div>
                    <div class="panel-body">
                        <form role="form" name="chngpwd" method="post" onSubmit="return valid();">
                            <div class="form-group">
                                <label>Enter LRN</label>
                                <input class="form-control" type="text" name="lrn" required autocomplete="off" />
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <input class="form-control" type="password" name="newpassword" required autocomplete="off" />
                            </div>
                            <div class="form-group">
                                <label>Confirm Password</label>
                                <input class="form-control" type="password" name="confirmpassword" required autocomplete="off" />
                            </div>
                            <button type="submit" name="change" class="btn btn-info">Change Password</button> | <a href="index.php">Login</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('includes/footer.php'); ?>
<script src="assets/js/jquery-1.10.2.js"></script>
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/custom.js"></script>
</body>
</html>

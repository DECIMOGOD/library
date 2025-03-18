<?php
session_start();
error_reporting(0);
include('includes/config.php');

if (strlen($_SESSION['alogin']) == 0) {   
    header('location:index.php');
    exit();
}

if (!isset($_GET['rid']) || !is_numeric($_GET['rid'])) {
    die("<div class='alert alert-danger'>Error: Invalid or missing record ID.</div>");
}

$rid = intval($_GET['rid']);

if (isset($_POST['return'])) {
    $fine = $_POST['fine'];
    $rstatus = 1;
    $bookid = intval($_POST['bookid']);

    // Update issued book details
    $sql1 = "UPDATE tblissuedbookdetails SET fine=:fine, ReturnStatus=:rstatus WHERE id=:rid";
    $query1 = $dbh->prepare($sql1);
    $query1->bindParam(':rid', $rid, PDO::PARAM_INT);
    $query1->bindParam(':fine', $fine, PDO::PARAM_STR);
    $query1->bindParam(':rstatus', $rstatus, PDO::PARAM_INT);
    
    if ($query1->execute()) {
        // Update book availability
        $sql2 = "UPDATE tblbooks SET isIssued=0 WHERE id=:bookid";
        $query2 = $dbh->prepare($sql2);
        $query2->bindParam(':bookid', $bookid, PDO::PARAM_INT);
        $query2->execute();

        $_SESSION['msg'] = "Book returned successfully!";
        header('location:manage-issued-books.php');
        exit();
    } else {
        $_SESSION['error'] = "Error updating record.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Issued Book Details</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/css/font-awesome.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include('includes/header.php'); ?>
<div class="content-wrapper">
    <div class="container">
        <div class="row pad-botm">
            <div class="col-md-12">
                <h4 class="header-line">Issued Book Details</h4>
            </div>
        </div>
        
        <!-- Back Button -->
        <div class="row">
            <div class="col-md-12">
                <a href="manage-issued-books.php" class="btn btn-primary"><i class="fa fa-arrow-left"></i> Back to Issued Books</a>
                <br><br>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-info">
                    <div class="panel-heading">Issued Book Details</div>
                    <div class="panel-body">
                        <?php 
                        $sql = "SELECT 
                                    tblstudents.LRN, 
                                    tblstudents.Name, 
                                    tblbooks.BookName, 
                                    tblbooks.ISBNNumber, 
                                    tblissuedbookdetails.IssuesDate, 
                                    tblissuedbookdetails.ReturnDate, 
                                    tblissuedbookdetails.id as rid, 
                                    tblissuedbookdetails.fine, 
                                    tblissuedbookdetails.ReturnStatus, 
                                    tblbooks.id as bid, 
                                    tblbooks.bookImage 
                                FROM tblissuedbookdetails 
                                JOIN tblstudents ON tblstudents.LRN = tblissuedbookdetails.LRN 
                                JOIN tblbooks ON tblbooks.id = tblissuedbookdetails.BookId 
                                WHERE tblissuedbookdetails.id=:rid";

                        $query = $dbh->prepare($sql);
                        $query->bindParam(':rid', $rid, PDO::PARAM_INT);
                        $query->execute();
                        $result = $query->fetch(PDO::FETCH_OBJ);

                        if ($result) { ?>    
                            <form role="form" method="post">
                                <input type="hidden" name="bookid" value="<?php echo htmlentities($result->bid); ?>">

                                <h4>Student Details</h4><hr />
                                <div class="row">
                                    <div class="col-md-6"> 
                                        <div class="form-group">
                                            <label>LRN:</label>
                                            <p><?php echo htmlentities($result->LRN); ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6"> 
                                        <div class="form-group">
                                            <label>Student Name:</label>
                                            <p><?php echo htmlentities($result->Name); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <h4>Book Details</h4><hr />
                                <div class="row">
                                    <div class="col-md-6"> 
                                        <div class="form-group">
                                            <label>Book Image:</label><br>
                                            <img src="/library/shared/bookImg/<?php echo htmlentities($result->bookImage); ?>" width="120">
                                        </div>
                                    </div>
                                    <div class="col-md-6"> 
                                        <div class="form-group">
                                            <label>Book Name:</label>
                                            <p><?php echo htmlentities($result->BookName); ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6"> 
                                        <div class="form-group">
                                            <label>ISBN:</label>
                                            <p><?php echo htmlentities($result->ISBNNumber); ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6"> 
                                        <div class="form-group">
                                            <label>Book Issued Date:</label>
                                            <p><?php echo htmlentities($result->IssuesDate); ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6"> 
                                        <div class="form-group">
                                            <label>Book Returned Date:</label>
                                            <p><?php echo $result->ReturnDate ? htmlentities($result->ReturnDate) : "Not Returned Yet"; ?></p>
                                        </div>
                                    </div>
                                </div>

                                <h4>Return Details</h4><hr />
                                <div class="row">
                                    <div class="col-md-6"> 
                                        <div class="form-group">
                                            <label>Fine (in USD):</label>
                                            <?php echo ($result->fine == "") ? '<input class="form-control" type="text" name="fine" required />' : htmlentities($result->fine); ?>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($result->ReturnStatus == 0) { ?>
                                    <button type="submit" name="return" class="btn btn-info">Return Book</button>
                                <?php } else { ?>
                                    <div class="alert alert-success">Book has already been returned.</div>
                                <?php } ?>
                            </form>
                        <?php } else { ?>
                            <div class="alert alert-danger">No record found.</div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
</body>
</html>
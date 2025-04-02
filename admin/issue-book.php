<?php
session_start();
error_reporting(0);
include('includes/config.php');

if(strlen($_SESSION['alogin'])==0) {   
    header('location:index.php');
    exit();
}

if(isset($_POST['issue'])) {
    $lrn = strtoupper($_POST['lrn']);
    $bookid = $_POST['bookid'];
    $aremark = $_POST['aremark'];
    
    // Verify student exists
    $sql = "SELECT id FROM tblstudents WHERE LRN=:lrn";
    $query = $dbh->prepare($sql);
    $query->bindParam(':lrn', $lrn, PDO::PARAM_STR);
    $query->execute();
    
    if($query->rowCount() == 0) {
        $_SESSION['error'] = "Student with this LRN not found";
        header('location:issue-book.php');
        exit();
    }

    // Verify book exists and is available
    $sql = "SELECT b.id, b.bookQty, 
                   (SELECT COUNT(*) FROM tblissuedbookdetails 
                    WHERE BookId = b.id AND ReturnStatus = 0) AS issuedCount
            FROM tblbooks b
            WHERE b.ISBNNumber = :bookid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':bookid', $bookid, PDO::PARAM_STR);
    $query->execute();
    $book = $query->fetch(PDO::FETCH_OBJ);
    
    if(!$book) {
        $_SESSION['error'] = "Book not found";
        header('location:issue-book.php');
        exit();
    }

    $availableQty = $book->bookQty - $book->issuedCount;
    if($availableQty <= 0) {
        $_SESSION['error'] = "Book is not available (0 copies left)";
        header('location:issue-book.php');
        exit();
    }

    try {
        $dbh->beginTransaction();
        
        // Correct INSERT statement using issuesDate
        $sql = "INSERT INTO tblissuedbookdetails(LRN, BookId, remark, issuesDate, ReturnStatus) 
                VALUES(:lrn, :bookid, :aremark, NOW(), 0)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':lrn', $lrn, PDO::PARAM_STR);
        $query->bindParam(':bookid', $book->id, PDO::PARAM_INT);
        $query->bindParam(':aremark', $aremark, PDO::PARAM_STR);
        $query->execute();
        
        // Update book status
        $sql = "UPDATE tblbooks SET isIssued = 1 WHERE id = :bookid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':bookid', $book->id, PDO::PARAM_INT);
        $query->execute();
        
        $dbh->commit();
        
        $_SESSION['msg'] = "Book issued successfully";
        header('location:manage-issued-books.php');
        exit();
    } catch (PDOException $e) {
        $dbh->rollBack();
        $_SESSION['error'] = "Error issuing book: " . $e->getMessage();
        header('location:issue-book.php');
        exit();
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
    <title>Online Library Management System | Issue a New Book</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
    
    <style type="text/css">
        .book-card {
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .book-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .book-image {
            text-align: center;
            margin-bottom: 15px;
        }
        #loaderIcon {
            display: none;
        }
        #book-results {
            margin-top: 20px;
        }
        .select-book {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php include('includes/header.php');?>

    <div class="content-wrapper">
        <div class="container">
            <div class="row pad-botm">
                <div class="col-md-12">
                    <h4 class="header-line">Issue a New Book</h4>
                </div>
            </div>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="row">
                    <div class="col-md-10 col-md-offset-1">
                        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-10 col-sm-6 col-xs-12 col-md-offset-1">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            Issue a New Book
                        </div>
                        <div class="panel-body">
                            <form role="form" method="post">
                                <div class="form-group">
                                    <label>LRN (Learner Reference Number)<span style="color:red;">*</span></label>
                                    <input class="form-control" type="text" name="lrn" id="lrn" onBlur="getstudent()" autocomplete="off" required pattern="[0-9]*" maxlength="12" oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                                </div>

                                <div class="form-group">
                                    <span id="get_student_name" style="font-size:16px;"></span> 
                                </div>

                                <div class="form-group">
                                    <label>Search Books<span style="color:red;">*</span></label>
                                    <input class="form-control" type="text" id="book_search" placeholder="Enter ISBN or Book Title">
                                    <button type="button" class="btn btn-info" style="margin-top:10px;" onclick="searchBooks()">
                                        <i class="fa fa-search"></i> Search Books
                                    </button>
                                </div>

                                <div class="form-group">
                                    <label>Selected Book</label>
                                    <input class="form-control" type="text" id="bookid_display" readonly>
                                    <input type="hidden" name="bookid" id="bookid">
                                </div>

                                <div id="book-results" class="book-details"></div>
                                <div id="book-selection"></div>

                                <div class="form-group">
                                    <label>Remark</label>
                                    <textarea class="form-control" name="aremark" id="aremark"></textarea> 
                                </div>

                                <button type="submit" name="issue" id="submit" class="btn btn-primary" disabled>
                                    <i class="fa fa-book"></i> Issue Book
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('includes/footer.php');?>

    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.js"></script>
    <script src="assets/js/custom.js"></script>

    <script>
    function getstudent() {
        $("#loaderIcon").show();
        jQuery.ajax({
            url: "get_student.php",
            data:'lrn='+$("#lrn").val(),
            type: "POST",
            success:function(data){
                $("#get_student_name").html(data);
                $("#loaderIcon").hide();
            },
            error:function (){}
        });
    }

    function searchBooks() {
        var searchTerm = $("#book_search").val();
        if(searchTerm.length < 2) {
            alert("Please enter at least 2 characters");
            return;
        }
        
        $("#book-results").html('<div class="text-center"><img src="assets/img/loader.gif"></div>');
        
        $.post("get_book.php", {bookid: searchTerm}, function(data) {
            $("#book-results").html(data);
        });
    }

    // Initialize search on Enter key
    $("#book_search").keypress(function(e) {
        if(e.which == 13) {
            searchBooks();
            return false;
        }
    });
    </script>
</body>
</html>
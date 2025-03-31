<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('includes/config.php');

if(strlen($_SESSION['alogin'])==0) {   
    header('location:index.php');
    exit;
}

if(isset($_POST['issue'])) {
    $dbh->beginTransaction();
    try {
        // Validate inputs
        $lrn = strtoupper(trim($_POST['lrn']));
        $bookid = trim($_POST['bookid']);
        $aremark = trim($_POST['aremark']);
        
        if(empty($lrn) || empty($bookid) || empty($aremark)) {
            throw new Exception("All fields are required");
        }

        if(!preg_match('/^\d{12}$/', $lrn)) {
            throw new Exception("LRN must be exactly 12 digits");
        }

        // Check student exists
        $sql = "SELECT id FROM tblstudents WHERE LRN = :lrn";
        $query = $dbh->prepare($sql);
        $query->bindParam(':lrn', $lrn, PDO::PARAM_STR);
        $query->execute();
        
        if($query->rowCount() == 0) {
            throw new Exception("Student with LRN $lrn not found");
        }

        // IMPROVED BOOK SEARCH - searches by ISBN or partial book name
        $sql = "SELECT ISBNNumber, BookName, bookQty FROM tblbooks 
                WHERE (ISBNNumber = :bookid OR BookName LIKE CONCAT('%', :bookid, '%'))
                AND bookQty > 0
                LIMIT 1";
        $query = $dbh->prepare($sql);
        $query->bindParam(':bookid', $bookid, PDO::PARAM_STR);
        $query->execute();
        $book = $query->fetch(PDO::FETCH_OBJ);
        
        if(!$book) {
            // More helpful error message
            $sql_check = "SELECT COUNT(*) FROM tblbooks WHERE ISBNNumber = :bookid OR BookName LIKE CONCAT('%', :bookid, '%')";
            $query = $dbh->prepare($sql_check);
            $query->bindParam(':bookid', $bookid, PDO::PARAM_STR);
            $query->execute();
            $exists = $query->fetchColumn();
            
            if($exists > 0) {
                throw new Exception("Book exists but all copies are currently checked out");
            } else {
                throw new Exception("Book not found. Please check the ISBN/Title");
            }
        }

        $actualBookId = $book->ISBNNumber;

        // Check if already issued to this student
        $sql = "SELECT id FROM tblissuedbookdetails 
                WHERE LRN = :lrn AND BookId = :bookid AND ReturnDate IS NULL";
        $query = $dbh->prepare($sql);
        $query->bindParam(':lrn', $lrn, PDO::PARAM_STR);
        $query->bindParam(':bookid', $actualBookId, PDO::PARAM_STR);
        $query->execute();
        
        if($query->rowCount() > 0) {
            throw new Exception("This book is already issued to this student");
        }

        // Issue the book
        $sql = "INSERT INTO tblissuedbookdetails (LRN, BookId, remark, IssuesDate) 
                VALUES (:lrn, :bookid, :aremark, NOW())";
        $query = $dbh->prepare($sql);
        $query->bindParam(':lrn', $lrn, PDO::PARAM_STR);
        $query->bindParam(':bookid', $actualBookId, PDO::PARAM_STR);
        $query->bindParam(':aremark', $aremark, PDO::PARAM_STR);
        
        if(!$query->execute()) {
            throw new Exception("Failed to issue book");
        }

        // Update inventory
        $sql = "UPDATE tblbooks 
                SET bookQty = bookQty - 1, 
                isIssued = CASE WHEN (bookQty - 1) <= 0 THEN 1 ELSE isIssued END
                WHERE ISBNNumber = :bookid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':bookid', $actualBookId, PDO::PARAM_STR);
        
        if(!$query->execute() || $query->rowCount() != 1) {
            throw new Exception("Failed to update book inventory");
        }

        $dbh->commit();
        
        $_SESSION['msg'] = "Book '{$book->BookName}' issued successfully";
        header("Location: manage-issued-books.php");
        exit;
        
    } catch (Exception $e) {
        $dbh->rollBack();
        $_SESSION['error'] = $e->getMessage();
        header("Location: issue-book.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Issue Book | Library System</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .book-result { 
            padding: 10px; 
            border-bottom: 1px solid #eee;
            cursor: pointer;
        }
        .book-result:hover { background: #f5f5f5; }
        #loaderIcon { display: none; }
        #book-results { max-height: 300px; overflow-y: auto; }
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
                        <div class="alert alert-danger">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <strong>Error!</strong> <?php echo htmlentities($_SESSION['error']); ?>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel panel-info">
                        <div class="panel-heading">Issue Book</div>
                        <div class="panel-body">
                            <form method="post" onsubmit="return validateForm()">
                                <div class="form-group">
                                    <label>LRN <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="lrn" id="lrn" 
                                           pattern="\d{12}" title="12-digit LRN" required
                                           onBlur="getstudent()" />
                                    <div id="get_student_name"></div>
                                </div>

                                <div class="form-group">
                                    <label>Book ISBN/Title <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="bookid" id="bookid" 
                                           onkeyup="getbook()" required />
                                    <div id="loaderIcon">
                                        <img src="assets/img/loader.gif" width="20" />
                                    </div>
                                    <div id="book-results" class="book-details"></div>
                                </div>

                                <div class="form-group">
                                    <label>Remarks</label>
                                    <textarea class="form-control" name="aremark" required></textarea>
                                </div>

                                <button type="submit" name="issue" class="btn btn-success">
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
    
    <script>
    function getstudent() {
        var lrn = $("#lrn").val().trim();
        if(lrn.length !== 12) return;
        
        $("#loaderIcon").show();
        $.ajax({
            url: "get_student.php",
            data: {lrn: lrn},
            type: "POST",
            success: function(data) {
                $("#get_student_name").html(data);
                $("#loaderIcon").hide();
            },
            error: function() {
                $("#loaderIcon").hide();
                $("#get_student_name").html('<span class="text-danger">Error loading student</span>');
            }
        });
    }

    function getbook() {
        var query = $("#bookid").val().trim();
        if(query.length < 2) {
            $("#book-results").empty();
            return;
        }
        
        $("#loaderIcon").show();
        $.ajax({
            url: "search_books.php",
            data: {query: query},
            type: "POST",
            success: function(data) {
                $("#book-results").html(data);
                $("#loaderIcon").hide();
                
                // Add click handler for book selection
                $(".book-result").click(function() {
                    $("#bookid").val($(this).data('isbn'));
                    $("#book-results").empty();
                });
            },
            error: function() {
                $("#loaderIcon").hide();
                $("#book-results").html('<div class="text-danger">Error searching books</div>');
            }
        });
    }

    function validateForm() {
        var lrn = $("#lrn").val().trim();
        if(!/^\d{12}$/.test(lrn)) {
            Swal.fire('Error', 'LRN must be 12 digits', 'error');
            return false;
        }
        
        if($("#bookid").val().trim().length === 0) {
            Swal.fire('Error', 'Please select a book', 'error');
            return false;
        }
        
        return true;
    }
    </script>
</body>
</html>
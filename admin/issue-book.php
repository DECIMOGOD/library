<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('includes/config.php');

if(strlen($_SESSION['alogin'])==0) {   
    header('location:index.php');
    exit();
}

if(isset($_POST['issue'])) {
    $lrn = strtoupper($_POST['lrn']);
    $bookid = $_POST['bookid']; 
    $aremark = $_POST['aremark']; 
    $aqty = $_POST['aqty'];

    if(empty($lrn) || empty($bookid) || empty($aremark)) {
        $_SESSION['error'] = "All fields are required";
        header('location:issue-book.php');
        exit();
    }

    if($aqty > 0) {
        $sql = "SELECT id FROM tblstudents WHERE LRN=:lrn";
        $query = $dbh->prepare($sql);
        $query->bindParam(':lrn', $lrn, PDO::PARAM_STR);
        $query->execute();
        
        if($query->rowCount() == 0) {
            $_SESSION['error'] = "Student with this LRN not found";
            header('location:issue-book.php');
            exit();
        }

        $sql = "SELECT id FROM tblbooks WHERE id=:bookid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':bookid', $bookid, PDO::PARAM_INT);
        $query->execute();
        
        if($query->rowCount() == 0) {
            $_SESSION['error'] = "Book not found";
            header('location:issue-book.php');
            exit();
        }

        $sql = "INSERT INTO tblissuedbookdetails(LRN, BookId, remark) VALUES(:lrn, :bookid, :aremark)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':lrn', $lrn, PDO::PARAM_STR);
        $query->bindParam(':bookid', $bookid, PDO::PARAM_INT);
        $query->bindParam(':aremark', $aremark, PDO::PARAM_STR);
        $query->execute();
        $lastInsertId = $dbh->lastInsertId();

        if($lastInsertId) {
            $sql = "UPDATE tblbooks SET bookQty = bookQty - 1 WHERE id=:bookid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':bookid', $bookid, PDO::PARAM_INT);
            $query->execute();

            $_SESSION['msg'] = "Book issued successfully";
            header('location:manage-issued-books.php');
            exit();
        } else {
            $_SESSION['error'] = "Something went wrong. Please try again";
            header('location:issue-book.php');
            exit();
        }
    } else {
        $_SESSION['error'] = "Book not available";
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
    <title>Online Library Management System | Issue a new Book</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/issue-book-style.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <style>
        body {
            background-color: rgba(26, 26, 46, 0.95) !important; /* Updated background color */
            color: white; /* Ensure text is white for readability */
        }
        .book-details {
            color: white;
            background-color: rgba(26, 26, 46, 0.98); /* Keep the book details background */
            padding: 10px;
            border-radius: 5px;
            margin-top: 50px;
            height: 100%;
        }
        .book-details img {
            max-width: 200px;
            height: auto;
            display: block;
            margin-bottom: 10px;
            margin-left: 100px;
        }
        table {
            color: white;
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
                    <?php if(isset($_SESSION['error'])) { ?>
                        <div class="alert alert-danger">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <?php echo htmlentities($_SESSION['error']); unset($_SESSION['error']);?>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <span class="material-icons-round" style="vertical-align: middle; margin-right: 8px;">menu_book</span>
                            Issue Book
                        </div>
                        <div class="panel-body">
                            <form method="post" onsubmit="return validateForm()">
                                <div class="form-group">
                                    <label class="required-field">LRN</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="material-icons-round" style="font-size: 1.2rem; line-height: 1.2;">person</i>
                                        </span>
                                        <input class="form-control" type="text" name="lrn" id="lrn" onBlur="getstudent()" autocomplete="off" required pattern="[0-9]*" maxlength="12" oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="Enter student's LRN" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div id="get_student_name" style="font-size:16px;"></div>
                                </div>

                                <div class="form-group">
                                    <label class="required-field">ISBN Number or Book Title</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="material-icons-round" style="font-size: 1.2rem; line-height: 1.2;">qr_code_scanner</i>
                                        </span>
                                        <input class="form-control" type="text" name="bookid" id="bookid" onBlur="getbook()" required placeholder="Scan barcode or enter ISBN/title" />
                                    </div>
                                    <small class="text-muted">Scan the barcode or manually enter the ISBN or part of the book title.</small>
                                </div>

                                <div class="form-group">
                                    <div id="loaderIcon" class="text-center">
                                        <img src="/library/shared/bookImg/" alt="Loading..." />
                                    </div>
                                    <div id="get_book_name" class="book-details"></div>
                                </div>

                                <div class="form-group">
                                    <label class="required-field">Remark</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="material-icons-round" style="font-size: 1.2rem; line-height: 1.2;">notes</i>
                                        </span>
                                        <textarea class="form-control" name="aremark" id="aremark" required placeholder="Enter any remarks about this issuance"></textarea>
                                    </div>
                                </div>

                                <button type="submit" name="issue" id="submit" class="btn btn-info">
                                    <span class="material-icons-round" style="vertical-align: middle; margin-right: 5px; font-size: 1.2rem;">assignment_turned_in</span>
                                    Issue Book
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
    document.addEventListener('DOMContentLoaded', function () {
        const bookIdInput = document.getElementById('bookid');
        let barcode = '';
        let isProcessingBarcode = false;

        document.addEventListener('keypress', function (event) {
            if (document.activeElement === bookIdInput) { // Ensure only Book ID input processes the barcode
                if (event.key === 'Enter') {
                    if (barcode && !isProcessingBarcode) {
                        isProcessingBarcode = true;
                        bookIdInput.value = barcode;
                        barcode = '';
                        getbook(); // Trigger book details fetch
                        isProcessingBarcode = false;
                    }
                    event.preventDefault(); // Prevent form submission
                } else {
                    barcode += event.key;
                }
            }
        });
    });

    $(document).on('click', '.book-selection-item', function() {
        var bookid = $(this).data('bookid');
        $("#loaderIcon").show();
        $("#bookid").val(bookid);
        
        $.ajax({
            url: "get_book.php",
            type: "POST",
            data: { getbook: bookid },
            success: function(data) {
                $("#get_book_name").html(data);
                $("#loaderIcon").hide();
            },
            error: function() {
                $("#loaderIcon").hide();
                $("#get_book_name").html('<div class="alert alert-danger">Error loading book details</div>');
            }
        });
    });

    function getstudent() {
        $("#loaderIcon").show();
        $.ajax({
            url: "get_book.php",
            data: { lrn: $("#lrn").val() },
            type: "POST",
            success: function(data) {
                $("#get_student_name").html(data);
                $("#loaderIcon").hide();
            },
            error: function() {
                $("#loaderIcon").hide();
                $("#get_student_name").html('<div class="alert alert-danger">Error loading student data</div>');
            }
        });
    }

    function getbook() {
        var bookid = $("#bookid").val();
        if(bookid === "") return;
        
        $("#loaderIcon").show();
        $("#get_book_name").html('');
        
        $.ajax({
            url: "get_book.php",
            data: { bookid: bookid },
            type: "POST",
            success: function(data) {
                $("#get_book_name").html(data);
                $("#loaderIcon").hide();
            },
            error: function() {
                $("#loaderIcon").hide();
                $("#get_book_name").html('<div class="alert alert-danger">Error loading book data</div>');
            }
        });
    }

    function validateForm() {
        var lrn = $("#lrn").val();
        var bookid = $("#bookid").val();
        var aremark = $("#aremark").val();
        
        if(lrn == "" || bookid == "" || aremark == "") {
            alert("Please fill all required fields");
            return false;
        }
        
        var aqty = $("#aqty").length ? $("#aqty").val() : 0;
        if(aqty <= 0) {
            alert("This book is not available for checkout");
            return false;
        }
        
        return true;
    }
    </script>
</body>
</html>
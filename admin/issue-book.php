<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('includes/config.php');

if(strlen($_SESSION['alogin'])==0) {   
    header('location:index.php');
}
else{ 

if(isset($_POST['issue']))
{
    $lrn=strtoupper($_POST['lrn']);
    $bookid=$_POST['bookid']; 
    $aremark=$_POST['aremark']; 
    $isissued=1;
    $aqty=$_POST['aqty'];

    if($aqty > 0){
        $sql="INSERT INTO tblissuedbookdetails(LRN, BookId, remark) VALUES(:lrn, :bookid, :aremark)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':lrn', $lrn, PDO::PARAM_STR);
        $query->bindParam(':bookid', $bookid, PDO::PARAM_STR);
        $query->bindParam(':aremark', $aremark, PDO::PARAM_STR);
        $query->execute();
        $lastInsertId = $dbh->lastInsertId();

        if($lastInsertId) {
            // Update book quantity
            $sql = "UPDATE tblbooks SET AvailableQty = AvailableQty - 1 WHERE (ISBNNumber=:bookid OR BookName=:bookid)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':bookid', $bookid, PDO::PARAM_STR);
            $query->execute();

            $_SESSION['msg']="Book issued successfully";
            header('location:manage-issued-books.php');
        } else {
            $_SESSION['error']="Something went wrong. Please try again";
            header('location:manage-issued-books.php');
        }
    } else {
        $_SESSION['error']="Book Not available";
        header('location:manage-issued-books.php');   
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
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
    
    <script>
    // function for getting student name
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

    // function for book details
    function getbook() {
        $("#loaderIcon").show();
        jQuery.ajax({
            url: "get_book.php",
            data:'bookid='+$("#bookid").val(),
            type: "POST",
            success:function(data){
                $("#get_book_name").html(data);
                $("#loaderIcon").hide();
            },
            error:function (){}
        });
    }
    </script> 

    <style type="text/css">
        .others {
            color:red;
        }
        .book-details {
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        .book-image {
            text-align: center;
            margin-bottom: 15px;
        }
        #loaderIcon {
            display: none;
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

            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel panel-info">
                        <div class="panel-heading">Issue Book</div>
                        <div class="panel-body">
                            <form method="post" onsubmit="return validateForm()">
                                <div class="form-group">
                                    <label>LRN<span style="color:red;">*</span></label>
                                    <input class="form-control" type="text" name="lrn" id="lrn" onBlur="getstudent()"autocomplete="off" required pattern="[0-9]*" maxlength="12"oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                                </div>

                                <div class="form-group">
                                    <span id="get_student_name" style="font-size:16px;"></span> 
                                </div>

                                <div class="form-group">
                                    <label>ISBN Number or Book Title<span style="color:red;">*</span></label>
                                    <input class="form-control" type="text" name="bookid" id="bookid" onBlur="getbook()" required />
                                </div>

                                <div class="form-group">
                                    <div id="loaderIcon" class="text-center">
                                        <img src="assets/img/loader.gif" alt="Loading..." />
                                    </div>
                                    <div id="get_book_name" class="book-details"></div>
                                </div>

                                <div id="book-results" class="book-details"></div>
                                <div id="book-selection"></div>

                                <div class="form-group">
                                    <label>Remark<span style="color:red;">*</span></label>
                                    <textarea class="form-control" name="aremark" id="aremark" required></textarea> 
                                </div>

                                <button type="submit" name="issue" id="submit" class="btn btn-info">Issue Book</button>
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

</body>
</html>
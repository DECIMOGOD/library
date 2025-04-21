<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/config.php');

if(strlen($_SESSION['alogin']) == 0) {   
    header('location:index.php');
    exit();
}

if(isset($_POST['add'])) {
    // Validate and sanitize inputs
    $bookname = trim($_POST['bookname']);
    $category = intval($_POST['category']);
    $publisher = intval($_POST['publisher']);
    $isbn = trim($_POST['isbn']);
    $bqty = intval($_POST['bqty']);
    
    // Validate inputs
    $errors = [];
    
    if(empty($bookname)) $errors[] = "Book name is required";
    if($category <= 0) $errors[] = "Please select a valid category";
    if($publisher <= 0) $errors[] = "Please select a valid publisher";
    if(empty($isbn)) $errors[] = "ISBN is required";
    if($bqty <= 0) $errors[] = "Quantity must be greater than 0";
    
    // Process image upload
    $imageUploaded = false;
    $imgnewname = '';
    
    if(isset($_FILES['bookpic']) && $_FILES['bookpic']['error'] == UPLOAD_ERR_OK) {
        $bookimg = $_FILES['bookpic']['name'];
        $extension = strtolower(pathinfo($bookimg, PATHINFO_EXTENSION));
        $allowed_extensions = array("jpg", "jpeg", "png", "gif");
        
        if(!in_array($extension, $allowed_extensions)) {
            $errors[] = 'Invalid format. Only jpg / jpeg / png / gif format allowed';
        } else {
            $imgnewname = md5($bookimg.time()).'.'.$extension;
            $upload_path = "../shared/bookImg/".$imgnewname;
            
            if(!move_uploaded_file($_FILES['bookpic']['tmp_name'], $upload_path)) {
                $errors[] = 'Failed to upload image';
            } else {
                $imageUploaded = true;
            }
        }
    } else {
        $errors[] = 'Book image is required';
    }
    
    if(!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        header('location:add-book.php');
        exit();
    }

    try {
        // Check if ISBN already exists
        $sql = "SELECT id FROM tblbooks WHERE ISBNNumber = :isbn";
        $query = $dbh->prepare($sql);
        $query->bindParam(':isbn', $isbn, PDO::PARAM_STR);
        $query->execute();
        
        if($query->rowCount() > 0) {
            // Delete the uploaded image if ISBN exists
            if($imageUploaded && file_exists($upload_path)) {
                unlink($upload_path);
            }
            $_SESSION['error'] = 'This ISBN already exists in the system';
            header('location:add-book.php');
            exit();
        }

        // Insert new book
        $sql = "INSERT INTO tblbooks(BookName, CatId, PublisherID, ISBNNumber, bookImage, bookQty) 
                VALUES(:bookname, :category, :publisher, :isbn, :imgnewname, :bqty)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':bookname', $bookname, PDO::PARAM_STR);
        $query->bindParam(':category', $category, PDO::PARAM_INT);
        $query->bindParam(':publisher', $publisher, PDO::PARAM_INT);
        $query->bindParam(':isbn', $isbn, PDO::PARAM_STR);
        $query->bindParam(':imgnewname', $imgnewname, PDO::PARAM_STR);
        $query->bindParam(':bqty', $bqty, PDO::PARAM_INT);
        
        if($query->execute()) {
            $_SESSION['msg'] = 'Book added successfully';
            header('location:manage-books.php');
            exit();
        } else {
            // Delete the uploaded image if insert fails
            if($imageUploaded && file_exists($upload_path)) {
                unlink($upload_path);
            }
            $_SESSION['error'] = 'Something went wrong. Please try again';
            header('location:add-book.php');
            exit();
        }
    } catch (PDOException $e) {
        // Delete the uploaded image if database error occurs
        if($imageUploaded && file_exists($upload_path)) {
            unlink($upload_path);
        }
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        header('location:add-book.php');
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
    <title>Online Library Management System | Add Book</title>
    <!-- BOOTSTRAP CORE STYLE  -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FONT AWESOME STYLE  -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- CUSTOM STYLE  -->
    <link href="assets/css/style.css" rel="stylesheet" />
    <!-- GOOGLE FONT -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
    <style>
        .required-field::after {
            content: " *";
            color: red;
        }
        .help-block {
            font-size: 12px;
            color: #737373;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isbnInput = document.getElementById('isbn');
            let barcode = '';
            let isProcessingBarcode = false;

            document.addEventListener('keypress', function (event) {
                if (document.activeElement === isbnInput) { // Ensure only ISBN input processes the barcode
                    if (event.key === 'Enter') {
                        if (barcode && !isProcessingBarcode) {
                            isProcessingBarcode = true;
                            isbnInput.value = barcode;
                            barcode = '';
                            isProcessingBarcode = false;
                        }
                        event.preventDefault(); // Prevent form submission
                    } else {
                        barcode += event.key;
                    }
                }
            });
        });
    </script>
</head>
<body>
    <!------MENU SECTION START-->
    <?php include('includes/header.php');?>
    <!-- MENU SECTION END-->
    <div class="content-wrapper">
         <div class="container">
        <div class="row pad-botm">
            <div class="col-md-12">
                <h4 class="header-line">Add Book</h4>
                
                            </div>

</div>
<div class="row">
<div class="col-md-12 col-sm-12 col-xs-12">
<div class="panel panel-info">
<div class="panel-heading">
Book Info
</div>
<div class="panel-body">
<form role="form" method="post" enctype="multipart/form-data">

<div class="col-md-6">   
<div class="form-group">
<label class="required-field">Book Name</label>
<input class="form-control" type="text" name="bookname" autocomplete="off"  required />
</div>
</div>

<div class="col-md-6">  
<div class="form-group">
<label class="required-field"> Category</label>
<select class="form-control" name="category" required="required">
<option value=""> Select Category</option>
<?php 
$status=1;
$sql = "SELECT * from  tblcategory where Status=:status";
$query = $dbh -> prepare($sql);
$query -> bindParam(':status',$status, PDO::PARAM_STR);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $result)
{               ?>  
<option value="<?php echo htmlentities($result->id);?>"><?php echo htmlentities($result->CategoryName);?></option>
 <?php }} ?> 
</select>
</div></div>

<div class="col-md-6">  
<div class="form-group">
<label class="required-field"> Publisher</label>
<select class="form-control" name="publisher" required="required">
<option value=""> Select Publisher</option>
<?php 

$sql = "SELECT * from  tblpublishers ";
$query = $dbh -> prepare($sql);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $result)
{               ?>  
<option value="<?php echo htmlentities($result->id);?>"><?php echo htmlentities($result->PublisherName);?></option>
 <?php }} ?> 
</select>
</div></div>

<div class="col-md-6">  
<div class="form-group">
<label class="required-field">ISBN Number</label>
<input class="form-control" type="text" name="isbn" id="isbn" required="required" autocomplete="off" onBlur="checkisbnAvailability()" />
<p class="help-block">Scan the barcode or manually enter the ISBN. ISBN must be unique.</p>
<span id="isbn-availability-status" style="font-size:12px;"></span>
</div></div>

<div class="col-md-6">  
 <div class="form-group">
 <label class="required-field">Book Picture</label>
 <input class="form-control" type="file" name="bookpic" autocomplete="off"   required="required" />
 </div>
    </div>

<div class="col-md-6">  
 <div class="form-group">
 <label class="required-field">Book Quantity</label>
 <input class="form-control" type="number" name="bqty" min="1" autocomplete="off"   required="required" />
 </div>
</div>
<div class="col-md-12"> 
<button type="submit" name="add" id="add" class="btn btn-info">Submit </button>
</div>
 </div>
</div>
                            </div>

        </div>
   
    </div>
    </div>
     <!-- CONTENT-WRAPPER SECTION END-->
  <?php include('includes/footer.php');?>
      <!-- FOOTER SECTION END-->
    <!-- JAVASCRIPT FILES PLACED AT THE BOTTOM TO REDUCE THE LOADING TIME  -->
    <!-- CORE JQUERY  -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <!-- BOOTSTRAP SCRIPTS  -->
    <script src="assets/js/bootstrap.js"></script>
      <!-- CUSTOM SCRIPTS  -->
    <script src="assets/js/custom.js"></script>
</body>
</html>
<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/config.php');

if(strlen($_SESSION['alogin']) == 0) {   
    header('location:index.php');
    exit();
}

// Single Book Addition
if(isset($_POST['add'])) {
    // Basic fields
    $bookname = trim($_POST['bookname']);
    $category = intval($_POST['category']);
    $publisher = intval($_POST['publisher']);
    $isbn = trim($_POST['isbn']);
    $bqty = intval($_POST['bqty']);
    
    // Additional details
    $edition = trim($_POST['edition']);
    $coverType = trim($_POST['coverType']);
    $pages = intval($_POST['pages']);
    $height = floatval($_POST['height']);
    $shelfLocation = trim($_POST['shelfLocation']);
    
    // Validate inputs
    $errors = [];
    
    if(empty($bookname)) $errors[] = "Book name is required";
    if($category <= 0) $errors[] = "Please select a valid category";
    if($publisher <= 0) $errors[] = "Please select a valid publisher";
    if(empty($isbn)) $errors[] = "ISBN is required";
    if($bqty <= 0) $errors[] = "Quantity must be greater than 0";
    if($pages < 0) $errors[] = "Page count cannot be negative";
    if($height < 0) $errors[] = "Height cannot be negative";
    
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
        // Check if ISBN exists
        $sql = "SELECT id FROM tblbooks WHERE ISBNNumber = :isbn";
        $query = $dbh->prepare($sql);
        $query->bindParam(':isbn', $isbn, PDO::PARAM_STR);
        $query->execute();
        
        if($query->rowCount() > 0) {
            if($imageUploaded && file_exists($upload_path)) {
                unlink($upload_path);
            }
            $_SESSION['error'] = 'This ISBN already exists in the system';
            header('location:add-book.php');
            exit();
        }

        // Insert new book with all details
        $sql = "INSERT INTO tblbooks(BookName, CatId, PublisherID, ISBNNumber, bookImage, bookQty, 
                Edition, CoverType, Pages, Height, ShelfLocation) 
                VALUES(:bookname, :category, :publisher, :isbn, :imgnewname, :bqty, 
                :edition, :coverType, :pages, :height, :shelfLocation)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':bookname', $bookname, PDO::PARAM_STR);
        $query->bindParam(':category', $category, PDO::PARAM_INT);
        $query->bindParam(':publisher', $publisher, PDO::PARAM_INT);
        $query->bindParam(':isbn', $isbn, PDO::PARAM_STR);
        $query->bindParam(':imgnewname', $imgnewname, PDO::PARAM_STR);
        $query->bindParam(':bqty', $bqty, PDO::PARAM_INT);
        $query->bindParam(':edition', $edition, PDO::PARAM_STR);
        $query->bindParam(':coverType', $coverType, PDO::PARAM_STR);
        $query->bindParam(':pages', $pages, PDO::PARAM_INT);
        $query->bindParam(':height', $height);
        $query->bindParam(':shelfLocation', $shelfLocation, PDO::PARAM_STR);
        
        if($query->execute()) {
            $_SESSION['msg'] = 'Book added successfully';
            header('location:manage-books.php');
            exit();
        } else {
            if($imageUploaded && file_exists($upload_path)) {
                unlink($upload_path);
            }
            $_SESSION['error'] = 'Something went wrong. Please try again';
            header('location:add-book.php');
            exit();
        }
    } catch (PDOException $e) {
        if($imageUploaded && file_exists($upload_path)) {
            unlink($upload_path);
        }
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        header('location:add-book.php');
        exit();
    }
}

// Bulk Upload Processing
if(isset($_POST['bulk_upload'])) {
    $errors = [];
    $successCount = 0;
    $errorCount = 0;
    $isbnImageMap = [];
    
    // Process image zip if uploaded
    if(isset($_FILES['images_zip']) && $_FILES['images_zip']['error'] == UPLOAD_ERR_OK) {
        $zip = new ZipArchive;
        $imageZipPath = $_FILES['images_zip']['tmp_name'];
        
        if ($zip->open($imageZipPath) === TRUE) {
            $extractPath = "../shared/bookImg/bulk_".time()."/";
            if (!file_exists($extractPath)) {
                mkdir($extractPath, 0777, true);
            }
            
            $zip->extractTo($extractPath);
            $zip->close();
            
            // Create mapping of ISBN to image paths
            $files = scandir($extractPath);
            foreach ($files as $file) {
                if ($file != "." && $file != "..") {
                    $isbn = pathinfo($file, PATHINFO_FILENAME);
                    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    $isbnImageMap[$isbn] = $extractPath.$file;
                }
            }
        }
    }
    
    // Process CSV file
    if(isset($_FILES['bulk_file']) && $_FILES['bulk_file']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['bulk_file']['tmp_name'];
        $handle = fopen($file, 'r');
        
        if($handle !== FALSE) {
            // Skip header row
            fgetcsv($handle);
            
            while(($data = fgetcsv($handle)) !== FALSE) {
                $rowErrors = [];
                
                if(count($data) < 5) {
                    $rowErrors[] = "Insufficient columns (expected at least 5)";
                } else {
                    // Required fields
                    $bookname = trim($data[0]);
                    $category = intval($data[1]);
                    $publisher = intval($data[2]);
                    $isbn = trim($data[3]);
                    $bqty = intval($data[4]);
                    
                    // Optional fields
                    $edition = isset($data[5]) ? trim($data[5]) : '';
                    $coverType = isset($data[6]) ? trim($data[6]) : '';
                    $pages = isset($data[7]) ? intval($data[7]) : 0;
                    $height = isset($data[8]) ? floatval($data[8]) : 0;
                    $shelfLocation = isset($data[9]) ? trim($data[9]) : '';
                    
                    // Validate fields
                    if(empty($bookname)) $rowErrors[] = "Book name required";
                    if($category <= 0) $rowErrors[] = "Invalid category";
                    if($publisher <= 0) $rowErrors[] = "Invalid publisher";
                    if(empty($isbn)) $rowErrors[] = "ISBN required";
                    if($bqty <= 0) $rowErrors[] = "Quantity must be > 0";
                    if($pages < 0) $rowErrors[] = "Page count cannot be negative";
                    if($height < 0) $rowErrors[] = "Height cannot be negative";
                    
                    // Check ISBN uniqueness
                    $sql = "SELECT id FROM tblbooks WHERE ISBNNumber = :isbn";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':isbn', $isbn, PDO::PARAM_STR);
                    $query->execute();
                    if($query->rowCount() > 0) {
                        $rowErrors[] = "ISBN already exists";
                    }
                }
                
                if(empty($rowErrors)) {
                    try {
                        // Process image
                        $imgnewname = '';
                        if(isset($isbnImageMap[$isbn])) {
                            $sourcePath = $isbnImageMap[$isbn];
                            $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
                            $allowed_extensions = array("jpg", "jpeg", "png", "gif");
                            
                            if(in_array($extension, $allowed_extensions)) {
                                $imgnewname = md5($isbn.time()).'.'.$extension;
                                $upload_path = "../shared/bookImg/".$imgnewname;
                                copy($sourcePath, $upload_path);
                            }
                        }
                        
                        // Insert book with all details
                        $sql = "INSERT INTO tblbooks(BookName, CatId, PublisherID, ISBNNumber, bookImage, bookQty, 
                                Edition, CoverType, Pages, Height, ShelfLocation) 
                                VALUES(:bookname, :category, :publisher, :isbn, :imgnewname, :bqty, 
                                :edition, :coverType, :pages, :height, :shelfLocation)";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':bookname', $bookname, PDO::PARAM_STR);
                        $query->bindParam(':category', $category, PDO::PARAM_INT);
                        $query->bindParam(':publisher', $publisher, PDO::PARAM_INT);
                        $query->bindParam(':isbn', $isbn, PDO::PARAM_STR);
                        $query->bindParam(':imgnewname', $imgnewname, PDO::PARAM_STR);
                        $query->bindParam(':bqty', $bqty, PDO::PARAM_INT);
                        $query->bindParam(':edition', $edition, PDO::PARAM_STR);
                        $query->bindParam(':coverType', $coverType, PDO::PARAM_STR);
                        $query->bindParam(':pages', $pages, PDO::PARAM_INT);
                        $query->bindParam(':height', $height);
                        $query->bindParam(':shelfLocation', $shelfLocation, PDO::PARAM_STR);
                        
                        if($query->execute()) {
                            $successCount++;
                        } else {
                            $errorCount++;
                        }
                    } catch (PDOException $e) {
                        $errorCount++;
                    }
                } else {
                    $errorCount++;
                    $errors[] = "Row error (ISBN: $isbn): ".implode(", ", $rowErrors);
                }
            }
            fclose($handle);
            
            // Clean up extracted images
            if(isset($extractPath)) {
                array_map('unlink', glob("$extractPath/*"));
                rmdir($extractPath);
            }
            
            // Set result message
            if($successCount > 0) {
                $_SESSION['msg'] = "Successfully added $successCount books";
                if($errorCount > 0) {
                    $_SESSION['msg'] .= " ($errorCount failed)";
                }
            }
            if(!empty($errors)) {
                $_SESSION['error'] = implode("<br>", array_slice($errors, 0, 10));
                if(count($errors) > 10) {
                    $_SESSION['error'] .= "<br>... and ".(count($errors)-10)." more errors";
                }
            }
            
            header('location:add-book.php');
            exit();
        }
    } else {
        $_SESSION['error'] = 'Please upload a valid CSV file';
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
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
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
        .tab-content {
            padding: 15px;
            border-left: 1px solid #ddd;
            border-right: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
            border-radius: 0 0 4px 4px;
        }
        .nav-tabs {
            margin-bottom: 0;
        }
        .book-details-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .detail-row {
            margin-bottom: 15px;
        }
        .half-width {
            width: 48%;
            display: inline-block;
        }
        .half-width:first-child {
            margin-right: 4%;
        }
        .template-download {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .bulk-upload-container {
            background: #fff;
            border-radius: 4px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .bulk-upload-header {
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-top: 0;
        }
        .upload-steps {
            margin: 20px 0;
            position: relative;
        }
        .step {
            display: none;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .step.active {
            display: block;
        }
        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            background: #3498db;
            color: white;
            text-align: center;
            line-height: 30px;
            border-radius: 50%;
            margin-right: 10px;
            font-weight: bold;
        }
        .step h5 {
            display: inline-block;
            margin: 0;
            vertical-align: middle;
        }
        .step-content {
            margin-top: 20px;
        }
        .step-instructions {
            padding-left: 20px;
        }
        .file-upload-wrapper {
            position: relative;
            margin-bottom: 10px;
        }
        .file-upload-preview {
            margin-top: 5px;
            font-size: 14px;
            color: #666;
        }
        .upload-guide {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 4px;
            margin-top: 30px;
        }
        .upload-guide h5 {
            margin-top: 0;
            color: #2c3e50;
        }
        .guide-section {
            margin-bottom: 20px;
        }
        .guide-section h6 {
            color: #3498db;
            margin-bottom: 10px;
        }
        
        /* Button Styles */
        .step-nav-buttons {
            margin-top: 25px;
            text-align: right;
        }
        .btn-step {
            padding: 8px 20px;
            border-radius: 4px;
            font-weight: bold;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
            font-size: 14px;
        }
        .btn-next {
            background-color: #3498db;
            color: white;
            border: 1px solid #2980b9;
        }
        .btn-next:hover {
            background-color: #2980b9;
        }
        .btn-prev {
            background-color: #95a5a6;
            color: white;
            border: 1px solid #7f8c8d;
            margin-right: 10px;
        }
        .btn-prev:hover {
            background-color: #7f8c8d;
        }
        .btn-prev:disabled {
            background-color: #bdc3c7;
            border-color: #bdc3c7;
            cursor: not-allowed;
        }
        
        @media (max-width: 768px) {
            .tab-content {
                padding: 10px;
            }
            .half-width {
                width: 100%;
                display: block;
            }
            .half-width:first-child {
                margin-right: 0;
            }
            .step-nav-buttons {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <?php include('includes/header.php');?>
    
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
                            Book Information
                        </div>
                        <div class="panel-body">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#single" data-toggle="tab">Single Entry</a></li>
                                <li><a href="#bulk" data-toggle="tab">Bulk Upload</a></li>
                            </ul>
                            
                            <div class="tab-content">
                                <!-- Single Entry Tab -->
                                <div class="tab-pane active" id="single">
                                    <form role="form" method="post" enctype="multipart/form-data">
                                        <div class="col-md-6">   
                                            <div class="form-group">
                                                <label class="required-field">Book Name</label>
                                                <input class="form-control" type="text" name="bookname" autocomplete="off" required />
                                            </div>
                                        </div>

                                        <div class="col-md-6">  
                                            <div class="form-group">
                                                <label class="required-field">Category</label>
                                                <select class="form-control" name="category" required="required">
                                                    <option value="">Select Category</option>
                                                    <?php 
                                                    $status=1;
                                                    $sql = "SELECT * from tblcategory where Status=:status";
                                                    $query = $dbh->prepare($sql);
                                                    $query->bindParam(':status',$status, PDO::PARAM_STR);
                                                    $query->execute();
                                                    $results=$query->fetchAll(PDO::FETCH_OBJ);
                                                    if($query->rowCount() > 0) {
                                                        foreach($results as $result) { ?>  
                                                        <option value="<?php echo htmlentities($result->id);?>"><?php echo htmlentities($result->CategoryName);?></option>
                                                    <?php }} ?> 
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">  
                                            <div class="form-group">
                                                <label class="required-field">Publisher</label>
                                                <select class="form-control" name="publisher" required="required">
                                                    <option value="">Select Publisher</option>
                                                    <?php 
                                                    $sql = "SELECT * from tblpublishers";
                                                    $query = $dbh->prepare($sql);
                                                    $query->execute();
                                                    $results=$query->fetchAll(PDO::FETCH_OBJ);
                                                    if($query->rowCount() > 0) {
                                                        foreach($results as $result) { ?>  
                                                        <option value="<?php echo htmlentities($result->id);?>"><?php echo htmlentities($result->PublisherName);?></option>
                                                    <?php }} ?> 
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">  
                                            <div class="form-group">
                                                <label class="required-field">ISBN Number</label>
                                                <input class="form-control" type="text" name="isbn" id="isbn" required="required" autocomplete="off" />
                                                <p class="help-block">ISBN must be unique</p>
                                            </div>
                                        </div>

                                        <div class="col-md-6">  
                                            <div class="form-group">
                                                <label class="required-field">Book Picture</label>
                                                <input class="form-control" type="file" name="bookpic" autocomplete="off" required="required" />
                                            </div>
                                        </div>

                                        <div class="col-md-6">  
                                            <div class="form-group">
                                                <label class="required-field">Book Quantity</label>
                                                <input class="form-control" type="number" name="bqty" min="1" autocomplete="off" required="required" />
                                            </div>
                                        </div>

                                        <!-- Additional Book Details -->
                                        <div class="col-md-12 book-details-section">
                                            <h4>Additional Book Details</h4>
                                            
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Edition</label>
                                                    <input class="form-control" type="text" name="edition" autocomplete="off" />
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label>Cover Type</label>
                                                    <select class="form-control" name="coverType">
                                                        <option value="">Select Cover Type</option>
                                                        <option value="Hardcover">Hardcover</option>
                                                        <option value="Paperback">Paperback</option>
                                                        <option value="Spiral">Spiral</option>
                                                        <option value="E-book">E-book</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Number of Pages</label>
                                                    <input class="form-control" type="number" name="pages" min="0" />
                                                </div>
                                                
                                                <div class="detail-row">
                                                    <div class="half-width">
                                                        <div class="form-group">
                                                            <label>Height (cm)</label>
                                                            <input class="form-control" type="number" step="0.1" name="height" min="0" />
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="half-width">
                                                        <div class="form-group">
                                                            <label>Shelf Location</label>
                                                            <input class="form-control" type="text" name="shelfLocation" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-12"> 
                                            <button type="submit" name="add" class="btn btn-info">Add Book</button>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Bulk Upload Tab -->
                                <div class="tab-pane" id="bulk">
                                    <div class="bulk-upload-container">
                                        <h4 class="bulk-upload-header"><i class="fa fa-upload"></i> Bulk Book Upload</h4>
                                        
                                        <div class="upload-steps">
                                            <div class="step active" id="step1">
                                                <div class="step-number">1</div>
                                                <h5>Prepare Your Files</h5>
                                                <div class="step-content">
                                                    <p>Prepare your book data and cover images:</p>
                                                    <ul class="step-instructions">
                                                        <li>Create a CSV file with book information</li>
                                                        <li>Place cover images in a folder (use ISBN as filenames)</li>
                                                        <li>Compress images to a ZIP file</li>
                                                    </ul>
                                                    <div class="step-nav-buttons">
                                                        <button type="button" class="btn-step btn-prev" disabled>Back</button>
                                                        <button type="button" class="btn-step btn-next next-step" data-next="step2">Next</button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="step" id="step2">
                                                <div class="step-number">2</div>
                                                <h5>Upload Files</h5>
                                                <div class="step-content">
                                                    <form role="form" method="post" enctype="multipart/form-data" id="bulk-upload-form">
                                                        <div class="form-group">
                                                            <label>CSV File (Required)</label>
                                                            <div class="file-upload-wrapper">
                                                                <input type="file" name="bulk_file" class="form-control file-upload" accept=".csv" required>
                                                                <div class="file-upload-preview"></div>
                                                            </div>
                                                            <p class="help-block">Upload your book data in CSV format</p>
                                                        </div>

                                                        <div class="form-group">
                                                            <label>Cover Images ZIP (Optional)</label>
                                                            <div class="file-upload-wrapper">
                                                                <input type="file" name="images_zip" class="form-control file-upload" accept=".zip">
                                                                <div class="file-upload-preview"></div>
                                                            </div>
                                                            <p class="help-block">ZIP file containing book cover images (named as ISBN.jpg/png)</p>
                                                        </div>

                                                        <div class="step-nav-buttons">
                                                            <button type="button" class="btn-step btn-prev prev-step" data-prev="step1">Back</button>
                                                            <button type="submit" name="bulk_upload" class="btn btn-info">Upload & Process</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="upload-guide">
                                            <h5><i class="fa fa-question-circle"></i> CSV Format Guide</h5>
                                            <div class="guide-content">
                                                <p>Your CSV should contain these columns in order:</p>
                                                <ol>
                                                    <li><strong>Book Name</strong> (required)</li>
                                                    <li><strong>Category ID</strong> (required)</li>
                                                    <li><strong>Publisher ID</strong> (required)</li>
                                                    <li><strong>ISBN</strong> (required)</li>
                                                    <li><strong>Quantity</strong> (required)</li>
                                                    <li>Edition (optional)</li>
                                                    <li>Cover Type (optional: Hardcover/Paperback/Spiral/E-book)</li>
                                                    <li>Pages (optional)</li>
                                                    <li>Height in cm (optional)</li>
                                                    <li>Shelf Location (optional)</li>
                                                </ol>
                                                <p><a href="sample-books.csv" class="btn btn-sm btn-default" download>
                                                    <i class="fa fa-download"></i> Download CSV Template
                                                </a></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
    $(document).ready(function() {
        // Step navigation for bulk upload
        $('.next-step').on('click', function() {
            var nextStep = $(this).data('next');
            $('.step').removeClass('active');
            $('#' + nextStep).addClass('active');
            return false;
        });

        $('.prev-step').on('click', function() {
            var prevStep = $(this).data('prev');
            $('.step').removeClass('active');
            $('#' + prevStep).addClass('active');
            return false;
        });

        // File upload preview
        $('.file-upload').change(function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).siblings('.file-upload-preview').text(fileName || 'No file selected');
        });

        // Form submission handling
        $('#bulk-upload-form').submit(function(e) {
            $('button[name="bulk_upload"]').html('<i class="fa fa-spinner fa-spin"></i> Processing...').prop('disabled', true);
        });
    });
    </script>
</body>
</html>
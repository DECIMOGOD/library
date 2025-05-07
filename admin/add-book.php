<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/config.php');

// Check if 'alogin' is set in the session and handle it safely
if (!isset($_SESSION['alogin']) || empty($_SESSION['alogin'])) {
    header('location:index.php');
    exit();
}

// Handle bulk upload
if(isset($_POST['bulkupload'])) {
    if(isset($_FILES['csvfile']) && $_FILES['csvfile']['error'] == UPLOAD_ERR_OK) {
        // Get the temporary file path
        $csvFile = $_FILES['csvfile']['tmp_name'];
        
        // Open the file for reading
        if (($handle = fopen($csvFile, "r")) !== FALSE) {
            // Skip the header row
            fgetcsv($handle);
            
            $successCount = 0;
            $errorCount = 0;
            $errors = array();
            
            // Read each line of the CSV
            while (($data = fgetcsv($handle))) {
                // Skip empty rows
                if(empty($data[0])) continue;
                
                // Prepare book data
                $bookname = $data[0];
                $publisher = $data[1];
                $copyrightDate = $data[2];
                $category = $data[3];
                $coverType = $data[4];
                $pages = $data[5];
                $height = $data[6];
                $bookQty = $data[7];
                $notes = $data[8];
                $edition = $data[9];
                $isbn = $data[10];
                
                // Default values for missing data
                if(empty($coverType)) $coverType = 'Paperback';
                if(empty($bookQty)) $bookQty = 1;
                
                // Find category ID
                $catId = 8; // Default to 'General'
                if(!empty($category)) {
                    $sql = "SELECT id FROM tblcategory WHERE CategoryName LIKE :category LIMIT 1";
                    $query = $dbh->prepare($sql);
                    $query->bindValue(':category', '%'.$category.'%', PDO::PARAM_STR);
                    $query->execute();
                    if($query->rowCount() > 0) {
                        $result = $query->fetch(PDO::FETCH_OBJ);
                        $catId = $result->id;
                    }
                }
                
                // Find publisher ID or create new publisher
                $publisherId = 1; // Default publisher
                if(!empty($publisher)) {
                    $sql = "SELECT id FROM tblpublishers WHERE PublisherName LIKE :publisher LIMIT 1";
                    $query = $dbh->prepare($sql);
                    $query->bindValue(':publisher', '%'.$publisher.'%', PDO::PARAM_STR);
                    $query->execute();
                    if($query->rowCount() > 0) {
                        $result = $query->fetch(PDO::FETCH_OBJ);
                        $publisherId = $result->id;
                    } else {
                        // Insert new publisher if not found
                        $sql = "INSERT INTO tblpublishers (PublisherName) VALUES (:publisher)";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':publisher', $publisher, PDO::PARAM_STR);
                        $query->execute();
                        $publisherId = $dbh->lastInsertId();
                    }
                }
                
                // Check if ISBN already exists
                $isbnExists = false;
                if(!empty($isbn)) {
                    $sql = "SELECT id FROM tblbooks WHERE ISBNNumber = :isbn";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':isbn', $isbn, PDO::PARAM_STR);
                    $query->execute();
                    $isbnExists = ($query->rowCount() > 0);
                }
                
                // Insert book if ISBN doesn't exist
                if(!$isbnExists) {
                    $sql = "INSERT INTO tblbooks (BookName, CatId, PublisherID, ISBNNumber, isIssued, bookQty, publisher, copyrightDate, edition, coverType, pages, height, notes) 
                            VALUES (:bookname, :catid, :publisherid, :isbn, 0, :bookqty, :publisher, :copyrightdate, :edition, :covertype, :pages, :height, :notes)";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':bookname', $bookname, PDO::PARAM_STR);
                    $query->bindParam(':catid', $catId, PDO::PARAM_INT);
                    $query->bindParam(':publisherid', $publisherId, PDO::PARAM_INT);
                    $query->bindParam(':isbn', $isbn, PDO::PARAM_STR);
                    $query->bindParam(':bookqty', $bookQty, PDO::PARAM_INT);
                    $query->bindParam(':publisher', $publisher, PDO::PARAM_STR);
                    $query->bindParam(':copyrightdate', $copyrightDate, PDO::PARAM_STR);
                    $query->bindParam(':edition', $edition, PDO::PARAM_STR);
                    $query->bindParam(':covertype', $coverType, PDO::PARAM_STR);
                    $query->bindParam(':pages', $pages, PDO::PARAM_INT);
                    $query->bindParam(':height', $height, PDO::PARAM_STR);
                    $query->bindParam(':notes', $notes, PDO::PARAM_STR);
                    
                    if($query->execute()) {
                        $successCount++;
                    } else {
                        $errorCount++;
                        $errors[] = "Error adding book: $bookname";
                    }
                } else {
                    $errorCount++;
                    $errors[] = "Skipped duplicate ISBN: $isbn for book: $bookname";
                }
            }
            fclose($handle);
            
            // Set status message
            if($errorCount == 0) {
                $_SESSION['success'] = "Successfully uploaded $successCount books!";
            } else {
                $_SESSION['error'] = "Uploaded $successCount books successfully, but $errorCount failed. Issues: " . implode(", ", $errors);
            }
            
            header("Location: add-book.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Please select a valid CSV file to upload.";
        header("Location: add-book.php");
        exit();
    }
}

// Handle single book addition
if(isset($_POST['add'])) {
    $bookname = $_POST['bookname'];
    $category = $_POST['category'];
    $publisher = $_POST['publisher'];
    $isbn = $_POST['isbn'];
    $bookqty = $_POST['bqty'];
    
    // Additional fields
    $edition = isset($_POST['edition']) ? $_POST['edition'] : '';
    $coverType = isset($_POST['coverType']) ? $_POST['coverType'] : '';
    $pages = isset($_POST['pages']) ? $_POST['pages'] : null;
    $height = isset($_POST['height']) ? $_POST['height'] : null;
    $shelfLocation = isset($_POST['shelfLocation']) ? $_POST['shelfLocation'] : '';
    
    // Check if ISBN already exists
    $sql = "SELECT id FROM tblbooks WHERE ISBNNumber = :isbn";
    $query = $dbh->prepare($sql);
    $query->bindParam(':isbn', $isbn, PDO::PARAM_STR);
    $query->execute();
    
    if($query->rowCount() > 0) {
        $_SESSION['error'] = "Error: A book with this ISBN already exists.";
        header("Location: add-book.php");
        exit();
    }
    
    // Handle file upload
    $bookpic = '';
    if(isset($_FILES['bookpic'])) {
        $file = $_FILES['bookpic'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];
        $fileType = $file['type'];
        
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = array('jpg', 'jpeg', 'png', 'gif');
        
        if(in_array($fileExt, $allowed)) {
            if($fileError === 0) {
                if($fileSize < 5000000) { // 5MB max
                    $fileNameNew = uniqid('', true).".".$fileExt;
                    $fileDestination = 'bookimages/'.$fileNameNew;
                    move_uploaded_file($fileTmpName, $fileDestination);
                    $bookpic = $fileNameNew;
                } else {
                    $_SESSION['error'] = "Error: Your file is too large (max 5MB).";
                    header("Location: add-book.php");
                    exit();
                }
            } else {
                $_SESSION['error'] = "Error: There was an error uploading your file.";
                header("Location: add-book.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Error: You cannot upload files of this type.";
            header("Location: add-book.php");
            exit();
        }
    }
    
    // Insert book into database
    $sql = "INSERT INTO tblbooks (BookName, CatId, PublisherID, ISBNNumber, bookImage, isIssued, bookQty, edition, coverType, pages, height, shelfLocation) 
            VALUES (:bookname, :catid, :publisherid, :isbn, :bookpic, 0, :bookqty, :edition, :covertype, :pages, :height, :shelflocation)";
    $query = $dbh->prepare($sql);
    $query->bindParam(':bookname', $bookname, PDO::PARAM_STR);
    $query->bindParam(':catid', $category, PDO::PARAM_INT);
    $query->bindParam(':publisherid', $publisher, PDO::PARAM_INT);
    $query->bindParam(':isbn', $isbn, PDO::PARAM_STR);
    $query->bindParam(':bookpic', $bookpic, PDO::PARAM_STR);
    $query->bindParam(':bookqty', $bookqty, PDO::PARAM_INT);
    $query->bindParam(':edition', $edition, PDO::PARAM_STR);
    $query->bindParam(':covertype', $coverType, PDO::PARAM_STR);
    $query->bindParam(':pages', $pages, PDO::PARAM_INT);
    $query->bindParam(':height', $height, PDO::PARAM_STR);
    $query->bindParam(':shelflocation', $shelfLocation, PDO::PARAM_STR);
    
    if($query->execute()) {
        $_SESSION['success'] = "Book added successfully!";
        header("Location: add-book.php");
        exit();
    } else {
        $_SESSION['error'] = "Error: Something went wrong. Please try again.";
        header("Location: add-book.php");
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
    <title>Online Library Management System | Add Book</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/add-book-style.css" rel="stylesheet" />
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
        .bulk-upload-section {
            margin-top: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .sample-csv {
            margin-top: 10px;
            font-size: 12px;
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

            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success']; 
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

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
                                    <div class="bulk-upload-section">
                                        <h4>Upload Books via CSV</h4>
                                        <p>Upload a CSV file containing your book data to add multiple books at once.</p>
                                        
                                        <form method="post" enctype="multipart/form-data">
                                            <div class="form-group">
                                                <label>CSV File</label>
                                                <input type="file" name="csvfile" accept=".csv" required class="form-control" />
                                                <p class="help-block">Please upload a CSV file with the correct format.</p>
                                            </div>
                                            
                                            <div class="form-group">
                                                <button type="submit" name="bulkupload" class="btn btn-success">Upload CSV</button>
                                            </div>
                                        </form>
                                        
                                        <div class="sample-csv">
                                            <h5>CSV Format Requirements:</h5>
                                            <p>Your CSV file should include the following columns in order:</p>
                                            <ol>
                                                <li>Book Title</li>
                                                <li>Publisher</li>
                                                <li>Copyright Date</li>
                                                <li>Category</li>
                                                <li>Cover Type</li>
                                                <li>Pages</li>
                                                <li>Height (cm)</li>
                                                <li>Quantity</li>
                                                <li>Notes</li>
                                                <li>Edition</li>
                                                <li>ISBN</li>
                                            </ol>
                                            <p><a href="sample_books.csv" download>Download sample CSV file</a></p>
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
</body>
</html>
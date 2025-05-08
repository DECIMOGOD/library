<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/config.php');

// Check if admin is logged in
if (!isset($_SESSION['alogin']) || empty($_SESSION['alogin'])) {
    header('location:index.php');
    exit();
}

// Function to verify upload directory
function verifyUploadDirectory($dir) {
    if (!is_dir($dir)) {
        $oldMask = umask(0);
        $created = mkdir($dir, 0775, true);
        umask($oldMask);
        
        if (!$created) {
            throw new Exception("Could not create upload directory");
        }
    }
    
    if (!is_writable($dir)) {
        if (!chmod($dir, 0775)) {
            throw new Exception("Upload directory is not writable");
        }
    }
    return true;
}

// Handle bulk upload
if(isset($_POST['bulkupload'])) {
    if(isset($_FILES['csvfile']) && $_FILES['csvfile']['error'] == UPLOAD_ERR_OK) {
        $csvFile = $_FILES['csvfile']['tmp_name'];

        if (($handle = fopen($csvFile, "r")) !== FALSE) {
            fgetcsv($handle); // Skip the header row

            $successCount = 0;
            $errorCount = 0;
            $errors = array();

            while (($data = fgetcsv($handle))) {
                if(empty($data[0])) continue; // Skip empty rows

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
                $bookImage = $data[11];
                $callNumber = $data[12] ?? null;
                $LRN = $data[13] ?? null;

                if(empty($coverType)) $coverType = 'Paperback';
                if(empty($bookQty)) $bookQty = 1;

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
                        $sql = "INSERT INTO tblpublishers (PublisherName) VALUES (:publisher)";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':publisher', $publisher, PDO::PARAM_STR);
                        $query->execute();
                        $publisherId = $dbh->lastInsertId();
                    }
                }

                $isbnExists = false;
                if(!empty($isbn)) {
                    $sql = "SELECT id FROM tblbooks WHERE ISBNNumber = :isbn";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':isbn', $isbn, PDO::PARAM_STR);
                    $query->execute();
                    $isbnExists = ($query->rowCount() > 0);
                }

                if(!$isbnExists) {
                    $bookImagePath = '';
                    if(!empty($bookImage)) {
                        $imagePath = '../shared/bookImg/' . basename($bookImage);
                        if(file_exists($imagePath)) {
                            $bookImagePath = $bookImage;
                        } else {
                            $errorCount++;
                            $errors[] = "Image not found: $bookImage for book: $bookname";
                            continue;
                        }
                    }

                    $sql = "INSERT INTO tblbooks (BookName, CatId, PublisherID, ISBNNumber, bookImage, isIssued, bookQty, publisher, copyrightDate, edition, coverType, pages, height, notes, callNumber, LRN) 
                            VALUES (:bookname, :catid, :publisherid, :isbn, :bookimage, 0, :bookqty, :publisher, :copyrightdate, :edition, :covertype, :pages, :height, :notes, :callNumber, :LRN)";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':bookname', $bookname, PDO::PARAM_STR);
                    $query->bindParam(':catid', $catId, PDO::PARAM_INT);
                    $query->bindParam(':publisherid', $publisherId, PDO::PARAM_INT);
                    $query->bindParam(':isbn', $isbn, PDO::PARAM_STR);
                    $query->bindParam(':bookimage', $bookImagePath, PDO::PARAM_STR);
                    $query->bindParam(':bookqty', $bookQty, PDO::PARAM_INT);
                    $query->bindParam(':publisher', $publisher, PDO::PARAM_STR);
                    $query->bindParam(':copyrightdate', $copyrightDate, PDO::PARAM_STR);
                    $query->bindParam(':edition', $edition, PDO::PARAM_STR);
                    $query->bindParam(':covertype', $coverType, PDO::PARAM_STR);
                    $query->bindParam(':pages', $pages, PDO::PARAM_INT);
                    $query->bindParam(':height', $height, PDO::PARAM_STR);
                    $query->bindParam(':notes', $notes, PDO::PARAM_STR);
                    $query->bindParam(':callNumber', $callNumber, PDO::PARAM_STR);
                    $query->bindParam(':LRN', $LRN, PDO::PARAM_STR);

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
    // Basic book information
    $bookname = $_POST['bookname'];
    $category = $_POST['category'];
    $publisher = $_POST['publisher'];
    $isbn = $_POST['isbn'];
    $bookqty = $_POST['bqty'];
    
    // Additional book details
    $edition = $_POST['edition'] ?? '';
    $coverType = $_POST['coverType'] ?? '';
    $pages = $_POST['pages'] ?? null;
    $height = $_POST['height'] ?? null;
    $shelfLocation = $_POST['shelfLocation'] ?? '';
    $copyrightDate = $_POST['copyrightDate'] ?? null;
    $notes = $_POST['notes'] ?? '';
    $callNumber = $_POST['callNumber'] ?? '';
    $LRN = $_POST['LRN'] ?? '';
    
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
    
    // Handle file upload with improved error handling
    $bookpic = '';
    if(isset($_FILES['bookpic']) && $_FILES['bookpic']['error'] === UPLOAD_ERR_OK) {
        try {
            // Verify upload directory
            verifyUploadDirectory('../shared/bookImg/');
            
            $file = $_FILES['bookpic'];
            $fileName = $file['name'];
            $fileTmpName = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileError = $file['error'];
            $fileType = $file['type'];
            
            // Sanitize filename
            $fileName = preg_replace("/[^a-zA-Z0-9\.]/", "_", $fileName);
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed = array('jpg', 'jpeg', 'png', 'gif');
            
            if(in_array($fileExt, $allowed)) {
                // Verify file is actually an image
                if (!getimagesize($fileTmpName)) {
                    throw new Exception("The uploaded file is not a valid image.");
                }

                if($fileSize < 5000000) { // 5MB max
                    // Generate unique filename
                    $fileNameNew = uniqid('', true).".".$fileExt;
                    $fileDestination = '../shared/bookImg/' . $fileNameNew;
                    
                    // Move the file
                    if(move_uploaded_file($fileTmpName, $fileDestination)) {
                        $bookpic = $fileNameNew;
                    } else {
                        throw new Exception("Unable to save the uploaded file. Please try again.");
                    }
                } else {
                    throw new Exception("Your file is too large (max 5MB).");
                }
            } else {
                throw new Exception("You cannot upload files of this type. Only JPG, JPEG, PNG, and GIF are allowed.");
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header("Location: add-book.php");
            exit();
        }
    } else {
        // Handle case where no file was uploaded or there was an upload error
        $uploadError = $_FILES['bookpic']['error'] ?? null;
        if ($uploadError !== UPLOAD_ERR_OK && $uploadError !== UPLOAD_ERR_NO_FILE) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
                UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive",
                UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded",
                UPLOAD_ERR_NO_FILE => "No file was uploaded",
                UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder",
                UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
                UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload"
            ];
            
            $errorMsg = $errorMessages[$uploadError] ?? "Unknown upload error (Code: $uploadError)";
            $_SESSION['error'] = "Error: File upload failed - " . $errorMsg;
            header("Location: add-book.php");
            exit();
        }
        
        // If no file was uploaded but it's required
        $_SESSION['error'] = "Error: Please select a book image to upload.";
        header("Location: add-book.php");
        exit();
    }
    
    // Insert book into database
    $sql = "INSERT INTO tblbooks (BookName, CatId, PublisherID, ISBNNumber, bookImage, isIssued, bookQty, edition, coverType, pages, height, shelfLocation, copyrightDate, notes, callNumber, LRN) 
            VALUES (:bookname, :catid, :publisherid, :isbn, :bookpic, 0, :bookqty, :edition, :covertype, :pages, :height, :shelflocation, :copyrightdate, :notes, :callNumber, :LRN)";
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
    $query->bindParam(':copyrightdate', $copyrightDate, PDO::PARAM_STR);
    $query->bindParam(':notes', $notes, PDO::PARAM_STR);
    $query->bindParam(':callNumber', $callNumber, PDO::PARAM_STR);
    $query->bindParam(':LRN', $LRN, PDO::PARAM_STR);
    
    if($query->execute()) {
        $_SESSION['success'] = "Book added successfully!";
        header("Location: add-book.php");
        exit();
    } else {
        $_SESSION['error'] = "Error: Something went wrong. Please try again.";
        // Delete the uploaded image if database insert failed
        if (!empty($bookpic)) {
            @unlink('../shared/bookImg/' . $bookpic);
        }
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
        .form-section {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .form-section h4 {
            margin-bottom: 15px;
            color: #337ab7;
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
                                        <!-- Basic Information Section -->
                                        <div class="form-section">
                                            <h4>Basic Information</h4>
                                            <div class="row">
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
                                                        <p class="help-block">Max 5MB. Allowed types: JPG, JPEG, PNG, GIF</p>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">  
                                                    <div class="form-group">
                                                        <label class="required-field">Book Quantity</label>
                                                        <input class="form-control" type="number" name="bqty" min="1" autocomplete="off" required="required" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Book Details Section -->
                                        <div class="form-section">
                                            <h4>Book Details</h4>
                                            <div class="row">
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

                                                    <div class="form-group">
                                                        <label>Copyright Date</label>
                                                        <input class="form-control" type="text" name="copyrightDate" placeholder="YYYY" maxlength="4" />
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
                                        </div>

                                        <!-- Additional Information Section -->
                                        <div class="form-section">
                                            <h4>Additional Information</h4>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Notes</label>
                                                        <textarea class="form-control" name="notes" rows="3"></textarea>
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
                                                <li>Image File Name</li>
                                                <li>Call Number</li>
                                                <li>LRN</li>
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
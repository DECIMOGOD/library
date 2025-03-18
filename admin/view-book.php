<?php
session_start();
error_reporting(0);
include('includes/config.php');

// Redirect if not logged in
if(strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit();
}

// Check if book ID is provided
if(isset($_GET['bookid'])) {
    $bookId = $_GET['bookid'];

    // Fetch book details - using AuthorId to match the database structure
    $sql = "SELECT b.*, c.CategoryName, p.PublisherName 
            FROM tblbooks b 
            JOIN tblcategory c ON c.id = b.CatId 
            JOIN tblpublishers p ON p.id = b.AuthorId 
            WHERE b.id = :bookid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':bookid', $bookId, PDO::PARAM_INT);
    $query->execute();
    $bookDetails = $query->fetch(PDO::FETCH_ASSOC);

    if(!$bookDetails) {
        $_SESSION['error'] = "Book not found.";
        header('location:manage-books.php');
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid request.";
    header('location:manage-books.php');
    exit();
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Online Library Management System | View Book</title>
    <!-- BOOTSTRAP CORE STYLE  -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FONT AWESOME STYLE  -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- CUSTOM STYLE  -->
    <link href="assets/css/style.css" rel="stylesheet" />
    <!-- NEW BOOK VIEW STYLE -->
    <link href="assets/css/library-book-view.css" rel="stylesheet" />
    <!-- GOOGLE FONT -->
    <link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
</head>
<body>
    <!------MENU SECTION START-->
    <?php include('includes/header.php');?>
    <!-- MENU SECTION END-->
    <div class="content-wrapper lib-book-container">
        <div class="container">
            <div class="row pad-botm">
                <div class="col-md-12">
                    <h4 class="header-line lib-page-header"><span class="lib-header-text">View Book Details</span></h4>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default lib-book-card">
                        <div class="panel-heading lib-book-header">
                            <h4>Book Information</h4>
                        </div>
                        <div class="panel-body lib-book-body">
                            <?php if($bookDetails): ?>
                                <div class="row">
                                    <div class="col-md-4 lib-book-image-container">
                                        <?php
                                        $imagePath = "/library/shared/bookImg/" . htmlentities($bookDetails['bookImage']);
                                        if(empty($bookDetails['bookImage'])) {
                                            $imagePath = "/library/shared/bookImg/placeholder-book.jpg";
                                        }
                                        ?>
                                        <img src="<?php echo $imagePath; ?>" class="lib-book-cover" 
                                             onerror="this.src='/library/shared/bookImg/placeholder-book.jpg'">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="lib-book-details">
                                            <?php
                                            // Fields to exclude from display
                                            $excludeFields = ['id', 'CatId', 'AuthorId', 'bookImage'];

                                            // Display all fields from the database query except excluded fields
                                            foreach($bookDetails as $key => $value) {
                                                if(in_array($key, $excludeFields)) continue; // Skip excluded fields
                                                
                                                // Customize the label for PublisherName
                                                $label = ucwords(str_replace('_', ' ', $key));
                                                if ($key == 'PublisherName') {
                                                    $label = 'Publisher'; // Change "PublisherName" to "Publisher"
                                                }
                                                
                                                echo '<div class="lib-book-detail-row">';
                                                echo '<span class="lib-book-label">' . $label . ':</span>';
                                                
                                                // Add special formatting based on field type
                                                if($key == 'RegDate' || $key == 'UpdationDate') {
                                                    echo '<span class="lib-book-value lib-book-date">';
                                                    echo !empty($value) ? date('F j, Y', strtotime($value)) : 'N/A';
                                                    echo '</span>';
                                                } 
                                                else if($key == 'ISBNNumber') {
                                                    echo '<span class="lib-book-value lib-book-isbn">' . htmlentities($value) . '</span>';
                                                }
                                                else if($key == 'CategoryName') {
                                                    echo '<span class="lib-book-value"><span class="lib-book-category">' . htmlentities($value) . '</span></span>';
                                                }
                                                else if($key == 'BookPrice') {
                                                    echo '<span class="lib-book-value">$' . number_format($value, 2) . '</span>';
                                                }
                                                else if($key == 'Status') {
                                                    if($value == 1) {
                                                        echo '<span class="lib-book-value lib-status-available">Available</span>';
                                                    } else {
                                                        echo '<span class="lib-book-value lib-status-unavailable">Not Available</span>';
                                                    }
                                                }
                                                else {
                                                    echo '<span class="lib-book-value">' . htmlentities($value) . '</span>';
                                                }
                                                
                                                echo '</div>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p class="text-center">No details found for this book.</p>
                            <?php endif; ?>
                        </div>
                        <div class="panel-footer lib-book-footer">
                            <a href="edit-book.php?bookid=<?php echo $bookId; ?>" class="btn btn-primary lib-edit-button">
                                <i class="fa fa-edit"></i> Edit Book Details
                            </a>
                            <a href="manage-books.php" class="btn lib-back-button">
                                <i class="fa fa-arrow-left"></i> Back to Manage Books
                            </a>
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
</body>
</html>
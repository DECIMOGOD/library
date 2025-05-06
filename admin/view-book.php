<?php
// Start session and include database connection
session_start();
include('../includes/config.php');

// Check if book ID is provided
if (!isset($_GET['bookid']) || empty($_GET['bookid'])) {
    $_SESSION['error'] = "Invalid book ID.";
    header('location:manage-books.php');
    exit();
}

$bookid = intval($_GET['bookid']);

try {
    // Fetch book details
    $sql = "SELECT tblbooks.BookName, tblbooks.ISBNNumber, tblbooks.bookQty, tblbooks.bookImage, tblbooks.isIssued,
            tblcategory.CategoryName, tblpublishers.PublisherName
            FROM tblbooks
            JOIN tblcategory ON tblcategory.id = tblbooks.CatId
            JOIN tblpublishers ON tblpublishers.id = tblbooks.PublisherID
            WHERE tblbooks.id = :id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $bookid, PDO::PARAM_INT);
    $query->execute();
    $book = $query->fetch(PDO::FETCH_OBJ);

    if (!$book) {
        $_SESSION['error'] = "Book not found.";
        header('location:manage-books.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header('location:manage-books.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="Online Library Management System" />
    <title>Library Management System | View Book</title>
    
    <!-- Bootstrap CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- Custom CSS -->
    <link href="assets/css/dashboard-style.css" rel="stylesheet" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .book-detail-card {
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(4, 0, 70, 0.1);
            overflow: hidden;
            background: #fff;
        }
        .book-cover {
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .detail-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .detail-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .detail-label {
            font-weight: 600;
            color: white;
            margin-bottom: 5px;
        }
        .detail-value {
            font-size: 1.05rem;
            color: white;
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 6px 12px;
            border-radius: 20px;
        }
        .back-btn {
            transition: all 0.3s ease;
        }
        .back-btn:hover {
            transform: translateX(-3px);
        }
        .header-title {
            position: relative;
            padding-bottom: 10px;
            color: white;
        }
        .header-title:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #4e73df, #224abe);
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <!-- HEADER SECTION -->
    <?php include('includes/header.php'); ?>
    
    <!-- MAIN CONTENT -->
    <div class="content-wrapper">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-12">
                    <h4 class="header-title" style=" color: white;">Book Details</h4>
                    
                    <!-- Display error message if any -->
                    <?php if(isset($_SESSION['error'])) { ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo htmlentities($_SESSION['error']); unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php } ?>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-12">
                    <div class="book-detail-card p-4" style="background: rgba(8, 0, 41, 0.95);">
                        <div class="row">
                            <!-- Book Cover Column -->
                            <div class="col-md-4 mb-4 mb-md-0">
                                <div class="h-100 d-flex align-items-center">
                                    <img src="<?php echo (!empty($book->bookImage) && file_exists('../shared/bookImg/' . $book->bookImage)) 
                                        ? '../shared/bookImg/' . htmlentities($book->bookImage) 
                                        : '../shared/bookImg/placeholder.jpg'; ?>" 
                                        class="book-cover w-100" 
                                        alt="<?php echo htmlentities($book->BookName); ?> cover"
                                        onerror="this.onerror=null;this.src='../shared/bookImg/placeholder.jpg'">
                                </div>
                            </div>
                            
                            <!-- Book Details Column -->
                            <div class="col-md-8">
                                <div class="d-flex flex-column h-100" style= " color: white;">
                                    <h3 class="mb-4"><?php echo htmlentities($book->BookName); ?></h3>
                                    
                                    <div class="detail-item">
                                        <div class="detail-label">Category</div>
                                        <div class="detail-value"><?php echo htmlentities($book->CategoryName); ?></div>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <div class="detail-label">Publisher</div>
                                        <div class="detail-value"><?php echo htmlentities($book->PublisherName); ?></div>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <div class="detail-label">ISBN Number</div>
                                        <div class="detail-value"><?php echo htmlentities($book->ISBNNumber); ?></div>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <div class="detail-label">Available Quantity</div>
                                        <div class="detail-value"><?php echo htmlentities($book->bookQty); ?></div>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <div class="detail-label">Status</div>
                                        <div class="detail-value">
                                            <?php 
                                            if ($book->isIssued == 1) {
                                                echo '<span class="status-badge bg-warning text-dark"><i class="fas fa-book-reader me-1"></i> Issued</span>';
                                            } elseif ($book->bookQty <= 0) {
                                                echo '<span class="status-badge bg-danger"><i class="fas fa-times-circle me-1"></i> Out of Stock</span>';
                                            } else {
                                                echo '<span class="status-badge bg-success"><i class="fas fa-check-circle me-1"></i> Available</span>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-auto pt-3">
                                        <a href="manage-books.php" class="btn btn-primary back-btn">
                                            <i class="fas fa-arrow-left me-2"></i> Back to Manage Books
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

   

    <!-- JavaScript Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
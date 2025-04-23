<?php
session_start();
include('includes/config.php');

// Clear session if already logged in
if (isset($_SESSION['login']) && $_SESSION['login'] != '') {
    $_SESSION['login'] = '';
}

// Fetch featured books from the database with available details
$sql = "SELECT tblbooks.id, tblbooks.BookName, tblbooks.bookImage, tblbooks.ISBNNumber, 
        tblcategory.CategoryName, tblpublishers.PublisherName, tblbooks.pages, tblbooks.edition
        FROM tblbooks 
        JOIN tblpublishers ON tblpublishers.id = tblbooks.PublisherID 
        JOIN tblcategory ON tblcategory.id = tblbooks.CatId
        WHERE tblbooks.isFeatured = 1
        ORDER BY tblbooks.id DESC LIMIT 3";
$query = $dbh->prepare($sql);
$query->execute();
$featuredBooks = $query->fetchAll(PDO::FETCH_OBJ);
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="Online Library Management System" />
    <meta name="author" content="Library Admin" />
    <title>Library Management System | Home</title>
    <!-- BOOTSTRAP CORE STYLE -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FONT AWESOME STYLE -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- CUSTOM STYLE -->
    <link href="assets/css/style.css" rel="stylesheet" />
    <!-- GOOGLE FONT -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
    <style>
        /* Remove space between header and carousel */
        .hero-section {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
        .carousel {
            margin-top: 0 !important;
        }
        .carousel-inner {
            margin-top: 0 !important;
        }
        .item {
            margin-top: 0 !important;
        }
    </style>
</head>
<body>
    <!-- MENU SECTION START-->
    <?php include('includes/header.php'); ?>
    <!-- MENU SECTION END-->

    <div class="content-wrapper">
        <div class="container">
            <!-- Hero Section with Slider - Captions removed and padding fixed -->
            <div class="hero-section">
                <div id="carousel-example" class="carousel slide" data-ride="carousel">
                    <div class="carousel-inner">
                        <div class="item active">
                            <img src="assets/img/1.jpg" alt="Library Collection">
                        </div>
                        <div class="item">
                            <img src="assets/img/2.jpg" alt="Study Space">
                        </div>
                        <div class="item">
                            <img src="assets/img/3.jpg" alt="Digital Resources">
                        </div>
                    </div>

                    <!-- INDICATORS -->
                    <ol class="carousel-indicators">
                        <li data-target="#carousel-example" data-slide-to="0" class="active"></li>
                        <li data-target="#carousel-example" data-slide-to="1"></li>
                        <li data-target="#carousel-example" data-slide-to="2"></li>
                    </ol>

                    <!-- PREV & NEXT BUTTONS -->
                    <a class="left carousel-control" href="#carousel-example" data-slide="prev">
                        <span class="glyphicon glyphicon-chevron-left"></span>
                    </a>
                    <a class="right carousel-control" href="#carousel-example" data-slide="next">
                        <span class="glyphicon glyphicon-chevron-right"></span>
                    </a>
                </div>
            </div>

            <!-- Welcome Section -->
            <div class="welcome-section">
                <h2>Welcome to Our Library Management System</h2>
                <p>Discover our vast collection of books across various categories. Our user-friendly system allows you to browse, borrow, and return books with ease. Join our growing community of readers today!</p>
            </div>

            <!-- Call to Action Section -->
            <div class="cta-container">
                <h3><i class="fa fa-book"></i> Ready to Start Your Reading Journey?</h3>
                <p>Create an account to access our full library catalog and borrow books.</p>
                <div class="cta-buttons">
                    <a href="signup.php"><i class="fa fa-user-plus"></i> Sign Up Now</a>
                    <a href="login.php"><i class="fa fa-sign-in"></i> Log In</a>
                </div>
            </div>

            <!-- Featured Books Section - "View Details" buttons removed -->
            <div class="featured-books-container">
                <h3><i class="fa fa-star"></i> Featured Books</h3>
                <div class="book-list">
                    <?php
                    if($query->rowCount() > 0) {
                        foreach($featuredBooks as $book) {
                            $shortDesc = "This book is available in our library collection.";
                    ?>
                        <div class="book-item">
                            <div class="book-badge">Featured</div>
                            <img src="shared/bookImg/<?php echo htmlentities($book->bookImage); ?>" alt="<?php echo htmlentities($book->BookName); ?>" class="img-fluid mb-3">
                            <h4 class="text-center"><?php echo htmlentities($book->BookName); ?></h4>
                            
                            <div class="book-details">
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <td><strong>Author:</strong></td>
                                            <td><?php echo htmlentities($book->PublisherName); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Category:</strong></td>
                                            <td><?php echo htmlentities($book->CategoryName); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Edition:</strong></td>
                                            <td><?php echo isset($book->edition) ? htmlentities($book->edition) : 'N/A'; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Pages:</strong></td>
                                            <td><?php echo isset($book->pages) ? htmlentities($book->pages) : 'N/A'; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>ISBN:</strong></td>
                                            <td><?php echo htmlentities($book->ISBNNumber); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="book-description text-center"><?php echo htmlentities($shortDesc); ?></div>
                            
                            <div class="book-actions text-center mt-3">
                                <?php if(isset($_SESSION['login'])) { ?>
                                <a href="checkout-book.php?bookid=<?php echo htmlentities($book->id); ?>" class="btn btn-success btn-sm"><i class="fa fa-bookmark"></i> Borrow</a>
                                <?php } else { ?>
                                <a href="login.php" class="btn btn-warning btn-sm"><i class="fa fa-lock"></i> Login to Borrow</a>
                                <?php } ?>
                            </div>
                        </div>
                    <?php
                        }
                    } else {
                        // Default books display
                    ?>
                        
                        <!-- Additional default books would follow the same pattern -->
                    <?php
                    }
                    ?>
                </div>
                <div class="view-all-container">
                    <a href="book-catalog.php" class="btn btn-primary btn-lg"><i class="fa fa-book"></i> Browse All Books</a>
                </div>
            </div>
        </div>
    </div>
    <!-- CONTENT-WRAPPER SECTION END-->

    <?php include('includes/footer.php'); ?>

    <!-- FOOTER SECTION END-->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <!-- BOOTSTRAP SCRIPTS -->
    <script src="assets/js/bootstrap.js"></script>
    <!-- CUSTOM SCRIPTS -->
    <script src="assets/js/custom.js"></script>
    <script>
        // Add animation to book items when they come into view
        $(document).ready(function() {
            $(window).scroll(function() {
                $('.book-item').each(function() {
                    var position = $(this).offset().top;
                    var scroll = $(window).scrollTop();
                    var windowHeight = $(window).height();
                    
                    if (scroll + windowHeight > position + 100) {
                        $(this).css('opacity', '1');
                    }
                });
            });
            
            // Initialize all book items as invisible
            $('.book-item').css('opacity', '0').css('transition', 'opacity 0.5s ease');
            
            // Trigger scroll event to check visibility on page load
            $(window).scroll();
        });
    </script>
</body>
</html>
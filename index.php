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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Enhanced styling for the redesigned index page */
        /* Enhanced styling for a more formal, modern landing page */
body {
    font-family: 'Poppins', sans-serif;
    background-color: #f9fafc;
    color: #2c3e50;
    line-height: 1.7;
    padding-top: 60px; /* Adjusted to reduce space above carousel */
}

/* Hero section and carousel styling */
.hero-section {
    margin-top: -40px; /* Removes white space above the carousel */
    padding: 0;
    position: relative;
    overflow: hidden;
}

#carousel-example {
    margin-top: 0 !important;
    box-shadow: 0 15px 40px rgba(0,0,0,0.1);
}

.carousel-inner {
    border-radius: 0;
}

.carousel-inner .item img {
    width: 100%;
    height: 550px;
    object-fit: cover;
    filter: brightness(0.9);
}

.carousel-indicators {
    bottom: 20px; /* Adjusted position */
}

.carousel-indicators li {
    border: none; /* Removed border for a cleaner look */
    width: 14px; /* Slightly larger size */
    height: 14px;
    margin: 0 6px; /* Adjusted spacing */
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.5); /* Semi-transparent white */
    transition: all 0.3s ease; /* Smooth transition */
}

.carousel-indicators li.active {
    background-color: #3b82f6; /* Matches the theme's primary color */
    width: 16px; /* Slightly larger active indicator */
    height: 16px;
}

.carousel-indicators li:hover {
    background-color: #2563eb; /* Slightly darker hover effect */
    transform: scale(1.2); /* Enlarges on hover */
}

.carousel-control {
    width: 5%;
    background-image: none;
    opacity: 0.7;
}

.carousel-control:hover {
    opacity: 1;
}

/* Welcome section styling */
.welcome-section {
    background-color: #ffffff;
    border-radius: 6px;
    padding: 50px 70px;
    margin: 40px auto;
    max-width: 85%;
    position: relative;
    z-index: 10;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    text-align: center;
}

.welcome-section h2 {
    color: #1e3a8a;
    font-weight: 600;
    font-size: 30px;
    margin-bottom: 25px;
    position: relative;
    display: inline-block;
    letter-spacing: 0.5px;
}

.welcome-section h2:after {
    content: '';
    position: absolute;
    width: 50px;
    height: 2px;
    background: #3b82f6;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
}

.welcome-section p {
    color: #475569;
    font-size: 16px;
    line-height: 1.8;
    max-width: 800px;
    margin: 0 auto;
    font-weight: 300;
}

/* CTA section styling */
.cta-container {
    background: linear-gradient(135deg, #2563eb, #1e3a8a);
    color: white;
    border-radius: 6px;
    padding: 60px 50px;
    margin: 60px auto;
    max-width: 85%;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    text-align: center;
    position: relative;
    overflow: hidden;
}

.cta-container:before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 200px;
    height: 200px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
    transform: translate(50%, -50%);
}

.cta-container h3 {
    font-size: 26px;
    font-weight: 500;
    margin-bottom: 20px;
    letter-spacing: 0.5px;
}

.cta-buttons {
    display: flex;
    justify-content: center;
    gap: 25px;
    margin-top: 35px;
}

.cta-buttons a {
    background-color: #f8fafc;
    color: #1e3a8a;
    padding: 12px 35px;
    border-radius: 4px;
    font-size: 15px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    letter-spacing: 0.3px;
}

.cta-buttons a i {
    margin-right: 10px;
}

.cta-buttons a:hover {
    background-color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

/* Featured books section styling */
.featured-books-container {
    background-color: #ffffff;
    border-radius: 6px;
    padding: 60px 40px;
    margin: 60px auto;
    max-width: 85%;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.featured-books-container h3 {
    color: #1e3a8a;
    font-size: 26px;
    font-weight: 600;
    margin-bottom: 50px;
    text-align: center;
    position: relative;
    letter-spacing: 0.5px;
}

.featured-books-container h3:after {
    content: '';
    position: absolute;
    width: 50px;
    height: 2px;
    background: #3b82f6;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
}

.featured-books-container h3 i {
    color: #3b82f6;
    margin-right: 10px;
}

.book-list {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 35px;
    margin-top: 40px;
}

.book-item {
    background: #fff;
    width: 300px;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    position: relative;
    border: none;
}

.book-item:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.12);
}

.book-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: #3b82f6;
    color: #ffffff;
    padding: 5px 15px;
    border-radius: 3px;
    font-weight: 500;
    font-size: 12px;
    z-index: 1;
    letter-spacing: 0.3px;
}

.book-item img {
    width: 100%;
    height: 250px;
    object-fit: cover;
    transition: all 0.5s ease;
}

.book-item:hover img {
    transform: scale(1.03);
}

.book-item h4 {
    color: #1e3a8a;
    font-weight: 500;
    padding: 20px 20px 10px;
    margin: 0;
    font-size: 17px;
    border-bottom: 1px solid #f1f5f9;
}

.book-details {
    padding: 15px 20px;
}

.book-details table {
    margin-bottom: 0;
}

.book-details strong {
    color: #3b82f6;
    font-weight: 500;
}

.book-description {
    padding: 15px 20px;
    color: #64748b;
    font-size: 14px;
    border-top: 1px solid #f1f5f9;
}

.book-actions {
    padding: 0 20px 20px;
}

.book-actions .btn {
    border-radius: 4px;
    padding: 8px 20px;
    font-weight: 500;
    transition: all 0.3s ease;
    letter-spacing: 0.3px;
}

.book-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.view-all-container {
    text-align: center;
    margin-top: 50px;
}

.view-all-container .btn {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 12px 35px;
    border-radius: 4px;
    font-size: 15px;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    letter-spacing: 0.3px;
}

.view-all-container .btn:hover {
    background: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .carousel-inner .item img {
        height: 350px;
    }
    
    .welcome-section {
        padding: 40px 25px;
        margin: 30px auto;
        max-width: 90%;
    }
    
    .cta-buttons {
        flex-direction: column;
        gap: 15px;
    }
    
    .book-item {
        width: 100%;
        max-width: 320px;
    }
}
    </style>
</head>
<body>
    <!-- MENU SECTION START-->
    <?php include('includes/header.php'); ?>
    <!-- MENU SECTION END-->

    <div class="content-wrapper">
        <div class="container">
            <!-- Hero Section with Carousel -->
            <div class="hero-section">
                <div id="carousel-example" class="carousel slide" data-ride="carousel">
                    <div class="carousel-inner">
                        <div class="item active">
                            <img src="assets/img/1.png" alt="Library Collection" style="width: 100%; height: 550px; object-fit: scale-down; filter: brightness(0.9);">
                        </div>
                        <div class="item">
                            <img src="assets/img/2.png" alt="Study Space" style="width: 100%; height: 550px; object-fit: contain; filter: brightness(0.9);">
                        </div>
                        <div class="item">
                            <img src="assets/img/3.png" alt="Digital Resources" style="width: 100%; height: 550px; object-fit: contain; filter: brightness(0.9);">
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
                <p>Discover our vast collection of books across various categories. Our user-friendly system allows you to browse books with ease. Join our growing community of readers today!</p>
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

            <!-- Featured Books Section -->
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
                            <img src="shared/bookImg/<?php echo htmlentities($book->bookImage); ?>" alt="<?php echo htmlentities($book->BookName); ?>">
                            <h4><?php echo htmlentities($book->BookName); ?></h4>
                            
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
                            
                            <div class="book-description"><?php echo htmlentities($shortDesc); ?></div>
                            
                            <div class="book-actions">
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
                        // No featured books found
                        echo '<div class="alert alert-info">No featured books available at the moment.</div>';
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
                        $(this).addClass('visible');
                    }
                });
            });
            
            // Add visible class for CSS transitions
            $('.book-item').css('opacity', '0');
            
            // Add CSS class for animations
            $('<style>')
                .prop('type', 'text/css')
                .html(`
                    .book-item {
                        opacity: 0;
                        transform: translateY(20px);
                        transition: opacity 0.5s ease, transform 0.5s ease;
                    }
                    .book-item.visible {
                        opacity: 1;
                        transform: translateY(0);
                    }
                `)
                .appendTo('head');
            
            // Trigger scroll event to check visibility on page load
            $(window).scroll();
        });
    </script>
</body>
</html>
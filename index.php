<?php
session_start();
include('includes/config.php');

// Clear session if already logged in
if (isset($_SESSION['login']) && $_SESSION['login'] != '') {
    $_SESSION['login'] = '';
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Library Management System | User Login</title>
    <!-- BOOTSTRAP CORE STYLE -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FONT AWESOME STYLE -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- CUSTOM STYLE -->
    <link href="assets/css/style.css" rel="stylesheet" />
    <!-- GOOGLE FONT -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
</head>
<body>
    <!-- MENU SECTION START-->
    <?php include('includes/header.php'); ?>
    <!-- MENU SECTION END-->

    <div class="content-wrapper">
        <div class="container">
            <!-- Slider -->
            <div id="carousel-example" class="carousel slide" data-ride="carousel">
                <div class="carousel-inner">
                    <div class="item active">
                        <img src="assets/img/1.jpg" alt="Slide 1">
                    </div>
                    <div class="item">
                        <img src="assets/img/2.jpg" alt="Slide 2">
                    </div>
                    <div class="item">
                        <img src="assets/img/3.jpg" alt="Slide 3">
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

            <hr />

            <!-- Call to Action Section -->
            <div class="cta-container">
                <h3>Get Started Today!</h3>
                <div class="cta-buttons">
                    <a href="signup.php">Sign Up Now</a>
                </div>
            </div>

            <!-- Featured Books Section -->
            <div class="featured-books-container">
                <h3>Featured Books</h3>
                <div style="display: flex; justify-content: center; gap: 20px;">
                    <div class="book-item">
                        <img src="assets/img/book1.jpg" alt="Book Title 1">
                        <h4>Book Title 1</h4>
                        <p>A brief description of the book.</p>
                    </div>
                    <div class="book-item">
                        <img src="assets/img/book2.jpg" alt="Book Title 2">
                        <h4>Book Title 2</h4>
                        <p>A brief description of the book.</p>
                    </div>
                    <div class="book-item">
                        <img src="assets/img/book3.jpg" alt="Book Title 3">
                        <h4>Book Title 3</h4>
                        <p>A brief description of the book.</p>
                    </div>
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
</body>
</html>

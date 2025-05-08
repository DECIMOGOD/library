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
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9fafc;
            color: #2c3e50;
            line-height: 1.7;
            padding-top: 80px;
        }

        /* Hero section and carousel styling */
        .hero-section {
            margin-top: 60px;
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
            height: auto;
            object-fit: contain;
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

        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 25px;
            margin-top: 35px;
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

        .book-list {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 35px;
            margin-top: 40px;
        }

        .book-item {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            height: auto;
            min-height: 450px; /* Ensures all cards have a minimum height */
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            background-color: #fff;
        }

        .book-details {
            text-align: center;
            margin-bottom: 15px;
        }

        .book-actions {
            margin-top: auto;
        }

        /* Modal specific styles */
        .modal-content {
            border: none;
            border-radius: 10px;
            overflow: hidden;
        }

        .modal-header {
            border-bottom: none;
        }

        .modal-body .card {
            cursor: pointer;
            transition: all 0.3s ease;
            height: 100%;
        }

        .modal-body .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .hover-shadow {
            transition: all 0.3s ease;
        }

        .hover-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .view-all-container {
            margin-top: 30px; /* Added margin to create space between the button and the featured books */
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

            .modal-body .card {
                margin-bottom: 15px;
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
                            <img src="assets/img/1.png" alt="Library Collection">
                        </div>
                        <div class="item">
                            <img src="assets/img/2.png" alt="Study Space">
                        </div>
                        <div class="item">
                            <img src="assets/img/3.png" alt="Digital Resources">
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
                    <a href="#" data-toggle="modal" data-target="#registerModal" class="btn btn-light btn-lg"><i class="fa fa-user-plus"></i> Sign Up Now</a>
                    <a href="login.php" class="btn btn-outline-light btn-lg"><i class="fa fa-sign-in"></i> Log In</a>
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
                        <div class="book-item hover-shadow">
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

    <!-- Registration Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerModalLabel">Register As</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <a href="signup.php" class="btn btn-primary btn-block">Register as Student</a>
                    <a href="reg-faculty.php" class="btn btn-secondary btn-block">Register as Faculty</a>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.js"></script>
    <script src="assets/js/custom.js"></script>
    <script>
        $(document).ready(function() {
            // Add animation to book items when they come into view
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
            
            // Smooth modal animation
            $('#registerModal').on('show.bs.modal', function (e) {
                $('.modal .card').css({
                    'opacity': '0',
                    'transform': 'translateY(20px)'
                });
                
                setTimeout(function() {
                    $('.modal .card').each(function(i) {
                        $(this).delay(100*i).animate({
                            'opacity': '1',
                            'transform': 'translateY(0)'
                        }, 300);
                    });
                }, 100);
            });
            
            // Initialize animations
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
                    .modal .card {
                        transition: all 0.4s ease;
                    }
                `)
                .appendTo('head');
            
            // Trigger scroll event to check visibility on page load
            $(window).scroll();
        });
    </script>
</body>
</html>
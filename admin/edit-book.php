<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database configuration file
include('includes/config.php');

// Redirect if the user is not logged in
if (empty($_SESSION['alogin'])) {
    header('location:index.php');
    exit();
}

// Validate book ID
if (!isset($_GET['bookid']) || !is_numeric($_GET['bookid'])) {
    die("Invalid book ID.");
}
$bookid = intval($_GET['bookid']);

// Handle form submission for updating book details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    // Sanitize and validate input data
    $bookname = htmlspecialchars($_POST['bookname']);
    $category = intval($_POST['category']);
    $publisher = htmlspecialchars($_POST['publisher']);
    $isbn = htmlspecialchars($_POST['isbn']);
    $bqty = intval($_POST['bqty']);
    $copyrightDate = htmlspecialchars($_POST['copyrightDate']);
    $edition = htmlspecialchars($_POST['edition']);
    $coverType = htmlspecialchars($_POST['coverType']);
    $pages = intval($_POST['pages']);
    $height = htmlspecialchars($_POST['height']);
    $shelfLocation = htmlspecialchars($_POST['shelfLocation']);
    $notes = htmlspecialchars($_POST['notes']);
    $callNumber = htmlspecialchars($_POST['callNumber']);

    // Update the book details in the database
    try {
        $sql = "UPDATE tblbooks SET 
                BookName = :bookname, 
                CatId = :category, 
                Publisher = :publisher, 
                ISBNNumber = :isbn, 
                bookQty = :bqty, 
                copyrightDate = :copyrightDate, 
                edition = :edition, 
                coverType = :coverType, 
                pages = :pages, 
                height = :height, 
                shelfLocation = :shelfLocation, 
                notes = :notes, 
                callNumber = :callNumber 
                WHERE id = :bookid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':bookname', $bookname, PDO::PARAM_STR);
        $query->bindParam(':category', $category, PDO::PARAM_INT);
        $query->bindParam(':publisher', $publisher, PDO::PARAM_STR);
        $query->bindParam(':isbn', $isbn, PDO::PARAM_STR);
        $query->bindParam(':bqty', $bqty, PDO::PARAM_INT);
        $query->bindParam(':copyrightDate', $copyrightDate, PDO::PARAM_STR);
        $query->bindParam(':edition', $edition, PDO::PARAM_STR);
        $query->bindParam(':coverType', $coverType, PDO::PARAM_STR);
        $query->bindParam(':pages', $pages, PDO::PARAM_INT);
        $query->bindParam(':height', $height, PDO::PARAM_STR);
        $query->bindParam(':shelfLocation', $shelfLocation, PDO::PARAM_STR);
        $query->bindParam(':notes', $notes, PDO::PARAM_STR);
        $query->bindParam(':callNumber', $callNumber, PDO::PARAM_STR);
        $query->bindParam(':bookid', $bookid, PDO::PARAM_INT);
        $query->execute();

        // Success message and redirect
        echo "<script>alert('Book info updated successfully');</script>";
        echo "<script>window.location.href='view-book.php?bookid=" . $bookid . "'</script>";
    } catch (PDOException $e) {
        // Handle database errors
        die("Database error: " . $e->getMessage());
    }
}

// Handle form submission for adding an author
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_author'])) {
    $authorName = htmlspecialchars($_POST['authorName']);

    if (!empty($authorName)) {
        try {
            $sql = "INSERT INTO tblauthors (AuthorName) VALUES (:authorName)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':authorName', $authorName, PDO::PARAM_STR);
            $query->execute();

            $_SESSION['success'] = "Author added successfully.";
            header("location:edit-book.php?bookid=" . $bookid);
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error adding author: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Please enter an author name.";
    }
}

// Fetch book details for display
try {
    $sql = "SELECT tblbooks.BookName, tblcategory.CategoryName, tblcategory.id as cid, 
            tblbooks.Publisher, tblbooks.ISBNNumber, 
            tblbooks.bookImage, tblbooks.bookQty, 
            tblbooks.copyrightDate, tblbooks.edition, tblbooks.coverType, 
            tblbooks.pages, tblbooks.height, tblbooks.shelfLocation, tblbooks.notes, tblbooks.callNumber 
            FROM tblbooks 
            JOIN tblcategory ON tblcategory.id = tblbooks.CatId 
            WHERE tblbooks.id = :bookid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':bookid', $bookid, PDO::PARAM_INT);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    if ($query->rowCount() == 0) {
        die("No book found with ID: " . $bookid);
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Online Library Management System | Edit Book</title>
    <!-- BOOTSTRAP CORE STYLE -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FONT AWESOME STYLE -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- CUSTOM STYLE -->
    <link href="assets/css/style.css" rel="stylesheet" />
    <!-- GOOGLE FONT -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.css">
    <style>
        body {
            background-color: rgba(26, 26, 46, 0.95) !important;/* Dark background */
            color: white; /* Ensure all text is white */
        }
        .form-group label, .form-control, .panel, .panel-heading, .alert {
            color: white; /* Ensure form text, labels, and alerts are white */
        }
        .panel {
            background-color: #2d3748; /* Dark panel background */
        }
        .panel-heading {
            background-color: #4a5568; /* Darker panel heading */
        }
        .btn-info {
            background-color: #3182ce; /* Button background */
            color: white; /* Button text color */
        }
        .btn-info:hover {
            background-color: #2b6cb0; /* Button hover background */
        }
        .help-block {
            color: #a0aec0; /* Light gray for help text */
        }
    </style>
</head>
<body>
    <!------MENU SECTION START-->
    <?php include('includes/header.php'); ?>
    <!-- MENU SECTION END-->
    <div class="content-wrapper" style="background-color: rgba(26, 26, 46, 0.95) !important; padding-top: 100px;">
        <div class="container">
            <div class="row pad-botm">
                <div class="col-md-12">
                    <h4 class="header-line">Edit Book</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            Book Information
                        </div>
                        <div class="panel-body">
                            <?php foreach ($results as $result) : ?>
                            <form role="form" method="post">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Book Image</label>
                                            <div>
                                                <?php
                                                $imagePath = !empty($result->bookImage) ? "/library/shared/bookImg/" . htmlentities($result->bookImage) : "/library/shared/bookImg/placeholder-book.jpg";
                                                ?>
                                                <img src="<?php echo $imagePath; ?>" width="150" class="img-responsive img-thumbnail" onerror="this.src='/library/shared/bookImg/placeholder-book.jpg'"><br>
                                                <a href="change-bookimg.php?bookid=<?php echo htmlentities($bookid); ?>" class="btn btn-primary btn-sm" style="margin-top: 10px;">
                                                    <i class="fa fa-picture-o"></i> Change Book Image
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Book Name<span style="color:red;">*</span></label>
                                            <input class="form-control" type="text" name="bookname" value="<?php echo htmlentities($result->BookName ?? ''); ?>" required />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Category<span style="color:red;">*</span></label>
                                            <select class="form-control" name="category" required>
                                                <option value="<?php echo htmlentities($result->cid ?? ''); ?>"><?php echo htmlentities($result->CategoryName ?? ''); ?></option>
                                                <?php
                                                $sql1 = "SELECT * FROM tblcategory WHERE Status = 1";
                                                $query1 = $dbh->prepare($sql1);
                                                $query1->execute();
                                                $categories = $query1->fetchAll(PDO::FETCH_OBJ);
                                                foreach ($categories as $cat) {
                                                    if ($cat->id != $result->cid) {
                                                        echo "<option value='" . htmlentities($cat->id ?? '') . "'>" . htmlentities($cat->CategoryName ?? '') . "</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Publisher<span style="color:red;">*</span></label>
                                            <input class="form-control" type="text" name="publisher" value="<?php echo htmlentities($result->Publisher ?? ''); ?>" required />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>ISBN Number<span style="color:red;">*</span></label>
                                            <input class="form-control" type="text" name="isbn" value="<?php echo htmlentities($result->ISBNNumber ?? ''); ?>" readonly />
                                            <p class="help-block">An ISBN is an International Standard Book Number. ISBN cannot be changed.</p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Book Quantity<span style="color:red;">*</span></label>
                                            <input class="form-control" type="text" name="bqty" value="<?php echo htmlentities($result->bookQty ?? ''); ?>" required />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Copyright Date<span style="color:red;">*</span></label>
                                            <input class="form-control" type="text" name="copyrightDate" value="<?php echo htmlentities($result->copyrightDate ?? ''); ?>" required />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Edition<span style="color:red;">*</span></label>
                                            <input class="form-control" type="text" name="edition" value="<?php echo htmlentities($result->edition ?? ''); ?>" required />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Cover Type<span style="color:red;">*</span></label>
                                            <input class="form-control" type="text" name="coverType" value="<?php echo htmlentities($result->coverType ?? ''); ?>" required />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Pages<span style="color:red;">*</span></label>
                                            <input class="form-control" type="text" name="pages" value="<?php echo htmlentities($result->pages ?? ''); ?>" required />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Height (in cm)<span style="color:red;">*</span></label>
                                            <input class="form-control" type="text" name="height" value="<?php echo htmlentities($result->height ?? ''); ?>" required />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Shelf Location<span style="color:red;">*</span></label>
                                            <input class="form-control" type="text" name="shelfLocation" value="<?php echo htmlentities($result->shelfLocation ?? ''); ?>" required />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Notes</label>
                                            <textarea class="form-control" name="notes"><?php echo htmlentities($result->notes ?? ''); ?></textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Call Number<span style="color:red;">*</span></label>
                                            <input class="form-control" type="text" name="callNumber" value="<?php echo htmlentities($result->callNumber ?? ''); ?>" required />
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" name="update" class="btn btn-info">
                                                <i class="fa fa-refresh"></i> Update Book
                                            </button>
                                            <a href="view-book.php?bookid=<?php echo $bookid; ?>" class="btn btn-default">
                                                <i class="fa fa-arrow-left"></i> Cancel
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Author Section -->
            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            Add New Author
                        </div>
                        <div class="panel-body">
                            <?php if (isset($_SESSION['error'])) : ?>
                                <div class="alert alert-danger">
                                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($_SESSION['success'])) : ?>
                                <div class="alert alert-success">
                                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                                </div>
                            <?php endif; ?>
                            <form role="form" method="post">
                                <div class="form-group">
                                    <label>Author Name<span style="color:red;">*</span></label>
                                    <input class="form-control" type="text" name="authorName" required />
                                </div>
                                <button type="submit" name="add_author" class="btn btn-info">
                                    <i class="fa fa-plus"></i> Add Author
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- CONTENT-WRAPPER SECTION END-->
    <?php include('includes/footer.php'); ?>
    <!-- FOOTER SECTION END-->
    <!-- JAVASCRIPT FILES PLACED AT THE BOTTOM TO REDUCE THE LOADING TIME -->
    <!-- jQuery -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <!-- DataTables JS -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <!-- BOOTSTRAP SCRIPTS -->
    <script src="assets/js/bootstrap.js"></script>
    <!-- CUSTOM SCRIPTS -->
    <script src="assets/js"></script>
</body>
</html>
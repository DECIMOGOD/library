<?php
session_start(); // Start the session at the very beginning
include('includes/config.php'); // Include the configuration file

// Check if the user is logged in
if (!isset($_SESSION['login']) || strlen($_SESSION['login']) == 0) {
    header('location:index.php'); // Redirect to the login page
    exit(); // Stop further execution
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Online Library Management System | Issued Books</title>
    <!-- BOOTSTRAP CORE STYLE -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FONT AWESOME STYLE -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- DATATABLE STYLE -->
    <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <!-- CUSTOM STYLE -->
    <link href="assets/css/style.css" rel="stylesheet" />
    <!-- GOOGLE FONT -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
</head>
<body>
    <!------MENU SECTION START-->
    <?php include('includes/header.php'); ?>
    <!-- MENU SECTION END-->
    <div class="content-wrapper">
        <div class="container">
            <div class="row pad-botm">
                <div class="col-md-12">
                    <h4 class="header-line">Manage Issued Books</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <!-- Advanced Tables -->
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Issued Books
                        </div>
                        <div class="panel-body">
                            <?php
                           try {
                            // SQL query to fetch book details
                            $sql = "SELECT tblbooks.BookName, tblcategory.CategoryName, tblbooks.publisher AS PublisherName, tblbooks.ISBNNumber, tblbooks.id as bookid, tblbooks.bookImage, tblbooks.isIssued, tblbooks.bookQty,  
        COUNT(tblissuedbookdetails.id) AS issuedBooks,
        COUNT(tblissuedbookdetails.ReturnStatus) AS returnedbook
        FROM tblbooks
        LEFT JOIN tblissuedbookdetails ON tblissuedbookdetails.BookId = tblbooks.id
        LEFT JOIN tblcategory ON tblcategory.id = tblbooks.CatId
        GROUP BY tblbooks.id";
                            $query = $dbh->prepare($sql); // Prepare the SQL statement
                            $query->execute(); // Execute the query
                            $results = $query->fetchAll(PDO::FETCH_OBJ); // Fetch results as objects
                            $cnt = 1; // Counter for books
                        } catch (PDOException $e) {
                            echo "Error: " . $e->getMessage(); // Display error message
                            exit();
                        }

                        if ($query->rowCount() > 0) { // Check if there are any results
                            foreach ($results as $result) { // Loop through each book
                        ?>
                                        <div class="col-md-4" style="height:350px;">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <td rowspan="2">
                                                        <img src="shared/bookimg/<?php echo htmlentities($result->bookImage); ?>" width="120" alt="Book Image">
                                                    </td>
                                                    <th>Book Name</th>
                                                    <td><?php echo htmlentities($result->BookName); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Publisher</th>
                                                    <td><?php echo htmlentities($result->PublisherName); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>ISBN Number</th>
                                                    <td colspan="2"><?php echo htmlentities($result->ISBNNumber); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Book Quantity</th>
                                                    <td colspan="2"><?php echo htmlentities($result->bookQty); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Available Book Quantity</th>
                                                    <td colspan="2">
                                                        <?php
                                                        // Calculate available books
                                                        if ($result->issuedBooks == 0) {
                                                            echo htmlentities($result->bookQty);
                                                        } else {
                                                            echo htmlentities($result->bookQty - ($result->issuedBooks - $result->returnedbook));
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                            <?php
                                        $cnt = $cnt + 1; // Increment counter
                                    }
                                } else {
                                    echo "<div class='col-md-12'><p>No books found.</p></div>"; // Display message if no books are found
                                }
                            ?>
                        </div>
                    </div>
                    <!--End Advanced Tables -->
                </div>
            </div>
        </div>
    </div>
    <!-- CONTENT-WRAPPER SECTION END-->
    <?php include('includes/footer.php'); ?>
    <!-- FOOTER SECTION END-->
    <!-- JAVASCRIPT FILES PLACED AT THE BOTTOM TO REDUCE THE LOADING TIME -->
    <!-- CORE JQUERY -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <!-- BOOTSTRAP SCRIPTS -->
    <script src="assets/js/bootstrap.js"></script>
    <!-- DATATABLE SCRIPTS -->
    <script src="assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="assets/js/dataTables/dataTables.bootstrap.js"></script>
    <!-- CUSTOM SCRIPTS -->
    <script src="assets/js/custom.js"></script>
</body>
</html>
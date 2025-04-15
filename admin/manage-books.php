<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/config.php');

// Redirect if not logged in
if(strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit();
}

// Handle book deletion
if(isset($_GET['del'])) {
    $id = intval($_GET['del']);
    
    try {
        // Begin transaction
        $dbh->beginTransaction();
        
        // First check if the book is issued
        $checkSql = "SELECT id FROM tblissuedbookdetails WHERE BookId = :id AND ReturnStatus IS NULL";
        $checkQuery = $dbh->prepare($checkSql);
        $checkQuery->bindParam(':id', $id, PDO::PARAM_INT);
        $checkQuery->execute();
        
        if($checkQuery->rowCount() > 0) {
            $_SESSION['error'] = "Cannot delete book. It is currently issued to a student.";
            $dbh->rollBack();
            header('location:manage-books.php');
            exit();
        }
        
        // Get the image name to delete it from server
        $sql = "SELECT bookImage FROM tblbooks WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_OBJ);
        
        if($result) {
            $imagePath = "../shared/bookImg/".$result->bookImage;
            
            // Delete the book record
            $deleteSql = "DELETE FROM tblbooks WHERE id = :id";
            $deleteQuery = $dbh->prepare($deleteSql);
            $deleteQuery->bindParam(':id', $id, PDO::PARAM_INT);
            
            if($deleteQuery->execute()) {
                // Delete the image file if it exists and isn't the placeholder
                if(file_exists($imagePath)) {
                    $placeholderPath = "../shared/bookImg/placeholder-book.jpg";
                    if($imagePath != $placeholderPath && $result->bookImage != '') {
                        unlink($imagePath);
                    }
                }
                
                $_SESSION['msg'] = "Book deleted successfully";
                $dbh->commit();
            } else {
                $_SESSION['error'] = "Failed to delete book";
                $dbh->rollBack();
            }
        } else {
            $_SESSION['error'] = "Book not found";
            $dbh->rollBack();
        }
    } catch (PDOException $e) {
        $dbh->rollBack();
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }
    
    header('location:manage-books.php');
    exit();
}

// Define image paths
define('BOOK_IMAGE_DIR', '../shared/bookImg/');
define('PLACEHOLDER_IMAGE', BOOK_IMAGE_DIR . 'placeholder-book.jpg');
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Online Library Management System | Manage Books</title>
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
    <style>
        .book-image {
            max-width: 100px;
            max-height: 100px;
            object-fit: contain;
        }
        .action-buttons .btn {
            margin-bottom: 5px;
        }
        @media (max-width: 768px) {
            .action-buttons .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!------MENU SECTION START-->
    <?php include('includes/header.php'); ?>
    <!-- MENU SECTION END-->
    <div class="content-wrapper">
        <div class="container">
            <div class="row pad-botm">
                <div class="col-md-12">
                    <h4 class="header-line">Manage Books</h4>
                </div>
                
                <!-- Display success/error messages -->
                <?php if(isset($_SESSION['msg'])) { ?>
                <div class="col-md-12">
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fa fa-check"></i> <?php echo htmlentities($_SESSION['msg']); unset($_SESSION['msg']); ?>
                    </div>
                </div>
                <?php } ?>
                
                <?php if(isset($_SESSION['error'])) { ?>
                <div class="col-md-12">
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fa fa-exclamation-triangle"></i> <?php echo htmlentities($_SESSION['error']); unset($_SESSION['error']); ?>
                    </div>
                </div>
                <?php } ?>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <!-- Advanced Tables -->
                    <div class="panel panel-default">
                        <div class="panel-heading clearfix">
                            Books Listing
                            <div class="pull-right">
                                <a href="add-book.php" class="btn btn-primary btn-sm">
                                    <i class="fa fa-plus"></i> Add New Book
                                </a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Cover</th>
                                            <th>Book Name</th>
                                            <th>Category</th>
                                            <th>Publisher</th>
                                            <th>ISBN</th>
                                            <th>Qty</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    try {
                                        $sql = "SELECT tblbooks.id, tblbooks.BookName, tblbooks.ISBNNumber, tblbooks.bookQty, 
                                                tblbooks.bookImage, tblbooks.isIssued,
                                                tblcategory.CategoryName, tblpublishers.PublisherName
                                                FROM tblbooks 
                                                JOIN tblcategory ON tblcategory.id = tblbooks.CatId 
                                                JOIN tblpublishers ON tblpublishers.id = tblbooks.PublisherID
                                                ORDER BY tblbooks.id DESC";
                                        $query = $dbh->prepare($sql);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                        $cnt = 1;

                                        if($query->rowCount() > 0) {
                                            foreach($results as $result) {
                                                // Determine the correct image path
                                                $imageFile = htmlentities($result->bookImage);
                                                $imagePath = !empty($imageFile) ? BOOK_IMAGE_DIR . $imageFile : PLACEHOLDER_IMAGE;
                                                
                                                // Verify the image exists (for display purposes)
                                                $finalImagePath = (file_exists($imagePath)) 
                                                    ? $imagePath 
                                                    : PLACEHOLDER_IMAGE;
                                                
                                                // Determine book status
                                                $status = '';
                                                if($result->isIssued == 1) {
                                                    $status = '<span class="label label-warning">Issued</span>';
                                                } elseif($result->bookQty <= 0) {
                                                    $status = '<span class="label label-danger">Out of Stock</span>';
                                                } else {
                                                    $status = '<span class="label label-success">Available</span>';
                                                }
                                    ?>
                                        <tr>
                                            <td><?php echo htmlentities($cnt); ?></td>
                                            <td>
                                                <img src="<?php echo $finalImagePath; ?>" class="book-image img-thumbnail" 
                                                     onerror="this.onerror=null;this.src='<?php echo PLACEHOLDER_IMAGE; ?>'">
                                            </td>
                                            <td><?php echo htmlentities($result->BookName); ?></td>
                                            <td><?php echo htmlentities($result->CategoryName); ?></td>
                                            <td><?php echo htmlentities($result->PublisherName); ?></td>
                                            <td><?php echo htmlentities($result->ISBNNumber); ?></td>
                                            <td><?php echo htmlentities($result->bookQty); ?></td>
                                            <td><?php echo $status; ?></td>
                                            <td class="action-buttons">
                                                <div class="btn-group-vertical" role="group">
                                                    <a href="view-book.php?bookid=<?php echo htmlentities($result->id); ?>" 
                                                       class="btn btn-info btn-xs" title="View Details">
                                                        <i class="fa fa-eye"></i> View
                                                    </a>
                                                    <a href="edit-book.php?bookid=<?php echo htmlentities($result->id); ?>" 
                                                       class="btn btn-primary btn-xs" title="Edit Book">
                                                        <i class="fa fa-edit"></i> Edit
                                                    </a>
                                                    <a href="manage-books.php?del=<?php echo htmlentities($result->id); ?>" 
                                                       class="btn btn-danger btn-xs" title="Delete Book"
                                                       onclick="return confirm('Are you sure you want to delete this book?');">
                                                        <i class="fa fa-trash"></i> Delete
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php
                                                $cnt++;
                                            }
                                        } else {
                                            echo '<tr><td colspan="9" class="text-center">No books found in the database.</td></tr>';
                                        }
                                    } catch(PDOException $e) {
                                        echo '<tr><td colspan="9" class="text-center">Database error: ' . $e->getMessage() . '</td></tr>';
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
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
    <script>
        $(document).ready(function() {
            // Destroy existing DataTable if it exists
            if ($.fn.DataTable.isDataTable('#dataTables-example')) {
                $('#dataTables-example').DataTable().destroy();
            }
            
            // Initialize DataTable
            $('#dataTables-example').DataTable({
                responsive: true,
                "order": [[0, "desc"]],
                "columnDefs": [
                    { "orderable": false, "targets": [1, 7, 8] },
                    { "width": "5%", "targets": 0 },
                    { "width": "10%", "targets": 1 },
                    { "width": "20%", "targets": 2 },
                    { "width": "10%", "targets": [6, 7] },
                    { "width": "15%", "targets": 8 }
                ],
                "language": {
                    "lengthMenu": "Show _MENU_ books per page",
                    "zeroRecords": "No books found",
                    "info": "Showing _START_ to _END_ of _TOTAL_ books",
                    "infoEmpty": "No books available",
                    "infoFiltered": "(filtered from _MAX_ total books)",
                    "search": "Search books:",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Next",
                        "previous": "Previous"
                    }
                }
            });
        });
    </script>
</body>
</html>
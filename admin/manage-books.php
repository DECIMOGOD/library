<?php
// Start session and include database connection
session_start();
include('../includes/config.php');

// Define constants for image paths
define('BOOK_IMAGE_DIR', 'shared/bookImg/');
define('PLACEHOLDER_IMAGE', 'shared/bookImg/placeholder.jpg');

// Check if delete action is requested
if(isset($_GET['del'])) {
    $bookid = intval($_GET['del']);
    try {
        $sql = "DELETE FROM tblbooks WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $bookid, PDO::PARAM_INT);
        $query->execute();
        $_SESSION['msg'] = "Book deleted successfully";
        header('location:manage-books.php');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error deleting book: " . $e->getMessage();
        header('location:manage-books.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="Online Library Management System" />
    <title>Library Management System | Manage Books</title>
    
    <!-- Core Styles -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
    <link href="assets/css/manage-books-style.css" rel="stylesheet" />
        
</head>
<body>
    <!-- HEADER SECTION -->
    <?php include('includes/header.php'); ?>
    <!-- MAIN CONTENT -->
    <div class="content-wrapper">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-12">
                    <h4 class="header-line">Manage Books</h4>
                    
                    <!-- Display success message -->
                    <?php if(isset($_SESSION['msg'])) { ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Success!</strong> <?php echo htmlentities($_SESSION['msg']); unset($_SESSION['msg']); ?>
                        </div>
                    </div>
                    <?php } ?>
                    
                    <!-- Display error message -->
                    <?php if(isset($_SESSION['error'])) { ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            <strong>Error!</strong> <?php echo htmlentities($_SESSION['error']); unset($_SESSION['error']); ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>Book Collection</div>
                            <a href="add-book.php" class="btn btn-primary btn-add">
                                <i class="fas fa-plus"></i> Add New Book
                            </a>
                        </div>
                        <div class="panel-body">
                            <div class="sort-controls">
                                <label>Sort by:</label>
                                <select id="sort-select" class="sort-select">
                                    <option value="0_desc">Latest to Oldest</option>
                                    <option value="0_asc">Oldest to Latest</option>
                                    <option value="2_asc">A-Z (Book Name)</option>
                                    <option value="2_desc">Z-A (Book Name)</option>
                                    <option value="4_asc">A-Z (Publisher)</option>
                                    <option value="4_desc">Z-A (Publisher)</option>
                                </select>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table" id="books-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%">#</th>
                                            <th style="width: 8%">Cover</th>
                                            <th style="width: 20%">Book Name</th>
                                            <th style="width: 12%">Category</th>
                                            <th style="width: 15%">Publisher</th>
                                            <th style="width: 12%">ISBN</th>
                                            <th style="width: 6%">Qty</th>
                                            <th style="width: 10%">Status</th>
                                            <th style="width: 12%">Actions</th>
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
                                                
                                                // Determine book status with modern badges
                                                $status = '';
                                                if($result->isIssued == 1) {
                                                    $status = '<span class="badge badge-warning">Issued</span>';
                                                } elseif($result->bookQty <= 0) {
                                                    $status = '<span class="badge badge-danger">Out of Stock</span>';
                                                } else {
                                                    $status = '<span class="badge badge-success">Available</span>';
                                                }
                                    ?>
                                        <tr>
                                            <td><?php echo htmlentities($cnt); ?></td>
                                            <td>
                                                <img src="<?php echo $imagePath; ?>" class="book-image" 
                                                     onerror="this.onerror=null;this.src='<?php echo PLACEHOLDER_IMAGE; ?>'">
                                            </td>
                                            <td>
                                                <strong><?php echo htmlentities($result->BookName); ?></strong>
                                            </td>
                                            <td><?php echo htmlentities($result->CategoryName); ?></td>
                                            <td><?php echo htmlentities($result->PublisherName); ?></td>
                                            <td><?php echo htmlentities($result->ISBNNumber); ?></td>
                                            <td><?php echo htmlentities($result->bookQty); ?></td>
                                            <td><?php echo $status; ?></td>
                                            <td>
                                                <div class="actions">
                                                    <a href="view-book.php?bookid=<?php echo htmlentities($result->id); ?>" 
                                                       class="btn btn-action btn-view" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit-book.php?bookid=<?php echo htmlentities($result->id); ?>" 
                                                       class="btn btn-action btn-edit" title="Edit Book">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="manage-books.php?del=<?php echo htmlentities($result->id); ?>" 
                                                       class="btn btn-action btn-delete" title="Delete Book"
                                                       onclick="return confirm('Are you sure you want to delete this book?');">
                                                        <i class="fas fa-trash"></i>
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
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER SECTION -->
    <footer>
        <!-- Footer content would go here -->
    </footer>

    <!-- JAVASCRIPT FILES -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Initialize DataTable with modern options
        var table = $('#books-table').DataTable({
            responsive: true,
            order: [[0, "desc"]], // Default sort by ID (latest first)
            columnDefs: [
                { orderable: false, targets: [1, 8] }, // Disable sorting for image and actions columns
                { className: "align-middle", targets: "_all" }
            ],
            language: {
                lengthMenu: "Show _MENU_ books per page",
                zeroRecords: "No books found in collection",
                info: "_START_-_END_ of _TOTAL_ books",
                infoEmpty: "No books available",
                infoFiltered: "(filtered from _MAX_ books)",
                search: "",
                searchPlaceholder: "Search books...",
                paginate: {
                    first: '<i class="fas fa-angle-double-left"></i>',
                    last: '<i class="fas fa-angle-double-right"></i>',
                    previous: '<i class="fas fa-angle-left"></i>',
                    next: '<i class="fas fa-angle-right"></i>'
                }
            },
            dom: '<"top"lf>rt<"bottom"ip><"clear">',
            stateSave: true,
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
            drawCallback: function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-sm');
            }
        });

        // Enhanced sorting control
        $('#sort-select').on('change', function() {
            var val = $(this).val().split('_');
            var col = parseInt(val[0]);
            var dir = val[1];
            table.order([col, dir]).draw();
        });
        
        // Add animation to table rows
        $('.table tbody tr').each(function(index) {
            $(this).css({
                'animation': 'fadeInUp 0.5s ease forwards',
                'animation-delay': (index * 0.05) + 's',
                'opacity': '0'
            });
        });
        
        // Auto close alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });
    </script>
</body>
</html>
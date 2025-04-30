<?php
// Start session and include database connection
session_start();
include('../includes/config.php');

// Define constants for image paths (using absolute paths for reliability)
define('BOOK_IMAGE_DIR', '../shared/bookImg/');
define('PLACEHOLDER_IMAGE', '../shared/bookImg/placeholder.jpg');

// Check if delete action is requested
if(isset($_GET['del'])) {
    $bookid = intval($_GET['del']);
    try {
        // First get the image path to delete the file
        $sql = "SELECT bookImage FROM tblbooks WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $bookid, PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_OBJ);
        
        // Delete the book record
        $sql = "DELETE FROM tblbooks WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $bookid, PDO::PARAM_INT);
        $query->execute();
        
        // Delete the associated image file if it's not the placeholder
        if($result && !empty($result->bookImage) && $result->bookImage != 'placeholder.jpg') {
            $imagePath = BOOK_IMAGE_DIR . $result->bookImage;
            if(file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
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
    
    <!-- Bootstrap CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <!-- Custom Dashboard CSS -->
    <link href="assets/css/dashboard-style.css" rel="stylesheet" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
    <style>
        body {
            background-color: #1e293b;
            color: #ffffff;
        }
        .book-image {
            width: 50px;
            height: 70px;
            object-fit: cover;
            border-radius: 3px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .actions .btn-action {
            padding: 5px 8px;
            margin: 0 2px;
        }
        .badge {
            font-size: 0.85em;
            padding: 5px 8px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Dark Theme Styles */
        .content-wrapper {
            background-color: #1e293b;
            color: #ffffff;
        }
        .panel {
            background-color: #1e293b;
            border: 1px solid #334155;
            color: #ffffff;
        }
        .panel-heading {
            background-color: #0f172a;
            color: #ffffff;
            border-bottom: 1px solid #334155;
        }
        .table {
            background-color: #1e293b;
            color: #ffffff;
        }
        .table th {
            background-color: #0f172a;
            color: #ffffff;
            border-color: #334155;
        }
        .table td {
            border-color: #334155;
            color: #ffffff;
        }
        .table-hover tbody tr:hover {
            background-color: #334155;
            color: #ffffff;
        }
        .form-control, 
        .form-select {
            background-color: #0f172a;
            color: #ffffff;
            border-color: #334155;
        }
        .form-control:focus, 
        .form-select:focus {
            background-color: #0f172a;
            color: #ffffff;
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.25);
        }
        .alert {
            background-color: #0f172a;
            border-color: #334155;
            color: #ffffff;
        }
        .alert-success {
            background-color: #064e3b;
            border-color: #047857;
            color: #ffffff;
        }
        .alert-danger {
            background-color: #7f1d1d;
            border-color: #b91c1c;
            color: #ffffff;
        }
        .page-item.disabled .page-link {
            background-color: #0f172a;
            border-color: #334155;
            color: #64748b;
        }
        .page-link {
            background-color: #0f172a;
            border-color: #334155;
            color: #ffffff;
        }
        .page-item.active .page-link {
            background-color: #4f46e5;
            border-color: #4f46e5;
            color: #ffffff;
        }
        .dataTables_info {
            color: #94a3b8 !important;
        }
        .btn-primary {
            background-color: #4f46e5;
            border-color: #4f46e5;
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: #4338ca;
            border-color: #4338ca;
            color: #ffffff;
        }
        .btn-warning {
            background-color: #f59e0b;
            border-color: #f59e0b;
            color: #1e293b;
        }
        .btn-danger {
            background-color: #dc2626;
            border-color: #dc2626;
            color: #ffffff;
        }
        .header-line {
            color: #ffffff;
        }
        .footer {
            color: #ffffff;
            background-color: #0f172a !important;
        }
        .sort-controls label {
            color: #ffffff;
        }
        /* Fix for DataTables search box */
        .dataTables_filter input {
            color: white !important;
        }
        /* Fix for select dropdown text */
        .dataTables_length select option {
            color: #1e293b;
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
                            <div class="sort-controls mb-3">
                                <label>Sort by:</label>
                                <select id="sort-select" class="form-select form-select-sm" style="width: auto; display: inline-block;">
                                    <option value="0_desc">Latest to Oldest</option>
                                    <option value="0_asc">Oldest to Latest</option>
                                    <option value="2_asc">A-Z (Book Name)</option>
                                    <option value="2_desc">Z-A (Book Name)</option>
                                    <option value="4_asc">A-Z (Publisher)</option>
                                    <option value="4_desc">Z-A (Publisher)</option>
                                </select>
                            </div>
                            
                            <div class="table-responsive">  
                                <table class="table table-hover" id="books-table">
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
                                                $imagePath = (!empty($imageFile) && file_exists(BOOK_IMAGE_DIR . $imageFile)) 
                                                    ? BOOK_IMAGE_DIR . $imageFile 
                                                    : PLACEHOLDER_IMAGE;
                                                
                                                // Determine book status with modern badges
                                                $status = '';
                                                if($result->isIssued == 1) {
                                                    $status = '<span class="badge bg-warning text-dark">Issued</span>';
                                                } elseif($result->bookQty <= 0) {
                                                    $status = '<span class="badge bg-danger">Out of Stock</span>';
                                                } else {
                                                    $status = '<span class="badge bg-success">Available</span>';
                                                }
                                    ?>
                                        <tr>
                                            <td><?php echo htmlentities($cnt); ?></td>
                                            <td>
                                                <img src="<?php echo $imagePath; ?>" class="book-image" 
                                                     onerror="this.onerror=null;this.src='<?php echo PLACEHOLDER_IMAGE; ?>'"
                                                     alt="<?php echo htmlentities($result->BookName); ?> cover">
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
                                                       class="btn btn-sm btn-action btn-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit-book.php?bookid=<?php echo htmlentities($result->id); ?>" 
                                                       class="btn btn-sm btn-action btn-warning" title="Edit Book">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="manage-books.php?del=<?php echo htmlentities($result->id); ?>" 
                                                       class="btn btn-sm btn-action btn-danger" title="Delete Book"
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
                                            echo '<tr><td colspan="9" class="text-center py-4">No books found in the database.</td></tr>';
                                        }
                                    } catch(PDOException $e) {
                                        echo '<tr><td colspan="9" class="text-center py-4 text-danger">Database error: ' . htmlentities($e->getMessage()) . '</td></tr>';
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
    <footer class="footer mt-auto py-3">
        <div class="container">
            <div class="text-center">
                &copy; <?php echo date('Y'); ?> Library Management System
            </div>
        </div>
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
                { className: "align-middle", targets: "_all" },
                { searchable: false, targets: [1, 6, 7, 8] } // Disable search for these columns
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
            dom: '<"top"<"row"<"col-md-6"l><"col-md-6"f>>>rt<"bottom"<"row"<"col-md-6"i><"col-md-6"p>>><"clear">',
            stateSave: true,
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
            initComplete: function() {
                $('.dataTables_filter input').addClass('form-control form-control-sm');
                $('.dataTables_length select').addClass('form-select form-select-sm');
                // Force white text in search box
                $('.dataTables_filter input').css('color', 'white');
            },
            drawCallback: function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-sm');
                // Add animation to table rows
                $('#books-table tbody tr').each(function(index) {
                    $(this).css({
                        'animation': 'fadeInUp 0.5s ease forwards',
                        'animation-delay': (index * 0.05) + 's',
                        'opacity': '0'
                    });
                });
            }
        });

        // Enhanced sorting control
        $('#sort-select').on('change', function() {
            var val = $(this).val().split('_');
            var col = parseInt(val[0]);
            var dir = val[1];
            table.order([col, dir]).draw();
        });
        
        // Auto close alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });
    </script>
</body>
</html>
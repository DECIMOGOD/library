<?php
session_start();
include('includes/config.php');

// Check if admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('location:index.php');
    exit;
}

// Handle form submission for updating featured books
if (isset($_POST['update'])) {
    $bookId = intval($_POST['bookId']);
    $isFeatured = intval($_POST['isFeatured']);

    $sql = "UPDATE tblbooks SET isFeatured = :isFeatured WHERE id = :bookId";
    $query = $dbh->prepare($sql);
    $query->bindParam(':isFeatured', $isFeatured, PDO::PARAM_INT);
    $query->bindParam(':bookId', $bookId, PDO::PARAM_INT);
    $query->execute();

    $_SESSION['msg'] = "Featured book updated successfully!";
    header('location:edit-featured-books.php');
    exit;
}

// Fetch all books with additional details
$sql = "SELECT tblbooks.id, tblbooks.BookName, tblbooks.isFeatured, tblcategory.CategoryName, 
        tblpublishers.PublisherName, tblbooks.ISBNNumber, tblbooks.bookImage, tblbooks.bookQty, 
        tblbooks.copyrightDate, tblbooks.edition, tblbooks.coverType, tblbooks.pages, 
        tblbooks.height, tblbooks.shelfLocation, tblbooks.notes, tblbooks.callNumber, 
        tblbooks.LRN 
        FROM tblbooks 
        JOIN tblcategory ON tblcategory.id = tblbooks.CatId 
        JOIN tblpublishers ON tblpublishers.id = tblbooks.PublisherID";
$query = $dbh->prepare($sql);
$query->execute();
$books = $query->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Featured Books</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FONT AWESOME STYLE -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- DATATABLE STYLE -->
    <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
    <!-- CUSTOM STYLE -->
    <link href="assets/css/edit-book-featured-style.css" rel="stylesheet" />
    <!-- GOOGLE FONT -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
    <style>
        body {
            background-color: #ffffff; /* Set background color to white */
        }
        .dataTables_filter {
            display: none; /* Hide the default DataTables search */
        }
        .search-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 15px;
        }
        .search-container input {
            width: 30% !important;
        }
    </style>
</head>
<body>
    <?php include('includes/header.php'); ?>
    <div class="container mt-4">
        <h2>Edit Featured Books</h2>
        <?php if (isset($_SESSION['msg'])) { ?>
            <div class="alert alert-success">
                <?php echo htmlentities($_SESSION['msg']); unset($_SESSION['msg']); ?>
            </div>
        <?php } ?>

        <!-- Search Bar and Sort Filter -->
        <div class="search-container">
            <input type="text" id="searchBar" class="form-control" placeholder="Search books by name...">
            <select id="sortFilter" class="form-select ms-2" style="width: 30%;">
                <option value="desc" selected>Latest to Oldest</option>
                <option value="asc">Oldest to Latest</option>
            </select>
        </div>

        <table class="table table-hover table-striped table-bordered table-responsive-md text-center align-middle shadow-sm" id="booksTable">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Cover</th>
                    <th>Book Name</th>
                    <th>Category</th>
                    <th>Publisher</th>
                    <th>ISBN</th>
                    <th>Quantity</th>
                    <th>Edition</th>
                    <th>Pages</th>
                    <th>Featured</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($query->rowCount() > 0) {
                    $cnt = 1;
                    foreach ($books as $book) { ?>
                        <tr>
                            <td><?php echo htmlentities($cnt); ?></td>
                            <td>
                                <img src="../shared/bookImg/<?php echo htmlentities($book->bookImage); ?>" 
                                     alt="<?php echo htmlentities($book->BookName); ?>" 
                                     class="img-thumbnail" style="width: 50px; height: auto;">
                            </td>
                            <td class="book-name"><?php echo htmlentities($book->BookName); ?></td>
                            <td><?php echo htmlentities($book->CategoryName); ?></td>
                            <td><?php echo htmlentities($book->PublisherName); ?></td>
                            <td><?php echo htmlentities($book->ISBNNumber); ?></td>
                            <td><?php echo htmlentities($book->bookQty); ?></td>
                            <td><?php echo htmlentities($book->edition); ?></td>
                            <td><?php echo htmlentities($book->pages); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $book->isFeatured ? 'success' : 'secondary'; ?>">
                                    <?php echo $book->isFeatured ? 'Yes' : 'No'; ?>
                                </span>
                            </td>
                            <td>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="bookId" value="<?php echo htmlentities($book->id); ?>">
                                    <select name="isFeatured" class="form-select form-select-sm d-inline-block w-auto">
                                        <option value="1" <?php echo $book->isFeatured ? 'selected' : ''; ?>>Yes</option>
                                        <option value="0" <?php echo !$book->isFeatured ? 'selected' : ''; ?>>No</option>
                                    </select>
                                    <button type="submit" name="update" class="btn btn-sm btn-primary mt-1">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php $cnt++; }
                } else { ?>
                    <tr>
                        <td colspan="11" class="text-muted">No books found.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Include DataTables scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            var table = $('#booksTable').DataTable({
                "pageLength": 10,
                "lengthChange": false,
                "ordering": true, // Enable ordering
                "order": [[0, "desc"]], // Default order: Latest to Oldest
                "info": true,
                "paging": true
            });
            
            // Custom search functionality
            $('#searchBar').keyup(function(){
                table.search($(this).val()).draw();
            });

            // Sort filter functionality
            $('#sortFilter').change(function () {
                var order = $(this).val() === 'desc' ? 'desc' : 'asc';
                table.order([0, order]).draw();
            });
        });
    </script>
</body>
</html>
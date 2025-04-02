<?php
session_start();
include('includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['login'])) {
    header('location:index.php');
    exit();
}

// Set default role if not set
$role = $_SESSION['role'] ?? 'student';

// Search functionality
$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$booksPerPage = 5;
$offset = ($page - 1) * $booksPerPage;

// Base SQL query
$sql = "SELECT SQL_CALC_FOUND_ROWS 
        tblbooks.*, tblpublishers.PublisherName, tblcategory.CategoryName 
        FROM tblbooks 
        LEFT JOIN tblpublishers ON tblpublishers.id = tblbooks.PublisherID
        LEFT JOIN tblcategory ON tblcategory.id = tblbooks.CatId
        WHERE tblbooks.BookName LIKE :search 
           OR tblbooks.ISBNNumber LIKE :search
           OR tblpublishers.PublisherName LIKE :search
           OR tblcategory.CategoryName LIKE :search
        ORDER BY tblbooks.BookName
        LIMIT :offset, :booksPerPage";

$query = $dbh->prepare($sql);
$searchParam = "%$search%";
$query->bindParam(':search', $searchParam, PDO::PARAM_STR);
$query->bindParam(':offset', $offset, PDO::PARAM_INT);
$query->bindParam(':booksPerPage', $booksPerPage, PDO::PARAM_INT);
$query->execute();
$books = $query->fetchAll(PDO::FETCH_OBJ);

// Get total books count
$totalBooks = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn();
$totalPages = ceil($totalBooks / $booksPerPage);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Online Library Management System | Books Listing</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
    <style>
        .book-card {
            border: 1px solid #e1e1e1;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            height: 100%;
            transition: all 0.3s ease;
        }
        .book-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .book-image {
            max-height: 180px;
            width: auto;
            margin: 0 auto;
            display: block;
        }
        .search-box {
            margin-bottom: 30px;
        }
        .pagination {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include('includes/header.php'); ?>
    
    <div class="content-wrapper">
        <div class="container">
            <div class="row pad-botm">
                <div class="col-md-12">
                    <h4 class="header-line">Available Books</h4>
                </div>
            </div>

            <!-- Search Box -->
            <div class="row search-box">
                <div class="col-md-6 col-md-offset-3">
                    <form method="get" action="">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search by book, author, ISBN..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <span class="input-group-btn">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fa fa-search"></i> Search
                                </button>
                                <?php if (!empty($search)): ?>
                                    <a href="listed-books.php" class="btn btn-default">
                                        <i class="fa fa-times"></i> Clear
                                    </a>
                                <?php endif; ?>
                            </span>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <?php if (!empty($books)): ?>
                                <div class="row">
                                    <?php foreach ($books as $book): ?>
                                        <div class="col-md-4">
                                            <div class="book-card">
                                                <div class="text-center">
                                                    <img src="shared/bookimg/<?php echo htmlentities($book->bookImage); ?>" 
                                                         class="book-image img-responsive">
                                                </div>
                                                <h4><?php echo htmlentities($book->BookName); ?></h4>
                                                <p><strong>Publisher:</strong> <?php echo htmlentities($book->PublisherName); ?></p>
                                                <p><strong>Category:</strong> <?php echo htmlentities($book->CategoryName); ?></p>
                                                <p><strong>ISBN:</strong> <?php echo htmlentities($book->ISBNNumber); ?></p>
                                                <p><strong>Available:</strong> <?php echo htmlentities($book->bookQty); ?></p>
                                                
                                                <?php if ($role == 'librarian'): ?>
                                                    <a href="issue-book.php?bookid=<?php echo $book->id; ?>" 
                                                       class="btn btn-primary btn-sm btn-block">
                                                        <i class="fa fa-book"></i> Issue This Book
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Pagination -->
                                <div class="text-center">
                                    <ul class="pagination">
                                        <?php if ($page > 1): ?>
                                            <li><a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>">&laquo;</a></li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <li class="<?php echo $i == $page ? 'active' : ''; ?>">
                                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                            <li><a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>">&raquo;</a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info text-center">
                                    No books found matching your search criteria.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    
    
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.js"></script>
</body>
</html>
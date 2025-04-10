<?php
session_start();
include('includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['login'])) {
    header('location:index.php');
    exit();
}

// Search and pagination
$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$booksPerPage = 8; // Changed from 5 to 8
$offset = ($page - 1) * $booksPerPage;

// Main query
$sql = "SELECT SQL_CALC_FOUND_ROWS 
        tblbooks.*, tblcategory.CategoryName 
        FROM tblbooks 
        LEFT JOIN tblcategory ON tblcategory.id = tblbooks.CatId
        WHERE tblbooks.BookName LIKE :search 
           OR tblbooks.ISBNNumber LIKE :search
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

// Get total count for pagination
$totalBooks = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn();
$totalPages = ceil($totalBooks / $booksPerPage);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Online Library Management System | Books Listing</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .container {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .header-line {
            text-align: center;
            width: 100%;
            margin-bottom: 30px;
        }
        .search-box {
            margin: 20px auto;
            max-width: 800px;
            width: 100%;
            display: flex;
            justify-content: center;
        }
        .search-box form {
            width: 100%;
            display: flex;
            justify-content: center;
        }
        .input-group {
            max-width: 600px;
            width: 100%;
        }
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }
        .book-card {
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            padding: 15px;
            transition: all 0.3s ease;
            background: #fff;
            height: 100%;
            display: flex;
            flex-direction: column;
            text-align: center;
            margin: 0 auto;
            max-width: 240px;
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .book-image-container {
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        .book-image {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }
        .book-info {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .book-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
            text-align: center;
        }
        .book-detail {
            margin-bottom: 8px;
            font-size: 14px;
            text-align: center;
            width: 100%;
        }
        .availability {
            font-weight: bold;
            margin-top: 10px;
            text-align: center;
            width: 100%;
        }
        .available {
            color: #28a745;
        }
        .unavailable {
            color: #dc3545;
        }
        .no-books {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px;
            width: 100%;
        }
        .pagination-container {
            display: flex;
            justify-content: center;
            width: 100%;
            margin: 30px 0;
        }
        .pagination {
            display: flex;
            justify-content: center;
        }
    </style>
</head>
<body>
    <?php include('includes/header.php'); ?>
    
    <div class="content-wrapper">
        <div class="container">
            <div class="row pad-botm">
                <div class="col-md-12">
                    <h2 class="header-line">Available Books</h2>
                </div>
            </div>

            <!-- Centered Search Box -->
            <div class="row">
                <div class="col-md-12">
                    <div class="search-box">
                        <form method="get" class="input-group">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search by book title, ISBN or category..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fa fa-search"></i> Search
                                </button>
                                <?php if (!empty($search)): ?>
                                    <a href="listed-books.php" class="btn btn-outline-secondary">
                                        <i class="fa fa-times"></i> Clear
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="book-grid">
                        <?php if (empty($books)): ?>
                            <div class="no-books alert alert-info">
                                No books found matching your criteria.
                            </div>
                        <?php else: ?>
                            <?php foreach ($books as $book): 
                                $available = $book->bookQty - ($book->issuedBooks ?? 0) + ($book->returnedbook ?? 0);
                            ?>
                                <div class="book-card">
                                    <div class="book-image-container">
                                        <img src="shared/bookImg/<?php echo htmlentities($book->bookImage); ?>" 
                                             class="book-image" 
                                             alt="<?php echo htmlentities($book->BookName); ?>"
                                             onerror="this.src='assets/img/default-book.png'">
                                    </div>
                                    <div class="book-info">
                                        <h5 class="book-title"><?php echo htmlentities($book->BookName); ?></h5>
                                        <div class="book-detail">
                                            <strong>Category:</strong> <?php echo htmlentities($book->CategoryName); ?>
                                        </div>
                                        <div class="book-detail">
                                            <strong>ISBN:</strong> <?php echo htmlentities($book->ISBNNumber); ?>
                                        </div>
                                        <div class="book-detail">
                                            <strong>Total Copies:</strong> <?php echo htmlentities($book->bookQty); ?>
                                        </div>
                                        <div class="availability <?php echo ($available > 0) ? 'available' : 'unavailable'; ?>">
                                            <strong>Available:</strong> <?php echo max(0, $available); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Centered Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination-container">
                            <nav aria-label="Page navigation">
                                <ul class="pagination">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>">
                                                Previous
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>">
                                                Next
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.js"></script>
</body>
</html>
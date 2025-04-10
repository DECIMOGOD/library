<?php
// Database connection
$host = '127.0.0.1';
$dbname = 'library';
$username = 'root'; // Change as needed
$password = ''; // Change as needed

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Function to count total books
function countBooks($pdo, $search = '', $category = null, $publisher = null) {
    $sql = "SELECT COUNT(*) as total 
            FROM tblbooks b
            JOIN tblcategory c ON b.CatId = c.id
            JOIN tblpublishers p ON b.PublisherID = p.id
            WHERE (b.BookName LIKE :search OR p.PublisherName LIKE :search OR b.ISBNNumber LIKE :search)
            AND c.Status = 1";
    
    $params = [':search' => "%$search%"];
    
    if ($category) {
        $sql .= " AND b.CatId = :category";
        $params[':category'] = $category;
    }
    
    if ($publisher) {
        $sql .= " AND b.PublisherID = :publisher";
        $params[':publisher'] = $publisher;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

// Function to get paginated books
function getBooks($pdo, $search = '', $category = null, $publisher = null, $page = 1, $perPage = 6) {
    $offset = ($page - 1) * $perPage;
    
    $sql = "SELECT b.*, c.CategoryName, p.PublisherName 
            FROM tblbooks b
            JOIN tblcategory c ON b.CatId = c.id
            JOIN tblpublishers p ON b.PublisherID = p.id
            WHERE (b.BookName LIKE :search OR p.PublisherName LIKE :search OR b.ISBNNumber LIKE :search)
            AND c.Status = 1";
    
    $params = [
        ':search' => "%$search%",
        ':perPage' => $perPage,
        ':offset' => $offset
    ];
    
    if ($category) {
        $sql .= " AND b.CatId = :category";
        $params[':category'] = $category;
    }
    
    if ($publisher) {
        $sql .= " AND b.PublisherID = :publisher";
        $params[':publisher'] = $publisher;
    }
    
    $sql .= " ORDER BY b.BookName LIMIT :perPage OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    
    // Bind parameters
    foreach ($params as $key => &$value) {
        $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue($key, $value, $paramType);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get all categories
function getCategories($pdo) {
    $stmt = $pdo->query("SELECT * FROM tblcategory WHERE Status = 1 ORDER BY CategoryName");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get all publishers
function getPublishers($pdo) {
    $stmt = $pdo->query("SELECT * FROM tblpublishers ORDER BY PublisherName");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? null;
$publisher = $_GET['publisher'] ?? null;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 6; // Books per page

// Get data
$totalBooks = countBooks($pdo, $search, $category, $publisher);
$totalPages = ceil($totalBooks / $perPage);
$books = getBooks($pdo, $search, $category, $publisher, $page, $perPage);
$categories = getCategories($pdo);
$publishers = getPublishers($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Book Catalog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 150px;
        }
        .search-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
            margin-top: 20px;
        }
        .no-results {
            text-align: center;
            padding: 50px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .book-card {
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .book-image {
            height: 200px;
            object-fit: contain;
            background-color: #f1f1f1;
        }
        .availability-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .book-details {
            font-size: 0.9rem;
        }
        .detail-label {
            font-weight: 600;
            color: #495057;
        }
        .pagination {
            margin-top: 30px;
        }
        .modal-body th {
            width: 35%;
        }
        .physical-details {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-top: 15px;
        }
        .physical-details h6 {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="container">
    <div class="search-card">
        <form method="get" action="">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label">Search Books</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Search by title, publisher, or ISBN">
                </div>
                <div class="col-md-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($category == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['CategoryName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="publisher" class="form-label">Publisher</label>
                    <select class="form-select" id="publisher" name="publisher">
                        <option value="">All Publishers</option>
                        <?php foreach ($publishers as $pub): ?>
                            <option value="<?= $pub['id'] ?>" <?= ($publisher == $pub['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($pub['PublisherName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='?'">Reset Filters</button>
                </div>
            </div>
        </form>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php if (count($books) > 0): ?>
            <?php foreach ($books as $book): ?>
                <div class="col">
                    <div class="card book-card h-100 position-relative">
                        <?php if ($book['bookQty'] > 0): ?>
                            <span class="badge bg-success availability-badge">Available (<?= $book['bookQty'] ?>)</span>
                        <?php else: ?>
                            <span class="badge bg-danger availability-badge">Checked Out</span>
                        <?php endif; ?>
                        
                        <?php if (!empty($book['bookImage'])): ?>
                            <img src="shared/bookImg/<?= htmlspecialchars($book['bookImage']) ?>" 
                                 class="card-img-top book-image p-3" 
                                 alt="<?= htmlspecialchars($book['BookName']) ?>">
                        <?php else: ?>
                            <div class="book-image d-flex align-items-center justify-content-center p-3">
                                <i class="fas fa-book fa-5x text-secondary"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($book['BookName']) ?></h5>
                            <p class="card-text text-muted">by <?= htmlspecialchars($book['PublisherName']) ?></p>
                            
                            <div class="book-details mt-3">
                                <div class="mb-2">
                                    <span class="detail-label">Category:</span> 
                                    <?= htmlspecialchars($book['CategoryName']) ?>
                                </div>
                                
                                <?php if ($book['ISBNNumber']): ?>
                                    <div class="mb-2">
                                        <span class="detail-label">ISBN:</span> 
                                        <?= htmlspecialchars($book['ISBNNumber']) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($book['edition']): ?>
                                    <div class="mb-2">
                                        <span class="detail-label">Edition:</span> 
                                        <?= htmlspecialchars($book['edition']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-transparent">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" 
                                    data-bs-target="#bookModal<?= $book['id'] ?>">
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Modal for each book with all details -->
                <div class="modal fade" id="bookModal<?= $book['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><?= htmlspecialchars($book['BookName']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <?php if (!empty($book['bookImage'])): ?>
                                            <img src="shared/bookImg/<?= htmlspecialchars($book['bookImage']) ?>" 
                                                 class="img-fluid rounded mb-3" 
                                                 alt="<?= htmlspecialchars($book['BookName']) ?>">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center bg-light mb-3" style="height: 200px;">
                                                <i class="fas fa-book fa-5x text-secondary"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-subtitle mb-2 text-muted">Availability</h6>
                                                <?php if ($book['bookQty'] > 0): ?>
                                                    <span class="badge bg-success">Available (<?= $book['bookQty'] ?> copies)</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Currently checked out</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <table class="table table-sm">
                                            <tr>
                                                <th>Publisher</th>
                                                <td><?= htmlspecialchars($book['PublisherName']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Category</th>
                                                <td><?= htmlspecialchars($book['CategoryName']) ?></td>
                                            </tr>
                                            
                                            <?php if ($book['ISBNNumber']): ?>
                                                <tr>
                                                    <th>ISBN</th>
                                                    <td><?= htmlspecialchars($book['ISBNNumber']) ?></td>
                                                </tr>
                                            <?php endif; ?>
                                            
                                            <?php if ($book['LRN']): ?>
                                                <tr>
                                                    <th>LRN</th>
                                                    <td><?= htmlspecialchars($book['LRN']) ?></td>
                                                </tr>
                                            <?php endif; ?>
                                            
                                            <?php if ($book['copyrightDate']): ?>
                                                <tr>
                                                    <th>Copyright Year</th>
                                                    <td><?= htmlspecialchars($book['copyrightDate']) ?></td>
                                                </tr>
                                            <?php endif; ?>
                                            
                                            <?php if ($book['edition']): ?>
                                                <tr>
                                                    <th>Edition</th>
                                                    <td><?= htmlspecialchars($book['edition']) ?></td>
                                                </tr>
                                            <?php endif; ?>
                                        </table>
                                        
                                        <!-- Physical Details Section -->
                                        <div class="physical-details">
                                            <h6>Physical Details</h6>
                                            <table class="table table-sm">
                                                <?php if ($book['coverType']): ?>
                                                    <tr>
                                                        <th>Cover Type</th>
                                                        <td><?= htmlspecialchars($book['coverType']) ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                                
                                                <?php if ($book['pages']): ?>
                                                    <tr>
                                                        <th>Pages</th>
                                                        <td><?= htmlspecialchars($book['pages']) ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                                
                                                <?php if ($book['height']): ?>
                                                    <tr>
                                                        <th>Height</th>
                                                        <td><?= htmlspecialchars($book['height']) ?> cm</td>
                                                    </tr>
                                                <?php endif; ?>
                                                
                                                <?php if ($book['shelfLocation']): ?>
                                                    <tr>
                                                        <th>Shelf Location</th>
                                                        <td><?= htmlspecialchars($book['shelfLocation']) ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                            </table>
                                        </div>
                                        
                                        <!-- Library Information Section -->
                                        <div class="physical-details mt-3">
                                            <h6>Library Information</h6>
                                            <table class="table table-sm">
                                                <?php if ($book['callNumber']): ?>
                                                    <tr>
                                                        <th>Call Number</th>
                                                        <td><?= htmlspecialchars($book['callNumber']) ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                                
                                                <tr>
                                                    <th>Added Date</th>
                                                    <td><?= date('M d, Y', strtotime($book['RegDate'])) ?></td>
                                                </tr>
                                                
                                                <?php if ($book['UpdationDate']): ?>
                                                    <tr>
                                                        <th>Last Updated</th>
                                                        <td><?= date('M d, Y', strtotime($book['UpdationDate'])) ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                            </table>
                                        </div>
                                        
                                        <?php if ($book['notes']): ?>
                                            <div class="physical-details mt-3">
                                                <h6>Additional Notes</h6>
                                                <p><?= htmlspecialchars($book['notes']) ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="no-results">
                    <i class="fas fa-book-open fa-4x text-muted mb-3"></i>
                    <h4>No books found matching your criteria</h4>
                    <p class="text-muted">Try adjusting your search filters</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <!-- Previous Page Link -->
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" 
                   href="?search=<?= urlencode($search) ?>&category=<?= $category ?>&publisher=<?= $publisher ?>&page=<?= $page-1 ?>" 
                   aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            
            <!-- Page Numbers -->
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" 
                       href="?search=<?= urlencode($search) ?>&category=<?= $category ?>&publisher=<?= $publisher ?>&page=<?= $i ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
            
            <!-- Next Page Link -->
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link" 
                   href="?search=<?= urlencode($search) ?>&category=<?= $category ?>&publisher=<?= $publisher ?>&page=<?= $page+1 ?>" 
                   aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
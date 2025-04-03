<?php
// Strict error reporting
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Set JSON header first
header('Content-Type: application/json');

// Database configuration
$config = [
    'host' => 'localhost',
    'dbname' => 'library',
    'username' => 'root',
    'password' => ''
];

try {
    // Establish database connection
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
    $conn = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Sanitize and validate input parameters
    $filters = [
        'genre' => $_GET['genre'] ?? '',
        'year' => $_GET['year'] ?? '',
        'sort' => $_GET['sort'] ?? '',
        'search' => $_GET['search'] ?? ''
    ];

    // Base query with correct table joins
    $sql = "SELECT 
                b.id,
                b.BookName,
                DATE_FORMAT(b.RegDate, '%Y-%m-%d') AS RegDate,
                b.bookImage,
                a.AuthorName,
                c.CategoryName
            FROM tblbooks b
            INNER JOIN tblauthors a ON b.AuthorId = a.id
            INNER JOIN tblcategory c ON b.CatId = c.id
            WHERE 1=1";

    $params = [];

    // Apply filters
    if (!empty($filters['genre'])) {
        $sql .= " AND c.CategoryName = :genre";
        $params[':genre'] = $filters['genre'];
    }

    if (!empty($filters['year'])) {
        $year = (int)$filters['year'];
        $currentYear = (int)date('Y');
        
        if ($filters['year'] === '2020') {
            $sql .= " AND YEAR(b.RegDate) >= 2020";
        } elseif ($filters['year'] === '2010') {
            $sql .= " AND YEAR(b.RegDate) BETWEEN 2010 AND 2019";
        } // ... other year cases
    }

    if (!empty($filters['search'])) {
        $sql .= " AND (b.BookName LIKE :search OR a.AuthorName LIKE :search)";
        $params[':search'] = "%{$filters['search']}%";
    }

    // Apply sorting
    switch ($filters['sort']) {
        case 'newest':
            $sql .= " ORDER BY b.RegDate DESC";
            break;
        case 'title':
            $sql .= " ORDER BY b.BookName ASC";
            break;
    }

    // Prepare and execute query
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $books = $stmt->fetchAll();

    // Process results
    $response = [
        'success' => true,
        'data' => array_map(function($book) {
            return [
                'id' => $book['id'],
                'BookName' => $book['BookName'],
                'RegDate' => $book['RegDate'],
                'BookImage' => $book['bookImage'] 
                    ? 'uploads/books/' . $book['bookImage']
                    : 'assets/images/default-book.jpg',
                'AuthorName' => $book['AuthorName'],
                'CategoryName' => $book['CategoryName']
            ];
        }, $books)
    ];

    echo json_encode($response);
    exit;

} catch (PDOException $e) {
    // Database-specific errors
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database Error',
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
    exit;
} catch (Exception $e) {
    // General errors
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Application Error',
        'message' => $e->getMessage()
    ]);
    exit;
}
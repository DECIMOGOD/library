<?php
session_start();
include('includes/config.php');
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['login'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    // Get and sanitize parameters
    $search = $_GET['search'] ?? '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = min(max(5, (int)($_GET['per_page'] ?? 12)), 50);
    $offset = ($page - 1) * $perPage;
    $genre = $_GET['genre'] ?? '';
    $year = $_GET['year'] ?? '';

    // Base query with SQL_CALC_FOUND_ROWS for pagination
    $sql = "SELECT SQL_CALC_FOUND_ROWS 
            b.id, b.BookName, b.ISBNNumber, b.bookImage, b.bookQty, b.RegDate,
            p.PublisherName, c.CategoryName
            FROM tblbooks b
            LEFT JOIN tblpublishers p ON b.PublisherId = p.id
            LEFT JOIN tblcategory c ON b.CatId = c.id
            WHERE 1=1";

    $params = [];
    $types = [];

    // Add search conditions
    if (!empty($search)) {
        $sql .= " AND (b.BookName LIKE :search 
                  OR b.ISBNNumber LIKE :search
                  OR p.PublisherName LIKE :search
                  OR c.CategoryName LIKE :search)";
        $params[':search'] = "%$search%";
        $types[':search'] = PDO::PARAM_STR;
    }

    // Add genre filter
    if (!empty($genre)) {
        $sql .= " AND c.CategoryName = :genre";
        $params[':genre'] = $genre;
        $types[':genre'] = PDO::PARAM_STR;
    }

    // Add year filter
    if (!empty($year)) {
        $yearCondition = match($year) {
            '2020' => "YEAR(b.RegDate) >= 2020",
            '2010' => "YEAR(b.RegDate) BETWEEN 2010 AND 2019",
            '2000' => "YEAR(b.RegDate) BETWEEN 2000 AND 2009",
            '1990' => "YEAR(b.RegDate) BETWEEN 1990 AND 1999",
            'older' => "YEAR(b.RegDate) < 1990",
            default => ""
        };
        
        if ($yearCondition) {
            $sql .= " AND $yearCondition";
        }
    }

    // Add sorting and pagination
    $sql .= " ORDER BY b.BookName ASC LIMIT :offset, :per_page";
    $params[':offset'] = $offset;
    $types[':offset'] = PDO::PARAM_INT;
    $params[':per_page'] = $perPage;
    $types[':per_page'] = PDO::PARAM_INT;

    // Prepare and execute query
    $query = $dbh->prepare($sql);
    
    foreach ($params as $key => $value) {
        $query->bindValue($key, $value, $types[$key] ?? PDO::PARAM_STR);
    }

    $query->execute();
    $books = $query->fetchAll(PDO::FETCH_ASSOC);

    // Get total count
    $total = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn();
    $totalPages = ceil($total / $perPage);

    // Process image paths
    foreach ($books as &$book) {
        $book['bookImage'] = !empty($book['bookImage']) 
            ? 'shared/bookimg/' . $book['bookImage']
            : 'shared/bookimg/placeholder.jpg';
    }

    // Return JSON response
    echo json_encode([
        'success' => true,
        'data' => $books,
        'pagination' => [
            'total' => (int)$total,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
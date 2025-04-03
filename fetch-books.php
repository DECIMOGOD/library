<?php
include 'includes/config.php';

header('Content-Type: application/json');

try {
    // Get parameters from request
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';

    // Base query
    $query = "SELECT 
                b.id, 
                b.BookName, 
                b.ISBNNumber, 
                b.BookPrice, 
                b.bookImage, 
                b.bookQty, 
                c.CategoryName 
              FROM tblbooks b
              LEFT JOIN tblcategory c ON b.CatId = c.id
              WHERE 1=1";

    // Add search condition if provided
    if (!empty($search)) {
        $query .= " AND (b.BookName LIKE :search OR b.ISBNNumber LIKE :search)";
    }

    // Add category filter if selected
    if ($category > 0) {
        $query .= " AND b.CatId = :category";
    }

    // Add sorting
    switch ($sort) {
        case 'price_asc':
            $query .= " ORDER BY b.BookPrice ASC";
            break;
        case 'price_desc':
            $query .= " ORDER BY b.BookPrice DESC";
            break;
        default:
            $query .= " ORDER BY b.BookName ASC";
            break;
    }

    // Prepare and execute the query
    $stmt = $dbh->prepare($query);

    if (!empty($search)) {
        $searchParam = "%$search%";
        $stmt->bindParam(':search', $searchParam);
    }

    if ($category > 0) {
        $stmt->bindParam(':category', $category, PDO::PARAM_INT);
    }

    $stmt->execute();
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Output the results
    echo json_encode($books);

} catch (PDOException $e) {
    // Handle database errors
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
    exit();
}
?>
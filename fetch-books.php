<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$host = 'localhost';
$dbname = 'library';
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Log incoming query parameters for debugging
    file_put_contents('debug_params.log', print_r($_GET, true) . PHP_EOL, FILE_APPEND);

    // Get query parameters
    $genre = $_GET['genre'] ?? '';
    $year = $_GET['year'] ?? '';
    $sort = $_GET['sort'] ?? '';
    $search = $_GET['search'] ?? '';

    // Base query - Updated to use tblpublishers instead of tblauthors
    $sql = "SELECT b.id, b.BookName, b.RegDate, 
                   b.bookImage AS BookImage, p.PublisherName, c.CategoryName 
            FROM tblbooks b
            JOIN tblpublishers p ON b.AuthorId = p.id
            JOIN tblcategory c ON b.CatId = c.id
            WHERE 1=1";

    // Apply filters
    if (!empty($genre)) {
        $sql .= " AND c.CategoryName = :genre";
    }
    if (!empty($year)) {
        if ($year === '2020') {
            $sql .= " AND YEAR(b.RegDate) >= 2020";
        } elseif ($year === '2010') {
            $sql .= " AND YEAR(b.RegDate) BETWEEN 2010 AND 2019";
        } elseif ($year === '2000') {
            $sql .= " AND YEAR(b.RegDate) BETWEEN 2000 AND 2009";
        } elseif ($year === '1990') {
            $sql .= " AND YEAR(b.RegDate) BETWEEN 1990 AND 1999";
        } elseif ($year === 'older') {
            $sql .= " AND YEAR(b.RegDate) < 1990";
        }
    }
    if (!empty($search)) {
        $sql .= " AND (b.BookName LIKE :search OR p.PublisherName LIKE :search OR c.CategoryName LIKE :search)";
    }

    // Apply sorting - Removed price sorting option
    if (!empty($sort)) {
        if ($sort === 'newest') {
            $sql .= " ORDER BY b.RegDate DESC";
        } elseif ($sort === 'title') {
            $sql .= " ORDER BY b.BookName ASC";
        }
    } else {
        // Default sorting if none specified
        $sql .= " ORDER BY b.BookName ASC";
    }

    // Log the final SQL query for debugging
    file_put_contents('debug_sql.log', $sql . PHP_EOL, FILE_APPEND);

    // Prepare and execute the query
    $stmt = $conn->prepare($sql);

    // Bind parameters if they exist
    if (!empty($genre)) {
        $stmt->bindParam(':genre', $genre, PDO::PARAM_STR);
    }
    if (!empty($search)) {
        $searchTerm = "%$search%";
        $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
    }

    $stmt->execute();
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if no books were found
    if (empty($books)) {
        throw new Exception('No books found matching the criteria.');
    }

    // Add the correct image path to each book record
    foreach ($books as &$book) {
        // Check if BookImage field exists and is not empty
        if (isset($book['BookImage']) && !empty($book['BookImage'])) {
            // Update to use the shared folder path
            $book['BookImage'] = '/library/shared/bookImg/' . $book['BookImage'];
        } else {
            // Set a default image if none exists
            $book['BookImage'] = '/library/shared/bookImg/placeholder-book.jpg';
        }
        
        // Rename PublisherName to AuthorName for backward compatibility with frontend
        if (isset($book['PublisherName'])) {
            $book['AuthorName'] = $book['PublisherName'];
            unset($book['PublisherName']); // Remove the original field
        }
    }
    unset($book); // Break the reference to the last element

    // Debug output of the data
    file_put_contents('debug_data.log', print_r($books, true) . PHP_EOL, FILE_APPEND);

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($books);
} catch (PDOException $e) {
    // Log database errors for debugging
    file_put_contents('error.log', date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . "\n", FILE_APPEND);
    
    // Return error as JSON
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    // Log general errors for debugging
    file_put_contents('error.log', date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . "\n", FILE_APPEND);
    
    // Return error as JSON
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
<?php 
include 'includes/config.php';
try {
    // Fetch categories using PDO
    $catQuery = $dbh->query("SELECT * FROM tblcategory");
    $categories = $catQuery->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Catalog</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .controls {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h2 {
            margin-top: 0;
            color: #333;
        }
        .search-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 15px;
            align-items: center;
        }
        input, select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        button {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background-color: #45a049;
        }
        .book-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }
        .book-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .book-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid #eee;
        }
        .book-info {
            padding: 15px;
        }
        .book-title {
            font-weight: bold;
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #333;
            height: 40px;
            overflow: hidden;
        }
        .book-detail {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
        .no-books {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="controls">
            <h2>Book Catalog</h2>
            <div class="search-row">
                <input type="text" id="search" placeholder="Search by name or ISBN">
                <select id="category">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['id']) ?>">
                            <?= htmlspecialchars($cat['CategoryName']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select id="sort">
                    <option value="name_asc">Sort by Name (A-Z)</option>
                    <option value="price_asc">Sort by Price (Low to High)</option>
                    <option value="price_desc">Sort by Price (High to Low)</option>
                </select>
                <button id="search-btn">Search</button>
            </div>
        </div>
        
        <div class="book-container" id="book-list">
            <!-- Books will be loaded here -->
        </div>
    </div>
    
    <script>
        $(document).ready(function() {
            // Load books on page load
            fetchBooks();
            
            // Search button click handler
            $('#search-btn').click(fetchBooks);
            
            // Enter key in search field
            $('#search').keypress(function(e) {
                if (e.which === 13) {
                    fetchBooks();
                }
            });
            
            // Category/sort change handlers
            $('#category, #sort').change(fetchBooks);
        });
        
        function fetchBooks() {
            const search = $('#search').val();
            const category = $('#category').val();
            const sort = $('#sort').val();
            
            $('#book-list').html('<div class="no-books">Loading books...</div>');
            
            $.ajax({
                url: 'fetchbook.php',
                type: 'GET',
                data: {
                    search: search,
                    category: category,
                    sort: sort
                },
                dataType: 'json',
                success: function(books) {
                    let html = '';
                    
                    if (books.length === 0) {
                        html = '<div class="no-books">No books found matching your criteria.</div>';
                    } else {
                        books.forEach(book => {
                            html += `
                            <div class="book-card">
                                <img src="uploads/${book.bookImage}" alt="${book.BookName}" class="book-image">
                                <div class="book-info">
                                    <h3 class="book-title">${book.BookName}</h3>
                                    <p class="book-detail"><strong>ISBN:</strong> ${book.ISBNNumber}</p>
                                    <p class="book-detail"><strong>Price:</strong> $${book.BookPrice}</p>
                                    <p class="book-detail"><strong>Category:</strong> ${book.CategoryName}</p>
                                    <p class="book-detail"><strong>Available:</strong> ${book.bookQty}</p>
                                </div>
                            </div>`;
                        });
                    }
                    
                    $('#book-list').html(html);
                },
                error: function(xhr, status, error) {
                    $('#book-list').html(`<div class="no-books">Error loading books: ${error}</div>`);
                    console.error("AJAX Error:", status, error);
                }
            });
        }
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Catalog</title>
    <link href="assets/css/style.css" rel="stylesheet" />
</head>
<body>
    <!-- Include the header.php file -->
    <?php include 'includes/header.php'; ?>
    
    <main class="main-content">
        <!-- Search bar -->
        <div class="content-search-container">
            <div class="content-search-bar">
                <input type="text" id="searchInput" placeholder="Search for books, authors, or genres...">
                <div class="content-search-icon">🔍</div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters">
            <div class="filter-title">Filter Books</div>
            <div class="filter-options">
                <select class="filter-select" id="genreFilter">
                    <option value="">Genre</option>
                    <option value="Technology">Technology</option>
                    <option value="Science">Science</option>
                    <option value="Management">Management</option>
                    <option value="General">General</option>
                    <option value="Programming">Programming</option>
                </select>
                
                <select class="filter-select" id="yearFilter">
                    <option value="">Year Published</option>
                    <option value="2020">2020+</option>
                    <option value="2010">2010-2019</option>
                    <option value="2000">2000-2009</option>
                    <option value="1990">1990-1999</option>
                    <option value="older">Before 1990</option>
                </select>
                
                <select class="filter-select" id="sortFilter">
                    <option value="">Sort By</option>
                    <option value="newest">Newest First</option>
                    <option value="title">Title A-Z</option>
                </select>
            </div>
        </div>
        
        <!-- Loading indicator -->
        <div id="loadingIndicator" style="display: none; text-align: center; padding: 20px;">
            Loading books...
        </div>
        
        <!-- Error message area -->
        <div id="errorMessage" style="display: none; color: red; text-align: center; padding: 20px;"></div>
        
        <!-- Book Grid -->
        <div class="book-grid" id="bookGrid">
            <!-- Book cards will be dynamically inserted here -->
        </div>
        
        <!-- Pagination -->
        <div class="pagination">
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">3</button>
            <button class="page-btn">4</button>
            <button class="page-btn">5</button>
            <button class="page-btn">Next</button>
        </div>
    </main>

    <!-- JavaScript to Fetch and Display Books -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            fetchBooks();

            // Add event listeners for filters
            document.getElementById('genreFilter').addEventListener('change', fetchBooks);
            document.getElementById('yearFilter').addEventListener('change', fetchBooks);
            document.getElementById('sortFilter').addEventListener('change', fetchBooks);
            
            // Debounce search input to prevent too many requests
            let searchTimeout;
            document.getElementById('searchInput').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(fetchBooks, 300);
            });
        });

        function fetchBooks() {
            const genre = document.getElementById('genreFilter').value;
            const year = document.getElementById('yearFilter').value;
            const sort = document.getElementById('sortFilter').value;
            const search = document.getElementById('searchInput').value;
            
            // Show loading indicator
            document.getElementById('loadingIndicator').style.display = 'block';
            // Hide error message if present
            document.getElementById('errorMessage').style.display = 'none';

            // Build URL with parameters
            const url = `fetch-books.php?genre=${encodeURIComponent(genre)}&year=${encodeURIComponent(year)}&sort=${encodeURIComponent(sort)}&search=${encodeURIComponent(search)}`;
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.text(); // Use .text() instead of .json() to log the raw response
                })
                .then(text => {
                    console.log('Raw response:', text); // Log the raw response for debugging

                    try {
                        const data = JSON.parse(text); // Manually parse the JSON
                        console.log('Data received:', data); // For debugging
                        
                        // Hide loading indicator
                        document.getElementById('loadingIndicator').style.display = 'none';
                        
                        const bookGrid = document.getElementById('bookGrid');
                        bookGrid.innerHTML = ''; // Clear existing content
                        
                        // Check if we got an error or no results
                        if (data.error) {
                            document.getElementById('errorMessage').textContent = `Error: ${data.error}`;
                            document.getElementById('errorMessage').style.display = 'block';
                            return;
                        }
                        
                        if (data.length === 0) {
                            bookGrid.innerHTML = '<div style="text-align: center; grid-column: 1 / -1;">No books found matching your criteria.</div>';
                            return;
                        }

                        data.forEach(book => {
                            console.log('Processing book:', book); // Debug: Log the full book object
                            console.log('Using image path:', book.BookImage); // Debug: Log the image path
                            
                            // Use BookImage directly as it already contains the full path from PHP
                            const bookCard = `
                                <div class="book-card">
                                    <div class="book-cover">
                                        <img src="${book.BookImage}" alt="${book.BookName}" onerror="this.src='/library/shared/bookImg/placeholder-book.jpg'">
                                    </div>
                                    <div class="book-info">
                                        <div class="book-title">${book.BookName}</div>
                                        <div class="book-author">${book.AuthorName}</div>
                                        <div class="book-genre">${book.CategoryName}</div>
                                    </div>
                                </div>
                            `;
                            bookGrid.insertAdjacentHTML('beforeend', bookCard);
                        });
                    } catch (error) {
                        console.error('Error parsing JSON:', error);
                        document.getElementById('errorMessage').textContent = 'Failed to parse book data. Please check the server response.';
                        document.getElementById('errorMessage').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error fetching books:', error);
                    // Hide loading indicator
                    document.getElementById('loadingIndicator').style.display = 'none';
                    // Show error message
                    document.getElementById('errorMessage').textContent = `Failed to load books. ${error.message}`;
                    document.getElementById('errorMessage').style.display = 'block';
                });
        }
    </script>
     <?php include('includes/footer.php'); ?>

</body>
</html>
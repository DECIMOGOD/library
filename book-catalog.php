<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Book Catalog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .book-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .book-cover {
            height: 300px;
            overflow: hidden;
        }
        .book-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .search-box {
            max-width: 600px;
            margin: 0 auto 30px;
        }
        #loadingIndicator {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
        }
        .error-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            max-width: 400px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-5">
        <div class="search-box input-group mb-4">
            <input type="text" id="searchInput" class="form-control" placeholder="Search books...">
            <button class="btn btn-primary" id="searchButton">Search</button>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <select class="form-select" id="genreFilter">
                    <option value="">All Genres</option>
                    <option value="Technology">Technology</option>
                    <option value="Science">Science</option>
                    <option value="Programming">Programming</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="yearFilter">
                    <option value="">All Years</option>
                    <option value="2020">2020+</option>
                    <option value="2010">2010-2019</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="sortFilter">
                    <option value="">Default Sort</option>
                    <option value="newest">Newest First</option>
                    <option value="title">Title A-Z</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-outline-secondary w-100" id="resetFilters">Reset Filters</button>
            </div>
        </div>

        <div id="bookGrid" class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4"></div>

        <div id="noResults" class="text-center py-5" style="display: none;">
            <h4>No books found matching your criteria</h4>
            <button class="btn btn-primary mt-3" id="resetSearch">Reset Search</button>
        </div>

        <div class="d-flex justify-content-center mt-5">
            <nav aria-label="Page navigation">
                <ul class="pagination" id="pagination">
                    <!-- Pagination will be inserted here by JavaScript -->
                </ul>
            </nav>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="spinner-border text-primary" style="display: none;"></div>

    <!-- Error Alert -->
    <div id="errorAlert" class="alert alert-danger error-alert" style="display: none;">
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        <strong>Error:</strong> <span id="errorMessage"></span>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize
            loadBooks();
            
            // Event listeners
            document.getElementById('searchButton').addEventListener('click', loadBooks);
            document.getElementById('resetFilters').addEventListener('click', resetFilters);
            document.getElementById('resetSearch').addEventListener('click', resetFilters);
            
            // Debounced search input
            let searchTimer;
            document.getElementById('searchInput').addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(loadBooks, 500);
            });
            
            // Filter changes
            document.getElementById('genreFilter').addEventListener('change', loadBooks);
            document.getElementById('yearFilter').addEventListener('change', loadBooks);
            document.getElementById('sortFilter').addEventListener('change', loadBooks);
        });

        function loadBooks() {
            const loading = document.getElementById('loadingIndicator');
            const errorAlert = document.getElementById('errorAlert');
            const bookGrid = document.getElementById('bookGrid');
            const noResults = document.getElementById('noResults');
            
            // Show loading, hide others
            loading.style.display = 'block';
            errorAlert.style.display = 'none';
            bookGrid.innerHTML = '';
            noResults.style.display = 'none';
            
            // Get filters
            const params = new URLSearchParams({
                genre: document.getElementById('genreFilter').value,
                year: document.getElementById('yearFilter').value,
                sort: document.getElementById('sortFilter').value,
                search: document.getElementById('searchInput').value
            });
            
            fetch(`fetch-books.php?${params}`)
                .then(async response => {
                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        const text = await response.text();
                        throw new Error(`Invalid response: ${text.substring(0, 100)}`);
                    }
                    
                    if (!response.ok) {
                        const error = await response.json();
                        throw new Error(error.message || 'Request failed');
                    }
                    
                    return response.json();
                })
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.error || 'Unknown error occurred');
                    }
                    
                    if (data.data.length === 0) {
                        noResults.style.display = 'block';
                        return;
                    }
                    
                    renderBooks(data.data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('errorMessage').textContent = 
                        error.message || 'Failed to load books';
                    errorAlert.style.display = 'block';
                })
                .finally(() => {
                    loading.style.display = 'none';
                });
        }

        function renderBooks(books) {
            const bookGrid = document.getElementById('bookGrid');
            
            books.forEach(book => {
                const col = document.createElement('div');
                col.className = 'col';
                
                col.innerHTML = `
                    <div class="card book-card h-100">
                        <div class="book-cover">
                            <img src="${book.BookImage}" class="card-img-top" alt="${book.BookName}"
                                 onerror="this.src='assets/images/default-book.jpg'">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">${book.BookName}</h5>
                            <p class="card-text text-muted">${book.AuthorName}</p>
                            <span class="badge bg-primary">${book.CategoryName}</span>
                        </div>
                        <div class="card-footer bg-transparent">
                            <small class="text-muted">Added: ${book.RegDate}</small>
                        </div>
                    </div>
                `;
                
                bookGrid.appendChild(col);
            });
        }

        function resetFilters() {
            document.getElementById('genreFilter').value = '';
            document.getElementById('yearFilter').value = '';
            document.getElementById('sortFilter').value = '';
            document.getElementById('searchInput').value = '';
            loadBooks();
        }
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
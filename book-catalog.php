<?php
session_start();
include('includes/config.php');

if (!isset($_SESSION['login'])) {
    header('location:index.php');
    exit();
}

$role = $_SESSION['role'] ?? 'student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Online Library Management System | Book Catalog</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
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
            transform: translateY(-5px);
        }
        .book-image {
            max-height: 180px;
            width: auto;
            margin: 0 auto;
            display: block;
        }
        .filters {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        #loadingIndicator {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
</head>
<body>
    <?php include('includes/header.php'); ?>
    
    <div class="content-wrapper">
        <div class="container">
            <div class="row pad-botm">
                <div class="col-md-12">
                    <h4 class="header-line">Book Catalog</h4>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="row">
                <div class="col-md-12">
                    <div class="filters">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="input-group mb-3">
                                    <input type="text" id="searchInput" class="form-control" 
                                           placeholder="Search by book, author, ISBN...">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" id="searchBtn" type="button">
                                            <i class="fa fa-search"></i> Search
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <select class="form-control" id="genreFilter">
                                            <option value="">All Genres</option>
                                            <?php
                                            $genres = $dbh->query("SELECT DISTINCT CategoryName FROM tblcategory")->fetchAll();
                                            foreach ($genres as $genre) {
                                                echo '<option value="'.htmlspecialchars($genre['CategoryName']).'">'.htmlspecialchars($genre['CategoryName']).'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <select class="form-control" id="yearFilter">
                                            <option value="">All Years</option>
                                            <option value="2020">2020+</option>
                                            <option value="2010">2010-2019</option>
                                            <option value="2000">2000-2009</option>
                                            <option value="1990">1990-1999</option>
                                            <option value="older">Before 1990</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div id="loadingIndicator">
                <div class="spinner"></div>
                <p>Loading books...</p>
            </div>

            <!-- Error Message -->
            <div id="errorMessage" class="alert alert-danger" style="display:none;"></div>

            <!-- Book Grid -->
            <div class="row" id="bookGrid"></div>

            <!-- Pagination -->
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center" id="pagination"></ul>
            </nav>
        </div>
    </div>

    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.js"></script>
    <script>
        $(document).ready(function() {
            let currentPage = 1;
            const booksPerPage = 12;
            let isLoading = false;

            // Initial load
            loadBooks();

            // Search button click
            $('#searchBtn').click(function() {
                currentPage = 1;
                loadBooks();
            });

            // Enter key in search
            $('#searchInput').keypress(function(e) {
                if (e.which === 13) {
                    currentPage = 1;
                    loadBooks();
                }
            });

            // Filter changes
            $('#genreFilter, #yearFilter').change(function() {
                currentPage = 1;
                loadBooks();
            });

            // Load books function
            function loadBooks() {
                if (isLoading) return;
                
                isLoading = true;
                showLoading(true);
                hideError();

                const search = $('#searchInput').val().trim();
                const genre = $('#genreFilter').val();
                const year = $('#yearFilter').val();

                $.ajax({
                    url: 'fetch-books.php',
                    method: 'GET',
                    data: {
                        search: search,
                        genre: genre,
                        year: year,
                        page: currentPage,
                        per_page: booksPerPage
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.success) {
                            displayBooks(data);
                            updatePagination(data.pagination);
                        } else {
                            showError(data.error || 'Failed to load books');
                        }
                    },
                    error: function(xhr, status, error) {
                        showError('Error loading books: ' + error);
                        console.error('AJAX Error:', status, error);
                    },
                    complete: function() {
                        isLoading = false;
                        showLoading(false);
                    }
                });
            }

            // Display books
            function displayBooks(data) {
                const bookGrid = $('#bookGrid');
                bookGrid.empty();

                if (!data.data || data.data.length === 0) {
                    bookGrid.html('<div class="col-md-12"><div class="alert alert-info">No books found matching your criteria.</div></div>');
                    return;
                }

                data.data.forEach(book => {
                    const bookCard = `
                        <div class="col-md-4 mb-4">
                            <div class="book-card">
                                <div class="text-center">
                                    <img src="${book.bookImage}" class="book-image img-fluid" 
                                         onerror="this.src='shared/bookimg/placeholder.jpg'">
                                </div>
                                <h4>${escapeHtml(book.BookName)}</h4>
                                <p><strong>Publisher:</strong> ${escapeHtml(book.PublisherName)}</p>
                                <p><strong>Category:</strong> ${escapeHtml(book.CategoryName)}</p>
                                <p><strong>ISBN:</strong> ${escapeHtml(book.ISBNNumber)}</p>
                                <p><strong>Available:</strong> ${book.bookQty}</p>
                                ${role === 'librarian' ? 
                                    `<a href="issue-book.php?bookid=${book.id}" class="btn btn-primary btn-sm btn-block">
                                        <i class="fa fa-book"></i> Issue This Book
                                    </a>` : ''}
                            </div>
                        </div>
                    `;
                    bookGrid.append(bookCard);
                });
            }

            // Update pagination
            function updatePagination(pagination) {
                const paginationEl = $('#pagination');
                paginationEl.empty();

                if (pagination.total_pages <= 1) return;

                // Previous button
                const prevLi = $(`<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>`);
                prevLi.click(e => {
                    e.preventDefault();
                    if (currentPage > 1) changePage(currentPage - 1);
                });
                paginationEl.append(prevLi);

                // Page numbers
                const maxVisible = 5;
                let start = Math.max(1, currentPage - Math.floor(maxVisible / 2));
                let end = Math.min(pagination.total_pages, start + maxVisible - 1);

                if (end - start + 1 < maxVisible) {
                    start = Math.max(1, end - maxVisible + 1);
                }

                if (start > 1) {
                    paginationEl.append(createPageItem(1));
                    if (start > 2) {
                        paginationEl.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                    }
                }

                for (let i = start; i <= end; i++) {
                    paginationEl.append(createPageItem(i));
                }

                if (end < pagination.total_pages) {
                    if (end < pagination.total_pages - 1) {
                        paginationEl.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                    }
                    paginationEl.append(createPageItem(pagination.total_pages));
                }

                // Next button
                const nextLi = $(`<li class="page-item ${currentPage === pagination.total_pages ? 'disabled' : ''}">
                    <a class="page-link" href="#" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>`);
                nextLi.click(e => {
                    e.preventDefault();
                    if (currentPage < pagination.total_pages) changePage(currentPage + 1);
                });
                paginationEl.append(nextLi);
            }

            function createPageItem(pageNum) {
                const isActive = pageNum === currentPage;
                return $(`<li class="page-item ${isActive ? 'active' : ''}">
                    <a class="page-link" href="#">${pageNum}</a>
                </li>`).click(e => {
                    e.preventDefault();
                    changePage(pageNum);
                });
            }

            function changePage(newPage) {
                currentPage = newPage;
                loadBooks();
                $('html, body').animate({ scrollTop: 0 }, 'fast');
            }

            function showLoading(show) {
                $('#loadingIndicator').toggle(show);
            }

            function showError(message) {
                $('#errorMessage').text(message).show();
            }

            function hideError() {
                $('#errorMessage').hide();
            }

            function escapeHtml(unsafe) {
                return unsafe?.toString()
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;") || '';
            }

            // PHP role variable to JS
            const role = '<?php echo $role; ?>';
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-dark: #1a1a2e;
            --primary-accent: #4cc9f0;
            --secondary-accent: #f72585;
            --light-bg: #f8f9fa;
            --text-light: #e6e6e6;
            --text-dark: #14213d;
            --transition-speed: 0.3s;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            padding-top: 70px;
        }

        .navbar-custom {
            background: rgba(26, 26, 46, 0.95) !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 0.5rem 1rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1030;
            transition: all var(--transition-speed) ease;
        }

        .navbar-custom.scrolled {
            background: rgba(26, 26, 46, 0.98) !important;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.2);
            padding: 0.3rem 1rem;
        }

        .navbar-brand {
            color: white !important;
            display: flex;
            align-items: center;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all var(--transition-speed) ease;
        }

        .navbar-brand img {
            max-height: 36px;
            margin-right: 12px;
            transition: all var(--transition-speed) ease;
        }

        .navbar-custom.scrolled .navbar-brand img {
            max-height: 32px;
        }

        .navbar-nav .nav-link {
            color: var(--text-light) !important;
            font-weight: 500;
            padding: 0.8rem 1.2rem !important;
            margin: 0 0.2rem;
            position: relative;
            transition: all var(--transition-speed) ease;
            border-radius: 8px;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link:focus {
            color: white !important;
            background: rgba(76, 201, 240, 0.1);
        }

        .navbar-nav .nav-link.active {
            color: white !important;
            background: rgba(76, 201, 240, 0.2);
        }

        .navbar-nav .nav-link i {
            margin-right: 8px;
            width: 20px;
            text-align: center;
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 8px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary-accent);
            transition: all var(--transition-speed) ease;
        }

        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link.active::after {
            width: calc(100% - 2.4rem);
        }

        .dropdown-menu {
            background: rgba(111, 113, 255, 0.95);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 0.5rem 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            min-width: 220px;
        }

        .dropdown-item {
            color: var(--text-light);
            padding: 0.5rem 1.5rem;
            transition: all var(--transition-speed) ease;
            position: relative;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            color: white;
            background: rgba(76, 201, 240, 0.2);
        }

        .dropdown-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 3px;
            height: 100%;
            background: var(--primary-accent);
            opacity: 0;
            transition: all var(--transition-speed) ease;
        }

        .dropdown-item:hover::before {
            opacity: 1;
        }

        .dropdown-divider {
            border-color: rgba(255, 255, 255, 0.1);
        }

        .logout-btn {
            background: linear-gradient(135deg, var(--secondary-accent), #b5179e);
            color: white !important;
            border: none;
            border-radius: 8px;
            padding: 0.6rem 1.2rem !important;
            margin-left: 1rem;
            font-weight: 500;
            transition: all var(--transition-speed) ease;
            box-shadow: 0 4px 15px rgba(247, 37, 133, 0.3);
            display: flex;
            align-items: center;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(247, 37, 133, 0.4);
            color: white !important;
        }

        .logout-btn i {
            margin-right: 8px;
        }

        .navbar-toggler {
            border: none;
            padding: 0.5rem;
            transition: all var(--transition-speed) ease;
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 3px rgba(76, 201, 240, 0.3);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(76, 201, 240, 1)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
            width: 1.5em;
            height: 1.5em;
        }

        @media (max-width: 991.98px) {
            .navbar-collapse {
                background: rgba(26, 26, 46, 0.98);
                backdrop-filter: blur(15px);
                -webkit-backdrop-filter: blur(15px);
                padding: 1rem;
                border-radius: 0 0 12px 12px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                margin-top: 8px;
            }

            .navbar-nav {
                padding: 0.5rem 0;
            }

            .nav-link {
                margin: 0.2rem 0 !important;
            }

            .dropdown-menu {
                background: rgba(40, 40, 70, 0.95);
                margin-left: 1.5rem;
                width: calc(100% - 3rem);
            }

            .logout-btn {
                margin: 1rem 0 0.5rem;
                width: 100%;
                justify-content: center;
            }
        }

        @media (min-width: 992px) {
            .dropdown:hover .dropdown-menu {
                display: block;
                margin-top: 0;
                animation: fadeIn var(--transition-speed) ease;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- Modern Glassmorphism Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom navbar-dark fixed-top">
        <div class="container-fluid">
            <!-- Logo with Modern Typography -->
            <a class="navbar-brand" href="dashboard.php">
                <img src="assets/img/logo.png" alt="Library Management Logo">
                <span class="d-none d-md-inline">Librarian<span class="text-accent">Panel</span></span>
            </a>

            <!-- Mobile Toggle Button - Animated -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                <span class="menu-text">Menu</span>
            </button>

            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link" id="dashboard-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>

                    <!-- Categories Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" id="categories-link" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-layer-group"></i> Categories
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="categoriesDropdown">
                            <li><a class="dropdown-item" href="add-category.php"><i class="fas fa-plus-circle me-2"></i>Add Category</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="manage-categories.php"><i class="fas fa-edit me-2"></i>Manage Categories</a></li>
                        </ul>
                    </li>

                    <!-- Books Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" id="books-link" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-book-open"></i> Books
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="booksDropdown">
                            <li><a class="dropdown-item" href="add-book.php"><i class="fas fa-plus-circle me-2"></i>Add Book</a></li>
                            <li><a class="dropdown-item" href="manage-books.php"><i class="fas fa-edit me-2"></i>Manage Books</a></li>
                            <!-- Added Edit Featured Books link under Books section -->
                            <li><a class="dropdown-item" href="edit-featured-books.php"><i class="fas fa-star me-2"></i>Edit Featured Books</a></li>
                        </ul>
                    </li>

                    <!-- Publishers Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" id="publishers-link" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-building"></i> Publishers
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="publishersDropdown">
                            <li><a class="dropdown-item" href="add-publisher.php"><i class="fas fa-plus-circle me-2"></i>Add Publisher</a></li>
                            <li><a class="dropdown-item" href="manage-publishers.php"><i class="fas fa-edit me-2"></i>Manage Publishers</a></li>
                        </ul>
                    </li>

                    <!-- Issue Books Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" id="circulation-link" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-exchange-alt"></i> Circulation
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="issueBooksDropdown">
                            <li><a class="dropdown-item" href="issue-book.php"><i class="fas fa-hand-holding me-2"></i>Issue Book</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="manage-issued-books.php"><i class="fas fa-tasks me-2"></i>Manage Issues</a></li>
                        </ul>
                    </li>

                    <!-- Manage Accounts Section -->
                    <li class="nav-item">
                        <a class="nav-link" id="students-link" href="reg-students.php">
                            <i class="fas fa-users"></i> Manage Accounts
                        </a>
                    </li>

                    <!-- Pending Accounts Link -->
                    <li class="nav-item">
                        <a class="nav-link" id="pending-accounts-link" href="account-approval.php">
                            <i class="fas fa-user-clock"></i> Pending Accounts
                        </a>
                    </li>

                    <!-- Security -->
                    <li class="nav-item">
                        <a class="nav-link" id="security-link" href="change-password.php">
                            <i class="fas fa-key"></i> Security
                        </a>
                    </li>
                </ul>

                <!-- Logout Button -->
                <a href="logout.php" class="btn logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Bootstrap 5 JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"></script>
    
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-custom');
            if (window.scrollY > 10) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Set active menu item based on current page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop().toLowerCase();
            
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            if (currentPage === 'dashboard.php' || currentPage === '') {
                document.getElementById('dashboard-link').classList.add('active');
            } 
            else if (currentPage.includes('category')) {
                document.getElementById('categories-link').classList.add('active');
            }
            else if (currentPage.includes('book') && !currentPage.includes('issue')) {
                document.getElementById('books-link').classList.add('active');
            }
            else if (currentPage.includes('issue')) {
                document.getElementById('circulation-link').classList.add('active');
            }
            else if (currentPage.includes('reg-students.php')) {
                document.getElementById('students-link').classList.add('active');
            }
            else if (currentPage.includes('account-approval.php')) {
                document.getElementById('pending-accounts-link').classList.add('active');
            }
            else if (currentPage.includes('change-password.php')) {
                document.getElementById('security-link').classList.add('active');
            }
        });
    </script>
</body>
</html>
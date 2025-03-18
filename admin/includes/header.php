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
    <style>
        :root {
            --primary-blue: #2c3e50;     /* Dark blue for main background */
            --secondary-blue: #3498db;   /* Bright blue for highlights */
            --light-blue: #ebf5fb;       /* Very light blue for backgrounds */
            --hover-blue: #34495e;       /* Slightly lighter dark blue for hover */
        }

        body {
            background-color: var(--light-blue);
        }

        .navbar-custom {
            background-color: var(--primary-blue) !important;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            color: white !important;
            display: flex;
            align-items: center;
        }

        .navbar-brand img {
            max-height: 40px;
            margin-right: 15px;
        }

        .navbar-nav .nav-link {
            color: white !important;
            transition: all 0.3s ease;
            position: relative;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link:focus {
            color: var(--secondary-blue) !important;
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background-color: var(--secondary-blue);
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover::after {
            width: 100%;
            left: 0;
        }

        .dropdown-menu {
            background-color: var(--primary-blue);
            border: none;
        }

        .dropdown-menu a {
            color: white !important;
            transition: background-color 0.3s ease;
        }

        .dropdown-menu a:hover {
            background-color: var(--secondary-blue);
        }

        .logout-btn {
            background-color: #e74c3c;
            color: white !important;
            border: none;
            border-radius: 5px;
            padding: 6px 12px;
            margin-left: 15px;
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #c0392b;
        }

        .navbar-toggler {
            border-color: var(--secondary-blue);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(52, 152, 219, 1)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
        }

        @media (max-width: 991px) {
            .navbar-collapse {
                max-height: 80vh;
                overflow-y: auto;
                background-color: var(--primary-blue);
            }

            .navbar-nav {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom navbar-dark">
        <div class="container-fluid">
            <!-- Logo -->
            <a class="navbar-brand" href="dashboard.php">
                <img src="assets/img/logo.png" alt="Library Management Logo" class="d-inline-block align-text-top">
                Library Management
            </a>

            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-home me-2"></i> Dashboard
                        </a>
                    </li>

                    <!-- Categories Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-list me-2"></i> Categories
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="categoriesDropdown">
                            <li><a class="dropdown-item" href="add-category.php">Add Category</a></li>
                            <li><a class="dropdown-item" href="manage-categories.php">Manage Categories</a></li>
                        </ul>
                    </li>

                    <!-- Publishers Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="publishersDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-building me-2"></i> Publishers
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="publishersDropdown">
                            <li><a class="dropdown-item" href="add-publisher.php">Add Publisher</a></li>
                            <li><a class="dropdown-item" href="manage-publishers.php">Manage Publishers</a></li>
                        </ul>
                    </li>

                    <!-- Books Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="booksDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-book me-2"></i> Books
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="booksDropdown">
                            <li><a class="dropdown-item" href="add-book.php">Add Book</a></li>
                            <li><a class="dropdown-item" href="manage-books.php">Manage Books</a></li>
                        </ul>
                    </li>

                    <!-- Issue Books Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="issueBooksDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-exchange-alt me-2"></i> Issue Books
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="issueBooksDropdown">
                            <li><a class="dropdown-item" href="issue-book.php">Issue New Book</a></li>
                            <li><a class="dropdown-item" href="manage-issued-books.php">Manage Issued Books</a></li>
                        </ul>
                    </li>

                    <!-- Additional Links -->
                    <li class="nav-item">
                        <a class="nav-link" href="reg-students.php">
                            <i class="fas fa-user-graduate me-2"></i> Reg Students
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="change-password.php">
                            <i class="fas fa-key me-2"></i> Change Password
                        </a>
                    </li>
                </ul>

                <!-- Logout Button -->
                <a href="logout.php" class="btn logout-btn">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Bootstrap 5 JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
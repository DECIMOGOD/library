<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Other head elements -->
    <link href="assets/css/header-styles.css" rel="stylesheet">
</head>
<body>
    <header class="lms-header-container">
        <nav class="lms-navbar">
            <div class="lms-brand">
                <img src="assets/img/logo.png" alt="Library Logo" class="lms-brand-img">
                <span class="lms-brand-text">Library MS</span>
            </div>

            <ul class="lms-nav">
                <?php if(isset($_SESSION['login'])): ?>
                    <li class="lms-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                        <a href="dashboard.php" class="lms-nav-link">Dashboard</a>
                    </li>
                    <li class="lms-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'listed-books.php') ? 'active' : ''; ?>">
                        <a href="listed-books.php" class="lms-nav-link">Book Catalog</a>
                    </li>
                    <li class="lms-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'issued-books.php') ? 'active' : ''; ?>">
                        <a href="issued-books.php" class="lms-nav-link">My Books</a>
                    </li>
                    <li class="lms-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'my-profile.php') ? 'active' : ''; ?>">
                        <a href="my-profile.php" class="lms-nav-link">Profile</a>
                    </li>
                <?php else: ?>
                    <li class="lms-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                        <a href="index.php" class="lms-nav-link">Home</a>
                    </li>
                    <li class="lms-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'book-catalog.php') ? 'active' : ''; ?>">
                        <a href="book-catalog.php" class="lms-nav-link">Book Catalog</a>
                    </li>
                    <li class="lms-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'adminlogin.php') ? 'active' : ''; ?>">
                        <a href="adminlogin.php" class="lms-nav-link">Librarian Login</a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="lms-actions">
                <?php if(isset($_SESSION['login'])): ?>
                    <a href="logout.php" class="lms-btn-login">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="lms-btn-login">Login</a>
                    <a href="signup.php" class="lms-btn-signup">Sign Up</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
</body>
</html>
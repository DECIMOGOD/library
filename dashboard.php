<?php
session_start();
error_reporting(E_ALL);
include('includes/config.php');

if(strlen($_SESSION['login'])==0) { 
    header('location:index.php');
    exit();
} else { 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Library Management System | User Dashboard</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link href="assets/css/font-awesome.css" rel="stylesheet">
    <!-- Custom Styles -->
    <style>
        .dashboard-card {
            transition: all 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .sidebar {
            transition: all 0.3s ease;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                z-index: 50;
                height: 100vh;
            }
            .sidebar.active {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <!-- Mobile Menu Button -->
    <button id="menuToggle" class="md:hidden fixed top-4 left-4 z-50 bg-blue-600 text-white p-2 rounded-lg shadow-lg">
        <i class="fa fa-bars"></i>
    </button>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include('includes/header.php'); ?>

        <!-- Main Content -->
        <div class="flex-1 p-6 md:p-8">
            <div class="mb-8 mt-14"> <!-- Added top margin (mt-6) -->
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-6 rounded-lg shadow-lg">
                    <h1 class="text-4xl font-extrabold mb-2">Welcome to your Library Management Portal</h1>
                    <p class="text-lg">Manage your books, track your activities, and explore the library with ease.</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Books Listed -->
                <a href="listed-books.php" class="dashboard-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg">
                    <div class="p-6 flex items-center">
                        <div class="bg-green-100 p-4 rounded-full mr-4">
                            <i class="fa fa-book text-green-600 text-3xl"></i>
                        </div>
                        <div>
                            <?php 
                            $sql = "SELECT id FROM tblbooks";
                            $query = $dbh->prepare($sql);
                            $query->execute();
                            $listdbooks = $query->rowCount();
                            ?>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo htmlentities($listdbooks); ?></h3>
                            <p class="text-gray-600">Books Listed</p>
                        </div>
                    </div>
                </a>


                <!-- Books Not Returned -->
                <div class="dashboard-card bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="p-6 flex items-center">
                        <div class="bg-yellow-100 p-4 rounded-full mr-4">
                            <i class="fa fa-recycle text-yellow-600 text-3xl"></i>
                        </div>
                        <div>
                            <?php 
                            $rsts = 0;
                            $lrn = isset($_SESSION['login']) ? $_SESSION['login'] : '';
                            $sql2 = "SELECT id FROM tblissuedbookdetails WHERE LRN = :lrn AND (ReturnStatus = :rsts OR ReturnStatus IS NULL OR ReturnStatus = '')";
                            $query2 = $dbh->prepare($sql2);
                            $query2->bindParam(':lrn', $lrn, PDO::PARAM_STR);
                            $query2->bindParam(':rsts', $rsts, PDO::PARAM_INT);
                            $query2->execute();
                            $returnedbooks = $query2->rowCount();
                            ?>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo htmlentities($returnedbooks); ?></h3>
                            <p class="text-gray-600">Books Not Returned</p>
                        </div>
                    </div>
                </div>

                <!-- Total Issued Books -->
                <a href="issued-books.php" class="dashboard-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg">
                    <div class="p-6 flex items-center">
                        <div class="bg-blue-100 p-4 rounded-full mr-4">
                            <i class="fa fa-book text-blue-600 text-3xl"></i>
                        </div>
                        <div>
                            <?php 
                            try {
                                $ret = $dbh->prepare("SELECT id FROM tblissuedbookdetails WHERE LRN = :lrn");
                                $ret->bindParam(':lrn', $lrn, PDO::PARAM_STR);
                                $ret->execute();
                                $totalissuedbook = $ret->rowCount();
                            } catch(PDOException $e) {
                                $totalissuedbook = 0;
                                error_log("Database error: " . $e->getMessage());
                            }
                            ?>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo htmlentities($totalissuedbook); ?></h3>
                            <p class="text-gray-600">Total Issued Books</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Recent Activity Section -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">Recent Activity</h2>
                </div>
                <div class="p-6">
                    <p class="text-gray-600">Your recent library activities will appear here.</p>
                    <!-- You can add dynamic content here later -->
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script>
        // Mobile menu toggle
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const menuToggle = document.getElementById('menuToggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                event.target !== menuToggle) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>
</html>
<?php } ?>
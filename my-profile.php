<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('includes/config.php');

// Validate session and LRN
if (!isset($_SESSION['LRN']) || empty($_SESSION['LRN'])) {
    header("Location: index.php?error=no_session");
    exit();
}

$lrn = $_SESSION['LRN'];

// Fetch student data with error handling
try {
    $sql = "SELECT Name, RegDate, UpdationDate, Status FROM tblstudents WHERE LRN = :lrn";
    $query = $dbh->prepare($sql);
    $query->bindParam(':lrn', $lrn, PDO::PARAM_STR);
    $query->execute();
    
    $student = $query->fetch(PDO::FETCH_OBJ);
    
    if (!$student) {
        throw new Exception("No student found with LRN: $lrn");
    }
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
} catch (Exception $e) {
    die($e->getMessage());
}

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $fullname = trim($_POST['fullname']);
    
    if (empty($fullname)) {
        $error = "Full name cannot be empty";
    } else {
        try {
            $update_sql = "UPDATE tblstudents SET 
                          Name = :fullname,
                          UpdationDate = CURRENT_TIMESTAMP
                          WHERE LRN = :lrn";
            
            $update_query = $dbh->prepare($update_sql);
            $update_query->bindParam(':fullname', $fullname, PDO::PARAM_STR);
            $update_query->bindParam(':lrn', $lrn, PDO::PARAM_STR);
            
            if ($update_query->execute()) {
                $success = "Profile updated successfully!";
                // Refresh student data
                $query->execute();
                $student = $query->fetch(PDO::FETCH_OBJ);
            }
        } catch (PDOException $e) {
            $error = "Update failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Library Management System | My Profile</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link href="assets/css/font-awesome.css" rel="stylesheet">
    <!-- Custom Styles -->
    <style>
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
<body class="bg-gray-50 font-sans">
    <!-- Mobile Menu Button -->
    <button id="menuToggle" class="md:hidden fixed top-4 left-4 z-50 bg-blue-600 text-white p-2 rounded-lg shadow-lg">
        <i class="fa fa-bars"></i>
    </button>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include('includes/header.php'); ?>

        <!-- Main Content -->
        <div class="flex-1 p-6 md:p-8">
            <div class="mb-8">
                <div class="bg-gradient-to-r from-blue-400 to-blue-500 text-white p-6 rounded-lg shadow-lg mt-14">
                    <h1 class="text-3xl font-extrabold mb-2">My Profile</h1>
                    <p class="text-lg">View and update your account information with ease.</p>
                </div>
            </div>

            <!-- Alerts -->
            <?php if (isset($error)): ?>
                <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded">
                    <div class="flex items-center">
                        <i class="fa fa-exclamation-triangle mr-3"></i>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded">
                    <div class="flex items-center">
                        <i class="fa fa-check-circle mr-3"></i>
                        <p><?php echo htmlspecialchars($success); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="max-w-3xl mx-auto">
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-blue-800">
                        <h2 class="text-xl font-semibold text-white">
                            <i class="fa fa-user mr-2"></i> Profile Information
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        <form method="post" action="">
                            <!-- LRN Field -->
                            <div class="mb-6">
                                <label class="block text-gray-700 font-medium mb-2">LRN</label>
                                <div class="p-3 bg-gray-100 rounded-lg">
                                    <p class="text-gray-800 font-mono"><?php echo htmlspecialchars($lrn); ?></p>
                                </div>
                            </div>

                            <!-- Full Name Field -->
                            <div class="mb-6">
                                <label for="fullname" class="block text-gray-700 font-medium mb-2">Full Name</label>
                                <input type="text" id="fullname" name="fullname" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                       value="<?php echo htmlspecialchars($student->Name); ?>" required>
                            </div>

                            <!-- Registration Date -->
                            <div class="mb-6">
                                <label class="block text-gray-700 font-medium mb-2">Registration Date</label>
                                <div class="p-3 bg-gray-100 rounded-lg">
                                    <p class="text-gray-800">
                                        <?php 
                                        if (!empty($student->RegDate)) {
                                            echo date('F j, Y \a\t g:i A', strtotime($student->RegDate));
                                        } else {
                                            echo 'Not available';
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Last Updated -->
                            <?php if(!empty($student->UpdationDate)): ?>
                            <div class="mb-6">
                                <label class="block text-gray-700 font-medium mb-2">Last Updated</label>
                                <div class="p-3 bg-gray-100 rounded-lg">
                                    <p class="text-gray-800">
                                        <?php echo date('F j, Y \a\t g:i A', strtotime($student->UpdationDate)); ?>
                                    </p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Account Status -->
                            <div class="mb-8">
                                <label class="block text-gray-700 font-medium mb-2">Account Status</label>
                                <div class="p-3 rounded-lg <?php echo ($student->Status == 1) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <div class="flex items-center">
                                        <i class="fa <?php echo ($student->Status == 1) ? 'fa-check-circle' : 'fa-times-circle'; ?> mr-2"></i>
                                        <p><?php echo ($student->Status == 1) ? 'Active' : 'Blocked'; ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="flex flex-col sm:flex-row gap-4">
                                <button type="submit" name="update" 
                                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                    <i class="fa fa-save mr-2"></i> Update Profile
                                </button>
                                <a href="dashboard.php" 
                                   class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition-colors duration-200 text-center focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                    <i class="fa fa-arrow-left mr-2"></i> Back to Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
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
<?php
session_start();
error_reporting(0);
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
    <title>Online Library Management System | Issued Books</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link href="assets/css/font-awesome.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
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
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.5em 1em;
            margin-left: 0.25em;
            border-radius: 0.375rem;
            border: 1px solid #e2e8f0;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #3b82f6;
            color: white !important;
            border: 1px solid #3b82f6;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #e2e8f0;
            border: 1px solid #e2e8f0;
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
                    <h1 class="text-3xl font-extrabold mb-2">Manage Issued Books</h1>
                    <p class="text-lg">View and track all books you've borrowed from the library with ease.</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">Your Issued Books</h2>
                </div>
                
                <div class="p-6 overflow-x-auto">
                    <table id="issuedBooksTable" class="min-w-full divide-y divide-gray-200 stripe hover">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ISBN</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issued Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Return Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fine (USD)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php 
                            $lrn = $_SESSION['login'];
                            $sql = "SELECT tblbooks.BookName, tblbooks.ISBNNumber, tblissuedbookdetails.IssuesDate, 
                                           tblissuedbookdetails.ReturnDate, tblissuedbookdetails.id as rid, tblissuedbookdetails.fine 
                                    FROM tblissuedbookdetails 
                                    JOIN tblstudents ON tblstudents.LRN = tblissuedbookdetails.LRN 
                                    JOIN tblbooks ON tblbooks.id = tblissuedbookdetails.BookId 
                                    WHERE tblstudents.LRN = :lrn 
                                    ORDER BY tblissuedbookdetails.id DESC";

                            $query = $dbh->prepare($sql);
                            $query->bindParam(':lrn', $lrn, PDO::PARAM_STR);
                            $query->execute();
                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                            $cnt = 1;

                            if ($query->rowCount() > 0) {
                                foreach ($results as $result) { ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlentities($cnt); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlentities($result->BookName); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlentities($result->ISBNNumber); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlentities($result->IssuesDate); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <?php if ($result->ReturnDate == "") { ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Not Returned</span>
                                            <?php } else {
                                                echo htmlentities($result->ReturnDate);
                                            } ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php if ($result->fine > 0) { ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">$<?php echo htmlentities($result->fine); ?></span>
                                            <?php } else {
                                                echo "$0";
                                            } ?>
                                        </td>
                                    </tr>
                            <?php 
                                    $cnt++;
                                }
                            } else { ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No issued books found</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Additional Help Section -->
            <div class="mt-8 bg-blue-50 rounded-lg p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fa fa-info-circle text-blue-500 text-2xl mr-4"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-blue-800">Need help with your books?</h3>
                        <p class="mt-2 text-blue-700">
                            If you have any questions about your issued books or need to request an extension, 
                            please contact the library staff at <a href="mailto:library@example.com" class="text-blue-600 hover:underline">library@example.com</a>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#issuedBooksTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search books...",
                }
            });

            // Mobile menu toggle
            $('#menuToggle').click(function() {
                $('.sidebar').toggleClass('active');
            });

            // Close mobile menu when clicking outside
            $(document).click(function(event) {
                const sidebar = $('.sidebar');
                const menuToggle = $('#menuToggle');
                
                if ($(window).width() <= 768 && 
                    !sidebar.is(event.target) && 
                    sidebar.has(event.target).length === 0 && 
                    !menuToggle.is(event.target) && 
                    menuToggle.has(event.target).length === 0) {
                    sidebar.removeClass('active');
                }
            });
        });
    </script>
</body>
</html>
<?php } ?>
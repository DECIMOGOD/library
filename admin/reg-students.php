<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database configuration file
include('includes/config.php');

// Debugging: Check if $dbh is set
if (!isset($dbh)) {
    die("Database connection is not established. Check your config.php file.");
}

if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit;
} else {
    // Function to handle CSV import
    function importCSV($dbh, $fileName) {
        if ($_FILES['csv_file']['size'] > 0 && strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION)) == 'csv') {
            $file = fopen($fileName, "r");

            // Skip the first row if it contains headers
            fgetcsv($file);

            // Loop through each row of the CSV
            while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
                // Retrieve the data from the CSV
                $LRN = $column[0];
                $Name = $column[1];
                $Address = $column[2];
                $Department = $column[3];
                $Grade_Level = $column[4]; // Ensure this matches your CSV column
                $Section = $column[5];
                $Strand = $column[6];
                $Password = isset($column[7]) ? $column[7] : ''; // Get password from CSV if available
                
                // Hash the password if it exists
                $hashedPassword = !empty($Password) ? password_hash($Password, PASSWORD_DEFAULT) : '';
                
                // Check if student already exists
                $checkSql = "SELECT LRN FROM tblstudents WHERE LRN = :LRN";
                $checkQuery = $dbh->prepare($checkSql);
                $checkQuery->bindParam(':LRN', $LRN, PDO::PARAM_STR);
                $checkQuery->execute();
                
                if ($checkQuery->rowCount() > 0) {
                    // If student exists and password is provided, update only the password
                    if (!empty($Password)) {
                        $updateSql = "UPDATE tblstudents SET Password = :password WHERE LRN = :LRN";
                        $updateQuery = $dbh->prepare($updateSql);
                        $updateQuery->bindParam(':LRN', $LRN, PDO::PARAM_STR);
                        $updateQuery->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
                        $updateQuery->execute();
                    }
                    continue; // Skip to next record
                }

                // Insert the data into the tblstudents table
                $sql = "INSERT INTO tblstudents (LRN, Name, Address, Department, Grade_Level, Section, Strand, Password)
                        VALUES (:LRN, :Name, :Address, :Department, :Grade_Level, :Section, :Strand, :Password)";
                $query = $dbh->prepare($sql);
                $query->bindParam(':LRN', $LRN, PDO::PARAM_STR);
                $query->bindParam(':Name', $Name, PDO::PARAM_STR);
                $query->bindParam(':Address', $Address, PDO::PARAM_STR);
                $query->bindParam(':Department', $Department, PDO::PARAM_STR);
                $query->bindParam(':Grade_Level', $Grade_Level, PDO::PARAM_STR);
                $query->bindParam(':Section', $Section, PDO::PARAM_STR);
                $query->bindParam(':Strand', $Strand, PDO::PARAM_STR);
                $query->bindParam(':Password', $hashedPassword, PDO::PARAM_STR);
                $query->execute();
            }
            fclose($file);
            echo "<script>alert('CSV Data Imported Successfully');</script>";
        } else {
            echo "<script>alert('Please upload a valid CSV file.');</script>";
        }
    }

    // Function to change student status
    function changeStudentStatus($dbh, $id, $status) {
        $sql = "UPDATE tblstudents SET Status=:status WHERE id=:id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->bindParam(':status', $status, PDO::PARAM_INT);
        $query->execute();
        header('location:reg-students.php');
        exit;
    }

    // Function to delete a student
    function deleteStudent($dbh, $id) {
        $sql = "DELETE FROM tblstudents WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        if ($query->execute()) {
            echo "<script>alert('Student Deleted Successfully');</script>";
        } else {
            echo "<script>alert('Error: Failed to delete student.');</script>";
        }
        header('location:reg-students.php');
        exit;
    }

    // Function to add a new student
    function addStudent($dbh, $LRN, $Name, $Address, $Department, $Grade_Level, $Section, $Strand, $Password = '') {
        // Check if the LRN already exists
        $checkSql = "SELECT LRN FROM tblstudents WHERE LRN = :LRN";
        $checkQuery = $dbh->prepare($checkSql);
        $checkQuery->bindParam(':LRN', $LRN, PDO::PARAM_STR);
        $checkQuery->execute();

        if ($checkQuery->rowCount() > 0) {
            // LRN already exists
            echo "<script>alert('Error: LRN $LRN already exists.');</script>";
            return;
        }

        // Hash the password if it exists
        $hashedPassword = !empty($Password) ? password_hash($Password, PASSWORD_DEFAULT) : '';

        // Insert the new student
        $sql = "INSERT INTO tblstudents (LRN, Name, Address, Department, Grade_Level, Section, Strand, Password)
                VALUES (:LRN, :Name, :Address, :Department, :Grade_Level, :Section, :Strand, :Password)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':LRN', $LRN, PDO::PARAM_STR);
        $query->bindParam(':Name', $Name, PDO::PARAM_STR);
        $query->bindParam(':Address', $Address, PDO::PARAM_STR);
        $query->bindParam(':Department', $Department, PDO::PARAM_STR);
        $query->bindParam(':Grade_Level', $Grade_Level, PDO::PARAM_STR);
        $query->bindParam(':Section', $Section, PDO::PARAM_STR);
        $query->bindParam(':Strand', $Strand, PDO::PARAM_STR);
        $query->bindParam(':Password', $hashedPassword, PDO::PARAM_STR);

        if ($query->execute()) {
            echo "<script>alert('Student Added Successfully');</script>";
        } else {
            echo "<script>alert('Error: Failed to add student.');</script>";
        }
    }

    // Handle CSV import
    if (isset($_POST['import'])) {
        $fileName = $_FILES['csv_file']['tmp_name'];
        importCSV($dbh, $fileName);
    }

    // Handle student status change (inactive)
    if (isset($_GET['inid'])) {
        $id = $_GET['inid'];
        changeStudentStatus($dbh, $id, 0);
    }

    // Handle student status change (active)
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        changeStudentStatus($dbh, $id, 1);
    }

    // Handle student deletion
    if (isset($_GET['delid'])) {
        $id = $_GET['delid'];
        deleteStudent($dbh, $id);
    }

    // Handle adding a new student
    if (isset($_POST['add_student'])) {
        $LRN = $_POST['LRN'];
        $Name = $_POST['Name'];
        $Address = $_POST['Address'];
        $Department = $_POST['Department'];
        $Grade_Level = $_POST['Grade_Level'];
        $Section = $_POST['Section'];
        $Strand = ($Department === "Senior High School") ? $_POST['Strand'] : ''; // Only include Strand for Senior High
        $Password = isset($_POST['Password']) ? $_POST['Password'] : ''; // Get password if provided

        addStudent($dbh, $LRN, $Name, $Address, $Department, $Grade_Level, $Section, $Strand, $Password);
    }
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Online Library Management System | Manage Reg Students</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
    <style>
        /* Internal CSS */
        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
        }
        .action-buttons .btn {
            margin: 0;
            white-space: nowrap;
        }
        .table td.center, .table th.center {
            text-align: center;
            vertical-align: middle !important;
            white-space: nowrap;
            padding: 8px 10px;
        }
        #dataTables-example th {
            white-space: nowrap;
        }
        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
        }
        #file-input-popup, #add-student-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        #file-input-popup form, #add-student-popup form {
            display: flex;
            flex-direction: column;
        }
        #file-input-popup button, #add-student-popup button {
            margin-top: 10px;
        }
        #overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        #strand-group {
            display: none; /* Hide Strand field by default */
        }

        /* Remove scrollbar from the table */
        .table-responsive {
            overflow: hidden; /* Hide overflow */
            width: 100%; /* Ensure the container takes full width */
        }

        /* Ensure the table fits within its container */
        #dataTables-example {
            width: 100% !important; /* Ensure the table takes full width */
            table-layout: auto; /* Allow the table to adjust its width based on content */
        }

        /* Optional: Prevent horizontal scrolling */
        body {
            overflow-x: hidden; /* Hide horizontal scrollbar for the entire page */
        }

        /* Adjust pagination styling */
        .dataTables_paginate {
            margin-top: 10px;
            text-align: center;
        }

        /* Ensure the table container has enough height */
        .panel-body {
            min-height: 500px; /* Adjust this value as needed */
        }
    </style>
</head>
<body>
    <?php include('includes/header.php');?>
    <div class="content-wrapper">
        <div class="container">
            <div class="row pad-botm">
                <div class="col-md-12 d-flex justify-content-between align-items-center">
                    <h4 class="header-line">Manage Reg Students</h4>
                    <div>
                        <button class="btn btn-success" id="import-csv-btn">Import CSV</button>
                        <button class="btn btn-primary" id="add-student-btn">Add Student</button>
                    </div>
                </div>
            </div>

            <!-- Pop-up File Input -->
            <div id="file-input-popup">
                <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
                    <div class="form-group">
                        <label for="csv_file">Choose CSV File:</label>
                        <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv" required />
                    </div>
                    <button type="submit" name="import" class="btn btn-primary">Import CSV</button>
                    <button type="button" class="btn btn-secondary" id="cancel-import">Cancel</button>
                </form>
            </div>

            <!-- Add Student Pop-up -->
            <div id="add-student-popup">
                <form method="POST" onsubmit="return validateAddStudentForm()">
                    <div class="form-group">
                        <label for="LRN">LRN:</label>
                        <input type="text" name="LRN" id="LRN" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label for="Name">Name:</label>
                        <input type="text" name="Name" id="Name" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label for="Address">Address:</label>
                        <input type="text" name="Address" id="Address" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label for="Department">Department:</label>
                        <select name="Department" id="Department" class="form-control" required onchange="updateGradeLevelAndStrand()">
                            <option value="">Select Department</option>
                            <option value="Junior High School">Junior High School</option>
                            <option value="Senior High School">Senior High School</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="Grade_Level">Grade Level:</label>
                        <select name="Grade_Level" id="Grade_Level" class="form-control" required>
                            <option value="">Select Grade Level</option>
                            <!-- Options will be populated dynamically -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="Section">Section:</label>
                        <input type="text" name="Section" id="Section" class="form-control" required />
                    </div>
                    <div class="form-group" id="strand-group">
                        <label for="Strand">Strand:</label>
                        <select name="Strand" id="Strand" class="form-control">
                            <option value="">Select Strand</option>
                            <!-- Options will be populated dynamically -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="Password">Password:</label>
                        <input type="password" name="Password" id="Password" class="form-control" />
                        <small class="form-text text-muted">Optional. Leave blank to require manual registration.</small>
                    </div>
                    <button type="submit" name="add_student" class="btn btn-primary">Add Student</button>
                    <button type="button" class="btn btn-secondary" id="cancel-add-student">Cancel</button>
                </form>
            </div>

            <!-- Overlay -->
            <div id="overlay"></div>

            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">Reg Students</div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
                                            <th class="center">#</th>
                                            <th class="center">LRN</th>
                                            <th class="center">Name</th>
                                            <th class="center">Address</th>
                                            <th class="center">Department</th>
                                            <th class="center">Grade Level</th>
                                            <th class="center">Section</th>
                                            <th class="center">Strand</th>
                                            <th class="center">Status</th>
                                            <th class="center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT * from tblstudents";
                                        $query = $dbh->prepare($sql);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                        $cnt = 1;
                                        if ($query->rowCount() > 0) {
                                            foreach ($results as $result) { ?>
                                                <tr class="odd gradeX">
                                                    <td class="center"><?php echo htmlentities($cnt);?></td>
                                                    <td class="center"><?php echo htmlentities($result->LRN ?? '');?></td>
                                                    <td class="center"><?php echo htmlentities($result->Name ?? '');?></td>
                                                    <td class="center"><?php echo htmlentities($result->Address ?? '');?></td>
                                                    <td class="center"><?php echo htmlentities($result->Department ?? '');?></td>
                                                    <td class="center"><?php echo htmlentities($result->Grade_Level ?? '');?></td>
                                                    <td class="center"><?php echo htmlentities($result->Section ?? '');?></td>
                                                    <td class="center"><?php echo htmlentities($result->Strand ?? '');?></td>
                                                    <td class="center"><?php echo ($result->Status == 1) ? "Active" : "Inactive"; ?></td>
                                                    <td class="center">
                                                        <div class="action-buttons">
                                                            <?php if ($result->Status == 1) { ?>
                                                                <a href="reg-students.php?inid=<?php echo htmlentities($result->id ?? '');?>" onclick="return confirm('Are you sure you want to deactivate this student?');">
                                                                    <button class="btn btn-danger btn-sm">Deactivate</button>
                                                                </a>
                                                            <?php } else { ?>
                                                                <a href="reg-students.php?id=<?php echo htmlentities($result->id ?? '');?>" onclick="return confirm('Are you sure you want to activate this student?');">
                                                                    <button class="btn btn-primary btn-sm">Activate</button>
                                                                </a>
                                                            <?php } ?>
                                                            <a href="student-history.php?stdid=<?php echo htmlentities($result->LRN ?? '');?>">
                                                                <button class="btn btn-success btn-sm">Details</button>
                                                            </a>
                                                            <!-- Add the delete button -->
                                                            <a href="reg-students.php?delid=<?php echo htmlentities($result->id ?? '');?>" onclick="return confirm('Are you sure you want to delete this student?');">
                                                                <button class="btn btn-danger btn-sm">Delete</button>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                        <?php
                                            $cnt++;
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('includes/footer.php');?>
    <script src="assets/js/bootstrap.js"></script>
    <script src="assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="assets/js/dataTables/dataTables.bootstrap.js"></script>
    <script src="assets/js/custom.js"></script>
    <script>
        // Function to update Grade Level and Strand dropdowns
        function updateGradeLevelAndStrand() {
            const department = document.getElementById('Department').value;
            const gradeLevelSelect = document.getElementById('Grade_Level');
            const strandGroup = document.getElementById('strand-group');
            const strandSelect = document.getElementById('Strand');

            // Clear existing options
            gradeLevelSelect.innerHTML = '<option value="">Select Grade Level</option>';
            strandSelect.innerHTML = '<option value="">Select Strand</option>';

            if (department === "Junior High School") {
                // Add Junior High School grade levels
                for (let i = 7; i <= 10; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = `Grade ${i}`;
                    gradeLevelSelect.appendChild(option);
                }
                // Hide Strand field
                strandGroup.style.display = 'none';
            } else if (department === "Senior High School") {
                // Add Senior High School grade levels
                for (let i = 11; i <= 12; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = `Grade ${i}`;
                    gradeLevelSelect.appendChild(option);
                }
                // Show Strand field and populate options
                strandGroup.style.display = 'block';
                const strands = ["STEM", "HUMSS", "ABM", "ICT", "HE"];
                strands.forEach(strand => {
                    const option = document.createElement('option');
                    option.value = strand;
                    option.textContent = strand;
                    strandSelect.appendChild(option);
                });
            } else {
                // Hide Strand field if no department is selected
                strandGroup.style.display = 'none';
            }
        }

        // Initialize the form when the page loads
        document.addEventListener('DOMContentLoaded', function () {
            updateGradeLevelAndStrand();
        });

        // Handle pop-up display
        $(document).ready(function() {
            $('#import-csv-btn').click(function() {
                $('#file-input-popup').show();
                $('#overlay').show();
            });

            $('#cancel-import').click(function() {
                $('#file-input-popup').hide();
                $('#overlay').hide();
            });

            $('#add-student-btn').click(function() {
                $('#add-student-popup').show();
                $('#overlay').show();
            });

            $('#cancel-add-student').click(function() {
                $('#add-student-popup').hide();
                $('#overlay').hide();
            });
        });

        // Form validation
        function validateForm() {
            var fileInput = document.getElementById('csv_file');
            if (fileInput.files.length === 0) {
                alert('Please select a CSV file.');
                return false;
            }
            return true;
        }

        function validateAddStudentForm() {
            var LRN = document.getElementById('LRN').value;
            var Name = document.getElementById('Name').value;
            var Address = document.getElementById('Address').value;
            var Department = document.getElementById('Department').value;
            var Grade_Level = document.getElementById('Grade_Level').value;
            var Section = document.getElementById('Section').value;
            var Strand = document.getElementById('Strand').value;

            if (LRN === '' || Name === '' || Address === '' || Department === '' || Grade_Level === '' || Section === '') {
                alert('Please fill in all required fields.');
                return false;
            }
            
            // Validate Strand only for Senior High School
            if (Department === "Senior High School" && Strand === '') {
                alert('Please select a Strand for Senior High School students.');
                return false;
            }
            
            return true;
        }
    </script>
</body>
</html>
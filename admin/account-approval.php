<?php
ob_start(); // Start output buffering

include('includes/config.php');
include('includes/header.php');

// Function to approve an account
function approveAccount($id, $role, $dbh) {
    $table = $role === 'Student' ? 'tblstudents' : 'tblfaculty';
    $sql = "UPDATE $table SET Status = 1 WHERE id = :id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_INT);

    // Add error handling
    if ($query->execute()) {
        return true;
    } else {
        error_log("Failed to approve account with ID: $id in table: $table");
        return false;
    }
}

// Function to reject an account
function rejectAccount($id, $role, $dbh) {
    $table = $role === 'Student' ? 'tblstudents' : 'tblfaculty';
    $sql = "DELETE FROM $table WHERE id = :id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_INT);

    // Add error handling
    if ($query->execute()) {
        return true;
    } else {
        error_log("Failed to reject account with ID: $id in table: $table");
        return false;
    }
}

// Handle approval/rejection actions
if (isset($_POST['approve']) || isset($_POST['reject'])) {
    $id = intval($_POST['id']);
    $role = $_POST['role'];

    if ($role === 'Student' || $role === 'Faculty') {
        if (isset($_POST['approve']) && approveAccount($id, $role, $dbh)) {
            $_SESSION['message'] = "Account approved successfully!";
        } elseif (isset($_POST['reject']) && rejectAccount($id, $role, $dbh)) {
            $_SESSION['message'] = "Account rejected successfully!";
        } else {
            $_SESSION['message'] = "An error occurred. Please try again.";
        }
        header("Location: account-approval.php");
        exit();
    }
}

// Get pending students and faculty
$sql_students = "SELECT id, LRN, Name, Department, RegDate FROM tblstudents WHERE Status = 0 ORDER BY RegDate DESC";
$sql_faculty = "SELECT id, faculty_id, fullname, department, reg_date FROM tblfaculty WHERE Status = 0 ORDER BY reg_date DESC";
$query_students = $dbh->prepare($sql_students);
$query_faculty = $dbh->prepare($sql_faculty);
$query_students->execute();
$query_faculty->execute();
$students = $query_students->fetchAll(PDO::FETCH_ASSOC);
$faculty = $query_faculty->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Online Library Management System | Account Approvals</title>
    <!-- BOOTSTRAP CORE STYLE  -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FONT AWESOME STYLE  -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- CUSTOM STYLE  -->
    <link href="assets/css/add-category-style.css" rel="stylesheet" />
    <!-- GOOGLE FONT -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
    <link rel="stylesheet" href="assets/css/account-approval-style.css">
</head>
<body>
    <!-- MENU SECTION START -->
    <?php include('includes/header.php'); ?>
    <!-- MENU SECTION END -->

    <div class="content-wrapper">
        <div class="container mt-5 pt-4">
            <!-- Success Message -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Pending Account Approvals</h2>
                <div class="badge bg-primary rounded-pill">
                    Total Pending: <?php echo count($students) + count($faculty); ?>
                </div>
            </div>

            <!-- Students Table -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Students (<?php echo count($students); ?>)</h4>
                </div>
                <div class="card-body">
                    <?php if (count($students) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>LRN</th>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td><?php echo htmlentities($student['LRN']); ?></td>
                                            <td><?php echo htmlentities($student['Name']); ?></td>
                                            <td><?php echo htmlentities($student['Department']); ?></td>
                                            <td><?php echo date("M d, Y", strtotime($student['RegDate'])); ?></td>
                                            <td>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                                    <input type="hidden" name="role" value="Student">
                                                    <button type="submit" name="approve" class="btn btn-success btn-sm">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                    <button type="submit" name="reject" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No pending student accounts.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Faculty Table -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">Faculty (<?php echo count($faculty); ?>)</h4>
                </div>
                <div class="card-body">
                    <?php if (count($faculty) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Faculty ID</th>
                                        <th>Full Name</th>
                                        <th>Department</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($faculty as $fac): ?>
                                        <tr>
                                            <td><?php echo htmlentities($fac['faculty_id']); ?></td>
                                            <td><?php echo htmlentities($fac['fullname']); ?></td>
                                            <td><?php echo htmlentities($fac['department']); ?></td>
                                            <td><?php echo date("M d, Y", strtotime($fac['reg_date'])); ?></td>
                                            <td>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="id" value="<?php echo $fac['id']; ?>">
                                                    <input type="hidden" name="role" value="Faculty">
                                                    <button type="submit" name="approve" class="btn btn-success btn-sm">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                    <button type="submit" name="reject" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No pending faculty accounts.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- CONTENT-WRAPPER SECTION END -->
    <?php include('includes/footer.php'); ?>
    <!-- FOOTER SECTION END -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.js"></script>
    <script src="assets/js/custom.js"></script>
</body>
</html>
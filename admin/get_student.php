<?php
include('includes/config.php');

if (!empty($_POST['lrn'])) {
    $lrn = strtoupper(trim($_POST['lrn']));

    try {
        // Use the correct column name 'Name' for the student's name
        $sql = "SELECT id, Name FROM tblstudents WHERE LRN = :lrn";
        $query = $dbh->prepare($sql);
        $query->bindParam(':lrn', $lrn, PDO::PARAM_STR);
        $query->execute();

        if ($query->rowCount() > 0) {
            $result = $query->fetch(PDO::FETCH_OBJ);
            echo "<span class='text-success'>Student Found: " . htmlentities($result->Name) . "</span>";
        } else {
            echo "<span class='text-danger'>No student found with LRN: " . htmlentities($lrn) . "</span>";
        }
    } catch (PDOException $e) {
        echo "<span class='text-danger'>Error: " . $e->getMessage() . "</span>";
    }
} else {
    echo "<span class='text-danger'>LRN is required</span>";
}
?>
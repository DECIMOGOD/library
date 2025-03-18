<?php 
require_once("includes/config.php");

if(!empty($_POST["lrn"])) {  
    $lrn = strtoupper($_POST["lrn"]);

    $sql ="SELECT LRN, Name, Address, Department, Grade_Level, Section, Strand, Status FROM tblstudents WHERE LRN = :lrn";
    $query = $dbh->prepare($sql);
    $query->bindParam(':lrn', $lrn, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    if($query->rowCount() > 0) {
        foreach ($results as $result) {
            if($result->Status == 0) {
                echo "<span style='color:red'> Student is Blocked </span><br />";
                echo "<b>Student Name: </b>" . htmlentities($result->Name) . "<br />";
                echo "<script>$('#submit').prop('disabled',true);</script>";
            } else {
                echo htmlentities($result->Name) . "<br />";
                echo htmlentities($result->Address) . "<br />";
                echo htmlentities($result->Department) . "<br />";
                echo htmlentities($result->Grade_Level) . "<br />";
                echo htmlentities($result->Section) . "<br />";
                echo htmlentities($result->Strand) . "<br />";
                echo "<script>$('#submit').prop('disabled',false);</script>";
            }
        }
    } else {
        echo "<span style='color:red'> Invalid LRN. Please Enter a Valid LRN.</span>";
        echo "<script>$('#submit').prop('disabled',true);</script>";
    }
}
?>

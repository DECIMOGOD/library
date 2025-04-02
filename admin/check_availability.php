<?php
require_once("includes/config.php");

if(!empty($_POST["isbn"])) {
    $isbn = $_POST["isbn"];
    
    $sql = "SELECT b.id, b.BookName, b.bookQty,
                   (SELECT COUNT(*) FROM tblissuedbookdetails 
                    WHERE BookId = b.id AND ReturnStatus = 0) AS issuedCount
            FROM tblbooks b
            WHERE b.ISBNNumber = :isbn";
    
    $query = $dbh->prepare($sql);
    $query->bindParam(':isbn', $isbn, PDO::PARAM_STR);
    $query->execute();
    $book = $query->fetch(PDO::FETCH_OBJ);
    
    if($query->rowCount() > 0) {
        $availableQty = $book->bookQty - $book->issuedCount;
        
        if($availableQty > 0) {
            echo "<span style='color:green'>Available ($availableQty copies)</span>";
            echo "<script>$('#add').prop('disabled',false);</script>";
        } else {
            echo "<span style='color:red'>All copies are currently issued</span>";
            echo "<script>$('#add').prop('disabled',true);</script>";
        }
    } else {
        echo "<span style='color:blue'>New ISBN - Available for registration</span>";
        echo "<script>$('#add').prop('disabled',false);</script>";
    }
}
?>
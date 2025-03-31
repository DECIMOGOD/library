<?php
require_once('includes/config.php');

$query = $_POST['query'] ?? '';
if(empty($query)) die();

$sql = "SELECT ISBNNumber, BookName, bookQty FROM tblbooks 
        WHERE (ISBNNumber LIKE CONCAT('%', :query, '%') 
           OR BookName LIKE CONCAT('%', :query, '%'))
        AND bookQty > 0
        LIMIT 10";
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':query', $query, PDO::PARAM_STR);
$stmt->execute();

while($book = $stmt->fetch(PDO::FETCH_OBJ)) {
    echo '<div class="book-result" data-isbn="'.$book->ISBNNumber.'">';
    echo '<strong>'.$book->BookName.'</strong> ('.$book->ISBNNumber.')';
    echo ' - Available: '.$book->bookQty;
    echo '</div>';
}
?>
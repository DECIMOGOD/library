<?php 
require_once("includes/config.php");

if(!empty($_POST["bookid"])) {
    $bookid = $_POST["bookid"];
  
    $sql = "SELECT b.id, b.BookName, b.ISBNNumber, b.bookImage, b.bookQty, 
                   c.CategoryName, p.PublisherName,
                   (SELECT COUNT(*) FROM tblissuedbookdetails 
                    WHERE BookId = b.id AND ReturnStatus = 0) AS issuedCount
            FROM tblbooks b
            LEFT JOIN tblcategory c ON c.id = b.CatId
            LEFT JOIN tblpublishers p ON p.id = b.PublisherID
            WHERE (b.ISBNNumber = :bookid OR b.BookName LIKE :bookname)
            ORDER BY b.BookName";
           
    $query = $dbh->prepare($sql);
    $booknameParam = "%$bookid%";
    $query->bindParam(':bookid', $bookid, PDO::PARAM_STR);
    $query->bindParam(':bookname', $booknameParam, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    
    if($query->rowCount() > 0) {
        echo '<div class="row" style="margin-top:20px;">';
        foreach ($results as $result) {
            $availableQty = $result->bookQty - $result->issuedCount;
            $isAvailable = $availableQty > 0;
            
            echo '<div class="col-md-4">
                    <div class="panel panel-'.($isAvailable ? 'success' : 'danger').' book-card">
                        <div class="panel-heading">
                            '.htmlentities($result->BookName).'
                        </div>
                        <div class="panel-body">
                            <div class="text-center">
                                <img src="../shared/bookimg/'.htmlentities($result->bookImage).'" 
                                     class="img-thumbnail" 
                                     style="max-height:180px;margin-bottom:15px;">
                            </div>
                            <table class="table table-condensed">
                                <tr>
                                    <th>ISBN</th>
                                    <td>'.htmlentities($result->ISBNNumber).'</td>
                                </tr>
                                <tr>
                                    <th>Category</th>
                                    <td>'.htmlentities($result->CategoryName).'</td>
                                </tr>
                                <tr>
                                    <th>Publisher</th>
                                    <td>'.htmlentities($result->PublisherName).'</td>
                                </tr>
                                <tr>
                                    <th>Total Copies</th>
                                    <td>'.htmlentities($result->bookQty).'</td>
                                </tr>
                                <tr class="'.($isAvailable ? 'success' : 'danger').'">
                                    <th>Available</th>
                                    <td><strong>'.$availableQty.'</strong></td>
                                </tr>
                            </table>';
            
            if ($isAvailable) {
                echo '<button type="button" class="btn btn-success btn-block select-book"
                        data-bookid="'.$result->id.'"
                        data-isbn="'.htmlentities($result->ISBNNumber).'"
                        data-bookname="'.htmlentities($result->BookName).'">
                        <i class="fa fa-check"></i> Select
                    </button>';
            } else {
                echo '<button type="button" class="btn btn-danger btn-block" disabled>
                        <i class="fa fa-times"></i> Unavailable
                    </button>';
            }
            
            echo '</div></div></div>';
        }
        echo '</div>';
        
        echo '<script>
                $(".select-book").click(function() {
                    var bookid = $(this).data("bookid");
                    var isbn = $(this).data("isbn");
                    var bookname = $(this).data("bookname");
                    
                    // Update form fields
                    $("#bookid").val(isbn);
                    $("#bookid_display").val(bookname + " (" + isbn + ")");
                    
                    // Show selection confirmation
                    $("#book-selection").html(\'<div class="alert alert-success">\'
                        + \'<i class="fa fa-check-circle"></i> Selected: <strong>\'
                        + bookname + \'</strong> (ISBN: \' + isbn + \')\'
                        + \'</div>\');
                    
                    // Enable submit button
                    $("#submit").prop("disabled", false);
                    
                    // Scroll to selection confirmation
                    $("html, body").animate({
                        scrollTop: $("#book-selection").offset().top - 100
                    }, 500);
                });
              </script>';
    } else {
        echo '<div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle"></i> No books found matching: '.htmlentities($bookid).'
              </div>';
        echo '<script>
                $("#book-selection").html("");
                $("#submit").prop("disabled", true);
              </script>';
    }
}
?>
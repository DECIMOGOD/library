<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('includes/config.php');

function getBookImagePath($imageName) {
    $basePath = 'shared/bookImg';
    $absolutePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $basePath . $imageName;
    
    if (file_exists($absolutePath) && !empty($imageName)) {
        return $basePath . $imageName;
    }
    return false;
}

if(isset($_POST['bookid'])) {
    $bookid = trim($_POST['bookid']);
    
    $sql = "SELECT tblbooks.id, tblbooks.BookName, tblbooks.ISBNNumber, tblbooks.bookQty, 
                   tblcategory.CategoryName, tblpublishers.PublisherName,
                   tblbooks.bookImage
            FROM tblbooks 
            JOIN tblcategory ON tblbooks.CatId = tblcategory.id 
            JOIN tblpublishers ON tblbooks.PublisherID = tblpublishers.id
            WHERE tblbooks.ISBNNumber = :bookid OR tblbooks.BookName LIKE :bookname
            ORDER BY tblbooks.BookName ASC";
    
    $query = $dbh->prepare($sql);
    $query->bindParam(':bookid', $bookid, PDO::PARAM_STR);
    $booknameParam = "%".$bookid."%";
    $query->bindParam(':bookname', $booknameParam, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    
    if($query->rowCount() > 0) {
        if($query->rowCount() == 1) {
            $book = $results[0];
            $imagePath = getBookImagePath($book->bookImage);
            
            $html = '<div class="row">
                <div class="col-md-6">
                    <div class="book-image">';
            $html .= $imagePath ? 
                '<img src="'.$imagePath.'" width="200" height="300" class="img-thumbnail" />' : 
                '<i class="fa fa-book fa-5x" style="color:#999;"></i>';
            $html .= '</div>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th>Book Title</th>
                            <td>'.htmlentities($book->BookName).'</td>
                        </tr>
                        <tr>
                            <th>ISBN</th>
                            <td>'.htmlentities($book->ISBNNumber).'</td>
                        </tr>
                        <tr>
                            <th>Publisher</th>
                            <td>'.htmlentities($book->PublisherName).'</td>
                        </tr>
                        <tr>
                            <th>Category</th>
                            <td>'.htmlentities($book->CategoryName).'</td>
                        </tr>
                        <tr>
                            <th>Availability</th>
                            <td>';
            $html .= $book->bookQty > 0 ? 
                '<span class="label label-success">Available ('.$book->bookQty.' copies)</span>' : 
                '<span class="label label-danger">Currently checked out</span>';
            $html .= '</td>
                        </tr>
                    </table>
                    <input type="hidden" name="aqty" id="aqty" value="'.$book->bookQty.'" />
                    <input type="hidden" name="bookid" id="bookid" value="'.$book->id.'" />
                </div>
            </div>';
            
            echo $html;
        } else {
            $html = '<div class="book-selection-container">
                    <h4>Multiple books found. Please select one:</h4>
                    <div class="row">';
            
            foreach($results as $book) {
                $imagePath = getBookImagePath($book->bookImage);
                
                $html .= '<div class="col-md-4 book-selection-item" data-bookid="'.$book->id.'" 
                      style="margin-bottom:20px;cursor:pointer;">
                        <div class="thumbnail" style="padding:10px;">';
                $html .= $imagePath ? 
                    '<img src="'.$imagePath.'" style="height:150px;object-fit:contain;display:block;margin:0 auto;">' : 
                    '<i class="fa fa-book fa-4x" style="color:#999;display:block;text-align:center;"></i>';
                $html .= '<div class="caption">
                            <h4 style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'.htmlentities($book->BookName).'</h4>
                            <p>ISBN: '.htmlentities($book->ISBNNumber).'</p>
                            <p>Available: '.($book->bookQty > 0 ? 
                                '<span class="text-success">'.$book->bookQty.' copies</span>' : 
                                '<span class="text-danger">Checked out</span>').'</p>
                          </div>
                        </div>
                      </div>';
            }
            
            $html .= '</div></div>';
            echo $html;
        }
    } else {
        echo '<div class="alert alert-danger">
                No book found matching your search.
              </div>';
    }
    exit();
}

if(isset($_POST['getbook'])) {
    $bookid = (int)$_POST['getbook'];
    
    $sql = "SELECT tblbooks.*, tblcategory.CategoryName, tblpublishers.PublisherName
            FROM tblbooks 
            JOIN tblcategory ON tblbooks.CatId = tblcategory.id 
            JOIN tblpublishers ON tblbooks.PublisherID = tblpublishers.id
            WHERE tblbooks.id = :bookid";
    
    $query = $dbh->prepare($sql);
    $query->bindParam(':bookid', $bookid, PDO::PARAM_INT);
    $query->execute();
    $book = $query->fetch(PDO::FETCH_OBJ);
    
    if($book) {
        $imagePath = getBookImagePath($book->bookImage);
        
        $html = '<div class="row">
            <div class="col-md-6">
                <div class="book-image">';
        $html .= $imagePath ? 
            '<img src="'.$imagePath.'" width="200" height="300" class="img-thumbnail" />' : 
            '<i class="fa fa-book fa-5x" style="color:#999;"></i>';
        $html .= '</div>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th>Book Title</th>
                        <td>'.htmlentities($book->BookName).'</td>
                    </tr>
                    <tr>
                        <th>ISBN</th>
                        <td>'.htmlentities($book->ISBNNumber).'</td>
                    </tr>
                    <tr>
                        <th>Publisher</th>
                        <td>'.htmlentities($book->PublisherName).'</td>
                    </tr>
                    <tr>
                        <th>Category</th>
                        <td>'.htmlentities($book->CategoryName).'</td>
                    </tr>
                    <tr>
                        <th>Availability</th>
                        <td>';
        $html .= $book->bookQty > 0 ? 
            '<span class="label label-success">Available ('.$book->bookQty.' copies)</span>' : 
            '<span class="label label-danger">Currently checked out</span>';
        $html .= '</td>
                    </tr>
                </table>
                <input type="hidden" name="aqty" id="aqty" value="'.$book->bookQty.'" />
                <input type="hidden" name="bookid" id="bookid" value="'.$book->id.'" />
            </div>
        </div>';
        
        echo $html;
    } else {
        echo '<div class="alert alert-danger">Book not found</div>';
    }
    exit();
}

if(isset($_POST['lrn'])) {
    $lrn = $_POST['lrn'];
    
    $sql = "SELECT * FROM tblstudents WHERE LRN=:lrn";
    $query = $dbh->prepare($sql);
    $query->bindParam(':lrn', $lrn, PDO::PARAM_STR);
    $query->execute();
    $student = $query->fetch(PDO::FETCH_OBJ);
    
    if($query->rowCount() > 0) {
        $html = '<div class="alert alert-success">
                <strong>Student Found!</strong><br>
                Name: '.htmlentities($student->Name).'<br>
                LRN: '.htmlentities($student->LRN).'<br>';
        if(!empty($student->Grade_Level)) {
            $html .= 'Grade Level: '.htmlentities($student->Grade_Level).'<br>';
        }
        if(!empty($student->Section)) {
            $html .= 'Section: '.htmlentities($student->Section).'<br>';
        }
        if(!empty($student->Strand)) {
            $html .= 'Strand: '.htmlentities($student->Strand);
        }
        $html .= '</div>';
        echo $html;
    } else {
        echo '<div class="alert alert-danger">No student found with LRN: '.htmlentities($lrn).'</div>';
    }
    exit();
}
?>
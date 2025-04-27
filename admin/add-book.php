<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Online Library Management System | Add Book</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <style>
    :root {
        /* Modern dark theme color palette */
        --primary-color: #6366f1;
        --primary-light: #818cf8;
        --primary-dark: #4f46e5;
        --secondary-color: #10b981;
        --accent-color: #06b6d4;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
        --success-color: #22c55e;
        --info-color: #3b82f6;
        --dark-color: #0f172a;
        --darker-color: #020617;
        --light-color: #f8fafc;
        --gray-100: #f1f5f9;
        --gray-200: #e2e8f0;
        --gray-300: #cbd5e1;
        --gray-700: #334155;
        --gray-800: #1e293b;
        --gray-900: #0f172a;
        
        /* Glassmorphism and depth variables */
        --card-glass: rgba(30, 41, 59, 0.7);
        --card-border: rgba(255, 255, 255, 0.08);
        --card-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        --card-shadow-hover: 0 15px 40px rgba(0, 0, 0, 0.3);
        --transition-smooth: all 0.4s cubic-bezier(0.25, 1, 0.5, 1);
        
        /* Typography */
        --font-primary: 'Inter', 'SF Pro Display', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    body {
        font-family: var(--font-primary);
        background: linear-gradient(135deg, var(--darker-color) 0%, var(--dark-color) 100%);
        color: var(--gray-200);
        line-height: 1.7;
        overflow-x: hidden;
    }

    .content-wrapper {
        padding: 2.5rem;
        margin-top: 70px;
        position: relative;
    }

    /* Modern decorative background elements */
    .content-wrapper::before {
        content: '';
        position: fixed;
        top: 0;
        right: 0;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(99, 102, 241, 0.08) 0%, rgba(99, 102, 241, 0) 70%);
        z-index: -1;
        border-radius: 50%;
        transform: translate(20%, -30%);
    }

    .content-wrapper::after {
        content: '';
        position: fixed;
        bottom: 0;
        left: 0;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(6, 182, 212, 0.06) 0%, rgba(6, 182, 212, 0) 70%);
        z-index: -1;
        border-radius: 50%;
        transform: translate(-30%, 30%);
    }

    .header-line {
        font-size: 2rem;
        font-weight: 800;
        color: var(--light-color);
        margin-bottom: 2.5rem;
        position: relative;
        padding-bottom: 1rem;
        letter-spacing: -0.5px;
    }

    .header-line::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 5px;
        background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        border-radius: 10px;
        transition: var(--transition-smooth);
    }

    .header-line:hover::after {
        width: 120px;
    }

    /* Panel Styling */
    .panel {
        border: 1px solid var(--card-border);
        border-radius: 16px;
        overflow: hidden;
        transition: var(--transition-smooth);
        box-shadow: var(--card-shadow);
        background: var(--card-glass);
        backdrop-filter: blur(20px);
        transform-style: preserve-3d;
    }

    .panel:hover {
        transform: translateY(-5px);
        box-shadow: var(--card-shadow-hover);
    }

    .panel-heading {
        background: linear-gradient(90deg, var(--gray-800), var(--gray-900));
        color: var(--light-color);
        border-bottom: 1px solid var(--card-border);
        font-weight: 600;
        letter-spacing: 0.5px;
        padding: 1.25rem 1.5rem;
    }

    .panel-body {
        padding: 2rem;
    }

    /* Form Styling */
    .form-control {
        background-color: rgba(15, 23, 42, 0.7);
        border: 1px solid var(--gray-700);
        color: var(--gray-200);
        border-radius: 8px;
        padding: 0.75rem 1rem;
        transition: var(--transition-smooth);
    }

    .form-control:focus {
        background-color: rgba(30, 41, 59, 0.9);
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        color: var(--light-color);
    }

    .form-group label {
        font-weight: 500;
        color: var(--gray-300);
        margin-bottom: 0.5rem;
        display: block;
    }

    .required-field::after {
        content: " *";
        color: var(--danger-color);
    }

    .help-block {
        font-size: 13px;
        color: var(--gray-300);
        margin-top: 0.25rem;
    }

    /* Tab Styling */
    .nav-tabs {
        border-bottom: 1px solid var(--gray-700);
    }

    .nav-tabs > li > a {
        color: var(--gray-300);
        border-radius: 8px 8px 0 0;
        padding: 0.75rem 1.5rem;
        margin-right: 0.25rem;
        font-weight: 500;
        transition: var(--transition-smooth);
        border: 1px solid transparent;
    }

    .nav-tabs > li > a:hover {
        background-color: rgba(99, 102, 241, 0.1);
        border-color: transparent;
        color: var(--primary-light);
    }

    .nav-tabs > li.active > a,
    .nav-tabs > li.active > a:hover,
    .nav-tabs > li.active > a:focus {
        color: var(--primary-color);
        background-color: rgba(15, 23, 42, 0.7);
        border: 1px solid var(--gray-700);
        border-bottom-color: transparent;
        font-weight: 600;
    }

    .tab-content {
        padding: 1.5rem;
        background-color: rgba(15, 23, 42, 0.7);
        border: 1px solid var(--gray-700);
        border-top: none;
        border-radius: 0 0 8px 8px;
    }

    /* Button Styling */
    .btn {
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: var(--transition-smooth);
        border: none;
    }

    .btn-info {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
    }

    .btn-info:hover {
        background: linear-gradient(135deg, var(--primary-light), var(--primary-color));
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
    }

    /* Book Details Section */
    .book-details-section {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid var(--gray-700);
    }

    .book-details-section h4 {
        color: var(--light-color);
        margin-bottom: 1.5rem;
        font-weight: 600;
        position: relative;
        padding-bottom: 0.5rem;
    }

    .book-details-section h4::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 40px;
        height: 3px;
        background: linear-gradient(90deg, var(--accent-color), transparent);
        border-radius: 3px;
    }

    /* Bulk Upload Styles */
    .bulk-upload-container {
        background: rgba(15, 23, 42, 0.8);
        border-radius: 12px;
        padding: 2rem;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--gray-700);
    }

    .bulk-upload-header {
        color: var(--light-color);
        border-bottom: 1px solid var(--gray-700);
        padding-bottom: 1rem;
        margin-top: 0;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .bulk-upload-header i {
        color: var(--primary-color);
    }

    .upload-steps {
        margin: 2rem 0;
        position: relative;
    }

    .step {
        display: none;
        padding: 1.5rem;
        background: rgba(30, 41, 59, 0.6);
        border-radius: 12px;
        margin-bottom: 1.5rem;
        border: 1px solid var(--gray-700);
    }

    .step.active {
        display: block;
        animation: fadeInUp 0.5s ease-out forwards;
    }

    .step-number {
        display: inline-flex;
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-right: 12px;
        font-weight: bold;
        font-size: 14px;
    }

    .step h5 {
        display: inline-flex;
        margin: 0;
        vertical-align: middle;
        color: var(--light-color);
        font-weight: 600;
        align-items: center;
        height: 32px;
    }

    .step-content {
        margin-top: 1.5rem;
    }

    .step-instructions {
        padding-left: 44px;
        color: var(--gray-300);
    }

    .step-instructions li {
        margin-bottom: 0.5rem;
    }

    .file-upload-wrapper {
        position: relative;
        margin-bottom: 1rem;
    }

    .file-upload-preview {
        margin-top: 0.5rem;
        font-size: 14px;
        color: var(--gray-300);
        padding: 0.5rem;
        background: rgba(15, 23, 42, 0.5);
        border-radius: 6px;
        border: 1px dashed var(--gray-700);
    }

    .upload-guide {
        background: rgba(30, 41, 59, 0.8);
        padding: 1.5rem;
        border-radius: 12px;
        margin-top: 2rem;
        border: 1px solid var(--gray-700);
    }

    .upload-guide h5 {
        margin-top: 0;
        color: var(--light-color);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .upload-guide h5 i {
        color: var(--accent-color);
    }

    .guide-section {
        margin-bottom: 1.5rem;
    }

    .guide-section h6 {
        color: var(--primary-light);
        margin-bottom: 0.75rem;
    }

    /* Button Styles */
    .step-nav-buttons {
        margin-top: 1.5rem;
        text-align: right;
    }

    .btn-step {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: var(--transition-smooth);
        cursor: pointer;
        border: none;
        font-size: 14px;
    }

    .btn-next {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
    }

    .btn-next:hover {
        background: linear-gradient(135deg, var(--primary-light), var(--primary-color));
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(99, 102, 241, 0.3);
    }

    .btn-prev {
        background: rgba(255, 255, 255, 0.05);
        color: var(--gray-300);
        border: 1px solid var(--gray-700);
        margin-right: 0.75rem;
    }

    .btn-prev:hover {
        background: rgba(255, 255, 255, 0.1);
        color: var(--light-color);
    }

    .btn-prev:disabled {
        background: rgba(255, 255, 255, 0.02);
        color: var(--gray-700);
        cursor: not-allowed;
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive Adjustments */
    @media (max-width: 992px) {
        .content-wrapper {
            padding: 2rem;
        }
    }

    @media (max-width: 768px) {
        .content-wrapper {
            padding: 1.5rem;
        }
        
        .panel-body {
            padding: 1.5rem;
        }
        
        .step-nav-buttons {
            text-align: center;
        }
        
        .btn-step {
            display: inline-block;
            width: auto;
        }
    }

    @media (max-width: 576px) {
        .content-wrapper {
            padding: 1rem;
        }
        
        .header-line {
            font-size: 1.75rem;
        }
        
        .panel-body {
            padding: 1rem;
        }
        
        .tab-content {
            padding: 1rem;
        }
    }
    </style>
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container">
            <div class="row pad-botm">
                <div class="col-md-12">
                    <h4 class="header-line">Add Book</h4>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            Book Information
                        </div>
                        <div class="panel-body">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#single" data-toggle="tab">Single Entry</a></li>
                                <li><a href="#bulk" data-toggle="tab">Bulk Upload</a></li>
                            </ul>
                            
                            <div class="tab-content">
                                <!-- Single Entry Tab -->
                                <div class="tab-pane active" id="single">
                                    <form role="form" method="post" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-6">   
                                                <div class="form-group">
                                                    <label class="required-field">Book Name</label>
                                                    <input class="form-control" type="text" name="bookname" autocomplete="off" required />
                                                </div>
                                            </div>

                                            <div class="col-md-6">  
                                                <div class="form-group">
                                                    <label class="required-field">Category</label>
                                                    <select class="form-control" name="category" required="required">
                                                        <option value="">Select Category</option>
                                                        <?php 
                                                        $status=1;
                                                        $sql = "SELECT * from tblcategory where Status=:status";
                                                        $query = $dbh->prepare($sql);
                                                        $query->bindParam(':status',$status, PDO::PARAM_STR);
                                                        $query->execute();
                                                        $results=$query->fetchAll(PDO::FETCH_OBJ);
                                                        if($query->rowCount() > 0) {
                                                            foreach($results as $result) { ?>  
                                                            <option value="<?php echo htmlentities($result->id);?>"><?php echo htmlentities($result->CategoryName);?></option>
                                                        <?php }} ?> 
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">  
                                                <div class="form-group">
                                                    <label class="required-field">Publisher</label>
                                                    <select class="form-control" name="publisher" required="required">
                                                        <option value="">Select Publisher</option>
                                                        <?php 
                                                        $sql = "SELECT * from tblpublishers";
                                                        $query = $dbh->prepare($sql);
                                                        $query->execute();
                                                        $results=$query->fetchAll(PDO::FETCH_OBJ);
                                                        if($query->rowCount() > 0) {
                                                            foreach($results as $result) { ?>  
                                                            <option value="<?php echo htmlentities($result->id);?>"><?php echo htmlentities($result->PublisherName);?></option>
                                                        <?php }} ?> 
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">  
                                                <div class="form-group">
                                                    <label class="required-field">ISBN Number</label>
                                                    <input class="form-control" type="text" name="isbn" id="isbn" required="required" autocomplete="off" />
                                                    <p class="help-block">ISBN must be unique</p>
                                                </div>
                                            </div>

                                            <div class="col-md-6">  
                                                <div class="form-group">
                                                    <label class="required-field">Book Picture</label>
                                                    <input class="form-control" type="file" name="bookpic" autocomplete="off" required="required" />
                                                </div>
                                            </div>

                                            <div class="col-md-6">  
                                                <div class="form-group">
                                                    <label class="required-field">Book Quantity</label>
                                                    <input class="form-control" type="number" name="bqty" min="1" autocomplete="off" required="required" />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Additional Book Details -->
                                        <div class="book-details-section">
                                            <h4>Additional Book Details</h4>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Edition</label>
                                                        <input class="form-control" type="text" name="edition" autocomplete="off" />
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label>Cover Type</label>
                                                        <select class="form-control" name="coverType">
                                                            <option value="">Select Cover Type</option>
                                                            <option value="Hardcover">Hardcover</option>
                                                            <option value="Paperback">Paperback</option>
                                                            <option value="Spiral">Spiral</option>
                                                            <option value="E-book">E-book</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Number of Pages</label>
                                                        <input class="form-control" type="number" name="pages" min="0" />
                                                    </div>
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Height (cm)</label>
                                                                <input class="form-control" type="number" step="0.1" name="height" min="0" />
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Shelf Location</label>
                                                                <input class="form-control" type="text" name="shelfLocation" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-12"> 
                                                <button type="submit" name="add" class="btn btn-info">
                                                    <i class="fa fa-plus-circle"></i> Add Book
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Bulk Upload Tab -->
                                <div class="tab-pane" id="bulk">
                                    <div class="bulk-upload-container">
                                        <h4 class="bulk-upload-header"><i class="fa fa-upload"></i> Bulk Book Upload</h4>
                                        
                                        <div class="upload-steps">
                                            <div class="step active" id="step1">
                                                <div class="step-number">1</div>
                                                <h5>Prepare Your Files</h5>
                                                <div class="step-content">
                                                    <p>Prepare your book data and cover images:</p>
                                                    <ul class="step-instructions">
                                                        <li>Create a CSV file with book information</li>
                                                        <li>Place cover images in a folder (use ISBN as filenames)</li>
                                                        <li>Compress images to a ZIP file</li>
                                                    </ul>
                                                    <div class="step-nav-buttons">
                                                        <button type="button" class="btn-step btn-prev" disabled>Back</button>
                                                        <button type="button" class="btn-step btn-next next-step" data-next="step2">Next</button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="step" id="step2">
                                                <div class="step-number">2</div>
                                                <h5>Upload Files</h5>
                                                <div class="step-content">
                                                    <form role="form" method="post" enctype="multipart/form-data" id="bulk-upload-form">
                                                        <div class="form-group">
                                                            <label>CSV File (Required)</label>
                                                            <div class="file-upload-wrapper">
                                                                <input type="file" name="bulk_file" class="form-control file-upload" accept=".csv" required>
                                                                <div class="file-upload-preview">No file selected</div>
                                                            </div>
                                                            <p class="help-block">Upload your book data in CSV format</p>
                                                        </div>

                                                        <div class="form-group">
                                                            <label>Cover Images ZIP (Optional)</label>
                                                            <div class="file-upload-wrapper">
                                                                <input type="file" name="images_zip" class="form-control file-upload" accept=".zip">
                                                                <div class="file-upload-preview">No file selected</div>
                                                            </div>
                                                            <p class="help-block">ZIP file containing book cover images (named as ISBN.jpg/png)</p>
                                                        </div>

                                                        <div class="step-nav-buttons">
                                                            <button type="button" class="btn-step btn-prev prev-step" data-prev="step1">Back</button>
                                                            <button type="submit" name="bulk_upload" class="btn btn-info">
                                                                <i class="fa fa-upload"></i> Upload & Process
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="upload-guide">
                                            <h5><i class="fa fa-question-circle"></i> CSV Format Guide</h5>
                                            <div class="guide-content">
                                                <p>Your CSV should contain these columns in order:</p>
                                                <ol>
                                                    <li><strong>Book Name</strong> (required)</li>
                                                    <li><strong>Category ID</strong> (required)</li>
                                                    <li><strong>Publisher ID</strong> (required)</li>
                                                    <li><strong>ISBN</strong> (required)</li>
                                                    <li><strong>Quantity</strong> (required)</li>
                                                    <li>Edition (optional)</li>
                                                    <li>Cover Type (optional: Hardcover/Paperback/Spiral/E-book)</li>
                                                    <li>Pages (optional)</li>
                                                    <li>Height in cm (optional)</li>
                                                    <li>Shelf Location (optional)</li>
                                                </ol>
                                                <p><a href="sample-books.csv" class="btn btn-sm btn-default" download>
                                                    <i class="fa fa-download"></i> Download CSV Template
                                                </a></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include('includes/footer.php');?>
    
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.js"></script>
    <script>
    $(document).ready(function() {
        // Step navigation for bulk upload
        $('.next-step').on('click', function() {
            var nextStep = $(this).data('next');
            $('.step').removeClass('active');
            $('#' + nextStep).addClass('active');
            return false;
        });

        $('.prev-step').on('click', function() {
            var prevStep = $(this).data('prev');
            $('.step').removeClass('active');
            $('#' + prevStep).addClass('active');
            return false;
        });

        // File upload preview
        $('.file-upload').change(function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).siblings('.file-upload-preview').text(fileName || 'No file selected');
        });

        // Form submission handling
        $('#bulk-upload-form').submit(function(e) {
            $('button[name="bulk_upload"]').html('<i class="fa fa-spinner fa-spin"></i> Processing...').prop('disabled', true);
        });
    });
    </script>
</body>
</html>
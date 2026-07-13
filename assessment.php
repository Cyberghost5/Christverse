<?php 
include 'includes/db.php';
include 'includes/head.php';

// Configurable Google Document URL for Camp Christos Assessment questions
// Replace this with your actual Google Document URL
$googleDocUrl = "https://forms.gle/xFnLjGa2zLCJ3BL69";
?>

<body>
    
    <?php include 'includes/navbar.php'; ?> 

    <!-- Page Header Start -->
    <div class="container-fluid page-header mb-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container text-center">
            <h1 class="display-4 text-white animated slideInDown mb-4">Camp Christos Assessment</h1>
            <nav aria-label="breadcrumb animated slideInDown">
                <ol class="breadcrumb justify-content-center mb-0">
                    <li class="breadcrumb-item"><a class="text-white" href="./">Home</a></li>
                    <li class="breadcrumb-item"><a class="text-white" href="camp-christos">Camp Christos</a></li>
                    <li class="breadcrumb-item text-primary active" aria-current="page">Assessment</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- Page Header End -->

    <!-- Assessment Card Section Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="bg-light p-5 rounded text-center shadow border-top border-5 border-primary position-relative overflow-hidden">
                        <div class="d-inline-flex align-items-center justify-content-center text-primary rounded-circle mb-4" style="width: 80px; height: 80px; background-color: var(--secondary);">
                            <i class="fa fa-file-signature fa-2x"></i>
                        </div>
                        <h2 class="mb-3 text-dark">Submit Your Camp Christos Assessment</h2>
                        <p class="fs-5 text-muted mb-4">Thank you for taking this step towards spiritual growth and accountability. Click the button below to access your Google Document questionnaire. Complete all modules with integrity and sincerity.</p>
                        
                        <div class="alert alert-info border-0 bg-secondary text-primary d-inline-block px-4 py-3 rounded mb-4" role="alert">
                            <i class="fa fa-info-circle me-2"></i><strong>Note:</strong> Your progress and answers will be reviewed by the Camp Christos mentoring board.
                        </div>
                        
                        <div class="mt-2">
                            <!-- Link to the prepared Google Document -->
                            <a href="<?php echo htmlspecialchars($googleDocUrl); ?>" target="_blank" class="btn btn-primary py-3 px-5 fs-5 shadow-sm">
                                Submit Assessment for Camp Christos
                                <div class="d-inline-flex btn-sm-square bg-white text-primary rounded-circle ms-2">
                                    <i class="fa fa-external-link-alt"></i>
                                </div>
                            </a>
                        </div>
                        
                        <div class="mt-5 text-muted small">
                            If you encounter any issues accessing the document, contact our technical support at <a href="mailto:christverse.live@gmail.com" class="text-primary">christverse.live@gmail.com</a>.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Assessment Card Section End -->

    <?php include 'includes/footer.php'; ?>
    
</body>
</html>

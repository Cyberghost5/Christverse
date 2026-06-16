<?php 
include 'includes/db.php';
include 'includes/head.php';

$join_success = false;
$join_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name']) && !empty($_POST['email'])) {
    if (!check_captcha_answer('join', $_POST['captcha'] ?? '')) {
        $join_error = "Incorrect verification answer. Please solve the math puzzle again.";
    } elseif (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO registrations (name, email, department, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $_POST['name'],
                $_POST['email'],
                $_POST['department'] ?? '',
                $_POST['message'] ?? ''
            ]);
            $join_success = true;
        } catch (\PDOException $e) {
            $join_error = "We encountered a database error while saving your details. Please try again.";
        }
    } else {
        $join_error = "Database connection is currently unavailable. Please verify your MySQL server is running.";
    }
}
?>

<body>
    
    <?php include 'includes/navbar.php'; ?> 

    <!-- Page Header Start -->
    <div class="container-fluid page-header mb-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container text-center">
            <h1 class="display-4 text-white animated slideInDown mb-4">Join Us</h1>
            <nav aria-label="breadcrumb animated slideInDown">
                <ol class="breadcrumb justify-content-center mb-0">
                    <li class="breadcrumb-item"><a class="text-white" href="./">Home</a></li>
                    <li class="breadcrumb-item text-primary active" aria-current="page">Join Us</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- Page Header End -->


    <!-- Join Us Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row justify-content-center">
                <?php if ($join_success): ?>
                    <!-- Form Submission Success Card -->
                    <div class="col-lg-8 mx-auto wow fadeIn" data-wow-delay="0.1s">
                        <div class="bg-white p-5 rounded shadow text-center border-top border-5 border-primary">
                            <div class="d-inline-flex align-items-center justify-content-center text-primary rounded-circle mb-4" style="width: 80px; height: 80px; background-color: var(--secondary);">
                                <i class="fa fa-check fa-2x"></i>
                            </div>
                            <h2 class="mb-3 text-dark">Welcome to the Family!</h2>
                            <p class="fs-5 text-muted mb-4">Thank you, <strong><?php echo htmlspecialchars($_POST['name']); ?></strong>, for joining Christverse. We have received your application for the <strong><?php echo htmlspecialchars($_POST['department']); ?></strong> department.</p>
                            <p class="text-muted">A confirmation has been sent to <strong><?php echo htmlspecialchars($_POST['email']); ?></strong>. Our team will reach out to you shortly with next steps.</p>
                            <a href="./" class="btn btn-primary py-3 px-5 mt-3">
                                Back to Home
                                <div class="d-inline-flex btn-sm-square bg-white text-primary rounded-circle ms-2">
                                    <i class="fa fa-arrow-right"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Onboarding Form -->
                    <?php $selected_dept = isset($_GET['dept']) ? $_GET['dept'] : ''; ?>
                    <?php if ($join_error): ?>
                        <div class="col-12 mb-4">
                            <div class="alert alert-danger" role="alert">
                                <i class="fa fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($join_error); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="col-lg-6 wow fadeIn" data-wow-delay="0.1s">
                        <div class="d-inline-block rounded-pill bg-secondary text-primary py-1 px-3 mb-3">Get Involved</div>
                        <h1 class="display-6 mb-4">Start Your Journey with Christverse</h1>
                        <p class="text-muted mb-5">We are a global community on a mission to reach 1 billion people across 10,000 cities. Whether you are an influencer, writer, artist, or seeking mentorship, there is a place for you to express your faith and grow.</p>
                        
                        <div class="bg-light rounded p-4 border-start border-5 border-primary mb-4">
                            <h5 class="mb-2"><i class="fa fa-users text-primary me-2"></i>Departments & Mentorship</h5>
                            <p class="mb-0 text-muted">Join any of our 8 active initiatives to partner in the spread of the gospel and personal development.</p>
                        </div>
                    </div>
                    <div class="col-lg-6 wow fadeIn" data-wow-delay="0.5s">
                        <div class="h-100 bg-secondary p-5 rounded">
                            <form action="join.php" method="POST">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" name="name" class="form-control bg-light border-0" id="name" placeholder="Your Name" required>
                                            <label for="name">Your Name</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="email" name="email" class="form-control bg-light border-0" id="email" placeholder="Your Email" required>
                                            <label for="email">Your Email</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <select name="department" class="form-select bg-light border-0" id="department" required style="padding-top: 1.625rem; padding-bottom: 0.625rem;">
                                                <option value="" disabled <?php echo empty($selected_dept) ? 'selected' : ''; ?>>Select Department / Initiative</option>
                                                <option value="iCreate" <?php echo ($selected_dept == 'iCreate') ? 'selected' : ''; ?>>iCreate (Creatives)</option>
                                                <option value="Camp Christos" <?php echo ($selected_dept == 'Camp Christos') ? 'selected' : ''; ?>>Camp Christos (Mentorship)</option>
                                                <option value="TBT Podcast" <?php echo ($selected_dept == 'TBT Podcast') ? 'selected' : ''; ?>>TBT Podcast</option>
                                                <option value="Colors" <?php echo ($selected_dept == 'Colors') ? 'selected' : ''; ?>>Colors (Hangouts & Outreach)</option>
                                                <option value="Freeform" <?php echo ($selected_dept == 'Freeform') ? 'selected' : ''; ?>>Freeform (Community Bonding)</option>
                                            </select>
                                            <label for="department">Interest Area</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea name="message" class="form-control bg-light border-0" placeholder="Tell us a bit about yourself" id="message" style="height: 100px"></textarea>
                                            <label for="message">About Yourself (Optional)</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="number" name="captcha" class="form-control bg-light border-0" id="captcha" placeholder="Solve CAPTCHA" required>
                                            <label for="captcha">Spam Verification: Solve <?php echo get_captcha_question('join'); ?> =</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary w-100 py-3">
                                            Submit Application
                                            <div class="d-inline-flex btn-sm-square bg-white text-primary rounded-circle ms-2">
                                                <i class="fa fa-arrow-right"></i>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Join Us End -->

    <?php include 'includes/footer.php';?>
    
</body>

</html>

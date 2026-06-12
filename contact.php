<?php 
include 'includes/db.php';
include 'includes/head.php';

$contact_success = false;
$contact_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name']) && !empty($_POST['email'])) {
    if (!check_captcha_answer('contact', $_POST['captcha'] ?? '')) {
        $contact_error = "Incorrect verification answer. Please solve the math puzzle again.";
    } elseif (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $_POST['name'],
                $_POST['email'],
                $_POST['subject'] ?? '',
                $_POST['message'] ?? ''
            ]);
            $contact_success = true;
        } catch (\PDOException $e) {
            $contact_error = "We encountered a database error while sending your message. Please try again.";
        }
    } else {
        $contact_error = "Database connection is currently unavailable. Please verify your MySQL server is running.";
    }
}
?>

<body>
    
    <?php include 'includes/navbar.php'; ?> 

    <!-- Page Header Start -->
    <div class="container-fluid page-header mb-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container text-center">
            <h1 class="display-4 text-white animated slideInDown mb-4">Contact Us</h1>
            <nav aria-label="breadcrumb animated slideInDown">
                <ol class="breadcrumb justify-content-center mb-0">
                    <li class="breadcrumb-item"><a class="text-white" href="#">Home</a></li>
                    <li class="breadcrumb-item"><a class="text-white" href="#">Pages</a></li>
                    <li class="breadcrumb-item text-primary active" aria-current="page">Contact Us</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- Page Header End -->


    <!-- Contact Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row g-5">
                <?php if ($contact_success): ?>
                    <div class="col-lg-12 text-center wow fadeIn" data-wow-delay="0.1s">
                        <div class="bg-white p-5 rounded shadow border-top border-5 border-primary">
                            <div class="d-inline-flex align-items-center justify-content-center text-primary rounded-circle mb-4" style="width: 80px; height: 80px; background-color: var(--secondary);">
                                <i class="fa fa-envelope-open fa-2x"></i>
                            </div>
                            <h2 class="mb-3 text-dark">Message Sent Successfully!</h2>
                            <p class="fs-5 text-muted mb-4">Thank you, <strong><?php echo htmlspecialchars($_POST['name']); ?></strong>. We have received your message and will get back to you shortly at <strong><?php echo htmlspecialchars($_POST['email']); ?></strong>.</p>
                            <a href="./" class="btn btn-primary py-3 px-5 mt-2">
                                Back to Home
                                <div class="d-inline-flex btn-sm-square bg-white text-primary rounded-circle ms-2">
                                    <i class="fa fa-arrow-right"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="col-lg-6 wow fadeIn" data-wow-delay="0.1s">
                        <div class="d-inline-block rounded-pill bg-secondary text-primary py-1 px-3 mb-3">Contact Us</div>
                        <h1 class="display-6 mb-5">Have Questions? Reach Out to Us</h1>
                        <?php if ($contact_error): ?>
                            <div class="alert alert-danger mb-4" role="alert">
                                <i class="fa fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($contact_error); ?>
                            </div>
                        <?php endif; ?>
                        <form action="contact.php" method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" name="name" class="form-control" id="name" placeholder="Your Name" required>
                                        <label for="name">Your Name</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" name="email" class="form-control" id="email" placeholder="Your Email" required>
                                        <label for="email">Your Email</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" name="subject" class="form-control" id="subject" placeholder="Subject" required>
                                        <label for="subject">Subject</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea name="message" class="form-control" placeholder="Leave a message here" id="message" style="height: 120px" required></textarea>
                                        <label for="message">Message</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="number" name="captcha" class="form-control" id="captcha" placeholder="Solve CAPTCHA" required>
                                        <label for="captcha">Spam Verification: Solve <?php echo get_captcha_question('contact'); ?> =</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary py-3 px-5">
                                        Send Message
                                        <div class="d-inline-flex btn-sm-square bg-white text-primary rounded-circle ms-2">
                                            <i class="fa fa-arrow-right"></i>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-6 wow fadeIn" data-wow-delay="0.5s" style="min-height: 450px;">
                        <div class="position-relative rounded overflow-hidden h-100 shadow-sm border">
                            <iframe class="position-relative w-100 h-100"
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d252163.63056191776!2d7.3364964648784195!3d9.055375497217316!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x104e767f4c02f1f3%3A0x6795f9e2b1f8eb4f!2sAbuja%2C%20Federal%20Capital%20Territory%2C%20Nigeria!5e0!3m2!1sen!2sng!4v1700000000000!5m2!1sen!2sng"
                            frameborder="0" style="min-height: 450px; border:0; filter: grayscale(100%) contrast(120%) opacity(85%);" allowfullscreen="" aria-hidden="false"
                            tabindex="0"></iframe>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Contact End -->


    <?php include 'includes/footer.php';?>
    
</body>

</html>
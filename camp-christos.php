<?php 
include 'includes/db.php';
include 'includes/head.php';

$app_success = false;
$app_error = null;
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_camp'])) {
    $name = trim($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $phone = trim($_POST['phone'] ?? '');
    $motivation = trim($_POST['motivation'] ?? '');
    $captcha = trim($_POST['captcha'] ?? '');

    if (empty($name) || empty($email) || empty($motivation)) {
        $app_error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $app_error = "Please provide a valid email address.";
    } elseif (!check_captcha_answer('camp_app', $captcha)) {
        $app_error = "Incorrect verification answer. Please solve the math puzzle again.";
    } else {
        if (isset($pdo)) {
            try {
                // Check if name or email already exists in applications
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM camp_christos_applications WHERE name = ? OR email = ?");
                $stmt->execute([$name, $email]);
                if ($stmt->fetchColumn() > 0) {
                    $app_error = "An application has already been submitted under this name or email address.";
                } else {
                    // Insert application
                    $stmt = $pdo->prepare("INSERT INTO camp_christos_applications (name, email, phone, motivation) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $email, $phone, $motivation]);
                    $app_success = true;

                    $html_message = "
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset='utf-8'>
                        <title>" . htmlspecialchars($subject) . "</title>
                        <style>
                            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f8; margin: 0; padding: 0; color: #2b303a; }
                            .wrapper { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
                            .header { background: #CE9B2E; padding: 25px; text-align: center; }
                            .header img { max-height: 45px; }
                            .content { padding: 40px 30px; line-height: 1.6; font-size: 16px; }
                            .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #8e9aa8; border-top: 1px solid #eeeeee; }
                            .footer a { color: #CE9B2E; text-decoration: none; }
                        </style>
                    </head>
                    <body>
                        <div class='wrapper'>
                            <div class='header'>
                                <!-- Brand Header -->
                                <div style='color: #ffffff; font-size: 24px; font-weight: bold; font-family: sans-serif; letter-spacing: 1px;'><img src='https://christverse.org/img/logos/Christverse%20Horizontal%20White.png' alt='Christverse Logo' style='height: 80px;'></div>
                            </div>
                            <div class='content'>
                                
                                <h3>Dear " . htmlspecialchars($name) . ",</h3>
                                <p>Congratulations on your onboarding into the Camp Christos discipleship program.</p>
                                <p>The teaching modules are designed specifically for your progress and joy in the faith.</p>
                                <p>Please be informed that you will need to access the WhatsApp group for more information.</p>
                                <p>Find the link below.</p>
                                <p style='margin-top: 30px; text-align: center;'>
                                    <a href='https://chat.whatsapp.com/HVKdFeyyxCW5TIj3A0HqRA' style='background-color: #CE9B2E; color: white; padding: 12px 25px; text-decoration: none; border-radius: 30px; font-weight: bold; display: inline-block;'>Explore the Community</a>
                                </p>
                                <p>We hope you have a wonderful journey with us. May you learn Christ and make Him known to your world.</p>

                                <p><strong>Calvary Regards,</strong><br>Camp Cristos.</p>
                            
                            </div>
                            <div class='footer'>
                                <p>You received this email because you are registered with Christverse.</p>
                                <p>&copy; " . date('Y') . " Christverse Community, Abuja, Nigeria. All rights reserved.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                    ";

                    // Send customized emails
                    send_email($email, "Welcome to Camp Christos Cohort Application", $html_message);
                    
                    send_email(
                        "christverse.live@gmail.com", 
                        "New Camp Christos Application Submitted", 
                        "<p>A new discipleship application has been received for Camp Christos.</p><p><strong>Name:</strong> " . htmlspecialchars($name) . "<br><strong>Email:</strong> " . htmlspecialchars($email) . "<br><strong>Phone:</strong> " . htmlspecialchars($phone) . "<br><strong>Motivation:</strong><br>" . nl2br(htmlspecialchars($motivation)) . "</p>"
                    );
                }
            } catch (\PDOException $e) {
                $app_error = "We encountered a database error while saving your application. Please try again.";
            }
        } else {
            $app_error = "Database connection is currently unavailable.";
        }
    }
}
?>

<style>
    .pillar-card {
        transition: all 0.3s ease;
        border: none;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
    .pillar-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.1) !important;
    }
    .spec-icon {
        width: 60px;
        height: 60px;
        background-color: var(--secondary);
        color: var(--primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-bottom: 20px;
    }
    .pillar-num {
        font-family: 'Saira', sans-serif;
        font-size: 3rem;
        font-weight: 700;
        color: rgba(206, 155, 46, 0.15);
        position: absolute;
        top: 10px;
        right: 20px;
        line-height: 1;
    }
</style>

<body>
    
    <?php include 'includes/navbar.php'; ?> 

    <!-- Page Header Start -->
    <div class="container-fluid page-header mb-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container text-center">
            <h1 class="display-4 text-white animated slideInDown mb-4">Camp Christos</h1>
            <nav aria-label="breadcrumb animated slideInDown">
                <ol class="breadcrumb justify-content-center mb-0">
                    <li class="breadcrumb-item"><a class="text-white" href="./">Home</a></li>
                    <li class="breadcrumb-item text-white-50"><a class="text-white-50" href="initiatives">Initiatives</a></li>
                    <li class="breadcrumb-item text-primary active" aria-current="page">Camp Christos</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- Page Header End -->

    <!-- Camp Christos Intro Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="position-relative overflow-hidden h-100" style="min-height: 400px;">
                        <img class="position-absolute w-100 h-100 rounded" src="img/camp-cristos.jpg" alt="Camp Christos Discipleship" style="object-fit: cover;">
                    </div>
                </div>
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="h-100">
                        <div class="d-inline-block rounded-pill bg-secondary text-primary py-1 px-3 mb-3">Discipleship & Mentorship</div>
                        <h1 class="display-6 mb-4">Grow and Excel in Spiritual Maturity</h1>
                        <p class="mb-4">Camp Christos is the official discipleship and mentorship platform of Christverse. We are dedicated to raising young, righteous, counter-culture and narrative-changing believers who rule for Christ in their respective spheres of influence. Through sound biblical teachings, structured fellowship, and close mentorship, disciples are disciplined and established in foundational truth.</p>
                        
                        <div class="row g-4 mb-4">
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center bg-light rounded p-3">
                                    <div class="flex-shrink-0 btn-lg-square bg-white text-primary rounded-circle me-3">
                                        <i class="fa fa-user-friends"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">50 Disciples Limit</h6>
                                        <small class="text-muted">Per cohort for focused growth</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center bg-light rounded p-3">    
                                    <div class="flex-shrink-0 btn-lg-square bg-white text-primary rounded-circle me-3">
                                        <i class="fa fa-calendar-alt"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">3-Month Duration</h6>
                                        <small class="text-muted">Comprehensive curriculum</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Camp Christos Intro End -->

    <!-- Curriculum Pillars Start -->
    <div class="container-xxl py-5 bg-light">
        <div class="container">
            <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
                <div class="d-inline-block rounded-pill bg-secondary text-primary py-1 px-3 mb-3">Pillars of Growth</div>
                <h1 class="display-6 mb-4">Our Discipleship Curriculum</h1>
                <p class="text-muted">The learning path is structured to anchor each disciple in sound doctrine and deep personal fellowship with the Father.</p>
            </div>
            <div class="row g-4 justify-content-center">
                <!-- Salvation -->
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="card pillar-card bg-white h-100 p-4 p-xl-5 relative position-relative">
                        <span class="pillar-num">01</span>
                        <div class="spec-icon"><i class="fa fa-cross fa-2x"></i></div>
                        <h4 class="mb-3">Salvation</h4>
                        <p class="text-muted mb-0">Deepening understanding of redemptive grace, the finished work of Christ, justification by faith, and our identity as sons and daughters of God.</p>
                    </div>
                </div>

                <!-- Eternal Life -->
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.2s">
                    <div class="card pillar-card bg-white h-100 p-4 p-xl-5 relative position-relative">
                        <span class="pillar-num">02</span>
                        <div class="spec-icon"><i class="fa fa-infinity fa-2x"></i></div>
                        <h4 class="mb-3">Eternal Life</h4>
                        <p class="text-muted mb-0">Exploring the ZOE life, the quality of life and power that we possess in Christ, manifesting victory in our daily walk starting right now.</p>
                    </div>
                </div>

                <!-- Righteousness -->
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="card pillar-card bg-white h-100 p-4 p-xl-5 relative position-relative">
                        <span class="pillar-num">03</span>
                        <div class="spec-icon"><i class="fa fa-shield-alt fa-2x"></i></div>
                        <h4 class="mb-3">Righteousness</h4>
                        <p class="text-muted mb-0">Unveiling our right standing with the Father and living out righteous values in a counter-culture movement that inspires the world for Jesus.</p>
                    </div>
                </div>

                <!-- The Holy Spirit -->
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.4s">
                    <div class="card pillar-card bg-white h-100 p-4 p-xl-5 relative position-relative">
                        <span class="pillar-num">04</span>
                        <div class="spec-icon"><i class="fa fa-fire fa-2x"></i></div>
                        <h4 class="mb-3">The Holy Spirit</h4>
                        <p class="text-muted mb-0">Cultivating intimacy with the Person of the Holy Spirit, understanding His gifts, and walking in His power and guidance daily.</p>
                    </div>
                </div>

                <!-- Eschatology -->
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="card pillar-card bg-white h-100 p-4 p-xl-5 relative position-relative">
                        <span class="pillar-num">05</span>
                        <div class="spec-icon"><i class="fa fa-book-open fa-2x"></i></div>
                        <h4 class="mb-3">Eschatology</h4>
                        <p class="text-muted mb-0">Gaining scriptural knowledge of end-times prophecy, eternity, and our blessed hope, keeping us focused on our global mission.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Curriculum Pillars End -->

    <!-- Onboarding Form Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row justify-content-center">
                <?php if ($app_success): ?>
                    <div class="col-lg-8 text-center wow fadeIn" data-wow-delay="0.1s">
                        <div class="bg-white p-5 rounded shadow border-top border-5 border-primary">
                            <div class="d-inline-flex align-items-center justify-content-center text-primary rounded-circle mb-4" style="width: 80px; height: 80px; background-color: var(--secondary);">
                                <i class="fa fa-check fa-2x"></i>
                            </div>
                            <h2 class="mb-3 text-dark">Application Submitted Successfully!</h2>
                            <p class="fs-5 text-muted mb-4">Thank you, <strong><?php echo htmlspecialchars($name); ?></strong>. We have logged your application for the upcoming Camp Christos cohort.</p>
                            <p class="text-muted">A confirmation update has been sent to <strong><?php echo htmlspecialchars($email); ?></strong>. The admissions team will review your application and communicate status details soon.</p>
                            <a href="./" class="btn btn-primary py-3 px-5 mt-2">
                                Back to Home
                                <div class="d-inline-flex btn-sm-square bg-white text-primary rounded-circle ms-2">
                                    <i class="fa fa-arrow-right"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="col-lg-8">
                        <div class="bg-secondary p-5 rounded shadow-sm wow fadeIn" data-wow-delay="0.1s">
                            <div class="text-center mb-5">
                                <h2 class="text-dark mb-2">Apply for Camp Christos</h2>
                                <p class="text-muted">Cohort size is limited to 50 disciples. Secure your place for the next 6-month mentorship session.</p>
                            </div>
                            
                            <?php if ($app_error): ?>
                                <div class="alert alert-danger mb-4" role="alert">
                                    <i class="fa fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($app_error); ?>
                                </div>
                            <?php endif; ?>
                            
                            <form action="camp-christos" method="POST">
                                <input type="hidden" name="apply_camp" value="1">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" name="name" class="form-control bg-light border-0" id="name" placeholder="Your Name" required>
                                            <label for="name">Your Name</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="email" name="email" class="form-control bg-light border-0" id="email" placeholder="Your Email" required>
                                            <label for="email">Your Email Address</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="tel" name="phone" class="form-control bg-light border-0" id="phone" placeholder="Phone Number">
                                            <label for="phone">Phone Number (Optional)</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea name="motivation" class="form-control bg-light border-0" placeholder="Why do you want to join?" id="motivation" style="height: 120px" required></textarea>
                                            <label for="motivation">Why do you want to join this cohort? Describe your expectations.</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="number" name="captcha" class="form-control bg-light border-0" id="captcha" placeholder="Solve CAPTCHA" required>
                                            <label for="captcha">Verification: Solve <?php echo get_captcha_question('camp_app'); ?> =</label>
                                        </div>
                                    </div>
                                    <div class="col-12 text-center mt-4">
                                        <button type="submit" class="btn btn-primary w-100 py-3 rounded">
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
    <!-- Onboarding Form End -->

    <?php include 'includes/footer.php';?>
    
</body>
</html>

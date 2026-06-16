<!-- Footer Start -->
<div class="container-fluid bg-dark text-white-50 footer mt-5 pt-5 wow fadeIn" data-wow-delay="0.1s">
    <div class="container py-5">
        <div class="row g-5">
            <div class="col-lg-3 col-md-6">
                <img src="img/logos/Christverse Horizontal White.png" alt="Christverse Logo" style="height: 45px;" class="mb-4">
                <p>A global community of believers focused on raising young, rich, righteous, counter-culture and narrative-changing influencers for Christ.</p>
                <div class="d-flex pt-2">
                    <a class="btn btn-square me-1" href="https://www.facebook.com/christverse.live" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a class="btn btn-square me-1" href="https://www.instagram.com/christverse_live" target="_blank"><i class="fab fa-instagram"></i></a>
                    <a class="btn btn-square me-0" href="https://www.tiktok.com/@christverse_community" target="_blank"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <h5 class="text-light mb-4">Address</h5>
                <p><i class="fa fa-map-marker-alt me-3"></i>Abuja, Nigeria</p>
                <p><i class="fa fa-phone-alt me-3"></i>+234 704 437 3911</p>
                <p><i class="fa fa-envelope me-3"></i>christverse.live@gmail.com</p>
            </div>
            <div class="col-lg-3 col-md-6">
                <h5 class="text-light mb-4">Quick Links</h5>
                <a class="btn btn-link" href="about">About Us</a>
                <a class="btn btn-link" href="contact">Contact Us</a>
                <a class="btn btn-link" href="initiatives">Our Initiatives</a>
                <a class="btn btn-link" href="join">Join Us</a>
                <a class="btn btn-link" href="testimonial">Testimonials</a>
            </div>
            <div class="col-lg-3 col-md-6">
                <h5 class="text-light mb-4">Newsletter</h5>
                <p>Subscribe to our newsletter for updates and daily faith declarations.</p>
                <form action="subscribe" method="POST" class="mx-auto" style="max-width: 400px;">
                    <div class="position-relative mb-2">
                        <input name="email" class="form-control bg-transparent w-100 py-3 ps-4 pe-5" type="email" placeholder="Your email" required>
                        <button type="submit" class="btn btn-primary py-2 position-absolute top-0 end-0 mt-2 me-2">SignUp</button>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="text-white-50 small me-2">Verify human: <?php echo get_captcha_question('newsletter'); ?> =</span>
                        <input name="captcha" type="number" class="form-control bg-transparent text-white border-secondary" style="width: 70px; height: 32px; padding: 2px 8px; font-size: 0.85rem;" required>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="container-fluid copyright">
        <div class="container">
            <div class="row">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    &copy; <a href="./">Christverse</a>, All Rights Reserved.
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <!--/*** This template is free as long as you keep the footer author’s credit link/attribution link/backlink. If you'd like to use the template without the footer author’s credit link/attribution link/backlink, you can purchase the Credit Removal License from "https://htmlcodex.com/credit-removal". Thank you for your support. ***/-->
                    Designed By <a href="https://htmlcodex.com">HTML Codex</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_GET['subscribed'])): ?>
    <div class="alert alert-dismissible fade show text-center m-0 shadow border-top border-4 <?php
        if ($_GET['subscribed'] == '1') echo 'alert-success border-success';
        elseif ($_GET['subscribed'] == 'duplicate') echo 'alert-info border-info';
        elseif ($_GET['subscribed'] == 'wrongcaptcha') echo 'alert-danger border-danger';
        else echo 'alert-warning border-warning';
    ?>" role="alert" style="position: fixed; bottom: 30px; right: 30px; z-index: 9999; max-width: 380px;">
        <div class="d-flex align-items-center">
            <div class="me-3">
                <?php if ($_GET['subscribed'] == '1'): ?>
                    <i class="fa fa-check-circle fa-2x text-success"></i>
                <?php elseif ($_GET['subscribed'] == 'duplicate'): ?>
                    <i class="fa fa-info-circle fa-2x text-info"></i>
                <?php elseif ($_GET['subscribed'] == 'wrongcaptcha'): ?>
                    <i class="fa fa-times-circle fa-2x text-danger"></i>
                <?php else: ?>
                    <i class="fa fa-exclamation-circle fa-2x text-warning"></i>
                <?php endif; ?>
            </div>
            <div class="text-start">
                <h6 class="mb-1 text-dark font-weight-bold">
                    <?php
                    if ($_GET['subscribed'] == '1') echo 'Subscription Successful!';
                    elseif ($_GET['subscribed'] == 'duplicate') echo 'Already Subscribed!';
                    elseif ($_GET['subscribed'] == 'wrongcaptcha') echo 'Verification Failed';
                    else echo 'Subscription Alert';
                    ?>
                </h6>
                <small class="text-muted">
                    <?php
                    if ($_GET['subscribed'] == '1') echo 'Thank you for subscribing to the Christverse newsletter.';
                    elseif ($_GET['subscribed'] == 'duplicate') echo 'You are already in our list of subscribers!';
                    elseif ($_GET['subscribed'] == 'wrongcaptcha') echo 'Incorrect math answer. Please try again.';
                    elseif ($_GET['subscribed'] == 'nodb') echo 'Connection offline. Subscription failed.';
                    else echo 'An error occurred. Please try again later.';
                    ?>
                </small>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="top: 10px;"></button>
    </div>
<?php endif; ?>
<!-- Footer End -->


<!-- Back to Top -->
<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

<!-- JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="lib/wow/wow.min.js"></script>
<script src="lib/easing/easing.min.js"></script>
<script src="lib/waypoints/waypoints.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>
<script src="lib/parallax/parallax.min.js"></script>

<!-- Template Javascript -->
<script src="js/main.js"></script>
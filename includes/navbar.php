<?php
$active_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Spinner Start -->
<div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
    <div class="spinner-grow text-primary" role="status"></div>
</div>
<!-- Spinner End -->
     
<!-- Navbar Start -->
<div class="container-fluid fixed-top px-0 wow fadeIn" data-wow-delay="0.1s">
    <div class="top-bar text-white-50 row gx-0 align-items-center d-none d-lg-flex">
        <div class="col-lg-6 px-5 text-start">
            <small><i class="fa fa-map-marker-alt me-2"></i>Abuja, Nigeria</small>
            <small class="ms-4"><i class="fa fa-envelope me-2"></i>christverse.live@gmail.com</small>
        </div>
        <div class="col-lg-6 px-5 text-end">
            <small>Follow us:</small>
            <a class="text-white-50 ms-3" href="https://www.facebook.com/christverse.live" target="_blank"><i class="fab fa-facebook-f"></i></a>
            <a class="text-white-50 ms-3" href="https://www.instagram.com/christverse_live" target="_blank"><i class="fab fa-instagram"></i></a>
            <a class="text-white-50 ms-3" href="https://www.tiktok.com/@christverse_community" target="_blank"><i class="fab fa-tiktok"></i></a>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark py-lg-0 px-lg-5 wow fadeIn" data-wow-delay="0.1s">
        <a href="./" class="navbar-brand ms-4 ms-lg-0 d-flex align-items-center">
            <img src="img/logos/Christverse Horizontal White.png" alt="Christverse Logo" style="height: 45px;">
        </a>
        <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto p-4 p-lg-0">
                <a href="./" class="nav-item nav-link <?php echo ($active_page == './.php') ? 'active' : ''; ?>">Home</a>
                <a href="about" class="nav-item nav-link <?php echo ($active_page == 'about.php') ? 'active' : ''; ?>">About</a>
                <a href="initiatives" class="nav-item nav-link <?php echo ($active_page == 'initiatives.php') ? 'active' : ''; ?>">Initiatives</a>
                <a href="events" class="nav-item nav-link <?php echo ($active_page == 'events.php') ? 'active' : ''; ?>">Events</a>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle <?php echo in_array($active_page, ['team.php', 'testimonial.php']) ? 'active' : ''; ?>" data-bs-toggle="dropdown">Pages</a>
                    <div class="dropdown-menu m-0">
                        <a href="team" class="dropdown-item">Our Team</a>
                        <a href="testimonial" class="dropdown-item">Testimonials</a>
                    </div>
                </div>
                <a href="contact" class="nav-item nav-link <?php echo ($active_page == 'contact.php') ? 'active' : ''; ?>">Contact</a>
            </div>
            <div class="d-none d-lg-flex ms-2">
                <a class="btn btn-outline-primary py-2 px-3" href="join">
                    Join Us
                    <div class="d-inline-flex btn-sm-square bg-white text-primary rounded-circle ms-2">
                        <i class="fa fa-arrow-right"></i>
                    </div>
                </a>
            </div>
        </div>
    </nav>
</div>
<!-- Navbar End -->
<?php 
include 'includes/db.php';
include 'includes/head.php';

$reg_success = false;
$reg_error = null;
$name = '';
$email = '';
$event_title = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_event'])) {
    $event_id = intval($_POST['event_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $captcha = trim($_POST['captcha'] ?? '');

    if (empty($name) || empty($email) || empty($event_id)) {
        $reg_error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $reg_error = "Please provide a valid email address.";
    } elseif (!check_captcha_answer('event_reg', $captcha)) {
        $reg_error = "Incorrect verification answer. Please solve the math puzzle again.";
    } else {
        if (isset($pdo)) {
            try {
                // Verify event exists and fetch title
                $stmt = $pdo->prepare("SELECT title FROM events WHERE id = ?");
                $stmt->execute([$event_id]);
                $event_title = $stmt->fetchColumn();
                
                if (!$event_title) {
                    $reg_error = "Selected event does not exist.";
                } else {
                    // Insert registration
                    $stmt = $pdo->prepare("INSERT INTO event_registrations (event_id, name, email, phone, message) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$event_id, $name, $email, $phone, $message]);
                    $reg_success = true;
                }
            } catch (\PDOException $e) {
                $reg_error = "We encountered a database error while processing your registration. Please try again.";
            }
        } else {
            $reg_error = "Database connection is currently unavailable.";
        }
    }
}

// Fetch upcoming events from DB
$events = [];
$db_conn_error = false;
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
        $events = $stmt->fetchAll();
    } catch (\PDOException $e) {
        $db_conn_error = true;
    }
} else {
    $db_conn_error = true;
}

// Fallback static events if database is offline or empty
if ($db_conn_error || empty($events)) {
    $events = [
        [
            'id' => 1,
            'title' => 'Colors Outreach Hangout',
            'description' => 'Our official hangout and outdoor fellowship session where we connect, share stories, play team games, and share the gospel of Christ.',
            'category' => 'Outreach',
            'event_date' => '2026-07-11 16:00:00',
            'location' => 'Abuja Central Park & Online',
            'image_path' => 'img/courses-1.jpg'
        ],
        [
            'id' => 2,
            'title' => 'Camp Christos Mentorship Masterclass',
            'description' => 'A structured session for youth development, personal growth, and kingdom principles, aimed at raising narrative-changing rulers.',
            'category' => 'Mentorship',
            'event_date' => '2026-07-25 10:00:00',
            'location' => 'Zoom & YouTube Live',
            'image_path' => 'img/courses-2.jpg'
        ],
        [
            'id' => 3,
            'title' => 'Freeform Virtual Meetup',
            'description' => 'Our monthly virtual gathering for community bonding, fellowship, team camaraderie, and sharing faith declarations.',
            'category' => 'Community',
            'event_date' => '2026-08-08 18:00:00',
            'location' => 'Google Meet / Discord',
            'image_path' => 'img/courses-3.jpg'
        ]
    ];
}
?>

<style>
    .event-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        overflow: hidden;
    }
    .event-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
    }
    .event-img-container {
        position: relative;
        overflow: hidden;
        height: 220px;
    }
    .event-img-container img {
        transition: transform 0.5s ease;
        object-fit: cover;
        width: 100%;
        height: 100%;
    }
    .event-card:hover .event-img-container img {
        transform: scale(1.08);
    }
    .badge-category {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 6px 14px;
        font-weight: 600;
        font-size: 0.8rem;
        border-radius: 30px;
        z-index: 10;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .badge-outreach {
        background-color: #FCE8E6;
        color: #EA4335;
    }
    .badge-mentorship {
        background-color: #E8F0FE;
        color: #1A73E8;
    }
    .badge-community {
        background-color: #E6F4EA;
        color: #137333;
    }
    .filter-btn {
        border: 2px solid transparent;
        transition: all 0.3s ease;
        font-weight: 500;
    }
    .filter-btn.active {
        background-color: var(--primary) !important;
        color: white !important;
        border-color: var(--primary) !important;
    }
    .filter-btn:hover:not(.active) {
        border-color: var(--primary);
        color: var(--primary);
    }
</style>

<body>
    
    <?php include 'includes/navbar.php'; ?> 

    <!-- Page Header Start -->
    <div class="container-fluid page-header mb-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container text-center">
            <h1 class="display-4 text-white animated slideInDown mb-4">Upcoming Events</h1>
            <nav aria-label="breadcrumb animated slideInDown">
                <ol class="breadcrumb justify-content-center mb-0">
                    <li class="breadcrumb-item"><a class="text-white" href="./">Home</a></li>
                    <li class="breadcrumb-item text-primary active" aria-current="page">Events</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- Page Header End -->

    <!-- Events Content Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <?php if ($reg_success): ?>
                <!-- Registration Success Message -->
                <div class="row justify-content-center">
                    <div class="col-lg-8 text-center wow fadeIn" data-wow-delay="0.1s">
                        <div class="bg-white p-5 rounded shadow border-top border-5 border-primary">
                            <div class="d-inline-flex align-items-center justify-content-center text-primary rounded-circle mb-4" style="width: 80px; height: 80px; background-color: var(--secondary);">
                                <i class="fa fa-calendar-check fa-2x"></i>
                            </div>
                            <h2 class="mb-3 text-dark">Registration Confirmed!</h2>
                            <p class="fs-5 text-muted mb-4">Thank you, <strong><?php echo htmlspecialchars($name); ?></strong>. You have registered successfully for <strong><?php echo htmlspecialchars($event_title); ?></strong>.</p>
                            <p class="text-muted">A confirmation update has been sent to <strong><?php echo htmlspecialchars($email); ?></strong>. We will keep you posted with the event link and reminders.</p>
                            <a href="events" class="btn btn-primary py-3 px-5 mt-2">
                                Back to Events
                                <div class="d-inline-flex btn-sm-square bg-white text-primary rounded-circle ms-2">
                                    <i class="fa fa-arrow-right"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Event Listing & Filter -->
                <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
                    <div class="d-inline-block rounded-pill bg-secondary text-primary py-1 px-3 mb-3">Community Gatherings</div>
                    <h1 class="display-6 mb-4">Join Our Fellowship & Outreach Activities</h1>
                    <p class="text-muted">Get involved in our global hangouts, virtual meetups, and youth mentorship platforms to deepen your faith walk and connect with other believers.</p>
                </div>

                <?php if ($reg_error): ?>
                    <div class="row justify-content-center">
                        <div class="col-lg-10 mb-4">
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fa fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($reg_error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Category Filters -->
                <div class="row justify-content-center mb-5 wow fadeInUp" data-wow-delay="0.2s">
                    <div class="col-auto">
                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            <button class="btn btn-light rounded-pill filter-btn active px-4 py-2" onclick="filterEvents('all')">All Events</button>
                            <button class="btn btn-light rounded-pill filter-btn px-4 py-2" onclick="filterEvents('Outreach')">Outreach</button>
                            <button class="btn btn-light rounded-pill filter-btn px-4 py-2" onclick="filterEvents('Mentorship')">Mentorship</button>
                            <button class="btn btn-light rounded-pill filter-btn px-4 py-2" onclick="filterEvents('Community')">Community</button>
                        </div>
                    </div>
                </div>

                <!-- Events Grid -->
                <div class="row g-4 justify-content-center" id="eventsGrid">
                    <?php foreach ($events as $event): ?>
                        <?php 
                            $date_obj = new DateTime($event['event_date']);
                            $formatted_date = $date_obj->format('M d, Y');
                            $formatted_time = $date_obj->format('h:i A');
                            $category_class = 'badge-community';
                            if (strtolower($event['category']) == 'outreach') {
                                $category_class = 'badge-outreach';
                            } elseif (strtolower($event['category']) == 'mentorship') {
                                $category_class = 'badge-mentorship';
                            }
                        ?>
                        <div class="col-lg-4 col-md-6 event-item-card wow fadeInUp" data-wow-delay="0.1s" data-category="<?php echo htmlspecialchars($event['category']); ?>">
                            <div class="card event-card bg-white h-100 shadow-sm rounded">
                                <div class="event-img-container">
                                    <span class="badge-category <?php echo $category_class; ?>">
                                        <?php echo htmlspecialchars($event['category']); ?>
                                    </span>
                                    <img src="<?php echo htmlspecialchars($event['image_path']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
                                </div>
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="d-flex flex-wrap gap-3 mb-3 text-muted small">
                                        <span><i class="fa fa-calendar-alt text-primary me-2"></i><?php echo $formatted_date; ?></span>
                                        <span><i class="fa fa-clock text-primary me-2"></i><?php echo $formatted_time; ?></span>
                                    </div>
                                    <h4 class="card-title mb-3 text-dark"><?php echo htmlspecialchars($event['title']); ?></h4>
                                    <p class="card-text text-muted mb-4 flex-grow-1"><?php echo htmlspecialchars($event['description']); ?></p>
                                    <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-auto">
                                        <span class="small text-muted"><i class="fa fa-map-marker-alt text-primary me-2"></i><?php echo htmlspecialchars($event['location']); ?></span>
                                        <button type="button" class="btn btn-outline-primary rounded-pill px-3 py-2 btn-sm" onclick="openRegisterModal(<?php echo $event['id']; ?>, '<?php echo htmlspecialchars($event['title'], ENT_QUOTES); ?>')">
                                            Register
                                            <i class="fa fa-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Events Content End -->

    <!-- Event Registration Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-primary text-white border-0 py-3" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                    <h5 class="modal-title text-white" id="registerModalLabel"><i class="fa fa-edit me-2"></i>Event Registration</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="events" method="POST">
                    <div class="modal-body p-4">
                        <input type="hidden" name="register_event" value="1">
                        <input type="hidden" name="event_id" id="modal_event_id">
                        
                        <div class="mb-3">
                            <label class="form-label text-dark small" style="font-weight: 600;">Selected Event</label>
                            <input type="text" class="form-control bg-light border-0" id="modal_event_title" readonly style="font-weight: 600; color: var(--primary);">
                        </div>
                        <div class="mb-3">
                            <div class="form-floating">
                                <input type="text" name="name" class="form-control bg-light border-0" id="reg_name" placeholder="Your Name" required>
                                <label for="reg_name">Your Name</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-floating">
                                <input type="email" name="email" class="form-control bg-light border-0" id="reg_email" placeholder="Your Email" required>
                                <label for="reg_email">Your Email Address</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-floating">
                                <input type="tel" name="phone" class="form-control bg-light border-0" id="reg_phone" placeholder="Phone Number">
                                <label for="reg_phone">Phone Number (Optional)</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-floating">
                                <textarea name="message" class="form-control bg-light border-0" placeholder="Notes / Expectations" id="reg_message" style="height: 80px"></textarea>
                                <label for="reg_message">Notes / Expectations (Optional)</label>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-floating">
                                <input type="number" name="captcha" class="form-control bg-light border-0" id="reg_captcha" placeholder="Verification" required>
                                <label for="reg_captcha">Human Verification: Solve <?php echo get_captcha_question('event_reg'); ?> =</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light p-3" style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            Register Now
                            <i class="fa fa-paper-plane ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php';?>

    <!-- Category Filter script and Modal loader -->
    <script>
        function filterEvents(category) {
            // Update active state on buttons
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');

            // Filter cards
            const cards = document.querySelectorAll('.event-item-card');
            cards.forEach(card => {
                if (category === 'all' || card.getAttribute('data-category') === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function openRegisterModal(id, title) {
            document.getElementById('modal_event_id').value = id;
            document.getElementById('modal_event_title').value = title;
            var regModal = new bootstrap.Modal(document.getElementById('registerModal'));
            regModal.show();
        }
    </script>
</body>
</html>

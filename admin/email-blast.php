<?php
include 'includes/head.php';
include 'includes/sidebar.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_group = $_POST['recipients'] ?? '';
    $custom_emails = trim($_POST['custom_emails'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');
    
    if (empty($recipient_group) || empty($subject) || empty($body)) {
        $error_message = "All fields except custom emails list are required.";
    } else {
        $recipients_list = [];
        
        if ($recipient_group === 'custom') {
            // Split custom emails by comma, space or newline
            $raw_emails = preg_split('/[\s,;]+/', $custom_emails);
            foreach ($raw_emails as $email) {
                $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
                if ($email) {
                    $recipients_list[] = $email;
                }
            }
            if (empty($recipients_list)) {
                $error_message = "Please enter at least one valid custom email address.";
            }
        } elseif (isset($pdo)) {
            try {
                if ($recipient_group === 'subscribers') {
                    $stmt = $pdo->query("SELECT email FROM newsletter_subscribers");
                    $recipients_list = $stmt->fetchAll(PDO::FETCH_COLUMN);
                } elseif ($recipient_group === 'registrations') {
                    $stmt = $pdo->query("SELECT DISTINCT email FROM registrations");
                    $recipients_list = $stmt->fetchAll(PDO::FETCH_COLUMN);
                } elseif ($recipient_group === 'mentorship') {
                    $stmt = $pdo->query("SELECT DISTINCT email FROM camp_christos_applications");
                    $recipients_list = $stmt->fetchAll(PDO::FETCH_COLUMN);
                } elseif ($recipient_group === 'bookings') {
                    $stmt = $pdo->query("SELECT DISTINCT email FROM event_registrations");
                    $recipients_list = $stmt->fetchAll(PDO::FETCH_COLUMN);
                }
            } catch (\PDOException $e) {
                $error_message = "Failed to query database for recipients: " . $e->getMessage();
            }
        }
        
        // Remove duplicates and empty entries
        $recipients_list = array_filter(array_unique($recipients_list));
        
        if (empty($error_message) && empty($recipients_list)) {
            $error_message = "No recipients found in the selected group.";
        }
        
        if (empty($error_message)) {
            $sent_count = 0;
            $fail_count = 0;
            
            // Build visual HTML wrapper for emails
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
                        " . $body . "
                    </div>
                    <div class='footer'>
                        <p>You received this email because you are registered with Christverse.</p>
                        <p>&copy; " . date('Y') . " Christverse Community, Abuja, Nigeria. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            foreach ($recipients_list as $to_email) {
                if (send_email($to_email, $subject, $html_message)) {
                    $sent_count++;
                } else {
                    $fail_count++;
                }
            }
            
            if ($fail_count === 0) {
                $success_message = "Successfully broadcasted email to <strong>{$sent_count}</strong> recipient(s)!";
            } else {
                $success_message = "Broadcast completed: <strong>{$sent_count}</strong> sent successfully, <strong>{$fail_count}</strong> failed.";
            }
        }
    }
}
?>

<!-- Form Interface -->
<?php if (!empty($success_message)): ?>
<div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4 rounded-3" role="alert">
    <i class="fas fa-check-circle me-2"></i> <?php echo $success_message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
<div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4 rounded-3" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($error_message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Email Blast Editor Form -->
    <div class="col-xl-8 col-lg-7">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="admin-card-title"><i class="fas fa-paper-plane me-2 text-primary"></i>Broadcast HTML Email</h6>
            </div>
            
            <div class="admin-card-body">
                <form action="email-blast" method="POST" id="emailForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-dark small fw-semibold">Recipient Group</label>
                            <select name="recipients" id="recipients_select" class="form-select" onchange="toggleCustomEmailsField(this.value)" required>
                                <option value="" disabled selected>Select Target Audience</option>
                                <option value="subscribers">All Newsletter Subscribers</option>
                                <option value="registrations">All Onboarding Registrations</option>
                                <option value="mentorship">All Camp Christos Applicants</option>
                                <option value="bookings">All Event Registrants</option>
                                <option value="custom">-- Custom Email List --</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-dark small fw-semibold">Email Subject</label>
                            <input type="text" name="subject" id="email_subject" class="form-control" placeholder="e.g. Welcome to the Christverse Family" required>
                        </div>
                        
                        <div class="col-12" id="custom_emails_container" style="display: none;">
                            <label class="form-label text-dark small fw-semibold">Custom Email Addresses (comma separated)</label>
                            <textarea name="custom_emails" id="custom_emails" class="form-control" rows="2" placeholder="e.g. john@example.com, sarah@example.com"></textarea>
                            <span class="text-muted small">Input target email addresses separated by commas.</span>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label text-dark small fw-semibold">HTML Content Body</label>
                            <textarea name="body" id="email_body" class="form-control" rows="12" placeholder="Write your HTML content or select a preset template on the right..."></textarea>
                            <span class="text-muted small">You can use standard HTML tags (e.g. <code>&lt;p&gt;</code>, <code>&lt;strong&gt;</code>, <code>&lt;h3&gt;</code>, <code>&lt;a&gt;</code>) to format the body.</span>
                        </div>
                        
                        <div class="col-12 text-end pt-3">
                            <button type="submit" class="btn btn-primary rounded-pill px-5">
                                Send Broadcast <i class="fas fa-paper-plane ms-2"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Presets / Quick Layout Templates -->
    <div class="col-xl-4 col-lg-5">
        <div class="admin-card">
            <div class="admin-card-header bg-light">
                <h6 class="admin-card-title text-dark"><i class="fas fa-palette me-2 text-info"></i>HTML Template Presets</h6>
            </div>
            
            <div class="admin-card-body">
                <p class="small text-muted mb-4">Click a preset below to prefill the email subject and HTML body editor with premium templates:</p>
                
                <div class="d-grid gap-3">
                    <button class="btn btn-outline-primary text-start py-3 px-3 rounded-3" onclick="applyPreset('welcome')">
                        <div class="fw-bold"><i class="fas fa-handshake me-2"></i> Welcome & Greetings</div>
                        <div class="small text-muted mt-1">General introduction and onboarding welcome template.</div>
                    </button>
                    
                    <button class="btn btn-outline-success text-start py-3 px-3 rounded-3" onclick="applyPreset('event')">
                        <div class="fw-bold"><i class="fas fa-calendar me-2"></i> Event Invitation</div>
                        <div class="small text-muted mt-1">Promote upcoming outdoor hangouts or Zoom sessions.</div>
                    </button>
                    
                    <button class="btn btn-outline-info text-start py-3 px-3 rounded-3" onclick="applyPreset('announcement')">
                        <div class="fw-bold"><i class="fas fa-bullhorn me-2"></i> Community Announcement</div>
                        <div class="small text-muted mt-1">A clean header/paragraph template for core updates.</div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .ck-editor__editable {
        min-height: 320px;
        color: #2b303a !important;
    }
</style>
<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>

<script>
function toggleCustomEmailsField(value) {
    const container = document.getElementById('custom_emails_container');
    const input = document.getElementById('custom_emails');
    if (value === 'custom') {
        container.style.display = 'block';
        input.required = true;
        input.focus();
    } else {
        container.style.display = 'none';
        input.required = false;
    }
}

const presets = {
    welcome: {
        subject: "Welcome to Christverse Community!",
        body: `<h3>Welcome to the Family!</h3>
<p>We are absolutely thrilled to welcome you to the Christverse global community. You are joining an army of narrative-changing believers committed to spreads the gospel of Christ and growing spiritually.</p>
<p>Here are a few next steps to get started:</p>
<ul>
    <li><strong>Explore initiatives</strong>: Meet with our leadership teams on iCreate, TBT podcast, Colors, and Freeform.</li>
    <li><strong>Engage in Fellowship</strong>: Attend our virtual and physical monthly meetups to build camaraderie.</li>
    <li><strong>Declare the Word</strong>: Declare life daily through faith affirmations.</li>
</ul>
<p>If you registered to join a specific department, our talent managers will get in touch with you shortly.</p>
<p style="margin-top: 30px; text-align: center;">
    <a href="https://christverse.org" style="background-color: #CE9B2E; color: white; padding: 12px 25px; text-decoration: none; border-radius: 30px; font-weight: bold; display: inline-block;">Explore the Community</a>
</p>
<p>In Christ,</p>
<p><strong>Nathaniel Thomas Yosi</strong><br>Global Team Lead, Christverse</p>`
    },
    event: {
        subject: "Join Us for Our Next Colors Hangout & Fellowship!",
        body: `<h3>You're Invited! 📢</h3>
<p>Hi Beloved,</p>
<p>We are excited to invite you to our upcoming community meetup! This gathering will be a structured time of bonding, team games, faith confessions, and sharing the gospel.</p>
<div style="background-color: #f8f9fa; border-left: 4px solid #CE9B2E; padding: 15px; margin: 20px 0; border-radius: 4px;">
    <strong>📅 Date:</strong> Saturday, July 11, 2026<br>
    <strong>🕒 Time:</strong> 4:00 PM GMT+1<br>
    <strong>📍 Location:</strong> Abuja Central Park & Online (Zoom/YouTube)
</div>
<p>Whether you're joining us physical in Abuja or virtually from anywhere in the world, there's a seat saved for you. Come prepared to connect and speak life.</p>
<p style="margin-top: 30px; text-align: center;">
    <a href="https://christverse.org/events" style="background-color: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 30px; font-weight: bold; display: inline-block;">Register to Attend</a>
</p>
<p>Warm regards,</p>
<p><strong>The Colors Team</strong><br>Christverse Community</p>`
    },
    announcement: {
        subject: "Important Community Update from Christverse",
        body: `<h3>Beloved Community,</h3>
<p>We are writing to share some exciting developments within Christverse globally. In line with our mission of reaching 1 billion people in 10,000 cities, we are expanding our initiatives in creative spheres and youth development.</p>
<p>Please take note of these key updates:</p>
<p><strong>1. Devotional Launch:</strong> The pilot release of the Harmony Daily Devotional is slated for next month. It will feature structural declarations to deepen your faith walk.</p>
<p><strong>2. Mentorship Expansion:</strong> Camp Christos is opening new cohorts for direct mentorship with narrative-changing rulers across various spheres.</p>
<p>We appreciate your continued partnership, prayers, and declarations of faith as we take the gospel to the ends of the earth.</p>
<p>Blessings,</p>
<p><strong>Adebisi Covenant</strong><br>Director of Technology, Christverse</p>`
    }
};

let editorInstance;

document.addEventListener("DOMContentLoaded", function() {
    ClassicEditor
        .create(document.querySelector('#email_body'), {
            toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo' ]
        })
        .then(editor => {
            editorInstance = editor;
        })
        .catch(error => {
            console.error(error);
        });

    // Custom submit-time validation since the native textarea is hidden and cannot be focusable/required
    const form = document.getElementById('emailForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            let bodyContent = '';
            if (editorInstance) {
                bodyContent = editorInstance.getData().trim();
            } else {
                bodyContent = document.getElementById('email_body').value.trim();
            }

            if (!bodyContent) {
                e.preventDefault();
                alert('Please enter the HTML content body for the email broadcast.');
                if (editorInstance) {
                    editorInstance.editing.view.focus();
                } else {
                    document.getElementById('email_body').focus();
                }
            }
        });
    }
});

function applyPreset(type) {
    if (presets[type]) {
        if (confirm("Prefill the subject and HTML editor with the '" + type + "' preset template? This will replace your current text.")) {
            document.getElementById('email_subject').value = presets[type].subject;
            if (editorInstance) {
                editorInstance.setData(presets[type].body);
            } else {
                document.getElementById('email_body').value = presets[type].body;
            }
        }
    }
}
</script>

<?php
include 'includes/footer.php';
?>

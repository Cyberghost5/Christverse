<?php
include 'includes/head.php';
include 'includes/sidebar.php';

$success_message = '';
$error_message = '';

// Handle POST actions (Create and Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $event_date_raw = $_POST['event_date'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $existing_image_path = trim($_POST['existing_image_path'] ?? '');
    
    $image_path = $existing_image_path; // Default to existing banner if editing
    
    // Handle File Upload
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['event_image']['tmp_name'];
        $fileName = $_FILES['event_image']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Generate a random hashed name to prevent naming collisions
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = __DIR__ . '/../img/';
            
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            
            $dest_path = $uploadFileDir . $newFileName;
            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $image_path = 'img/' . $newFileName;
            } else {
                $error_message = 'Failed to move uploaded banner to image folder.';
            }
        } else {
            $error_message = 'Upload failed. Allowed extensions: ' . implode(', ', $allowedfileExtensions);
        }
    }
    
    // Convert datetime-local input to MySQL DATETIME format
    $event_date = '';
    if (!empty($event_date_raw)) {
        $event_date = date('Y-m-d H:i:s', strtotime($event_date_raw));
    }
    
    if (empty($title) || empty($category) || empty($event_date) || empty($location) || empty($description)) {
        $error_message = "All fields except new image banner are required.";
    } elseif (empty($image_path) && $action === 'create') {
        $error_message = "Event banner image is required.";
    } elseif (empty($error_message)) {
        if (isset($pdo)) {
            try {
                if ($action === 'create') {
                    // Create new event
                    $stmt = $pdo->prepare("INSERT INTO events (title, description, category, event_date, location, image_path) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $description, $category, $event_date, $location, $image_path]);
                    $success_message = "Event created successfully!";
                } elseif ($action === 'edit') {
                    // Edit existing event
                    $id = intval($_POST['id'] ?? 0);
                    $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, category = ?, event_date = ?, location = ?, image_path = ? WHERE id = ?");
                    $stmt->execute([$title, $description, $category, $event_date, $location, $image_path, $id]);
                    $success_message = "Event updated successfully!";
                }
            } catch (\PDOException $e) {
                $error_message = "Database Error: " . $e->getMessage();
            }
        }
    }
}

// Handle GET actions (Delete)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);
    if (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
            $stmt->execute([$delete_id]);
            $success_message = "Event deleted successfully!";
        } catch (\PDOException $e) {
            $error_message = "Database Error: " . $e->getMessage();
        }
    }
}

// Fetch all events
$events = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
        $events = $stmt->fetchAll();
    } catch (\PDOException $e) {
        $error_message = "Failed to load events: " . $e->getMessage();
    }
}
?>

<!-- Alerts -->
<?php if (!empty($success_message)): ?>
<div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4 rounded-3" role="alert">
    <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($success_message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
<div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4 rounded-3" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($error_message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <h6 class="admin-card-title"><i class="fas fa-calendar-alt me-2 text-primary"></i>Community Events</h6>
        <button class="btn btn-sm btn-primary rounded-pill px-3 d-flex align-items-center gap-2" onclick="openAddModal()">
            <i class="fas fa-plus"></i> Add New Event
        </button>
    </div>
    
    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom align-middle">
                <thead>
                    <tr>
                        <th style="width: 80px;">Image</th>
                        <th>Event Title</th>
                        <th>Category</th>
                        <th>Date & Time</th>
                        <th>Location</th>
                        <th class="text-end" style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($events)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-calendar-times fa-3x mb-3 text-light"></i>
                                <p class="m-0">No events found. Click "Add New Event" to create one.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($events as $event): 
                            $date_obj = new DateTime($event['event_date']);
                            $formatted_date = $date_obj->format('M d, Y - h:i A');
                            
                            $badge_class = 'bg-secondary text-white';
                            if (strtolower($event['category']) == 'outreach') {
                                $badge_class = 'bg-danger text-white';
                            } elseif (strtolower($event['category']) == 'mentorship') {
                                $badge_class = 'bg-primary text-white';
                            } elseif (strtolower($event['category']) == 'community') {
                                $badge_class = 'bg-success text-white';
                            }
                        ?>
                            <tr>
                                <td>
                                    <img src="../<?php echo htmlspecialchars($event['image_path']); ?>" 
                                         alt="Event Thumbnail" 
                                         class="rounded shadow-sm"
                                         style="width: 60px; height: 45px; object-fit: cover;"
                                         onerror="this.src='../img/carousel-1.jpg'">
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?php echo htmlspecialchars($event['title']); ?></div>
                                    <div class="text-muted small text-truncate" style="max-width: 280px;"><?php echo htmlspecialchars($event['description']); ?></div>
                                </td>
                                <td>
                                    <span class="badge badge-dept <?php echo $badge_class; ?>">
                                        <?php echo htmlspecialchars($event['category']); ?>
                                    </span>
                                </td>
                                <td class="small fw-medium text-dark"><?php echo $formatted_date; ?></td>
                                <td class="small text-muted"><i class="fas fa-map-marker-alt me-1 text-primary"></i><?php echo htmlspecialchars($event['location']); ?></td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Edit button -->
                                        <button class="btn btn-action btn-light border" title="Edit Event"
                                                onclick="openEditModal(<?php echo htmlspecialchars(json_encode($event)); ?>)">
                                            <i class="fas fa-edit text-primary"></i>
                                        </button>
                                        <!-- Delete button -->
                                        <button class="btn btn-action btn-light border text-danger" title="Delete Event"
                                                onclick="confirmDelete(<?php echo $event['id']; ?>)">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Event Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-primary text-white border-0 py-3" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="modal-title text-white" id="eventModalLabel">Event Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="events" method="POST" id="eventForm" enctype="multipart/form-data">
                <input type="hidden" name="action" id="form_action" value="create">
                <input type="hidden" name="id" id="event_id" value="">
                <input type="hidden" name="existing_image_path" id="existing_image_path" value="">
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-dark small fw-semibold">Event Title</label>
                            <input type="text" name="title" id="event_title" class="form-control" placeholder="e.g. Prayer & Power Night" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-dark small fw-semibold">Category</label>
                            <select name="category" id="event_category" class="form-select" required>
                                <option value="">Select Category</option>
                                <option value="Outreach">Outreach</option>
                                <option value="Mentorship">Mentorship</option>
                                <option value="Community">Community</option>
                                <option value="Fellowship">Fellowship</option>
                                <option value="Special Initiative">Special Initiative</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-dark small fw-semibold">Event Date & Time</label>
                            <input type="datetime-local" name="event_date" id="event_date" class="form-control" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-dark small fw-semibold">Location / Venue</label>
                            <input type="text" name="location" id="event_location" class="form-control" placeholder="e.g. Zoom / Abuja Central Park" required>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label text-dark small fw-semibold">Event Banner Image</label>
                            <input type="file" name="event_image" id="event_image" class="form-control" accept="image/*">
                            <span class="text-muted small" id="image_help_text">Select a JPG, JPEG, PNG, GIF, or WEBP image to upload.</span>
                            
                            <div id="image_preview_container" class="mt-3 d-flex align-items-center gap-3" style="display: none !important;">
                                <div class="text-muted small fw-semibold">Current Banner:</div>
                                <img id="image_preview" src="" class="rounded border shadow-sm" style="max-height: 70px; width: auto; object-fit: cover;">
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label text-dark small fw-semibold">Event Description</label>
                            <textarea name="description" id="event_description" class="form-control" rows="4" placeholder="Describe what the event is about, activities, objectives, etc." required></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 bg-light p-3" style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" id="submit_btn">Save Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let eventModal;

document.addEventListener("DOMContentLoaded", function() {
    eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
    
    // Check if redirect parameters request opening modal
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'new') {
        openAddModal();
    }
});

function openAddModal() {
    document.getElementById('eventForm').reset();
    document.getElementById('form_action').value = 'create';
    document.getElementById('event_id').value = '';
    document.getElementById('existing_image_path').value = '';
    document.getElementById('eventModalLabel').innerText = "Create New Event";
    document.getElementById('submit_btn').innerText = "Create Event";
    
    // Reset file input requirements and preview
    document.getElementById('event_image').required = true;
    const previewContainer = document.getElementById('image_preview_container');
    previewContainer.setAttribute('style', 'display: none !important;');
    document.getElementById('image_preview').src = '';
    
    eventModal.show();
}

function openEditModal(eventData) {
    document.getElementById('eventForm').reset();
    document.getElementById('form_action').value = 'edit';
    document.getElementById('event_id').value = eventData.id;
    document.getElementById('eventModalLabel').innerText = "Edit Event Details";
    document.getElementById('submit_btn').innerText = "Update Event";
    
    document.getElementById('event_title').value = eventData.title;
    document.getElementById('event_category').value = eventData.category;
    document.getElementById('event_location').value = eventData.location;
    document.getElementById('event_description').value = eventData.description;
    
    // Format date from YYYY-MM-DD HH:MM:SS to YYYY-MM-DDTHH:MM
    const dateObj = new Date(eventData.event_date);
    const tzOffset = dateObj.getTimezoneOffset() * 60000; // offset in milliseconds
    const localISOTime = (new Date(dateObj.getTime() - tzOffset)).toISOString().slice(0, 16);
    document.getElementById('event_date').value = localISOTime;
    
    // Set existing image values
    document.getElementById('event_image').required = false;
    document.getElementById('existing_image_path').value = eventData.image_path;
    
    // Show image preview
    const preview = document.getElementById('image_preview');
    preview.src = '../' + eventData.image_path;
    const previewContainer = document.getElementById('image_preview_container');
    previewContainer.setAttribute('style', 'display: flex !important;');
    
    eventModal.show();
}

function confirmDelete(id) {
    if (confirm("Are you sure you want to delete this event? This action will also delete all registrations associated with it.")) {
        window.location.href = "events?action=delete&id=" + id;
    }
}
</script>

<?php
include 'includes/footer.php';
?>

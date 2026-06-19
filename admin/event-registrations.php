<?php
include 'includes/head.php';
include 'includes/sidebar.php';

$delete_success = false;
$filter_event = $_GET['event_id'] ?? '';
$search_query = trim($_GET['search'] ?? '');

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);
    if (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("DELETE FROM event_registrations WHERE id = ?");
            $stmt->execute([$delete_id]);
            header("Location: event-registrations?deleted=1" . ($filter_event ? "&event_id=" . urlencode($filter_event) : "") . ($search_query ? "&search=" . urlencode($search_query) : ""));
            header("Location: event-registrations");
            exit;
        } catch (\PDOException $e) {
            $error_message = "Failed to delete booking: " . $e->getMessage();
        }
    }
}

if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    $delete_success = true;
}

// Fetch all events for the filter dropdown
$all_events = [];
if (isset($pdo)) {
    try {
        $all_events = $pdo->query("SELECT id, title FROM events ORDER BY event_date DESC")->fetchAll();
    } catch (\PDOException $e) {
        // Suppress or handle error
    }
}

// Build query
$params = [];
$sql = "SELECT er.*, e.title as event_title 
        FROM event_registrations er 
        LEFT JOIN events e ON er.event_id = e.id 
        WHERE 1=1";

if (!empty($filter_event)) {
    $sql .= " AND er.event_id = ?";
    $params[] = intval($filter_event);
}

if (!empty($search_query)) {
    $sql .= " AND (er.name LIKE ? OR er.email LIKE ? OR er.phone LIKE ? OR er.message LIKE ?)";
    $like_search = "%$search_query%";
    $params[] = $like_search;
    $params[] = $like_search;
    $params[] = $like_search;
    $params[] = $like_search;
}

$sql .= " ORDER BY er.created_at DESC";

$bookings = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $bookings = $stmt->fetchAll();
    } catch (\PDOException $e) {
        $error_message = "Failed to query bookings: " . $e->getMessage();
    }
}
?>

<!-- Alerts -->
<?php if ($delete_success): ?>
<div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4 rounded-3" role="alert">
    <i class="fas fa-check-circle me-2"></i> Event registration deleted successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
<div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4 rounded-3" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($error_message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header flex-column flex-md-row align-items-stretch align-items-md-center gap-3">
        <h6 class="admin-card-title m-0"><i class="fas fa-ticket-alt me-2 text-primary"></i>Event Bookings</h6>
        
        <!-- Search and Filter Form -->
        <form action="event-registrations" method="GET" class="d-flex flex-wrap gap-2 align-items-center ms-auto">
            <div class="input-group input-group-sm" style="width: 240px;">
                <input type="text" name="search" class="form-control border-end-0" placeholder="Search registrant details..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button class="btn btn-outline-secondary border-start-0" type="submit">
                    <i class="fas fa-search text-muted"></i>
                </button>
            </div>
            
            <select name="event_id" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 200px;">
                <option value="">All Events</option>
                <?php foreach ($all_events as $ev): ?>
                    <option value="<?php echo $ev['id']; ?>" <?php echo ($filter_event == $ev['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($ev['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <?php if (!empty($filter_event) || !empty($search_query)): ?>
                <a href="event-registrations" class="btn btn-sm btn-light border rounded-pill px-3">Clear Filters</a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom align-middle">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Name</th>
                        <th>Email Address</th>
                        <th>Phone Number</th>
                        <th>Registered Event</th>
                        <th>Booking Date</th>
                        <th class="text-end" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-ticket-alt fa-3x mb-3 text-light"></i>
                                <p class="m-0">No event registrations found matching criteria.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $counter = 1;
                        foreach ($bookings as $book): 
                        ?>
                            <tr>
                                <td class="text-muted small"><?php echo $counter++; ?></td>
                                <td class="fw-semibold text-dark"><?php echo htmlspecialchars($book['name']); ?></td>
                                <td><a href="mailto:<?php echo htmlspecialchars($book['email']); ?>" class="text-decoration-none"><?php echo htmlspecialchars($book['email']); ?></a></td>
                                <td class="text-muted"><?php echo !empty($book['phone']) ? htmlspecialchars($book['phone']) : '<span class="text-light">-</span>'; ?></td>
                                <td>
                                    <span class="fw-semibold" style="color: var(--primary);">
                                        <?php echo htmlspecialchars($book['event_title'] ?? 'Deleted Event'); ?>
                                    </span>
                                </td>
                                <td class="small text-muted"><?php echo date('M d, Y h:i A', strtotime($book['created_at'])); ?></td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Details Button -->
                                        <button class="btn btn-action btn-light border" title="View details"
                                                onclick="showDetails(<?php echo htmlspecialchars(json_encode($book)); ?>)">
                                            <i class="fas fa-eye text-primary"></i>
                                        </button>
                                        <!-- Delete Button -->
                                        <button class="btn btn-action btn-light border text-danger" title="Delete booking"
                                                onclick="confirmDelete(<?php echo $book['id']; ?>)">
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

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-primary text-white border-0 py-3" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="modal-title text-white" id="detailsModalLabel"><i class="fas fa-ticket-alt me-2"></i>Booking Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="text-muted small fw-semibold text-uppercase">Registrant Name</label>
                    <div id="modal-name" class="fw-bold fs-5 text-dark"></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <label class="text-muted small fw-semibold text-uppercase">Email Address</label>
                        <div><a id="modal-email" href="" class="text-decoration-none fw-semibold"></a></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small fw-semibold text-uppercase">Phone Number</label>
                        <div id="modal-phone" class="fw-semibold text-dark"></div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <label class="text-muted small fw-semibold text-uppercase">Registered Event</label>
                        <div id="modal-event" class="fw-semibold text-primary"></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small fw-semibold text-uppercase">Registration Date</label>
                        <div id="modal-date" class="text-dark"></div>
                    </div>
                </div>
                
                <div class="mb-0">
                    <label class="text-muted small fw-semibold text-uppercase">Notes / Expectations</label>
                    <div class="p-3 bg-light rounded-3 text-dark mt-1" style="font-size: 0.92rem; min-height: 80px; white-space: pre-wrap;" id="modal-message"></div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light p-3" style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function showDetails(data) {
    document.getElementById('modal-name').innerText = data.name;
    document.getElementById('modal-email').innerText = data.email;
    document.getElementById('modal-email').href = "mailto:" + data.email;
    document.getElementById('modal-phone').innerText = data.phone ? data.phone : "Not provided";
    document.getElementById('modal-event').innerText = data.event_title ? data.event_title : "Deleted Event";
    
    // Format date nicely
    const dateObj = new Date(data.created_at);
    document.getElementById('modal-date').innerText = dateObj.toLocaleString('en-US', { 
        weekday: 'short', 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit' 
    });
    
    document.getElementById('modal-message').innerText = data.message ? data.message : "No notes or expectations provided.";
    
    var myModal = new bootstrap.Modal(document.getElementById('detailsModal'));
    myModal.show();
}

function confirmDelete(id) {
    if (confirm("Are you sure you want to permanently delete this event booking?")) {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('action', 'delete');
        currentUrl.searchParams.set('id', id);
        window.location.href = currentUrl.toString();
    }
}
</script>

<?php
include 'includes/footer.php';
?>

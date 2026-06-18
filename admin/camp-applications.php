<?php
include 'includes/head.php';
include 'includes/sidebar.php';

$delete_success = false;
$search_query = trim($_GET['search'] ?? '');

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);
    if (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("DELETE FROM camp_christos_applications WHERE id = ?");
            $stmt->execute([$delete_id]);
            header("Location: camp-applications?deleted=1" . ($search_query ? "&search=" . urlencode($search_query) : ""));
            exit;
        } catch (\PDOException $e) {
            $error_message = "Failed to delete application: " . $e->getMessage();
        }
    }
}

if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    $delete_success = true;
}

// Build query
$params = [];
$sql = "SELECT * FROM camp_christos_applications WHERE 1=1";

if (!empty($search_query)) {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ? OR motivation LIKE ?)";
    $like_search = "%$search_query%";
    $params[] = $like_search;
    $params[] = $like_search;
    $params[] = $like_search;
    $params[] = $like_search;
}

$sql .= " ORDER BY created_at DESC";

$applications = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $applications = $stmt->fetchAll();
    } catch (\PDOException $e) {
        $error_message = "Failed to query applications: " . $e->getMessage();
    }
}
?>

<!-- Alerts -->
<?php if ($delete_success): ?>
<div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4 rounded-3" role="alert">
    <i class="fas fa-check-circle me-2"></i> Application deleted successfully.
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
        <h6 class="admin-card-title m-0"><i class="fas fa-user-graduate me-2 text-primary"></i>Camp Christos Applications</h6>
        
        <!-- Search -->
        <div class="d-flex flex-wrap gap-2 align-items-center ms-auto">
            <form action="camp-applications" method="GET" class="d-flex gap-2">
                <div class="input-group input-group-sm" style="width: 260px;">
                    <input type="text" name="search" class="form-control border-end-0" placeholder="Search applications..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button class="btn btn-outline-secondary border-start-0" type="submit">
                        <i class="fas fa-search text-muted"></i>
                    </button>
                </div>
                <?php if (!empty($search_query)): ?>
                    <a href="camp-applications" class="btn btn-sm btn-light border rounded-pill px-3">Clear</a>
                <?php endif; ?>
            </form>
        </div>
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
                        <th>Application Date</th>
                        <th class="text-end" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applications)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-graduation-cap fa-3x mb-3 text-light"></i>
                                <p class="m-0">No applications found.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $counter = 1;
                        foreach ($applications as $app): 
                        ?>
                            <tr>
                                <td class="text-muted small"><?php echo $counter++; ?></td>
                                <td class="fw-semibold text-dark"><?php echo htmlspecialchars($app['name']); ?></td>
                                <td><a href="mailto:<?php echo htmlspecialchars($app['email']); ?>" class="text-decoration-none"><?php echo htmlspecialchars($app['email']); ?></a></td>
                                <td class="text-muted"><?php echo !empty($app['phone']) ? htmlspecialchars($app['phone']) : '<span class="text-light">-</span>'; ?></td>
                                <td class="small text-muted"><?php echo date('M d, Y h:i A', strtotime($app['created_at'])); ?></td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Details Button -->
                                        <button class="btn btn-action btn-light border" title="View details"
                                                onclick="showDetails(<?php echo htmlspecialchars(json_encode($app)); ?>)">
                                            <i class="fas fa-eye text-primary"></i>
                                        </button>
                                        <!-- Delete Button -->
                                        <button class="btn btn-action btn-light border text-danger" title="Delete application"
                                                onclick="confirmDelete(<?php echo $app['id']; ?>)">
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
                <h5 class="modal-title text-white" id="detailsModalLabel"><i class="fas fa-graduation-cap me-2"></i>Application Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="text-muted small fw-semibold text-uppercase">Applicant Name</label>
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
                
                <div class="mb-3">
                    <label class="text-muted small fw-semibold text-uppercase">Application Date</label>
                    <div id="modal-date" class="text-dark"></div>
                </div>
                
                <div class="mb-0">
                    <label class="text-muted small fw-semibold text-uppercase">Motivation Statement</label>
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
    
    document.getElementById('modal-message').innerText = data.motivation ? data.motivation : "No motivation statement provided.";
    
    var myModal = new bootstrap.Modal(document.getElementById('detailsModal'));
    myModal.show();
}

function confirmDelete(id) {
    if (confirm("Are you sure you want to permanently delete this application?")) {
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

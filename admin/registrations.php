<?php
include 'includes/head.php';
include 'includes/sidebar.php';

$delete_success = false;
$filter_dept = $_GET['department'] ?? '';
$search_query = trim($_GET['search'] ?? '');

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);
    if (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("DELETE FROM registrations WHERE id = ?");
            $stmt->execute([$delete_id]);
            // header("Location: registrations?deleted=1" . ($filter_dept ? "&department=" . urlencode($filter_dept) : "") . ($search_query ? "&search=" . urlencode($search_query) : ""));
            header("Location: registrations");
            exit;
        } catch (\PDOException $e) {
            $error_message = "Failed to delete registration: " . $e->getMessage();
        }
    }
}

if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    $delete_success = true;
}

// Build query
$params = [];
$sql = "SELECT * FROM registrations WHERE 1=1";

if (!empty($filter_dept)) {
    $sql .= " AND department = ?";
    $params[] = $filter_dept;
}

if (!empty($search_query)) {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR message LIKE ?)";
    $like_search = "%$search_query%";
    $params[] = $like_search;
    $params[] = $like_search;
    $params[] = $like_search;
}

$sql .= " ORDER BY created_at DESC";

$registrations = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $registrations = $stmt->fetchAll();
    } catch (\PDOException $e) {
        $error_message = "Failed to query registrations: " . $e->getMessage();
    }
}
?>

<!-- Alerts -->
<?php if ($delete_success): ?>
<div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4 rounded-3" role="alert">
    <i class="fas fa-check-circle me-2"></i> Registration deleted successfully.
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
        <h6 class="admin-card-title m-0"><i class="fas fa-users me-2 text-primary"></i>Onboarding Applications</h6>
        
        <!-- Search and Filter Form -->
        <form action="registrations.php" method="GET" class="d-flex flex-wrap gap-2 align-items-center ms-auto">
            <div class="input-group input-group-sm" style="width: 240px;">
                <input type="text" name="search" class="form-control border-end-0" placeholder="Search by name/email..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button class="btn btn-outline-secondary border-start-0" type="submit">
                    <i class="fas fa-search text-muted"></i>
                </button>
            </div>
            
            <select name="department" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 180px;">
                <option value="">All Departments</option>
                <option value="iCreate" <?php echo ($filter_dept === 'iCreate') ? 'selected' : ''; ?>>iCreate (Creatives)</option>
                <option value="TBT Podcast" <?php echo ($filter_dept === 'TBT Podcast') ? 'selected' : ''; ?>>TBT Podcast</option>
                <option value="Colors" <?php echo ($filter_dept === 'Colors') ? 'selected' : ''; ?>>Colors (Hangouts)</option>
                <option value="Freeform" <?php echo ($filter_dept === 'Freeform') ? 'selected' : ''; ?>>Freeform (Meetings)</option>
            </select>
            
            <?php if (!empty($filter_dept) || !empty($search_query)): ?>
                <a href="registrations.php" class="btn btn-sm btn-light border rounded-pill px-3">Clear Filters</a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email Address</th>
                        <th>Interest Area</th>
                        <th>Submission Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($registrations)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-folder-open fa-3x mb-3 text-light"></i>
                                <p class="m-0">No onboarding applications found matching criteria.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $counter = 1;
                        foreach ($registrations as $reg): 
                            $badge_class = 'bg-secondary';
                            if ($reg['department'] === 'iCreate') $badge_class = 'bg-primary text-white';
                            elseif ($reg['department'] === 'TBT Podcast') $badge_class = 'bg-success text-white';
                            elseif ($reg['department'] === 'Colors') $badge_class = 'bg-warning text-dark';
                            elseif ($reg['department'] === 'Freeform') $badge_class = 'bg-info text-white';
                        ?>
                            <tr>
                                <td class="text-muted small"><?php echo $counter++; ?></td>
                                <td class="fw-semibold text-dark"><?php echo htmlspecialchars($reg['name']); ?></td>
                                <td><a href="mailto:<?php echo htmlspecialchars($reg['email']); ?>" class="text-decoration-none"><?php echo htmlspecialchars($reg['email']); ?></a></td>
                                <td>
                                    <span class="badge badge-dept <?php echo $badge_class; ?>">
                                        <?php echo htmlspecialchars($reg['department']); ?>
                                    </span>
                                </td>
                                <td class="small text-muted"><?php echo date('M d, Y h:i A', strtotime($reg['created_at'])); ?></td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Details Button -->
                                        <button class="btn btn-action btn-light border" title="View details" 
                                                onclick="showDetails(<?php echo htmlspecialchars(json_encode($reg)); ?>)">
                                            <i class="fas fa-eye text-primary"></i>
                                        </button>
                                        <!-- Delete Button -->
                                        <button class="btn btn-action btn-light border text-danger" title="Delete application"
                                                onclick="confirmDelete(<?php echo $reg['id']; ?>)">
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
                <h5 class="modal-title text-white" id="detailsModalLabel"><i class="fas fa-user-circle me-2"></i>Applicant Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="text-muted small fw-semibold text-uppercase">Full Name</label>
                    <div id="modal-name" class="fw-bold fs-5 text-dark"></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <label class="text-muted small fw-semibold text-uppercase">Email Address</label>
                        <div><a id="modal-email" href="" class="text-decoration-none fw-semibold"></a></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small fw-semibold text-uppercase">Interest Area</label>
                        <div><span id="modal-dept" class="badge badge-dept"></span></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="text-muted small fw-semibold text-uppercase">Application Date</label>
                    <div id="modal-date" class="text-dark"></div>
                </div>
                
                <div class="mb-0">
                    <label class="text-muted small fw-semibold text-uppercase">Personal Statement / Message</label>
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
    
    const deptBadge = document.getElementById('modal-dept');
    deptBadge.innerText = data.department;
    // Set badge classes based on dept
    deptBadge.className = "badge badge-dept";
    if (data.department === 'iCreate') deptBadge.classList.add('bg-primary', 'text-white');
    else if (data.department === 'TBT Podcast') deptBadge.classList.add('bg-success', 'text-white');
    else if (data.department === 'Colors') deptBadge.classList.add('bg-warning', 'text-dark');
    else if (data.department === 'Freeform') deptBadge.classList.add('bg-info', 'text-white');
    else deptBadge.classList.add('bg-secondary');

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
    
    document.getElementById('modal-message').innerText = data.message ? data.message : "No message provided.";
    
    var myModal = new bootstrap.Modal(document.getElementById('detailsModal'));
    myModal.show();
}

function confirmDelete(id) {
    if (confirm("Are you sure you want to permanently delete this onboarding application?")) {
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

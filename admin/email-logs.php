<?php
include 'includes/head.php';
include 'includes/sidebar.php';

$delete_success = false;
$clear_success = false;
$search_query = trim($_GET['search'] ?? '');
$filter_status = $_GET['status'] ?? '';
$filter_method = $_GET['method'] ?? '';

// Handle delete individual log action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);
    if (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("DELETE FROM email_logs WHERE id = ?");
            $stmt->execute([$delete_id]);
            // header("Location: email-logs?deleted=1" . ($filter_status ? "&status=" . urlencode($filter_status) : "") . ($filter_method ? "&method=" . urlencode($filter_method) : "") . ($search_query ? "&search=" . urlencode($search_query) : ""));
            header("Location: email-logs");
            exit;
        } catch (\PDOException $e) {
            $error_message = "Failed to delete log entry: " . $e->getMessage();
        }
    }
}

// Handle clear all logs action
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    if (isset($pdo)) {
        try {
            $pdo->exec("TRUNCATE TABLE email_logs");
            header("Location: email-logs?cleared=1");
            exit;
        } catch (\PDOException $e) {
            $error_message = "Failed to clear email logs: " . $e->getMessage();
        }
    }
}

if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    $delete_success = true;
}
if (isset($_GET['cleared']) && $_GET['cleared'] == 1) {
    $clear_success = true;
}

// Build query
$params = [];
$sql = "SELECT * FROM email_logs WHERE 1=1";

if (!empty($search_query)) {
    $sql .= " AND (recipient LIKE ? OR subject LIKE ? OR error_message LIKE ?)";
    $like_search = "%$search_query%";
    $params[] = $like_search;
    $params[] = $like_search;
    $params[] = $like_search;
}

if (!empty($filter_status)) {
    $sql .= " AND status = ?";
    $params[] = $filter_status;
}

if (!empty($filter_method)) {
    $sql .= " AND method LIKE ?";
    $params[] = "%$filter_method%";
}

$sql .= " ORDER BY created_at DESC";

$logs = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();
    } catch (\PDOException $e) {
        $error_message = "Failed to query email logs: " . $e->getMessage();
    }
}
?>

<!-- Alerts -->
<?php if ($delete_success): ?>
<div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4 rounded-3" role="alert">
    <i class="fas fa-check-circle me-2"></i> Log entry deleted successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if ($clear_success): ?>
<div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4 rounded-3" role="alert">
    <i class="fas fa-check-circle me-2"></i> All email diagnostic logs cleared successfully.
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
    <div class="admin-card-header flex-column flex-xxl-row align-items-stretch align-items-xxl-center gap-3">
        <div class="d-flex align-items-center">
            <h6 class="admin-card-title m-0"><i class="fas fa-history me-2 text-primary"></i>Email Delivery & Diagnostic Logs</h6>
        </div>
        
        <!-- Search and Filter Form -->
        <form action="email-logs" method="GET" class="d-flex flex-wrap gap-2 align-items-center ms-auto">
            <div class="input-group input-group-sm" style="width: 220px;">
                <input type="text" name="search" class="form-control border-end-0" placeholder="Search recipient/subject..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button class="btn btn-outline-secondary border-start-0" type="submit">
                    <i class="fas fa-search text-muted"></i>
                </button>
            </div>
            
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 130px;">
                <option value="">All Statuses</option>
                <option value="success" <?php echo ($filter_status === 'success') ? 'selected' : ''; ?>>Success</option>
                <option value="failed" <?php echo ($filter_status === 'failed') ? 'selected' : ''; ?>>Failed</option>
            </select>

            <select name="method" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 140px;">
                <option value="">All Methods</option>
                <option value="SMTP" <?php echo ($filter_method === 'SMTP') ? 'selected' : ''; ?>>SMTP Only</option>
                <option value="PHP mail" <?php echo ($filter_method === 'PHP mail') ? 'selected' : ''; ?>>PHP mail() Only</option>
            </select>
            
            <?php if (!empty($filter_status) || !empty($filter_method) || !empty($search_query)): ?>
                <a href="email-logs" class="btn btn-sm btn-light border rounded-pill px-3">Clear</a>
            <?php endif; ?>
            
            <?php if (!empty($logs)): ?>
                <button type="button" onclick="confirmClearAll()" class="btn btn-sm btn-danger rounded-pill px-3 ms-xxl-2">
                    <i class="fas fa-trash-sweep me-1"></i> Clear All Logs
                </button>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom align-middle">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Recipient</th>
                        <th>Subject</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date & Time</th>
                        <th class="text-end" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-history fa-3x mb-3 text-light"></i>
                                <p class="m-0">No email diagnostic logs found.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $counter = 1;
                        foreach ($logs as $log): 
                            $status_class = ($log['status'] === 'success') ? 'bg-success text-white' : 'bg-danger text-white';
                        ?>
                            <tr>
                                <td class="text-muted small"><?php echo $counter++; ?></td>
                                <td class="fw-semibold text-dark">
                                    <a href="mailto:<?php echo htmlspecialchars($log['recipient']); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($log['recipient']); ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="text-dark d-inline-block text-truncate" style="max-width: 250px;" title="<?php echo htmlspecialchars($log['subject']); ?>">
                                        <?php echo htmlspecialchars($log['subject']); ?>
                                    </span>
                                </td>
                                <td class="small text-muted"><?php echo htmlspecialchars($log['method']); ?></td>
                                <td>
                                    <span class="badge rounded-pill <?php echo $status_class; ?> px-3 py-1">
                                        <?php echo ucfirst($log['status']); ?>
                                    </span>
                                </td>
                                <td class="small text-muted"><?php echo date('M d, Y h:i A', strtotime($log['created_at'])); ?></td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Details Button -->
                                        <button class="btn btn-action btn-light border" title="View Details / Diagnostic Info"
                                                onclick="showLogDetails(<?php echo htmlspecialchars(json_encode($log)); ?>)">
                                            <i class="fas fa-info-circle text-primary"></i>
                                        </button>
                                        <!-- Delete Button -->
                                        <button class="btn btn-action btn-light border text-danger" title="Delete Log"
                                                onclick="confirmDelete(<?php echo $log['id']; ?>)">
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

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" aria-labelledby="logDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-primary text-white border-0 py-3" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="modal-title text-white" id="logDetailsModalLabel"><i class="fas fa-info-circle me-2"></i>Email Delivery Diagnostics</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="text-muted small fw-semibold text-uppercase">Recipient Address</label>
                    <div id="modal-recipient" class="fw-bold fs-6 text-dark"></div>
                </div>
                
                <div class="mb-3">
                    <label class="text-muted small fw-semibold text-uppercase">Email Subject</label>
                    <div id="modal-subject" class="text-dark fw-medium"></div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-6">
                        <label class="text-muted small fw-semibold text-uppercase">Sending Method</label>
                        <div id="modal-method" class="text-dark fw-bold"></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small fw-semibold text-uppercase">Delivery Status</label>
                        <div><span id="modal-status" class="badge rounded-pill"></span></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="text-muted small fw-semibold text-uppercase">Timestamp</label>
                    <div id="modal-date" class="text-dark small"></div>
                </div>
                
                <div id="modal-error-container" class="mb-0" style="display: none;">
                    <label class="text-danger small fw-semibold text-uppercase"><i class="fas fa-exclamation-circle me-1"></i>Diagnostic Error Report</label>
                    <div class="p-3 bg-danger-subtle text-danger border border-danger-subtle rounded-3 mt-1" style="font-size: 0.9rem; font-family: monospace; white-space: pre-wrap;" id="modal-error-message"></div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light p-3" style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function showLogDetails(data) {
    document.getElementById('modal-recipient').innerText = data.recipient;
    document.getElementById('modal-subject').innerText = data.subject;
    document.getElementById('modal-method').innerText = data.method;
    
    const statusBadge = document.getElementById('modal-status');
    statusBadge.innerText = data.status.toUpperCase();
    statusBadge.className = "badge rounded-pill px-3 py-1";
    if (data.status === 'success') {
        statusBadge.classList.add('bg-success', 'text-white');
    } else {
        statusBadge.classList.add('bg-danger', 'text-white');
    }

    const dateObj = new Date(data.created_at);
    document.getElementById('modal-date').innerText = dateObj.toLocaleString('en-US', { 
        weekday: 'short', 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit',
        second: '2-digit'
    });

    const errorContainer = document.getElementById('modal-error-container');
    const errorMessage = document.getElementById('modal-error-message');
    if (data.status === 'failed' && data.error_message) {
        errorMessage.innerText = data.error_message;
        errorContainer.style.display = 'block';
    } else {
        errorContainer.style.display = 'none';
    }
    
    var myModal = new bootstrap.Modal(document.getElementById('logDetailsModal'));
    myModal.show();
}

function confirmDelete(id) {
    if (confirm("Are you sure you want to delete this log entry?")) {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('action', 'delete');
        currentUrl.searchParams.set('id', id);
        window.location.href = currentUrl.toString();
    }
}

function confirmClearAll() {
    if (confirm("WARNING: Are you sure you want to permanently clear all email diagnostic logs? This action cannot be undone.")) {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('action', 'clear');
        window.location.href = currentUrl.toString();
    }
}
</script>

<?php
include 'includes/footer.php';
?>

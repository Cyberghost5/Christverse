<?php
// Handle CSV export at the very top before any HTML output
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    require_once 'auth.php';
    require_admin_login();
    require_once '../includes/db.php';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=christverse_subscribers_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    // Header columns
    fputcsv($output, ['ID', 'Email Address', 'Subscription Date']);
    
    if (isset($pdo)) {
        try {
            $stmt = $pdo->query("SELECT id, email, created_at FROM newsletter_subscribers ORDER BY created_at DESC");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                fputcsv($output, $row);
            }
        } catch (\PDOException $e) {
            // Suppress error in CSV output or append as row
            fputcsv($output, ['ERROR', 'Failed to retrieve subscribers data', '']);
        }
    }
    fclose($output);
    exit;
}

include 'includes/head.php';
include 'includes/sidebar.php';

$delete_success = false;
$search_query = trim($_GET['search'] ?? '');

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);
    if (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("DELETE FROM newsletter_subscribers WHERE id = ?");
            $stmt->execute([$delete_id]);
            // header("Location: subscribers?deleted=1" . ($search_query ? "&search=" . urlencode($search_query) : ""));
            header("Location: subscribers");
            exit;
        } catch (\PDOException $e) {
            $error_message = "Failed to delete subscriber: " . $e->getMessage();
        }
    }
}

if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    $delete_success = true;
}

// Build query
$params = [];
$sql = "SELECT * FROM newsletter_subscribers WHERE 1=1";

if (!empty($search_query)) {
    $sql .= " AND email LIKE ?";
    $params[] = "%$search_query%";
}

$sql .= " ORDER BY created_at DESC";

$subscribers = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $subscribers = $stmt->fetchAll();
    } catch (\PDOException $e) {
        $error_message = "Failed to query subscribers: " . $e->getMessage();
    }
}
?>

<!-- Alerts -->
<?php if ($delete_success): ?>
<div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4 rounded-3" role="alert">
    <i class="fas fa-check-circle me-2"></i> Subscriber deleted successfully.
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
        <h6 class="admin-card-title m-0"><i class="fas fa-envelope-open-text me-2 text-primary"></i>Newsletter Subscribers</h6>
        
        <!-- Search and Actions -->
        <div class="d-flex flex-wrap gap-2 align-items-center ms-auto">
            <form action="subscribers" method="GET" class="d-flex gap-2">
                <div class="input-group input-group-sm" style="width: 260px;">
                    <input type="text" name="search" class="form-control border-end-0" placeholder="Search by email..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button class="btn btn-outline-secondary border-start-0" type="submit">
                        <i class="fas fa-search text-muted"></i>
                    </button>
                </div>
                <?php if (!empty($search_query)): ?>
                    <a href="subscribers" class="btn btn-sm btn-light border rounded-pill px-3">Clear</a>
                <?php endif; ?>
            </form>
            
            <a href="subscribers?export=csv" class="btn btn-sm btn-success rounded-pill px-3 d-flex align-items-center gap-2">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>
        </div>
    </div>
    
    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom align-middle">
                <thead>
                    <tr>
                        <th style="width: 80px;">#</th>
                        <th>Email Address</th>
                        <th>Subscription Date</th>
                        <th class="text-end" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($subscribers)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                <i class="fas fa-envelope-open fa-3x mb-3 text-light"></i>
                                <p class="m-0">No newsletter subscribers found.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $counter = 1;
                        foreach ($subscribers as $sub): 
                        ?>
                            <tr>
                                <td class="text-muted small"><?php echo $counter++; ?></td>
                                <td class="fw-semibold text-dark">
                                    <a href="mailto:<?php echo htmlspecialchars($sub['email']); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($sub['email']); ?>
                                    </a>
                                </td>
                                <td class="small text-muted"><?php echo date('M d, Y h:i A', strtotime($sub['created_at'])); ?></td>
                                <td class="text-end">
                                    <!-- Delete Button -->
                                    <button class="btn btn-action btn-light border text-danger" title="Unsubscribe / Delete"
                                            onclick="confirmDelete(<?php echo $sub['id']; ?>)">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm("Are you sure you want to remove this subscriber from the mailing list?")) {
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

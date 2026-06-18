<?php
include 'includes/head.php';
include 'includes/sidebar.php';

// Fetch Statistics
$registrations_count = 0;
$subscribers_count = 0;
$mentorship_count = 0;
$events_count = 0;
$event_regs_count = 0;

if (isset($pdo)) {
    try {
        $registrations_count = $pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
        $subscribers_count = $pdo->query("SELECT COUNT(*) FROM newsletter_subscribers")->fetchColumn();
        $mentorship_count = $pdo->query("SELECT COUNT(*) FROM camp_christos_applications")->fetchColumn();
        $events_count = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
        $event_regs_count = $pdo->query("SELECT COUNT(*) FROM event_registrations")->fetchColumn();
        
        // Fetch registrations by department for visual bars
        $dept_stmt = $pdo->query("SELECT department, COUNT(*) as count FROM registrations GROUP BY department");
        $dept_counts = [];
        while ($row = $dept_stmt->fetch()) {
            if (!empty($row['department'])) {
                $dept_counts[$row['department']] = $row['count'];
            }
        }
        
        // Fetch recent registrations
        $recent_regs_stmt = $pdo->query("SELECT * FROM registrations ORDER BY created_at DESC LIMIT 5");
        $recent_regs = $recent_regs_stmt->fetchAll();
        
        // Fetch recent subscribers
        $recent_subs_stmt = $pdo->query("SELECT * FROM newsletter_subscribers ORDER BY created_at DESC LIMIT 5");
        $recent_subs = $recent_subs_stmt->fetchAll();
        
    } catch (\PDOException $e) {
        $db_error = "Database Error: " . $e->getMessage();
    }
}

// Default departments list to ensure all are accounted for
$all_depts = ['iCreate' => 0, 'TBT Podcast' => 0, 'Colors' => 0, 'Freeform' => 0];
foreach ($all_depts as $dept => $val) {
    if (isset($dept_counts[$dept])) {
        $all_depts[$dept] = $dept_counts[$dept];
    }
}
?>

<!-- Database Offline Warning -->
<?php if (isset($db_error)): ?>
<div class="alert alert-danger shadow-sm border-0 mb-4 rounded-3 d-flex align-items-center" role="alert">
    <i class="fas fa-exclamation-triangle fa-lg me-3"></i>
    <div>
        <strong>Database Connection Error!</strong> <?php echo htmlspecialchars($db_error); ?>
    </div>
</div>
<?php endif; ?>

<!-- Statistics Cards Row -->
<div class="row g-4 mb-4">
    <!-- Total Onboardings -->
    <div class="col-md-4 col-sm-6">
        <div class="stat-card">
            <div>
                <div class="stat-value"><?php echo $registrations_count; ?></div>
                <div class="stat-label">Registrations</div>
            </div>
            <div class="stat-icon" style="background-color: #E8F0FE; color: #1A73E8;">
                <i class="fas fa-user-plus"></i>
            </div>
        </div>
    </div>
    
    <!-- Newsletter Subscribers -->
    <div class="col-md-4 col-sm-6">
        <div class="stat-card">
            <div>
                <div class="stat-value"><?php echo $subscribers_count; ?></div>
                <div class="stat-label">Subscribers</div>
            </div>
            <div class="stat-icon" style="background-color: #E6F4EA; color: #137333;">
                <i class="fas fa-envelope-open-text"></i>
            </div>
        </div>
    </div>

    <!-- Mentorship Applications -->
    <div class="col-md-4 col-sm-6">
        <div class="stat-card">
            <div>
                <div class="stat-value"><?php echo $mentorship_count; ?></div>
                <div class="stat-label">Camp Christos</div>
            </div>
            <div class="stat-icon" style="background-color: #E1BEE7; color: #8E24AA;">
                <i class="fas fa-graduation-cap"></i>
            </div>
        </div>
    </div>
    
    <!-- Community Events -->
    <div class="col-md-4 col-sm-6">
        <div class="stat-card">
            <div>
                <div class="stat-value"><?php echo $events_count; ?></div>
                <div class="stat-label">Events</div>
            </div>
            <div class="stat-icon" style="background-color: #FEF7E0; color: #B06000;">
                <i class="fas fa-calendar-check"></i>
            </div>
        </div>
    </div>
    
    <!-- Event Bookings -->
    <div class="col-md-4 col-sm-6">
        <div class="stat-card">
            <div>
                <div class="stat-value"><?php echo $event_regs_count; ?></div>
                <div class="stat-label">Event Bookings</div>
            </div>
            <div class="stat-icon" style="background-color: #FCE8E6; color: #C5221F;">
                <i class="fas fa-ticket-alt"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Onboarding Department Distributions (Premium CSS Progress Bars) -->
    <div class="col-xl-4 col-lg-5">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <h6 class="admin-card-title"><i class="fas fa-chart-pie me-2 text-primary"></i>Onboarding by Initiative</h6>
            </div>
            <div class="admin-card-body">
                <p class="small text-muted mb-4">Total submissions per interest area:</p>
                <?php
                $max_dept_count = max(array_values($all_depts)) ?: 1;
                $colors = [
                    'iCreate' => 'bg-primary',
                    'TBT Podcast' => 'bg-success',
                    'Colors' => 'bg-warning',
                    'Freeform' => 'bg-info'
                ];
                foreach ($all_depts as $dept => $count):
                    $percentage = round(($count / $max_dept_count) * 100);
                    $color_class = $colors[$dept] ?? 'bg-secondary';
                ?>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold text-dark"><?php echo htmlspecialchars($dept); ?></span>
                            <span class="badge bg-light text-dark border small fw-bold"><?php echo $count; ?></span>
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 10px;">
                            <div class="progress-bar <?php echo $color_class; ?> rounded" role="progressbar" style="width: <?php echo $percentage; ?>%;" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Recent Registrations (Join Submissions) -->
    <div class="col-xl-8 col-lg-7">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <h6 class="admin-card-title"><i class="fas fa-users-cog me-2 text-success"></i>Recent Registrations</h6>
                <a href="registrations" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
            </div>
            <div class="admin-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Email</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_regs)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No recent applications found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_regs as $reg): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark"><?php echo htmlspecialchars($reg['name']); ?></div>
                                        </td>
                                        <td>
                                            <?php
                                            $badge_class = 'bg-secondary';
                                            if ($reg['department'] === 'iCreate') $badge_class = 'bg-primary text-white';
                                            elseif ($reg['department'] === 'TBT Podcast') $badge_class = 'bg-success text-white';
                                            elseif ($reg['department'] === 'Colors') $badge_class = 'bg-warning text-dark';
                                            elseif ($reg['department'] === 'Freeform') $badge_class = 'bg-info text-white';
                                            ?>
                                            <span class="badge badge-dept <?php echo $badge_class; ?>">
                                                <?php echo htmlspecialchars($reg['department']); ?>
                                            </span>
                                        </td>
                                        <td class="text-muted"><?php echo htmlspecialchars($reg['email']); ?></td>
                                        <td class="small text-muted"><?php echo date('M d, Y', strtotime($reg['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <!-- Recent Subscribers -->
    <div class="col-md-6">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="admin-card-title"><i class="fas fa-envelope-open-text me-2 text-warning"></i>Recent Newsletter Subscribers</h6>
                <a href="subscribers" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
            </div>
            <div class="admin-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th>Email Address</th>
                                <th>Subscription Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_subs)): ?>
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">No recent newsletter subscriptions.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_subs as $sub): ?>
                                    <tr>
                                        <td class="fw-semibold text-dark"><?php echo htmlspecialchars($sub['email']); ?></td>
                                        <td class="small text-muted"><?php echo date('M d, Y h:i A', strtotime($sub['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links & Actions -->
    <div class="col-md-6">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="admin-card-title"><i class="fas fa-bolt me-2 text-info"></i>Quick Admin Actions</h6>
            </div>
            <div class="admin-card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <a href="events?action=new" class="btn btn-outline-primary w-100 py-3 rounded-3 d-flex flex-column align-items-center gap-2">
                            <i class="fas fa-calendar-plus fa-2x"></i>
                            <span class="small fw-semibold">Create New Event</span>
                        </a>
                    </div>
                    <div class="col-sm-6">
                        <a href="subscribers?export=csv" class="btn btn-outline-success w-100 py-3 rounded-3 d-flex flex-column align-items-center gap-2">
                            <i class="fas fa-file-csv fa-2x"></i>
                            <span class="small fw-semibold">Export Subscriber List</span>
                        </a>
                    </div>
                    <div class="col-sm-6">
                        <a href="registrations" class="btn btn-outline-info w-100 py-3 rounded-3 d-flex flex-column align-items-center gap-2">
                            <i class="fas fa-user-check fa-2x"></i>
                            <span class="small fw-semibold">Review Onboardings</span>
                        </a>
                    </div>
                    <div class="col-sm-6">
                        <a href="camp-applications" class="btn btn-outline-warning w-100 py-3 rounded-3 d-flex flex-column align-items-center gap-2">
                            <i class="fas fa-graduation-cap fa-2x"></i>
                            <span class="small fw-semibold">Review Camp Christos App.</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>

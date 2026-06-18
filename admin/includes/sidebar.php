        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <div class="sidebar-brand">
                <a href="index">
                    <img src="../img/logos/Christverse Horizontal Thin.png" alt="Christverse Logo" onerror="this.src='../img/logos/Christverse.png'">
                </a>
            </div>
            
            <div class="sidebar-heading">Core Dashboard</div>
            <div class="list-group list-group-flush">
                <a href="index" class="list-group-item-sidebar <?php echo ($current_page === 'index.php') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i> Overview
                </a>
            </div>

            <div class="sidebar-heading">Members & Growth</div>
            <div class="list-group list-group-flush">
                <a href="registrations" class="list-group-item-sidebar <?php echo ($current_page === 'registrations.php') ? 'active' : ''; ?>">
                    <i class="fas fa-users-cog"></i> Registrations
                </a>
                <a href="subscribers" class="list-group-item-sidebar <?php echo ($current_page === 'subscribers.php') ? 'active' : ''; ?>">
                    <i class="fas fa-envelope-open-text"></i> Subscribers
                </a>
                <a href="camp-applications" class="list-group-item-sidebar <?php echo ($current_page === 'camp-applications.php') ? 'active' : ''; ?>">
                    <i class="fas fa-graduation-cap"></i> Camp Christos
                </a>
                <a href="email-blast" class="list-group-item-sidebar <?php echo ($current_page === 'email-blast.php') ? 'active' : ''; ?>">
                    <i class="fas fa-paper-plane"></i> Email Broadcast
                </a>
                <a href="email-logs" class="list-group-item-sidebar <?php echo ($current_page === 'email-logs.php') ? 'active' : ''; ?>">
                    <i class="fas fa-history"></i> Email Logs
                </a>
            </div>

            <div class="sidebar-heading">Events & Gatherings</div>
            <div class="list-group list-group-flush">
                <a href="events" class="list-group-item-sidebar <?php echo ($current_page === 'events.php') ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt"></i> Manage Events
                </a>
                <a href="event-registrations" class="list-group-item-sidebar <?php echo ($current_page === 'event-registrations.php') ? 'active' : ''; ?>">
                    <i class="fas fa-ticket-alt"></i> Event Bookings
                </a>
            </div>

            <div class="sidebar-heading">Navigation</div>
            <div class="list-group list-group-flush">
                <a href="../" target="_blank" class="list-group-item-sidebar">
                    <i class="fas fa-external-link-alt"></i> Public Website
                </a>
                <a href="logout" class="list-group-item-sidebar text-danger">
                    <i class="fas fa-sign-out-alt"></i> Log Out
                </a>
            </div>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg admin-navbar d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <button class="btn btn-light rounded-circle shadow-sm border p-0 d-flex align-items-center justify-content-center me-3 d-lg-none" 
                            id="sidebarToggle" 
                            style="width: 40px; height: 40px;"
                            type="button">
                        <i class="fas fa-bars text-primary"></i>
                    </button>
                    <div>
                        <h5 class="m-0 font-weight-bold" style="font-family: 'Saira', sans-serif;">
                            <?php
                            if ($current_page === 'index.php') echo 'Dashboard Overview';
                            elseif ($current_page === 'registrations.php') echo 'Community Registrations';
                            elseif ($current_page === 'subscribers.php') echo 'Newsletter Subscribers';
                            elseif ($current_page === 'camp-applications.php') echo 'Camp Christos';
                            elseif ($current_page === 'email-blast.php') echo 'Email Broadcast Center';
                            elseif ($current_page === 'email-logs.php') echo 'Email Sending Logs';
                            elseif ($current_page === 'events.php') echo 'Events Management';
                            elseif ($current_page === 'event-registrations.php') echo 'Event Registrations';
                            ?>
                        </h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb m-0 mt-1" style="font-size: 0.8rem;">
                                <li class="breadcrumb-item"><a href="index">Admin</a></li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <?php
                                    if ($current_page === 'index.php') echo 'Overview';
                                    elseif ($current_page === 'registrations.php') echo 'Registrations';
                                    elseif ($current_page === 'subscribers.php') echo 'Subscribers';
                                    elseif ($current_page === 'camp-applications.php') echo 'Camp Christos';
                                    elseif ($current_page === 'email-blast.php') echo 'Email Broadcast';
                                    elseif ($current_page === 'email-logs.php') echo 'Email Logs';
                                    elseif ($current_page === 'events.php') echo 'Events';
                                    elseif ($current_page === 'event-registrations.php') echo 'Event Bookings';
                                    ?>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div class="text-end d-none d-sm-block">
                        <div class="small fw-semibold text-dark">Administrator</div>
                        <div class="text-muted small" style="font-size: 0.75rem;">Active Session</div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light rounded-circle shadow-sm border p-0 d-flex align-items-center justify-content-center" 
                                style="width: 40px; height: 40px;" 
                                type="button" 
                                id="adminUserDropdown" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <i class="fas fa-user-shield text-primary"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-2" aria-labelledby="adminUserDropdown" style="border-radius: 10px;">
                            <li><a class="dropdown-item py-2" href="../" target="_blank"><i class="fas fa-external-link-alt text-muted me-2"></i> Visit Site</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 text-danger" href="logout"><i class="fas fa-sign-out-alt me-2"></i> Log Out</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="container-fluid px-4 py-4">

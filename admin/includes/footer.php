            </div>
            <!-- /container-fluid -->
        </div>
        <!-- /#page-content-wrapper -->
    </div>
    <!-- /#wrapper -->

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Sidebar Toggle script for mobile/tablet responsive layouts
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('wrapper').classList.toggle('toggled');
            });
        }
        
        // Collapse sidebar when clicking main content area on mobile
        const pageContent = document.getElementById('page-content-wrapper');
        if (pageContent) {
            pageContent.addEventListener('click', function(e) {
                const wrapper = document.getElementById('wrapper');
                if (window.innerWidth <= 991.98 && wrapper.classList.contains('toggled') && !e.target.closest('#sidebarToggle')) {
                    wrapper.classList.remove('toggled');
                }
            });
        }
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learnify Admin Dashboard</title>
 
</head>
<body>
 <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Dashboard Content -->
            <div id="dashboard" class="content-area active">
                <div class="header">
                    <h2>Dashboard Overview</h2>
                    <div class="header-actions">
                        <select class="form-select">
                            <option>Last 30 days</option>
                            <option>Last 7 days</option>
                            <option>Last 90 days</option>
                        </select>
                        <button class="btn btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                            </svg>
                            Quick Action
                        </button>
                    </div>
                </div>

                                <?php
                    include '../Databaseconnection.php';

                    // Get counts for all user types
                    $userCountQuery = "SELECT COUNT(*) as total_users FROM users";
                    $studentCountQuery = "SELECT COUNT(*) as total_students FROM users WHERE role_id = 1";
                    $teacherCountQuery = "SELECT COUNT(*) as total_teachers FROM users WHERE role_id = 2";

                    $userCount = $conn->query($userCountQuery)->fetch_assoc()['total_users'];
                    $studentCount = $conn->query($studentCountQuery)->fetch_assoc()['total_students'];
                    $teacherCount = $conn->query($teacherCountQuery)->fetch_assoc()['total_teachers'];
                    ?>

                    <div class="stats-grid">
                        <!-- Total Users Card -->
                        <div class="stat-card">
                            <div class="stat-header">
                                <span class="stat-title">Total Users</span>
                                <div class="stat-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                                        <path d="M16 4c0-1.11.89-2 2-2s2 .89 2 2-.89 2-2 2-2-.89-2-2zM4 18v-4h3v4H4z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="stat-number"><?= number_format($userCount) ?></div>
                            
                        </div>

                        <!-- Total Students Card -->
                        <div class="stat-card">
                            <div class="stat-header">
                                <span class="stat-title">Total Students</span>
                                <div class="stat-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                                        <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="stat-number"><?= number_format($studentCount) ?></div>
                         
                        </div>

                        <!-- Total Teachers Card -->
                        <div class="stat-card">
                            <div class="stat-header">
                                <span class="stat-title">Total Teachers</span>
                                <div class="stat-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                                        <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="stat-number"><?= number_format($teacherCount) ?></div>
                        
                        </div>
                    </div>



                <div class="report-section">
                    <div class="report-header">
                        <h3 class="report-title">Generate Reports</h3>
                        <div class="report-controls">
                            <select class="form-select" id="reportType">
                                <option value="user-report">User Report</option>
                                <option value="course-report">Course Report</option>
                                <option value="payment-report">Payment Report</option>
                                <option value="performance-report">Performance Report</option>
                            </select>
                            <select class="form-select" id="reportPeriod">
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                            <button class="btn btn-primary" onclick="generateReport()">Generate Report</button>
                        </div>
                    </div>
                    <div id="reportContent">
                        <p style="text-align: center; color: #666; padding: 40px;">Select report type and period, then click "Generate Report" to view data.</p>
                    </div>
                </div>
            </div>

            

    <script>
        // Toggle submenu visibility
        function toggleSubmenu(link, submenuId) {
            const submenu = document.getElementById(submenuId);
            const icon = link.querySelector('svg:last-child');
            
            if (submenu.classList.contains('open')) {
                submenu.classList.remove('open');
                icon.style.transform = 'rotate(0deg)';
            } else {
                submenu.classList.add('open');
                icon.style.transform = 'rotate(180deg)';
            }
            
            // Close other submenus
            document.querySelectorAll('.submenu').forEach(menu => {
                if (menu.id !== submenuId && menu.classList.contains('open')) {
                    menu.classList.remove('open');
                    menu.previousElementSibling.querySelector('svg:last-child').style.transform = 'rotate(0deg)';
                }
            });
        }

        // Show content area based on clicked menu item
        function showContent(contentId) {
            document.querySelectorAll('.content-area').forEach(area => {
                area.classList.remove('active');
            });
            
            document.getElementById(contentId).classList.add('active');
            
            // Update active nav link
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Find the link that triggered this content and make it active
            const allLinks = document.querySelectorAll('.nav-link, .submenu-link');
            allLinks.forEach(link => {
                if (link.getAttribute('onclick') && link.getAttribute('onclick').includes(`'${contentId}'`)) {
                    link.classList.add('active');
                    
                    // If it's a submenu link, also make its parent nav-link active
                    if (link.classList.contains('submenu-link')) {
                        const parentNavLink = link.closest('.nav-item').querySelector('.nav-link');
                        parentNavLink.classList.add('active');
                    }
                }
            });
        }

function logout() {
    showConfirmation('Logout', 'Are you sure you want to logout?', () => {
        // In a real app, we would redirect to login page
        window.location.href = 'logout.php';
    });
}
    </script>
</body>
</html>
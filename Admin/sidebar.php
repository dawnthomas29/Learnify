<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learnify Admin Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            overflow-y: auto;
            box-shadow: 2px 0 20px rgba(0, 0, 0, 0.1);
        }

        .logo {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .logo h1 {
            color: #667eea;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .logo p {
            color: #666;
            font-size: 14px;
        }

        .nav-menu {
            padding: 20px 0;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: #555;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            cursor: pointer;
        }

        .nav-link:hover {
            background: linear-gradient(90deg, #667eea, #764ba2);
            color: white;
            transform: translateX(5px);
        }

        .nav-link.active {
            background: linear-gradient(90deg, #667eea, #764ba2);
            color: white;
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            margin-right: 15px;
            fill: currentColor;
        }

        .submenu {
            max-height: 0;
            overflow: hidden;
            background: rgba(102, 126, 234, 0.1);
            transition: all 0.3s ease;
        }

        .submenu.open {
            max-height: 300px;
        }

        .submenu-link {
            display: block;
            padding: 12px 50px;
            color: #666;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .submenu-link:hover {
            background: rgba(102, 126, 234, 0.2);
            color: #667eea;
            transform: translateX(5px);
        }

        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 0px;
            padding: 20px 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h2 {
            color: #333;
            font-size: 28px;
            font-weight: 600;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
           
        }

        .toggle-btn {
        padding: 12px 24px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 94px;  /* ✅ Keeps size consistent when toggling */
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-secondary {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            border: 1px solid rgba(102, 126, 234, 0.3);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .stat-title {
            color: #666;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }

        .report-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .report-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }

        .report-controls {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .form-select {
            padding: 12px 15px;
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 10px;
            background: white;
            color: #333;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .content-area {
            min-height: 400px;
            display: none;
        }

        .content-area.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .table th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 500;
        }

        .table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .table tr:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-blocked {
            background: #f8d7da;
            color: #721c24;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: fixed;
                top: 0;
                left: -100%;
                z-index: 999;
                transition: left 0.3s ease;
            }

            .sidebar.open {
                left: 0;
            }

            .main-content {
                width: 100%;
                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }

        
    </style>
</head>
<body>
    
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <h1>Learnify</h1>
                <p>Admin Dashboard</p>
            </div>
            
            <nav class="nav-menu">
                <div class="nav-item">
                    <a href="admin_dashboard.php" class="nav-link active" onclick="showContent('dashboard')">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                        </svg>
                        Dashboard
                    </a>
                </div>

                <div class="nav-item">
                    <a href="#" class="nav-link" onclick="toggleSubmenu(this, 'users-submenu')">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M16 4c0-1.11.89-2 2-2s2 .89 2 2-.89 2-2 2-2-.89-2-2zM4 18v-4h3v4H4zM4 12V8h3v4H4zM8 18v-4h3v4H8zM8 12V8h3v4H8zM12 18v-4h3v4h-3zM12 12V8h3v4h-3z"/>
                        </svg>
                        Manage Users
                        <svg class="nav-icon" style="margin-left: auto; transform: rotate(0deg); transition: transform 0.3s;" viewBox="0 0 24 24">
                            <path d="M7 10l5 5 5-5z"/>
                        </svg>
                    </a>
                    <div class="submenu" id="users-submenu">
                        <a href="Viewusers.php" class="submenu-link"> View Users</a>
                        <a href="Blockedusers.php" class="submenu-link" onclick="showContent('block-users')">Blocked Users</a>
                        
                    </div>
                </div>

                <div class="nav-item">
                    <a href="#" class="nav-link" onclick="toggleSubmenu(this, 'courses-submenu')">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
                        </svg>
                        Manage Courses
                        <svg class="nav-icon" style="margin-left: auto; transform: rotate(0deg); transition: transform 0.3s;" viewBox="0 0 24 24">
                            <path d="M7 10l5 5 5-5z"/>
                        </svg>
                    </a>
                    <div class="submenu" id="courses-submenu">
                        <a href="Addcourse.php" class="submenu-link" onclick="showContent('add-course')">Add Course</a>
                        <a href="Viewcourse.php" class="submenu-link" onclick="showContent('remove-course')">View Course</a>
                        <a href="Updatecourses.php" class="submenu-link" onclick="showContent('update-course')">Update Course</a>
                    </div>
                </div>

                <div class="nav-item">
                    <a href="#" class="nav-link" onclick="toggleSubmenu(this, 'payments-submenu')">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
                        </svg>
                        Manage Payments
                        <svg class="nav-icon" style="margin-left: auto; transform: rotate(0deg); transition: transform 0.3s;" viewBox="0 0 24 24">
                            <path d="M7 10l5 5 5-5z"/>
                        </svg>
                    </a>
                    <div class="submenu" id="payments-submenu">
                        <a href="#" class="submenu-link" onclick="showContent('approve-payment')">Approve Payment</a>
                        <a href="#" class="submenu-link" onclick="showContent('update-payment')">Update Payment</a>
                        <a href="#" class="submenu-link" onclick="showContent('view-payment')">View Payment</a>
                        <a href="#" class="submenu-link" onclick="showContent('add-payment')">Add Payment</a>
                    </div>
                </div>

                <div class="nav-item">
                    <a href="adminprofile.php" class="nav-link" onclick="Loadprofile()">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                        Admin Profile
                    </a>
                </div>

                <div class="nav-item">
                    <a href="#" class="nav-link" onclick="toggleSubmenu(this, 'settings-submenu')">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.74,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.8,11.69,4.8,12s0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.44-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/>
                        </svg>
                        Settings
                        <svg class="nav-icon" style="margin-left: auto; transform: rotate(0deg); transition: transform 0.3s;" viewBox="0 0 24 24">
                            <path d="M7 10l5 5 5-5z"/>
                        </svg>
                    </a>
                    <div class="submenu" id="settings-submenu">
                        <a href="#" class="submenu-link" onclick="showContent('system-settings')">System Settings</a>
                        <a href="#" class="submenu-link" onclick="showContent('email-settings')">Email Settings</a>
                        <a href="#" class="submenu-link" onclick="showContent('notification-settings')">Notifications</a>
                        <a href="#" class="submenu-link" onclick="showContent('backup-settings')">Backup & Restore</a>
                    </div>
                </div>

                <div class="nav-item">
                    <a href="../HomePage.php" class="nav-link" onclick="logout()">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                        </svg>
                        Logout
                    </a>
                </div>
            </nav>
        </aside>

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

        function Loadprofile() {
    showConfirmation(' ', () => {
        // In a real app, we would redirect to login page
        window.location.href = 'adminprofile.php';
    }); 
}
function logout() {
    showConfirmation('Logout', 'Are you sure you want to logout?', () => {
        // In a real app, we would redirect to login page
        window.location.href = '../HomePage.php';
    });
}

    </script>
</body>
</html>
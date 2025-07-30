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
            border-radius: 15px;
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
                    <a href="#" class="nav-link active" onclick="showContent('dashboard')">
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
                        <a href="#" class="submenu-link" onclick="showContent('add-course')">Add Course</a>
                        <a href="#" class="submenu-link" onclick="showContent('remove-course')">Remove Course</a>
                        <a href="#" class="submenu-link" onclick="showContent('update-course')">Update Course</a>
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
                    <a href="#" class="nav-link" onclick="showContent('admin-profile')">
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
                    <a href="#" class="nav-link" onclick="logout()">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                        </svg>
                        Logout
                    </a>
                </div>
            </nav>
        </aside>

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
                    include 'Databaseconnection.php';

                    // Get counts for all user types
                    $userCountQuery = "SELECT COUNT(*) as total_users FROM users";
                    $studentCountQuery = "SELECT COUNT(*) as total_students FROM users WHERE role_id = 3";
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

            <!-- User Management Content Areas -->
            <div id="add-users" class="content-area">
                <div class="header">
                   <h2>View users</h2>
                </div>   
            </div>
            <div id="block-users" class="content-area">
                <div class="header">
                    <h2>Blocked Users</h2>
                </div>
            </div>



            <!-- Course Management Content Areas -->
            <div id="add-course" class="content-area">
                <div class="header">
                    <h2>Add New Course</h2>
                </div>
                <div class="report-section">
                    <form onsubmit="addCourse(event)">
                        <div class="form-group">
                            <label class="form-label">Course Title</label>
                            <input type="text" class="form-input" name="courseTitle" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Course Description</label>
                            <textarea class="form-input" name="courseDescription" rows="4" required></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Instructor</label>
                            <select class="form-select" name="instructor" required>
                                <option value="">Select Instructor</option>
                                <option value="jane-smith">Jane Smith</option>
                                <option value="david-lee">David Lee</option>
                                <option value="sarah-wilson">Sarah Wilson</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category" required>
                                <option value="">Select Category</option>
                                <option value="programming">Programming</option>
                                <option value="design">Design</option>
                                <option value="business">Business</option>
                                <option value="marketing">Marketing</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Price (₹)</label>
                            <input type="number" class="form-input" name="price" min="0" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Duration (hours)</label>
                            <input type="number" class="form-input" name="duration" min="1" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Course</button>
                    </form>
                </div>
            </div>

            <div id="remove-course" class="content-area">
                <div class="header">
                    <h2>Remove Courses</h2>
                    <div class="header-actions">
                        <input type="text" placeholder="Search courses..." class="form-input" style="width: 250px;" onkeyup="filterUsers(this.value, 'removeCoursesTable')">
                    </div>
                </div>
                <div class="report-section">
                    <table class="table" id="removeCoursesTable">
                        <thead>
                            <tr>
                                <th>Course ID</th>
                                <th>Title</th>
                                <th>Instructor</th>
                                <th>Students</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>C001</td>
                                <td>JavaScript Fundamentals</td>
                                <td>Jane Smith</td>
                                <td>45</td>
                                <td>₹2,999</td>
                                <td><button class="btn" style="background: #dc3545; color: white;" onclick="removeCourse(this, 'C001')">Remove Course</button></td>
                            </tr>
                            <tr>
                                <td>C002</td>
                                <td>Web Design Basics</td>
                                <td>David Lee</td>
                                <td>32</td>
                                <td>₹1,999</td>
                                <td><button class="btn" style="background: #dc3545; color: white;" onclick="removeCourse(this, 'C002')">Remove Course</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="update-course" class="content-area">
                <div class="header">
                    <h2>Update Course</h2>
                    <div class="header-actions">
                        <select class="form-select" onchange="loadCourseData(this.value)" style="width: 250px;">
                            <option value="">Select Course to Update</option>
                            <option value="C001">JavaScript Fundamentals</option>
                            <option value="C002">Web Design Basics</option>
                            <option value="C003">React Development</option>
                        </select>
                    </div>
                </div>
                <div class="report-section">
                    <form onsubmit="updateCourse(event)" id="updateCourseForm">
                        <div class="form-group">
                            <label class="form-label">Course Title</label>
                            <input type="text" class="form-input" name="courseTitle" id="updateCourseTitle">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Course Description</label>
                            <textarea class="form-input" name="courseDescription" rows="4" id="updateCourseDescription"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Price (₹)</label>
                            <input type="number" class="form-input" name="price" min="0" id="updateCoursePrice">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Duration (hours)</label>
                            <input type="number" class="form-input" name="duration" min="1" id="updateCourseDuration">
                        </div>
                        <button type="submit" class="btn btn-primary" disabled id="updateCourseBtn">Update Course</button>
                    </form>
                </div>
            </div>

            <!-- Payment Management Content Areas -->
            <div id="approve-payment" class="content-area">
                <div class="header">
                    <h2>Approve Payments</h2>
                    <div class="header-actions">
                        <input type="text" placeholder="Search payments..." class="form-input" style="width: 250px;" onkeyup="filterUsers(this.value, 'approvePaymentsTable')">
                    </div>
                </div>
                <div class="report-section">
                    <table class="table" id="approvePaymentsTable">
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>P001</td>
                                <td>John Doe</td>
                                <td>JavaScript Fundamentals</td>
                                <td>₹2,999</td>
                                <td>2024-07-18</td>
                                <td><span class="status-badge status-pending">Pending</span></td>
                                <td>
                                    <button class="btn btn-primary" onclick="approvePayment(this, 'P001')">Approve</button>
                                    <button class="btn" style="background: #dc3545; color: white; margin-left: 5px;" onclick="rejectPayment(this, 'P001')">Reject</button>
                                </td>
                            </tr>
                            <tr>
                                <td>P002</td>
                                <td>Mike Johnson</td>
                                <td>Web Design Basics</td>
                                <td>₹1,999</td>
                                <td>2024-07-19</td>
                                <td><span class="status-badge status-pending">Pending</span></td>
                                <td>
                                    <button class="btn btn-primary" onclick="approvePayment(this, 'P002')">Approve</button>
                                    <button class="btn" style="background: #dc3545; color: white; margin-left: 5px;" onclick="rejectPayment(this, 'P002')">Reject</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="update-payment" class="content-area">
                <div class="header">
                    <h2>Update Payment</h2>
                    <div class="header-actions">
                        <input type="text" placeholder="Enter Payment ID" class="form-input" style="width: 200px;" id="searchPaymentId">
                        <button class="btn btn-secondary" onclick="searchPayment()">Search</button>
                    </div>
                </div>
                <div class="report-section">
                    <form onsubmit="updatePayment(event)" id="updatePaymentForm">
                        <div class="form-group">
                            <label class="form-label">Payment ID</label>
                            <input type="text" class="form-input" name="paymentId" readonly id="updatePaymentId">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Amount (₹)</label>
                            <input type="number" class="form-input" name="amount" min="0" id="updatePaymentAmount">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="updatePaymentStatus">
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="refunded">Refunded</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" name="paymentMethod" id="updatePaymentMethod">
                                <option value="credit_card">Credit Card</option>
                                <option value="debit_card">Debit Card</option>
                                <option value="upi">UPI</option>
                                <option value="net_banking">Net Banking</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" disabled id="updatePaymentBtn">Update Payment</button>
                    </form>
                </div>
            </div>

            <div id="view-payment" class="content-area">
                <div class="header">
                    <h2>View Payments</h2>
                    <div class="header-actions">
                        <select class="form-select" onchange="filterPaymentsByStatus(this.value)">
                            <option value="">All Payments</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <input type="text" placeholder="Search payments..." class="form-input" style="width: 250px;" onkeyup="filterUsers(this.value, 'viewPaymentsTable')">
                    </div>
                </div>
                <div class="report-section">
                    <table class="table" id="viewPaymentsTable">
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>P001</td>
                                <td>John Doe</td>
                                <td>JavaScript Fundamentals</td>
                                <td>₹2,999</td>
                                <td>UPI</td>
                                <td>2024-07-18</td>
                                <td><span class="status-badge status-pending">Pending</span></td>
                                <td><button class="btn btn-secondary" onclick="viewPaymentDetails('P001')">View Details</button></td>
                            </tr>
                            <tr>
                                <td>P003</td>
                                <td>Sarah Wilson</td>
                                <td>React Development</td>
                                <td>₹4,999</td>
                                <td>Credit Card</td>
                                <td>2024-07-17</td>
                                <td><span class="status-badge status-active">Approved</span></td>
                                <td><button class="btn btn-secondary" onclick="viewPaymentDetails('P003')">View Details</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="add-payment" class="content-area">
                <div class="header">
                    <h2>Add Manual Payment</h2>
                </div>
                <div class="report-section">
                    <form onsubmit="addPayment(event)">
                        <div class="form-group">
                            <label class="form-label">Student</label>
                            <select class="form-select" name="student" required>
                                <option value="">Select Student</option>
                                <option value="john-doe">John Doe</option>
                                <option value="mike-johnson">Mike Johnson</option>
                                <option value="sarah-wilson">Sarah Wilson</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Course</label>
                            <select class="form-select" name="course" required>
                                <option value="">Select Course</option>
                                <option value="javascript-fundamentals">JavaScript Fundamentals</option>
                                <option value="web-design-basics">Web Design Basics</option>
                                <option value="react-development">React Development</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Amount (₹)</label>
                            <input type="number" class="form-input" name="amount" min="0" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" name="paymentMethod" required>
                                <option value="">Select Payment Method</option>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Reference Number</label>
                            <input type="text" class="form-input" name="referenceNumber">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Notes</label>
                            <textarea class="form-input" name="notes" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Payment</button>
                    </form>
                </div>
            </div>

            <!-- Admin Profile -->
            <div id="admin-profile" class="content-area">
                <div class="header">
                    <h2>Admin Profile</h2>
                </div>
                <div class="report-section">
                    <form onsubmit="updateProfile(event)">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-input" value="Admin User" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-input" value="admin@learnify.in" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-input" value="+91 9876543210" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-input" value="Super Admin" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Change Password</label>
                            <input type="password" class="form-input" placeholder="Enter new password">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-input" placeholder="Confirm new password">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>

            <!-- Settings Content Areas -->
            <div id="system-settings" class="content-area">
                <div class="header">
                    <h2>System Settings</h2>
                </div>
                <div class="report-section">
                    <form onsubmit="updateSystemSettings(event)">
                        <div class="form-group">
                            <label class="form-label">Platform Name</label>
                            <input type="text" class="form-input" value="Learnify">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Maximum Users</label>
                            <input type="number" class="form-input" value="10000">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Session Timeout (minutes)</label>
                            <input type="number" class="form-input" value="30">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Default Course Price (₹)</label>
                            <input type="number" class="form-input" value="1999">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Settings</button>
                    </form>
                </div>
            </div>

            <div id="email-settings" class="content-area">
                <div class="header">
                    <h2>Email Settings</h2>
                </div>
                <div class="report-section">
                    <form onsubmit="updateEmailSettings(event)">
                        <div class="form-group">
                            <label class="form-label">SMTP Server</label>
                            <input type="text" class="form-input" value="smtp.learnify.in">
                        </div>
                        <div class="form-group">
                            <label class="form-label">SMTP Port</label>
                            <input type="number" class="form-input" value="587">
                        </div>
                        <div class="form-group">
                            <label class="form-label">From Email</label>
                            <input type="email" class="form-input" value="noreply@learnify.in">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Support Email</label>
                            <input type="email" class="form-input" value="support@learnify.in">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Email Settings</button>
                    </form>
                </div>
            </div>

            <div id="notification-settings" class="content-area">
                <div class="header">
                    <h2>Notification Settings</h2>
                </div>
                <div class="report-section">
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" checked style="margin-right: 10px;">
                            Email notifications for new user registrations
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" checked style="margin-right: 10px;">
                            Email notifications for course enrollments
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" style="margin-right: 10px;">
                            SMS notifications for payment confirmations
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" checked style="margin-right: 10px;">
                            Weekly admin reports
                        </label>
                    </div>
                    <button class="btn btn-primary" onclick="updateNotificationSettings()">Save Settings</button>
                </div>
            </div>

            <div id="backup-settings" class="content-area">
                <div class="header">
                    <h2>Backup & Restore</h2>
                </div>
                <div class="report-section">
                    <div class="form-group">
                        <label class="form-label">Automatic Backup</label>
                        <select class="form-select">
                            <option value="daily">Daily</option>
                            <option value="weekly" selected>Weekly</option>
                            <option value="monthly">Monthly</option>
    </select>


                            </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Backup</label>
                        <input type="text" class="form-input" value="2024-07-19 03:00 AM" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Backup Location</label>
                        <input type="text" class="form-input" value="/var/backups/learnify">
                    </div>
                    <div style="display: flex; gap: 15px; margin-top: 30px;">
                        <button class="btn btn-primary" onclick="createBackup()">Create Backup Now</button>
                        <button class="btn btn-secondary" onclick="restoreBackup()">Restore from Backup</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Payment Details Modal -->
    <div class="modal" id="paymentDetailsModal">
        <div class="modal-content">
            <h3 style="margin-bottom: 20px;">Payment Details</h3>
            <div id="paymentDetailsContent">
                <!-- Payment details will be loaded here -->
            </div>
            <button class="btn" style="margin-top: 20px; background: #6c757d; color: white;" onclick="closeModal('paymentDetailsModal')">Close</button>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal" id="confirmationModal">
        <div class="modal-content">
            <h3 style="margin-bottom: 20px;" id="confirmationTitle">Confirm Action</h3>
            <p id="confirmationMessage">Are you sure you want to perform this action?</p>
            <div style="display: flex; gap: 15px; margin-top: 30px;">
                <button class="btn btn-primary" id="confirmActionBtn">Confirm</button>
                <button class="btn" style="background: #6c757d; color: white;" onclick="closeModal('confirmationModal')">Cancel</button>
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
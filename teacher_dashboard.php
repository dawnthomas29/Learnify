<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - E-Learning Platform</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transition: all 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar.collapsed {
            width: 80px;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toggle-btn {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            padding: 5px;
        }

        .nav-menu {
            list-style: none;
            padding: 20px 0;
        }

        .nav-item {
            margin: 5px 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .nav-link i {
            width: 20px;
            margin-right: 15px;
            text-align: center;
        }

        .nav-text {
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed .nav-text {
            opacity: 0;
        }

        /* Submenu Styles */
        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background: rgba(0,0,0,0.1);
        }

        .submenu.open {
            max-height: 500px;
        }

        .submenu .nav-link {
            padding-left: 50px;
            font-size: 14px;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            transition: margin-left 0.3s ease;
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-left: 80px;
        }

        /* Top Header */
        .top-header {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .search-bar {
            display: flex;
            align-items: center;
            background: #f8f9fa;
            border-radius: 25px;
            padding: 8px 20px;
            width: 400px;
            max-width: 100%;
        }

        .search-bar input {
            border: none;
            outline: none;
            background: transparent;
            flex: 1;
            margin-left: 10px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification-btn {
            position: relative;
            background: none;
            border: none;
            font-size: 20px;
            color: #666;
            cursor: pointer;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        /* Dashboard Content */
        .dashboard-content {
            padding: 30px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            margin-right: 20px;
        }

        .stat-icon.blue { background: linear-gradient(45deg, #3498db, #2980b9); }
        .stat-icon.green { background: linear-gradient(45deg, #2ecc71, #27ae60); }
        .stat-icon.orange { background: linear-gradient(45deg, #f39c12, #e67e22); }
        .stat-icon.purple { background: linear-gradient(45deg, #9b59b6, #8e44ad); }

        .stat-info h3 {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-info p {
            color: #7f8c8d;
            font-size: 14px;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        /* Recent Activity */
        .activity-card, .quick-actions-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .card-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f8f9fa;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f8f9fa;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 16px;
        }

        .activity-content h4 {
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 3px;
        }

        .activity-content p {
            font-size: 12px;
            color: #7f8c8d;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            text-align: center;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .action-btn i {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .action-btn span {
            font-size: 12px;
            font-weight: 500;
        }

        /* Course Grid */
        .courses-section {
            margin-top: 30px;
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .course-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .course-card:hover {
            transform: translateY(-5px);
        }

        .course-header {
            height: 120px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
        }

        .course-content {
            padding: 20px;
        }

        .course-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .course-meta {
            display: flex;
            justify-content: space-between;
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .progress-bar {
            background: #f8f9fa;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 15px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .course-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #666;
            border: 1px solid #e9ecef;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                transform: translateX(-100%);
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .top-header {
                padding: 15px 20px;
            }

            .search-bar {
                width: 200px;
            }

            .dashboard-content {
                padding: 20px;
            }

            .content-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }
        }

        /* Utility Classes */
        .hidden {
            display: none !important;
        }

        .text-center {
            text-align: center;
        }

        .mb-20 {
            margin-bottom: 20px;
        }

        .mt-20 {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-graduation-cap"></i>
                <span class="nav-text">Learnify</span>
            </div>
            <button class="toggle-btn" id="toggleSidebar">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <nav>
            <ul class="nav-menu">
                
               
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="toggleSubmenu('subjects-menu')">
                        <i class="fas fa-list"></i>
                        <span class="nav-text">My Subjects</span>
                        <i class="fas fa-chevron-down" style="margin-left: auto;"></i>
                    </a>
                    <ul class="submenu" id="subjects-menu">
                        <li><a href="AddSubject.php" class="nav-link" data-page="my-subjects">My Assigned Subjects</a></li>
                        <li><a href="#" class="nav-link" data-page="add-chapter">Add Chapter</a></li>
                        <li><a href="#" class="nav-link" data-page="manage-chapters">Manage Chapters</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="toggleSubmenu('content-menu')">
                        <i class="fas fa-file-alt"></i>
                        <span class="nav-text">Content & Chapters</span>
                        <i class="fas fa-chevron-down" style="margin-left: auto;"></i>
                    </a>
                    <ul class="submenu" id="content-menu">
                        <li><a href="#" class="nav-link" data-page="upload-content">Upload Content</a></li>
                        <li><a href="#" class="nav-link" data-page="chapter-content">Chapter Content</a></li>
                        <li><a href="#" class="nav-link" data-page="interactive-tools">Interactive Tools</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-page="students">
                        <i class="fas fa-users"></i>
                        <span class="nav-text">Students</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="toggleSubmenu('assessment-menu')">
                        <i class="fas fa-clipboard-check"></i>
                        <span class="nav-text">Assessments</span>
                        <i class="fas fa-chevron-down" style="margin-left: auto;"></i>
                    </a>
                    <ul class="submenu" id="assessment-menu">
                        <li><a href="#" class="nav-link" data-page="create-test">Create Test</a></li>
                        <li><a href="#" class="nav-link" data-page="grade-assignments">Grade Assignments</a></li>
                        <li><a href="#" class="nav-link" data-page="gradebook">Grade Book</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="toggleSubmenu('live-menu')">
                        <i class="fas fa-video"></i>
                        <span class="nav-text">Live Classes</span>
                        <i class="fas fa-chevron-down" style="margin-left: auto;"></i>
                    </a>
                    <ul class="submenu" id="live-menu">
                        <li><a href="#" class="nav-link" data-page="schedule-session">Schedule Session</a></li>
                        <li><a href="#" class="nav-link" data-page="upcoming-classes">Upcoming Classes</a></li>
                        <li><a href="#" class="nav-link" data-page="recordings">Recordings</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="toggleSubmenu('communication-menu')">
                        <i class="fas fa-comments"></i>
                        <span class="nav-text">Communication</span>
                        <i class="fas fa-chevron-down" style="margin-left: auto;"></i>
                    </a>
                    <ul class="submenu" id="communication-menu">
                        <li><a href="#" class="nav-link" data-page="messages">Messages</a></li>
                        <li><a href="#" class="nav-link" data-page="announcements">Announcements</a></li>
                        <li><a href="#" class="nav-link" data-page="forums">Discussion Forums</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-page="analytics">
                        <i class="fas fa-chart-bar"></i>
                        <span class="nav-text">Analytics</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-page="settings">
                        <i class="fas fa-cog"></i>
                        <span class="nav-text">Settings</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Top Header -->
        <header class="top-header">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search courses, students, content...">
            </div>
            <div class="header-actions">
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </button>
                <div class="user-profile">
                    <div class="user-avatar">JD</div>
                    <div>
                        <div style="font-weight: 600;">John Doe</div>
                        <div style="font-size: 12px; color: #666;">Teacher</div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <div class="page-title">
                <i class="fas fa-tachometer-alt"></i>
                Teacher Dashboard
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="stat-info">
                        <h3>5</h3>
                        <p>Assigned Subjects</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="stat-info">
                        <h3>23</h3>
                        <p>Total Chapters</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>156</h3>
                        <p>Total Students</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3>12</h3>
                        <p>Pending Grades</p>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Recent Activity -->
                <div class="activity-card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Activity</h3>
                    </div>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon" style="background: #3498db;">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div class="activity-content">
                                <h4>New chapter added to Mathematics</h4>
                                <p>2 hours ago</p>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon" style="background: #2ecc71;">
                                <i class="fas fa-upload"></i>
                            </div>
                            <div class="activity-content">
                                <h4>Video content uploaded to Physics Chapter 5</h4>
                                <p>4 hours ago</p>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon" style="background: #f39c12;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="activity-content">
                                <h4>25 assignments graded in Chemistry</h4>
                                <p>1 day ago</p>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon" style="background: #9b59b6;">
                                <i class="fas fa-video"></i>
                            </div>
                            <div class="activity-content">
                                <h4>Live session completed for Biology</h4>
                                <p>2 days ago</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions-card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="quick-actions">
                        <a href="AddSubject.php" class="action-btn">
                            <i class="fas fa-plus"></i>
                            <span>Add Chapter</span>
                        </a>
                        <a href="#" class="action-btn">
                            <i class="fas fa-upload"></i>
                            <span>Upload Content</span>
                        </a>
                        <a href="#" class="action-btn">
                            <i class="fas fa-clipboard-check"></i>
                            <span>Create Test</span>
                        </a>
                        <a href="#" class="action-btn">
                            <i class="fas fa-video"></i>
                            <span>Schedule Live Class</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- My Assigned Subjects -->
            <div class="courses-section">
                <div class="card-header">
                    <h3 class="card-title">My Assigned Subjects</h3>
                </div>
                <div class="courses-grid">
                    <div class="course-card">
                        <div class="course-header">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div class="course-content">
                            <h4 class="course-title">Advanced Mathematics</h4>
                            <div class="course-meta">
                                <span>Course: Science Stream</span>
                                <span>8 Chapters</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 75%;"></div>
                            </div>
                            <div class="course-actions">
                                <a href="#" class="btn btn-primary">
                                    <i class="fas fa-eye"></i>
                                    View Subject
                                </a>
                                <a href="#" class="btn btn-secondary">
                                    <i class="fas fa-plus"></i>
                                    Add Chapter
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="course-card">
                        <div class="course-header">
                            <i class="fas fa-atom"></i>
                        </div>
                        <div class="course-content">
                            <h4 class="course-title">Organic Chemistry</h4>
                            <div class="course-meta">
                                <span>Course: Chemistry Major</span>
                                <span>12 Chapters</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 60%;"></div>
                            </div>
                            <div class="course-actions">
                                <a href="#" class="btn btn-primary">
                                    <i class="fas fa-eye"></i>
                                    View Subject
                                </a>
                                <a href="#" class="btn btn-secondary">
                                    <i class="fas fa-plus"></i>
                                    Add Chapter
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="course-card">
                        <div class="course-header">
                            <i class="fas fa-square-root-alt"></i>
                        </div>
                        <div class="course-content">
                            <h4 class="course-title">Calculus & Statistics</h4>
                            <div class="course-meta">
                                <span>Course: Mathematics</span>
                                <span>6 Chapters</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 90%;"></div>
                            </div>
                            <div class="course-actions">
                                <a href="#" class="btn btn-primary">
                                    <i class="fas fa-eye"></i>
                                    View Subject
                                </a>
                                <a href="#" class="btn btn-secondary">
                                    <i class="fas fa-plus"></i>
                                    Add Chapter
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="course-card">
                        <div class="course-header">
                            <i class="fas fa-flask"></i>
                        </div>
                        <div class="course-content">
                            <h4 class="course-title">Physical Chemistry</h4>
                            <div class="course-meta">
                                <span>Course: Chemistry Major</span>
                                <span>10 Chapters</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 45%;"></div>
                            </div>
                            <div class="course-actions">
                                <a href="#" class="btn btn-primary">
                                    <i class="fas fa-eye"></i>
                                    View Subject
                                </a>
                                <a href="#" class="btn btn-secondary">
                                    <i class="fas fa-plus"></i>
                                    Add Chapter
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="course-card">
                        <div class="course-header">
                            <i class="fas fa-wave-square"></i>
                        </div>
                        <div class="course-content">
                            <h4 class="course-title">Quantum Physics</h4>
                            <div class="course-meta">
                                <span>Course: Physics Advanced</span>
                                <span>7 Chapters</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 80%;"></div>
                            </div>
                            <div class="course-actions">
                                <a href="#" class="btn btn-primary">
                                    <i class="fas fa-eye"></i>
                                    View Subject
                                </a>
                                <a href="#" class="btn btn-secondary">
                                    <i class="fas fa-plus"></i>
                                    Add Chapter
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const toggleBtn = document.getElementById('toggleSidebar');

        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });

        // Navigation
        const navLinks = document.querySelectorAll('.nav-link[data-page]');
        
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all links
                navLinks.forEach(l => l.classList.remove('active'));
                
                // Add active class to clicked link
                this.classList.add('active');
                
                // Here you would typically load the content for the selected page
                const page = this.getAttribute('data-page');
                console.log('Navigate to:', page);
                // loadPage(page); // You'll implement this function later
            });
        });

        // Submenu Toggle
        function toggleSubmenu(menuId) {
            const submenu = document.getElementById(menuId);
            submenu.classList.toggle('open');
        }

        // Mobile Sidebar Toggle
        function toggleMobileSidebar() {
            sidebar.classList.toggle('mobile-open');
        }

        // Close mobile sidebar when clicking outside
        document.addEventListener('click', function(e) {
            if (window.innerWidth <=
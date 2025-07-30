<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learnify - Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #2e59d9;
            --text-color: #5a5c69;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            margin: 0;
            padding: 0;
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
            color: var(--text-color);
        }
        
        /* Sidebar Styles */
        .sidebar {
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
        }
        
        .sidebar-header {
            text-align: center;
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .menu-item:hover {
            background-color: var(--accent-color);
        }
        
        .menu-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Main Content Styles */
        .main-content {
            background-color: var(--secondary-color);
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #ddd;
            margin-right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .welcome-section {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .welcome-section h1 {
            margin-top: 0;
            color: var(--primary-color);
        }
        
        .welcome-section p {
            margin-bottom: 20px;
        }
        
        .subjects-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .subject-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            transition: transform 0.3s;
        }
        
        .subject-card:hover {
            transform: translateY(-5px);
        }
        
        .subject-card h3 {
            margin-top: 0;
            color: var(--primary-color);
        }
        
        .subject-list {
            list-style-type: none;
            padding: 0;
        }
        
        .subject-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .subscribe-banner {
            background-color: var(--primary-color);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-top: 30px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Learnify</h2>
        </div>
        <div class="sidebar-menu">
            <div class="menu-item">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-user"></i>
                <span>My Profile</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-book"></i>
                <span>My Courses</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-users"></i>
                <span>Parent Corner</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-question-circle"></i>
                <span>Support</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-ticket-alt"></i>
                <span>Doubt Ticket</span>
            </div>
             <div class="menu-item" onclick="window.location.href='HomePage.php'">
    <i class="fas fa-sign-out-alt"></i>
    <span>Sign Out</span>
</div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2>Student Dashboard</h2>
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <span>Student Name</span>
            </div>
        </div>
        
        <div class="welcome-section">
            <h1>Welcome to Learnify!</h1>
            <p>Don<br>Tith Standard - HSE</p>
        </div>
        
        <h2>Subjects</h2>
        <div class="subjects-container">
            <div class="subject-card">
                <h3>Science Stream</h3>
                <ul class="subject-list">
                    <li><strong>Maths</strong></li>
                    <li>Biology</li>
                </ul>
            </div>
            
            <div class="subject-card">
                <h3>Commerce Stream</h3>
                <ul class="subject-list">
                    <li><strong>Chemistry</strong></li>
                    <li>Physics</li>
                </ul>
            </div>
            
            <div class="subject-card">
                <h3>Humanities</h3>
                <ul class="subject-list">
                    <li><strong>English</strong></li>
                    <li>Maths English Class</li>
                </ul>
            </div>
        </div>
        
        <div class="subscribe-banner">
            <h3>90+ Subscribe Now</h3>
            <p>Click here to subscribe full course</p>
        </div>
    </div>
</body>
</html>
<?php
session_start();
require '../Databaseconnection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $target_dir = "../uploads/profile_pictures/";
    
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
    $new_filename = $user_id . '_' . time() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
    if($check !== false) {
        // Check file size (limit to 5MB)
        if ($_FILES["profile_picture"]["size"] <= 5000000) {
            // Allow certain file formats
            if($file_extension == "jpg" || $file_extension == "png" || $file_extension == "jpeg" || $file_extension == "gif" ) {
                if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                    // Update database with new profile picture path
                    $update_query = "UPDATE users SET profile_picture = ? WHERE user_id = ?";
                    $update_stmt = $conn->prepare($update_query);
                    $update_stmt->bind_param("si", $new_filename, $user_id);
                    
                    if ($update_stmt->execute()) {
                        $success_message = "Profile picture updated successfully!";
                    } else {
                        $error_message = "Error updating database.";
                    }
                } else {
                    $error_message = "Sorry, there was an error uploading your file.";
                }
            } else {
                $error_message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            }
        } else {
            $error_message = "Sorry, your file is too large. Maximum size is 5MB.";
        }
    } else {
        $error_message = "File is not an image.";
    }
}

// SQL query to fetch only admin user's profile
$query = "
    SELECT 
        u.*, 
        r.role_name, 
        r.role_prefix,
        pd.phone,
        pd.date_of_birth,
        pd.country,
        pd.city,
        pd.postal_code
    FROM users u 
    JOIN roles r ON u.role_id = r.role_id
    LEFT JOIN profile_details pd ON u.user_id = pd.user_id
    WHERE u.user_id = ? AND r.role_name = 'Admin'
";

$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    die("Access denied. You are not an admin user.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile | Learnify</title>
    <style>
        :root {
            --primary-color: #2d7d32;
            --secondary-color: #4caf50;
            --light-gray: #f8f9fa;
            --dark-gray: #343a40;
            --border-color: #e0e0e0;
            --text-secondary: #666;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fb;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        
                .date-text {
                position: absolute;   /* ✅ This is important */
                top: 5px;             /* Match this with .time-text */
                  left: 300px;  
                font-weight: 500;
                font-size: 16px;
                color:white;
                opacity: 0.8;
            }



        
        .time-text {
            position: absolute;      
            top: 5px;               
            right: 10px;
            font-size: 16px;
            opacity: 0.8;
            color:white;
        }

        
        .nav-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .social-nav {
            display: flex;
            gap: 8px;
            margin-right: 15px;
        }
        
        .social-btn {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .social-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .social-btn.gmail {
            background: #ea4335;
            border-color: #ea4335;
        }
        
        .social-btn.gmail:hover {
            background: #d33b2c;
        }
        
        .social-btn.instagram {
            background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
            border: none;
        }
        
        .social-btn.instagram:hover {
            opacity: 0.9;
        }
        
        .social-btn.facebook {
            background: #1877f2;
            border-color: #1877f2;
        }
        
        .social-btn.facebook:hover {
            background: #166fe5;
        }
        
        .social-icon {
            width: 16px;
            height: 16px;
            fill: white;
        }
        
        .notification-icon {
            width: 36px;
            height: 36px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .notification-icon:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .notification-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 16px;
            height: 16px;
            background: #f44336;
            border-radius: 50%;
            font-size: 10px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        /* Adjust main content for fixed header */
       
        
      
        
        .profile-container {
            width: 1150px;
            margin: 0 auto;
        }
        
        .profile-header {
            background: white;
            border-radius: 3px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 25px;
        }
        
        .profile-picture-container {
            position: relative;
            flex-shrink: 0;
        }
        
        .profile-picture {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-color);
        }
        
        .camera-overlay {
            position: absolute;
            bottom: -5px;
            right: -5px;
            width: 32px;
            height: 32px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        
        .camera-overlay:hover {
            background: var(--secondary-color);
            transform: scale(1.1);
        }
        
        .camera-icon {
            width: 16px;
            height: 16px;
            fill: white;
        }
        
        .upload-input {
            display: none;
        }
        
        .profile-info h1 {
            margin: 0 0 5px 0;
            font-size: 28px;
            font-weight: 600;
            color: #333;
        }
        
        .profile-info p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 16px;
        }
        
        .profile-location {
            margin-top: 5px;
            color: var(--text-secondary);
            font-size: 14px;
        }
        
        .info-section {
            background: white;
            border-radius: 3px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .edit-btn {
            background: none;
            border: 1px solid var(--border-color);
            padding: 8px 16px;
            border-radius: 6px;
            color: var(--text-secondary);
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .edit-btn:hover {
            background: #f5f5f5;
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 30px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .info-value {
            color: #333;
            font-size: 16px;
            font-weight: 500;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
            opacity: 1;
            transition: opacity 0.5s ease-out;
        }
        
        .alert.fade-out {
            opacity: 0;
        }
        
        .alert-success {
            background-color: #e8f5e8;
            border: 1px solid #4caf50;
            color: #2d7d32;
        }
        
        .alert-error {
            background-color: #ffeaea;
            border: 1px solid #f44336;
            color: #c62828;
        }
        
        @media (max-width: 768px) {
            .top-nav {
                left: 0;
                flex-direction: column;
                padding: 15px;
                gap: 10px;
            }
            
            .search-container {
                max-width: 100%;
                margin: 0;
            }
            
            .nav-right {
                gap: 10px;
            }
            
            .main-content {
                margin-left: 0;
               
                padding: 20px;
            }
            
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
        
        @media (max-width: 600px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        
            
                <div class="date-text" id="currentDate"></div>
                <div class="time-text" id="currentTime"></div>
                <br>
          
           
        
        
        
        <div class="profile-container">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success" id="successAlert"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error" id="errorAlert"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="profile-header">
                <div class="profile-picture-container">
                    <?php 
                    $profile_pic_path = !empty($user['profile_picture']) 
                        ? '../uploads/profile_pictures/' . $user['profile_picture'] 
                        : '../assets/default-avatar.png';
                    
                    // Check if file exists, otherwise use default
                    if (!file_exists($profile_pic_path) || empty($user['profile_picture'])) {
                        $profile_pic_path = 'data:image/svg+xml;base64,' . base64_encode('
                        <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="50" fill="#e9ecef"/>
                            <circle cx="50" cy="35" r="15" fill="#adb5bd"/>
                            <path d="M50 60c-13 0-25 11-25 25v15h50v-15c0-14-12-25-25-25z" fill="#adb5bd"/>
                        </svg>');
                    }
                    ?>
                    
                    <img src="<?php echo $profile_pic_path; ?>" alt="Profile Picture" class="profile-picture" id="profileImage">
                    
                    <form id="uploadForm" method="POST" enctype="multipart/form-data" style="display: inline;">
                        <input type="file" name="profile_picture" accept="image/*" class="upload-input" id="fileInput">
                        <div class="camera-overlay" onclick="document.getElementById('fileInput').click()">
                            <svg class="camera-icon" viewBox="0 0 24 24">
                                <path d="M12 2l3 3h4a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h4l3-3h0zm0 4a6 6 0 1 0 0 12 6 6 0 0 0 0-12zm0 2a4 4 0 1 1 0 8 4 4 0 0 1 0-8z"/>
                            </svg>
                        </div>
                    </form>
                </div>
                
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($user['name']); ?></h1>
                    <p><?php echo htmlspecialchars($user['role_name']); ?></p>
                    <?php if (!empty($user['city']) && !empty($user['country'])): ?>
                        <div class="profile-location"><?php echo htmlspecialchars($user['city'] . ', ' . $user['country']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="info-section">
                <div class="section-header">
                    <h2 class="section-title">Personal Information</h2>
                    <a href="edit-profile.php" class="edit-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="m18.5 2.5 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Edit
                    </a>
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['name']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value">
                            <?php echo !empty($user['date_of_birth']) ? 
                                date('d-m-Y', strtotime($user['date_of_birth'])) : 'Not set'; ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Email Address</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value"><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not set'; ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">User Role</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['role_name']); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="info-section">
                <div class="section-header">
                    <h2 class="section-title">Address</h2>
                    <a href="edit-profile.php" class="edit-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="m18.5 2.5 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Edit
                    </a>
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Country</div>
                        <div class="info-value"><?php echo !empty($user['country']) ? htmlspecialchars($user['country']) : 'Not set'; ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">City</div>
                        <div class="info-value"><?php echo !empty($user['city']) ? htmlspecialchars($user['city']) : 'Not set'; ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Postal Code</div>
                        <div class="info-value"><?php echo !empty($user['postal_code']) ? htmlspecialchars($user['postal_code']) : 'Not set'; ?></div>
                    </div>

                    <div class="social-nav">
               
                <a href="https://instagram.com" target="_blank" class="social-btn instagram" title="Instagram">
                    <svg class="social-icon" viewBox="0 0 24 24">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                </a>
                
                <a href="https://facebook.com" target="_blank" class="social-btn facebook" title="Facebook">
                    <svg class="social-icon" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </a>
            </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-submit form when file is selected
        document.getElementById('fileInput').addEventListener('change', function() {
            if (this.files && this.files[0]) {
                // Preview the image before upload
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profileImage').src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
                
                // Submit the form
                document.getElementById('uploadForm').submit();
            }
        });

        // Update date and time
        function updateDateTime() {
            const now = new Date();
            
            // Format date
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            const dateString = now.toLocaleDateString('en-US', options);
            
            // Format time
            const timeString = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
            
            document.getElementById('currentDate').textContent = dateString;
            document.getElementById('currentTime').textContent = timeString;
        }
        
        // Update time every second
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Auto-hide success and error messages after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.getElementById('successAlert');
            const errorAlert = document.getElementById('errorAlert');
            
            if (successAlert) {
                setTimeout(function() {
                    successAlert.classList.add('fade-out');
                    setTimeout(function() {
                        successAlert.style.display = 'none';
                    }, 500);
                }, 3000);
            }
            
            if (errorAlert) {
                setTimeout(function() {
                    errorAlert.classList.add('fade-out');
                    setTimeout(function() {
                        errorAlert.style.display = 'none';
                    }, 500);
                }, 5000);
            }
        });
    </script>
</body>
</html>
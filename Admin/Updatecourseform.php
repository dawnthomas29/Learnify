\<?php
session_start();
require '../Databaseconnection.php';

$course_id = $_GET['course_id'] ?? '';
if (!$course_id) die("Invalid Course ID.");

// Fetch course details
$courseQuery = $conn->query("SELECT * FROM courses WHERE course_id = '$course_id'");
if ($courseQuery->num_rows === 0) die("Course not found.");
$course = $courseQuery->fetch_assoc();

// Fetch all teachers
$teachers = [];
$teacherQuery = $conn->query("SELECT user_id, name FROM users WHERE user_id LIKE 'TR%'");
while ($row = $teacherQuery->fetch_assoc()) $teachers[] = $row;

// Fetch all subjects
$allSubjects = [];
$subjectQuery = $conn->query("SELECT subject_id, subject_name FROM subjects");
while ($row = $subjectQuery->fetch_assoc()) $allSubjects[] = $row;

// Fetch course subjects
$courseSubjects = [];
$csQuery = $conn->query("SELECT subject_id FROM course_subject WHERE course_id = '$course_id'");
while ($row = $csQuery->fetch_assoc()) $courseSubjects[] = $row['subject_id'];

// Fetch assigned teachers
$assignedTeachers = [];
$ctQuery = $conn->query("SELECT subject_id, teacher_id FROM course_teachers WHERE course_id = '$course_id'");
while ($row = $ctQuery->fetch_assoc()) {
    $assignedTeachers[$row['subject_id']] = $row['teacher_id'];
}

// Handle form submission
$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['course_name'];
    $stream = $_POST['stream'];
    $class = $_POST['class_level'];
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0.0;
    $desc = $_POST['description'];

    $subjectIDs = [];
    for ($i = 1; $i <= 6; $i++) {
        $subjectIDs[$i] = $_POST["subject$i"] ?? '';
    }

    $teacherIDs = [];
    for ($i = 1; $i <= 6; $i++) {
        $teacherIDs[$i] = $_POST["teacher$i"] ?? '';
    }

    // Image handling
    $image = $course['image'];
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageName = basename($_FILES["image"]["name"]);
        $imagePath = "../uploads/" . $imageName;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) {
            $image = $imagePath;
        }
    }

    // Update main course table
    $stmt = $conn->prepare("UPDATE courses SET course_name=?, stream=?, class_level=?, price=?, description=?, image=? WHERE course_id=?");
    $stmt->bind_param("sssdsss", $name, $stream, $class, $price, $desc, $image, $course_id);

    if ($stmt->execute()) {
        // Remove old subject/teacher assignments
        $conn->query("DELETE FROM course_subject WHERE course_id = '$course_id'");
        $conn->query("DELETE FROM course_teachers WHERE course_id = '$course_id'");

        // Add new subject + teacher assignments
        for ($i = 1; $i <= 6; $i++) {
            $subj = $subjectIDs[$i];
            $teacher = $teacherIDs[$i];
            if ($subj) {
                $conn->query("INSERT INTO course_subject (course_id, subject_id) VALUES ('$course_id', '$subj')");
                if ($teacher) {
                    $conn->query("INSERT INTO course_teachers (course_id, subject_id, teacher_id) 
                                  VALUES ('$course_id', '$subj', '$teacher')");
                }
            }
        }

        $success = "Course updated successfully!";
    } else {
        $error = "Update failed: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - Learnify</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #8B5FBF 0%, #6366F1 25%, #8B5FBF 50%, #EC4899 75%, #F59E0B 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
            padding: 20px;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .main-content {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .header-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .header-card h1 {
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
        }

        .graduation-icon {
            font-size: 48px;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
        }

        .header-card p {
            color: #64748b;
            font-size: 18px;
            font-weight: 500;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
        }

        .form-section {
            margin-bottom: 40px;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 3px solid #f1f5f9;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
        }

        input, select, textarea {
            padding: 16px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #8b5fbf;
            box-shadow: 0 0 0 4px rgba(139, 95, 191, 0.1);
            transform: translateY(-2px);
        }

        input:hover, select:hover, textarea:hover {
            border-color: #d1d5db;
            transform: translateY(-1px);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
            font-family: inherit;
        }

        .file-upload-area {
            position: relative;
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            padding: 32px;
            text-align: center;
            background: rgba(249, 250, 251, 0.8);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .file-upload-area:hover {
            border-color: #8b5fbf;
            background: rgba(139, 95, 191, 0.02);
            transform: translateY(-2px);
        }

        .file-upload-area.dragover {
            border-color: #8b5fbf;
            background: rgba(139, 95, 191, 0.05);
        }

        .upload-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.6;
        }

        .upload-text {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .upload-subtext {
            color: #6b7280;
            font-size: 14px;
        }

        .file-input {
            display: none;
        }

        .current-image {
            margin-top: 16px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            max-width: 200px;
        }

        .subjects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
        }

        .subject-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(248,250,252,0.9) 100%);
            border: 2px solid rgba(226, 232, 240, 0.8);
            border-radius: 16px;
            padding: 24px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .subject-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #8b5fbf, #6366f1);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .subject-card:hover {
            border-color: #8b5fbf;
            transform: translateY(-4px);
            box-shadow: 0 16px 40px rgba(139, 95, 191, 0.15);
        }

        .subject-card:hover::before {
            opacity: 1;
        }

        .subject-header {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .subject-number {
            background: linear-gradient(135deg, #8b5fbf, #6366f1);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 14px;
        }

        .elective-badge {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: auto;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }

        .form-actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 50px;
            padding-top: 40px;
            border-top: 2px solid #f1f5f9;
        }

        .btn {
            padding: 16px 32px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 160px;
            justify-content: center;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(16, 185, 129, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6b7280, #4b5563);
            color: white;
            box-shadow: 0 8px 24px rgba(107, 114, 128, 0.3);
        }

        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(107, 114, 128, 0.4);
        }

        /* Success/Error Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            transform: scale(0.9) translateY(20px);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .modal-overlay.show .modal-content {
            transform: scale(1) translateY(0);
        }

        .modal-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            animation: modalIconPop 0.5s ease 0.2s both;
        }

        .modal-icon.success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .modal-icon.error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        @keyframes modalIconPop {
            0% { transform: scale(0); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .modal-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #1e293b;
        }

        .modal-message {
            font-size: 16px;
            color: #64748b;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .modal-close {
            background: linear-gradient(135deg, #8b5fbf, #6366f1);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .modal-close:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(139, 95, 191, 0.3);
        }

        /* Loading animation */
        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .btn-primary.loading .loading-spinner {
            display: block;
        }

        .btn-primary.loading .btn-text {
            display: none;
        }

        @media (max-width: 768px) {
            body {
                padding: 12px;
            }

            .header-card {
                padding: 24px;
            }

            .header-card h1 {
                font-size: 32px;
                flex-direction: column;
                gap: 8px;
            }

            .form-container {
                padding: 24px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .subjects-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .form-actions {
                flex-direction: column;
                gap: 12px;
            }

            .btn {
                width: 100%;
            }

            .modal-content {
                padding: 24px;
                margin: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="header-section">
            <div class="header-card">
                <h1>
                    <span class="graduation-icon">🎓</span>
                    Edit Course
                </h1>
                <p>Update course information, subjects and teacher assignments</p>
            </div>
        </div>

        <div class="form-container">
            <form method="post" enctype="multipart/form-data" id="courseForm">
                <!-- Basic Information Section -->
                <div class="form-section">
                    <div class="section-title">
                        📋 Basic Information
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>📚 Course Name</label>
                            <input type="text" name="course_name" value="<?= htmlspecialchars($course['course_name']) ?>" placeholder="Enter course name" required>
                        </div>

                        <div class="form-group">
                            <label>🎯 Stream</label>
                            <input type="text" name="stream" value="<?= htmlspecialchars($course['stream']) ?>" placeholder="Enter stream" required>
                        </div>

                        <div class="form-group">
                            <label>📊 Class Level</label>
                            <input type="text" name="class_level" value="<?= htmlspecialchars($course['class_level']) ?>" placeholder="Enter class level" required>
                        </div>

                        <div class="form-group">
                            <label>💰 Course Fee (₹)</label>
                            <input type="number" name="price" step="0.01" value="<?= htmlspecialchars($course['price']) ?>" placeholder="Enter course fee" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label>📝 Course Description</label>
                            <textarea name="description" placeholder="Enter detailed course description..." required><?= htmlspecialchars($course['description']) ?></textarea>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>🖼️ Course Image</label>
                            <div class="file-upload-area" onclick="document.getElementById('imageInput').click()">
                                <div class="upload-icon">📁</div>
                                <div class="upload-text">Click to upload or drag and drop</div>
                                <div class="upload-subtext">PNG, JPG, GIF up to 10MB</div>
                                <input type="file" name="image" class="file-input" id="imageInput" accept="image/*">
                            </div>
                            <?php if (!empty($course['image'])): ?>
                                <img src="<?= htmlspecialchars($course['image']) ?>" class="current-image" alt="Current course image">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Subjects & Teachers Section -->
                <div class="form-section">
                    <div class="section-title">
                        🎯 Subjects & Teachers Assignment
                    </div>

                    <div class="subjects-grid">
                        <?php for ($i = 1; $i <= 6; $i++): 
                            $existingSubj = $courseSubjects[$i - 1] ?? '';
                            $assigned = $assignedTeachers[$existingSubj] ?? '';
                        ?>
                        <div class="subject-card">
                            <div class="subject-header">
                                <span class="subject-number"><?= $i ?></span>
                                Subject <?= $i ?>
                                <?php if ($i == 6): ?>
                                    <span class="elective-badge">ELECTIVE</span>
                                <?php endif; ?>
                            </div>

                            <div class="form-group" style="margin-bottom: 16px;">
                                <label>📖 Subject</label>
                                <select name="subject<?= $i ?>" required>
                                    <option value="">Select Subject</option>
                                    <?php foreach ($allSubjects as $subject): ?>
                                        <option value="<?= $subject['subject_id'] ?>" <?= ($subject['subject_id'] == $existingSubj) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($subject['subject_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>👨‍🏫 Teacher</label>
                                <select name="teacher<?= $i ?>" required>
                                    <option value="">Select Teacher</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?= $teacher['user_id'] ?>" <?= ($teacher['user_id'] == $assigned) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($teacher['name']) ?> (<?= htmlspecialchars($teacher['user_id']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="updateBtn">
                        <span class="btn-text">
                            ✏️ Update Course
                        </span>
                        <div class="loading-spinner"></div>
                    </button>
                    
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='Viewcourse.php'">
                        ← Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success/Error Modal -->
    <div class="modal-overlay" id="messageModal">
        <div class="modal-content">
            <div class="modal-icon" id="modalIcon">
                <span id="modalIconSymbol"></span>
            </div>
            <div class="modal-title" id="modalTitle"></div>
            <div class="modal-message" id="modalMessage"></div>
            <button class="modal-close" onclick="closeModal()">Got it!</button>
        </div>
    </div>

    <script>
        // Show modal for success/error messages
        <?php if ($success): ?>
            showModal('success', 'Course Updated!', '<?= addslashes($success) ?>');
        <?php endif; ?>

        <?php if ($error): ?>
            showModal('error', 'Update Failed!', '<?= addslashes($error) ?>');
        <?php endif; ?>

        function showModal(type, title, message) {
            const modal = document.getElementById('messageModal');
            const icon = document.getElementById('modalIcon');
            const iconSymbol = document.getElementById('modalIconSymbol');
            const modalTitle = document.getElementById('modalTitle');
            const modalMessage = document.getElementById('modalMessage');

            // Set icon and styling based on type
            if (type === 'success') {
                icon.className = 'modal-icon success';
                iconSymbol.textContent = '✓';
            } else {
                icon.className = 'modal-icon error';
                iconSymbol.textContent = '✕';
            }

            modalTitle.textContent = title;
            modalMessage.textContent = message;
            modal.classList.add('show');
        }

        function closeModal() {
            const modal = document.getElementById('messageModal');
            modal.classList.remove('show');
            
            // If success, redirect after closing
            <?php if ($success): ?>
                setTimeout(() => {
                    window.location.href = 'Viewcourse.php';
                }, 300);
            <?php endif; ?>
        }

        // Close modal on background click
        document.getElementById('messageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // File upload enhancements
        const fileInput = document.getElementById('imageInput');
        const uploadArea = document.querySelector('.file-upload-area');

        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const uploadText = uploadArea.querySelector('.upload-text');
                const uploadIcon = uploadArea.querySelector('.upload-icon');
                
                uploadText.textContent = file.name;
                uploadIcon.textContent = '✓';
                uploadArea.style.borderColor = '#10b981';
                uploadArea.style.background = 'rgba(16, 185, 129, 0.05)';
            }
        });

        // Drag and drop functionality
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                const event = new Event('change', { bubbles: true });
                fileInput.dispatchEvent(event);
            }
        });

        // Form submission with loading state
        document.getElementById('courseForm').addEventListener('submit', function() {
            const btn = document.getElementById('updateBtn');
            btn.classList.add('loading');
            btn.disabled = true;
        });

        // Auto-close modal after 5 seconds for success
        <?php if ($success): ?>
            setTimeout(() => {
                closeModal();
            }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>
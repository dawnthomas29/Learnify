<?php
session_start();
require '../Databaseconnection.php';

$success = false;
$error = "";

// Fetch subjects and teachers for dropdowns
$subjectsResult = $conn->query("SELECT subject_id, subject_name FROM subjects");
$teachersResult = $conn->query("SELECT user_id, name FROM users WHERE user_id LIKE 'TR%'");

$subjects = [];
while ($row = $subjectsResult->fetch_assoc()) {
    $subjects[] = $row;
}

$teachers = [];
while ($row = $teachersResult->fetch_assoc()) {
    $teachers[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name = $_POST['course_name'] ?? '';
    $stream = $_POST['stream'] ?? '';
    $class_level = $_POST['class_level'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';

    if (!$course_name || !$stream || !$class_level || !$description || $price === '') {
        $error = "Please fill in all required fields.";
    } else {
        // Course ID generation
        $streamCodes = [
            'Bio Science' => 'BSC',
            'Computer Science' => 'CSI',
            'Home Science' => 'HSC',
            'Humanities' => 'HUM',
            'Commerce' => 'COM'
        ];
        $streamCode = $streamCodes[$stream] ?? 'GEN';

        $electiveSubjectId = $_POST['subject6'] ?? '';
        $electiveName = 'ELE';
        if ($electiveSubjectId) {
            $electiveQuery = $conn->prepare("SELECT subject_name FROM subjects WHERE subject_id = ?");
            $electiveQuery->bind_param("s", $electiveSubjectId);
            $electiveQuery->execute();
            $result = $electiveQuery->get_result();
            if ($row = $result->fetch_assoc()) {
                $electiveName = $row['subject_name'];
            }
        }
        $electiveCode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $electiveName), 0, 3));
        $classCode = ($class_level === "1") ? "01" : "02";
        $classLabel = ($class_level === "1") ? "Plus One" : "Plus Two";
        $course_id = $streamCode . $classCode . $electiveCode;

        // Image upload
        $imagePath = '';
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageName = basename($_FILES['image']['name']);
            $imagePath = "../uploads/" . $imageName;
            move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
        }

        // Insert into courses table (no subject fields)
        $stmt = $conn->prepare("INSERT INTO courses (course_id, course_name, stream, class_level, price, description, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssdss", $course_id, $course_name, $stream, $classLabel, $price, $description, $imagePath);

        if ($stmt->execute()) {
            // Insert 6 subjects into course_subject
            for ($i = 1; $i <= 6; $i++) {
                $subject_id = $_POST["subject$i"] ?? '';
                if ($subject_id) {
                    // Prevent duplicate entry
                    $check = $conn->prepare("SELECT 1 FROM course_subject WHERE course_id = ? AND subject_id = ?");
                    $check->bind_param("ss", $course_id, $subject_id);
                    $check->execute();
                    $checkResult = $check->get_result();

                    if ($checkResult->num_rows === 0) {
                        $insertSubject = $conn->prepare("INSERT INTO course_subject (course_id, subject_id) VALUES (?, ?)");
                        $insertSubject->bind_param("ss", $course_id, $subject_id);
                        $insertSubject->execute();
                    }
                }
            }

            // Assign teachers
            for ($i = 1; $i <= 6; $i++) {
                $subject_id = $_POST["subject$i"] ?? '';
                $teacher_id = $_POST["teacher$i"] ?? '';
                if ($subject_id && $teacher_id) {
                    $teachStmt = $conn->prepare("INSERT INTO course_teachers (course_id, subject_id, teacher_id) VALUES (?, ?, ?)");
                    $teachStmt->bind_param("sss", $course_id, $subject_id, $teacher_id);
                    $teachStmt->execute();
                }
            }

            $success = true;
        } else {
            $error = "Course insertion failed: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course - Learnify Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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

        /* =============== MAIN CONTENT STYLES - ALIGNED WITH SIDEBAR =============== */
        .main-content {
            margin-left: 30px; /* Match your sidebar width */
            min-height: 100vh;
            padding: 30px;
            background: transparent;
        }

        .content-wrapper {
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 4px;
            padding: 30px 40px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .page-header h1 {
            color: #333;
            font-size: 2.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .page-header p {
            color: #666;
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 4px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .form-group {
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102,126,234,0.15);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .description-group {
            grid-column: 1 / -1;
        }

        .subjects-section {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            padding: 30px;
            border-radius: 4px;
            margin: 30px 0;
            border: 1px solid rgba(102, 126, 234, 0.1);
        }

        .subjects-section h3 {
            color: #2c3e50;
            margin-bottom: 25px;
            font-size: 1.4rem;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .subject-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }

        .subject-item {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 25px;
            border-radius: 4px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .subject-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .subject-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.12);
        }

        .subject-item h4 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .elective-badge {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px 20px;
            background: #f8f9fa;
            border: 2px dashed #e1e8ed;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
            color: #6c757d;
            min-height: 60px;
        }

        .file-input-label:hover {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            color: #667eea;
            transform: translateY(-2px);
        }

        .file-input-label i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .file-selected {
            border-color: #28a745 !important;
            background: rgba(40, 167, 69, 0.05) !important;
            color: #28a745 !important;
        }

        .button-group {
            display: flex;
            gap: 20px;
            margin-top: 40px;
            justify-content: center;
            align-items: center;
        }

        .submit-btn,
        .cancel-btn {
            padding: 15px 30px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 160px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .submit-btn {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .cancel-btn {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            color: white;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, #218838, #1ea080);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }

        .cancel-btn:hover {
            background: linear-gradient(135deg, #5a6268, #495057);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
        }

        .submit-btn:active,
        .cancel-btn:active {
            transform: translateY(-1px);
        }

        /* =============== POPUP STYLES =============== */
        .popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }

        .popup.show {
            opacity: 1;
            visibility: visible;
        }

        .popup-content {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 450px;
            width: 90%;
            transform: scale(0.7);
            transition: transform 0.3s ease;
            position: relative;
        }

        .popup.show .popup-content {
            transform: scale(1);
        }

        .popup-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            animation: popupIconBounce 0.6s ease;
        }

        @keyframes popupIconBounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        .popup-icon.success {
            color: #28a745;
        }

        .popup-icon.error {
            color: #dc3545;
        }

        .popup h3 {
            margin-bottom: 15px;
            font-size: 1.5rem;
            color: #333;
        }

        .popup p {
            margin-bottom: 25px;
            color: #6c757d;
            line-height: 1.6;
            font-size: 1rem;
        }

        .popup-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            min-width: 120px;
        }

        .popup-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102,126,234,0.4);
        }

        /* =============== MOBILE RESPONSIVENESS =============== */
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 70px 20px 20px 20px; /* Add top padding for mobile toggle */
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 80px 15px 20px 15px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .subject-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .form-container {
                padding: 25px;
            }
            
            .page-header {
                padding: 25px;
            }
            
            .page-header h1 {
                font-size: 1.8rem;
                flex-direction: column;
                gap: 10px;
            }

            .button-group {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }

            .submit-btn,
            .cancel-btn {
                min-width: unset;
                width: 100%;
            }

            .subjects-section {
                padding: 20px;
            }

            .subject-item {
                padding: 20px;
            }
        }

        /* Loading states */
        .loading {
            position: relative;
            pointer-events: none;
            opacity: 0.7;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #fff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Enhanced form validation styles */
        .form-group.error input,
        .form-group.error select,
        .form-group.error textarea {
            border-color: #dc3545;
            background: rgba(220, 53, 69, 0.05);
        }

        .form-group.success input,
        .form-group.success select,
        .form-group.success textarea {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.05);
        }

        .error-message {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Smooth transitions for all interactive elements */
        * {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        input, select, textarea, button {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>        

    <!-- Main Content Area -->
    <div class="main-content">
        <div class="content-wrapper">
            <!-- Page Header -->
            <div class="page-header">
                <h1>
                    <i class="fas fa-graduation-cap"></i>
                    Add New Course
                </h1>
                <p>Create and configure a new course with subjects and teachers</p>
            </div>

            <!-- Form Container -->
            <div class="form-container">
                <form method="post" enctype="multipart/form-data" id="courseForm">
                    <!-- Basic Course Information -->
                    <div class="form-grid">
                        <div class="form-group">
                            <label>
                                <i class="fas fa-book"></i>
                                Course Name
                            </label>
                            <input type="text" name="course_name" placeholder="Enter course name" required>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-stream"></i>
                                Stream
                            </label>
                            <select name="stream" id="stream" onchange="autofillSubjects()" required>
                                <option value="">Select Stream</option>
                                <option value="Bio Science">Bio Science</option>
                                <option value="Computer Science">Computer Science</option>
                                <option value="Home Science">Home Science</option>
                                <option value="Humanities">Humanities</option>
                                <option value="Commerce">Commerce</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-layer-group"></i>
                                Class Level
                            </label>
                            <select name="class_level" id="class_level" onchange="autofillSubjects()" required>
                                <option value="">Select Class</option>
                                <option value="1">Plus One</option>
                                <option value="2">Plus Two</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-rupee-sign"></i>
                                Course Fee (₹)
                            </label>
                            <input type="number" step="0.01" name="price" placeholder="Enter course fee" required>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-image"></i>
                                Course Image
                            </label>
                            <div class="file-input-wrapper">
                                <input type="file" name="image" id="image" accept="image/*">
                                <label for="image" class="file-input-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    Choose Image File
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Course Description -->
                    <div class="form-group description-group">
                        <label>
                            <i class="fas fa-align-left"></i>
                            Course Description
                        </label>
                        <textarea name="description" placeholder="Enter detailed course description..." required></textarea>
                    </div>

                    <!-- Subjects & Teachers Section -->
                    <div class="subjects-section">
                        <h3>
                            <i class="fas fa-chalkboard-teacher"></i>
                            Subjects & Teachers Assignment
                        </h3>
                        <div class="subject-grid">
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <div class="subject-item">
                                    <h4>
                                        <i class="fas fa-book-open"></i>
                                        Subject <?= $i ?>
                                        <?php if ($i == 6): ?>
                                            <span class="elective-badge">ELECTIVE</span>
                                        <?php endif; ?>
                                    </h4>
                                    
                                    <div class="form-group">
                                        <label>
                                            <i class="fas fa-bookmark"></i>
                                            Subject
                                        </label>
                                        <select name="subject<?= $i ?>" id="subject<?= $i ?>" required>
                                            <option value="">Select Subject</option>
                                            <?php foreach ($subjects as $sub): ?>
                                                <option value="<?= $sub['subject_id'] ?>">
                                                    <?= $sub['subject_name'] ?> (<?= $sub['subject_id'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>
                                            <i class="fas fa-user-tie"></i>
                                            Teacher
                                        </label>
                                        <select name="teacher<?= $i ?>" required>
                                            <option value="">Select Teacher</option>
                                            <?php foreach ($teachers as $t): ?>
                                                <option value="<?= $t['user_id'] ?>">
                                                    <?= $t['name'] ?> (<?= $t['user_id'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="button-group">
                        <button type="submit" class="submit-btn" id="submitBtn">
                            <i class="fas fa-plus-circle"></i>
                            Add Course
                        </button>
                        <button type="button" class="cancel-btn" onclick="goBack()">
                            <i class="fas fa-arrow-left"></i>
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Success/Error Popup -->
    <div class="popup" id="messagePopup">
        <div class="popup-content">
            <div class="popup-icon" id="popupIcon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 id="popupTitle">Success!</h3>
            <p id="popupMessage">Course has been added successfully.</p>
            <button class="popup-btn" onclick="closePopup()">OK</button>
        </div>
    </div>

    <script>
        // Subject mapping for auto-fill functionality
        const subjectMap = {
            "Bio Science": ["BIO01", "CHE01", "PHY01", "MAT01"],
            "Computer Science": ["CSC01", "CHE01", "MAT01", "PHY01"],
            "Home Science": ["HSC01", "CHE01", "BIO01", "PHYO01"],
            "Humanities": ["HIS01", "ECO01", "POL01", "SOC01"],
            "Commerce": ["ACC01", "BST01", "ECO01", "MAT01"]
        };
           
        
        if (performance.navigation.type === performance.navigation.TYPE_RELOAD) {
    // Clear form data from sessionStorage if page was refreshed
    sessionStorage.removeItem('courseFormData');
        }
 
        // Auto-fill subjects based on stream and class level
        function autofillSubjects() {
            const stream = document.getElementById('stream').value;
            const level = document.getElementById('class_level').value;

            if (!stream || !level) return;

            const subjects = subjectMap[stream] || [];
            subjects.unshift("ENG01"); // English is always first

            // Fill first 5 subjects
            for (let i = 1; i <= 5; i++) {
                const subjectSelect = document.getElementById('subject' + i);
                if (subjects[i - 1] && subjectSelect) {
                    subjectSelect.value = subjects[i - 1];
                }
            }
        }

        // Popup functionality
        function showPopup(isSuccess, message) {
            const popup = document.getElementById('messagePopup');
            const icon = document.getElementById('popupIcon');
            const title = document.getElementById('popupTitle');
            const messageEl = document.getElementById('popupMessage');

            if (isSuccess) {
                icon.innerHTML = '<i class="fas fa-check-circle"></i>';
                icon.className = 'popup-icon success';
                title.textContent = 'Success!';
                messageEl.textContent = message || 'Course has been added successfully.';
            } else {
                icon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
                icon.className = 'popup-icon error';
                title.textContent = 'Error!';
                messageEl.textContent = message || 'An error occurred while adding the course.';
            }

            popup.classList.add('show');
        }

        function closePopup() {
            const popup = document.getElementById('messagePopup');
            popup.classList.remove('show');
        }

        // File input functionality
        document.getElementById('image').addEventListener('change', function(e) {
            const label = document.querySelector('.file-input-label');
            const fileName = e.target.files[0]?.name;
            
            if (fileName) {
                label.innerHTML = `<i class="fas fa-check"></i> ${fileName}`;
                label.classList.add('file-selected');
            } else {
                label.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Choose Image File';
                label.classList.remove('file-selected');
            }
        });

        // Form submission with loading state
        document.getElementById('courseForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.classList.add('loading');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding Course...';
            submitBtn.disabled = true;
        });

        // Navigation functions
        function goBack() {
            if (confirm('Are you sure you want to cancel? All unsaved changes will be lost.')) {
                window.location.href = "admin_dashboard.php";
            }
        }

        // Show popup based on PHP results
        <?php if ($success): ?>
            document.addEventListener('DOMContentLoaded', function() {
                showPopup(true, 'Course added successfully!');
            });
        <?php elseif ($error): ?>
            document.addEventListener('DOMContentLoaded', function() {
                showPopup(false, '<?= addslashes($error) ?>');
            });
        <?php endif; ?>

        // Close popup when clicking outside
        document.getElementById('messagePopup').addEventListener('click', function(e) {
            if (e.target === this) {
                closePopup();
            }
        });

        // Form validation enhancement
        function validateForm() {
            let isValid = true;
            const requiredFields = document.querySelectorAll('input[required], select[required], textarea[required]');
            
            requiredFields.forEach(field => {
                const formGroup = field.closest('.form-group');
                
                if (!field.value.trim()) {
                    formGroup.classList.add('error');
                    formGroup.classList.remove('success');
                    isValid = false;
                } else {
                    formGroup.classList.remove('error');
                    formGroup.classList.add('success');
                }
            });
            
            return isValid;
        }

        // Real-time validation
        document.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('blur', function() {
                const formGroup = this.closest('.form-group');
                
                if (this.hasAttribute('required')) {
                    if (!this.value.trim()) {
                        formGroup.classList.add('error');
                        formGroup.classList.remove('success');
                    } else {
                        formGroup.classList.remove('error');
                        formGroup.classList.add('success');
                    }
                }
            });
        });

        // Enhanced form submission
        document.getElementById('courseForm').addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                showPopup(false, 'Please fill in all required fields correctly.');
                return;
            }
        });

        // Smooth scroll to error fields
        function scrollToFirstError() {
            const firstError = document.querySelector('.form-group.error');
            if (firstError) {
                firstError.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }
        }

        // Auto-save form data to prevent loss
        function saveFormData() {
            const formData = new FormData(document.getElementById('courseForm'));
            const data = {};
            
            for (let [key, value] of formData.entries()) {
                if (key !== 'image') { // Don't save file input
                    data[key] = value;
                }
            }
            
            sessionStorage.setItem('courseFormData', JSON.stringify(data));
        }

        function loadFormData() {
            const savedData = sessionStorage.getItem('courseFormData');
            if (savedData) {
                const data = JSON.parse(savedData);
                
                Object.keys(data).forEach(key => {
                    const field = document.querySelector(`[name="${key}"]`);
                    if (field && field.type !== 'file') {
                        field.value = data[key];
                    }
                });
            }
        }

        // Load saved data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadFormData();
        });

        // Save data on form changes
        document.getElementById('courseForm').addEventListener('input', function() {
            saveFormData();
        });

        // Clear saved data on successful submission
        <?php if ($success): ?>
            sessionStorage.removeItem('courseFormData');
        <?php endif; ?>

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + S to save (submit form)
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                document.getElementById('courseForm').dispatchEvent(new Event('submit'));
            }
            
            // Escape to close popup
            if (e.key === 'Escape') {
                closePopup();
            }
        });

        // Enhanced mobile experience
        if (window.innerWidth <= 768) {
            // Adjust form behavior for mobile
            document.querySelectorAll('select').forEach(select => {
                select.addEventListener('focus', function() {
                    this.style.fontSize = '16px'; // Prevent zoom on iOS
                });
            });
        }

        // Progress indicator
        function updateProgress() {
            const totalFields = document.querySelectorAll('input[required], select[required], textarea[required]').length;
            const filledFields = document.querySelectorAll('input[required]:valid, select[required]:valid, textarea[required]:valid').length;
            const progress = Math.round((filledFields / totalFields) * 100);
            
            // You can add a progress bar here if needed
            console.log(`Form completion: ${progress}%`);
        }

        // Update progress on field changes
        document.addEventListener('input', updateProgress);
        document.addEventListener('change', updateProgress);
    </script>
</body>
</html>
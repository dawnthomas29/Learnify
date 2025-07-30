<?php
session_start();
require '../Databaseconnection.php';

// Handle delete request
if (isset($_GET['delete_course_id'])) {
    $course_id = $_GET['delete_course_id'];

    $conn->query("DELETE FROM course_teachers WHERE course_id = '$course_id'");
    $conn->query("DELETE FROM course_subject WHERE course_id = '$course_id'");
    $conn->query("DELETE FROM courses WHERE course_id = '$course_id'");

    header("Location: view-courses.php?deleted=1");
    exit;
}

// Fetch all courses
$courses = $conn->query("SELECT * FROM courses");

// Count number of teachers per course
$teacherCountQuery = $conn->query("SELECT course_id, COUNT(DISTINCT teacher_id) AS teacher_count FROM course_teachers GROUP BY course_id");
$teacherCountMap = [];
while ($row = $teacherCountQuery->fetch_assoc()) {
    $teacherCountMap[$row['course_id']] = $row['teacher_count'];
}

// Get statistics
$totalCourses = $courses->num_rows;
$courses->data_seek(0); // Reset pointer

// Count different streams
$streamQuery = $conn->query("SELECT stream, COUNT(*) as count FROM courses GROUP BY stream");
$streamStats = [];
while ($row = $streamQuery->fetch_assoc()) {
    $streamStats[$row['stream']] = $row['count'];
}

// Count total subjects
$totalSubjects = 0;
$courses->data_seek(0);
while ($row = $courses->fetch_assoc()) {
    for ($i = 1; $i <= 6; $i++) {
        if (!empty($row["subject$i"])) $totalSubjects++;
    }
}
$courses->data_seek(0); // Reset pointer again

// Count total teachers
$totalTeachersQuery = $conn->query("SELECT COUNT(DISTINCT teacher_id) as count FROM course_teachers");
$totalTeachers = $totalTeachersQuery->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - Learnify Admin</title>
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

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            margin-left: 100px; /* Reduced from 280px to 200px */
        }

        .page-header {
            background: white;
            border-radius: 4px;
            padding: 25px 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 5px solid #667eea;
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-title h1 {
            color: #333;
            font-size: 28px;
            font-weight: 600;
            margin: 0;
        }

        .page-title-icon {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .page-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
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

        .btn-outline {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            border: 2px solid rgba(102, 126, 234, 0.3);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 40px;
            margin-bottom: 30px;
            width: 900px; /* Limit width to push left */
        }

        .stat-card {
            background: white;
            border-radius: 4px;
            padding: 50px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            gap: 50px;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-title {
            color: #666;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 6px;
        }

        .stat-change {
            font-size: 12px;
            color: #28a745;
            font-weight: 500;
        }

        .content-section {
            background: white;
            border-radius: 4px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: none; /* Remove any width restrictions */
        }

        .section-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .search-container {
            position: relative;
            max-width: 350px;
        }

        .search-input {
            width: 100%;
            padding: 12px 20px 12px 45px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 25px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            backdrop-filter: blur(10px);
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.8);
        }

        .search-input:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.6);
            background: rgba(255, 255, 255, 0.2);
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.8);
            font-size: 16px;
        }

        .table-container {
            padding: 0;
        }

        .alert {
            margin: 20px 30px;
            padding: 15px 20px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.5s ease;
        }

        .alert-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border-left: 5px solid #1e7e34;
        }

        @keyframes slideIn {
            from { transform: translateX(-100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8f9fa;
            color: #495057;
            padding: 18px 20px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            border-bottom: 2px solid #e9ecef;
        }

        td {
            padding: 18px 20px;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.2s ease;
        }

        tr:hover {
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
        }

        tr:last-child td {
            border-bottom: none;
        }

        .course-id {
            font-weight: 600;
            color: #667eea;
            font-family: 'Courier New', monospace;
        }

        .stream-badge {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 8px 16px; /* Increased padding */
            border-radius: 20px;
            font-size: 11px; /* Slightly smaller font */
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: inline-block;
            white-space: nowrap; /* Prevent text wrapping */
            max-width: 120px; /* Set maximum width */
            overflow: hidden;
            text-overflow: ellipsis; /* Add ellipsis for overflow */
            line-height: 1.2;
        }

        .count-badge {
            background: #f8f9fa;
            color: #495057;
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: 600;
            font-size: 13px;
            border: 2px solid #e9ecef;
        }

        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin: 0 3px;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .edit-btn {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .delete-btn {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .no-results i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .fade-in {
            animation: fadeIn 0.6s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            .page-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .section-header {
                flex-direction: column;
                gap: 15px;
            }

            .search-container {
                max-width: 100%;
            }

            table {
                font-size: 14px;
            }

            th, td {
                padding: 12px 10px;
            }

            .action-btn {
                padding: 6px 10px;
                font-size: 11px;
            }

            .page-title h1 {
                font-size: 24px;
            }

            .stream-badge {
                font-size: 10px;
                padding: 6px 12px;
                max-width: 100px;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="dashboard-container">
        <main class="main-content fade-in">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <div class="page-title-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div>
                        <h1>Course Management</h1>
                        <p style="color: #666; font-size: 14px; margin: 5px 0 0 0;">Manage and organize your educational courses</p>
                    </div>
                </div>
                <div class="page-actions">
                    <a href="add-course.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add New Course
                    </a>
                    <button class="btn btn-outline" onclick="window.print()">
                        <i class="fas fa-print"></i>
                        Export
                    </button>
                </div>
            </div>

            <!-- Statistics Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Total Courses</span>
                        <div class="stat-icon">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?= $totalCourses ?></div>
                    <div class="stat-change">Active courses in system</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Total Subjects</span>
                        <div class="stat-icon">
                            <i class="fas fa-list"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?= $totalSubjects ?></div>
                    <div class="stat-change">Across all courses</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Total Teachers</span>
                        <div class="stat-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?= $totalTeachers ?></div>
                    <div class="stat-change">Assigned to courses</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Popular Stream</span>
                        <div class="stat-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                    </div>
                    <div class="stat-number">
                        <?php 
                        if (!empty($streamStats)) {
                            $maxStream = array_keys($streamStats, max($streamStats))[0];
                            echo substr($maxStream, 0, 8) . (strlen($maxStream) > 8 ? '...' : '');
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </div>
                    <div class="stat-change">Most enrolled stream</div>
                </div>
            </div>

            <!-- Success Message -->
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Course deleted successfully!
                </div>
            <?php endif; ?>

            <!-- Main Content Section -->
            <div class="content-section">
                <div class="section-header">
                    <div class="section-title">
                        <i class="fas fa-table"></i>
                        All Courses
                    </div>
                    <div class="search-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="searchInput" class="search-input" placeholder="Search courses..." onkeyup="filterCourses()">
                    </div>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th><i class="fas fa-id-card"></i> Course ID</th>
                                <th><i class="fas fa-stream"></i> Stream</th>
                                <th><i class="fas fa-layer-group"></i> Class Level</th>
                                <th><i class="fas fa-book"></i> Subjects</th>
                                <th><i class="fas fa-chalkboard-teacher"></i> Teachers</th>
                                <th><i class="fa-solid fa-indian-rupee-sign"></i> Price</th>
                                <th><i class="fas fa-cogs"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody id="courseTableBody">
                            <?php 
                            $hasRows = false;
                            while ($row = $courses->fetch_assoc()): 
                                $hasRows = true;
                            ?>
                                <?php
                                    $subjectCount = 0;
                                    for ($i = 1; $i <= 6; $i++) {
                                        if (!empty($row["subject$i"])) $subjectCount++;
                                    }
                                    $teacherCount = $teacherCountMap[$row['course_id']] ?? 0;
                                ?>
                                <tr>
                                    <td><span class="course-id"><?= htmlspecialchars($row['course_id']) ?></span></td>
                                    <td><span class="stream-badge" title="<?= htmlspecialchars($row['stream']) ?>"><?= htmlspecialchars($row['stream']) ?></span></td>
                                    <td><?= htmlspecialchars($row['class_level']) ?></td>
                                    <td><span class="count-badge"><?= $subjectCount ?></span></td>
                                    <td><span class="count-badge"><?= $teacherCount ?></span></td>
                                    <td><span class="count-badge">₹<?= htmlspecialchars($row['price']) ?></span></td>

                                    <td>
                                        <button class="action-btn edit-btn" onclick="window.location.href='Updatecourseform.php?course_id=<?= $row['course_id'] ?>'">
                                            <i class="fas fa-edit"></i> Update
                                        </button>
                                        <button class="action-btn delete-btn" onclick="showDeleteModal('<?= $row['course_id'] ?>')">>
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            
                            <?php if (!$hasRows): ?>
                                <tr>
                                    <td colspan="6" class="no-results">
                                        <i class="fas fa-graduation-cap"></i><br>
                                        <strong>No courses found</strong><br>
                                        <span style="color: #999;">Start by adding your first course</span>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Custom confirmation modal
        function showDeleteModal(course_id) {
            const modal = document.createElement('div');
            modal.id = 'deleteModal';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 10000;
                backdrop-filter: blur(5px);
                animation: fadeIn 0.3s ease;
            `;

            modal.innerHTML = `
                <div style="
                    background: white;
                    border-radius: 8px;
                    padding: 30px;
                    max-width: 450px;
                    width: 90%;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                    text-align: center;
                    animation: slideIn 0.3s ease;
                ">
                    <div style="
                        width: 60px;
                        height: 60px;
                        background: linear-gradient(135deg, #dc3545, #c82333);
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin: 0 auto 20px;
                        color: white;
                        font-size: 24px;
                    ">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    
                    <h3 style="
                        color: #333;
                        margin-bottom: 15px;
                        font-size: 22px;
                        font-weight: 600;
                    ">Delete Course?</h3>
                    
                    <p style="
                        color: #666;
                        margin-bottom: 25px;
                        line-height: 1.5;
                        font-size: 14px;
                    ">
                        This action cannot be undone and will permanently remove:
                        <br><br>
                        <span style="color: #dc3545; font-weight: 500;">
                            • Course subjects<br>
                            • Teacher assignments<br>
                            • Student enrollments
                        </span>
                    </p>
                    
                    <div style="display: flex; gap: 12px; justify-content: center;">
                        <button onclick="closeDeleteModal()" style="
                            padding: 12px 24px;
                            border: 2px solid #6c757d;
                            background: white;
                            color: #6c757d;
                            border-radius: 6px;
                            cursor: pointer;
                            font-weight: 500;
                            font-size: 14px;
                            transition: all 0.3s ease;
                        " onmouseover="this.style.background='#6c757d'; this.style.color='white';" 
                           onmouseout="this.style.background='white'; this.style.color='#6c757d';">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        
                        <button onclick="confirmDelete('${course_id}')" style="
                            padding: 12px 24px;
                            border: none;
                            background: linear-gradient(135deg, #dc3545, #c82333);
                            color: white;
                            border-radius: 6px;
                            cursor: pointer;
                            font-weight: 500;
                            font-size: 14px;
                            transition: all 0.3s ease;
                        " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(220, 53, 69, 0.3)';" 
                           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                            <i class="fas fa-trash"></i> Delete Course
                        </button>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            if (modal) {
                modal.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => {
                    document.body.removeChild(modal);
                }, 300);
            }
        }

        function confirmDelete(course_id) {
            closeDeleteModal();
            // Show loading state
            showLoading();
            // Redirect to delete
            setTimeout(() => {
                window.location.href = "view-courses.php?delete_course_id=" + course_id;
            }, 500);
        }

        function showLoading() {
            const loading = document.createElement('div');
            loading.id = 'loadingModal';
            loading.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 10001;
                backdrop-filter: blur(5px);
            `;

            loading.innerHTML = `
                <div style="
                    background: white;
                    border-radius: 8px;
                    padding: 30px;
                    text-align: center;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                ">
                    <div style="
                        width: 40px;
                        height: 40px;
                        border: 4px solid #f3f3f3;
                        border-top: 4px solid #dc3545;
                        border-radius: 50%;
                        animation: spin 1s linear infinite;
                        margin: 0 auto 15px;
                    "></div>
                    <p style="color: #666; margin: 0; font-weight: 500;">Deleting course...</p>
                </div>
            `;

            document.body.appendChild(loading);
        }

        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
            @keyframes slideIn {
                from { transform: translateY(-50px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);

        function filterCourses() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let tbody = document.getElementById("courseTableBody");
            let rows = tbody.querySelectorAll("tr");
            let visibleCount = 0;

            rows.forEach(row => {
                // Skip the "no results" row
                if (row.querySelector('.no-results')) {
                    return;
                }

                let match = false;
                row.querySelectorAll("td").forEach(cell => {
                    if (cell.textContent.toLowerCase().includes(input)) {
                        match = true;
                    }
                });
                
                if (match) {
                    row.style.display = "";
                    visibleCount++;
                } else {
                    row.style.display = "none";
                }
            });

            // Show/hide no results message
            let noResultsRow = tbody.querySelector('.no-results')?.parentElement;
            if (noResultsRow) {
                if (visibleCount === 0 && input !== "") {
                    noResultsRow.style.display = "";
                    noResultsRow.querySelector('.no-results').innerHTML = 
                        `<i class="fas fa-search"></i><br>
                         <strong>No results found for "${input}"</strong><br>
                         <span style="color: #999;">Try a different search term</span>`;
                } else {
                    noResultsRow.style.display = "none";
                }
            }
        }

        // Enhanced animations on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Animate statistics cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Animate table rows
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    row.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateX(0)';
                }, 800 + (index * 50));
            });
        });

        // Add smooth hover effects
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>
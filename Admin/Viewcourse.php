<?php
session_start();
require '../Databaseconnection.php';

// Fetch all courses
$courseQuery = "SELECT * FROM courses";
$courseResult = $conn->query($courseQuery);

// Build a map for subjects + teachers per course
$teachersMap = [];
$teacherQuery = "
    SELECT cs.course_id, cs.subject_id, s.subject_name, ct.teacher_id, u.name AS teacher_name
    FROM course_subject cs
    JOIN subjects s ON cs.subject_id = s.subject_id
    LEFT JOIN course_teachers ct ON cs.course_id = ct.course_id AND cs.subject_id = ct.subject_id
    LEFT JOIN users u ON ct.teacher_id = u.user_id
";
$teacherResult = $conn->query($teacherQuery);
while ($row = $teacherResult->fetch_assoc()) {
    $teachersMap[$row['course_id']][] = [
        'subject_id' => $row['subject_id'],
        'subject_name' => $row['subject_name'],
        'teacher_name' => $row['teacher_name'] ?? 'Not Assigned',
        'assigned' => !empty($row['teacher_id'])
    ];
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Courses - Learnify Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- jsPDF Library for PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
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

        /* Main content area - adjusted to work with sidebar */
        .main-content {
           
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .container {
            width: 100%;
            margin: 0;
            background: white;
            border-radius: 4px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* User Management Header */
        .user-management-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 25px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #3498db;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-left h2 {
            font-size: 1.8rem;
            font-weight: 600;
            margin: 0;
        }

        .cap-icon {
            font-size: 2rem;
            color: #3498db;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        .export-btn {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
            background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
        }

        .export-btn:active {
            transform: translateY(0);
        }

        .export-btn i {
            font-size: 1.1rem;
        }

        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px 40px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 300;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .content {
            padding: 40px;
        }

        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            gap: 20px;
        }

        .search-container {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-container i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 1.1rem;
        }

        .search-bar input {
            width: 100%;
            padding: 15px 20px 15px 45px;
            border: 2px solid #e1e8ed;
            border-radius: 4px;
            font-size: 1rem;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }

        .search-bar input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 8px 25px rgba(102,126,234,0.15);
        }

        .stats {
            display: flex;
            gap: 20px;
            align-items: center;
            color: #6c757d;
            font-size: 0.95rem;
        }

        .stats span {
            background: #f8f9fa;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 600;
        }

        .table-container {
            background: white;
            border-radius: 4px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
            border: 1px solid #e9ecef;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        td {
            padding: 20px 15px;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: top;
            transition: background-color 0.2s ease;
        }

        tr:hover td {
            background-color: #f8f9fa;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .course-id {
            font-family: 'Courier New', monospace;
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-block;
        }

        .course-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.05rem;
        }

        .stream-badge {
            
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;         
        }

        .class-level {
            
            padding: 8px 16px;
            border-radius: 25px;
            color: #495057;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-block;
        }

        .view-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .view-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102,126,234,0.3);
        }

        .view-btn i {
            font-size: 0.8rem;
        }

        .description-preview {
            max-width: 200px;
            color: #6c757d;
            font-size: 0.9rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .image-preview {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: transform 0.3s ease;
        }

        .image-preview:hover {
            transform: scale(1.1);
        }

        .no-image {
            width: 80px;
            height: 60px;
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 0.8rem;
            text-align: center;
        }

        .subjects-list {
            list-style: none;
            padding: 0;
            margin: 0;
            max-width: 350px;
        }

        .subjects-list li {
            background: #f8f9fa;
            margin-bottom: 8px;
            padding: 12px 15px;
            border-radius: 4px;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }

        .subjects-list li:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        .subject-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
        }

        .teacher-name {
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 4px;
        }

        .not-assigned {
            color: #dc3545;
            font-style: italic;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }

        .empty-state h3 {
            margin-bottom: 10px;
            color: #495057;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .loading i {
            font-size: 2rem;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 4px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideIn 0.3s ease;
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 5px;
            border-radius:4px;
            transition: background-color 0.3s ease;
        }

        .close:hover {
            background-color: rgba(255,255,255,0.2);
        }

        .modal-body {
            padding: 30px;
            max-height: 400px;
            overflow-y: auto;
        }

        .description-full {
            line-height: 1.6;
            color: #495057;
            font-size: 1rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Export loading animation */
        .export-loading {
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            
            .user-management-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-container {
                max-width: none;
            }
            
            .stats {
                justify-content: center;
                margin-top: 15px;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            table {
                min-width: 800px;
            }
            
            th, td {
                padding: 15px 10px;
            }
            
            .header h1 {
                font-size: 1.8rem;
                flex-direction: column;
                gap: 10px;
            }
        }

        /* Scrollbar Styling */
        .table-container::-webkit-scrollbar {
            height: 8px;
        }

        .table-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>   

    <!-- Main content area aligned with sidebar -->
    <div class="main-content">
        <div class="container">
            <!-- User Management Header -->
            <div class="user-management-header">
                <div class="header-left">
                    <i class="fas fa-graduation-cap fa-lg"></i>

                    <h2>User Management</h2>
                </div>
                <button class="export-btn" onclick="exportToPDF()" id="exportBtn">
                    <i class="fas fa-file-pdf"></i>
                    Export
                </button>
            </div>

            <div class="header">
                <h1>
                    <span><i class="fas fa-graduation-cap"></i> 
                    Course Management</span>
                </h1>

                <p>View and manage all academic courses in the system</p>
            </div>

            <div class="content">
                <div class="controls">
                    <div class="search-container">
                        <div class="search-bar">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Search courses by name, stream, ID, or teacher...">
                        </div>
                    </div>
                    <div class="stats">
                        <span id="totalCourses"><i class="fas fa-book"></i> Total: 0</span>
                        <span id="visibleCourses"><i class="fas fa-eye"></i> Showing: 0</span>
                    </div>
                </div>

                <div class="table-container">
                    <table id="courseTable">
                        <thead>
                            <tr>
                                <th><i class="fas fa-id-badge"></i> Course ID</th>
                                <th><i class="fas fa-book"></i> Course Name</th>
                                <th><i class="fas fa-stream"></i> Stream</th>
                                <th><i class="fas fa-layer-group"></i> Class Level</th>
                                <th><i class="fa-solid fa-indian-rupee-sign"></i> Price</th>
                                <th><i class="fas fa-align-left"></i> Description</th>
                                <th><i class="fas fa-image"></i> Image</th>
                                <th><i class="fas fa-chalkboard-teacher"></i> Subjects & Teachers</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $courseCount = 0;
                            while ($row = $courseResult->fetch_assoc()): 
                                $courseCount++;
                                $streamClass = 'stream-' . strtolower(str_replace(' ', '', $row['stream']));
                            ?>
                                <tr>
                                    <td>
                                        <span class="course-id"><?= htmlspecialchars($row['course_id']) ?></span>
                                    </td>
                                    <td>
                                        <div class="course-name"><?= htmlspecialchars($row['course_name']) ?></div>
                                    </td>
                                    <td>
                                        <span class="stream-badge <?= $streamClass ?>"><?= htmlspecialchars($row['stream']) ?></span>
                                    </td>
                                    <td>
                                        <span class="class-level"><?= htmlspecialchars($row['class_level']) ?></span>
                                    </td>
                                    <td>
                                        <span class="class-level"><?= htmlspecialchars($row['price']) ?></span>
                                    </td>
                                    <td>
                                        <div class="description-preview"><?= htmlspecialchars(substr($row['description'], 0, 100)) ?><?= strlen($row['description']) > 100 ? '...' : '' ?></div>
                                        <button class="view-btn" onclick="viewDescription('<?= htmlspecialchars($row['course_id']) ?>')">
                                            <i class="fas fa-eye"></i> View Full
                                        </button>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['image'])): ?>
                                            <img src="<?= htmlspecialchars($row['image']) ?>" class="image-preview" alt="Course Image">
                                        <?php else: ?>
                                            <div class="no-image">
                                                <i class="fas fa-image"></i><br>No Image
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="view-btn" onclick="viewSubjects('<?= htmlspecialchars($row['course_id']) ?>')">
                                            <i class="fas fa-list"></i> View Subjects
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            
                            <?php if ($courseCount == 0): ?>
                                <tr>
                                    <td colspan="8">
                                        <div class="empty-state">
                                            <i class="fas fa-graduation-cap"></i>
                                            <h3>No Courses Found</h3>
                                            <p>There are no courses in the system yet.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for viewing full details -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Course Details</h3>
                <button class="close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        // Store course data for modal display and PDF export
        const courseData = {
    <?php 
    $courseResult = $conn->query($courseQuery); // Re-run query for JavaScript data
    $jsData = [];
    while ($row = $courseResult->fetch_assoc()) {
        $subjectsData = [];
        if (!empty($teachersMap[$row['course_id']])) {
            foreach ($teachersMap[$row['course_id']] as $entry) {
                $subjectsData[] = [
                    'name' => $entry['subject_name'],
                    'teacher' => $entry['teacher_name'],
                    'assigned' => $entry['assigned']
                ];
            }
        }

        $jsData[] = '"' . $row['course_id'] . '": {
            "course_id": ' . json_encode($row['course_id']) . ',
            "course_name": ' . json_encode($row['course_name']) . ',
            "stream": ' . json_encode($row['stream']) . ',
            "class_level": ' . json_encode($row['class_level']) . ',
            "price": ' . json_encode($row['price']) . ',
            "description": ' . json_encode($row['description']) . ',
            "subjects": ' . json_encode($subjectsData) . '
        }';
    }
    echo implode(',', $jsData);
    ?>
};


        function viewDescription(courseId) {
            const course = courseData[courseId];
            if (course) {
                document.getElementById('modalTitle').innerHTML = '<i class="fas fa-align-left"></i> Course Description';
                document.getElementById('modalBody').innerHTML = '<div class="description-full">' + course.description.replace(/\n/g, '<br>') + '</div>';
                document.getElementById('detailModal').style.display = 'block';
            }
        }

        function viewSubjects(courseId) {
            const course = courseData[courseId];
            if (course) {
                document.getElementById('modalTitle').innerHTML = '<i class="fas fa-chalkboard-teacher"></i> Subjects & Teachers';
                
                let subjectsHtml = '<ul class="subjects-list">';
                course.subjects.forEach(subject => {
                    subjectsHtml += '<li>';
                    subjectsHtml += '<div class="subject-name">' + subject.name + '</div>';
                    if (subject.assigned) {
                        subjectsHtml += '<div class="teacher-name"><i class="fas fa-user"></i> ' + subject.teacher + '</div>';
                    } else {
                        subjectsHtml += '<div class="teacher-name not-assigned"><i class="fas fa-exclamation-triangle"></i> ' + subject.teacher + '</div>';
                    }
                    subjectsHtml += '</li>';
                });
                subjectsHtml += '</ul>';
                
                document.getElementById('modalBody').innerHTML = subjectsHtml;
                document.getElementById('detailModal').style.display = 'block';
            }
        }

        function closeModal() {
            document.getElementById('detailModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('detailModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        // Export to PDF function
        function exportToPDF() {
            const exportBtn = document.getElementById('exportBtn');
            const originalText = exportBtn.innerHTML;
            
            // Show loading state
            exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';
            exportBtn.classList.add('export-loading');
            exportBtn.disabled = true;

            // Create jsPDF instance
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4'); // Landscape orientation

            // Add title
            doc.setFontSize(20);
            doc.setTextColor(44, 62, 80);
            doc.text('Course Management Report', 20, 20);

            // Add subtitle
            doc.setFontSize(12);
            doc.setTextColor(108, 117, 125);
            doc.text('Generated on: ' + new Date().toLocaleDateString(), 20, 30);

            // Prepare table data
            const tableData = [];
            const visibleRows = document.querySelectorAll("#courseTable tbody tr:not([style*='display: none'])");
            
            visibleRows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 5) { // Make sure it's not the empty state row
                    const courseId = cells[0].textContent.trim();
                    const courseName = cells[1].textContent.trim();
                    const stream = cells[2].textContent.trim();
                    const classLevel = cells[3].textContent.trim();
                    const price = cells[4].textContent.trim();
                    
                    // Get subjects from courseData
                    let subjects = 'N/A';
                    if (courseData[courseId] && courseData[courseId].subjects) {
                        subjects = courseData[courseId].subjects
                            .map(sub => sub.name + ' (' + sub.teacher + ')')
                            .join(', ');
                        if (subjects.length > 50) {
                            subjects = subjects.substring(0, 50) + '...';
                        }
                    }

                    tableData.push([
                        courseId,
                        courseName,
                        stream,
                        classLevel,
                        price,
                        subjects
                    ]);
                }
            });

            // Create the table
            doc.autoTable({
                head: [['Course ID', 'Course Name', 'Stream', 'Class Level', 'Price', 'Subjects & Teachers']],
                body: tableData,
                startY: 40,
                theme: 'grid',
                headStyles: {
                    fillColor: [102, 126, 234],
                    textColor: [255, 255, 255],
                    fontStyle: 'bold',
                    fontSize: 10
                },
                bodyStyles: {
                    fontSize: 9,
                    cellPadding: 3
                },
                alternateRowStyles: {
                    fillColor: [248, 249, 250]
                },
                columnStyles: {
                    0: { cellWidth: 25 },
                    1: { cellWidth: 40 },
                    2: { cellWidth: 25 },
                    3: { cellWidth: 25 },
                    4: { cellWidth: 20 },
                    5: { cellWidth: 60 }
                },
                margin: { top: 40, right: 20, bottom: 20, left: 20 },
                didDrawPage: function (data) {
                    // Add page footer
                    doc.setFontSize(8);
                    doc.setTextColor(128, 128, 128);
                    doc.text('Page ' + data.pageNumber, data.settings.margin.left, doc.internal.pageSize.height - 10);
                    doc.text('Learnify Admin - Course Management System', doc.internal.pageSize.width - 80, doc.internal.pageSize.height - 10);
                }
            });

            // Add summary at the bottom
            const finalY = doc.lastAutoTable.finalY + 20;
            doc.setFontSize(12);
            doc.setTextColor(44, 62, 80);
            doc.text('Summary:', 20, finalY);
            doc.setFontSize(10);
            doc.setTextColor(108, 117, 125);
            doc.text(`Total Courses: ${tableData.length}`, 20, finalY + 10);
            doc.text(`Export Date: ${new Date().toLocaleString()}`, 20, finalY + 20);

            // Save the PDF
            const fileName = `Course_Management_Report_${new Date().toISOString().split('T')[0]}.pdf`;
            doc.save(fileName);

            // Reset button state
            setTimeout(() => {
                exportBtn.innerHTML = originalText;
                exportBtn.classList.remove('export-loading');
                exportBtn.disabled = false;
                
                // Show success message
                showNotification('PDF exported successfully!', 'success');
            }, 1000);
        }

        // Show notification function
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#28a745' : '#17a2b8'};
                color: white;
                padding: 15px 20px;
                border-radius: 5px;
                z-index: 9999;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                font-weight: 600;
                animation: slideInRight 0.3s ease;
            `;
            notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i> ${message}`;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Add animation keyframes for notifications
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOutRight {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);

        // Update course counts
        function updateStats() {
            const totalRows = document.querySelectorAll("#courseTable tbody tr").length;
            const visibleRows = document.querySelectorAll("#courseTable tbody tr:not([style*='display: none'])").length;
            
            document.getElementById("totalCourses").innerHTML = `<i class="fas fa-book"></i> Total: ${totalRows}`;
            document.getElementById("visibleCourses").innerHTML = `<i class="fas fa-eye"></i> Showing: ${visibleRows}`;
        }

        // Initialize stats
        updateStats();

        // Enhanced search functionality
        document.getElementById("searchInput").addEventListener("keyup", function () {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll("#courseTable tbody tr");
            
            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                if (text.includes(filter)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
            
            updateStats();
        });

        // Add loading animation on page load
        window.addEventListener('load', function() {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.3s ease';
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
        });

        // Keyboard shortcut for export (Ctrl+E)
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'e') {
                e.preventDefault();
                exportToPDF();
            }
        });
    </script>
</body>
</html>
<?php
session_start();
include 'db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Get all public lectures
$lectures_sql = "SELECT l.*, u.name as teacher_name FROM lectures l 
                 JOIN users u ON l.uploaded_by = u.id 
                 WHERE l.is_public = 1 
                 ORDER BY l.upload_date DESC";
$lectures_stmt = $conn->prepare($lectures_sql);
$lectures_stmt->execute();
$lectures = $lectures_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle download
if (isset($_GET['download']) && is_numeric($_GET['download'])) {
    $lecture_id = $_GET['download'];
    
    // Get lecture details
    $lecture_sql = "SELECT * FROM lectures WHERE id = ? AND is_public = 1";
    $lecture_stmt = $conn->prepare($lecture_sql);
    $lecture_stmt->bind_param("i", $lecture_id);
    $lecture_stmt->execute();
    $lecture = $lecture_stmt->get_result()->fetch_assoc();
    
    if ($lecture && file_exists($lecture['file_path'])) {
        // Update download count
        $update_sql = "UPDATE lectures SET download_count = download_count + 1 WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $lecture_id);
        $update_stmt->execute();
        
        // Download file
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $lecture['file_name'] . '"');
        header('Content-Length: ' . filesize($lecture['file_path']));
        readfile($lecture['file_path']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Lectures - FocusBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }
        .main-content {
            background-color: #f8fafc;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .file-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        .file-pdf { background-color: #dc2626; }
        .file-doc { background-color: #2563eb; }
        .file-ppt { background-color: #ea580c; }
        .file-txt { background-color: #6b7280; }
        .file-video { background-color: #7c3aed; }
        .lecture-card {
            transition: transform 0.2s ease;
        }
        .lecture-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-4">
                    <h4 class="text-white mb-4">
                        <i class="ri-graduation-cap-line me-2"></i>
                        FocusBridge
                    </h4>
                </div>
                
                <nav class="nav flex-column px-3">
                    <?php if ($user_role === 'teacher'): ?>
                        <a class="nav-link" href="teacher_dashboard.php">
                            <i class="ri-dashboard-line me-2"></i> Dashboard
                        </a>
                        <a class="nav-link" href="attendance_sheet.php">
                            <i class="ri-calendar-check-line me-2"></i> Attendance Sheet
                        </a>
                        <a class="nav-link" href="teacher_work_tracker.php">
                            <i class="ri-time-line me-2"></i> Work Tracker
                        </a>
                        <a class="nav-link" href="lecture_upload.php">
                            <i class="ri-upload-line me-2"></i> Lecture Upload
                        </a>
                        <a class="nav-link" href="assignment_upload.php">
                            <i class="ri-file-text-line me-2"></i> Assignment Upload
                        </a>
                        <a class="nav-link active" href="view_lectures.php">
                            <i class="ri-eye-line me-2"></i> View Lectures
                        </a>
                        <a class="nav-link" href="view_assignments.php">
                            <i class="ri-file-list-line me-2"></i> View Assignments
                        </a>
                    <?php else: ?>
                        <a class="nav-link" href="<?php echo $user_role === 'admin' ? 'admin_dashboard.php' : 'student_dashboard.php'; ?>">
                            <i class="ri-dashboard-line me-2"></i> Dashboard
                        </a>
                        <a class="nav-link" href="view_lectures.php">
                            <i class="ri-eye-line me-2"></i> View Lectures
                        </a>
                        <a class="nav-link" href="view_assignments.php">
                            <i class="ri-file-list-line me-2"></i> View Assignments
                        </a>
                        <a class="nav-link" href="view_notes.php">
                            <i class="ri-file-line me-2"></i> View Notes
                        </a>
                        <a class="nav-link" href="exams.php">
                            <i class="ri-calendar-line me-2"></i> Exams
                        </a>
                        <a class="nav-link" href="tracker.php">
                            <i class="ri-time-line me-2"></i> Work Tracker
                        </a>
                    <?php endif; ?>
                    <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
                    <a class="nav-link" href="logout.php">
                        <i class="ri-logout-box-line me-2"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Available Lectures</h2>
                    <div class="text-muted">
                        <i class="ri-book-open-line me-1"></i>
                        <?php echo count($lectures); ?> lectures available
                    </div>
                </div>

                <!-- Search and Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ri-search-line"></i></span>
                                    <input type="text" class="form-control" id="searchInput" placeholder="Search lectures...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <select class="form-select" id="subjectFilter">
                                    <option value="">All Subjects</option>
                                    <?php
                                    $subjects = array_unique(array_column($lectures, 'subject'));
                                    foreach ($subjects as $subject):
                                    ?>
                                        <option value="<?php echo htmlspecialchars($subject); ?>"><?php echo htmlspecialchars($subject); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lectures Grid -->
                <div class="row" id="lecturesGrid">
                    <?php if (empty($lectures)): ?>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <i class="ri-book-open-line fs-1 text-muted mb-3"></i>
                                    <h5 class="text-muted">No lectures available</h5>
                                    <p class="text-muted">Check back later for new lecture materials.</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($lectures as $lecture): ?>
                            <div class="col-md-6 col-lg-4 mb-4 lecture-card" data-subject="<?php echo htmlspecialchars($lecture['subject']); ?>" data-title="<?php echo htmlspecialchars(strtolower($lecture['title'])); ?>">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="file-icon file-<?php echo $lecture['file_type']; ?> me-3">
                                                <?php
                                                switch($lecture['file_type']) {
                                                    case 'pdf': echo '<i class="ri-file-pdf-line"></i>'; break;
                                                    case 'doc':
                                                    case 'docx': echo '<i class="ri-file-word-line"></i>'; break;
                                                    case 'ppt':
                                                    case 'pptx': echo '<i class="ri-file-ppt-line"></i>'; break;
                                                    case 'txt': echo '<i class="ri-file-text-line"></i>'; break;
                                                    case 'mp4':
                                                    case 'avi':
                                                    case 'mov': echo '<i class="ri-video-line"></i>'; break;
                                                    default: echo '<i class="ri-file-line"></i>';
                                                }
                                                ?>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="card-title mb-1"><?php echo htmlspecialchars($lecture['title']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($lecture['subject']); ?></small>
                                            </div>
                                        </div>
                                        
                                        <?php if ($lecture['description']): ?>
                                            <p class="card-text text-muted small"><?php echo htmlspecialchars($lecture['description']); ?></p>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <small class="text-muted">
                                                <i class="ri-user-line me-1"></i>
                                                <?php echo htmlspecialchars($lecture['teacher_name']); ?>
                                            </small>
                                            <small class="text-muted">
                                                <i class="ri-download-line me-1"></i>
                                                <?php echo $lecture['download_count']; ?>
                                            </small>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <?php echo date('M j, Y', strtotime($lecture['upload_date'])); ?>
                                            </small>
                                            <a href="?download=<?php echo $lecture['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="ri-download-line me-1"></i> Download
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const cards = document.querySelectorAll('.lecture-card');
            
            cards.forEach(card => {
                const title = card.getAttribute('data-title');
                if (title.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Subject filter
        document.getElementById('subjectFilter').addEventListener('change', function() {
            const selectedSubject = this.value;
            const cards = document.querySelectorAll('.lecture-card');
            
            cards.forEach(card => {
                const subject = card.getAttribute('data-subject');
                if (selectedSubject === '' || subject === selectedSubject) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
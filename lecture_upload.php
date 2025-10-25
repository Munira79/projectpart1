<?php
session_start();
include 'db_config.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: login.php?role=teacher");
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'upload_lecture') {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $subject = $_POST['subject'];
        
        // Handle file upload
        if (isset($_FILES['lecture_file']) && $_FILES['lecture_file']['error'] == 0) {
            $file = $_FILES['lecture_file'];
            $file_name = $file['name'];
            $file_tmp = $file['tmp_name'];
            $file_size = $file['size'];
            $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Allowed file types
            $allowed_types = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'mp4', 'avi', 'mov'];
            
            if (in_array($file_type, $allowed_types)) {
                // Create uploads directory if it doesn't exist
                $upload_dir = 'uploads/lectures/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Generate unique filename
                $unique_filename = uniqid() . '_' . $file_name;
                $file_path = $upload_dir . $unique_filename;
                
                if (move_uploaded_file($file_tmp, $file_path)) {
                    // Insert into database
                    $sql = "INSERT INTO lectures (title, description, subject, file_path, file_name, file_type, file_size, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssssii", $title, $description, $subject, $file_path, $file_name, $file_type, $file_size, $teacher_id);
                    
                    if ($stmt->execute()) {
                        $_SESSION['success'] = "Lecture uploaded successfully!";
                    } else {
                        $_SESSION['error'] = "Failed to save lecture information. Please try again.";
                    }
                } else {
                    $_SESSION['error'] = "Failed to upload file. Please try again.";
                }
            } else {
                $_SESSION['error'] = "Invalid file type. Allowed types: " . implode(', ', $allowed_types);
            }
        } else {
            $_SESSION['error'] = "Please select a file to upload.";
        }
        
        header("Location: lecture_upload.php");
        exit();
    }
}

// Get teacher's uploaded lectures
$lectures_sql = "SELECT * FROM lectures WHERE uploaded_by = ? ORDER BY upload_date DESC";
$lectures_stmt = $conn->prepare($lectures_sql);
$lectures_stmt->bind_param("i", $teacher_id);
$lectures_stmt->execute();
$lectures = $lectures_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecture Upload - FocusBridge</title>
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
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }
        .file-pdf { background-color: #dc2626; }
        .file-doc { background-color: #2563eb; }
        .file-ppt { background-color: #ea580c; }
        .file-txt { background-color: #6b7280; }
        .file-video { background-color: #7c3aed; }
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
                    <a class="nav-link" href="teacher_dashboard.php">
                        <i class="ri-dashboard-line me-2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="attendance_sheet.php">
                        <i class="ri-calendar-check-line me-2"></i> Attendance Sheet
                    </a>
                    <a class="nav-link" href="teacher_work_tracker.php">
                        <i class="ri-time-line me-2"></i> Work Tracker
                    </a>
                    <a class="nav-link active" href="lecture_upload.php">
                        <i class="ri-upload-line me-2"></i> Lecture Upload
                    </a>
                    <a class="nav-link" href="assignment_upload.php">
                        <i class="ri-file-text-line me-2"></i> Assignment Upload
                    </a>
                    <a class="nav-link" href="view_lectures.php">
                        <i class="ri-eye-line me-2"></i> View Lectures
                    </a>
                    <a class="nav-link" href="view_assignments.php">
                        <i class="ri-file-list-line me-2"></i> View Assignments
                    </a>
                    <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
                    <a class="nav-link" href="logout.php">
                        <i class="ri-logout-box-line me-2"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Lecture Upload</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="ri-upload-line me-1"></i> Upload New Lecture
                    </button>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Upload Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Upload New Lecture</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="upload_lecture">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="title" class="form-label">Lecture Title</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="lecture_file" class="form-label">Lecture File</label>
                                <input type="file" class="form-control" id="lecture_file" name="lecture_file" required>
                                <div class="form-text">Allowed file types: PDF, DOC, DOCX, PPT, PPTX, TXT, MP4, AVI, MOV</div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-upload-line me-1"></i> Upload Lecture
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Uploaded Lectures -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Your Uploaded Lectures</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($lectures)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="ri-upload-line fs-1 mb-3"></i>
                                <p>No lectures uploaded yet</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>File</th>
                                            <th>Title</th>
                                            <th>Subject</th>
                                            <th>Description</th>
                                            <th>Size</th>
                                            <th>Upload Date</th>
                                            <th>Downloads</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($lectures as $lecture): ?>
                                            <tr>
                                                <td>
                                                    <div class="file-icon file-<?php echo $lecture['file_type']; ?>">
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
                                                </td>
                                                <td><?php echo htmlspecialchars($lecture['title']); ?></td>
                                                <td><?php echo htmlspecialchars($lecture['subject']); ?></td>
                                                <td><?php echo htmlspecialchars($lecture['description']); ?></td>
                                                <td><?php echo number_format($lecture['file_size'] / 1024, 1); ?> KB</td>
                                                <td><?php echo date('M j, Y', strtotime($lecture['upload_date'])); ?></td>
                                                <td><?php echo $lecture['download_count']; ?></td>
                                                <td>
                                                    <a href="<?php echo $lecture['file_path']; ?>" class="btn btn-sm btn-outline-primary" download>
                                                        <i class="ri-download-line"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
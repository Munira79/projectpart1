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
    if (isset($_POST['action']) && $_POST['action'] == 'upload_assignment') {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $subject = $_POST['subject'];
        $due_date = $_POST['due_date'];
        $max_marks = $_POST['max_marks'];
        
        // Handle file upload
        if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] == 0) {
            $file = $_FILES['assignment_file'];
            $file_name = $file['name'];
            $file_tmp = $file['tmp_name'];
            $file_size = $file['size'];
            $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Allowed file types
            $allowed_types = ['pdf', 'doc', 'docx', 'txt'];
            
            if (in_array($file_type, $allowed_types)) {
                // Create uploads directory if it doesn't exist
                $upload_dir = 'uploads/assignments/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Generate unique filename
                $unique_filename = uniqid() . '_' . $file_name;
                $file_path = $upload_dir . $unique_filename;
                
                if (move_uploaded_file($file_tmp, $file_path)) {
                    // Insert into database
                    $sql = "INSERT INTO assignments (title, description, subject, file_path, file_name, file_type, file_size, due_date, max_marks, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssssisii", $title, $description, $subject, $file_path, $file_name, $file_type, $file_size, $due_date, $max_marks, $teacher_id);
                    
                    if ($stmt->execute()) {
                        $_SESSION['success'] = "Assignment uploaded successfully!";
                    } else {
                        $_SESSION['error'] = "Failed to save assignment information. Please try again.";
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
        
        header("Location: assignment_upload.php");
        exit();
    }
}

// Get teacher's uploaded assignments
$assignments_sql = "SELECT * FROM assignments WHERE uploaded_by = ? ORDER BY upload_date DESC";
$assignments_stmt = $conn->prepare($assignments_sql);
$assignments_stmt->bind_param("i", $teacher_id);
$assignments_stmt->execute();
$assignments = $assignments_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Upload - FocusBridge</title>
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
        .file-txt { background-color: #6b7280; }
        .due-date {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .due-soon { background-color: #fef3c7; color: #92400e; }
        .due-overdue { background-color: #fecaca; color: #991b1b; }
        .due-future { background-color: #d1fae5; color: #065f46; }
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
                    <a class="nav-link" href="lecture_upload.php">
                        <i class="ri-upload-line me-2"></i> Lecture Upload
                    </a>
                    <a class="nav-link active" href="assignment_upload.php">
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
                    <h2 class="mb-0">Assignment Upload</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="ri-upload-line me-1"></i> Upload New Assignment
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
                        <h5 class="mb-0">Upload New Assignment</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="upload_assignment">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="title" class="form-label">Assignment Title</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="due_date" class="form-label">Due Date</label>
                                    <input type="date" class="form-control" id="due_date" name="due_date" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="max_marks" class="form-label">Maximum Marks</label>
                                    <input type="number" class="form-control" id="max_marks" name="max_marks" min="1" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="assignment_file" class="form-label">Assignment File</label>
                                <input type="file" class="form-control" id="assignment_file" name="assignment_file" required>
                                <div class="form-text">Allowed file types: PDF, DOC, DOCX, TXT</div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-upload-line me-1"></i> Upload Assignment
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Uploaded Assignments -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Your Uploaded Assignments</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($assignments)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="ri-file-text-line fs-1 mb-3"></i>
                                <p>No assignments uploaded yet</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>File</th>
                                            <th>Title</th>
                                            <th>Subject</th>
                                            <th>Due Date</th>
                                            <th>Max Marks</th>
                                            <th>Size</th>
                                            <th>Upload Date</th>
                                            <th>Downloads</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($assignments as $assignment): ?>
                                            <?php
                                            $due_date = new DateTime($assignment['due_date']);
                                            $today = new DateTime();
                                            $diff = $today->diff($due_date);
                                            
                                            if ($due_date < $today) {
                                                $due_class = 'due-overdue';
                                                $due_text = 'Overdue';
                                            } elseif ($diff->days <= 3) {
                                                $due_class = 'due-soon';
                                                $due_text = 'Due Soon';
                                            } else {
                                                $due_class = 'due-future';
                                                $due_text = 'Future';
                                            }
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="file-icon file-<?php echo $assignment['file_type']; ?>">
                                                        <?php
                                                        switch($assignment['file_type']) {
                                                            case 'pdf': echo '<i class="ri-file-pdf-line"></i>'; break;
                                                            case 'doc':
                                                            case 'docx': echo '<i class="ri-file-word-line"></i>'; break;
                                                            case 'txt': echo '<i class="ri-file-text-line"></i>'; break;
                                                            default: echo '<i class="ri-file-line"></i>';
                                                        }
                                                        ?>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                                <td><?php echo htmlspecialchars($assignment['subject']); ?></td>
                                                <td>
                                                    <span class="due-date <?php echo $due_class; ?>">
                                                        <?php echo date('M j, Y', strtotime($assignment['due_date'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $assignment['max_marks']; ?></td>
                                                <td><?php echo number_format($assignment['file_size'] / 1024, 1); ?> KB</td>
                                                <td><?php echo date('M j, Y', strtotime($assignment['upload_date'])); ?></td>
                                                <td><?php echo $assignment['download_count']; ?></td>
                                                <td>
                                                    <a href="<?php echo $assignment['file_path']; ?>" class="btn btn-sm btn-outline-primary" download>
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

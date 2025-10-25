<?php
session_start();
include 'db_config.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: login.php?role=teacher");
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Get teacher info
$teacher_sql = "SELECT name, email, student_id FROM users WHERE id = ?";
$teacher_stmt = $conn->prepare($teacher_sql);
$teacher_stmt->bind_param("i", $teacher_id);
$teacher_stmt->execute();
$teacher_result = $teacher_stmt->get_result();
$teacher = $teacher_result->fetch_assoc();

// Get recent lectures count
$lectures_sql = "SELECT COUNT(*) as count FROM lectures WHERE uploaded_by = ?";
$lectures_stmt = $conn->prepare($lectures_sql);
$lectures_stmt->bind_param("i", $teacher_id);
$lectures_stmt->execute();
$lectures_result = $lectures_stmt->get_result();
$lectures_count = $lectures_result->fetch_assoc()['count'];

// Get recent assignments count
$assignments_sql = "SELECT COUNT(*) as count FROM assignments WHERE uploaded_by = ?";
$assignments_stmt = $conn->prepare($assignments_sql);
$assignments_stmt->bind_param("i", $teacher_id);
$assignments_stmt->execute();
$assignments_result = $assignments_stmt->get_result();
$assignments_count = $assignments_result->fetch_assoc()['count'];

// Get recent work sessions count
$work_sql = "SELECT COUNT(*) as count FROM teacher_work_sessions WHERE teacher_id = ? AND session_date = CURDATE()";
$work_stmt = $conn->prepare($work_sql);
$work_stmt->bind_param("i", $teacher_id);
$work_stmt->execute();
$work_result = $work_stmt->get_result();
$work_count = $work_result->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - FocusBridge</title>
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
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            transition: transform 0.2s ease;
        }
        .stats-card:hover {
            transform: translateY(-2px);
        }
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .feature-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        footer {
      background: var(--footer-bg);
      padding: 20px 0;
      text-align: center;
      color: var(--footer-text);
      transition: background-color 0.3s ease, color 0.3s ease;
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
                    <div class="text-center mb-4">
                        <div class="bg-white bg-opacity-20 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="ri-user-line fs-3"></i>
                        </div>
                        <h6 class="mt-2 mb-0"><?php echo htmlspecialchars($teacher['name']); ?></h6>
                        <small class="text-white-50">Teacher</small>
                    </div>
                </div>
                
                <nav class="nav flex-column px-3">
                    <a class="nav-link" href="profile.php">
                        <i class="ri-file-list-line me-2"></i> Profile
                    </a>
                    <a class="nav-link active" href="teacher_dashboard.php">
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
                    <h2 class="mb-0">Teacher Dashboard</h2>
                    <div class="text-muted">
                        <i class="ri-calendar-line me-1"></i>
                        <?php echo date('F j, Y'); ?>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon me-3" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                                    <i class="ri-upload-line"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0"><?php echo $lectures_count; ?></h3>
                                    <p class="text-muted mb-0">Lectures Uploaded</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon me-3" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                                    <i class="ri-file-text-line"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0"><?php echo $assignments_count; ?></h3>
                                    <p class="text-muted mb-0">Assignments Uploaded</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon me-3" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                                    <i class="ri-time-line"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0"><?php echo $work_count; ?></h3>
                                    <p class="text-muted mb-0">Work Sessions Today</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon me-3" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                                    <i class="ri-calendar-check-line"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0">0</h3>
                                    <p class="text-muted mb-0">Attendance Records</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-12">
                        <h4 class="mb-3">Quick Actions</h4>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="feature-card" onclick="location.href='attendance_sheet.php'">
                            <div class="text-center">
                                <div class="stats-icon mx-auto mb-3" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                                    <i class="ri-calendar-check-line"></i>
                                </div>
                                <h5>Attendance Sheet</h5>
                                <p class="text-muted">Manage student attendance and export to Excel</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="feature-card" onclick="location.href='teacher_work_tracker.php'">
                            <div class="text-center">
                                <div class="stats-icon mx-auto mb-3" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                                    <i class="ri-time-line"></i>
                                </div>
                                <h5>Work Tracker</h5>
                                <p class="text-muted">Track your teaching activities and time</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="feature-card" onclick="location.href='lecture_upload.php'">
                            <div class="text-center">
                                <div class="stats-icon mx-auto mb-3" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                                    <i class="ri-upload-line"></i>
                                </div>
                                <h5>Upload Lecture</h5>
                                <p class="text-muted">Upload lecture materials for students</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="feature-card" onclick="location.href='assignment_upload.php'">
                            <div class="text-center">
                                <div class="stats-icon mx-auto mb-3" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                                    <i class="ri-file-text-line"></i>
                                </div>
                                <h5>Upload Assignment</h5>
                                <p class="text-muted">Create and upload assignments for students</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="stats-card">
                            <h5 class="mb-3">Recent Activity</h5>
                            <div class="text-center text-muted py-4">
                                <i class="ri-time-line fs-1 mb-3"></i>
                                <p>No recent activity to display</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
   <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-4 mt-5">
        <div class="container text-md-left">
            <div class="row text-md-left">
                <div class="col-md-4 col-lg-4 col-xl-4 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 fw-bold">
                        <i class="ri-graduation-cap-line me-2"></i>Focus Bridge
                    </h5>
                    <p>Your comprehensive study companion for tracking progress, managing exams, and staying motivated.</p>
                </div>
                <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mt-3">
                    <h6 class="text-uppercase mb-4 fw-bold">Admin Features</h6>
                    <p><a href="manage_exams.php" class="text-white text-decoration-none">Manage Exams</a></p>
                    <p><a href="notes_upload.php" class="text-white text-decoration-none">Upload Notes</a></p>
                    <p><a href="manage_notices.php" class="text-white text-decoration-none">Post Notices</a></p>
                    <p><a href="manage_quotes.php" class="text-white text-decoration-none">Motivational Quotes</a></p>
                </div>
                <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mt-3">
                    <h6 class="text-uppercase mb-4 fw-bold">Help & Support</h6>
                    <p><a href="#" class="text-white text-decoration-none">Help Center</a></p>
                    <p><a href="#" class="text-white text-decoration-none">Privacy Policy</a></p>
                    <p><a href="#" class="text-white text-decoration-none">Terms of Service</a></p>
                </div>
                <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h6 class="text-uppercase mb-4 fw-bold">Contact</h6>
                    <p><i class="ri-home-line me-3"></i> Focus Bridge Developers</p>
                    <p><i class="ri-mail-line me-3"></i> info@focusbridge.com</p>
                    <p><i class="ri-phone-line me-3"></i> 01734343434</p>
                </div>
            </div>
            <hr class="mb-4">
            <div class="row align-items-center">
                <div class="col-md-7 col-lg-8 text-center text-md-start">
                    <p class="mb-0">&copy; 2024 All Rights Reserved by <a href="#" class="text-decoration-none text-white fw-bold">Focus Bridge</a></p>
                </div>
                <div class="col-md-5 col-lg-4 text-center text-md-end mt-3 mt-md-0">
                    <ul class="list-unstyled list-inline">
                        <li class="list-inline-item">
                            <a href="#" class="btn-floating btn-sm text-white fs-5"><i class="ri-facebook-box-fill"></i></a>
                        </li>
                        <li class="list-inline-item">
                            <a href="#" class="btn-floating btn-sm text-white fs-5"><i class="ri-twitter-fill"></i></a>
                        </li>
                        <li class="list-inline-item">
                            <a href="#" class="btn-floating btn-sm text-white fs-5"><i class="ri-linkedin-box-fill"></i></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    
</body>
</html>
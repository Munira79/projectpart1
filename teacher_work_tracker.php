<?php
session_start();
include 'db_config.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: login.php?role=teacher");
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'add_session') {
        $subject = $_POST['subject'];
        $activity_type = $_POST['activity_type'];
        $notes = $_POST['notes'];
        $duration = $_POST['duration'];
        $session_date = $_POST['session_date'];
        
        $sql = "INSERT INTO teacher_work_sessions (teacher_id, subject, activity_type, notes, duration, session_date) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssis", $teacher_id, $subject, $activity_type, $notes, $duration, $session_date);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Work session added successfully!";
        } else {
            $_SESSION['error'] = "Failed to add work session. Please try again.";
        }
        
        header("Location: teacher_work_tracker.php");
        exit();
    }
}

// Get work sessions for the current month
$current_month = date('Y-m');
$sessions_sql = "SELECT * FROM teacher_work_sessions WHERE teacher_id = ? AND DATE_FORMAT(session_date, '%Y-%m') = ? ORDER BY session_date DESC, created_at DESC";
$sessions_stmt = $conn->prepare($sessions_sql);
$sessions_stmt->bind_param("is", $teacher_id, $current_month);
$sessions_stmt->execute();
$sessions = $sessions_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate total hours for current month
$total_hours_sql = "SELECT SUM(duration) as total_hours FROM teacher_work_sessions WHERE teacher_id = ? AND DATE_FORMAT(session_date, '%Y-%m') = ?";
$total_hours_stmt = $conn->prepare($total_hours_sql);
$total_hours_stmt->bind_param("is", $teacher_id, $current_month);
$total_hours_stmt->execute();
$total_hours = $total_hours_stmt->get_result()->fetch_assoc()['total_hours'] ?? 0;

// Get today's sessions
$today = date('Y-m-d');
$today_sessions_sql = "SELECT * FROM teacher_work_sessions WHERE teacher_id = ? AND session_date = ? ORDER BY created_at DESC";
$today_sessions_stmt = $conn->prepare($today_sessions_sql);
$today_sessions_stmt->bind_param("is", $teacher_id, $today);
$today_sessions_stmt->execute();
$today_sessions = $today_sessions_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate today's total hours
$today_hours_sql = "SELECT SUM(duration) as total_hours FROM teacher_work_sessions WHERE teacher_id = ? AND session_date = ?";
$today_hours_stmt = $conn->prepare($today_hours_sql);
$today_hours_stmt->bind_param("is", $teacher_id, $today);
$today_hours_stmt->execute();
$today_hours = $today_hours_stmt->get_result()->fetch_assoc()['total_hours'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Tracker - FocusBridge</title>
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
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .activity-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .activity-lecture { background-color: #dbeafe; color: #1e40af; }
        .activity-assignment { background-color: #fef3c7; color: #92400e; }
        .activity-exam { background-color: #fecaca; color: #991b1b; }
        .activity-research { background-color: #d1fae5; color: #065f46; }
        .activity-grading { background-color: #e0e7ff; color: #3730a3; }
        .activity-meeting { background-color: #f3e8ff; color: #6b21a8; }
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
                    <a class="nav-link active" href="teacher_work_tracker.php">
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
                    <h2 class="mb-0">Work Tracker</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSessionModal">
                        <i class="ri-add-line me-1"></i> Add Work Session
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

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="ri-time-line fs-1 text-primary"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0"><?php echo number_format($today_hours, 1); ?></h3>
                                    <p class="text-muted mb-0">Hours Today</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="ri-calendar-line fs-1 text-success"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0"><?php echo number_format($total_hours, 1); ?></h3>
                                    <p class="text-muted mb-0">Hours This Month</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="ri-list-check fs-1 text-info"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0"><?php echo count($today_sessions); ?></h3>
                                    <p class="text-muted mb-0">Sessions Today</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="ri-bar-chart-line fs-1 text-warning"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0"><?php echo count($sessions); ?></h3>
                                    <p class="text-muted mb-0">Total Sessions</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Sessions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Today's Work Sessions</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($today_sessions)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="ri-time-line fs-1 mb-3"></i>
                                <p>No work sessions recorded for today</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Subject</th>
                                            <th>Activity</th>
                                            <th>Duration</th>
                                            <th>Notes</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($today_sessions as $session): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($session['subject']); ?></td>
                                                <td>
                                                    <span class="activity-badge activity-<?php echo strtolower(str_replace(' ', '-', $session['activity_type'])); ?>">
                                                        <?php echo htmlspecialchars($session['activity_type']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $session['duration']; ?> min</td>
                                                <td><?php echo htmlspecialchars($session['notes']); ?></td>
                                                <td><?php echo date('H:i', strtotime($session['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- All Sessions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">All Work Sessions (<?php echo date('F Y'); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($sessions)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="ri-time-line fs-1 mb-3"></i>
                                <p>No work sessions recorded this month</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Subject</th>
                                            <th>Activity</th>
                                            <th>Duration</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sessions as $session): ?>
                                            <tr>
                                                <td><?php echo date('M j, Y', strtotime($session['session_date'])); ?></td>
                                                <td><?php echo htmlspecialchars($session['subject']); ?></td>
                                                <td>
                                                    <span class="activity-badge activity-<?php echo strtolower(str_replace(' ', '-', $session['activity_type'])); ?>">
                                                        <?php echo htmlspecialchars($session['activity_type']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $session['duration']; ?> min</td>
                                                <td><?php echo htmlspecialchars($session['notes']); ?></td>
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

    <!-- Add Session Modal -->
    <div class="modal fade" id="addSessionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Work Session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_session">
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="activity_type" class="form-label">Activity Type</label>
                            <select class="form-select" id="activity_type" name="activity_type" required>
                                <option value="Lecture">Lecture</option>
                                <option value="Assignment Review">Assignment Review</option>
                                <option value="Exam Preparation">Exam Preparation</option>
                                <option value="Research">Research</option>
                                <option value="Grading">Grading</option>
                                <option value="Meeting">Meeting</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="duration" class="form-label">Duration (minutes)</label>
                            <input type="number" class="form-control" id="duration" name="duration" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="session_date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="session_date" name="session_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Session</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

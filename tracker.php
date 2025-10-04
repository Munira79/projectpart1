<?php
session_start();
// Include the database configuration file
include 'db_config.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user ID from the session
$user_id = $_SESSION['user_id'];

// Handle form submission to log a new session
if (isset($_POST['log_session'])) {
    $subject = $_POST['subject'];
    $activity_type = $_POST['activity_type'];
    $notes = $_POST['notes'];
    $duration = (int)$_POST['duration']; // Duration in minutes

    if (!empty($subject) && $duration > 0) {
        // Use a prepared statement to prevent SQL injection
        $sql = "INSERT INTO sessions (user_id, subject, activity_type, notes, duration) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssi", $user_id, $subject, $activity_type, $notes, $duration);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Session logged successfully! ✅";
        } else {
            $_SESSION['error_message'] = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Please fill out all required fields.";
    }
    header('location: tracker.php');
    exit();
}

// Handle session deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    // Use a prepared statement for deletion
    $delete_sql = "DELETE FROM sessions WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("ii", $delete_id, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Session deleted! 👍";
    } else {
        $_SESSION['error_message'] = "Error: " . $stmt->error;
    }
    $stmt->close();
    header('location: tracker.php');
    exit();
}

// Fetch all sessions for the current user using prepared statement
$sessions_query = "SELECT * FROM sessions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($sessions_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$sessions_result = $stmt->get_result();
$stmt->close();

// Calculate statistics using prepared statements for security
// Total study time
$total_time_query = "SELECT SUM(duration) AS total FROM sessions WHERE user_id = ?";
$stmt = $conn->prepare($total_time_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_time_result = $stmt->get_result();
$total_minutes = $total_time_result->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Total sessions
$total_sessions_query = "SELECT COUNT(*) AS total_sessions FROM sessions WHERE user_id = ?";
$stmt = $conn->prepare($total_sessions_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_sessions_result = $stmt->get_result();
$total_sessions = $total_sessions_result->fetch_assoc()['total_sessions'] ?? 0;
$stmt->close();

// Sessions this week
$week_sessions_query = "SELECT COUNT(*) AS sessions_this_week FROM sessions WHERE user_id = ? AND created_at >= CURDATE() - INTERVAL (WEEKDAY(CURDATE())) DAY";
$stmt = $conn->prepare($week_sessions_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$week_sessions_result = $stmt->get_result();
$sessions_this_week = $week_sessions_result->fetch_assoc()['sessions_this_week'] ?? 0;
$stmt->close();

// Top subjects
$top_subjects_query = "SELECT subject, SUM(duration) AS total_duration FROM sessions WHERE user_id = ? GROUP BY subject ORDER BY total_duration DESC LIMIT 3";
$stmt = $conn->prepare($top_subjects_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$top_subjects_result = $stmt->get_result();
$stmt->close();

// Helper function to format duration
function format_duration($minutes) {
    if ($minutes === null) return "0m";
    $hours = floor($minutes / 60);
    $minutes = $minutes % 60;
    return ($hours > 0 ? $hours . 'h ' : '') . $minutes . 'm';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Work Tracker - FocusBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Pacifico&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .logo { font-family: 'Pacifico', cursive; }
        .card { border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .session-item { background: white; border-radius: 8px; padding: 15px; margin-bottom: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .session-item .badge { background-color: #f1f5f9; color: #475569; }
        .stats-text { font-size: 1.5rem; font-weight: 600; }
        .alert-message { position: fixed; top: 20px; right: 20px; z-index: 1050; }
        .great-job-card { background: linear-gradient(135deg, #a78bfa, #8b5cf6); color: white; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
      <div class="container">
        <a class="navbar-brand logo text-primary fs-3" href="student_dashboard.php">FocusBridge</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="student_dashboard.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Quotes</a></li>
            <li class="nav-item"><a class="nav-link" href="exams.php">Exam Reminders</a></li>
            <li class="nav-item"><a class="nav-link active" href="tracker.php">Work Tracker</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Notes</a></li>
            <li class="nav-item"><a class="btn btn-primary ms-3" href="logout.php">Logout</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <main class="container py-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-0">Work Tracker</h2>
            <p class="text-muted">Monitor your study sessions and track your progress</p>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-message" role="alert">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-message" role="alert">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card p-4 mb-4">
                    <h5 class="mb-3">Start your study Session</h5>
                    <form action="tracker.php" method="post">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" placeholder="e.g., Mathematics" required>
                            </div>
                            <div class="col-md-6">
                                <label for="duration" class="form-label">Duration (in minutes)</label>
                                <input type="number" class="form-control" id="duration" name="duration" required>
                            </div>
                        </div>
                        <div class="mb-3 mt-3">
                            <label for="activity_type" class="form-label">Activity Type</label>
                            <select class="form-select" id="activity_type" name="activity_type">
                                <option value="Study">Study</option>
                                <option value="Lecture">Assignment</option>
                                <option value="Homework">Homework</option>
                                <option value="Reading">Book Reading</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="What will you work on?"></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="log_session" class="btn btn-primary">Start Tracking</button>
                        </div>
                    </form>
                </div>

                <div class="card p-4">
                    <h5 class="mb-3">Recent Sessions</h5>
                    <?php if ($sessions_result->num_rows > 0): ?>
                        <?php while($session = $sessions_result->fetch_assoc()): ?>
                        <div class="session-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-semibold"><?php echo htmlspecialchars($session['subject']); ?></h6>
                                <p class="mb-0 text-muted small"><?php echo htmlspecialchars($session['activity_type']); ?> &bull; <?php echo date("F j, Y, g:i a", strtotime($session['created_at'])); ?></p>
                            </div>
                            <div class="text-end d-flex align-items-center">
                                <span class="badge me-3"><?php echo format_duration($session['duration']); ?></span>
                                <a href="tracker.php?delete_id=<?php echo $session['id']; ?>" class="btn btn-outline-danger border-0 p-0"><i class="ri-delete-bin-line"></i></a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-info text-center" role="alert">No sessions tracked yet. Start tracking your first session!</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="card p-4 mb-4">
                    <h5 class="mb-3">Statistics</h5>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Total Study Time</span>
                        <span class="text-primary stats-text"><?php echo format_duration($total_minutes); ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Sessions This Week</span>
                        <span class="text-info stats-text"><?php echo $sessions_this_week; ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Total Sessions</span>
                        <span class="text-success stats-text"><?php echo $total_sessions; ?></span>
                    </div>
                </div>

                <div class="card p-4 mb-4">
                    <h5 class="mb-3">Top Subjects</h5>
                    <?php if ($top_subjects_result->num_rows > 0): ?>
                        <?php while($subject = $top_subjects_result->fetch_assoc()): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><?php echo htmlspecialchars($subject['subject']); ?></span>
                            <span class="text-muted"><?php echo format_duration($subject['total_duration']); ?></span>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted small">Log some sessions to see your top subjects.</p>
                    <?php endif; ?>
                </div>

                <div class="card p-4 great-job-card">
                    <div class="d-flex align-items-center mb-3">
                        <i class="ri-trophy-line display-6 me-2"></i>
                        <h5 class="mb-0">Great Job!</h5>
                        
                    </div>
                    <p class="mb-0">You've completed <?php echo $total_sessions; ?> study sessions. Keep up the excellent work and stay consistent with your learning goals!</p>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-dark text-white pt-5 pb-4">
        <div class="container text-md-left">
            <div class="row text-md-left">
                <div class="col-md-4 col-lg-4 col-xl-4 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 fw-bold">FocusBridge</h5>
                    <p>Your comprehensive study companion for tracking progress, managing exams, and staying motivated.</p>
                </div>
                <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mt-3">
                    <h6 class="text-uppercase mb-4 fw-bold">Features</h6>
                    <p>Motivational Quotes</p>
                    <p>Exam Reminders</p>
                    <p>Work Tracker</p>
                    <p>Notes Upload</p>
                </div>
                <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h6 class="text-uppercase mb-4 fw-bold">Support</h6>
                    <p>Help Center</p>
                    <p>Contact Us</p>
                    <p>Privacy Policy</p>
                    <p>Terms of Service</p>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-12 text-center">
                    <p class="mb-0">&copy; 2024 FocusBridge. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript for success/error messages
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert-message');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() { alert.style.display = 'none'; }, 500);
            });
        }, 3000);
    </script>
</body>
</html>
<?php
session_start();
include('config.php');

// Redirect to login if not logged in
if (!isset($_SESSION['email'])) {
    header('location: login.php');
    exit();
}

// Get user ID at the top to avoid undefined variable warnings.
$email = $_SESSION['email'];
$user_query = "SELECT id FROM users WHERE email='$email'";
$user_result = mysqli_query($conn, $user_query);
if ($user_result && mysqli_num_rows($user_result) > 0) {
    $user_id = mysqli_fetch_assoc($user_result)['id'];
} else {
    header('location: logout.php');
    exit();
}

// Function to handle adding an exam
if (isset($_POST['add_exam'])) {
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    // Note: 'location' and 'type' are not saved to the database.
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);

    // Only save subject, date, and description
    $sql = "INSERT INTO exams (user_id, subject, date, description) VALUES ('$user_id', '$subject', '$date', '$description')";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success_message'] = "Exam reminder added successfully! 🎉";
    } else {
        $_SESSION['error_message'] = "Error: " . mysqli_error($conn);
    }
    header('location: exams.php');
    exit();
}

// Function to handle deleting an exam
if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    
    // Ensure the user owns the exam before deleting
    $delete_sql = "DELETE FROM exams WHERE id='$delete_id' AND user_id='$user_id'";
    if (mysqli_query($conn, $delete_sql)) {
        $_SESSION['success_message'] = "Exam reminder deleted! 👍";
    } else {
        $_SESSION['error_message'] = "Error: " . mysqli_error($conn);
    }
    header('location: exams.php');
    exit();
}

// Fetch all exams for the current user
$exams_query = "SELECT * FROM exams WHERE user_id='$user_id' ORDER BY date ASC";
$exams_result = mysqli_query($conn, $exams_query);

// Fetch quick stats
$total_exams_query = "SELECT COUNT(*) AS total FROM exams WHERE user_id='$user_id'";
$total_exams_result = mysqli_query($conn, $total_exams_query);
$total_exams = mysqli_fetch_assoc($total_exams_result)['total'];

$completed_exams_query = "SELECT COUNT(*) AS completed FROM exams WHERE user_id='$user_id' AND date < NOW()";
$completed_exams_result = mysqli_query($conn, $completed_exams_query);
$completed_exams = mysqli_fetch_assoc($completed_exams_result)['completed'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Exam Reminders - FocusBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Pacifico&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .logo { font-family: 'Pacifico', cursive; }
        .card { border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .exam-item { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .exam-item.overdue { border-left: 5px solid #dc3545; }
        .overdue-text { color: #dc3545; font-size: 0.875rem; font-weight: 600; }
        .exam-status { margin-left: auto; }
        .study-tip-card { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; }
        .alert-message { position: fixed; top: 20px; right: 20px; z-index: 1050; }
        .form-modal .modal-content { border-radius: 16px; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
      <div class="container">
        <a class="navbar-brand logo text-primary fs-3" href="index.html">FocusBridge</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Quotes</a></li>
            <li class="nav-item"><a class="nav-link active" href="exams.php">Exam Reminders</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Work Tracker</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Notes</a></li>
            <li class="nav-item"><a class="btn btn-primary ms-3" href="logout.php">Logout</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <main class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">Exam Reminders</h2>
                <p class="text-muted">Stay organized and never miss an important exam</p>
            </div>
            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addExamModal">
                <i class="ri-add-line me-2"></i>Add Exam
            </button>
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
                <div class="card p-4">
                    <h4 class="mb-3">Upcoming Exams</h4>
                    <?php if (mysqli_num_rows($exams_result) > 0): ?>
                        <?php while($exam = mysqli_fetch_assoc($exams_result)): ?>
                        <?php 
                            $exam_date = new DateTime($exam['date']);
                            $now = new DateTime();
                            $interval = $now->diff($exam_date);
                            $is_overdue = $now > $exam_date;
                            $days_text = $is_overdue ? $interval->days . ' days overdue' : $interval->days . ' days left';
                            $exam_class = $is_overdue ? 'overdue' : '';
                        ?>
                        <div class="exam-item d-flex align-items-center <?php echo $exam_class; ?>">
                            <div class="flex-grow-1">
                                <h5 class="mb-1 d-flex align-items-center">
                                    <?php echo htmlspecialchars($exam['subject']); ?> 
                                    </h5>
                                <div class="d-flex align-items-center flex-wrap text-muted small mt-2">
                                    <span class="me-3"><i class="ri-calendar-line me-1"></i><?php echo date("F j, Y, H:i", strtotime($exam['date'])); ?></span>
                                    <span class="me-3"><i class="ri-time-line me-1"></i><?php echo date("H:i", strtotime($exam['date'])); ?></span>
                                    </div>
                                <p class="mb-0 mt-2 text-muted"><?php echo htmlspecialchars($exam['description']); ?></p>
                                <p class="mb-0 mt-2 overdue-text"><?php echo $days_text; ?></p>
                            </div>
                            <div class="d-flex exam-status">
                                <a href="exams.php?delete_id=<?php echo $exam['id']; ?>" class="btn btn-outline-danger border-0"><i class="ri-delete-bin-line h4 mb-0"></i></a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-info text-center" role="alert">No upcoming exams. Add one to get started!</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="card p-4 mb-4">
                    <h5 class="mb-3">Quick Stats</h5>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Upcoming Exams</span>
                        <span class="badge bg-primary fs-6"><?php echo $total_exams; ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Completed Exams</span>
                        <span class="badge bg-success fs-6"><?php echo $completed_exams; ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>This Week</span>
                        <span class="badge bg-info fs-6">0</span>
                    </div>
                </div>

                <div class="card p-4 study-tip-card">
                    <div class="d-flex align-items-center mb-3">
                        <i class="ri-lightbulb-line display-6 me-2"></i>
                        <h5 class="mb-0">Study Tip</h5>
                    </div>
                    <p class="mb-0">Create a study schedule at least 2 weeks before your exam date. Break down topics into manageable daily goals for better retention.</p>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="addExamModal" tabindex="-1" aria-labelledby="addExamModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content form-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="addExamModalLabel">Add a New Exam</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="exams.php" method="post">
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="type" class="form-label">Exam Type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="">Select a type</option>
                                <option value="Quiz">Quiz</option>
                                <option value="Midterm">Midterm</option>
                                <option value="Final Exam">Final Exam</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="date" class="form-label">Date and Time</label>
                            <input type="datetime-local" class="form-control" id="date" name="date" required>
                        </div>
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="add_exam" class="btn btn-primary">Add Exam Reminder</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white pt-5 pb-4">
        <div class="container text-md-left">
            <div class="row text-md-left">
                <div class="col-md-4 col-lg-4 col-xl-4 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 fw-bold"><i class="bi bi-mortarboard-fill me-2"></i>FocusBridge</h5>
                    <p>Your comprehensive study companion for tracking progress, managing exams, and staying motivated.</p>
                </div>
                <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mt-3">
                    <h6 class="text-uppercase mb-4 fw-bold">Features</h6>
                    <p><i class="bi bi-lightbulb me-2"></i>Motivational Quotes</p>
                    <p><i class="bi bi-calendar-event me-2"></i>Exam Reminders</p>
                    <p><i class="bi bi-clock-history me-2"></i>Work Tracker</p>
                    <p><i class="bi bi-upload me-2"></i>Notes Upload</p>
                </div>
                <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h6 class="text-uppercase mb-4 fw-bold">Support</h6>
                    <p><i class="bi bi-question-circle me-2"></i>Help Center</p>
                    <p><i class="bi bi-envelope me-2"></i>Contact Us</p>
                    <p><i class="bi bi-shield-lock me-2"></i>Privacy Policy</p>
                    <p><i class="bi bi-file-earmark-text me-2"></i>Terms of Service</p>
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
<?php
session_start();
include 'db_config.php';
include 'helper_functions.php';

// Check if user is logged in and has admin/teacher role
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'teacher')) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : $_SESSION['user_email'];

// Get user's academic info for targeting
$user_info = getUserInfo($conn, $user_id);

// ------------------------------------------------
// UPDATED: Handle Form Submission (Add OR Update)
// ------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = $_POST['subject'];
    $exam_type = $_POST['exam_type'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $exam_date = $_POST['exam_date'];
    $exam_time = $_POST['exam_time'];
    $duration = (int)$_POST['duration']; // Ensure duration is an integer for binding
    $location = $_POST['location'];
    
    // 1. Handle ADD NEW EXAM (Your original logic, slightly cleaned up)
    if (isset($_POST['add_exam'])) {
        $department = $user_info['department'];
        $batch = $user_info['batch'];
        $section = $user_info['section'];
        
        $sql = "INSERT INTO exams (subject, exam_type, title, description, exam_date, exam_time, duration, location, department, batch, section, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        // Updated bind_param to include department, batch, section
        $stmt->bind_param("ssssssissssi", $subject, $exam_type, $title, $description, $exam_date, $exam_time, $duration, $location, $department, $batch, $section, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Exam added successfully!";
        } else {
            $_SESSION['error_message'] = "Error adding exam. Please try again. " . $conn->error;
        }
        header("Location: manage_exams.php");
        exit();

    // 2. NEW: Handle UPDATE EXISTING EXAM
    } elseif (isset($_POST['update_exam'])) {
        $exam_id_to_update = (int)$_POST['exam_id'];
        $status = $_POST['status'] ?? 'upcoming'; 
        $department = $user_info['department'];
        $batch = $user_info['batch'];
        $section = $user_info['section'];

        // Query to update all fields, restricting update permission to the creator
        $sql = "UPDATE exams SET subject = ?, exam_type = ?, title = ?, description = ?, exam_date = ?, exam_time = ?, duration = ?, location = ?, department = ?, batch = ?, section = ?, status = ? WHERE id = ? AND created_by = ?";
        $stmt = $conn->prepare($sql);
        
        // Updated bind_param to include department, batch, section
        $stmt->bind_param("ssssssissssisi", $subject, $exam_type, $title, $description, $exam_date, $exam_time, $duration, $location, $department, $batch, $section, $status, $exam_id_to_update, $user_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $_SESSION['success_message'] = "Exam updated successfully! 👍";
            } else {
                $_SESSION['error_message'] = "No changes were made or you do not have permission to edit this exam. 🔒";
            }
        } else {
            $_SESSION['error_message'] = "Error updating exam: " . $conn->error;
        }
        header("Location: manage_exams.php");
        exit();
    }
}

// Handle exam deletion (ORIGINAL CODE)
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id']; // Cast to integer for security
    $delete_sql = "DELETE FROM exams WHERE id = ? AND created_by = ?"; // Secure deletion
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("ii", $delete_id, $user_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['success_message'] = "Exam deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Deletion failed. Exam not found or you lack permission. 🛑";
        }
    } else {
        $_SESSION['error_message'] = "Error deleting exam.";
    }
    header("Location: manage_exams.php");
    exit();
}

// Fetch only exams created by the current user
$exams_query = "SELECT * FROM exams WHERE created_by = ? ORDER BY exam_date DESC, exam_time DESC";
$stmt = $conn->prepare($exams_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$exams_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Exams - Focus Bridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Pacifico&display=swap" rel="stylesheet" />
    
    <style>
        :root {
            --bg-color: #f8fafc;
            --text-color: #1f2937;
            --card-bg: white;
            --navbar-bg: white;
            --footer-bg: #e5e7eb;
            --footer-text: #4b5563;
        }

        [data-theme="dark"] {
            --bg-color: #1f2937;
            --text-color: #f9fafb;
            --card-bg: #374151;
            --navbar-bg: #374151;
            --footer-bg: #111827;
            --footer-text: #9ca3af;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        .logo {
            font-family: 'Pacifico', cursive;
        }
        
        .navbar {
            background-color: var(--navbar-bg) !important;
            transition: background-color 0.3s ease;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
        
        .exam-card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 20px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        
        .exam-card:hover {
            transform: translateY(-2px);
        }
        
        .theme-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .theme-toggle:hover {
            background-color: rgba(0,0,0,0.1);
        }

        [data-theme="dark"] .theme-toggle:hover {
            background-color: rgba(255,255,255,0.1);
        }

        .status-badge {
            font-size: 0.8rem;
            padding: 4px 8px;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .hero-section {
                padding: 40px 0;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light shadow-sm">
        <div class="container">
            <a class="navbar-brand logo text-primary fs-3" href="admin_dashboard.php">Focus Bridge</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="manage_exams.php">Exam Schedule</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="notes_upload.php">Notes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_notices.php">Notice</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_quotes.php">Motivational Quotes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tracker.php">Work Tracker</a>
                    </li>
                    <li class="nav-item">
                        <button class="theme-toggle" id="themeToggle" onclick="toggleTheme()">🌙</button>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-danger ms-3" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-3">Manage Your Exams</h1>
            <p class="lead">Add, edit, and manage exam schedules for your department, batch, and section.</p>
        </div>
    </section>

    <div class="container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row mb-5">
            <div class="col-12">
                <div class="exam-card">
                    <h4 class="mb-4"><i class="ri-add-circle-line me-2"></i>Add New Exam</h4>
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="exam_type" class="form-label">Exam Type</label>
                                <select class="form-select" id="exam_type" name="exam_type" required>
                                    <option value="exam">Exam</option>
                                    <option value="assignment">Assignment</option>
                                    <option value="tutorial">Tutorial</option>
                                    <option value="quiz">Quiz</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="duration" class="form-label">Duration (minutes)</label>
                                <input type="number" class="form-control" id="duration" name="duration" value="60" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="exam_date" class="form-label">Exam Date</label>
                                <input type="date" class="form-control" id="exam_date" name="exam_date" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="exam_time" class="form-label">Exam Time</label>
                                <input type="time" class="form-control" id="exam_time" name="exam_time" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" placeholder="e.g., Room 101">
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            <strong>Target Audience:</strong> This exam will be visible to students from 
                            <strong><?php echo $user_info['department'] ?? 'Your Department'; ?></strong>, 
                            <strong>Batch <?php echo $user_info['batch'] ?? 'Your Batch'; ?></strong>, 
                            <strong>Section <?php echo $user_info['section'] ?? 'Your Section'; ?></strong>
                        </div>
                        <button type="submit" name="add_exam" class="btn btn-primary">Add Exam</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <h4 class="mb-4"><i class="ri-calendar-line me-2"></i>All Exams</h4>
                <?php if ($exams_result->num_rows > 0): ?>
                    <?php while($exam = $exams_result->fetch_assoc()): ?>
                        <div class="exam-card">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center mb-2">
                                        <h5 class="mb-0 me-3"><?php echo htmlspecialchars($exam['title']); ?></h5>
                                        <span class="badge bg-primary status-badge"><?php echo ucfirst($exam['exam_type']); ?></span>
                                        <span class="badge bg-<?php echo $exam['status'] === 'upcoming' ? 'success' : ($exam['status'] === 'completed' ? 'secondary' : 'warning'); ?> status-badge ms-2">
                                            <?php echo ucfirst($exam['status'] ?? 'Upcoming'); ?>
                                        </span>
                                    </div>
                                    <p class="text-muted mb-2"><?php echo htmlspecialchars($exam['subject']); ?></p>
                                    <?php if ($exam['description']): ?>
                                        <p class="mb-2"><?php echo htmlspecialchars($exam['description']); ?></p>
                                    <?php endif; ?>
                                    <div class="d-flex align-items-center text-muted small">
                                        <i class="ri-calendar-line me-1"></i>
                                        <span class="me-3"><?php echo date('F j, Y', strtotime($exam['exam_date'])); ?></span>
                                        <i class="ri-time-line me-1"></i>
                                        <span class="me-3"><?php echo date('g:i A', strtotime($exam['exam_time'])); ?></span>
                                        <i class="ri-timer-line me-1"></i>
                                        <span class="me-3"><?php echo $exam['duration']; ?> minutes</span>
                                        <?php if ($exam['location']): ?>
                                            <i class="ri-map-pin-line me-1"></i>
                                            <span><?php echo htmlspecialchars($exam['location']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="btn-group" role="group">
                                        <button 
                                            type="button" 
                                            class="btn btn-outline-primary btn-sm edit-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editExamModal"
                                            data-id="<?php echo $exam['id']; ?>"
                                            data-subject="<?php echo htmlspecialchars($exam['subject']); ?>"
                                            data-type="<?php echo htmlspecialchars($exam['exam_type']); ?>"
                                            data-title="<?php echo htmlspecialchars($exam['title']); ?>"
                                            data-description="<?php echo htmlspecialchars($exam['description']); ?>"
                                            data-date="<?php echo htmlspecialchars($exam['exam_date']); ?>"
                                            data-time="<?php echo htmlspecialchars($exam['exam_time']); ?>"
                                            data-duration="<?php echo htmlspecialchars($exam['duration']); ?>"
                                            data-location="<?php echo htmlspecialchars($exam['location']); ?>"
                                            data-status="<?php echo htmlspecialchars($exam['status'] ?? 'upcoming'); ?>"
                                        >
                                            <i class="ri-edit-line"></i> Edit
                                        </button>
                                        
                                        <a href="manage_exams.php?delete_id=<?php echo $exam['id']; ?>" 
                                            class="btn btn-outline-danger btn-sm"
                                            onclick="return confirm('Are you sure you want to delete this exam?')">
                                            <i class="ri-delete-bin-line"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="exam-card text-center">
                        <i class="ri-calendar-line display-1 text-muted mb-3"></i>
                        <h5 class="text-muted">No exams found</h5>
                        <p class="text-muted">Add your first exam using the form above.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editExamModal" tabindex="-1" aria-labelledby="editExamModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editExamModalLabel"><i class="ri-edit-line me-2"></i>Edit Exam</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="editExamForm">
                        <input type="hidden" name="exam_id" id="edit-exam-id">
                        <input type="hidden" name="update_exam" value="1"> <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="edit-subject" name="subject" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-exam-type" class="form-label">Exam Type</label>
                                <select class="form-select" id="edit-exam-type" name="exam_type" required>
                                    <option value="exam">Exam</option>
                                    <option value="assignment">Assignment</option>
                                    <option value="tutorial">Tutorial</option>
                                    <option value="quiz">Quiz</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="edit-title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="edit-title" name="title" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit-duration" class="form-label">Duration (minutes)</label>
                                <input type="number" class="form-control" id="edit-duration" name="duration" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit-description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit-description" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="edit-exam-date" class="form-label">Exam Date</label>
                                <input type="date" class="form-control" id="edit-exam-date" name="exam_date" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit-exam-time" class="form-label">Exam Time</label>
                                <input type="time" class="form-control" id="edit-exam-time" name="exam_time" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit-location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="edit-location" name="location" placeholder="e.g., Room 101">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="edit-status" class="form-label">Status</label>
                            <select class="form-select" id="edit-status" name="status">
                                <option value="upcoming">Upcoming</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success"><i class="ri-save-line me-1"></i>Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <footer class="bg-dark text-white pt-5 pb-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 Focus Bridge. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Dark/Light mode toggle
        function toggleTheme() {
            const body = document.body;
            const currentTheme = localStorage.getItem('theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Update toggle button text
            const toggleBtn = document.getElementById('themeToggle');
            if (toggleBtn) {
                toggleBtn.textContent = newTheme === 'light' ? '🌙' : '☀️';
            }
        }

        // Initialize theme on page load AND Initialize Modal Handlers
        document.addEventListener('DOMContentLoaded', function() {
            // Theme initialization (Original Code)
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.body.setAttribute('data-theme', savedTheme);
            
            const toggleBtn = document.getElementById('themeToggle');
            if (toggleBtn) {
                toggleBtn.textContent = savedTheme === 'light' ? '🌙' : '☀️';
            }
            
            // ------------------------------------------------------------------
            // NEW: JAVASCRIPT FOR MODAL DATA POPULATION
            // ------------------------------------------------------------------
            const editExamModal = document.getElementById('editExamModal');
            if (editExamModal) {
                editExamModal.addEventListener('show.bs.modal', event => {
                    // Button that triggered the modal is event.relatedTarget
                    const button = event.relatedTarget; 

                    // Extract data attributes from the button and fill the form
                    document.getElementById('edit-exam-id').value = button.getAttribute('data-id');
                    document.getElementById('edit-subject').value = button.getAttribute('data-subject');
                    document.getElementById('edit-exam-type').value = button.getAttribute('data-type');
                    document.getElementById('edit-title').value = button.getAttribute('data-title');
                    document.getElementById('edit-description').value = button.getAttribute('data-description');
                    document.getElementById('edit-exam-date').value = button.getAttribute('data-date');
                    document.getElementById('edit-exam-time').value = button.getAttribute('data-time');
                    document.getElementById('edit-duration').value = button.getAttribute('data-duration');
                    document.getElementById('edit-location').value = button.getAttribute('data-location');
                    document.getElementById('edit-status').value = button.getAttribute('data-status');
                });
            }
        });
    </script>
</body>
</html>
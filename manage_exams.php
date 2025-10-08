<?php
session_start();
include 'db_config.php';

// Check if user is logged in and has admin/teacher role
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'teacher')) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_exam'])) {
    $subject = $_POST['subject'];
    $exam_type = $_POST['exam_type'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $exam_date = $_POST['exam_date'];
    $exam_time = $_POST['exam_time'];
    $duration = $_POST['duration'];
    $location = $_POST['location'];
    
    $sql = "INSERT INTO exams (subject, exam_type, title, description, exam_date, exam_time, duration, location, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssiis", $subject, $exam_type, $title, $description, $exam_date, $exam_time, $duration, $location, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Exam added successfully!";
    } else {
        $_SESSION['error_message'] = "Error adding exam. Please try again.";
    }
    header("Location: manage_exams.php");
    exit();
}

// Handle exam deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM exams WHERE id = ? AND created_by = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("ii", $delete_id, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Exam deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting exam.";
    }
    header("Location: manage_exams.php");
    exit();
}

// Fetch all exams
$exams_query = "SELECT * FROM exams ORDER BY exam_date DESC, exam_time DESC";
$exams_result = $conn->query($exams_query);
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
    <!-- Navigation -->
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

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-3">Manage Exams</h1>
            <p class="lead">Add, edit, and manage exam schedules for all students.</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">
        <!-- Success/Error Messages -->
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

        <!-- Add Exam Form -->
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
                        <button type="submit" name="add_exam" class="btn btn-primary">Add Exam</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Exams List -->
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
                                            <?php echo ucfirst($exam['status']); ?>
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
                                        <a href="edit_exam.php?id=<?php echo $exam['id']; ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="ri-edit-line"></i> Edit
                                        </a>
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

    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 Focus Bridge. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
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

        // Initialize theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.body.setAttribute('data-theme', savedTheme);
            
            const toggleBtn = document.getElementById('themeToggle');
            if (toggleBtn) {
                toggleBtn.textContent = savedTheme === 'light' ? '🌙' : '☀️';
            }
        });
    </script>
</body>
</html>

<?php
session_start();
include 'db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : $_SESSION['user_email'];


// Fetch all upcoming exams
$exams_query = "SELECT * FROM exams WHERE exam_date >= CURDATE() AND status = 'upcoming' ORDER BY exam_date ASC, exam_time ASC";
$exams_result = $conn->query($exams_query);

// Fetch all exams (for history)
$all_exams_query = "SELECT * FROM exams ORDER BY exam_date DESC, exam_time DESC";
$all_exams_result = $conn->query($all_exams_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Schedule - Focus Bridge</title>
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
            background: linear-gradient(135deg, #10b981, #34d399);
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

        .urgent-exam {
            border-left: 4px solid #dc3545;
        }

        .upcoming-exam {
            border-left: 4px solid #28a745;
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
            <a class="navbar-brand logo text-primary fs-3" href="student_dashboard.php">Focus Bridge</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="student_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tracker.php">Work Tracker</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="exams.php">Exam Schedule</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_notes.php">Notes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="quotes.php">Motivational Quotes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="notices.php">Notice</a>
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
            <h1 class="display-4 fw-bold mb-3">Exam Schedule</h1>
            <p class="lead">Stay updated with all your upcoming exams and assignments.</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">
        <!-- Quick Stats -->
        <div class="row mb-5">
            <div class="col-md-4 mb-3">
                <div class="exam-card text-center">
                    <i class="ri-calendar-check-line text-primary display-6 mb-2"></i>
                    <h5>Upcoming Exams</h5>
                    <h3 class="text-primary"><?php echo $exams_result->num_rows; ?></h3>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="exam-card text-center">
                    <i class="ri-time-line text-warning display-6 mb-2"></i>
                    <h5>Next Exam</h5>
                    <h3 class="text-warning">
                        <?php
                        $next_exam = $conn->query("SELECT exam_date FROM exams WHERE exam_date >= CURDATE() AND status = 'upcoming' ORDER BY exam_date ASC LIMIT 1")->fetch_assoc();
                        if ($next_exam) {
                            echo date('M j', strtotime($next_exam['exam_date']));
                        } else {
                            echo 'None';
                        }
                        ?>
                    </h3>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="exam-card text-center">
                    <i class="ri-book-line text-success display-6 mb-2"></i>
                    <h5>Total Exams</h5>
                    <h3 class="text-success"><?php echo $all_exams_result->num_rows; ?></h3>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs mb-4" id="examTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button" role="tab">
                    <i class="ri-calendar-check-line me-2"></i>Upcoming Exams
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                    <i class="ri-calendar-line me-2"></i>All Exams
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="examTabsContent">
            <!-- Upcoming Exams Tab -->
            <div class="tab-pane fade show active" id="upcoming" role="tabpanel">
                <h4 class="mb-4">Upcoming Exams</h4>
                <?php 
                // Reset the result pointer
                $exams_result->data_seek(0);
                if ($exams_result->num_rows > 0): 
                ?>
                    <?php while($exam = $exams_result->fetch_assoc()): ?>
                        <div class="exam-card <?php echo strtotime($exam['exam_date']) <= strtotime('+3 days') ? 'urgent-exam' : 'upcoming-exam'; ?>">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center mb-2">
                                        <h5 class="mb-0 me-3"><?php echo htmlspecialchars($exam['title']); ?></h5>
                                        <span class="badge bg-primary status-badge"><?php echo ucfirst($exam['exam_type']); ?></span>
                                        <span class="badge bg-success status-badge ms-2">Upcoming</span>
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
                                    <div class="text-muted small">
                                        <?php
                                        $days_left = ceil((strtotime($exam['exam_date']) - time()) / (60 * 60 * 24));
                                        if ($days_left == 0) {
                                            echo '<span class="text-danger fw-bold">Today!</span>';
                                        } elseif ($days_left == 1) {
                                            echo '<span class="text-warning fw-bold">Tomorrow</span>';
                                        } else {
                                            echo '<span class="text-info">' . $days_left . ' days left</span>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="exam-card text-center">
                        <i class="ri-calendar-check-line display-1 text-muted mb-3"></i>
                        <h5 class="text-muted">No upcoming exams</h5>
                        <p class="text-muted">You're all caught up! No exams scheduled.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- All Exams Tab -->
            <div class="tab-pane fade" id="all" role="tabpanel">
                <h4 class="mb-4">All Exams</h4>
                <?php if ($all_exams_result->num_rows > 0): ?>
                    <?php while($exam = $all_exams_result->fetch_assoc()): ?>
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
                                    <div class="text-muted small">
                                        <?php
                                        if ($exam['status'] === 'completed') {
                                            echo '<span class="text-secondary">Completed</span>';
                                        } elseif ($exam['status'] === 'upcoming') {
                                            $days_left = ceil((strtotime($exam['exam_date']) - time()) / (60 * 60 * 24));
                                            if ($days_left == 0) {
                                                echo '<span class="text-danger fw-bold">Today!</span>';
                                            } elseif ($days_left == 1) {
                                                echo '<span class="text-warning fw-bold">Tomorrow</span>';
                                            } else {
                                                echo '<span class="text-info">' . $days_left . ' days left</span>';
                                            }
                                        } else {
                                            echo '<span class="text-warning">' . ucfirst($exam['status']) . '</span>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="exam-card text-center">
                        <i class="ri-calendar-line display-1 text-muted mb-3"></i>
                        <h5 class="text-muted">No exams found</h5>
                        <p class="text-muted">No exams have been scheduled yet.</p>
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

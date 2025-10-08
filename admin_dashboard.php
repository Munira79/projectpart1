<?php
session_start();
include 'db_config.php';

// Check if user is logged in and has admin/teacher role
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'teacher')) {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Focus Bridge</title>
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
        
        .feature-card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
            height: 100%;
            text-decoration: none;
            color: inherit;
        }
        
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.15);
            color: inherit;
            text-decoration: none;
        }
        
        .feature-card .icon {
            font-size: 3.5rem;
            color: #2563eb;
            margin-bottom: 20px;
            transition: color 0.3s ease;
        }
        
        .feature-card:hover .icon {
            color: #1d4ed8;
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

        .stats-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: background-color 0.3s ease;
        }

        .welcome-card {
            background: linear-gradient(135deg, #10b981, #34d399);
            color: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .hero-section {
                padding: 40px 0;
            }
            
            .feature-card {
                margin-bottom: 20px;
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
                        <a class="nav-link" href="manage_exams.php">Exam Schedule</a>
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
            <h1 class="display-4 fw-bold mb-3">Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
            <p class="lead"><?php echo $user_role === 'admin' ? 'Student (CR) Dashboard' : 'Teacher Dashboard'; ?> - Manage and control key features of the application.</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">
        <!-- Welcome Card -->
        <div class="welcome-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-2">Admin Control Panel</h3>
                    <p class="mb-0">You have full access to manage exams, upload notes, post notices, and share motivational quotes with students.</p>
                </div>
                <div class="col-md-4 text-end">
                    <i class="ri-dashboard-3-line display-1"></i>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-5">
            <div class="col-md-3 mb-3">
                <div class="stats-card text-center">
                    <i class="ri-calendar-check-line text-primary display-6 mb-2"></i>
                    <h5>Upcoming Exams</h5>
                    <h3 class="text-primary"><?php
                        $exam_count = $conn->query("SELECT COUNT(*) as count FROM exams WHERE exam_date >= CURDATE() AND status = 'upcoming'")->fetch_assoc()['count'];
                        echo $exam_count;
                    ?></h3>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card text-center">
                    <i class="ri-file-chart-line text-success display-6 mb-2"></i>
                    <h5>Total Notes</h5>
                    <h3 class="text-success"><?php
                        $notes_count = $conn->query("SELECT COUNT(*) as count FROM notes")->fetch_assoc()['count'];
                        echo $notes_count;
                    ?></h3>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card text-center">
                    <i class="ri-quote-text text-warning display-6 mb-2"></i>
                    <h5>Motivational Quotes</h5>
                    <h3 class="text-warning"><?php
                        $quotes_count = $conn->query("SELECT COUNT(*) as count FROM quotes")->fetch_assoc()['count'];
                        echo $quotes_count;
                    ?></h3>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card text-center">
                    <i class="ri-notification-3-line text-info display-6 mb-2"></i>
                    <h5>Active Notices</h5>
                    <h3 class="text-info"><?php
                        $notices_count = $conn->query("SELECT COUNT(*) as count FROM notices WHERE is_active = 1")->fetch_assoc()['count'];
                        echo $notices_count;
                    ?></h3>
                </div>
            </div>
        </div>

        <!-- Feature Cards -->
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <a href="manage_exams.php" class="feature-card">
                    <i class="ri-calendar-line icon"></i>
                    <h5 class="fw-bold mb-3">Manage Exams</h5>
                    <p class="text-muted">Add, edit, or remove exam schedules, assignments, and tutorials for all students.</p>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="notes_upload.php" class="feature-card">
                    <i class="ri-file-upload-line icon"></i>
                    <h5 class="fw-bold mb-3">Upload Notes</h5>
                    <p class="text-muted">Upload and manage study materials, documents, and resources for students.</p>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="manage_notices.php" class="feature-card">
                    <i class="ri-megaphone-line icon"></i>
                    <h5 class="fw-bold mb-3">Post Notices</h5>
                    <p class="text-muted">Create and publish important announcements and updates for students.</p>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="manage_quotes.php" class="feature-card">
                    <i class="ri-quote-text icon"></i>
                    <h5 class="fw-bold mb-3">Motivational Quotes</h5>
                    <p class="text-muted">Share inspiring quotes and motivational content to boost student morale.</p>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="tracker.php" class="feature-card">
                    <i class="ri-timer-line icon"></i>
                    <h5 class="fw-bold mb-3">Work Tracker</h5>
                    <p class="text-muted">Track your personal tasks, study sessions, and productivity goals.</p>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="profile.php" class="feature-card">
                    <i class="ri-user-settings-line icon"></i>
                    <h5 class="fw-bold mb-3">Profile Settings</h5>
                    <p class="text-muted">Manage your profile information, preferences, and account settings.</p>
                </a>
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
                    <p><i class="ri-home-line me-3"></i> Focus Bridge HQ</p>
                    <p><i class="ri-mail-line me-3"></i> info@focusbridge.com</p>
                    <p><i class="ri-phone-line me-3"></i> +01 234 567 89</p>
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
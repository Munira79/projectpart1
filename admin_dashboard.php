<?php
session_start();
include 'db_config.php';

// Check if a user is logged in (but not their role)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard - FocusBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Pacifico&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: #333;
            background-color: #f8fafc;
            position: relative;
            z-index: 1;
        }
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://images.unsplash.com/photo-1542831371-29b0f74f9713?q=80&w=2670&auto=format&fit=crop'); /* You can replace this URL with your preferred image */
            background-size: cover;
            background-position: center;
            opacity: 0.05;
            z-index: -1;
            animation: fadeIn 2s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 0.05; }
        }
        .logo {
            font-family: 'Pacifico', cursive;
            font-weight: 400;
        }
        .card-admin {
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .card-admin::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.02);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1;
        }
        .card-admin:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        }
        .card-admin:hover::after {
            opacity: 1;
        }
        .card-body-content {
            position: relative;
            z-index: 2;
        }
        .card-admin .icon {
            font-size: 3.5rem;
            color: #1d4ed8;
            transition: color 0.3s ease;
        }
        .card-admin:hover .icon {
            color: #2563eb;
        }
        .footer-link {
            transition: color 0.3s ease;
        }
        .footer-link:hover {
            color: #ffffff !important;
        }
        .navbar-brand .logo {
            font-family: 'Pacifico', cursive;
            color: #1d4ed8;
        }
        .btn-danger-outline {
            color: #dc3545;
            border-color: #dc3545;
            transition: all 0.3s ease;
        }
        .btn-danger-outline:hover {
            background-color: #dc3545;
            color: #fff;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="admin_dashboard.php"><span class="logo">FocusBridge Admin</span></a>
            <span class="navbar-text ms-auto fw-bold text-danger">
                Admin Panel
            </span>
            <a href="logout.php" class="btn btn-danger ms-3">Logout</a>
        </div>
    </nav>

    <section class="container my-5 py-5">
        <h1 class="text-center mb-4 fw-bold display-5">Admin Dashboard</h1>
        <p class="text-center lead text-muted mb-5">Manage and control key features of the application.</p>
        <div class="row g-4 justify-content-center">
            <div class="col-md-6 col-lg-4">
                <a href="manage_exams.php" class="text-decoration-none">
                    <div class="card card-admin text-center p-4 h-100">
                        <div class="card-body-content">
                            <i class="ri-calendar-line icon mb-3"></i>
                            <h5 class="fw-bold mb-2">Manage Exams</h5>
                            <p class="text-muted small">Add, remove, or edit exam schedules for all students.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="manage_notices.php" class="text-decoration-none">
                    <div class="card card-admin text-center p-4 h-100">
                        <div class="card-body-content">
                            <i class="ri-megaphone-line icon mb-3"></i>
                            <h5 class="fw-bold mb-2">Post Notices & Notes</h5>
                            <p class="text-muted small">Publish important announcements and upload notes for students.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="notes_upload.php" class="text-decoration-none">
                    <div class="card card-admin text-center p-4 h-100">
                        <div class="card-body-content">
                            <i class="ri-file-chart-line icon mb-3"></i>
                            <h5 class="fw-bold mb-2"> Notes</h5>
                            <p class="text-muted small">Upload and view notes.</p>
                        </div>
                    </div>
                </a>
            </div>
             <div class="col-md-6 col-lg-4">
                <a href="tracker.php" class="text-decoration-none">
                    <div class="card card-admin text-center p-4 h-100">
                        <div class="card-body-content">
                            <i class="ri-timer-line icon mb-3"></i>
                            <h5 class="fw-bold mb-2">Work Tracker</h5>
                            <p class="text-muted small">Log your study sessions and track your productivity.</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white pt-5 pb-4">
        <div class="container text-center text-md-start">
            <div class="row text-center text-md-start">
                <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 fw-bold">FocusBridge</h5>
                    <p>Your comprehensive study companion for students and administrators alike.</p>
                </div>

                <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mt-3">
                    <h6 class="text-uppercase mb-4 fw-bold">Admin Links</h6>
                    <p><a href="manage_exams.php" class="text-white text-decoration-none footer-link">Manage Exams</a></p>
                    <p><a href="manage_notices.php" class="text-white text-decoration-none footer-link">Post Notices</a></p>
                    <p><a href="tracker.php" class="text-white text-decoration-none footer-link">Work Tracker</a></p>
                </div>
                
                <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mt-3">
                    <h6 class="text-uppercase mb-4 fw-bold">Help & Support</h6>
                    <p><a href="#" class="text-white text-decoration-none footer-link">Help Center</a></p>
                    <p><a href="#" class="text-white text-decoration-none footer-link">Privacy Policy</a></p>
                    <p><a href="#" class="text-white text-decoration-none footer-link">Terms of Service</a></p>
                </div>
                <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h6 class="text-uppercase mb-4 fw-bold">Contact</h6>
                    <p><i class="ri-home-line me-3"></i> FocusBridge HQ</p>
                    <p><i class="ri-mail-line me-3"></i> info@focusbridge.com</p>
                    <p><i class="ri-phone-line me-3"></i> +01 234 567 89</p>
                </div>
            </div>
            <hr class="mb-4">
            <div class="row align-items-center">
                <div class="col-md-7 col-lg-8 text-center text-md-start">
                    <p class="mb-0">&copy; 2024 All Rights Reserved by <a href="#" class="text-decoration-none text-white fw-bold">FocusBridge</a></p>
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
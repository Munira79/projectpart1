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
$user_email = $_SESSION['user_email'];
$user_role = $_SESSION['user_role'];

// Get user details from database
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $student_id = $_POST['student_id'];
    
    // Check if email is already taken by another user
    $check_email = "SELECT id FROM users WHERE email = ? AND id != ?";
    $stmt_check = $conn->prepare($check_email);
    $stmt_check->bind_param("si", $email, $user_id);
    $stmt_check->execute();
    $email_result = $stmt_check->get_result();
    
    if ($email_result->num_rows > 0) {
        $_SESSION['profile_error'] = "Email is already taken by another user.";
    } else {
        // Update user profile
        $update_query = "UPDATE users SET name = ?, email = ?, student_id = ? WHERE id = ?";
        $stmt_update = $conn->prepare($update_query);
        $stmt_update->bind_param("sssi", $name, $email, $student_id, $user_id);
        
        if ($stmt_update->execute()) {
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['profile_success'] = "Profile updated successfully!";
            header("Location: profile.php");
            exit();
        } else {
            $_SESSION['profile_error'] = "Error updating profile. Please try again.";
        }
    }
}

// Handle password change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    if (password_verify($current_password, $user_data['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_password = "UPDATE users SET password = ? WHERE id = ?";
            $stmt_pass = $conn->prepare($update_password);
            $stmt_pass->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt_pass->execute()) {
                $_SESSION['profile_success'] = "Password changed successfully!";
            } else {
                $_SESSION['profile_error'] = "Error changing password. Please try again.";
            }
        } else {
            $_SESSION['profile_error'] = "New passwords do not match.";
        }
    } else {
        $_SESSION['profile_error'] = "Current password is incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Focus Bridge</title>
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
        
        .profile-card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            transition: background-color 0.3s ease;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
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

        .nav-pills .nav-link {
            border-radius: 10px;
            margin-right: 10px;
        }

        .nav-pills .nav-link.active {
            background-color: #3b82f6;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .profile-header {
                padding: 20px;
            }
            
            .profile-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light shadow-sm">
        <div class="container">
            <a class="navbar-brand logo text-primary fs-3" href="<?php echo $user_role === 'student' ? 'student_dashboard.php' : 'admin_dashboard.php'; ?>">Focus Bridge</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
  <a class="nav-link" href="<?php
    if (isset($user_role)) {
        if ($user_role === 'student') {
            echo 'student_dashboard.php';
        } elseif ($user_role === 'admin') { // class representative
            echo 'admin_dashboard.php';
        } elseif ($user_role === 'teacher') {
            echo 'teacher_dashboard.php';
        } else {
            echo 'login.php'; // fallback if role unknown
        }
    } else {
        echo 'login.php'; // fallback if role not set
    }
  ?>">Dashboard</a>
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

    <!-- Main Content -->
    <div class="container py-5">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2">Profile Settings</h2>
                    <p class="mb-0">Manage your account information and preferences</p>
                </div>
                <div class="col-md-4 text-end">
                    <i class="ri-user-settings-line display-1"></i>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['profile_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['profile_success']; unset($_SESSION['profile_success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['profile_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['profile_error']; unset($_SESSION['profile_error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Profile Content -->
        <div class="row">
            <div class="col-md-3">
                <div class="profile-card">
                    <div class="text-center mb-4">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="ri-user-line text-white display-6"></i>
                        </div>
                        <h5 class="mt-3 mb-1"><?php echo htmlspecialchars($user_data['name']); ?></h5>
                        <p class="text-muted mb-0"><?php echo ucfirst($user_data['role']); ?></p>
                    </div>
                    
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="pill" href="#profile-info">Profile Information</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#change-password">Change Password</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#account-stats">Account Statistics</a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="tab-content">
                    <!-- Profile Information Tab -->
                    <div class="tab-pane fade show active" id="profile-info">
                        <div class="profile-card">
                            <h5 class="mb-4"><i class="ri-user-line me-2"></i>Profile Information</h5>
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="student_id" class="form-label">Student ID</label>
                                        <input type="text" class="form-control" id="student_id" name="student_id" value="<?php echo htmlspecialchars($user_data['student_id']); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="role" class="form-label">Role</label>
                                        <input type="text" class="form-control" value="<?php echo ucfirst($user_data['role']); ?>" readonly>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="created_at" class="form-label">Member Since</label>
                                        <input type="text" class="form-control" value="<?php echo date('F j, Y', strtotime($user_data['created_at'])); ?>" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="last_updated" class="form-label">Last Updated</label>
                                        <input type="text" class="form-control" value="<?php echo date('F j, Y', strtotime($user_data['updated_at'])); ?>" readonly>
                                    </div>
                                </div>
                                <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Change Password Tab -->
                    <div class="tab-pane fade" id="change-password">
                        <div class="profile-card">
                            <h5 class="mb-4"><i class="ri-lock-line me-2"></i>Change Password</h5>
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                </div>
                                <button type="submit" name="change_password" class="btn btn-warning">Change Password</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Account Statistics Tab -->
                    <div class="tab-pane fade" id="account-stats">
                        <div class="profile-card">
                            <h5 class="mb-4"><i class="ri-bar-chart-line me-2"></i>Account Statistics</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="text-center p-3 border rounded">
                                        <i class="ri-timer-line text-primary display-6 mb-2"></i>
                                        <h5>Study Sessions</h5>
                                        <h3 class="text-primary">
                                            <?php
                                            $sessions_count = $conn->query("SELECT COUNT(*) as count FROM sessions WHERE user_id = $user_id")->fetch_assoc()['count'];
                                            echo $sessions_count;
                                            ?>
                                        </h3>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="text-center p-3 border rounded">
                                        <i class="ri-time-line text-success display-6 mb-2"></i>
                                        <h5>Total Study Time</h5>
                                        <h3 class="text-success">
                                            <?php
                                            $total_time = $conn->query("SELECT SUM(duration) as total FROM sessions WHERE user_id = $user_id")->fetch_assoc()['total'] ?? 0;
                                            echo floor($total_time / 60) . 'h';
                                            ?>
                                        </h3>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="text-center p-3 border rounded">
                                        <i class="ri-calendar-check-line text-warning display-6 mb-2"></i>
                                        <h5>Upcoming Exams</h5>
                                        <h3 class="text-warning">
                                            <?php
                                            $exams_count = $conn->query("SELECT COUNT(*) as count FROM exams WHERE exam_date >= CURDATE() AND status = 'upcoming'")->fetch_assoc()['count'];
                                            echo $exams_count;
                                            ?>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($user_role === 'admin' || $user_role === 'teacher'): ?>
                            <div class="row mt-4">
                                <div class="col-md-4 mb-3">
                                    <div class="text-center p-3 border rounded">
                                        <i class="ri-file-chart-line text-info display-6 mb-2"></i>
                                        <h5>Notes Uploaded</h5>
                                        <h3 class="text-info">
                                            <?php
                                            $notes_count = $conn->query("SELECT COUNT(*) as count FROM notes WHERE uploaded_by = $user_id")->fetch_assoc()['count'];
                                            echo $notes_count;
                                            ?>
                                        </h3>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="text-center p-3 border rounded">
                                        <i class="ri-quote-text text-danger display-6 mb-2"></i>
                                        <h5>Quotes Posted</h5>
                                        <h3 class="text-danger">
                                            <?php
                                            $quotes_count = $conn->query("SELECT COUNT(*) as count FROM quotes WHERE created_by = $user_id")->fetch_assoc()['count'];
                                            echo $quotes_count;
                                            ?>
                                        </h3>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="text-center p-3 border rounded">
                                        <i class="ri-notification-3-line text-secondary display-6 mb-2"></i>
                                        <h5>Notices Posted</h5>
                                        <h3 class="text-secondary">
                                            <?php
                                            $notices_count = $conn->query("SELECT COUNT(*) as count FROM notices WHERE created_by = $user_id")->fetch_assoc()['count'];
                                            echo $notices_count;
                                            ?>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
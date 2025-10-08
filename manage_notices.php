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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_notice'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $notice_type = $_POST['notice_type'];
    $priority = $_POST['priority'];
    
    $sql = "INSERT INTO notices (title, content, notice_type, priority, created_by) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $title, $content, $notice_type, $priority, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Notice posted successfully!";
    } else {
        $_SESSION['error_message'] = "Error posting notice. Please try again.";
    }
    header("Location: manage_notices.php");
    exit();
}

// Handle notice deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM notices WHERE id = ? AND created_by = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("ii", $delete_id, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Notice deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting notice.";
    }
    header("Location: manage_notices.php");
    exit();
}

// Handle notice status toggle
if (isset($_GET['toggle_id'])) {
    $toggle_id = $_GET['toggle_id'];
    $toggle_sql = "UPDATE notices SET is_active = NOT is_active WHERE id = ? AND created_by = ?";
    $stmt = $conn->prepare($toggle_sql);
    $stmt->bind_param("ii", $toggle_id, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Notice status updated!";
    } else {
        $_SESSION['error_message'] = "Error updating notice status.";
    }
    header("Location: manage_notices.php");
    exit();
}

// Fetch all notices
$notices_query = "SELECT * FROM notices ORDER BY created_at DESC";
$notices_result = $conn->query($notices_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Notices - Focus Bridge</title>
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
        
        .notice-card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 20px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        
        .notice-card:hover {
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

        .priority-badge {
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
                        <a class="nav-link" href="manage_exams.php">Exam Schedule</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="notes_upload.php">Notes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="manage_notices.php">Notice</a>
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
            <h1 class="display-4 fw-bold mb-3">Manage Notices</h1>
            <p class="lead">Post and manage important announcements for students.</p>
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

        <!-- Add Notice Form -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="notice-card">
                    <h4 class="mb-4"><i class="ri-add-circle-line me-2"></i>Post New Notice</h4>
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="title" class="form-label">Notice Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select class="form-select" id="priority" name="priority" required>
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="notice_type" class="form-label">Notice Type</label>
                            <select class="form-select" id="notice_type" name="notice_type" required>
                                <option value="text">Text Notice</option>
                                <option value="urgent">Urgent Notice</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Notice Content</label>
                            <textarea class="form-control" id="content" name="content" rows="5" required placeholder="Enter your notice content here..."></textarea>
                        </div>
                        <button type="submit" name="add_notice" class="btn btn-primary">Post Notice</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Notices List -->
        <div class="row">
            <div class="col-12">
                <h4 class="mb-4"><i class="ri-notification-3-line me-2"></i>All Notices</h4>
                <?php if ($notices_result->num_rows > 0): ?>
                    <?php while($notice = $notices_result->fetch_assoc()): ?>
                        <div class="notice-card">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center mb-2">
                                        <h5 class="mb-0 me-3"><?php echo htmlspecialchars($notice['title']); ?></h5>
                                        <span class="badge bg-<?php 
                                            echo $notice['priority'] === 'urgent' ? 'danger' : 
                                                ($notice['priority'] === 'high' ? 'warning' : 
                                                ($notice['priority'] === 'medium' ? 'primary' : 'secondary')); 
                                        ?> priority-badge">
                                            <?php echo ucfirst($notice['priority']); ?>
                                        </span>
                                        <span class="badge bg-<?php echo $notice['is_active'] ? 'success' : 'secondary'; ?> priority-badge ms-2">
                                            <?php echo $notice['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </div>
                                    <p class="mb-3"><?php echo nl2br(htmlspecialchars($notice['content'])); ?></p>
                                    <div class="d-flex align-items-center text-muted small">
                                        <i class="ri-calendar-line me-1"></i>
                                        <span><?php echo date('F j, Y g:i A', strtotime($notice['created_at'])); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="btn-group" role="group">
                                        <a href="manage_notices.php?toggle_id=<?php echo $notice['id']; ?>" 
                                           class="btn btn-outline-<?php echo $notice['is_active'] ? 'warning' : 'success'; ?> btn-sm">
                                            <i class="ri-<?php echo $notice['is_active'] ? 'eye-off' : 'eye'; ?>-line"></i> 
                                            <?php echo $notice['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                        </a>
                                        <a href="manage_notices.php?delete_id=<?php echo $notice['id']; ?>" 
                                           class="btn btn-outline-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this notice?')">
                                            <i class="ri-delete-bin-line"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="notice-card text-center">
                        <i class="ri-notification-3-line display-1 text-muted mb-3"></i>
                        <h5 class="text-muted">No notices found</h5>
                        <p class="text-muted">Post your first notice using the form above.</p>
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

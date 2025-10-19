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

// Get filter parameters
$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : '';

// Build query for active notices only
$notices_query = "SELECT * FROM notices WHERE is_active = 1";

if ($priority_filter) {
    $notices_query .= " AND priority = ?";
}

$notices_query .= " ORDER BY 
    CASE priority 
        WHEN 'urgent' THEN 1 
        WHEN 'high' THEN 2 
        WHEN 'medium' THEN 3 
        WHEN 'low' THEN 4 
    END, 
    created_at DESC";

$stmt = $conn->prepare($notices_query);
if ($priority_filter) {
    $stmt->bind_param("s", $priority_filter);
}
$stmt->execute();
$notices_result = $stmt->get_result();

// Get notice statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as urgent,
    SUM(CASE WHEN priority = 'high' THEN 1 ELSE 0 END) as high,
    SUM(CASE WHEN priority = 'medium' THEN 1 ELSE 0 END) as medium,
    SUM(CASE WHEN priority = 'low' THEN 1 ELSE 0 END) as low
    FROM notices WHERE is_active = 1";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notices - Focus Bridge</title>
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
        
        .notice-card.urgent {
            border-left: 5px solid #dc3545;
        }
        
        .notice-card.high {
            border-left: 5px solid #fd7e14;
        }
        
        .notice-card.medium {
            border-left: 5px solid #0d6efd;
        }
        
        .notice-card.low {
            border-left: 5px solid #6c757d;
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

        .stats-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: background-color 0.3s ease;
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
                        <a class="nav-link" href="exams.php">Exam Schedule</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_notes.php">Notes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="quotes.php">Motivational Quotes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="notices.php">Notice</a>
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
            <h1 class="display-4 fw-bold mb-3">Important Notices</h1>
            <p class="lead">Stay updated with the latest announcements and important information.</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">
        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-2 col-6 mb-3">
                <div class="stats-card">
                    <h5 class="text-primary"><?php echo $stats['total']; ?></h5>
                    <small class="text-muted">Total</small>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="stats-card">
                    <h5 class="text-danger"><?php echo $stats['urgent']; ?></h5>
                    <small class="text-muted">Urgent</small>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="stats-card">
                    <h5 class="text-warning"><?php echo $stats['high']; ?></h5>
                    <small class="text-muted">High</small>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="stats-card">
                    <h5 class="text-info"><?php echo $stats['medium']; ?></h5>
                    <small class="text-muted">Medium</small>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="stats-card">
                    <h5 class="text-secondary"><?php echo $stats['low']; ?></h5>
                    <small class="text-muted">Low</small>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="stats-card">
                    <i class="ri-notification-3-line text-primary display-6"></i>
                    <small class="text-muted">Active</small>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-6">
                <form method="GET" class="d-flex gap-2">
                    <select name="priority" class="form-select" style="max-width: 200px;">
                        <option value="">All Priorities</option>
                        <option value="urgent" <?php echo $priority_filter === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                        <option value="high" <?php echo $priority_filter === 'high' ? 'selected' : ''; ?>>High</option>
                        <option value="medium" <?php echo $priority_filter === 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="low" <?php echo $priority_filter === 'low' ? 'selected' : ''; ?>>Low</option>
                    </select>
                    <button type="submit" class="btn btn-outline-primary">Filter</button>
                    <a href="notices.php" class="btn btn-outline-secondary">Clear</a>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <small class="text-muted">
                    <i class="ri-information-line me-1"></i>
                    Showing active notices only
                </small>
            </div>
        </div>

        <!-- Notices List -->
        <div class="row">
            <div class="col-12">
                <?php if ($notices_result->num_rows > 0): ?>
                    <?php while($notice = $notices_result->fetch_assoc()): ?>
                        <div class="notice-card <?php echo $notice['priority']; ?>">
                            <div class="row align-items-start">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center mb-3">
                                        <h5 class="mb-0 me-3"><?php echo htmlspecialchars($notice['title']); ?></h5>
                                        <span class="badge bg-<?php 
                                            echo $notice['priority'] === 'urgent' ? 'danger' : 
                                                ($notice['priority'] === 'high' ? 'warning' : 
                                                ($notice['priority'] === 'medium' ? 'primary' : 'secondary')); 
                                        ?> priority-badge">
                                            <?php echo ucfirst($notice['priority']); ?>
                                        </span>
                                    </div>
                                    <div class="notice-content">
                                        <?php echo nl2br(htmlspecialchars($notice['content'])); ?>
                                    </div>
                                    <div class="d-flex align-items-center text-muted small mt-3">
                                        <i class="ri-calendar-line me-1"></i>
                                        <span><?php echo date('F j, Y g:i A', strtotime($notice['created_at'])); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="d-flex flex-column align-items-end">
                                        <?php if ($notice['priority'] === 'urgent'): ?>
                                            <span class="badge bg-danger mb-2">
                                                <i class="ri-alarm-warning-line me-1"></i>URGENT
                                            </span>
                                        <?php endif; ?>
                                        <div class="text-muted small">
                                            <i class="ri-eye-line me-1"></i>
                                            Active Notice
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="notice-card text-center">
                        <i class="ri-notification-3-line display-1 text-muted mb-3"></i>
                        <h5 class="text-muted">No notices found</h5>
                        <p class="text-muted">
                            <?php if ($priority_filter): ?>
                                No notices found with the selected priority filter.
                            <?php else: ?>
                                No active notices at the moment. Check back later for updates.
                            <?php endif; ?>
                        </p>
                        <?php if ($priority_filter): ?>
                            <a href="notices.php" class="btn btn-primary">View All Notices</a>
                        <?php endif; ?>
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

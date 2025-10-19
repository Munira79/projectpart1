<?php
session_start();
include 'db_config.php';

// Check if user is logged in and has admin/teacher role
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'teacher')) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : $_SESSION['user_email'];


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_quote'])) {
    $quote_text = $_POST['quote_text'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    $sql = "INSERT INTO quotes (quote_text, author, category, is_featured, created_by) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $quote_text, $author, $category, $is_featured, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Quote posted successfully!";
    } else {
        $_SESSION['error_message'] = "Error posting quote. Please try again.";
    }
    header("Location: manage_quotes.php");
    exit();
}

// Handle quote deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM quotes WHERE id = ? AND created_by = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("ii", $delete_id, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Quote deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting quote.";
    }
    header("Location: manage_quotes.php");
    exit();
}

// Handle featured toggle
if (isset($_GET['toggle_featured_id'])) {
    $toggle_id = $_GET['toggle_featured_id'];
    $toggle_sql = "UPDATE quotes SET is_featured = NOT is_featured WHERE id = ? AND created_by = ?";
    $stmt = $conn->prepare($toggle_sql);
    $stmt->bind_param("ii", $toggle_id, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Quote featured status updated!";
    } else {
        $_SESSION['error_message'] = "Error updating quote status.";
    }
    header("Location: manage_quotes.php");
    exit();
}

// Fetch all quotes
$quotes_query = "SELECT * FROM quotes ORDER BY created_at DESC";
$quotes_result = $conn->query($quotes_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Quotes - Focus Bridge</title>
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
        
        .quote-card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 20px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        
        .quote-card:hover {
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

        .category-badge {
            font-size: 0.8rem;
            padding: 4px 8px;
        }

        .quote-text {
            font-style: italic;
            font-size: 1.1rem;
            line-height: 1.6;
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
                        <a class="nav-link" href="manage_notices.php">Notice</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="manage_quotes.php">Motivational Quotes</a>
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
            <h1 class="display-4 fw-bold mb-3">Manage Quotes</h1>
            <p class="lead">Share inspiring quotes and motivational content with students.</p>
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

        <!-- Add Quote Form -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="quote-card">
                    <h4 class="mb-4"><i class="ri-add-circle-line me-2"></i>Add New Quote</h4>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="quote_text" class="form-label">Quote Text</label>
                            <textarea class="form-control" id="quote_text" name="quote_text" rows="4" required placeholder="Enter the motivational quote here..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="author" class="form-label">Author</label>
                                <input type="text" class="form-control" id="author" name="author" placeholder="Enter author name (optional)">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="motivation">Motivation</option>
                                    <option value="success">Success</option>
                                    <option value="education">Education</option>
                                    <option value="perseverance">Perseverance</option>
                                    <option value="learning">Learning</option>
                                    <option value="general">General</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured">
                                <label class="form-check-label" for="is_featured">
                                    Mark as Featured Quote
                                </label>
                            </div>
                        </div>
                        <button type="submit" name="add_quote" class="btn btn-primary">Add Quote</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Quotes List -->
        <div class="row">
            <div class="col-12">
                <h4 class="mb-4"><i class="ri-quote-text me-2"></i>All Quotes</h4>
                <?php if ($quotes_result->num_rows > 0): ?>
                    <?php while($quote = $quotes_result->fetch_assoc()): ?>
                        <div class="quote-card">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="badge bg-primary category-badge me-2"><?php echo ucfirst($quote['category']); ?></span>
                                        <?php if ($quote['is_featured']): ?>
                                            <span class="badge bg-warning category-badge">Featured</span>
                                        <?php endif; ?>
                                    </div>
                                    <blockquote class="blockquote mb-3">
                                        <p class="quote-text">"<?php echo htmlspecialchars($quote['quote_text']); ?>"</p>
                                        <?php if ($quote['author']): ?>
                                            <footer class="blockquote-footer">
                                                <cite title="Source Title"><?php echo htmlspecialchars($quote['author']); ?></cite>
                                            </footer>
                                        <?php endif; ?>
                                    </blockquote>
                                    <div class="d-flex align-items-center text-muted small">
                                        <i class="ri-calendar-line me-1"></i>
                                        <span><?php echo date('F j, Y g:i A', strtotime($quote['created_at'])); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="btn-group" role="group">
                                        <a href="manage_quotes.php?toggle_featured_id=<?php echo $quote['id']; ?>" 
                                           class="btn btn-outline-<?php echo $quote['is_featured'] ? 'warning' : 'success'; ?> btn-sm">
                                            <i class="ri-<?php echo $quote['is_featured'] ? 'star-fill' : 'star-line'; ?>"></i> 
                                            <?php echo $quote['is_featured'] ? 'Unfeature' : 'Feature'; ?>
                                        </a>
                                        <a href="manage_quotes.php?delete_id=<?php echo $quote['id']; ?>" 
                                           class="btn btn-outline-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this quote?')">
                                            <i class="ri-delete-bin-line"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="quote-card text-center">
                        <i class="ri-quote-text display-1 text-muted mb-3"></i>
                        <h5 class="text-muted">No quotes found</h5>
                        <p class="text-muted">Add your first motivational quote using the form above.</p>
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

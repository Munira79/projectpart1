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


// Handle adding to favorites
if (isset($_GET['favorite_id'])) {
    $quote_id = $_GET['favorite_id'];
    $check_favorite = "SELECT id FROM user_favorites WHERE user_id = ? AND quote_id = ?";
    $stmt = $conn->prepare($check_favorite);
    $stmt->bind_param("ii", $user_id, $quote_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $add_favorite = "INSERT INTO user_favorites (user_id, quote_id) VALUES (?, ?)";
        $stmt = $conn->prepare($add_favorite);
        $stmt->bind_param("ii", $user_id, $quote_id);
        $stmt->execute();
        $_SESSION['success_message'] = "Quote added to favorites!";
    } else {
        $_SESSION['error_message'] = "Quote already in favorites!";
    }
    header("Location: quotes.php");
    exit();
}

// Handle removing from favorites
if (isset($_GET['unfavorite_id'])) {
    $quote_id = $_GET['unfavorite_id'];
    $remove_favorite = "DELETE FROM user_favorites WHERE user_id = ? AND quote_id = ?";
    $stmt = $conn->prepare($remove_favorite);
    $stmt->bind_param("ii", $user_id, $quote_id);
    $stmt->execute();
    $_SESSION['success_message'] = "Quote removed from favorites!";
    header("Location: quotes.php");
    exit();
}

// Get filter parameters
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$show_favorites = isset($_GET['favorites']) ? $_GET['favorites'] : '';

// Build query
$quotes_query = "SELECT q.*, uf.id as is_favorite FROM quotes q 
                 LEFT JOIN user_favorites uf ON q.id = uf.quote_id AND uf.user_id = ?";

if ($show_favorites) {
    $quotes_query .= " WHERE uf.id IS NOT NULL";
} else {
    $quotes_query .= " WHERE 1=1";
}

if ($category_filter) {
    $quotes_query .= " AND q.category = ?";
}

$quotes_query .= " ORDER BY q.is_featured DESC, q.created_at DESC";

$stmt = $conn->prepare($quotes_query);
if ($category_filter) {
    $stmt->bind_param("is", $user_id, $category_filter);
} else {
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$quotes_result = $stmt->get_result();

// Get categories for filter
$categories_query = "SELECT DISTINCT category FROM quotes ORDER BY category";
$categories_result = $conn->query($categories_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motivational Quotes - Focus Bridge</title>
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
        
        .quote-card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 25px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            position: relative;
        }
        
        .quote-card:hover {
            transform: translateY(-3px);
        }
        
        .quote-card.featured {
            border: 2px solid #fbbf24;
            background: linear-gradient(135deg, #fef3c7, #fde68a);
        }
        
        [data-theme="dark"] .quote-card.featured {
            background: linear-gradient(135deg, #451a03, #78350f);
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
            font-size: 1.2rem;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .favorite-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .favorite-btn:hover {
            transform: scale(1.2);
        }

        .featured-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: #fbbf24;
            color: #92400e;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: bold;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .hero-section {
                padding: 40px 0;
            }
            
            .quote-card {
                padding: 20px;
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
                        <a class="nav-link active" href="quotes.php">Motivational Quotes</a>
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
            <h1 class="display-4 fw-bold mb-3">Motivational Quotes</h1>
            <p class="lead">Get inspired and stay motivated with these uplifting quotes.</p>
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

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-8">
                <form method="GET" class="d-flex gap-2">
                    <select name="category" class="form-select" style="max-width: 200px;">
                        <option value="">All Categories</option>
                        <?php while($category = $categories_result->fetch_assoc()): ?>
                            <option value="<?php echo $category['category']; ?>" <?php echo $category_filter === $category['category'] ? 'selected' : ''; ?>>
                                <?php echo ucfirst($category['category']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" class="btn btn-outline-primary">Filter</button>
                    <a href="quotes.php" class="btn btn-outline-secondary">Clear</a>
                </form>
            </div>
            <div class="col-md-4 text-end">
                <a href="quotes.php?favorites=1" class="btn btn-outline-warning">
                    <i class="ri-heart-fill me-2"></i>My Favorites
                </a>
            </div>
        </div>

        <!-- Quotes Grid -->
        <div class="row">
            <?php if ($quotes_result->num_rows > 0): ?>
                <?php while($quote = $quotes_result->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="quote-card <?php echo $quote['is_featured'] ? 'featured' : ''; ?>">
                            <?php if ($quote['is_featured']): ?>
                                <div class="featured-badge">Featured</div>
                            <?php endif; ?>
                            
                            <button class="favorite-btn" onclick="toggleFavorite(<?php echo $quote['id']; ?>, <?php echo $quote['is_favorite'] ? 'true' : 'false'; ?>)">
                                <i class="ri-heart-<?php echo $quote['is_favorite'] ? 'fill' : 'line'; ?> <?php echo $quote['is_favorite'] ? 'text-danger' : 'text-muted'; ?>"></i>
                            </button>
                            
                            <div class="text-center mb-3">
                                <span class="badge bg-primary category-badge"><?php echo ucfirst($quote['category']); ?></span>
                            </div>
                            
                            <blockquote class="blockquote text-center">
                                <p class="quote-text">"<?php echo htmlspecialchars($quote['quote_text']); ?>"</p>
                                <?php if ($quote['author']): ?>
                                    <footer class="blockquote-footer">
                                        <cite title="Source Title"><?php echo htmlspecialchars($quote['author']); ?></cite>
                                    </footer>
                                <?php endif; ?>
                            </blockquote>
                            
                            <div class="text-center text-muted small">
                                <i class="ri-calendar-line me-1"></i>
                                <?php echo date('M j, Y', strtotime($quote['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="quote-card text-center">
                        <i class="ri-quote-text display-1 text-muted mb-3"></i>
                        <h5 class="text-muted">No quotes found</h5>
                        <p class="text-muted">
                            <?php if ($show_favorites): ?>
                                You haven't added any quotes to your favorites yet.
                            <?php else: ?>
                                No quotes match your current filter.
                            <?php endif; ?>
                        </p>
                        <?php if ($show_favorites): ?>
                            <a href="quotes.php" class="btn btn-primary">View All Quotes</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
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
        // Toggle favorite
        function toggleFavorite(quoteId, isFavorite) {
            if (isFavorite) {
                window.location.href = 'quotes.php?unfavorite_id=' + quoteId;
            } else {
                window.location.href = 'quotes.php?favorite_id=' + quoteId;
            }
        }

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

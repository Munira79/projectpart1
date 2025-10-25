<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>FocusBridge - Study Companion</title>

  <!-- Bootstrap & Remix Icons -->
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
    
    .hero {
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      color: white;
      padding: 80px 0;
    }
    
    .feature-card {
      background: var(--card-bg);
      border-radius: 16px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      padding: 24px;
      transition: transform 0.3s ease, background-color 0.3s ease;
    }
    
    .feature-card:hover {
      transform: translateY(-5px);
    }
    
    .stats-box {
      background: var(--card-bg);
      border-radius: 16px;
      padding: 24px;
      text-align: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
      transition: background-color 0.3s ease;
    }
    
    .cta-section {
      background: linear-gradient(to right, #10b981, #34d399);
      color: white;
      padding: 50px 20px;
      border-radius: 16px;
      text-align: center;
      margin: 40px 0;
    }
    
    footer {
      background: var(--footer-bg);
      padding: 20px 0;
      text-align: center;
      color: var(--footer-text);
      transition: background-color 0.3s ease, color 0.3s ease;
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

    /* Responsive design */
    @media (max-width: 768px) {
      .hero {
        padding: 40px 0;
      }
      
      .feature-card {
        margin-bottom: 20px;
      }
    }
  </style>
</head>
<body>

  <!-- Header -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
      <a class="navbar-brand logo text-primary fs-3" href="index.php">FocusBridge</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <li class="nav-item">
            <?php if(isset($_SESSION['user_id'])): ?>
              <a class="nav-link" href="tracker.php">Work Tracker</a>
            <?php else: ?>
              <a class="nav-link" href="#" onclick="showLoginMessage()">Work Tracker</a>
            <?php endif; ?>
          </li>
          <li class="nav-item">
            <?php if(isset($_SESSION['user_id'])): ?>
              <a class="nav-link" href="exams.php">Exam Schedule</a>
            <?php else: ?>
              <a class="nav-link" href="#" onclick="showLoginMessage()">Exam Schedule</a>
            <?php endif; ?>
          </li>
          <li class="nav-item">
            <?php if(isset($_SESSION['user_id'])): ?>
              <a class="nav-link" href="quotes.php">Motivational Quotes</a>
            <?php else: ?>
              <a class="nav-link" href="#" onclick="showLoginMessage()">Motivational Quotes</a>
            <?php endif; ?>
          </li>
          <li class="nav-item">
            <?php if(isset($_SESSION['user_id'])): ?>
              <a class="nav-link" href="view_notes.php">Notes</a>
            <?php else: ?>
              <a class="nav-link" href="#" onclick="showLoginMessage()">Notes</a>
            <?php endif; ?>
          </li>
          <li class="nav-item">
            <?php if(isset($_SESSION['user_id'])): ?>
              <a class="nav-link" href="profile.php">Profile</a>
            <?php else: ?>
              <a class="nav-link" href="#" onclick="showLoginMessage()">Profile</a>
            <?php endif; ?>
          </li>

          <li class="nav-item">
            <button class="theme-toggle" id="themeToggle" onclick="toggleTheme()">🌙</button>
          </li>
          <?php if(isset($_SESSION['user_id'])): ?>
            <!-- If logged in -->
            <li class="nav-item"><a class="btn btn-danger ms-3" href="logout.php">Logout</a></li>
          <?php else: ?>
            <!-- If not logged in -->
            <li class="nav-item"><a class="btn btn-primary ms-3" href="identify.php">Login</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero text-center"
    style="background-image: url('images/study-bg2.jpg');
           background-size: cover;
           background-position: center;
           background-repeat: no-repeat;
           height: 100vh;
           width: 100%;
           display: flex;
           align-items: center;
           justify-content: center;">
    <div class="container text-white">
      <h1 class="display-4 fw-bold">Manage. Motivate. Master.</h1>
      <p class="lead">Your ultimate academic companion for organizing, tracking, and excelling in your study life.</p>
      <a href="register.php" class="btn btn-light mt-3">Get Started</a>
    </div>
  </section>

  <!-- Intro Text Section -->
  <section class="py-5 text-center bg-white">
    <div class="container">
      <h2 class="fw-semibold">Everything You Need to Succeed</h2>
      <p class="lead text-muted">
        Our platform combines essential study tools with motivational features to help you achieve your academic goals.
      </p>
    </div>
  </section>

  <!-- Features Section -->
  <section class="container my-5">
    <h2 class="text-center mb-4">Features</h2>
    <div class="row g-4">
      <div class="col-md-6 col-lg-3">
        <div class="feature-card text-center">
          <i class="ri-lightbulb-flash-line display-6 text-primary mb-2"></i>
          <h5>Daily Motivation</h5>
          <p>Boost your focus with curated quotes and interactive favorites system.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="feature-card text-center">
          <i class="ri-calendar-check-line display-6 text-success mb-2"></i>
          <h5>Exam Reminders</h5>
          <p>Track exams with smart status updates and detailed info.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="feature-card text-center">
          <i class="ri-timer-flash-line display-6 text-warning mb-2"></i>
          <h5>Study Timer</h5>
          <p>Log your sessions and view weekly progress in one dashboard.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="feature-card text-center">
          <i class="ri-folder-upload-line display-6 text-danger mb-2"></i>
          <h5>Notes Manager</h5>
          <p>Upload, tag and organize notes with powerful filters and search.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Tracking Section -->
  <section class="tracking-section py-5 bg-light">
    <div class="container">
      <div class="row align-items-center">
        <!-- Left Text -->
        <div class="col-md-6">
          <h2 class="mb-3">Track Your Progress Like Never Before</h2>
          <p>
            Our intelligent work tracker helps you understand your study habits,
            identify peak productivity hours, and optimize your learning schedule for maximum effectiveness.
          </p>
          <ul>
            <li><strong>Real-time Analytics:</strong> Monitor your study sessions with detailed insights and performance metrics.</li>
            <li><strong>Goal Setting:</strong> Set daily, weekly, and monthly study goals to stay on track.</li>
            <li><strong>Progress Reports:</strong> Weekly summaries and achievements to celebrate your success.</li>
          </ul>
        </div>
        <!-- Right Image -->
        <div class="col-md-6 text-center">
          <img src="images/tracking.jpg" alt="Tracking Illustration" class="img-fluid">
        </div>
      </div>
    </div>
  </section>

  <!-- Statistics Section -->
  <section class="py-5">
    <div class="container">
      <div class="row align-items-center">
        <!-- Image -->
        <div class="col-md-6 text-center">
          <img src="images/stat.jpg" alt="Statistics Image" class="img-fluid rounded shadow">
        </div>
        <!-- Text -->
        <div class="col-md-6">
          <h2 class="fw-bold">Join Thousands of Successful Students</h2>
          <p>
            StudyHub has helped over 10,000 students improve their academic performance and achieve their educational goals.
            Join our community today and start your journey to success.
          </p>
          <div class="d-flex flex-wrap gap-4 mb-3">
            <div>
              <h3 class="fw-bold">10K+</h3>
              <p>Active Students</p>
            </div>
            <div>
              <h3 class="fw-bold">95%</h3>
              <p>Success Rate</p>
            </div>
            <div>
              <h3 class="fw-bold">50K+</h3>
              <p>Study Hours Tracked</p>
            </div>
          </div>
          <a href="#" class="btn btn-primary">Start Your Journey</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Final Call Section -->
  <section class="container-fluid bg-light py-5">
    <div class="container text-center">
      <h2 class="mb-3">Ready to Transform Your Study Experience?</h2>
      <p class="mb-4">
        Join StudyHub today and discover a smarter way to learn, track, and succeed in your academic journey.
      </p>
      <a href="register.php" class="btn btn-primary me-2">Get Started Free</a>
      <a href="view_demo.php" class="btn btn-outline-primary">View Demo</a>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-dark text-white pt-5 pb-4">
    <div class="container text-md-left">
      <div class="row text-md-left">
        <!-- Brand -->
        <div class="col-md-4 col-lg-4 col-xl-4 mx-auto mt-3">
          <h5 class="text-uppercase mb-4 fw-bold">
            <i class="bi bi-mortarboard-fill me-2"></i>FocusBridge
          </h5>
          <p>Your comprehensive study companion for tracking progress, managing exams, and staying motivated.</p>
        </div>
        <!-- Features -->
        <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mt-3">
          <h6 class="text-uppercase mb-4 fw-bold">Features</h6>
          <p><i class="bi bi-lightbulb me-2"></i>Motivational Quotes</p>
          <p><i class="bi bi-calendar-event me-2"></i>Exam Reminders</p>
          <p><i class="bi bi-clock-history me-2"></i>Work Tracker</p>
          <p><i class="bi bi-upload me-2"></i>Notes Upload</p>
        </div>
        <!-- Support -->
        <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
          <h6 class="text-uppercase mb-4 fw-bold">Support</h6>
          <p><i class="bi bi-question-circle me-2"></i>Help Center</p>
          <p><i class="bi bi-envelope me-2"></i>Contact Us</p>
          <p><i class="bi bi-shield-lock me-2"></i>Privacy Policy</p>
          <p><i class="bi bi-file-earmark-text me-2"></i>Terms of Service</p>
        </div>
      </div>
      <!-- Bottom -->
      <div class="row mt-4">
        <div class="col-md-12 text-center">
          <p class="mb-0">&copy; 2024 FocusBridge. All rights reserved.</p>
        </div>
      </div>
    </div>
  </footer>

  <!-- Bootstrap Script -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Custom JavaScript -->
  <script>
    // Show login message for non-logged in users
    function showLoginMessage() {
      alert('Please login first to access this feature!');
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

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      });
    });
  </script>
</body>
</html>
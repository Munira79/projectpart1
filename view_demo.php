<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FocusBridge | View Demo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
  <style>
    :root {
      --primary-color: #53bef8ff; /* sky blue */
      --text-color: #333;
      --bg-color: #f9fbfd;
    }

    body {
      background-color: var(--bg-color);
      color: var(--text-color);
      font-family: "Poppins", sans-serif;
      scroll-behavior: smooth;
    }

    /* Navbar */
    .navbar {
      background-color: white;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .nav-link {
      color: var(--text-color);
      font-weight: 500;
      margin-right: 15px;
    }

    .nav-link:hover {
      color: var(--primary-color);
    }

    /* Hero Section */
    .hero {
      background: linear-gradient(to right, #5bc0f8, #86e5ff);
      color: white;
      text-align: center;
      padding: 100px 20px;
      border-radius: 0 0 50px 50px;
    }

    .hero h1 {
      font-size: 3rem;
      font-weight: 700;
    }

    .hero p {
      font-size: 1.2rem;
      margin-top: 10px;
    }

    /* Feature Cards */
    .feature-card {
      background: white;
      border-radius: 16px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      padding: 30px;
      text-align: center;
      transition: all 0.4s ease;
    }

    .feature-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 8px 15px rgba(0,0,0,0.15);
    }

    .feature-card i {
      font-size: 40px;
      color: var(--primary-color);
      margin-bottom: 15px;
    }

    /* Carousel */
    .carousel-item {
      text-align: center;
      padding: 40px;
    }

    /* CTA */
    .cta-section {
      background-color: var(--primary-color);
      color: white;
      text-align: center;
      padding: 80px 20px;
      border-radius: 20px;
      margin: 80px auto;
    }

    .cta-section h2 {
      font-size: 2rem;
      font-weight: 700;
    }

    .cta-section a {
      background: white;
      color: var(--primary-color);
      border: none;
      font-weight: 600;
      padding: 12px 30px;
      border-radius: 30px;
      text-decoration: none;
      transition: 0.3s;
    }

    .cta-section a:hover {
      background: #e9faff;
      transform: scale(1.05);
    }

    footer {
      background-color: #fff;
      text-align: center;
      padding: 20px;
      color: #555;
      font-size: 14px;
      margin-top: 40px;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
      <a class="navbar-brand fw-bold" href="#" style="color: var(--primary-color);">FocusBridge</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="quotes.php">Motivational Quotes</a></li>
          <li class="nav-item"><a class="nav-link" href="notes.php">View Notes</a></li>
          <li class="nav-item"><a class="nav-link" href="worktracker.php">Work Tracker</a></li>
          <li class="nav-item"><a class="nav-link" href="#">🌙</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero">
    <div class="container">
      <h1>Experience FocusBridge in Action</h1>
      <p>Stay focused, motivated, and organized — see how FocusBridge helps students achieve their goals.</p>
    </div>
  </section>
  <!-- Animated Text Section -->
<section class="animated-text-section py-5 text-center">
  <div class="container">
    <h2 class="animated-text"></h2>
  </div>
</section>

<style>
  .animated-text-section {
    background: #e9faff;
    color: var(--primary-color);
    font-weight: 700;
    font-size: 2rem;
    letter-spacing: 1px;
    position: relative;
    overflow: hidden;
  }

  .animated-text {
    display: inline-block;
    white-space: nowrap;
    border-right: 3px solid var(--primary-color);
    animation: typing 4s steps(40, end), blink 0.7s infinite;
  }

  @keyframes typing {
    from { width: 0; }
    to { width: 100%; }
  }

  @keyframes blink {
    50% { border-color: transparent; }
  }
</style>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const textElement = document.querySelector(".animated-text");
    const text = "“All in one solution for smarter time management and better organization.”";
    let i = 0;

    function typeWriter() {
      if (i < text.length) {
        textElement.textContent += text.charAt(i);
        i++;
        setTimeout(typeWriter, 80);
      }
    }
    typeWriter();
  });
</script>
<!-- Guided Carousel Section -->
<div id="demoCarousel" class="carousel slide my-5" data-bs-ride="carousel" data-bs-interval="2000">
  <div class="carousel-inner text-center">

    <div class="carousel-item active">
      <h4>Step 1: Register Your Account</h4>
      <p>Join FocusBridge by creating a free account with your email or student ID.</p>
    </div>

    <div class="carousel-item">
      <h4>Step 2: Add Your Notes & Tasks</h4>
      <p>Organize your learning materials, assignments, and reminders in one place.</p>
    </div>

    <div class="carousel-item">
      <h4>Step 3: Track Your Study Sessions</h4>
      <p>Use the built-in timer to focus on your work and measure your progress.</p>
    </div>

    <div class="carousel-item">
      <h4>Step 4: Stay Inspired Daily</h4>
      <p>Read motivational quotes and speeches to keep your energy and focus high.</p>
    </div>

    <div class="carousel-item">
      <h4>Step 5: Review and Improve</h4>
      <p>Analyze your habits, stay consistent, and achieve your study goals faster with FocusBridge.</p>
    </div>

  </div>

  <!-- Optional Controls (Next/Prev buttons) -->
  <button class="carousel-control-prev" type="button" data-bs-target="#demoCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#demoCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
  </button>

</div>


<!-- Include AOS Animation CSS -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<section class="unique-section py-5" style="background-color: #f7fbff;">
  <div class="container text-center">

    <!-- 🌟 Smart Tips Cards -->
    <h2 class="fw-bold mb-4 text-primary" data-aos="fade-up">Smart Tips for Students</h2>
    <div class="row g-4 justify-content-center">
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
        <div class="tip-card p-4 shadow rounded-4">
          <h5 class="fw-bold">⏱ Use Focus Sessions</h5>
          <p>Study for 25 minutes, then take a 5-minute break to refresh your brain.</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
        <div class="tip-card p-4 shadow rounded-4">
          <h5 class="fw-bold">📝 Keep Notes by Topic</h5>
          <p>Organize notes in folders or tags for each subject or course.</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
        <div class="tip-card p-4 shadow rounded-4">
          <h5 class="fw-bold">🎯 Set Daily Goals</h5>
          <p>Write 3 small tasks each day and complete them — consistency matters!</p>
        </div>
      </div>
    </div>

    <!-- 🧭 Daily Focus Meter -->
    <div class="mt-5" data-aos="zoom-in">
      <h2 class="fw-bold text-primary">Your Daily Focus Meter</h2>
      <p class="text-muted">Track how focused you are today!</p>
      <div class="progress mx-auto" style="height: 25px; width: 80%; max-width: 500px;">
        <div id="focusBar" class="progress-bar progress-bar-striped progress-bar-animated bg-info" 
             style="width: 0%; font-weight: bold;">0%</div>
      </div>
    </div>

    <!-- 💬 Quote Rotator -->
    <div class="mt-5" data-aos="fade-up">
      <h2 class="fw-bold text-primary mb-3">Quote of the Moment</h2>
      <p id="quoteBox" class="fs-5 fst-italic text-secondary">
        "Believe in yourself."
      </p>
    </div>

  </div>
</section>

<!-- Include AOS Animation Script -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({
    duration: 800,
    once: true
  });

  // 🧭 Focus Meter Animation
  let width = 0;
  const bar = document.getElementById('focusBar');
  const progress = setInterval(() => {
    if (width >= 100) width = 0;
    else width += 10;
    bar.style.width = width + '%';
    bar.innerText = width + '% Focused';
  }, 1000);

  // 💬 Quote Rotator
  const quotes = [
    "Believe in yourself.",
    "Consistency beats motivation.",
    "Small progress is still progress.",
    "Your focus determines your future.",
    "Stay positive, work hard, make it happen."
  ];
  let index = 0;
  setInterval(() => {
    index = (index + 1) % quotes.length;
    const quoteBox = document.getElementById('quoteBox');
    quoteBox.style.opacity = 0;
    setTimeout(() => {
      quoteBox.innerText = `"${quotes[index]}"`;
      quoteBox.style.opacity = 1;
    }, 500);
  }, 3000);
</script>

<!-- Extra Styling -->
<style>
  .tip-card {
    background-color: white;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  .tip-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 6px 15px rgba(0, 136, 204, 0.2);
  }
  #quoteBox {
    transition: opacity 0.5s ease-in-out;
  }
</style>


  <!-- Features Section -->
  <section class="container my-5">
    <div class="text-center mb-5">
      <h2 class="fw-bold">Explore Our Key Features</h2>
      <p class="text-muted">A quick preview of what you’ll get inside FocusBridge.</p>
    </div>

    <div class="row g-4">
      <div class="col-md-4">
        <div class="feature-card">
          <i class="ri-lightbulb-flash-line"></i>
          <h5>Daily Motivation</h5>
          <p>Start your day with inspiring quotes and positive energy every morning.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="feature-card">
          <i class="ri-timer-2-line"></i>
          <h5>Study Timer</h5>
          <p>Stay productive with our built-in work tracker and session timer.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="feature-card">
          <i class="ri-sticky-note-line"></i>
          <h5>Notes Manager</h5>
          <p>Organize your class notes and ideas in one simple, searchable space.</p>
        </div>
      </div>
    </div>

    <div class="row g-4 mt-3">
      <div class="col-md-6">
        <div class="feature-card">
          <i class="ri-calendar-event-line"></i>
          <h5>Exam Reminders</h5>
          <p>Never miss an exam or important deadline — get gentle reminders on time.</p>
        </div>
      </div>
      <div class="col-md-6">
        <div class="feature-card">
          <i class="ri-contrast-drop-line"></i>
          <h5>Dark & Light Mode</h5>
          <p>Choose the look that fits your study mood with our smart theme toggle.</p>
        </div>
      </div>
    </div>
  </section>

  

  <!-- CTA Section -->
  <section class="cta-section">
    <h2>Ready to Focus Smarter?</h2>
    <p>Join hundreds of students already using FocusBridge to improve their study life.</p>
    <a href="index.php">Join Now</a>
  </section>

  <!-- Footer -->
  <footer>
    <p>© 2025 FocusBridge | Developed with 💙 by Munira, Nafisa, Nuri</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

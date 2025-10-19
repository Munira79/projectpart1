<?php
// view_result.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Result | Focus Bridge</title>

    <!-- Remixicon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #0d6efd;
            --card-bg: #ffffff;
            --footer-bg: #212529;
            --footer-text: #ffffff;
            --bg-light: #f8f9fa;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar */
        .navbar {
            background-color: var(--card-bg);
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: bold;
            color: var(--primary-color) !important;
        }

        .navbar-nav .nav-link {
            font-weight: 500;
            color: #333 !important;
            margin: 0 10px;
        }

        .navbar-nav .nav-link:hover {
            color: var(--primary-color) !important;
        }

        /* Page Header */
        .page-header {
            text-align: center;
            padding: 80px 20px 40px;
            background: linear-gradient(135deg, #e3f2fd, #ffffff);
        }

        .page-header h2 {
            font-weight: 700;
            color: var(--primary-color);
        }

        .page-header p {
            color: #555;
            font-size: 1.1rem;
        }

        /* Result Card */
        .result-card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            padding: 30px;
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .result-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.12);
        }

        .result-card p {
            font-size: 1.1rem;
            color: #444;
        }

        .result-card a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: var(--primary-color);
            color: white;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .result-card a:hover {
            background-color: #0b5ed7;
        }

        .result-card i {
            font-size: 1.4rem;
        }

        footer {
            background: var(--footer-bg);
            color: var(--footer-text);
            margin-top: auto;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="ri-graduation-cap-line me-2"></i>Focus Bridge
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="quotes.php">Motivational Quotes</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <h2>Result Access Portal</h2>
        <p>Check your official university result quickly and securely.</p>
    </section>

    <!-- Main Content -->
    <main class="container my-5">
        <div class="result-card">
            <p>To <strong>see your B.Sc. result</strong>, please click below:</p>
            <a href="https://lus.ac.bd/result/" target="_blank">
                <i class="ri-file-chart-line"></i> Go to Result Portal
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="pt-5 pb-4">
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
                    <p><i class="ri-home-line me-3"></i> Focus Bridge Developers</p>
                    <p><i class="ri-mail-line me-3"></i> info@focusbridge.com</p>
                    <p><i class="ri-phone-line me-3"></i> 01734343434</p>
                </div>
            </div>
            <hr class="mb-4">
            <div class="row align-items-center">
                <div class="col-md-7 col-lg-8 text-center text-md-start">
                    <p class="mb-0">&copy; 2024 All Rights Reserved by 
                        <a href="#" class="text-decoration-none text-white fw-bold">Focus Bridge</a>
                    </p>
                </div>
                <div class="col-md-5 col-lg-4 text-center text-md-end mt-3 mt-md-0">
                    <ul class="list-unstyled list-inline">
                        <li class="list-inline-item"><a href="#" class="text-white fs-5"><i class="ri-facebook-box-fill"></i></a></li>
                        <li class="list-inline-item"><a href="#" class="text-white fs-5"><i class="ri-twitter-fill"></i></a></li>
                        <li class="list-inline-item"><a href="#" class="text-white fs-5"><i class="ri-linkedin-box-fill"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Identify Yourself - FocusBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f4f8;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            position: relative;
            overflow: hidden;
        }

        /* Animated Background */
        .animated-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #e6e9f0, #f8f9fa, #e6e9f0);
            background-size: 400% 400%;
            animation: gradient-animation 15s ease infinite;
            z-index: -1;
        }

        @keyframes gradient-animation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 900px;
        }

        .card-option {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            color: #333;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        .card-option:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .card-option .icon {
            font-size: 3rem;
            color: #1d4ed8;
            margin-bottom: 15px;
        }

        .card-option h3 {
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="animated-bg"></div>

    <div class="container">
        <h1 class="display-5 fw-bold mb-3">Welcome to FocusBridge!</h1>
        <p class="lead mb-5 text-muted">Please select your role to continue to your personalized dashboard.</p>

        <?php if (isset($_SESSION['role_error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $_SESSION['role_error']; unset($_SESSION['role_error']); ?>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-md-4">
                <a href="login.php?role=student" class="card-option">
                    <i class="ri-user-2-line icon"></i>
                    <h3>Student</h3>
                    <p class="text-muted text-center">Access your study materials, track progress, and view exams.</p>
                </a>
            </div>
            <div class="col-md-4">
                <a href="login.php?role=admin" class="card-option">
                    <i class="ri-user-star-line icon"></i>
                    <h3>Student (CR)</h3>
                    <p class="text-muted text-center">Class representative with admin privileges to manage content.</p>
                </a>
            </div>
            <div class="col-md-4">
                <a href="login.php?role=teacher" class="card-option">
                    <i class="ri-briefcase-line icon"></i>
                    <h3>Teacher</h3>
                    <p class="text-muted text-center">Access and manage class-related information and content.</p>
                </a>
            </div>
        </div>
        
        <div class="mt-5">
            <a href="logout.php" class="btn btn-outline-secondary">Logout</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
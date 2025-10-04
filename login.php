<?php
session_start();

// Redirect to dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: identify.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - FocusBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f4f8;
        }
        .container-fluid {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
            display: flex;
            width: 900px;
            max-width: 90%;
        }
        .login-form-section {
            padding: 50px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-info-section {
            flex: 1;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }
        .logo-text {
            font-family: 'Pacifico', cursive;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        .footer-text {
            font-size: 0.8rem;
            color: #9ca3af;
            text-align: center;
            margin-top: 20px;
        }
        .social-icons a img {
            width: 24px;
            height: 24px;
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="login-card">
            <div class="login-form-section">
                <h2 class="mb-2 fw-semibold">Sign in</h2>
                <p class="text-muted mb-4">Built for busy students like you.</p>

                <?php if (isset($_SESSION['login_error'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
                    </div>
                <?php endif; ?>

                <form action="login_process.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                    </div>
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary py-2 fw-bold">Continue</button>
                    </div>
                </form>

                <div class="text-center my-3 text-muted">or sign in with</div>
                <div class="d-flex justify-content-center social-icons">
                    <a href="#"><img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google"></a>
                    <a href="#"><img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg" alt="Apple"></a>
                </div>

                <div class="mt-4 text-center">
                    Don't have an account? <a href="register.php" class="text-primary fw-bold text-decoration-none">Sign Up</a>
                </div>
            </div>
            <div class="login-info-section d-none d-md-flex">
                <div class="text-center">
                    <p class="fs-6 mb-2">Everything you need to stay on track.</p>
                    <h3 class="display-6 fw-bold">Manage your  tasks, exam schedule and more. All in one place.</h3>
                    <i class="ri-graduation-cap-line display-1 mt-4"></i>
                    <div class="mt-5 pt-5">
                        <p class="mb-2" style="font-size: 0.7rem;">Happy Journey!</p>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
session_start();
include 'db_config.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's name
$user_name = '';
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT name FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_name = $user['name'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Dashboard - FocusBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f0f4f8; }
        .feature-card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .feature-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">FocusBridge</a>
            <span class="navbar-text ms-auto">
                Welcome, <?php echo htmlspecialchars($user_name); ?>
            </span>
            <a href="logout.php" class="btn btn-danger ms-3">Logout</a>
        </div>
    </nav>

    <section class="container my-5">
        <h1 class="text-center mb-4 fw-bold">Student Dashboard</h1>
        <p class="text-center lead text-muted mb-5">Your tools for a successful semester.</p>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="card feature-card text-center p-4 h-100">
                    <i class="ri-calendar-check-line display-5 text-primary mb-3"></i>
                    <h5 class="fw-bold">Exam Reminders</h5>
                    <p class="text-muted">Keep track of all your upcoming exams and deadlines.</p>
                    <a href="exams.php" class="stretched-link"></a>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card feature-card text-center p-4 h-100">
                    <i class="ri-timer-flash-line display-5 text-success mb-3"></i>
                    <h5 class="fw-bold">Work Tracker</h5>
                    <p class="text-muted">Log your study sessions and track your productivity.</p>
                    <a href="tracker.php" class="stretched-link"></a>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card feature-card text-center p-4 h-100">
                    <i class="ri-folder-upload-line display-5 text-warning mb-3"></i>
                    <h5 class="fw-bold">Notes Manager</h5>
                    <p class="text-muted">Upload and organize all your lecture notes in one place.</p>
                    <a href="view_notes.php" class="stretched-link"></a>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card feature-card text-center p-4 h-100">
                    <i class="ri-lightbulb-flash-line display-5 text-info mb-3"></i>
                    <h5 class="fw-bold">Motivational Quotes</h5>
                    <p class="text-muted">Get inspired with a new quote every day.</p>
                    <a href="quotes.php" class="stretched-link"></a>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
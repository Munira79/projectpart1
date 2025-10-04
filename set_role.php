<?php
session_start();

// Ensure a user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the role from the URL and set the session variable
if (isset($_GET['role'])) {
    $role = $_GET['role'];
    $_SESSION['role'] = $role;

    // Redirect to the correct dashboard
    if ($role === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: student_dashboard.php");
    }
    exit();
} else {
    // If no role is provided, send them back to the identity page
    header("Location: identify.php");
    exit();
}
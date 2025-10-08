<?php
session_start();
include 'db_config.php';

// Ensure a user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the role from the URL and validate against user's actual role
if (isset($_GET['role'])) {
    $selected_role = $_GET['role'];
    $user_role = $_SESSION['user_role'];
    
    // Check if the selected role matches the user's actual role
    if ($selected_role === $user_role) {
        $_SESSION['role'] = $selected_role;
        
        // Redirect to the correct dashboard based on role
        if ($selected_role === 'admin') {
            header("Location: admin_dashboard.php");
        } elseif ($selected_role === 'teacher') {
            header("Location: admin_dashboard.php"); // Teachers use admin dashboard
        } else {
            header("Location: student_dashboard.php");
        }
        exit();
    } else {
        // Role mismatch - show error and redirect back
        $_SESSION['role_error'] = "You don't have permission to access this role. Please select the correct role for your account.";
        header("Location: identify.php");
        exit();
    }
} else {
    // If no role is provided, send them back to the identity page
    header("Location: identify.php");
    exit();
}
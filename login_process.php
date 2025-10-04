<?php
session_start();
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Special check for admin credentials
    if ($email === 'admin' && $password === 'admin') {
        // Set session for admin
        $_SESSION['user_id'] = 1; // Assuming admin is user ID 1
        $_SESSION['user_email'] = 'admin';
        $_SESSION['user_role'] = 'admin';
        header("Location: dashboard.php");
        exit();
    }

    // Regular user login logic
    $sql = "SELECT id, email, password, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            header("Location: identify.php");
            exit();
        } else {
            // Password incorrect
            $_SESSION['login_error'] = "Invalid email or password.";
            header("Location: login.php");
            exit();
        }
    } else {
        // User not found
        $_SESSION['login_error'] = "Invalid email or password.";
        header("Location: login.php");
        exit();
    }
}
?>
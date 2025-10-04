<?php
session_start();
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = isset($_POST['phone']) ? $_POST['phone'] : null;
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['reg_error'] = "Passwords do not match. Please try again.";
        header("Location: register.php");
        exit();
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $sql_check = "SELECT id FROM users WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $_SESSION['reg_error'] = "This email is already registered. Please login.";
        header("Location: register.php");
        exit();
    }

    // Insert new user into database
    $sql_insert = "INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("ssss", $name, $email, $phone, $hashed_password);

    if ($stmt_insert->execute()) {
        $_SESSION['reg_success'] = "Registration successful! You can now log in.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['reg_error'] = "Registration failed. Please try again.";
        header("Location: register.php");
        exit();
    }
}
?>
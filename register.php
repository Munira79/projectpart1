<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register - FocusBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f4f8;
        }
        .container {
            max-width: 500px;
            margin-top: 50px;
        }
        .register-card {
            background-color: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-card">
            <h2 class="mb-3 text-center">Create an account</h2>
            <p class="text-muted text-center mb-4">Start your journey to academic success.</p>

            <?php if (isset($_SESSION['reg_error'])): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $_SESSION['reg_error']; unset($_SESSION['reg_error']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['reg_success'])): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $_SESSION['reg_success']; unset($_SESSION['reg_success']); ?>
                </div>
            <?php endif; ?>

            <form action="register_process.php" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="student_id" class="form-label" id="id_label">Student ID</label>
                    <input type="text" class="form-control" id="student_id" name="student_id" placeholder="Enter your student ID" required>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="student">Student</option>
                        <option value="admin">Student (CR)</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary py-2 fw-bold">Register</button>
                </div>
            </form>

            <div class="mt-4 text-center">
                Already have an account? <a href="login.php" class="text-primary fw-bold text-decoration-none">Sign In</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const idLabel = document.getElementById('id_label');
            const studentIdInput = document.getElementById('student_id');
            
            roleSelect.addEventListener('change', function() {
                if (this.value === 'teacher') {
                    idLabel.textContent = 'Teacher ID';
                    studentIdInput.placeholder = 'Enter your teacher ID';
                    studentIdInput.name = 'teacher_id';
                } else {
                    idLabel.textContent = 'Student ID';
                    studentIdInput.placeholder = 'Enter your student ID';
                    studentIdInput.name = 'student_id';
                }
            });
        });
    </script>
</body>
</html>
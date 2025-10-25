<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register - FocusBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

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
        .ri-eye-line, .ri-eye-off-line {
            cursor: pointer;
            position: absolute;
            right: 15px;
            top: 38px;
        }
        .position-relative {
            position: relative;
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

                <div class="mb-3" id="departmentDiv">
                    <label for="department" class="form-label">Department</label>
                    <select class="form-select" id="department" name="department" required>
                        <option value="">Select Department</option>
                        <option value="CSE">Computer Science & Engineering (CSE)</option>
                        <option value="ECE">Electronics & Communication Engineering (ECE)</option>
                        <option value="EEE">Electrical & Electronics Engineering (EEE)</option>
                        <option value="ME">Mechanical Engineering (ME)</option>
                        <option value="CE">Civil Engineering (CE)</option>
                        <option value="IT">Information Technology (IT)</option>
                        <option value="AE">Aerospace Engineering (AE)</option>
                        <option value="CHE">Chemical Engineering (CHE)</option>
                        <option value="BT">Biotechnology (BT)</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="mb-3" id="batchDiv">
    <label for="batch" class="form-label">Batch</label>
    <select class="form-select" id="batch" name="batch" required>
        <option value="">Select Batch</option>
        <?php
        // Generate batch numbers from 50 to 100 automatically
        for ($i = 50; $i <= 100; $i++) {
            echo "<option value='$i'>$i</option>";
        }
        ?>
    </select>
</div>


                <div class="mb-3" id="sectionDiv">
                    <label for="section" class="form-label">Section</label>
                    <select class="form-select" id="section" name="section" required>
                        <option value="">Select Section</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="E">E</option>
                        <option value="F">F</option>
                        <option value="G">G</option>
                        <option value="H">H</option>
                        <option value="I">I</option>
                        <option value="J">J</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="mb-3 position-relative">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <i class="ri-eye-line" id="toggleIcon-password"></i>
                </div>

                <div class="mb-3 position-relative">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    <i class="ri-eye-line" id="toggleIcon-confirm"></i>
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
            const departmentDiv = document.getElementById('departmentDiv');
            const batchDiv = document.getElementById('batchDiv');
            const sectionDiv = document.getElementById('sectionDiv');
            const departmentSelect = document.getElementById('department');
            const batchSelect = document.getElementById('batch');
            const sectionSelect = document.getElementById('section');

            // Function to toggle fields based on role
            function toggleFields() {
                if (roleSelect.value === 'teacher') {
                    idLabel.textContent = 'Teacher ID';
                    studentIdInput.placeholder = 'Enter your teacher ID';
                    studentIdInput.name = 'teacher_id';
                    departmentDiv.style.display = 'none';
                    batchDiv.style.display = 'none';
                    sectionDiv.style.display = 'none';
                    departmentSelect.required = false;
                    batchSelect.required = false;
                    sectionSelect.required = false;
                } else {
                    idLabel.textContent = 'Student ID';
                    studentIdInput.placeholder = 'Enter your student ID';
                    studentIdInput.name = 'student_id';
                    departmentDiv.style.display = 'block';
                    batchDiv.style.display = 'block';
                    sectionDiv.style.display = 'block';
                    departmentSelect.required = true;
                    batchSelect.required = true;
                    sectionSelect.required = true;
                }
            }

            // Initial check when page loads
            toggleFields();

            // Update on role change
            roleSelect.addEventListener('change', toggleFields);

            // Password toggle logic
            const togglePassword = document.getElementById('toggleIcon-password');
            const toggleConfirm = document.getElementById('toggleIcon-confirm');
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('confirm_password');

            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                togglePassword.classList.toggle('ri-eye-line');
                togglePassword.classList.toggle('ri-eye-off-line');
            });

            toggleConfirm.addEventListener('click', function() {
                const type = confirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmInput.setAttribute('type', type);
                toggleConfirm.classList.toggle('ri-eye-line');
                toggleConfirm.classList.toggle('ri-eye-off-line');
            });
        });
    </script>
</body>
</html>

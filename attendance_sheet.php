<?php
session_start();
include 'db_config.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: login.php?role=teacher");
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'mark_attendance') {
        $subject = $_POST['subject'];
        $class_date = $_POST['class_date'];
        
        // Get all students
        $students_sql = "SELECT id, name, student_id FROM users WHERE role = 'student' ORDER BY name";
        $students_stmt = $conn->prepare($students_sql);
        $students_stmt->execute();
        $students = $students_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Process attendance for each student
        foreach ($students as $student) {
            $student_id = $student['id'];
            $status = $_POST['attendance'][$student_id] ?? 'absent';
            $notes = $_POST['notes'][$student_id] ?? '';
            
            // Check if attendance already exists
            $check_sql = "SELECT id FROM attendance WHERE teacher_id = ? AND student_id = ? AND subject = ? AND class_date = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("iiss", $teacher_id, $student_id, $subject, $class_date);
            $check_stmt->execute();
            $existing = $check_stmt->get_result()->fetch_assoc();
            
            if ($existing) {
                // Update existing attendance
                $update_sql = "UPDATE attendance SET status = ?, notes = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ssi", $status, $notes, $existing['id']);
                $update_stmt->execute();
            } else {
                // Insert new attendance
                $insert_sql = "INSERT INTO attendance (teacher_id, student_id, subject, class_date, status, notes) VALUES (?, ?, ?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("iissss", $teacher_id, $student_id, $subject, $class_date, $status, $notes);
                $insert_stmt->execute();
            }
        }
        
        $_SESSION['success'] = "Attendance marked successfully!";
        header("Location: attendance_sheet.php");
        exit();
    }
}

// Get all students
$students_sql = "SELECT id, name, student_id FROM users WHERE role = 'student' ORDER BY name";
$students_stmt = $conn->prepare($students_sql);
$students_stmt->execute();
$students = $students_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get attendance records for today
$today = date('Y-m-d');
$attendance_sql = "SELECT a.*, u.name as student_name, u.student_id FROM attendance a 
                   JOIN users u ON a.student_id = u.id 
                   WHERE a.teacher_id = ? AND a.class_date = ? 
                   ORDER BY u.name";
$attendance_stmt = $conn->prepare($attendance_sql);
$attendance_stmt->bind_param("is", $teacher_id, $today);
$attendance_stmt->execute();
$attendance_records = $attendance_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Create attendance lookup array
$attendance_lookup = [];
foreach ($attendance_records as $record) {
    $attendance_lookup[$record['student_id']] = $record;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Sheet - FocusBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }
        .main-content {
            background-color: #f8fafc;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-present { background-color: #d1fae5; color: #065f46; }
        .status-absent { background-color: #fee2e2; color: #991b1b; }
        .status-late { background-color: #fef3c7; color: #92400e; }
        .status-excused { background-color: #e0e7ff; color: #3730a3; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-4">
                    <h4 class="text-white mb-4">
                        <i class="ri-graduation-cap-line me-2"></i>
                        FocusBridge
                    </h4>
                </div>
                
                <nav class="nav flex-column px-3">
                    <a class="nav-link" href="teacher_dashboard.php">
                        <i class="ri-dashboard-line me-2"></i> Dashboard
                    </a>
                    <a class="nav-link active" href="attendance_sheet.php">
                        <i class="ri-calendar-check-line me-2"></i> Attendance Sheet
                    </a>
                    <a class="nav-link" href="teacher_work_tracker.php">
                        <i class="ri-time-line me-2"></i> Work Tracker
                    </a>
                    <a class="nav-link" href="lecture_upload.php">
                        <i class="ri-upload-line me-2"></i> Lecture Upload
                    </a>
                    <a class="nav-link" href="assignment_upload.php">
                        <i class="ri-file-text-line me-2"></i> Assignment Upload
                    </a>
                    <a class="nav-link" href="view_lectures.php">
                        <i class="ri-eye-line me-2"></i> View Lectures
                    </a>
                    <a class="nav-link" href="view_assignments.php">
                        <i class="ri-file-list-line me-2"></i> View Assignments
                    </a>
                    <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
                    <a class="nav-link" href="logout.php">
                        <i class="ri-logout-box-line me-2"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Attendance Sheet</h2>
                    <div class="text-muted">
                        <i class="ri-calendar-line me-1"></i>
                        <?php echo date('F j, Y'); ?>
                    </div>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Attendance Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Mark Attendance</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="mark_attendance">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="class_date" class="form-label">Class Date</label>
                                    <input type="date" class="form-control" id="class_date" name="class_date" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                            <?php $existing = $attendance_lookup[$student['id']] ?? null; ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                                <td>
                                                    <select class="form-select form-select-sm" name="attendance[<?php echo $student['id']; ?>]">
                                                        <option value="present" <?php echo ($existing && $existing['status'] == 'present') ? 'selected' : ''; ?>>Present</option>
                                                        <option value="absent" <?php echo ($existing && $existing['status'] == 'absent') ? 'selected' : ''; ?>>Absent</option>
                                                        <option value="late" <?php echo ($existing && $existing['status'] == 'late') ? 'selected' : ''; ?>>Late</option>
                                                        <option value="excused" <?php echo ($existing && $existing['status'] == 'excused') ? 'selected' : ''; ?>>Excused</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" name="notes[<?php echo $student['id']; ?>]" 
                                                           value="<?php echo $existing ? htmlspecialchars($existing['notes']) : ''; ?>" 
                                                           placeholder="Optional notes">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" onclick="markAllPresent()">
                                    <i class="ri-check-line me-1"></i> Mark All Present
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line me-1"></i> Save Attendance
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Export Options -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Export Options</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <button class="btn btn-success w-100" onclick="exportToExcel()">
                                    <i class="ri-file-excel-line me-2"></i> Export to Excel
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-info w-100" onclick="exportToPDF()">
                                    <i class="ri-file-pdf-line me-2"></i> Export to PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function markAllPresent() {
            const selects = document.querySelectorAll('select[name^="attendance"]');
            selects.forEach(select => {
                select.value = 'present';
            });
        }

        function exportToExcel() {
            // This would typically generate an Excel file
            alert('Excel export functionality would be implemented here');
        }

        function exportToPDF() {
            // This would typically generate a PDF file
            alert('PDF export functionality would be implemented here');
        }
    </script>
</body>
</html>
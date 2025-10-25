<?php
session_start();
include('db_config.php');
include('helper_functions.php');

// A user must be logged in and their role must be set as 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin')  {
    header('location: login.php'); // Redirect to login if not authenticated or not an admin
    exit();
}

// Get user ID based on session
$user_id = $_SESSION['user_id'];

// Get user's academic info for targeting
$user_info = getUserInfo($conn, $user_id);

// Function to handle file uploads
if (isset($_POST['upload_notes'])) {
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $tags = mysqli_real_escape_string($conn, $_POST['tags']);
    
    // File upload handling
    $target_dir = "uploads/";
    $file_name = uniqid() . '-' . basename($_FILES["fileToUpload"]["name"]);
    $target_file = $target_dir . $file_name;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        // ✅ FIXED: user_id → uploaded_by and added department, batch, section
        $department = $user_info['department'];
        $batch = $user_info['batch'];
        $section = $user_info['section'];
        
        $sql = "INSERT INTO notes (uploaded_by, subject, title, description, tags, file_path, file_type, department, batch, section) 
                VALUES ('$user_id', '$subject', '$title', '$description', '$tags', '$target_file', '$file_type', '$department', '$batch', '$section')";
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success_message'] = 'Note uploaded successfully! 🎉';
        } else {
            $_SESSION['error_message'] = 'Error: ' . mysqli_error($conn);
        }
    } else {
        $_SESSION['error_message'] = 'Sorry, there was an error uploading your file.';
    }
}

// Function to handle note deletion
if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    
    // ✅ FIXED: user_id → uploaded_by
    $file_query = "SELECT file_path FROM notes WHERE id='$delete_id' AND uploaded_by='$user_id'";
    $file_result = mysqli_query($conn, $file_query);
    $note = mysqli_fetch_assoc($file_result);

    if ($note) {
        if (unlink($note['file_path'])) {
            // ✅ FIXED: user_id → uploaded_by
            $delete_sql = "DELETE FROM notes WHERE id='$delete_id' AND uploaded_by='$user_id'";
            if (mysqli_query($conn, $delete_sql)) {
                $_SESSION['success_message'] = 'Note deleted successfully! 👍';
            } else {
                $_SESSION['error_message'] = 'Error: ' . mysqli_error($conn);
            }
        } else {
            $_SESSION['error_message'] = 'Error deleting file from server.';
        }
    }
    header('location: notes_upload.php');
    exit();
}

// ✅ FIXED: user_id → uploaded_by (already filtering by current user)
$where_clauses = ["uploaded_by='$user_id'"];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where_clauses[] = "(title LIKE '%$search%' OR tags LIKE '%$search%' OR description LIKE '%$search%')";
}

if (isset($_GET['subject_filter']) && !empty($_GET['subject_filter'])) {
    $subject_filter = mysqli_real_escape_string($conn, $_GET['subject_filter']);
    $where_clauses[] = "subject='$subject_filter'";
}

$notes_query = "SELECT * FROM notes WHERE " . implode(" AND ", $where_clauses) . " ORDER BY upload_date DESC";
$notes_result = mysqli_query($conn, $notes_query);

// ✅ FIXED: user_id → uploaded_by
$subjects_query = "SELECT DISTINCT subject FROM notes WHERE uploaded_by='$user_id'";
$subjects_result = mysqli_query($conn, $subjects_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Notes Manager - FocusBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Pacifico&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .logo { font-family: 'Pacifico', cursive; }
        .notes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .note-card { background: white; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 24px; position: relative; }
        .note-card .file-icon { font-size: 2.5rem; }
        .note-card .delete-icon { position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 1.5rem; color: #dc3545; cursor: pointer; }
        .note-card .actions-btn { display: flex; justify-content: space-between; margin-top: 15px; }
        .note-card .badge { margin-right: 5px; }
        .alert-message { position: fixed; top: 20px; right: 20px; z-index: 1050; }
        .upload-modal .modal-content { border-radius: 16px; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
      <div class="container">
        <a class="navbar-brand logo text-primary fs-3" href="admin_dashboard.php">FocusBridge</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link active" href="notes_upload.php">Notes Manager</a></li>
            <li class="nav-item"><a class="btn btn-primary ms-3" href="logout.php">Logout</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <main class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">Your Notes Manager</h2>
                <p class="text-muted">Upload, organize, and manage study materials for your department, batch, and section</p>
            </div>
            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="ri-upload-2-line me-2"></i>Upload Notes
            </button>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-message" role="alert">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-message" role="alert">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <form action="notes_upload.php" method="get" class="mb-5">
            <div class="row g-3">
                <div class="col-md-8">
                    <input type="text" class="form-control" placeholder="Search notes by title or tags..." name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="subject_filter">
                        <option value="">All Subjects</option>
                        <?php while ($subject = mysqli_fetch_assoc($subjects_result)): ?>
                            <option value="<?php echo htmlspecialchars($subject['subject']); ?>" <?php echo (isset($_GET['subject_filter']) && $_GET['subject_filter'] == $subject['subject']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($subject['subject']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
        </form>

        <div class="notes-grid">
            <?php if (mysqli_num_rows($notes_result) > 0): ?>
                <?php while($note = mysqli_fetch_assoc($notes_result)): ?>
                    <div class="note-card">
                        <button class="delete-icon" onclick="confirmDelete(<?php echo $note['id']; ?>)">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                        <div class="d-flex align-items-center mb-3">
                            <?php 
                                $file_icon = 'ri-file-line';
                                $file_color = 'text-primary';
                                switch ($note['file_type']) {
                                    case 'pdf': $file_icon = 'ri-file-pdf-line'; $file_color = 'text-danger'; break;
                                    case 'doc':
                                    case 'docx': $file_icon = 'ri-file-word-line'; $file_color = 'text-info'; break;
                                    case 'jpg':
                                    case 'jpeg':
                                    case 'png': $file_icon = 'ri-image-line'; $file_color = 'text-success'; break;
                                }
                            ?>
                            <i class="<?php echo $file_icon; ?> file-icon <?php echo $file_color; ?>"></i>
                            <div class="ms-3">
                                <h5 class="mb-0"><?php echo htmlspecialchars($note['title']); ?></h5>
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($note['subject']); ?></span>
                            </div>
                        </div>
                        <p class="text-muted"><?php echo htmlspecialchars($note['description']); ?></p>
                        <?php if (!empty($note['tags'])): ?>
                            <div class="mb-2">
                                <?php foreach (explode(',', $note['tags']) as $tag): ?>
                                    <span class="badge bg-light text-dark me-1"><?php echo trim($tag); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-center text-muted small mt-2">
                            <span><i class="ri-calendar-line me-1"></i><?php echo date("F j, Y", strtotime($note['upload_date'])); ?></span>
                            <span><?php echo strtoupper($note['file_type']); ?></span>
                        </div>
                        <div class="actions-btn">
                            <a href="<?php echo htmlspecialchars($note['file_path']); ?>" target="_blank" class="btn btn-outline-primary w-50 me-2"><i class="ri-eye-line me-1"></i>View</a>
                            <a href="<?php echo htmlspecialchars($note['file_path']); ?>" download class="btn btn-primary w-50"><i class="ri-download-2-line me-1"></i>Download</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <div class="alert alert-info" role="alert">No notes found. Upload your first note!</div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content upload-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">Upload New Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="notes_upload.php" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Note Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="tags" class="form-label">Tags (e.g., ai, os, algorithm)</label>
                            <input type="text" class="form-control" id="tags" name="tags" placeholder="Separate with commas">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="fileToUpload" class="form-label">Select File (PDF, DOC, DOCX, JPG, PNG)</label>
                            <input type="file" class="form-control" id="fileToUpload" name="fileToUpload" required>
                        </div>
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            <strong>Target Audience:</strong> This note will be visible to students from 
                            <strong><?php echo $user_info['department'] ?? 'Your Department'; ?></strong>, 
                            <strong>Batch <?php echo $user_info['batch'] ?? 'Your Batch'; ?></strong>, 
                            <strong>Section <?php echo $user_info['section'] ?? 'Your Section'; ?></strong>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" name="upload_notes" class="btn btn-primary">Upload Note</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert-message');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() { alert.style.display = 'none'; }, 500);
            });
        }, 3000);

        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this note?")) {
                window.location.href = 'notes_upload.php?delete_id=' + id;
            }
        }
    </script>
</body>
</html>
<?php
session_start();
include('db_config.php');
include('helper_functions.php');

// A user must be logged in to view notes
if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get content filter for user's department, batch, section
$content_filter = getContentFilter($conn, $user_id);

// Fetch all notes with search/filter functionality
$where_clauses = [$content_filter];

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

// Fetch unique subjects for the filter dropdown
$subjects_query = "SELECT DISTINCT subject FROM notes";
$subjects_result = mysqli_query($conn, $subjects_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>View Notes - FocusBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Pacifico&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .logo { font-family: 'Pacifico', cursive; }
        .notes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .note-card { background: white; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 24px; }
        .note-card .file-icon { font-size: 2.5rem; }
        .note-card .actions-btn { display: flex; justify-content: space-between; margin-top: 15px; }
        .note-card .badge { margin-right: 5px; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
      <div class="container">
        <a class="navbar-brand logo text-primary fs-3" href="student_dashboard.php">FocusBridge</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="student_dashboard.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link active" href="view_notes.php">Notes</a></li>
            <li class="nav-item"><a class="btn btn-primary ms-3" href="logout.php">Logout</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <main class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">Public Notes</h2>
                <p class="text-muted">Browse and download notes uploaded by administrators.</p>
            </div>
        </div>

        <form action="view_notes.php" method="get" class="mb-5">
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
                    <div class="alert alert-info" role="alert">No notes are currently available.</div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
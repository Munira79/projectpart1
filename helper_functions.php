<?php
// Helper functions for FocusBridge application

/**
 * Get user's department, batch, and section information
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @return array|false User info or false if not found
 */
function getUserInfo($conn, $user_id) {
    $sql = "SELECT department, batch, section FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Get content filtering WHERE clause for department, batch, section
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @return string WHERE clause for filtering content
 */
function getContentFilter($conn, $user_id) {
    $user_info = getUserInfo($conn, $user_id);
    if (!$user_info) {
        return "1=0"; // Return false condition if user not found
    }
    
    $department = $user_info['department'];
    $batch = $user_info['batch'];
    $section = $user_info['section'];
    
    // Filter content that matches user's department, batch, and section
    // OR content that has no specific targeting (NULL values)
    return "(department IS NULL OR department = '$department') AND 
            (batch IS NULL OR batch = '$batch') AND 
            (section IS NULL OR section = '$section')";
}

/**
 * Get user's department, batch, section for display
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @return string Formatted string of user's academic info
 */
function getUserAcademicInfo($conn, $user_id) {
    $user_info = getUserInfo($conn, $user_id);
    if (!$user_info) {
        return "Unknown";
    }
    
    $parts = [];
    if ($user_info['department']) $parts[] = $user_info['department'];
    if ($user_info['batch']) $parts[] = "Batch " . $user_info['batch'];
    if ($user_info['section']) $parts[] = "Section " . $user_info['section'];
    
    return implode(" - ", $parts);
}
?>
<?php
session_start();
require_once 'config.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $current_status = $_POST['current_status'] ?? '';
    
    if (!empty($user_id)) {
        // Check if user exists and is not an admin
        $check_sql = "SELECT role FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && $user['role'] !== 'admin') {
            // Toggle the status
            $new_status = $current_status ? 0 : 1;
            
            $update_sql = "UPDATE users SET is_active = ? WHERE user_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ii", $new_status, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Status user berhasil diperbarui!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Gagal memperbarui status user.";
                $_SESSION['message_type'] = "error";
            }
        }
    }
}

header("Location: admin.php");
exit();
?>
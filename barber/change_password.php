<?php
session_start();
header('Content-Type: application/json');

// Database connection configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'kaula_barbershop';

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Terima data JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    if (!isset($_SESSION['username'])) {
        echo json_encode(['success' => false, 'message' => 'Sesi tidak valid!']);
        exit;
    }

    $username = $_SESSION['username'];
    $currentPassword = $data->currentPassword;
    $newPassword = $data->newPassword;

    // Cek password saat ini
    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Verifikasi password saat ini
        if (password_verify($currentPassword, $row['password'])) {
            // Update password baru
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $update_stmt->bind_param("ss", $hashedPassword, $username);
            
            if ($update_stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Password berhasil diubah!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal mengubah password!']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Password saat ini tidak sesuai!']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'User tidak ditemukan!']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
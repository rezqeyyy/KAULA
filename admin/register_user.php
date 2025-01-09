<?php
session_start();
header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

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

    // Get POST data
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    // Check if username already exists
    $stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $stmt->bind_param("s", $data->username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username sudah digunakan!']);
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($data->password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, nama_kapster, password, role, is_active) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param("ssss", $data->username, $data->nama_kapster, $hashedPassword, $data->role);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registrasi berhasil!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal melakukan registrasi']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
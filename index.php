<?php
session_start();

// Database connection configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'kaula_barbershop';

try {
    // Using PDO consistently instead of mixing with mysqli
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Initialize variables
    $error = null;
    $nama_kapster = '';

    // Get kapster name if user is logged in
    if (isset($_SESSION['username'])) {
        $stmt = $pdo->prepare("SELECT nama_kapster FROM users WHERE username = ?");
        $stmt->execute([$_SESSION['username']]);
        if ($row = $stmt->fetch()) {
            $nama_kapster = $row['nama_kapster'];
        }
    }

    // Handle login form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1"); // Tambahkan pengecekan is_active
        $stmt->execute([$username]);

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['nama_kapster'] = $user['nama_kapster'];

                // Redirect based on role
                if ($user['role'] === 'kapster') {
                    header("Location: ../KAULA/barber/barber.php");
                    exit();
                } elseif ($user['role'] === 'admin') {
                    header("Location: ../KAULA/admin/admin.php");
                    exit();
                } else {
                    $error = "Role tidak valid!";
                }
            } else {
                $error = "Password salah!";
            }
        } else {
            // Periksa apakah user ada tapi nonaktif
            $stmt = $pdo->prepare("SELECT is_active FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && !$user['is_active']) {
                $error = "Akun Anda telah dinonaktifkan. Silakan hubungi admin.";
            } else {
                $error = "Username tidak ditemukan!";
            }
        }
    }
} catch (PDOException $e) {
    $error = "Connection failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .custom-input {
        background: rgba(255, 255, 255, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        color: white !important;
        transition: all 0.3s ease;
        padding: 12px 16px;
        border-radius: 10px;
        width: 100%;
    }

    .custom-input:focus {
        background: rgba(255, 255, 255, 0.15) !important;
        border-color: rgba(255, 255, 255, 0.4) !important;
    }

    .custom-button {
        background: linear-gradient(135deg, #00a6ff 0%, #0072ff 100%);
        transition: all 0.3s ease;
    }

    .custom-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 114, 255, 0.3);
    }
    </style>
    <title>KAULA BARBERSHOP - Login</title>
</head>

<body class="min-h-screen flex justify-center items-center">
    <div class="glass-card p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">KAULA BARBERSHOP</h1>
            <p class="text-gray-300">Please login to continue</p>
        </div>

        <?php if (isset($error)): ?>
        <p class="text-red-500 bg-red-100 p-2 mb-4 rounded"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-6">
            <div>
                <input type="text" name="username" placeholder="Enter your username" class="custom-input" required />
            </div>
            <div>
                <input type="password" name="password" placeholder="Enter your password" class="custom-input"
                    required />
            </div>
            <button type="submit" class="custom-button w-full rounded-lg px-6 py-4 text-white font-semibold text-lg">
                Login
            </button>
        </form>
    </div>
</body>

</html>
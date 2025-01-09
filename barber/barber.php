<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

// Tambahkan konstanta lokasi barbershop setelah konfigurasi database
define('BARBERSHOP_LAT', -7.054876); // Latitude Jl. Ketileng Raya
define('BARBERSHOP_LNG', 110.458237); // Longitude Jl. Ketileng Raya
define('MAX_DISTANCE', 200); // Radius maksimum dalam meter

// Database connection configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'kaula_barbershop';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// Fungsi untuk menghitung jarak menggunakan formula Haversine
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $R = 6371000; // Radius bumi dalam meter
    $φ1 = deg2rad($lat1);
    $φ2 = deg2rad($lat2);
    $Δφ = deg2rad($lat2 - $lat1);
    $Δλ = deg2rad($lon2 - $lon1);

    $a = sin($Δφ/2) * sin($Δφ/2) +
         cos($φ1) * cos($φ2) *
         sin($Δλ/2) * sin($Δλ/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));

    return $R * $c; // Jarak dalam meter
}

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Ambil nama_kapster dari database
    $nama_kapster = '';
    $username = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT nama_kapster FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $nama_kapster = $row['nama_kapster'];
    }

    // Check if already attended today
$today = date('Y-m-d');
$check_stmt = $conn->prepare("SELECT id, created_at FROM attendance WHERE nama_kapster = ? AND DATE(date) = CURRENT_DATE()");
$check_stmt->bind_param("s", $nama_kapster);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$already_attended = $check_result->num_rows > 0;
$attendance_time = '';

if ($already_attended) {
    $attendance_record = $check_result->fetch_assoc();
    $attendance_time = date('H:i', strtotime($attendance_record['created_at']));
} else {
    // Reset attendance status if not attended today
    $already_attended = false;
}

// Proses absensi
if (isset($_POST['attendance']) && !$already_attended) {
    $latitude = isset($_POST['lat']) ? floatval($_POST['lat']) : null;
    $longitude = isset($_POST['lng']) ? floatval($_POST['lng']) : null;

    if ($latitude === null || $longitude === null) {
        $attendance_message = "Error: Data lokasi tidak ditemukan!";
        $attendance_status = "error";
    } else {
        $distance = calculateDistance($latitude, $longitude, BARBERSHOP_LAT, BARBERSHOP_LNG);
        
        if ($distance <= MAX_DISTANCE) {
            $status = 'hadir';
            $current_date = date('Y-m-d H:i:s');
            
            // Tambahkan data lokasi ke database
            $stmt = $conn->prepare("INSERT INTO attendance (nama_kapster, status, date, created_at, latitude, longitude) VALUES (?, ?, CURRENT_DATE(), ?, ?, ?)");
            $stmt->bind_param("sssdd", $nama_kapster, $status, $current_date, $latitude, $longitude);
            
            if ($stmt->execute()) {
                $attendance_message = "Absensi berhasil dicatat!";
                $attendance_status = "success";
                $already_attended = true;
                $attendance_time = date('H:i');
            } else {
                $attendance_message = "Error mencatat absensi!";
                $attendance_status = "error";
            }
        } else {
            $attendance_message = "Anda harus berada dalam radius 200 meter dari barbershop untuk melakukan absensi!";
            $attendance_status = "error";
        }
    }
}

    // Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $jenis_treatment = isset($_POST['jenis-treatment']) ? $conn->real_escape_string($_POST['jenis-treatment']) : '';
    $harga = isset($_POST['harga']) ? $conn->real_escape_string($_POST['harga']) : 0;
    $produk = isset($_POST['produk']) ? $conn->real_escape_string($_POST['produk']) : '';
    $quantity = isset($_POST['quantity']) ? $conn->real_escape_string($_POST['quantity']) : 1;
    $product_price = isset($_POST['product-price']) ? $conn->real_escape_string($_POST['product-price']) : 0;
    $total_price = isset($_POST['total-price']) ? $conn->real_escape_string($_POST['total-price']) : 0;
    
    // Set default values or display appropriate message if no product is selected
    if (empty($produk)) {
        $produk = 'Tidak Ada';
        $quantity = 0;
        $product_price = 0;
        $total_price = 0;
    }

    $sql = "INSERT INTO karyawan (nama_kapster, jenis_treatment, harga, produk, quantity, product_price, total_price) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsidd", $nama_kapster, $jenis_treatment, $harga, $produk, $quantity, $product_price, $total_price);

    if ($stmt->execute()) {
        $success_message = "Data berhasil disimpan!";
    } else {
        $error_message = "Error: " . $conn->error;
    }
}

    // Fetch products for dropdown
    $products_query = "SELECT product_name, price FROM products";
    $products_result = $conn->query($products_query);
    $products = [];
    while ($row = $products_result->fetch_assoc()) {
        $products[] = $row;
    }

    // Fetch services for treatment dropdown
    $services_query = "SELECT service_name, price FROM services";
    $services_result = $conn->query($services_query);
    $services = [];
    while ($row = $services_result->fetch_assoc()) {
        $services[] = $row;
    }
    
} catch (Exception $e) {
    $error_message = "Error: " . $e->getMessage();
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

    input[readonly].custom-input {
        background: rgba(255, 255, 255, 0.1) !important;
        color: white !important;
    }

    .custom-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='white' viewBox='0 0 24 24'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
        background-position: right 12px center;
        background-repeat: no-repeat;
        background-size: 20px;
        padding-right: 40px;
        color: white !important;
    }

    .custom-select option {
        background-color: #2d2d2d;
        color: white;
        padding: 12px;
    }

    .submit-button {
        background: linear-gradient(135deg, #00a6ff 0%, #0072ff 100%);
        transition: all 0.3s ease;
    }

    .submit-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 114, 255, 0.3);
    }

    .logout-button {
        background: linear-gradient(135deg, #ff4646 0%, #ff2929 100%);
        transition: all 0.3s ease;
    }

    .logout-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 41, 41, 0.3);
    }

    .form-section {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .section-title {
        color: #00a6ff;
        font-weight: 600;
        margin-bottom: 15px;
        font-size: 1.1rem;
    }

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

    /* Add new attendance button styles */
    .attendance-button {
        background: linear-gradient(135deg, #00ff87 0%, #00c853 100%);
        transition: all 0.3s ease;
        margin-bottom: 1rem;
    }

    .attendance-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 200, 83, 0.3);
    }

    .attendance-message {
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        text-align: center;
        color: white;
    }

    .attendance-message.success {
        background: rgba(0, 200, 83, 0.2);
        border: 1px solid rgba(0, 200, 83, 0.4);
    }

    .attendance-message.warning {
        background: rgba(255, 193, 7, 0.2);
        border: 1px solid rgba(255, 193, 7, 0.4);
    }

    .attendance-message.error {
        background: rgba(244, 67, 54, 0.2);
        border: 1px solid rgba(244, 67, 54, 0.4);
    }
    </style>
    <title>KAULA BARBERSHOP - Input Form</title>
</head>

<body class="min-h-screen py-10 px-4">
    <div class="max-w-xl mx-auto">
        <div class="glass-card p-8 relative">
            <!-- Logo and Title -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-white mb-2">KAULA BARBERSHOP</h1>
                <p class="text-gray-300">Transaction Form</p>
            </div>

            <!-- Attendance Section -->
            <div class="mb-8">
                <form method="POST" action="" id="attendanceForm">
                    <input type="hidden" name="attendance" value="true">
                    <input type="hidden" name="lat" id="latitude">
                    <input type="hidden" name="lng" id="longitude">
                    <button type="button" onclick="submitAttendance()"
                        class="attendance-button w-full rounded-lg px-6 py-4 text-white font-semibold text-lg"
                        <?php echo $already_attended ? 'disabled' : ''; ?>>
                        <?php echo $already_attended ? 'Sudah Absen Hari Ini' : 'Absen Hari Ini'; ?>
                    </button>
                </form>

                <?php if (isset($attendance_message)): ?>
                <div class="attendance-message <?php echo $attendance_status; ?>">
                    <?php echo $attendance_message; ?>
                </div>
                <?php endif; ?>

                <?php if ($already_attended): ?>
                <div class="attendance-message success">
                    Anda telah absen pada pukul <?php echo $attendance_time; ?>
                </div>
                <?php endif; ?>
            </div>

            <form method="POST" action="" class="space-y-6">
                <div class="form-section">
                    <h3 class="section-title">Informasi Kapster</h3>
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Nama Kapster</label>
                        <input type="text" name="nama-kapster" value="<?php echo htmlspecialchars($nama_kapster); ?>"
                            readonly class="custom-input bg-opacity-20 text-white" style="background: rgba(255, 255, 255, 0.15) !important; 
                   color: white !important;
                   border: 1px solid rgba(255, 255, 255, 0.3) !important;" />
                    </div>
                </div>

                <!-- Treatment Section -->
                <div class="form-section">
                    <h3 class="section-title">Treatment</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="jenis-treatment" class="block text-sm font-medium text-gray-300 mb-2">
                                Jenis Treatment
                            </label>
                            <select id="jenis-treatment" name="jenis-treatment" onchange="updatePrice()"
                                class="custom-input custom-select">
                                <option value="" disabled selected>Pilih treatment</option>
                                <?php foreach ($services as $service): ?>
                                <option value="<?php echo htmlspecialchars($service['service_name']); ?>"
                                    data-price="<?php echo htmlspecialchars($service['price']); ?>">
                                    <?php echo htmlspecialchars($service['service_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="harga" class="block text-sm font-medium text-gray-300 mb-2">
                                Harga Treatment
                            </label>
                            <input id="harga" name="harga" type="number" required readonly class="custom-input"
                                placeholder="Harga akan terisi otomatis" />
                        </div>
                    </div>
                </div>

                <!-- Product Section -->
                <div class="form-section">
                    <h3 class="section-title">Produk</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="produk" class="block text-sm font-medium text-gray-300 mb-2">
                                Pilih Produk
                            </label>
                            <select id="produk" name="produk" onchange="updateProductPrice()"
                                class="custom-input custom-select">
                                <option value="" disabled selected>Pilih produk</option>
                                <?php foreach ($products as $product): ?>
                                <option value="<?php echo htmlspecialchars($product['product_name']); ?>"
                                    data-price="<?php echo htmlspecialchars($product['price']); ?>">
                                    <?php echo htmlspecialchars($product['product_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="product-price" class="block text-sm font-medium text-gray-300 mb-2">
                                Harga Produk
                            </label>
                            <input id="product-price" name="product-price" type="number" readonly class="custom-input"
                                placeholder="Harga akan terisi otomatis" />
                        </div>

                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-300 mb-2">
                                Jumlah
                            </label>
                            <input id="quantity" name="quantity" type="number" min="1" value="1" required
                                class="custom-input" onchange="updateTotalPrice()" onkeyup="updateTotalPrice()" />
                        </div>

                        <div>
                            <label for="total-price" class="block text-sm font-medium text-gray-300 mb-2">
                                Total Harga Produk
                            </label>
                            <input id="total-price" name="total-price" type="number" readonly class="custom-input"
                                placeholder="Total akan terisi otomatis" />
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="submit-button w-full rounded-lg px-6 py-4 text-white font-semibold text-lg">
                    Save Transaction
                </button>

                <!-- Ganti Password and Logout Buttons -->
                <div class="flex justify-between gap-4 w-full">
                    <button type="button" onclick="showPasswordModal()"
                        class="flex-1 px-4 py-2 rounded-lg text-white text-sm font-medium"
                        style="background: linear-gradient(135deg, #00a6ff 0%, #0072ff 100%);">
                        Ganti Password
                    </button>
                    <!-- Logout Button -->
                    <a href="../index.php"
                        class="flex-1 logout-button px-4 py-2 rounded-lg text-white text-sm font-medium text-center">
                        Logout
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Add this section before the closing form tag in barber.php -->
    <div id="passwordModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="glass-card p-8 w-96">
            <div class="flex justify-between items-center mb-6">
                <h3 class="section-title">Ganti Password</h3>
                <button onclick="hidePasswordModal()" class="text-gray-300 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Password Saat Ini
                    </label>
                    <input type="password" id="current-password" class="custom-input" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Password Baru
                    </label>
                    <input type="password" id="new-password" class="custom-input" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Konfirmasi Password
                    </label>
                    <input type="password" id="confirm-password" class="custom-input" required />
                </div>
                <button onclick="changePassword()"
                    class="submit-button w-full rounded-lg px-6 py-4 text-white font-semibold text-lg">
                    Update Password
                </button>
            </div>
        </div>
    </div>

    <script>
    // Your existing JavaScript functions remain the same
    function updatePrice() {
        const treatmentSelect = document.getElementById('jenis-treatment');
        const hargaInput = document.getElementById('harga');
        const selectedOption = treatmentSelect.options[treatmentSelect.selectedIndex];
        const price = selectedOption.getAttribute('data-price');

        if (price) {
            hargaInput.value = price;
        }
    }

    function updateProductPrice() {
        const productSelect = document.getElementById('produk');
        const priceInput = document.getElementById('product-price');
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const price = selectedOption.getAttribute('data-price');

        if (price) {
            priceInput.value = price;
            updateTotalPrice();
        }
    }

    function updateTotalPrice() {
        const price = parseFloat(document.getElementById('product-price').value) || 0;
        const quantity = parseInt(document.getElementById('quantity').value) || 1;
        const totalPrice = price * quantity;
        document.getElementById('total-price').value = totalPrice;
    }

    function showPasswordModal() {
        document.getElementById('passwordModal').classList.remove('hidden');
        document.getElementById('passwordModal').classList.add('flex');
    }

    function hidePasswordModal() {
        document.getElementById('passwordModal').classList.add('hidden');
        document.getElementById('passwordModal').classList.remove('flex');
    }

    function submitAttendance() {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementById('latitude').value = position.coords.latitude;
                document.getElementById('longitude').value = position.coords.longitude;
                document.getElementById('attendanceForm').submit();
            }, function(error) {
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        alert("Mohon izinkan akses lokasi untuk melakukan absensi");
                        break;
                    case error.POSITION_UNAVAILABLE:
                        alert("Informasi lokasi tidak tersedia");
                        break;
                    case error.TIMEOUT:
                        alert("Waktu permintaan lokasi habis");
                        break;
                    default:
                        alert("Terjadi kesalahan saat mendapatkan lokasi");
                        break;
                }
            });
        } else {
            alert("Browser Anda tidak mendukung geolokasi");
        }
    }

    async function changePassword() {
        const currentPassword = document.getElementById('current-password').value;
        const newPassword = document.getElementById('new-password').value;
        const confirmPassword = document.getElementById('confirm-password').value;

        if (!currentPassword || !newPassword || !confirmPassword) {
            alert('Semua field harus diisi!');
            return;
        }

        if (newPassword !== confirmPassword) {
            alert('Password baru dan konfirmasi tidak cocok!');
            return;
        }

        try {
            const response = await fetch('change_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    currentPassword,
                    newPassword
                })
            });

            const data = await response.json();
            alert(data.message);

            if (data.success) {
                hidePasswordModal();
                document.getElementById('current-password').value = '';
                document.getElementById('new-password').value = '';
                document.getElementById('confirm-password').value = '';
            }
        } catch (error) {
            alert('Terjadi kesalahan saat mengganti password.');
        }
    }
    </script>
</body>

</html>
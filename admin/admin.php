<?php
session_start();

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'kaula_barbershop';

// Initialize database connection
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in and has admin role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
$username = $_SESSION['nama_kapster'];

// Get search and pagination parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$rows_per_page = isset($_GET['rows']) ? (int)$_GET['rows'] : 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $rows_per_page;
$selected_date = isset($_GET['date']) ? $_GET['date'] : '';

// Calculate total rows and pages
$sql = "SELECT COUNT(*) AS total FROM karyawan";
if (!empty($search)) {
    $sql .= " WHERE DATE(created_at) LIKE '%$search%' OR nama_kapster LIKE '%$search%'";
}
$total_rows = $conn->query($sql)->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $rows_per_page);

// Get distinct dates
$sql_dates = "SELECT DISTINCT DATE(created_at) AS date FROM karyawan ORDER BY date DESC";
$result_dates = $conn->query($sql_dates);
$dates = [];
while ($row_date = $result_dates->fetch_assoc()) {
    $dates[] = $row_date['date'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Table</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    <script src="https://unpkg.com/babel-standalone@6/babel.min.js"></script>
    <style>
    .pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1.5rem;
        background-color: #f7fafc;
        padding: 0.75rem 1rem;
        border-radius: 0.375rem;
    }

    .pagination-button {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        padding: 0.5rem 1rem;
        margin: 0 0.125rem;
        background-color: #4dd2ff;
        color: #fff;
        border-radius: 0.375rem;
        transition: background-color 0.3s;
        cursor: pointer;
        border: none;
    }

    .pagination-button:hover {
        background-color: #3cb9e6;
    }

    .pagination-button.active {
        background-color: #0073e6;
    }

    .pagination-button.disabled {
        background-color: #e2e8f0;
        color: #a0aec0;
        cursor: not-allowed;
    }

    .pagination-info {
        color: #4a5568;
        font-size: 0.875rem;
    }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Selamat Datang, <?php echo $username; ?>!</h1>
        <div class="container mx-auto px-4 py-8">
            <!-- Search and Export Section -->
            <div class="flex justify-between items-center mb-6">
                <form id="searchForm" class="w-1/3 relative">
                    <input type="text" name="search" placeholder="Search by date or kapster name"
                        value="<?php echo htmlspecialchars($search); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit"
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 px-4 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                        Search
                    </button>
                </form>
                <div class="flex space-x-2">
                    <a href="../exel/export_excel.php" target="_blank"
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Excel
                    </a>
                    <a href="../pdf/export_pdf.php" target="_blank"
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        PDF
                    </a>
                    <button onclick="showPasswordModal()"
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Ganti Password
                    </button>
                    <button onclick="showRegisterModal()"
                        class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                        Tambah User
                    </button>
                    <a href="../index.php"
                        class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Logout
                    </a>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="flex items-center mb-4">
                <div class="mr-4">
                    <label for="rows" class="mr-2">Rows per Page:</label>
                    <select id="rows" name="rows"
                        class="px-4 py-2 bg-blue-100 border border-blue-300 text-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300"
                        onchange="changeRowsPerPage(this.value)">
                        <?php foreach ([10, 25, 50, 100] as $value): ?>
                        <option value="<?php echo $value; ?>" <?php echo $rows_per_page == $value ? 'selected' : ''; ?>>
                            <?php echo $value; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="date" class="mr-2">Select Date:</label>
                    <select id="date" name="date"
                        class="px-4 py-2 bg-blue-100 border border-blue-300 text-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300"
                        onchange="selectDate(this.value)">
                        <option value="">All Dates</option>
                        <?php foreach ($dates as $date): ?>
                        <option value="<?php echo $date; ?>" <?php echo $selected_date == $date ? 'selected' : ''; ?>>
                            <?php echo date('d-m-Y', strtotime($date)); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Staff Monthly Recap -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
                <h2 class="px-6 py-4 bg-gray-200 text-gray-700 text-lg font-semibold">Rekap Bulanan Per Kapster</h2>
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="px-6 py-3 text-center">Bulan</th>
                            <th class="px-6 py-3 text-center">Nama Kapster</th>
                            <th class="px-6 py-3 text-center">Kehadiran</th>
                            <th class="px-6 py-3 text-center">Total Harga Treatment</th>
                            <th class="px-6 py-3 text-center">Total Harga Produk</th>
                            <th class="px-6 py-3 text-center">Total Keseluruhan</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm">
                        <?php
            $staff_monthly_sql = "SELECT 
            DATE_FORMAT(IFNULL(k.created_at, CURRENT_DATE), '%Y-%m') as month,
            u.nama_kapster,
            COUNT(DISTINCT a.date) as attendance_count,
            IFNULL(SUM(k.harga), 0) as total_treatment,
            IFNULL(SUM(k.total_price), 0) as total_product,
            IFNULL(SUM(k.harga + k.total_price), 0) as total_all
        FROM users u
        LEFT JOIN karyawan k ON u.nama_kapster = k.nama_kapster 
            AND DATE_FORMAT(k.created_at, '%Y-%m') = DATE_FORMAT(CURRENT_DATE, '%Y-%m')
        LEFT JOIN attendance a ON u.nama_kapster = a.nama_kapster 
            AND DATE_FORMAT(k.created_at, '%Y-%m') = DATE_FORMAT(a.date, '%Y-%m')
        WHERE u.role = 'kapster'
        GROUP BY DATE_FORMAT(IFNULL(k.created_at, CURRENT_DATE), '%Y-%m'), u.nama_kapster
        ORDER BY month DESC, u.nama_kapster ASC";
            
            $staff_monthly_result = $conn->query($staff_monthly_sql);

            if ($staff_monthly_result->num_rows > 0) {
                while ($row = $staff_monthly_result->fetch_assoc()) {
                    ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <?php echo date('F Y', strtotime($row['month'] . '-01')); ?>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap font-medium">
                                <?php echo htmlspecialchars($row['nama_kapster']); ?>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <?php echo $row['attendance_count']; ?> hari
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                Rp <?php echo number_format($row['total_treatment'], 0, ',', '.'); ?>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                Rp <?php echo number_format($row['total_product'], 0, ',', '.'); ?>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                Rp <?php echo number_format($row['total_all'], 0, ',', '.'); ?>
                            </td>
                        </tr>
                        <?php
                }
            } else {
                echo '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada data rekap bulanan per kapster.</td></tr>';
            }
            ?>
                    </tbody>
                </table>
            </div>

            <!-- Attendance Table -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
                <h2 class="px-6 py-4 bg-gray-200 text-gray-700 text-lg font-semibold">Rekap Absensi Hari Ini</h2>
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="px-6 py-3 text-center">Nama Kapster</th>
                            <th class="px-6 py-3 text-center">Tanggal</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="px-6 py-3 text-center">Waktu Absen</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm">
                        <?php
            // Query attendance records for today only
            $attendance_sql = "SELECT nama_kapster, DATE(date) as tanggal, status, TIME(created_at) as waktu 
                             FROM attendance 
                             WHERE DATE(date) = CURDATE()
                             ORDER BY created_at DESC";
            $attendance_result = $conn->query($attendance_sql);

            if ($attendance_result->num_rows > 0) {
                while ($row = $attendance_result->fetch_assoc()) {
                    ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-6 py-4 text-center whitespace-nowrap font-medium">
                                <?php echo htmlspecialchars($row['nama_kapster']); ?>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <?php echo date('d-m-Y', strtotime($row['tanggal'])); ?>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span
                                    class="px-3 py-1 rounded-full text-sm font-semibold 
                                <?php echo $row['status'] === 'hadir' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap font-medium">
                                <?php echo $row['waktu']; ?>
                            </td>
                        </tr>
                        <?php
                }
            } else {
                echo '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500 font-medium">Belum ada data absensi hari ini.</td></tr>';
            }
            ?>
                    </tbody>
                </table>
            </div>

            <!-- User Management Section -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
                <h2 class="px-6 py-4 bg-gray-200 text-gray-700 text-lg font-semibold">Manajemen User</h2>

                <?php
            // Di sinilah letak kode SQL yang diubah
            $users_sql = "SELECT user_id, username, nama_kapster, role, is_active FROM users WHERE username != ? AND role = 'kapster'";
            $stmt = $conn->prepare($users_sql);
            $stmt->bind_param("s", $_SESSION['username']);
            $stmt->execute();
            $users_result = $stmt->get_result();
            ?>

                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="px-6 py-3 text-center">Username</th>
                            <th class="px-6 py-3 text-center">Nama Kapster</th>
                            <th class="px-6 py-3 text-center">Role</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="px-6 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm">
                        <?php while ($user = $users_result->fetch_assoc()): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <?php echo htmlspecialchars($user['username']); ?>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <?php echo htmlspecialchars($user['nama_kapster']); ?>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full text-sm font-semibold 
                        <?php echo $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <form action="toggle_user_status.php" method="POST" class="inline">
                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                    <input type="hidden" name="current_status"
                                        value="<?php echo $user['is_active']; ?>">
                                    <button type="submit"
                                        class="px-4 py-2 rounded-md text-white 
                            <?php echo $user['is_active'] ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600'; ?>"
                                        <?php echo $user['role'] === 'admin' ? 'disabled' : ''; ?>>
                                        <?php echo $user['is_active'] ? 'Nonaktifkan' : 'Aktifkan'; ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Data Table -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-200 text-gray-700">
                            <th class="px-6 py-3 text-left">Tanggal</th>
                            <th class="px-6 py-3 text-left">Nama Kapster</th>
                            <th class="px-6 py-3 text-left">Jenis Treatment</th>
                            <th class="px-6 py-3 text-left">Produk Terjual</th>
                            <th class="px-6 py-3 text-left">Jumlah Keseluruhan</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php
            $sql = "SELECT 
                DATE(created_at) AS date,
                nama_kapster,
                jenis_treatment,
                CONCAT(produk, ' (', quantity, ')') AS produk_terjual,
                (harga + total_price) AS jumlah_keseluruhan
            FROM karyawan";
            
            if (!empty($selected_date)) {
                $sql .= " WHERE DATE(created_at) = '$selected_date'";
            }
            if (!empty($search)) {
                $sql .= (strpos($sql, 'WHERE') !== false ? " AND" : " WHERE");
                $sql .= " (DATE(created_at) LIKE '%$search%' OR nama_kapster LIKE '%$search%')";
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT $offset, $rows_per_page";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    ?>
                        <tr class="border-b border-gray-200">
                            <td class="px-6 py-4 text-left"><?php echo date('d-m-Y', strtotime($row['date'])); ?>
                            </td>
                            <td class="px-6 py-4 text-left"><?php echo htmlspecialchars($row['nama_kapster']); ?>
                            </td>
                            <td class="px-6 py-4 text-left"><?php echo htmlspecialchars($row['jenis_treatment']); ?>
                            </td>
                            <td class="px-6 py-4 text-left"><?php echo htmlspecialchars($row['produk_terjual']); ?>
                            </td>
                            <td class="px-6 py-4 text-left">Rp
                                <?php echo number_format($row['jumlah_keseluruhan'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php
                }
            } else {
                echo '<tr><td colspan="5" class="px-6 py-4 text-center">Tidak ada data yang sesuai dengan pencarian.</td></tr>';
            }
            ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Section -->
            <div class="pagination">
                <div class="pagination-info">
                    Page <?php echo $current_page; ?> of <?php echo $total_pages; ?>
                </div>
                <div>
                    <button class="pagination-button <?php echo $current_page == 1 ? 'disabled' : ''; ?>"
                        <?php echo $current_page == 1 ? 'disabled' : ''; ?>
                        onclick="changePage(<?php echo $current_page - 1; ?>)">&laquo;</button>

                    <?php 
                $start_page = max(1, $current_page - 2);
                $end_page = min($start_page + 4, $total_pages);
                $start_page = max(1, $end_page - 4);
                
                for ($i = $start_page; $i <= $end_page; $i++): 
                ?>
                    <button class="pagination-button <?php echo $i == $current_page ? 'active' : ''; ?>"
                        onclick="changePage(<?php echo $i; ?>"><?php echo $i; ?></button>
                    <?php endfor; ?>

                    <button class="pagination-button <?php echo $current_page == $total_pages ? 'disabled' : ''; ?>"
                        <?php echo $current_page == $total_pages ? 'disabled' : ''; ?>
                        onclick="changePage(<?php echo $current_page + 1; ?>)">&raquo;</button>
                </div>
            </div>
        </div>

        <!-- Add before the closing body tag in admin.php -->
        <div id="passwordModal"
            class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-sm hidden items-center justify-center z-50">
            <div class="bg-white p-8 rounded-2xl shadow-xl w-96 transform transition-all">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Ganti Password</h2>
                    <button onclick="hidePasswordModal()" class="text-gray-500 hover:text-gray-700 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="passwordForm" class="space-y-5">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Password Saat Ini</label>
                        <div class="relative">
                            <input type="password" id="currentPassword"
                                class="w-full px-4 py-3 bg-white text-gray-800 rounded-lg border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-400 focus:ring-opacity-20 transition-all"
                                placeholder="Masukkan password saat ini">
                            <span class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Password Baru</label>
                        <div class="relative">
                            <input type="password" id="newPassword"
                                class="w-full px-4 py-3 bg-white text-gray-800 rounded-lg border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-400 focus:ring-opacity-20 transition-all"
                                placeholder="Masukkan password baru">
                            <span class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                        <div class="relative">
                            <input type="password" id="confirmPassword"
                                class="w-full px-4 py-3 bg-white text-gray-800 rounded-lg border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-400 focus:ring-opacity-20 transition-all"
                                placeholder="Konfirmasi password baru">
                            <span class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-8">
                        <button type="button" onclick="hidePasswordModal()"
                            class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-200">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-6 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transform hover:scale-105 transition-all duration-200">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Register Modal -->
        <div id="registerModal"
            class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-sm hidden items-center justify-center z-50">
            <div class="bg-white p-8 rounded-2xl shadow-xl w-96 transform transition-all">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Register New User</h2>
                    <button onclick="hideRegisterModal()" class="text-gray-500 hover:text-gray-700 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="registerForm" class="space-y-5">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" id="regUsername"
                            class="w-full px-4 py-3 bg-white text-gray-800 rounded-lg border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-400 focus:ring-opacity-20 transition-all"
                            required>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Nama Kapster</label>
                        <input type="text" id="regNamaKapster"
                            class="w-full px-4 py-3 bg-white text-gray-800 rounded-lg border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-400 focus:ring-opacity-20 transition-all"
                            required>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" id="regPassword"
                            class="w-full px-4 py-3 bg-white text-gray-800 rounded-lg border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-400 focus:ring-opacity-20 transition-all"
                            required>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Role</label>
                        <select id="regRole"
                            class="w-full px-4 py-3 bg-white text-gray-800 rounded-lg border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-400 focus:ring-opacity-20 transition-all"
                            required>
                            <option value="">Select a role</option>
                            <option value="kapster">Kapster</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-3 mt-8">
                        <button type="button" onclick="hideRegisterModal()"
                            class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-200">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-6 py-2.5 bg-green-500 text-white rounded-lg hover:bg-green-600 transform hover:scale-105 transition-all duration-200">
                            Register
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        function changeRowsPerPage(value) {
            window.location.href = `?page=1&rows=${value}&date=<?php echo $selected_date; ?>`;
        }

        function changePage(page) {
            window.location.href =
                `?page=${page}&rows=<?php echo $rows_per_page; ?>&date=<?php echo $selected_date; ?>`;
        }

        function selectDate(date) {
            window.location.href = `?date=${date}`;
        }

        function showPasswordModal() {
            const modal = document.getElementById('passwordModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => modal.querySelector('input').focus(), 100);
        }

        function hidePasswordModal() {
            const modal = document.getElementById('passwordModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.getElementById('passwordForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (!currentPassword || !newPassword || !confirmPassword) {
                alert('Semua field harus diisi!');
                return;
            }

            if (newPassword !== confirmPassword) {
                alert('Password baru dan konfirmasi tidak cocok!');
                return;
            }

            try {
                const response = await fetch('change_password_admin.php', {
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

                if (data.success) {
                    alert('✅ ' + data.message);
                    hidePasswordModal();
                    document.getElementById('passwordForm').reset();
                } else {
                    alert('❌ ' + data.message);
                }
            } catch (error) {
                alert('❌ Terjadi kesalahan saat mengganti password.');
            }
        });

        // Close modal when clicking outside
        document.getElementById('passwordModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hidePasswordModal();
            }
        });

        // Prevent modal close when clicking modal content
        document.querySelector('#passwordModal > div').addEventListener('click', function(e) {
            e.stopPropagation();
        });

        function showRegisterModal() {
            const modal = document.getElementById('registerModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function hideRegisterModal() {
            const modal = document.getElementById('registerModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Handle register form submission
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const username = document.getElementById('regUsername').value;
            const nama_kapster = document.getElementById('regNamaKapster').value;
            const password = document.getElementById('regPassword').value;
            const role = document.getElementById('regRole').value;

            try {
                const response = await fetch('register_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        username,
                        nama_kapster,
                        password,
                        role
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert('✅ ' + data.message);
                    hideRegisterModal();
                    document.getElementById('registerForm').reset();
                    // Refresh halaman untuk memperbarui tabel user
                    location.reload();
                } else {
                    alert('❌ ' + data.message);
                }
            } catch (error) {
                alert('❌ Terjadi kesalahan saat registrasi user.');
            }
        });

        // Close register modal when clicking outside
        document.getElementById('registerModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideRegisterModal();
            }
        });
        </script>
</body>

</html>
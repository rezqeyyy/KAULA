<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../vendor/autoload.php';

use Mpdf\Mpdf;

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'kaula_barbershop';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get data
$sql = "SELECT *, (harga + total_price) AS jumlah_keseluruhan, 
        CONCAT(produk, ' (', quantity, ')') AS produk_terjual 
        FROM karyawan 
        ORDER BY created_at DESC";
$result = $conn->query($sql);

// Create PDF content with styling
$html = '
<style>
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }
    th {
        background-color: #4B9CD3;
        color: white;
        font-weight: bold;
        padding: 10px;
        text-align: center;
        font-size: 12px;
    }
    td {
        padding: 8px;
        border: 1px solid #ddd;
        font-size: 11px;
    }
    tr:nth-child(even) {
        background-color: #f2f2f2;
    }
    .center {
        text-align: center;
    }
    .right {
        text-align: right;
    }
</style>
<h2 style="text-align: center;">Data Karyawan Kaula Barbershop</h2>
<table border="1">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Nama Kapster</th>
            <th>Jenis Treatment</th>
            <th>Produk Terjual</th>
            <th>Jumlah Keseluruhan</th>
        </tr>
    </thead>
    <tbody>';

while ($data = $result->fetch_assoc()) {
    $html .= '<tr>
        <td class="center">'.date('Y-m-d', strtotime($data['created_at'])).'</td>
        <td>'.$data['nama_kapster'].'</td>
        <td>'.$data['jenis_treatment'].'</td>
        <td>'.$data['produk_terjual'].'</td>
        <td class="right">Rp '.number_format($data['jumlah_keseluruhan'], 0, ',', '.').'</td>
    </tr>';
}

$html .= '</tbody></table>';

// Create new PDF instance with custom settings
$mpdf = new Mpdf([
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 15,
    'margin_bottom' => 15,
    'margin_header' => 10,
    'margin_footer' => 10
]);

// Add page numbers
$mpdf->SetFooter('Page {PAGENO}');

// Write content to PDF
$mpdf->WriteHTML($html);

// Output PDF for download
$mpdf->Output('Data_Karyawan_'.date('Y-m-d').'.pdf', 'D');
exit;
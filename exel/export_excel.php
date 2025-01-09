<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Style the header
$headerStyle = [
    'font' => [
        'bold' => true,
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => '4B9CD3',
        ],
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
    ],
];

// Set header
$sheet->setCellValue('A1', 'Tanggal');
$sheet->setCellValue('B1', 'Nama Kapster');
$sheet->setCellValue('C1', 'Jenis Treatment');
$sheet->setCellValue('D1', 'Produk Terjual');
$sheet->setCellValue('E1', 'Jumlah Keseluruhan');

// Apply header style
$sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

// Add data
$row = 2;
while ($data = $result->fetch_assoc()) {
    $sheet->setCellValue('A'.$row, date('Y-m-d', strtotime($data['created_at'])));
    $sheet->setCellValue('B'.$row, $data['nama_kapster']);
    $sheet->setCellValue('C'.$row, $data['jenis_treatment']);
    $sheet->setCellValue('D'.$row, $data['produk_terjual']);
    $sheet->setCellValue('E'.$row, 'Rp ' . number_format($data['jumlah_keseluruhan'], 0, ',', '.'));
    $row++;
}

// Auto size columns
foreach(range('A','E') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Set the header for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Data_Karyawan_'.date('Y-m-d').'.xlsx"');
header('Cache-Control: max-age=0');

// Save file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
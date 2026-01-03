<?php
session_start();
require_once 'connect.php';
require_once '../vendor/autoload.php'; // Assuming Composer autoload

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Check Admin Access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit('Unauthorized');
}

try {
    // Fetch Data
    $sql = "
        SELECT 
            a.idAppointment,
            a.datetime,
            at.name as type_name,
            at.price,
            ast.status_name,
            u_pat.name as pat_name,
            u_pat.last_name as pat_lastname
        FROM Appointment a
        JOIN Appointment_Type at ON a.Appointment_Type_idAppointment_Type = at.idAppointment_Type
        JOIN Appointment_Status ast ON a.Appointment_Status_idAppointment_Status = ast.idAppointment_Status
        LEFT JOIN Appointment_User au_pat ON a.idAppointment = au_pat.Appointment_idAppointment 
            AND au_pat.User_idUser IN (SELECT idUser FROM User WHERE Role_idRole = 3)
        LEFT JOIN User u_pat ON au_pat.User_idUser = u_pat.idUser
        ORDER BY a.datetime DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($appointments)) {
        echo "<script>
            if (window.opener && typeof window.opener.showNotification === 'function') {
                window.opener.showNotification('Nema podataka za izvoz.', 'error');
            } else {
                alert('Nema podataka za izvoz.');
            }
            window.close();
        </script>";
        exit;
    }

    // Create Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set Metadata
    $spreadsheet->getProperties()
        ->setCreator("OpusInTe")
        ->setLastModifiedBy("OpusInTe Admin")
        ->setTitle("Izvještaj o terminima")
        ->setSubject("Termini")
        ->setDescription("Lista svih termina iz baze podataka.");

    // Header Row
    $headers = ['ID', 'Pacijent', 'Usluga', 'Datum i Vrijeme', 'Cijena (KM)', 'Status'];
    $sheet->fromArray($headers, NULL, 'A1');

    // Style Header
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'C5A76A'], // OpusInTe Gold
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
            ],
        ],
    ];
    $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

    // Data Rows
    $row = 2;
    foreach ($appointments as $appt) {
        $patientName = ($appt['pat_name'] && $appt['pat_lastname']) 
            ? $appt['pat_name'] . ' ' . $appt['pat_lastname'] 
            : 'Nepoznato';
        
        // Format Date
        $date = new DateTime($appt['datetime']);
        $formattedDate = $date->format('d.m.Y H:i');

        $sheet->setCellValue('A' . $row, $appt['idAppointment']);
        $sheet->setCellValue('B' . $row, $patientName);
        $sheet->setCellValue('C' . $row, $appt['type_name']);
        $sheet->setCellValue('D' . $row, $formattedDate);
        $sheet->setCellValue('E' . $row, $appt['price']);
        $sheet->setCellValue('F' . $row, $appt['status_name']);
        $row++;
    }

    // Auto-size columns
    foreach (range('A', 'F') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Output
    $filename = 'termini_izvjestaj_' . date('Y-m-d_H-i') . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    // Log error and show user friendly message
    error_log($e->getMessage());
    die("Došlo je do greške prilikom generisanja Excel fajla.");
}

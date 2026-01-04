<?php
session_start();
require_once 'connect.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

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

    // Logo Path
    $logoPath = __DIR__ . '/../img/logo/logo_header.png';
    $logoData = base64_encode(file_get_contents($logoPath));
    $logoSrc = 'data:image/png;base64,' . $logoData;

    // HTML Template
    $html = '
    <!DOCTYPE html>
    <html lang="bs">
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: DejaVu Sans, sans-serif; color: #333; }
            .header { text-align: center; margin-bottom: 30px; }
            .logo { height: 60px; margin-bottom: 10px; }
            h1 { color: #C5A76A; font-size: 24px; margin: 0; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #C5A76A; color: white; font-weight: bold; }
            tr:nth-child(even) { background-color: #f9f9f9; }
            .footer { position: fixed; bottom: 0; left: 0; right: 0; height: 30px; font-size: 10px; text-align: center; color: #777; border-top: 1px solid #ddd; padding-top: 10px; }
        </style>
    </head>
    <body>
        <div class="header">
            <img src="' . $logoSrc . '" alt="OpusInTe Logo" class="logo">
            <h1>Izvještaj o terminima</h1>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pacijent</th>
                    <th>Usluga</th>
                    <th>Datum i Vrijeme</th>
                    <th>Cijena</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>';

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
        foreach ($appointments as $appt) {
            $patientName = ($appt['pat_name'] && $appt['pat_lastname']) 
                ? htmlspecialchars($appt['pat_name'] . ' ' . $appt['pat_lastname']) 
                : 'Nepoznato';
            
            $date = new DateTime($appt['datetime']);
            $formattedDate = $date->format('d.m.Y H:i');

            $html .= '<tr>
                <td>' . $appt['idAppointment'] . '</td>
                <td>' . $patientName . '</td>
                <td>' . htmlspecialchars($appt['type_name']) . '</td>
                <td>' . $formattedDate . '</td>
                <td>' . $appt['price'] . ' KM</td>
                <td>' . htmlspecialchars($appt['status_name']) . '</td>
            </tr>';
        }

    $html .= '
            </tbody>
        </table>

        <div class="footer">
            OpusInTe - Poročilo o terminima | Generisano: ' . date('d.m.Y H:i') . '
        </div>
    </body>
    </html>';

    // Configure Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true); // For images if needed, though we used base64
    
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Output
    $dompdf->stream("termini_izvjestaj_" . date('Y-m-d') . ".pdf", ["Attachment" => true]);

} catch (Exception $e) {
    error_log($e->getMessage());
    die("Došlo je do greške prilikom generisanja PDF fajla.");
}

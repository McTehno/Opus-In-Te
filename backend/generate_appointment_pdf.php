<?php
require_once 'connect.php';
require_once 'app_config.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$id = $_GET['id'] ?? null;
$token = $_GET['token'] ?? null;

if (!$id || !$token) {
    die('Invalid request.');
}

// Validate Token
$expectedToken = hash_hmac('sha256', $id, APP_SECRET);
if (!hash_equals($expectedToken, $token)) {
    die('Invalid token.');
}

try {
    // Fetch Appointment Data
    $sql = "
        SELECT 
            a.idAppointment,
            a.datetime,
            at.name as type_name,
            at.price,
            at.duration,
            u_pat.name as pat_name,
            u_pat.last_name as pat_lastname,
            u_pat.email as pat_email,
            u_pat.phone as pat_phone,
            ad.street,
            c.name as city_name
        FROM Appointment a
        JOIN Appointment_Type at ON a.Appointment_Type_idAppointment_Type = at.idAppointment_Type
        LEFT JOIN Appointment_User au_pat ON a.idAppointment = au_pat.Appointment_idAppointment 
            AND au_pat.User_idUser IN (SELECT idUser FROM User WHERE Role_idRole = 3)
        LEFT JOIN User u_pat ON au_pat.User_idUser = u_pat.idUser
        LEFT JOIN Address ad ON a.Address_idAddress = ad.idAddress
        LEFT JOIN City c ON ad.City_idCity = c.idCity
        WHERE a.idAppointment = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        die('Appointment not found.');
    }

    // Logo Path
    $logoPath = __DIR__ . '/../img/logo/logo_header.png';
    $logoData = '';
    if (file_exists($logoPath)) {
        $logoData = base64_encode(file_get_contents($logoPath));
    }
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
            .logo { max-width: 150px; margin-bottom: 10px; }
            .title { font-size: 24px; color: #C5A76A; margin-bottom: 5px; }
            .subtitle { font-size: 14px; color: #777; }
            .details-box { border: 1px solid #ddd; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
            .row { margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
            .label { font-weight: bold; width: 150px; display: inline-block; }
            .footer { text-align: center; font-size: 12px; color: #999; margin-top: 50px; }
        </style>
    </head>
    <body>
        <div class="header">
            ' . ($logoData ? '<img src="' . $logoSrc . '" class="logo">' : '') . '
            <div class="title">Potvrda Rezervacije</div>
            <div class="subtitle">Opus in te - Centar za psihoterapiju</div>
        </div>

        <div class="details-box">
            <div class="row">
                <span class="label">Klijent:</span> ' . htmlspecialchars($appointment['pat_name'] . ' ' . $appointment['pat_lastname']) . '
            </div>
            <div class="row">
                <span class="label">Usluga:</span> ' . htmlspecialchars($appointment['type_name']) . '
            </div>
            <div class="row">
                <span class="label">Datum i Vrijeme:</span> ' . date('d.m.Y H:i', strtotime($appointment['datetime'])) . '
            </div>
            <div class="row">
                <span class="label">Trajanje:</span> ' . htmlspecialchars($appointment['duration']) . ' min
            </div>
            <div class="row">
                <span class="label">Cijena:</span> ' . number_format($appointment['price'], 2) . ' KM
            </div>
            <div class="row">
                <span class="label">Lokacija:</span> ' . htmlspecialchars($appointment['city_name'] ?? 'Online') . '
            </div>
        </div>

        <div class="footer">
            <p>Hvala Vam na povjerenju.</p>
            <p>Opus in te | www.opusinte.ba | info@opusinte.ba</p>
        </div>
    </body>
    </html>';

    // Configure Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Output PDF
    $dompdf->stream("Termin_" . $appointment['idAppointment'] . ".pdf", ["Attachment" => false]);

} catch (Exception $e) {
    die('Error generating PDF: ' . $e->getMessage());
}
?>

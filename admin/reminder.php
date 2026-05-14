<?php
include("../conn.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../src/PHPMailer.php';
require '../src/SMTP.php';
require '../src/Exception.php';

function sendEmail(string $to, string $subject, string $message): void
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'arjaybalmores1@gmail.com';
        $mail->Password   = 'guay gtpr vgck japb';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('arjaybalmores1@gmail.com', 'Clinic System');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
    } catch (Exception $e) {
        error_log("Email error: " . $e->getMessage());
    }
}

// Send reminders for approved appointments scheduled for tomorrow
$result = mysqli_query($conn, "
    SELECT a.*, p.email, p.name AS patient_name, d.name AS doctor_name
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    JOIN doctors d ON a.doctor_id = d.id
    WHERE a.appointment_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
    AND a.status = 'approved'
");

while ($row = mysqli_fetch_assoc($result)) {
    $time = date("h:i A", strtotime($row['appointment_time']));
    $date = date("F d, Y", strtotime($row['appointment_date']));

    $body = "
    <div style='font-family: ui-sans-serif, system-ui, sans-serif; max-width: 600px; margin: 40px auto; background: #fff; border-radius: 1rem; overflow: hidden; border: 1px solid #f3f4f6;'>
        <div style='background-color: #ec4899; padding: 32px; text-align: center;'>
            <h1 style='color: #fff; margin: 0; font-size: 24px; font-weight: 800;'>CAMS</h1>
            <p style='color: #fce7f3; margin-top: 4px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em;'>Clinic Appointment Management System</p>
        </div>
        <div style='padding: 32px;'>
            <h2 style='color: #111827; font-size: 18px; font-weight: 700; margin: 0 0 12px;'>Hello, {$row['patient_name']}!</h2>
            <p style='color: #4b5563; font-size: 15px; line-height: 1.6; margin: 0 0 24px;'>This is a reminder that you have an appointment <strong>tomorrow</strong>.</p>
            <div style='background: #fdf2f8; border-radius: 0.75rem; padding: 20px; border: 1px solid #fce7f3;'>
                <table style='width: 100%; border-collapse: collapse;'>
                    <tr>
                        <td style='padding: 8px 0; color: #6b7280; font-size: 14px;'>👨‍⚕️ Doctor</td>
                        <td style='padding: 8px 0; color: #111827; font-size: 14px; font-weight: 600; text-align: right;'>{$row['doctor_name']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; color: #6b7280; font-size: 14px; border-top: 1px solid #fce7f3;'>📅 Date</td>
                        <td style='padding: 8px 0; color: #111827; font-size: 14px; font-weight: 600; text-align: right; border-top: 1px solid #fce7f3;'>$date</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; color: #6b7280; font-size: 14px; border-top: 1px solid #fce7f3;'>🕐 Time</td>
                        <td style='padding: 8px 0; color: #111827; font-size: 14px; font-weight: 600; text-align: right; border-top: 1px solid #fce7f3;'>$time</td>
                    </tr>
                </table>
            </div>
            <p style='color: #9ca3af; font-size: 13px; text-align: center; margin-top: 24px;'>Please arrive at least 15 minutes before your scheduled time.</p>
        </div>
        <div style='background: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #f3f4f6;'>
            <p style='margin: 0; color: #9ca3af; font-size: 11px;'>This is an automated message. Please do not reply.</p>
        </div>
    </div>
    ";

    sendEmail($row['email'], "Appointment Reminder — CAMS", $body);
}

echo "Reminders sent.";
exit();

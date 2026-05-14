<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../src/PHPMailer.php';
require '../src/SMTP.php';
require '../src/Exception.php';

session_start();
include("../conn.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_username = $_SESSION['username'] ?? 'Admin';

// ─── Email Helper ────────────────────────────────────────────────────────────

function sendEmail(string $to, string $subject, string $body): void
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
        $mail->Body    = $body;

        $mail->send();
    } catch (Exception $e) {
        error_log("Email error: " . $e->getMessage());
    }
}

// ─── Email Templates ─────────────────────────────────────────────────────────

function emailTemplate(string $headerColor, string $headerSubColor, string $badgeBg, string $badgeBorder, string $badgeColor, string $badgeIcon, string $badgeLabel, string $cardBg, string $cardBorder, string $cardAccent, string $greeting, string $intro, string $doctor, string $date, string $time, string $footer): string
{
    return "
    <div style='font-family: ui-sans-serif, system-ui, sans-serif; max-width: 600px; margin: 40px auto; background: #fff; border-radius: 1rem; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,.1); border: 1px solid #f3f4f6;'>
        <div style='background-color: $headerColor; padding: 40px 32px; text-align: center;'>
            <h1 style='color: #fff; margin: 0; font-size: 28px; font-weight: 800; letter-spacing: -.025em;'>CAMS</h1>
            <p style='color: $headerSubColor; margin-top: 4px; font-size: 11px; letter-spacing: .1em; text-transform: uppercase;'>Clinic Appointment Management System</p>
        </div>
        <div style='padding: 40px 32px;'>
            <div style='display: inline-block; background-color: $badgeBg; border-left: 4px solid $badgeBorder; border-radius: .375rem; padding: 12px 16px; margin-bottom: 28px;'>
                <p style='margin: 0; color: $badgeColor; font-weight: 700; font-size: 13px; text-transform: uppercase;'>$badgeIcon $badgeLabel</p>
            </div>
            <h2 style='color: #111827; font-size: 18px; font-weight: 700; margin: 0 0 12px;'>$greeting</h2>
            <p style='color: #4b5563; font-size: 15px; line-height: 1.6; margin: 0 0 32px;'>$intro</p>
            <div style='background-color: $cardBg; border-radius: .75rem; padding: 24px; border: 1px solid $cardBorder; margin-bottom: 32px;'>
                <p style='margin: 0 0 16px; font-size: 12px; font-weight: 700; color: $cardAccent; text-transform: uppercase; letter-spacing: .05em;'>Appointment Details</p>
                <table style='width: 100%; border-collapse: collapse;'>
                    <tr><td style='padding: 8px 0; color: #6b7280; font-size: 14px;'>👨‍⚕️ Doctor</td><td style='padding: 8px 0; color: #111827; font-size: 14px; font-weight: 600; text-align: right;'>$doctor</td></tr>
                    <tr><td style='padding: 8px 0; color: #6b7280; font-size: 14px; border-top: 1px solid $cardBorder;'>📅 Date</td><td style='padding: 8px 0; color: #111827; font-size: 14px; font-weight: 600; text-align: right; border-top: 1px solid $cardBorder;'>$date</td></tr>
                    <tr><td style='padding: 8px 0; color: #6b7280; font-size: 14px; border-top: 1px solid $cardBorder;'>🕐 Time</td><td style='padding: 8px 0; color: #111827; font-size: 14px; font-weight: 600; text-align: right; border-top: 1px solid $cardBorder;'>$time</td></tr>
                </table>
            </div>
            <p style='color: #9ca3af; font-size: 13px; text-align: center; margin: 0;'>$footer</p>
        </div>
        <div style='background-color: #f9fafb; padding: 24px; text-align: center; border-top: 1px solid #f3f4f6;'>
            <p style='margin: 0; color: #9ca3af; font-size: 11px;'>This is an automated message from CAMS. Please do not reply.</p>
        </div>
    </div>";
}


// ─── Approve Appointment ─────────────────────────────────────────────────────

if (isset($_GET['approve'])) {
    $id = (int) $_GET['approve'];

    mysqli_query($conn, "UPDATE appointments SET status = 'approved' WHERE id = '$id'");

    $row = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT p.email, p.name AS patient_name, a.patient_id, a.appointment_date, a.appointment_time, d.name AS doctor_name
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        JOIN doctors d ON a.doctor_id = d.id
        WHERE a.id = '$id'
    "));

    $fmt_time = date("h:i A", strtotime($row['appointment_time']));
    $fmt_date = date("F d, Y", strtotime($row['appointment_date']));

    $body = emailTemplate(
        '#059669', '#d1fae5',
        '#f0fdf4', '#10b981', '#15803d', '✅', 'Appointment Approved',
        '#f0fdf4', '#dcfce7', '#059669',
        "Great news, {$row['patient_name']}!",
        "Your appointment request has been reviewed and is now <strong>confirmed</strong>. Please see the finalized details below.",
        $row['doctor_name'], $fmt_date, $fmt_time,
        "Please arrive at least 15 minutes before your scheduled time. See you there!"
    );

    sendEmail($row['email'], "Appointment Approved — CAMS", $body);

    mysqli_query($conn, "
        INSERT INTO notifications (patient_id, appointment_id, type, message, status)
        VALUES ('{$row['patient_id']}', '$id', 'email', 'Approved', 'pending')
    ");

    header("Location: appointments.php");
    exit();
}

// ─── Cancel Appointment ──────────────────────────────────────────────────────

if (isset($_GET['cancel'])) {
    $id = (int) $_GET['cancel'];

    mysqli_query($conn, "UPDATE appointments SET status = 'cancelled' WHERE id = '$id'");

    $row = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT p.email, p.name AS patient_name, a.patient_id, a.appointment_date, a.appointment_time, d.name AS doctor_name
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        JOIN doctors d ON a.doctor_id = d.id
        WHERE a.id = '$id'
    "));

    $fmt_time = date("h:i A", strtotime($row['appointment_time']));
    $fmt_date = date("F d, Y", strtotime($row['appointment_date']));

    $body = emailTemplate(
        '#e11d48', '#fff1f2',
        '#fff1f2', '#f43f5e', '#be123c', '🚫', 'Appointment Cancelled',
        '#f9fafb', '#f3f4f6', '#e11d48',
        "Hello, {$row['patient_name']}",
        "We are writing to inform you that your appointment has been <strong>cancelled</strong>. If you believe this is a mistake or would like to reschedule, please contact the clinic.",
        $row['doctor_name'], $fmt_date, $fmt_time,
        "You can book a new appointment anytime through our online portal."
    );

    sendEmail($row['email'], "Appointment Cancelled — CAMS", $body);

    mysqli_query($conn, "
        INSERT INTO notifications (patient_id, appointment_id, type, message, status)
        VALUES ('{$row['patient_id']}', '$id', 'email', 'Cancelled', 'pending')
    ");

    header("Location: appointments.php?status=cancelled");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAMS — Appointments</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #ec4899; border-radius: 10px; }
    </style>
</head>
<body class="bg-gray-50 overflow-hidden">

<!-- Alert Modal -->
<div id="alertModal" class="fixed inset-0 bg-black/50 z-[300] hidden items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-sm mx-4 text-center">
        <div id="alertIcon" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"></div>
        <h3 id="alertTitle" class="text-lg font-black text-gray-800 mb-2"></h3>
        <p id="alertMsg" class="text-sm text-gray-400 mb-6"></p>
        <button onclick="closeAlert()" class="w-full py-3 bg-pink-500 hover:bg-pink-600 text-white rounded-xl font-bold text-sm transition-all">OK</button>
    </div>
</div>

<div id="backdrop" onclick="closeSidebar()" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden"></div>

<div class="flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed md:static inset-y-0 left-0 z-50 w-64 bg-pink-500 text-white flex flex-col shadow-lg -translate-x-full md:translate-x-0 transition-transform duration-300">
        <div class="flex items-center p-4 gap-3">
            <img class="w-12 h-12 flex-shrink-0" src="../mainlogo.png" alt="CAMS Logo">
            <div>
                <h1 class="font-bold text-xl uppercase tracking-tighter leading-none">CAMS</h1>
                <p class="font-bold text-[8px] text-pink-100 uppercase tracking-wider opacity-90 leading-tight mt-0.5">Clinic Appointment <br> Management System</p>
            </div>
        </div>
        <hr class="border-pink-400 mx-4 opacity-50">
        <nav class="mt-4 flex-1 px-3">
            <ul class="space-y-1">
                <li><a href="dashboard.php" class="flex items-center hover:bg-pink-600 px-3 py-2.5 rounded-lg transition-all"><i data-lucide="layout-dashboard" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">Dashboard</span></a></li>
                <li><a href="patients.php" class="flex items-center hover:bg-pink-600 px-3 py-2.5 rounded-lg transition-all"><i data-lucide="users" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">Patients</span></a></li>
                <li><a href="doctors.php" class="flex items-center hover:bg-pink-600 px-3 py-2.5 rounded-lg transition-all"><i data-lucide="stethoscope" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">Doctors</span></a></li>
                <li><a href="appointments.php" class="flex items-center bg-pink-600 border-l-4 border-white px-3 py-2.5 rounded-lg transition-all"><i data-lucide="clipboard-check" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">Appointments</span></a></li>
                <li><a href="notifications.php" class="flex items-center hover:bg-pink-600 px-3 py-2.5 rounded-lg transition-all"><i data-lucide="bell" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">Notifications</span></a></li>
            </ul>
        </nav>
        <div class="px-3 pb-5 mt-auto">
            <hr class="border-pink-400 mb-3 opacity-50">
            <div class="bg-pink-600/40 rounded-2xl p-3">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 bg-pink-400/50 rounded-full flex items-center justify-center flex-shrink-0">
                        <i data-lucide="user-check" class="w-4 h-4 text-white"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-white text-sm truncate"><?= htmlspecialchars($admin_username); ?></p>
                        <p class="text-pink-200 text-xs">Administrator</p>
                    </div>
                </div>
                <button onclick="location.href='../logout.php'" class="w-full flex items-center justify-center gap-2 bg-pink-700/50 hover:bg-pink-700 px-3 py-2 rounded-xl transition-all text-pink-100 hover:text-white text-sm font-bold">
                    <i data-lucide="log-out" class="w-4 h-4"></i> Logout
                </button>
            </div>
        </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        <!-- Topbar -->
        <header class="bg-white border-b border-gray-100 shadow-sm px-6 py-4 flex items-center flex-shrink-0">
            <button onclick="openSidebar()" class="md:hidden text-gray-500 hover:text-pink-500 transition-colors mr-4">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
            <h2 class="text-xl font-bold text-gray-700">Appointments</h2>
        </header>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                <div class="px-6 py-5 border-b border-gray-100">
                    <h3 class="text-lg font-black text-gray-800">Booking Schedule</h3>
                    <p class="text-xs text-gray-400 font-medium mt-0.5">Manage and review all patient appointments.</p>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50/50 text-gray-400 uppercase text-[10px] font-black border-b">
                                <th class="px-8 py-5">Patient</th>
                                <th class="px-8 py-5">Doctor</th>
                                <th class="px-8 py-5">Schedule</th>
                                <th class="px-8 py-5">Status</th>
                                <th class="px-8 py-5 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php
                            $result = mysqli_query($conn, "
                                SELECT a.*, p.name AS patient, d.name AS doctor
                                FROM appointments a
                                JOIN patients p ON a.patient_id = p.id
                                JOIN doctors d ON a.doctor_id = d.id
                                ORDER BY a.id DESC
                            ");

                            while ($row = mysqli_fetch_assoc($result)):
                                $status      = $row['status'];
                                $patient     = htmlspecialchars($row['patient']);
                                $doctor      = htmlspecialchars($row['doctor']);
                                $appt_date   = htmlspecialchars($row['appointment_date']);
                                $appt_time   = htmlspecialchars($row['appointment_time']);
                                $status_class = match($status) {
                                    'approved'  => 'bg-green-100 text-green-600',
                                    'pending'   => 'bg-yellow-100 text-yellow-600',
                                    default     => 'bg-red-100 text-red-600',
                                };
                            ?>
                            <tr class="hover:bg-pink-50/30 transition-all">
                                <td class="px-8 py-5 font-bold text-gray-700"><?= $patient; ?></td>
                                <td class="px-8 py-5 text-gray-600"><?= $doctor; ?></td>
                                <td class="px-8 py-5 text-sm">
                                    <span class="font-medium text-gray-700"><?= $appt_date; ?></span><br>
                                    <span class="text-xs text-gray-400"><?= $appt_time; ?></span>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="<?= $status_class; ?> px-4 py-1.5 rounded-full text-[10px] font-black uppercase"><?= htmlspecialchars($status); ?></span>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex justify-center gap-2">
                                        <a href="?approve=<?= $row['id']; ?>" title="Approve" class="bg-green-50 text-green-600 p-2 rounded-lg hover:bg-green-500 hover:text-white transition-all">
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                        </a>
                                        <button onclick="confirmCancel(<?= $row['id']; ?>)" title="Cancel" class="bg-red-50 text-red-600 p-2 rounded-lg hover:bg-red-500 hover:text-white transition-all">
                                            <i data-lucide="x" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- (Book Appointment modal removed — booking is handled by the Patient Portal) -->

<script>
    lucide.createIcons();

    // Sidebar
    function openSidebar()  { document.getElementById('sidebar').classList.remove('-translate-x-full'); document.getElementById('backdrop').classList.remove('hidden'); }
    function closeSidebar() { document.getElementById('sidebar').classList.add('-translate-x-full');    document.getElementById('backdrop').classList.add('hidden'); }

    // Alert modal
    function closeAlert() {
        const modal = document.getElementById('alertModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Show alert based on URL status param
    window.addEventListener('DOMContentLoaded', () => {
        const params = new URLSearchParams(window.location.search);
        if (!params.has('status')) return;

        const status = params.get('status');
        const modal  = document.getElementById('alertModal');
        const icon   = document.getElementById('alertIcon');
        const title  = document.getElementById('alertTitle');
        const msg    = document.getElementById('alertMsg');

        const configs = {
            cancelled: {
                bg: 'bg-orange-100', iconColor: 'text-orange-500', icon: 'ban',
                title: 'Appointment Cancelled',
                msg: 'The appointment has been cancelled and the patient has been notified.'
            },
        };

        const cfg = configs[status];
        if (!cfg) return;

        icon.className = `w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 ${cfg.bg}`;
        icon.innerHTML = `<i data-lucide="${cfg.icon}" class="w-8 h-8 ${cfg.iconColor}"></i>`;
        title.textContent = cfg.title;
        msg.textContent   = cfg.msg;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        lucide.createIcons();

        window.history.replaceState({}, document.title, window.location.pathname);
    });

    // Cancel confirmation via SweetAlert2
    function confirmCancel(id) {
        Swal.fire({
            title: 'Cancel Appointment?',
            text: "A cancellation email will be sent to the patient.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DB2777',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Cancel It',
            cancelButtonText: 'Go Back'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "?cancel=" + id;
            }
        });
    }
</script>
</body>
</html>

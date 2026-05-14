<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../src/PHPMailer.php';
require '../src/SMTP.php';
require '../src/Exception.php';

session_start();
include("../conn.php");

// ─── Auth Guard ───────────────────────────────────────────────────────────────
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];

// Link logged-in user to their patient record via matching email
$u_res      = $conn->query("SELECT email FROM users WHERE username = '" . $conn->real_escape_string($username) . "'");
$u_data     = $u_res->fetch_assoc();
$user_email = $u_data['email'] ?? '';

$p_res   = $conn->query("SELECT * FROM patients WHERE email = '" . $conn->real_escape_string($user_email) . "' LIMIT 1");
$patient = $p_res->fetch_assoc();

$patient_id = $patient['id'] ?? null;

// ─── Email Helper ─────────────────────────────────────────────────────────────
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

// ─── Book Appointment ─────────────────────────────────────────────────────────
if (isset($_POST['book_appointment']) && $patient_id) {
    $doctor_id = (int) $_POST['doctor_id'];
    $date      = $conn->real_escape_string($_POST['date']);
    $time      = date("H:i:s", strtotime($_POST['time']));

    // Check for scheduling conflict
    $conflict = $conn->query("
        SELECT id FROM appointments
        WHERE doctor_id = '$doctor_id'
        AND appointment_date = '$date'
        AND appointment_time = '$time'
        AND status IN ('pending','approved')
        LIMIT 1
    ");

    if ($conflict->num_rows > 0) {
        header("Location: portal.php?status=conflict");
        exit();
    }

    $insert = $conn->query("
        INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status)
        VALUES ('$patient_id', '$doctor_id', '$date', '$time', 'pending')
    ");

    if ($insert) {
        $appt_id  = $conn->insert_id;
        $fmt_time = date("h:i A", strtotime($time));
        $fmt_date = date("F d, Y", strtotime($date));
        $doc_row  = $conn->query("SELECT name FROM doctors WHERE id = '$doctor_id'")->fetch_assoc();

        $email_body = "
        <div style='font-family:ui-sans-serif,system-ui,sans-serif;max-width:600px;margin:40px auto;background:#fff;border-radius:1rem;overflow:hidden;border:1px solid #f3f4f6;'>
            <div style='background:#ec4899;padding:40px 32px;text-align:center;'>
                <h1 style='color:#fff;margin:0;font-size:28px;font-weight:800;'>CAMS</h1>
                <p style='color:#fce7f3;margin-top:4px;font-size:11px;text-transform:uppercase;letter-spacing:.1em;'>Clinic Appointment Management System</p>
            </div>
            <div style='padding:40px 32px;'>
                <div style='display:inline-block;background:#fff7ed;border-left:4px solid #f97316;border-radius:.375rem;padding:12px 16px;margin-bottom:28px;'>
                    <p style='margin:0;color:#ea580c;font-weight:700;font-size:13px;text-transform:uppercase;'>⏳ Pending Approval</p>
                </div>
                <h2 style='color:#111827;font-size:18px;font-weight:700;margin:0 0 12px;'>Hello, {$patient['name']}!</h2>
                <p style='color:#4b5563;font-size:15px;line-height:1.6;margin:0 0 32px;'>Your appointment has been submitted and is awaiting approval from our clinic staff.</p>
                <div style='background:#fdf2f8;border-radius:.75rem;padding:24px;border:1px solid #fce7f3;margin-bottom:32px;'>
                    <p style='margin:0 0 16px;font-size:12px;font-weight:700;color:#db2777;text-transform:uppercase;'>Appointment Details</p>
                    <table style='width:100%;border-collapse:collapse;'>
                        <tr><td style='padding:8px 0;color:#6b7280;font-size:14px;'>👨‍⚕️ Doctor</td><td style='padding:8px 0;color:#111827;font-size:14px;font-weight:600;text-align:right;'>{$doc_row['name']}</td></tr>
                        <tr><td style='padding:8px 0;color:#6b7280;font-size:14px;border-top:1px solid #fce7f3;'>📅 Date</td><td style='padding:8px 0;color:#111827;font-size:14px;font-weight:600;text-align:right;border-top:1px solid #fce7f3;'>$fmt_date</td></tr>
                        <tr><td style='padding:8px 0;color:#6b7280;font-size:14px;border-top:1px solid #fce7f3;'>🕐 Time</td><td style='padding:8px 0;color:#111827;font-size:14px;font-weight:600;text-align:right;border-top:1px solid #fce7f3;'>$fmt_time</td></tr>
                    </table>
                </div>
                <p style='color:#9ca3af;font-size:13px;text-align:center;'>You will receive a confirmation email once your request is approved.</p>
            </div>
            <div style='background:#f9fafb;padding:24px;text-align:center;border-top:1px solid #f3f4f6;'>
                <p style='margin:0;color:#9ca3af;font-size:11px;'>This is an automated message. Please do not reply.</p>
            </div>
        </div>";

        sendEmail($user_email, "Appointment Pending — CAMS", $email_body);

        $conn->query("
            INSERT INTO notifications (patient_id, appointment_id, type, message, status)
            VALUES ('$patient_id', '$appt_id', 'email', 'Pending Approval', 'pending')
        ");

        header("Location: portal.php?status=success");
        exit();
    }

    header("Location: portal.php?status=error");
    exit();
}

// ─── Fetch patient's appointments ─────────────────────────────────────────────
$appointments = [];
if ($patient_id) {
    $appt_res = $conn->query("
        SELECT a.*, d.name AS doctor_name, d.specialization
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        WHERE a.patient_id = '$patient_id'
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    while ($row = $appt_res->fetch_assoc()) {
        $appointments[] = $row;
    }
}

// ─── Fetch available doctors ──────────────────────────────────────────────────
$doctors_res = $conn->query("SELECT id, name, specialization FROM doctors WHERE status = 'available' ORDER BY name");
$doctors = [];
while ($row = $doctors_res->fetch_assoc()) {
    $doctors[] = $row;
}

// Stats
$total_appts    = count($appointments);
$pending_count  = count(array_filter($appointments, fn($a) => $a['status'] === 'pending'));
$approved_count = count(array_filter($appointments, fn($a) => $a['status'] === 'approved'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAMS — Patient Portal</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #ec4899; border-radius: 10px; }
    </style>
</head>
<body class="bg-gray-50 overflow-hidden">

<!-- Status Alert Modal -->
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
                <li><a href="portal.php" class="flex items-center bg-pink-600 border-l-4 border-white px-3 py-2.5 rounded-lg transition-all"><i data-lucide="layout-dashboard" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">My Dashboard</span></a></li>
                <li><a href="notifications.php" class="flex items-center hover:bg-pink-600 px-3 py-2.5 rounded-lg transition-all"><i data-lucide="bell" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">Notifications</span></a></li>
            </ul>
        </nav>
        <div class="px-3 pb-5 mt-auto">
            <hr class="border-pink-400 mb-3 opacity-50">
            <?php if ($patient): ?>
            <div class="bg-pink-600/40 rounded-2xl p-3">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 bg-pink-400/50 rounded-full flex items-center justify-center flex-shrink-0">
                        <i data-lucide="user" class="w-4 h-4 text-white"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-white text-sm truncate"><?= htmlspecialchars($patient['name']); ?></p>
                        <p class="text-pink-200 text-xs truncate"><?= htmlspecialchars($user_email); ?></p>
                    </div>
                </div>
                <button onclick="location.href='../logout.php'" class="w-full flex items-center justify-center gap-2 bg-pink-700/50 hover:bg-pink-700 px-3 py-2 rounded-xl transition-all text-pink-100 hover:text-white text-sm font-bold">
                    <i data-lucide="log-out" class="w-4 h-4"></i> Logout
                </button>
            </div>
            <?php endif; ?>
        </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        <!-- Topbar -->
        <header class="bg-white border-b border-gray-100 shadow-sm px-6 py-4 flex items-center flex-shrink-0">
            <button onclick="openSidebar()" class="md:hidden text-gray-500 hover:text-pink-500 transition-colors mr-4">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
            <h2 class="text-xl font-bold text-gray-700">Patient Portal</h2>
        </header>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto custom-scrollbar p-6">

            <?php if (!$patient_id): ?>
            <!-- No patient record linked -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-6 flex items-start gap-4">
                <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center text-yellow-600 flex-shrink-0">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
                <div>
                    <p class="font-bold text-yellow-800">Profile Not Linked</p>
                    <p class="text-sm text-yellow-700 mt-1">Your account is not linked to a patient record. Make sure the email on your account (<strong><?= htmlspecialchars($user_email); ?></strong>) matches the one registered in the clinic system, or contact the admin.</p>
                </div>
            </div>

            <?php else: ?>

            <!-- Welcome Banner -->
            <div class="bg-gradient-to-r from-pink-500 to-pink-400 rounded-3xl p-8 mb-8 text-white relative overflow-hidden shadow-lg">
                <div class="absolute right-0 top-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/4 pointer-events-none"></div>
                <div class="absolute right-16 bottom-0 w-32 h-32 bg-white/10 rounded-full translate-y-1/2 pointer-events-none"></div>
                <div class="relative">
                    <p class="text-pink-100 text-sm font-semibold uppercase tracking-widest mb-1">Welcome back</p>
                    <h2 class="text-3xl font-black mb-1"><?= htmlspecialchars($patient['name']); ?></h2>
                    <p class="text-pink-100 text-sm"><?= htmlspecialchars($patient['gender']); ?> &middot; <?= (int)$patient['age']; ?> years old &middot; <?= htmlspecialchars($patient['contact']); ?></p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-3xl shadow-lg border border-gray-100 flex items-center gap-4 hover:scale-105 transition-transform">
                    <div class="w-12 h-12 bg-pink-50 rounded-2xl flex items-center justify-center text-pink-500">
                        <i data-lucide="calendar" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total</p>
                        <p class="text-2xl font-black text-gray-800"><?= $total_appts; ?></p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-lg border border-gray-100 flex items-center gap-4 hover:scale-105 transition-transform">
                    <div class="w-12 h-12 bg-yellow-50 rounded-2xl flex items-center justify-center text-yellow-500">
                        <i data-lucide="clock" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Pending</p>
                        <p class="text-2xl font-black text-gray-800"><?= $pending_count; ?></p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-lg border border-gray-100 flex items-center gap-4 hover:scale-105 transition-transform">
                    <div class="w-12 h-12 bg-green-50 rounded-2xl flex items-center justify-center text-green-500">
                        <i data-lucide="check-circle" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Approved</p>
                        <p class="text-2xl font-black text-gray-800"><?= $approved_count; ?></p>
                    </div>
                </div>
            </div>

            <!-- Appointments Table -->
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-100 flex justify-between items-center">
                    <div>
                        <h3 class="text-2xl font-black text-gray-800 tracking-tight">My Appointments</h3>
                        <p class="text-sm text-gray-400 font-medium">Your full appointment history</p>
                    </div>
                    <button onclick="openBookModal()" class="flex items-center gap-2 bg-pink-500 hover:bg-pink-600 text-white px-5 py-3 rounded-xl text-sm font-bold shadow-lg transition-all active:scale-95">
                        <i data-lucide="plus" class="w-4 h-4"></i> Book Appointment
                    </button>
                </div>

                <?php if (empty($appointments)): ?>
                <div class="py-16 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="calendar-x" class="w-8 h-8 text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 font-semibold">No appointments yet</p>
                    <p class="text-sm text-gray-400 mt-1">Book your first appointment to get started.</p>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 text-gray-400 uppercase text-[10px] font-black border-b border-gray-100">
                                <th class="px-8 py-5">Doctor</th>
                                <th class="px-8 py-5">Specialization</th>
                                <th class="px-8 py-5">Schedule</th>
                                <th class="px-8 py-5">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach ($appointments as $appt):
                                $status       = $appt['status'];
                                $status_class = match($status) {
                                    'approved' => 'bg-green-100 text-green-600',
                                    'pending'  => 'bg-yellow-100 text-yellow-600',
                                    default    => 'bg-red-100 text-red-600',
                                };
                            ?>
                            <tr class="hover:bg-pink-50/30 transition-all">
                                <td class="px-8 py-5 font-bold text-gray-700"><?= htmlspecialchars($appt['doctor_name']); ?></td>
                                <td class="px-8 py-5 text-gray-600"><?= htmlspecialchars($appt['specialization']); ?></td>
                                <td class="px-8 py-5 text-sm">
                                    <span class="font-medium text-gray-700"><?= date('M d, Y', strtotime($appt['appointment_date'])); ?></span><br>
                                    <span class="text-xs text-gray-400"><?= date('h:i A', strtotime($appt['appointment_time'])); ?></span>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="<?= $status_class; ?> px-4 py-1.5 rounded-full text-[10px] font-black uppercase"><?= htmlspecialchars($status); ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Book Appointment Modal -->
<div id="bookModal" class="fixed inset-0 bg-black/60 z-[100] hidden flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden">
        <div class="bg-pink-500 p-6 text-white flex justify-between items-center">
            <div class="flex items-center gap-3">
                <i data-lucide="calendar-plus" class="w-6 h-6"></i>
                <h3 class="text-xl font-bold">Book an Appointment</h3>
            </div>
            <button onclick="closeBookModal()" class="hover:bg-pink-600 p-1 rounded-full transition-colors">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <form method="POST" class="p-8 space-y-5">
            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest ml-1">Select Doctor</label>
                <select name="doctor_id" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none text-sm focus:ring-2 focus:ring-pink-300 transition-all mt-1">
                    <option value="" disabled selected>Choose a doctor...</option>
                    <?php foreach ($doctors as $doc): ?>
                    <option value="<?= $doc['id']; ?>"><?= htmlspecialchars($doc['name']); ?> — <?= htmlspecialchars($doc['specialization']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest ml-1">Date</label>
                    <input type="date" name="date" id="bookDate" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-pink-300 transition-all mt-1">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest ml-1">Time</label>
                    <input type="time" name="time" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-pink-300 transition-all mt-1">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeBookModal()" class="flex-1 py-3 border border-gray-200 text-gray-500 rounded-xl font-bold text-sm hover:bg-gray-50 transition-all">Cancel</button>
                <button type="submit" name="book_appointment" class="flex-1 py-3 bg-pink-500 text-white rounded-xl font-bold text-sm shadow-lg hover:bg-pink-600 active:scale-95 transition-all">Confirm Booking</button>
            </div>
        </form>
    </div>
</div>

<script>
    lucide.createIcons();

    // Sidebar
    function openSidebar()  { document.getElementById('sidebar').classList.remove('-translate-x-full'); document.getElementById('backdrop').classList.remove('hidden'); }
    function closeSidebar() { document.getElementById('sidebar').classList.add('-translate-x-full');    document.getElementById('backdrop').classList.add('hidden'); }

    // Book modal
    function openBookModal() {
        document.getElementById('bookModal').classList.remove('hidden');
        document.getElementById('bookDate').min = new Date().toISOString().split('T')[0];
    }
    function closeBookModal() {
        document.getElementById('bookModal').classList.add('hidden');
    }
    window.onclick = (e) => {
        const m = document.getElementById('bookModal');
        if (e.target === m) closeBookModal();
    };

    // Alert modal
    function closeAlert() {
        const m = document.getElementById('alertModal');
        m.classList.add('hidden');
        m.classList.remove('flex');
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    window.addEventListener('DOMContentLoaded', () => {
        const params = new URLSearchParams(window.location.search);
        if (!params.has('status')) return;

        const status = params.get('status');
        const icon   = document.getElementById('alertIcon');
        const title  = document.getElementById('alertTitle');
        const msg    = document.getElementById('alertMsg');
        const modal  = document.getElementById('alertModal');

        const configs = {
            success:  { bg: 'bg-green-100', ic: 'text-green-500', icon: 'check-circle', title: 'Appointment Booked!',    msg: 'Your appointment has been submitted. You will receive a confirmation email once approved.' },
            conflict: { bg: 'bg-red-100',   ic: 'text-red-500',   icon: 'x-circle',     title: 'Time Slot Unavailable', msg: 'That doctor already has an appointment at that time. Please choose a different slot.' },
            error:    { bg: 'bg-red-100',   ic: 'text-red-500',   icon: 'x-circle',     title: 'Something Went Wrong',  msg: 'Could not save your appointment. Please try again.' },
        };

        const cfg = configs[status];
        if (!cfg) return;

        icon.className = `w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 ${cfg.bg}`;
        icon.innerHTML = `<i data-lucide="${cfg.icon}" class="w-8 h-8 ${cfg.ic}"></i>`;
        title.textContent = cfg.title;
        msg.textContent   = cfg.msg;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        lucide.createIcons();
    });
</script>
</body>
</html>

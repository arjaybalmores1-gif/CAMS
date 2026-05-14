<?php
session_start();
include("../conn.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];

// Link user to patient record
$u_res      = $conn->query("SELECT email FROM users WHERE username = '" . $conn->real_escape_string($username) . "'");
$u_data     = $u_res->fetch_assoc();
$user_email = $u_data['email'] ?? '';

$p_res   = $conn->query("SELECT * FROM patients WHERE email = '" . $conn->real_escape_string($user_email) . "' LIMIT 1");
$patient = $p_res->fetch_assoc();
$patient_id = $patient['id'] ?? null;

// Fetch this patient's notifications joined with appointment details
$notifications = [];
if ($patient_id) {
    $res = $conn->query("
        SELECT n.*, a.appointment_date, a.appointment_time, a.status AS appt_status,
               d.name AS doctor_name, d.specialization
        FROM notifications n
        JOIN appointments a ON n.appointment_id = a.id
        JOIN doctors d ON a.doctor_id = d.id
        WHERE n.patient_id = '$patient_id'
        ORDER BY n.id DESC
    ");
    while ($row = $res->fetch_assoc()) {
        $notifications[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAMS — My Notifications</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #ec4899; border-radius: 10px; }
    </style>
</head>
<body class="bg-gray-50 overflow-hidden">

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
                <li><a href="portal.php" class="flex items-center hover:bg-pink-600 px-3 py-2.5 rounded-lg transition-all"><i data-lucide="layout-dashboard" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">My Dashboard</span></a></li>
                <li><a href="notifications.php" class="flex items-center bg-pink-600 border-l-4 border-white px-3 py-2.5 rounded-lg transition-all"><i data-lucide="bell" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">Notifications</span></a></li>
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
            <h2 class="text-xl font-bold text-gray-700">My Notifications</h2>
        </header>

        <!-- Body -->
        <div class="flex-1 overflow-hidden p-6 bg-gray-50/50">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 h-full flex flex-col overflow-hidden">

                <div class="px-6 py-5 border-b border-gray-100">
                    <h3 class="text-lg font-black text-gray-800">Appointment Updates</h3>
                    <p class="text-xs text-gray-400 font-medium mt-0.5">Status updates sent to you by the clinic.</p>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <?php if (empty($notifications)): ?>
                    <div class="flex flex-col items-center justify-center h-full py-20 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="bell-off" class="w-8 h-8 text-gray-400"></i>
                        </div>
                        <p class="text-gray-500 font-semibold">No notifications yet</p>
                        <p class="text-sm text-gray-400 mt-1">You'll see updates here when the clinic acts on your appointments.</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($notifications as $notif):
                        $msg     = $notif['message'];
                        $status  = $notif['appt_status'];
                        $fmt_date = date('M d, Y', strtotime($notif['appointment_date']));
                        $fmt_time = date('h:i A', strtotime($notif['appointment_time']));

                        // Icon + color based on the notification message (what was communicated at send time)
                        [$icon, $bg, $text, $border] = match(true) {
                            str_contains($msg, 'Approved')  => ['check-circle', 'bg-green-50',  'text-green-600',  'border-green-200'],
                            str_contains($msg, 'Cancelled') => ['x-circle',     'bg-red-50',    'text-red-600',    'border-red-200'],
                            default                         => ['clock',        'bg-yellow-50', 'text-yellow-600', 'border-yellow-200'],
                        };

                        // Badge reflects the notification message, not the current appointment status
                        $badge_label = match(true) {
                            str_contains($msg, 'Approved')  => 'Approved',
                            str_contains($msg, 'Cancelled') => 'Cancelled',
                            default                         => 'Pending',
                        };
                    ?>
                    <div class="px-6 py-5 border-b border-gray-50 hover:bg-gray-50/50 transition-all">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 <?= $bg; ?> rounded-full flex items-center justify-center <?= $text; ?> flex-shrink-0 mt-0.5 border <?= $border; ?>">
                                <i data-lucide="<?= $icon; ?>" class="w-5 h-5"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <p class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($msg); ?></p>
                                    <span class="<?= $bg; ?> <?= $text; ?> border <?= $border; ?> px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase flex-shrink-0">
                                        <?= $badge_label; ?>
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    <span class="font-medium"><?= htmlspecialchars($notif['doctor_name']); ?></span>
                                    <span class="text-gray-400"> — <?= htmlspecialchars($notif['specialization']); ?></span>
                                </p>
                                <div class="flex items-center gap-3 mt-1.5 flex-wrap">
                                    <span class="flex items-center gap-1 text-xs text-gray-400">
                                        <i data-lucide="calendar" class="w-3 h-3"></i>
                                        <?= $fmt_date; ?>
                                    </span>
                                    <span class="flex items-center gap-1 text-xs text-gray-400">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        <?= $fmt_time; ?>
                                    </span>
                                    <span class="flex items-center gap-1 text-xs text-gray-400">
                                        <i data-lucide="mail" class="w-3 h-3"></i>
                                        Email sent to <?= htmlspecialchars($user_email); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="px-6 py-3 border-t border-gray-100">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">End of notifications</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
    function openSidebar()  { document.getElementById('sidebar').classList.remove('-translate-x-full'); document.getElementById('backdrop').classList.remove('hidden'); }
    function closeSidebar() { document.getElementById('sidebar').classList.add('-translate-x-full');    document.getElementById('backdrop').classList.add('hidden'); }
</script>
</body>
</html>

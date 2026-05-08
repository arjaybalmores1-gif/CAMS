<?php
session_start();
include("conn.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_username = $_SESSION['username'] ?? 'Admin';

// Count unread (pending) appointment notifications
$unread_res   = mysqli_query($conn, "SELECT COUNT(*) as total FROM appointments WHERE status = 'pending'");
$unread_count = mysqli_fetch_assoc($unread_res)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAMS — Notifications</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
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
    <aside id="sidebar" class="fixed md:static inset-y-0 left-0 z-50 w-64 bg-pink-500 text-white flex flex-col shadow-lg
                               -translate-x-full md:translate-x-0 transition-transform duration-300">
        <div class="flex items-center p-4 gap-3">
            <img class="w-12 h-12 flex-shrink-0" src="mainlogo.png" alt="CAMS Logo">
            <div>
                <h1 class="font-bold text-xl uppercase tracking-tighter leading-none">CAMS</h1>
                <p class="font-bold text-[8px] text-pink-100 uppercase tracking-wider opacity-90 leading-tight mt-0.5">
                    Clinic Appointment <br> Management System
                </p>
            </div>
        </div>

        <hr class="border-pink-400 mx-4 opacity-50">

        <nav class="mt-4 flex-1 px-3">
            <ul class="space-y-1">
                <li><a href="admin_dashboard.php" class="flex items-center hover:bg-pink-600 px-3 py-2.5 rounded-lg transition-all"><i data-lucide="layout-dashboard" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">Dashboard</span></a></li>
                <li><a href="patients_dashB.php" class="flex items-center hover:bg-pink-600 px-3 py-2.5 rounded-lg transition-all"><i data-lucide="users" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">Patients</span></a></li>
                <li><a href="doctors_dashB.php" class="flex items-center hover:bg-pink-600 px-3 py-2.5 rounded-lg transition-all"><i data-lucide="stethoscope" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">Doctors</span></a></li>
                <li><a href="appointment_dashB.php" class="flex items-center hover:bg-pink-600 px-3 py-2.5 rounded-lg transition-all"><i data-lucide="clipboard-check" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">Appointments</span></a></li>
                <li><a href="notification_dashB.php" class="flex items-center bg-pink-600 border-l-4 border-white px-3 py-2.5 rounded-lg transition-all"><i data-lucide="bell" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">Notifications</span></a></li>
            </ul>
        </nav>

        <div class="px-4 pb-3">
            <div class="bg-pink-600/50 rounded-2xl p-3">
                <p class="text-[10px] text-pink-200 font-bold uppercase tracking-widest mb-1">Logged in as</p>
                <p class="font-bold text-white text-sm truncate"><?= htmlspecialchars($admin_username); ?></p>
                <p class="text-pink-200 text-xs">Administrator</p>
            </div>
        </div>

        <div class="px-3 pb-5">
            <hr class="border-pink-400 mb-3 opacity-50">
            <button onclick="location.href='logout.php'" class="w-full flex items-center px-3 py-2.5 hover:bg-pink-700 rounded-xl transition-all text-pink-100 hover:text-white">
                <i data-lucide="log-out" class="w-5 h-5 flex-shrink-0"></i>
                <span class="ml-3 font-bold text-sm">Logout</span>
            </button>
        </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        <!-- Topbar -->
        <header class="bg-white border-b border-gray-100 shadow-sm px-6 py-4 flex justify-between items-center flex-shrink-0">
            <div class="flex items-center gap-4">
                <button onclick="openSidebar()" class="md:hidden text-gray-500 hover:text-pink-500 transition-colors">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <h2 class="text-xl font-bold text-gray-700">Notifications</h2>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-bold text-gray-800"><?= htmlspecialchars($admin_username); ?></p>
                    <p class="text-[10px] text-green-500 font-semibold uppercase">Online</p>
                </div>
                <div class="w-9 h-9 bg-pink-100 rounded-full flex items-center justify-center text-pink-500">
                    <i data-lucide="bell" class="w-4 h-4"></i>
                </div>
            </div>
        </header>

        <!-- Body -->
        <div class="flex-1 overflow-hidden p-6 bg-gray-50/50">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 h-full flex flex-col overflow-hidden">

                <!-- Header with badge -->
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-black text-gray-800">New Appointment Requests</h3>
                        <p class="text-xs text-gray-400 font-medium mt-0.5">Patients waiting for your approval.</p>
                    </div>
                    <?php if ($unread_count > 0): ?>
                    <span class="bg-pink-500 text-white text-xs font-black px-3 py-1 rounded-full">
                        <?= $unread_count; ?> pending
                    </span>
                    <?php endif; ?>
                </div>

                <!-- Pending appointments list -->
                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <?php
                    $result = mysqli_query($conn, "
                        SELECT a.*, p.name AS patient_name, p.contact, p.email,
                               d.name AS doctor_name, d.specialization
                        FROM appointments a
                        JOIN patients p ON a.patient_id = p.id
                        JOIN doctors d ON a.doctor_id = d.id
                        WHERE a.status = 'pending'
                        ORDER BY a.id DESC
                    ");

                    if (mysqli_num_rows($result) > 0):
                        while ($row = mysqli_fetch_assoc($result)):
                            $fmt_date = date('M d, Y', strtotime($row['appointment_date']));
                            $fmt_time = date('h:i A', strtotime($row['appointment_time']));
                    ?>
                    <div class="px-6 py-5 border-b border-gray-50 hover:bg-pink-50/30 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <!-- Avatar -->
                            <div class="w-10 h-10 bg-pink-100 rounded-full flex items-center justify-center text-pink-500 flex-shrink-0 mt-0.5">
                                <i data-lucide="user" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($row['patient_name']); ?></p>
                                <p class="text-xs text-gray-500 mt-0.5"><?= htmlspecialchars($row['email']); ?> &middot; <?= htmlspecialchars($row['contact']); ?></p>
                                <div class="flex items-center gap-3 mt-2 flex-wrap">
                                    <span class="flex items-center gap-1 text-xs text-gray-600">
                                        <i data-lucide="stethoscope" class="w-3.5 h-3.5 text-pink-400"></i>
                                        <?= htmlspecialchars($row['doctor_name']); ?>
                                        <span class="text-gray-400">— <?= htmlspecialchars($row['specialization']); ?></span>
                                    </span>
                                    <span class="flex items-center gap-1 text-xs text-gray-600">
                                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-pink-400"></i>
                                        <?= $fmt_date; ?>
                                    </span>
                                    <span class="flex items-center gap-1 text-xs text-gray-600">
                                        <i data-lucide="clock" class="w-3.5 h-3.5 text-pink-400"></i>
                                        <?= $fmt_time; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <!-- Go to appointments -->
                        <a href="appointment_dashB.php"
                           class="flex items-center gap-1.5 text-pink-500 hover:text-pink-700 text-xs font-bold transition-all flex-shrink-0 whitespace-nowrap">
                            Review <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                    <?php
                        endwhile;
                    else:
                    ?>
                    <div class="flex flex-col items-center justify-center h-full py-20 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="bell-off" class="w-8 h-8 text-gray-400"></i>
                        </div>
                        <p class="text-gray-500 font-semibold">All caught up!</p>
                        <p class="text-sm text-gray-400 mt-1">No pending appointment requests right now.</p>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="px-6 py-3 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Showing pending requests only</p>
                    <a href="appointment_dashB.php" class="text-xs text-pink-500 font-bold hover:underline">View all appointments →</a>
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

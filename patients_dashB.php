<?php
session_start();
include("conn.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_username = $_SESSION['username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAMS — Patients</title>
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
                <li><a href="patients_dashB.php" class="flex items-center bg-pink-600 border-l-4 border-white px-3 py-2.5 rounded-lg transition-all"><i data-lucide="users" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">Patients</span></a></li>
                <li><a href="doctors_dashB.php" class="flex items-center hover:bg-pink-600 px-3 py-2.5 rounded-lg transition-all"><i data-lucide="stethoscope" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">Doctors</span></a></li>
                <li><a href="appointment_dashB.php" class="flex items-center hover:bg-pink-600 px-3 py-2.5 rounded-lg transition-all"><i data-lucide="clipboard-check" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">Appointments</span></a></li>
                <li><a href="notification_dashB.php" class="flex items-center hover:bg-pink-600 px-3 py-2.5 rounded-lg transition-all"><i data-lucide="message-square-dot" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">Notifications</span></a></li>
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
                <h2 class="text-xl font-bold text-gray-700">Patients</h2>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-bold text-gray-800"><?= htmlspecialchars($admin_username); ?></p>
                    <p class="text-[10px] text-green-500 font-semibold uppercase">Online</p>
                </div>
                <div class="w-9 h-9 bg-pink-100 rounded-full flex items-center justify-center text-pink-500">
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                </div>
            </div>
        </header>

        <!-- Body -->
        <div class="flex-1 overflow-hidden p-6 bg-gray-50/50">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 h-full flex flex-col overflow-hidden">

                <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h3 class="text-lg font-black text-gray-800">Patient Records</h3>
                        <p class="text-xs text-gray-400 font-medium mt-0.5">All patients registered through the patient portal.</p>
                    </div>
                    <form method="GET" class="flex border border-gray-200 rounded-xl px-4 py-2 bg-gray-50 focus-within:ring-2 focus-within:ring-pink-300 transition-all">
                        <input type="text" name="search" placeholder="Search patient..." value="<?= htmlspecialchars($_GET['search'] ?? ''); ?>" class="bg-transparent outline-none w-44 text-xs text-gray-600">
                        <button type="submit" class="text-pink-500 ml-2"><i data-lucide="search" class="w-4 h-4"></i></button>
                    </form>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead class="sticky top-0 z-10">
                            <tr class="bg-gray-50 text-gray-400 uppercase text-[10px] font-black tracking-widest border-b">
                                <th class="px-6 py-4">Patient Name</th>
                                <th class="px-6 py-4">Age / Gender</th>
                                <th class="px-6 py-4">Date Registered</th>
                                <th class="px-6 py-4">Contact Info</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php
                            $query = "SELECT * FROM patients";
                            if (!empty($_GET['search'])) {
                                $search = mysqli_real_escape_string($conn, $_GET['search']);
                                $query .= " WHERE name LIKE '%$search%' OR email LIKE '%$search%' OR contact LIKE '%$search%'";
                            }
                            $query .= " ORDER BY id DESC";
                            $result = mysqli_query($conn, $query);

                            if (mysqli_num_rows($result) > 0):
                                while ($row = mysqli_fetch_assoc($result)):
                                    $date    = date('M d, Y', strtotime($row['created_at']));
                                    $name    = htmlspecialchars($row['name']);
                                    $contact = htmlspecialchars($row['contact']);
                                    $email   = htmlspecialchars($row['email']);
                                    $age     = (int) $row['age'];
                                    $gender  = htmlspecialchars($row['gender']);
                            ?>
                            <tr class="hover:bg-pink-50/30 transition-all">
                                <td class="px-6 py-4 font-bold text-gray-700 text-sm"><?= $name; ?></td>
                                <td class="px-6 py-4 text-gray-500 text-sm">
                                    <span class="block font-medium"><?= $age; ?> yrs old</span>
                                    <span class="text-xs"><?= $gender; ?></span>
                                </td>
                                <td class="px-6 py-4 text-gray-400 text-sm"><?= $date; ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="block font-medium text-gray-700"><?= $contact; ?></span>
                                    <span class="text-xs text-gray-400"><?= $email; ?></span>
                                </td>
                            </tr>
                            <?php
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic text-sm">No patient records found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-3 border-t border-gray-100">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">End of results</p>
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

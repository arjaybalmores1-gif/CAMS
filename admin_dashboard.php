<?php
session_start();
include("conn.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_username = $_SESSION['username'] ?? 'Admin';

// Handle create account form
$alert_type = '';
$alert_msg  = '';

if (isset($_POST['create_account'])) {
    $new_username = $conn->real_escape_string(trim($_POST['new_username']));
    $new_email    = $conn->real_escape_string(trim($_POST['new_email']));
    $new_password = $_POST['new_password'];
    $new_role     = $conn->real_escape_string($_POST['new_role']);
    $hashed       = password_hash($new_password, PASSWORD_DEFAULT);

    $check = $conn->query("SELECT id FROM users WHERE username = '$new_username'");
    if ($check->num_rows > 0) {
        $alert_type = 'error';
        $alert_msg  = 'Username already exists.';
    } else {
        $conn->query("INSERT INTO users (username, email, password, role) VALUES ('$new_username', '$new_email', '$hashed', '$new_role')");
        $alert_type = 'success';
        $alert_msg  = "Account \"$new_username\" created successfully!";
    }
}

$doctors_result  = mysqli_query($conn, "SELECT COUNT(*) as total FROM doctors");
$doctors_count   = mysqli_fetch_assoc($doctors_result)['total'] ?? 0;

$patients_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM patients");
$patient_count   = mysqli_fetch_assoc($patients_result)['total'] ?? 0;

$pending_result  = mysqli_query($conn, "SELECT COUNT(*) as total FROM appointments WHERE status='pending'");
$total_pending   = mysqli_fetch_assoc($pending_result)['total'] ?? 0;

$today_result    = mysqli_query($conn, "SELECT COUNT(*) as total FROM appointments WHERE appointment_date = CURDATE()");
$total_today     = mysqli_fetch_assoc($today_result)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAMS — Admin Dashboard</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #f472b6; border-radius: 10px; }
    </style>
</head>
<body class="bg-gray-50">

<!-- Alert Modal -->
<div id="alertModal" class="fixed inset-0 bg-black/50 z-[300] hidden items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-sm mx-4 text-center">
        <div id="alertIcon" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"></div>
        <h3 id="alertTitle" class="text-lg font-black text-gray-800 mb-2"></h3>
        <p id="alertMsg" class="text-sm text-gray-500 mb-6"></p>
        <button onclick="closeAlert()" class="w-full py-3 bg-pink-500 hover:bg-pink-600 text-white rounded-xl font-bold text-sm transition-all">OK</button>
    </div>
</div>

<!-- Create Account Modal -->
<div id="createAccountModal" class="fixed inset-0 bg-black/60 z-[200] hidden items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden">
        <div class="bg-pink-500 p-6 text-white flex justify-between items-center">
            <div class="flex items-center gap-3">
                <i data-lucide="user-plus" class="w-5 h-5"></i>
                <h3 class="text-lg font-bold">Create New Account</h3>
            </div>
            <button onclick="closeCreateModal()" class="hover:bg-pink-600 p-1 rounded-full transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Username</label>
                <input type="text" name="new_username" required placeholder="e.g. staff_juan"
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none text-sm transition-all">
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Email</label>
                <input type="email" name="new_email" required placeholder="email@clinic.com"
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none text-sm transition-all">
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Password</label>
                <input type="password" name="new_password" required placeholder="••••••••" minlength="6"
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none text-sm transition-all">
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Role</label>
                <select name="new_role" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none text-sm transition-all">
                    <option value="" disabled selected>Select role</option>
                    <option value="admin">Admin</option>
                    <option value="staff">Staff</option>
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeCreateModal()" class="flex-1 py-3 border border-gray-200 text-gray-500 rounded-xl font-bold text-sm hover:bg-gray-50 transition-all">Cancel</button>
                <button type="submit" name="create_account" class="flex-1 py-3 bg-pink-500 hover:bg-pink-600 text-white rounded-xl font-bold text-sm transition-all active:scale-95">Create</button>
            </div>
        </form>
    </div>
</div>

<!-- Mobile backdrop -->
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
                <li><a href="admin_dashboard.php" class="flex items-center bg-pink-600 border-l-4 border-white px-3 py-2.5 rounded-lg transition-all"><i data-lucide="layout-dashboard" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">Dashboard</span></a></li>
                <li><a href="patients_dashB.php" class="flex items-center hover:bg-pink-600 px-3 py-2.5 rounded-lg transition-all"><i data-lucide="users" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">Patients</span></a></li>
                <li><a href="doctors_dashB.php" class="flex items-center hover:bg-pink-600 px-3 py-2.5 rounded-lg transition-all"><i data-lucide="stethoscope" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">Doctors</span></a></li>
                <li><a href="appointment_dashB.php" class="flex items-center hover:bg-pink-600 px-3 py-2.5 rounded-lg transition-all"><i data-lucide="clipboard-check" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">Appointments</span></a></li>
                <li><a href="notification_dashB.php" class="flex items-center hover:bg-pink-600 px-3 py-2.5 rounded-lg transition-all"><i data-lucide="message-square-dot" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">Notifications</span></a></li>
            </ul>
        </nav>

        <!-- Logged in as -->
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
                <h2 class="text-xl font-bold text-gray-700">Admin Dashboard</h2>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-bold text-gray-800"><?= htmlspecialchars($admin_username); ?></p>
                    <p class="text-[10px] text-green-500 font-semibold uppercase">Online</p>
                </div>
                <button onclick="openCreateModal()" title="Create Account"
                    class="w-9 h-9 bg-pink-50 hover:bg-pink-100 rounded-full flex items-center justify-center text-pink-500 transition-colors">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                </button>
                <div class="w-9 h-9 bg-pink-100 rounded-full flex items-center justify-center text-pink-500">
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                </div>
            </div>
        </header>

        <!-- Body -->
        <main class="flex-1 overflow-y-auto custom-scrollbar p-6">

            <!-- Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="w-11 h-11 bg-pink-50 rounded-xl flex items-center justify-center text-pink-500 flex-shrink-0">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Patients</p>
                        <p class="text-2xl font-black text-gray-800"><?= $patient_count; ?></p>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center text-blue-500 flex-shrink-0">
                        <i data-lucide="stethoscope" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Doctors</p>
                        <p class="text-2xl font-black text-gray-800"><?= $doctors_count; ?></p>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="w-11 h-11 bg-green-50 rounded-xl flex items-center justify-center text-green-500 flex-shrink-0">
                        <i data-lucide="calendar-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Today</p>
                        <p class="text-2xl font-black text-gray-800"><?= $total_today; ?></p>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="w-11 h-11 bg-yellow-50 rounded-xl flex items-center justify-center text-yellow-500 flex-shrink-0">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pending</p>
                        <p class="text-2xl font-black text-gray-800"><?= $total_pending; ?></p>
                    </div>
                </div>
            </div>

            <!-- Recent Appointments -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-black text-gray-800">Recent Appointments</h3>
                        <p class="text-xs text-gray-400 font-medium mt-0.5">Latest 10 bookings</p>
                    </div>
                    <a href="appointment_dashB.php" class="text-pink-500 font-bold text-sm hover:underline">View All →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50 text-gray-400 uppercase text-[10px] font-black border-b border-gray-100">
                                <th class="px-6 py-4">Patient</th>
                                <th class="px-6 py-4">Doctor</th>
                                <th class="px-6 py-4">Schedule</th>
                                <th class="px-6 py-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php
                            $q   = "SELECT a.*, p.name AS patient, d.name AS doctor
                                    FROM appointments a
                                    JOIN patients p ON a.patient_id = p.id
                                    JOIN doctors d ON a.doctor_id = d.id
                                    ORDER BY a.id DESC LIMIT 10";
                            $res = mysqli_query($conn, $q);
                            while ($row = mysqli_fetch_assoc($res)):
                                $status = $row['status'];
                                $color  = match($status) {
                                    'approved' => 'bg-green-100 text-green-600',
                                    'pending'  => 'bg-yellow-100 text-yellow-600',
                                    default    => 'bg-red-100 text-red-600',
                                };
                            ?>
                            <tr class="hover:bg-pink-50/30 transition-all">
                                <td class="px-6 py-4 font-bold text-gray-700 text-sm"><?= htmlspecialchars($row['patient']); ?></td>
                                <td class="px-6 py-4 text-gray-600 text-sm"><?= htmlspecialchars($row['doctor']); ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="font-medium text-gray-700"><?= htmlspecialchars($row['appointment_date']); ?></span><br>
                                    <span class="text-xs text-gray-400"><?= htmlspecialchars($row['appointment_time']); ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="<?= $color; ?> px-3 py-1 rounded-full text-[10px] font-black uppercase"><?= htmlspecialchars($status); ?></span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    lucide.createIcons();

    function openSidebar() {
        document.getElementById('sidebar').classList.remove('-translate-x-full');
        document.getElementById('backdrop').classList.remove('hidden');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.add('-translate-x-full');
        document.getElementById('backdrop').classList.add('hidden');
    }

    // Create Account modal
    function openCreateModal() {
        document.getElementById('createAccountModal').classList.remove('hidden');
        document.getElementById('createAccountModal').classList.add('flex');
    }
    function closeCreateModal() {
        document.getElementById('createAccountModal').classList.add('hidden');
        document.getElementById('createAccountModal').classList.remove('flex');
    }
    document.getElementById('createAccountModal').addEventListener('click', function(e) {
        if (e.target === this) closeCreateModal();
    });

    // Alert modal
    function closeAlert() {
        document.getElementById('alertModal').classList.add('hidden');
        document.getElementById('alertModal').classList.remove('flex');
    }

    <?php if ($alert_type): ?>
    window.addEventListener('DOMContentLoaded', () => {
        const icon  = document.getElementById('alertIcon');
        const title = document.getElementById('alertTitle');
        const msg   = document.getElementById('alertMsg');
        const modal = document.getElementById('alertModal');

        <?php if ($alert_type === 'success'): ?>
        icon.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 bg-green-100';
        icon.innerHTML = '<i data-lucide="check-circle" class="w-8 h-8 text-green-500"></i>';
        title.textContent = 'Account Created!';
        <?php else: ?>
        icon.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 bg-red-100';
        icon.innerHTML = '<i data-lucide="x-circle" class="w-8 h-8 text-red-500"></i>';
        title.textContent = 'Error';
        <?php endif; ?>

        msg.textContent = <?= json_encode($alert_msg); ?>;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        lucide.createIcons();
    });
    <?php endif; ?>
</script>
</body>
</html>

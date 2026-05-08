<?php
session_start();
include("conn.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_username = $_SESSION['username'] ?? 'Admin';

// Handle add doctor form submission
if (isset($_POST['add_doctor'])) {
    $name           = $conn->real_escape_string($_POST['name']);
    $specialization = $conn->real_escape_string($_POST['specialization']);
    $contact        = $conn->real_escape_string($_POST['contact']);
    $status         = $conn->real_escape_string($_POST['status']);

    $stmt = $conn->prepare("INSERT INTO doctors (name, specialization, contact, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $specialization, $contact, $status);

    if ($stmt->execute()) {
        header("Location: doctors_dashB.php?status=success&msg=Doctor added successfully!");
    } else {
        header("Location: doctors_dashB.php?status=error&msg=Error adding doctor.");
    }
    $stmt->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAMS — Doctors</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
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
                <li><a href="doctors_dashB.php" class="flex items-center bg-pink-600 border-l-4 border-white px-3 py-2.5 rounded-lg transition-all"><i data-lucide="stethoscope" class="w-5 h-5 flex-shrink-0"></i><span class="ml-3 font-medium text-sm whitespace-nowrap">Doctors</span></a></li>
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
                <h2 class="text-xl font-bold text-gray-700">Doctors</h2>
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
        <div class="flex-1 overflow-y-auto p-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                <!-- Table Header -->
                <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h3 class="text-lg font-black text-gray-800">Doctor Records</h3>
                        <p class="text-xs text-gray-400 font-medium mt-0.5">Manage and view all registered clinic doctors.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <form method="GET" class="flex border border-gray-200 rounded-xl px-4 py-2 bg-gray-50 focus-within:ring-2 focus-within:ring-pink-300 transition-all">
                            <input type="text" name="search" placeholder="Search doctor..." value="<?= htmlspecialchars($_GET['search'] ?? ''); ?>" class="bg-transparent outline-none w-44 text-xs text-gray-600">
                            <button type="submit" class="text-pink-500 ml-2"><i data-lucide="search" class="w-4 h-4"></i></button>
                        </form>
                        <button onclick="openDoctorModal()" class="flex items-center gap-2 bg-pink-500 hover:bg-pink-600 text-white px-4 py-2.5 rounded-xl text-sm font-bold shadow-sm transition-all active:scale-95">
                            <i data-lucide="plus" class="w-4 h-4"></i> Add Doctor
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50/50 text-gray-400 uppercase text-[10px] font-black tracking-widest border-b">
                                <th class="px-8 py-5">Doctor Name</th>
                                <th class="px-8 py-5">Specialization</th>
                                <th class="px-8 py-5">Contact</th>
                                <th class="px-8 py-5">Status</th>
                                <th class="px-8 py-5 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 bg-white">
                            <?php
                            $query = "SELECT * FROM doctors";
                            if (!empty($_GET['search'])) {
                                $search = mysqli_real_escape_string($conn, $_GET['search']);
                                $query .= " WHERE name LIKE '%$search%' OR specialization LIKE '%$search%'";
                            }
                            $query .= " ORDER BY id DESC";
                            $result = mysqli_query($conn, $query);

                            if (mysqli_num_rows($result) > 0):
                                while ($row = mysqli_fetch_assoc($result)):
                                    $name           = htmlspecialchars($row['name']);
                                    $specialization = htmlspecialchars($row['specialization']);
                                    $contact        = htmlspecialchars($row['contact']);
                                    $status         = htmlspecialchars($row['status']);
                                    $status_class   = $row['status'] === 'available'
                                        ? 'bg-green-100 text-green-600'
                                        : 'bg-red-100 text-red-600';
                            ?>
                            <tr class="hover:bg-pink-50/30 transition-all">
                                <td class="px-8 py-5 font-bold text-gray-700"><?= $name; ?></td>
                                <td class="px-8 py-5 text-gray-500"><?= $specialization; ?></td>
                                <td class="px-8 py-5 text-gray-500"><?= $contact; ?></td>
                                <td class="px-8 py-5">
                                    <span class="<?= $status_class; ?> px-4 py-1.5 rounded-full text-[10px] font-black uppercase"><?= $status; ?></span>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex justify-center gap-2">
                                        <a href="doctors_edit.php?id=<?= $row['id']; ?>" class="bg-blue-50 text-blue-500 hover:bg-blue-500 hover:text-white p-2 rounded-lg transition-all">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </a>
                                        <button onclick="confirmDelete(<?= $row['id']; ?>)" class="bg-red-50 text-red-500 hover:bg-red-500 hover:text-white p-2 rounded-lg transition-all">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="5" class="text-center py-10 text-gray-400 italic">No records found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Doctor Modal -->
<div id="addDoctorModal" class="fixed inset-0 bg-black/60 z-[100] hidden flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden">
        <div class="bg-pink-500 p-6 text-white flex justify-between items-center">
            <div class="flex items-center gap-3">
                <i data-lucide="user-plus" class="w-6 h-6"></i>
                <h3 class="text-xl font-bold">Register New Doctor</h3>
            </div>
            <button onclick="closeDoctorModal()" class="hover:bg-pink-600 p-1 rounded-full transition-colors">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <form method="POST" class="p-8">
            <div class="space-y-5">
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest ml-1">Doctor's Full Name</label>
                    <input type="text" name="name" required placeholder="Dr. Juan Dela Cruz"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none transition-all text-sm">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest ml-1">Specialization</label>
                    <input type="text" name="specialization" required placeholder="e.g. Cardiologist"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none transition-all text-sm">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest ml-1">Contact Number</label>
                        <input type="text" name="contact" required placeholder="09123456789"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none transition-all text-sm">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest ml-1">Current Status</label>
                        <select name="status" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none transition-all text-sm">
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="mt-8 flex gap-3">
                <button type="button" onclick="closeDoctorModal()" class="flex-1 px-6 py-3 border border-gray-200 text-gray-500 rounded-xl font-bold text-sm hover:bg-gray-50 transition-all">Cancel</button>
                <button type="submit" name="add_doctor" class="flex-1 px-6 py-3 bg-pink-500 text-white rounded-xl font-bold text-sm shadow-lg hover:bg-pink-600 active:scale-95 transition-all">Save Doctor</button>
            </div>
        </form>
    </div>
</div>

<!-- Alert Modal -->
<div id="alertModal" class="fixed inset-0 bg-black/50 z-[300] hidden items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-sm mx-4 text-center">
        <div id="alertIcon" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"></div>
        <h3 id="alertTitle" class="text-lg font-black text-gray-800 mb-2"></h3>
        <p id="alertMsg" class="text-sm text-gray-400 mb-6"></p>
        <div class="flex gap-2">
            <button id="cancelBtn" onclick="closeAlert()" class="hidden flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-500 rounded-xl font-bold text-sm transition-all">Cancel</button>
            <button id="mainBtn" onclick="closeAlert()" class="flex-1 py-3 bg-pink-500 hover:bg-pink-600 text-white rounded-xl font-bold text-sm transition-all">OK</button>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();

    // Sidebar
    function openSidebar()  { document.getElementById('sidebar').classList.remove('-translate-x-full'); document.getElementById('backdrop').classList.remove('hidden'); }
    function closeSidebar() { document.getElementById('sidebar').classList.add('-translate-x-full');    document.getElementById('backdrop').classList.add('hidden'); }

    // Doctor modal
    const docModal = document.getElementById('addDoctorModal');
    function openDoctorModal()  { docModal.classList.remove('hidden'); }
    function closeDoctorModal() { docModal.classList.add('hidden'); }

    // Alert modal
    function showAlert(title, message, type = 'success') {
        const modal     = document.getElementById('alertModal');
        const iconDiv   = document.getElementById('alertIcon');
        const titleEl   = document.getElementById('alertTitle');
        const msgEl     = document.getElementById('alertMsg');
        const mainBtn   = document.getElementById('mainBtn');
        const cancelBtn = document.getElementById('cancelBtn');

        iconDiv.className = "w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4";
        cancelBtn.classList.add('hidden');
        mainBtn.innerText = "OK";
        mainBtn.onclick = closeAlert;

        const configs = {
            success: { bg: 'bg-green-100 text-green-600', icon: 'check-circle',    btnClass: 'bg-green-500 hover:bg-green-600' },
            error:   { bg: 'bg-red-100 text-red-600',     icon: 'x-circle',        btnClass: 'bg-red-500 hover:bg-red-600' },
            confirm: { bg: 'bg-orange-100 text-orange-600', icon: 'alert-triangle', btnClass: 'bg-red-500 hover:bg-red-600' },
        };

        const cfg = configs[type] || configs.success;
        iconDiv.classList.add(...cfg.bg.split(' '));
        iconDiv.innerHTML = `<i data-lucide="${cfg.icon}" class="w-8 h-8"></i>`;
        mainBtn.className = `flex-1 py-3 ${cfg.btnClass} text-white rounded-xl font-bold text-sm transition-all`;

        if (type === 'confirm') cancelBtn.classList.remove('hidden');

        titleEl.innerText = title;
        msgEl.innerText   = message;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        lucide.createIcons();
    }

    function closeAlert() {
        const modal = document.getElementById('alertModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Delete confirmation
    function confirmDelete(id) {
        const mainBtn = document.getElementById('mainBtn');
        mainBtn.innerText = "Yes, Delete It";
        mainBtn.onclick = () => { window.location.href = "doctors_delete.php?id=" + id; };
        showAlert("Are you sure?", "This doctor's record will be permanently deleted.", "confirm");
    }

    // Show alert from URL params on page load
    window.onload = () => {
        const params = new URLSearchParams(window.location.search);
        if (params.has('status')) {
            const status = params.get('status');
            const msg    = params.get('msg') || (status === 'success' ? 'Action completed!' : 'Something went wrong.');
            showAlert(status === 'success' ? "Success!" : "Error!", msg, status);
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    };
</script>
</body>
</html>

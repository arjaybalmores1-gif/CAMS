<?php
session_start();
include "conn.php";

// Already logged in? Redirect.
if (isset($_SESSION['role'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'patient/portal.php'));
    exit();
}

$alert_type = '';
$alert_msg  = '';

if (isset($_POST['login'])) {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $password = $_POST['pass'];

    $result = $conn->query("SELECT * FROM users WHERE username = '$username'");

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            header("Location: " . ($user['role'] === 'admin' ? 'admin/dashboard.php' : 'patient/portal.php'));
            exit();
        } else {
            $alert_type = 'error';
            $alert_msg  = 'Incorrect password. Please try again.';
        }
    } else {
        $alert_type = 'error';
        $alert_msg  = 'Username not found.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAMS — Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-pink-100 via-white to-rose-50 flex items-center justify-center p-4">

<!-- Alert Modal -->
<div id="alertModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-sm mx-4 text-center">
        <div id="alertIcon" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"></div>
        <h3 id="alertTitle" class="text-lg font-black text-gray-800 mb-2"></h3>
        <p id="alertMsg" class="text-sm text-gray-500 mb-6"></p>
        <button onclick="closeAlert()" class="w-full py-3 bg-pink-500 hover:bg-pink-600 text-white rounded-xl font-bold text-sm transition-all">OK</button>
    </div>
</div>

<div class="w-full max-w-sm">

    <!-- Logo -->
    <div class="text-center mb-8">
        <img src="mainlogo.png" alt="CAMS" class="w-20 h-20 mx-auto mb-4">
        <h1 class="text-2xl font-black text-pink-500 uppercase tracking-tight">CAMS</h1>
        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Clinic Appointment Management System</p>
    </div>

    <!-- Card -->
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
        <h2 class="text-xl font-black text-gray-800 mb-1">Welcome back</h2>
        <p class="text-sm text-gray-400 mb-6">Sign in to your account to continue.</p>

        <form method="POST" class="space-y-4">

            <!-- Username -->
            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Username</label>
                <div class="relative">
                    <i data-lucide="user" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                    <input type="text" name="username" required autofocus
                        placeholder="Enter your username"
                        class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 focus:border-pink-300 outline-none text-sm transition-all">
                </div>
            </div>

            <!-- Password -->
            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Password</label>
                <div class="relative">
                    <i data-lucide="lock" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                    <input type="password" name="pass" id="passField" required
                        placeholder="Enter your password"
                        class="w-full pl-10 pr-10 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 focus:border-pink-300 outline-none text-sm transition-all">
                    <button type="button" onclick="togglePass()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                        <i id="eyeIcon" data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" name="login"
                class="w-full py-3.5 bg-pink-500 hover:bg-pink-600 active:scale-95 text-white font-bold rounded-xl transition-all shadow-md shadow-pink-200 mt-2">
                Sign In
            </button>
        </form>

        <!-- Register link -->
        <div class="mt-6 pt-6 border-t border-gray-100 text-center space-y-3">
            <div>
                <p class="text-sm text-gray-500">New patient?</p>
                <a href="patient/register.php" class="inline-flex items-center gap-2 mt-1 text-pink-500 hover:text-pink-700 font-bold text-sm transition-colors">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    Create a patient account
                </a>
            </div>
            <div class="pt-2 border-t border-gray-50">
                <a href="admin_register.php" class="text-xs text-gray-300 hover:text-gray-500 transition-colors">
                    Admin / Staff? Register here
                </a>
            </div>
        </div>
    </div>

    <p class="text-center text-xs text-gray-400 mt-6">&copy; <?= date('Y'); ?> CAMS</p>
</div>

<?php if ($alert_type): ?>
<script>
window.addEventListener('DOMContentLoaded', () => {
    const icon  = document.getElementById('alertIcon');
    const title = document.getElementById('alertTitle');
    const msgEl = document.getElementById('alertMsg');
    const modal = document.getElementById('alertModal');

    <?php if ($alert_type === 'error'): ?>
    icon.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 bg-red-100';
    icon.innerHTML = '<i data-lucide="x-circle" class="w-8 h-8 text-red-500"></i>';
    title.textContent = 'Login Failed';
    <?php else: ?>
    icon.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 bg-green-100';
    icon.innerHTML = '<i data-lucide="check-circle" class="w-8 h-8 text-green-500"></i>';
    title.textContent = 'Success!';
    <?php endif; ?>

    msgEl.textContent = <?= json_encode($alert_msg); ?>;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    lucide.createIcons();
});
</script>
<?php endif; ?>

<script>
    lucide.createIcons();

    function togglePass() {
        const field = document.getElementById('passField');
        const icon  = document.getElementById('eyeIcon');
        const isHidden = field.type === 'password';
        field.type = isHidden ? 'text' : 'password';
        icon.setAttribute('data-lucide', isHidden ? 'eye-off' : 'eye');
        lucide.createIcons();
    }

    function closeAlert() {
        document.getElementById('alertModal').classList.add('hidden');
        document.getElementById('alertModal').classList.remove('flex');
    }
</script>
</body>
</html>

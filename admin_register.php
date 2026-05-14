<?php
session_start();
include "conn.php";

// If already logged in, redirect
if (isset($_SESSION['role'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'patient/portal.php'));
    exit();
}

$error   = '';
$success = '';

if (isset($_POST['register'])) {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $email    = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $role     = $conn->real_escape_string($_POST['role']);

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $check = $conn->query("SELECT id FROM users WHERE username = '$username'");
        if ($check->num_rows > 0) {
            $error = "Username already taken. Please choose another.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $conn->query("INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$hashed', '$role')");
            $success = "Account created! You can now log in.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAMS — Admin Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-pink-50 via-white to-rose-50 flex items-center justify-center p-4">

<div class="w-full max-w-sm">

    <!-- Logo -->
    <div class="text-center mb-8">
        <img src="mainlogo.png" alt="CAMS" class="w-16 h-16 mx-auto mb-3">
        <h1 class="text-2xl font-black text-pink-500 uppercase tracking-tight">CAMS</h1>
        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Admin / Staff Registration</p>
    </div>

    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">

        <div class="bg-pink-500 p-6 text-white">
            <div class="flex items-center gap-3">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
                <h2 class="text-lg font-bold">Create Admin Account</h2>
            </div>
            <p class="text-pink-100 text-xs mt-1">For clinic staff and administrators only.</p>
        </div>

        <div class="p-6">

            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 rounded-xl p-3 flex items-center gap-3 mb-5">
                <i data-lucide="alert-circle" class="w-4 h-4 text-red-500 flex-shrink-0"></i>
                <p class="text-sm text-red-700 font-medium"><?= htmlspecialchars($error); ?></p>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 rounded-xl p-3 flex items-center gap-3 mb-5">
                <i data-lucide="check-circle" class="w-4 h-4 text-green-500 flex-shrink-0"></i>
                <div>
                    <p class="text-sm text-green-700 font-medium"><?= htmlspecialchars($success); ?></p>
                    <a href="login.php" class="text-sm text-green-600 font-bold underline">Go to Login →</a>
                </div>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Username</label>
                    <input type="text" name="username" required placeholder="e.g. admin_juan"
                        value="<?= htmlspecialchars($_POST['username'] ?? ''); ?>"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none text-sm transition-all">
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Email</label>
                    <input type="email" name="email" required placeholder="admin@clinic.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none text-sm transition-all">
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Role</label>
                    <select name="role" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none text-sm transition-all">
                        <option value="" disabled selected>Select role</option>
                        <option value="admin"  <?= ($_POST['role'] ?? '') === 'admin'  ? 'selected' : ''; ?>>Admin</option>
                        <option value="staff"  <?= ($_POST['role'] ?? '') === 'staff'  ? 'selected' : ''; ?>>Staff</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="passField" required placeholder="Min. 6 characters"
                            class="w-full px-4 pr-10 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none text-sm transition-all">
                        <button type="button" onclick="togglePass('passField', 'eye1')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i id="eye1" data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Confirm Password</label>
                    <div class="relative">
                        <input type="password" name="confirm_password" id="confirmField" required placeholder="Repeat password"
                            class="w-full px-4 pr-10 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none text-sm transition-all">
                        <button type="button" onclick="togglePass('confirmField', 'eye2')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i id="eye2" data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" name="register"
                    class="w-full py-3.5 bg-pink-500 hover:bg-pink-600 active:scale-95 text-white font-bold rounded-xl transition-all shadow-md shadow-pink-200 mt-2">
                    Create Account
                </button>
            </form>

            <div class="mt-5 pt-5 border-t border-gray-100 text-center">
                <a href="login.php" class="inline-flex items-center gap-2 text-gray-400 hover:text-gray-600 text-sm font-semibold transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Back to Login
                </a>
            </div>
        </div>
    </div>

    <p class="text-center text-xs text-gray-400 mt-6">&copy; <?= date('Y'); ?> CAMS</p>
</div>

<script>
    lucide.createIcons();

    function togglePass(fieldId, iconId) {
        const field  = document.getElementById(fieldId);
        const icon   = document.getElementById(iconId);
        const hidden = field.type === 'password';
        field.type   = hidden ? 'text' : 'password';
        icon.setAttribute('data-lucide', hidden ? 'eye-off' : 'eye');
        lucide.createIcons();
    }
</script>
</body>
</html>

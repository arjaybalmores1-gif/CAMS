<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/PHPMailer.php';
require 'src/SMTP.php';
require 'src/Exception.php';

session_start();
include("conn.php");

// Already logged in? Redirect.
if (isset($_SESSION['role'])) {
    $redirect = match($_SESSION['role']) {
        'admin'   => 'admin_dashboard.php',
        'patient' => 'patient_portal.php',
        default   => 'login.php',
    };
    header("Location: $redirect");
    exit();
}

$error   = '';
$success = '';

if (isset($_POST['register'])) {
    $name     = $conn->real_escape_string(trim($_POST['name']));
    $age      = (int) $_POST['age'];
    $gender   = $conn->real_escape_string($_POST['gender']);
    $contact  = $conn->real_escape_string(trim($_POST['contact']));
    $email    = $conn->real_escape_string(trim($_POST['email']));
    $address  = $conn->real_escape_string(trim($_POST['address']));
    $username = $conn->real_escape_string(trim($_POST['username']));
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    // Validation
    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check if username already taken
        $check_user = $conn->query("SELECT id FROM users WHERE username = '$username'");
        if ($check_user->num_rows > 0) {
            $error = "Username is already taken. Please choose another.";
        } else {
            // Check if email already registered
            $check_email = $conn->query("SELECT id FROM users WHERE email = '$email'");
            if ($check_email->num_rows > 0) {
                $error = "An account with this email already exists.";
            } else {
                // Insert into patients table
                $stmt = $conn->prepare("INSERT INTO patients (name, age, gender, contact, email, address) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sissss", $name, $age, $gender, $contact, $email, $address);
                $stmt->execute();
                $stmt->close();

                // Insert into users table
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $conn->query("INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$hashed', 'patient')");

                // Send welcome email
                try {
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'arjaybalmores1@gmail.com';
                    $mail->Password   = 'guay gtpr vgck japb';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->setFrom('arjaybalmores1@gmail.com', 'Clinic System');
                    $mail->addAddress($email, $name);
                    $mail->isHTML(true);
                    $mail->Subject = "Welcome to CAMS — Account Created";
                    $mail->Body    = "
                    <div style='font-family:ui-sans-serif,system-ui,sans-serif;max-width:600px;margin:40px auto;background:#fff;border-radius:1rem;overflow:hidden;border:1px solid #f3f4f6;'>
                        <div style='background:#ec4899;padding:40px 32px;text-align:center;'>
                            <h1 style='color:#fff;margin:0;font-size:28px;font-weight:800;'>CAMS</h1>
                            <p style='color:#fce7f3;margin-top:4px;font-size:11px;text-transform:uppercase;letter-spacing:.1em;'>Clinic Appointment Management System</p>
                        </div>
                        <div style='padding:40px 32px;'>
                            <h2 style='color:#111827;font-size:18px;font-weight:700;margin:0 0 12px;'>Welcome, $name!</h2>
                            <p style='color:#4b5563;font-size:15px;line-height:1.6;'>Your patient account has been successfully created. You can now log in to the Patient Portal to book and manage your appointments.</p>
                            <div style='background:#fdf2f8;border-radius:.75rem;padding:20px;border:1px solid #fce7f3;margin:24px 0;'>
                                <p style='margin:0 0 8px;font-size:12px;font-weight:700;color:#db2777;text-transform:uppercase;'>Your Login Details</p>
                                <p style='margin:0;font-size:14px;color:#374151;'>Username: <strong>$username</strong></p>
                            </div>
                            <p style='color:#9ca3af;font-size:13px;text-align:center;'>Keep your credentials safe and do not share them with anyone.</p>
                        </div>
                        <div style='background:#f9fafb;padding:24px;text-align:center;border-top:1px solid #f3f4f6;'>
                            <p style='margin:0;color:#9ca3af;font-size:11px;'>This is an automated message. Please do not reply.</p>
                        </div>
                    </div>";
                    $mail->send();
                } catch (Exception $e) {
                    error_log("Welcome email error: " . $e->getMessage());
                }

                $success = "Account created successfully! You can now log in.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAMS — Patient Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gradient-to-br from-pink-50 via-white to-pink-50 min-h-screen flex items-center justify-center p-6">

<div class="w-full max-w-2xl">

    <!-- Logo -->
    <div class="text-center mb-8">
        <div class="flex items-center justify-center gap-3 mb-2">
            <img src="mainlogo.png" alt="CAMS" class="w-12 h-12">
            <div class="text-left">
                <h1 class="font-black text-pink-500 text-2xl uppercase tracking-tight">CAMS</h1>
                <p class="text-[9px] text-gray-400 uppercase tracking-widest font-bold leading-none">Patient Portal</p>
            </div>
        </div>
        <p class="text-gray-500 text-sm mt-2">Create your patient account to book and manage appointments online.</p>
    </div>

    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">

        <!-- Header -->
        <div class="bg-pink-500 p-6 text-white">
            <div class="flex items-center gap-3">
                <i data-lucide="user-plus" class="w-6 h-6"></i>
                <h2 class="text-xl font-bold">Create Patient Account</h2>
            </div>
            <p class="text-pink-100 text-sm mt-1">Fill in your details to register.</p>
        </div>

        <div class="p-8">

            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-3 mb-6">
                <i data-lucide="alert-circle" class="w-5 h-5 text-red-500 flex-shrink-0"></i>
                <p class="text-sm text-red-700 font-medium"><?= htmlspecialchars($error); ?></p>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3 mb-6">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-500 flex-shrink-0"></i>
                <div>
                    <p class="text-sm text-green-700 font-medium"><?= htmlspecialchars($success); ?></p>
                    <a href="login.php" class="text-sm text-green-600 font-bold underline mt-1 inline-block">Go to Login →</a>
                </div>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">

                <p class="text-xs font-black uppercase text-pink-500 tracking-widest border-b border-gray-100 pb-2">Personal Information</p>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Full Name</label>
                    <input type="text" name="name" required placeholder="Juan Dela Cruz"
                        value="<?= htmlspecialchars($_POST['name'] ?? ''); ?>"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none transition-all text-sm">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Age</label>
                        <input type="number" name="age" required placeholder="25" min="1" max="120"
                            value="<?= htmlspecialchars($_POST['age'] ?? ''); ?>"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none transition-all text-sm">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Gender</label>
                        <select name="gender" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none transition-all text-sm">
                            <option value="" disabled selected>Select...</option>
                            <option value="Male"   <?= ($_POST['gender'] ?? '') === 'Male'   ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?= ($_POST['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other"  <?= ($_POST['gender'] ?? '') === 'Other'  ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Contact Number</label>
                        <input type="text" name="contact" required placeholder="09123456789"
                            value="<?= htmlspecialchars($_POST['contact'] ?? ''); ?>"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none transition-all text-sm">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Email Address</label>
                        <input type="email" name="email" required placeholder="juan@example.com"
                            value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none transition-all text-sm">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Home Address</label>
                    <textarea name="address" required rows="2" placeholder="Street, Barangay, City"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none transition-all text-sm resize-none"><?= htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                </div>

                <p class="text-xs font-black uppercase text-pink-500 tracking-widest border-b border-gray-100 pb-2 pt-2">Account Credentials</p>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Username</label>
                    <input type="text" name="username" required placeholder="Choose a username"
                        value="<?= htmlspecialchars($_POST['username'] ?? ''); ?>"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none transition-all text-sm">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Password</label>
                        <input type="password" name="password" id="passField" required placeholder="Min. 6 characters"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none transition-all text-sm">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirmField" required placeholder="Repeat password"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none transition-all text-sm">
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="showPass" onchange="togglePasswords()" class="w-4 h-4 cursor-pointer accent-pink-500">
                    <label for="showPass" class="text-sm text-gray-600 cursor-pointer">Show passwords</label>
                </div>

                <button type="submit" name="register" class="w-full py-3.5 bg-pink-500 hover:bg-pink-600 text-white rounded-xl font-bold text-sm shadow-lg active:scale-95 transition-all mt-2">
                    Create Account
                </button>

                <p class="text-center text-sm text-gray-500">
                    Already have an account?
                    <a href="login.php" class="text-pink-500 font-bold hover:underline">Log in here</a>
                </p>
            </form>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();

    function togglePasswords() {
        const type = document.getElementById('showPass').checked ? 'text' : 'password';
        document.getElementById('passField').type    = type;
        document.getElementById('confirmField').type = type;
    }
</script>
</body>
</html>

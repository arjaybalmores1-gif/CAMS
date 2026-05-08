<?php
session_start();
include("conn.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch patient data by ID
if (isset($_GET['id'])) {
    $id   = (int) $_GET['id'];
    $res  = mysqli_query($conn, "SELECT * FROM patients WHERE id = $id");
    $data = mysqli_fetch_assoc($res);

    if (!$data) {
        header("Location: patients_dashB.php?status=error&msg=Patient+not+found.");
        exit;
    }
}

// Handle update form submission
if (isset($_POST['update'])) {
    $id      = (int) $_POST['id'];
    $name    = $conn->real_escape_string($_POST['name']);
    $age     = (int) $_POST['age'];
    $gender  = $conn->real_escape_string($_POST['gender']);
    $contact = $conn->real_escape_string($_POST['contact']);
    $email   = $conn->real_escape_string($_POST['email']);
    $address = $conn->real_escape_string($_POST['address']);

    $sql = "UPDATE patients SET name='$name', age='$age', gender='$gender', contact='$contact', email='$email', address='$address' WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        header("Location: patients_dashB.php?status=success&msg=Patient+record+updated+successfully!");
        exit();
    } else {
        header("Location: patients_dashB.php?status=error&msg=Error+updating+record.");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Patient - CAMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-6">

    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden border border-gray-100">

        <!-- Header -->
        <div class="bg-pink-500 p-6 text-white flex items-center gap-3">
            <i data-lucide="edit-3"></i>
            <h3 class="text-xl font-bold">Update Patient Record</h3>
        </div>

        <form method="POST" class="p-8 space-y-4">
            <input type="hidden" name="id" value="<?= (int) $data['id']; ?>">

            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Full Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($data['name']); ?>" required
                    class="w-full px-4 py-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-pink-300 text-sm">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Age</label>
                    <input type="number" name="age" value="<?= (int) $data['age']; ?>" required
                        class="w-full px-4 py-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-pink-300 text-sm">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Gender</label>
                    <select name="gender" class="w-full px-4 py-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-pink-300 text-sm">
                        <option value="Male"   <?= $data['gender'] === 'Male'   ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?= $data['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other"  <?= $data['gender'] === 'Other'  ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Contact</label>
                <input type="text" name="contact" value="<?= htmlspecialchars($data['contact']); ?>" required
                    class="w-full px-4 py-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-pink-300 text-sm">
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($data['email']); ?>" required
                    class="w-full px-4 py-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-pink-300 text-sm">
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Address</label>
                <textarea name="address" rows="2"
                    class="w-full px-4 py-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-pink-300 text-sm resize-none"><?= htmlspecialchars($data['address']); ?></textarea>
            </div>

            <div class="flex gap-3 pt-4">
                <a href="patients_dashB.php" class="flex-1 px-6 py-3 border border-gray-200 text-center rounded-xl font-bold text-sm hover:bg-gray-50 transition-all">Cancel</a>
                <button type="submit" name="update" class="flex-1 px-6 py-3 bg-pink-500 text-white rounded-xl font-bold text-sm shadow-lg hover:bg-pink-600 transition-all">Update Record</button>
            </div>
        </form>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>

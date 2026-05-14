<?php
session_start();
include("../conn.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id  = (int) $_GET['id'];
    $res = mysqli_query($conn, "SELECT * FROM doctors WHERE id = $id");
    $data = mysqli_fetch_assoc($res);

    if (!$data) {
        header("Location: doctors.php?status=error&msg=Doctor+not+found.");
        exit;
    }
}

if (isset($_POST['update_doctor'])) {
    $id             = (int) $_POST['id'];
    $name           = $conn->real_escape_string($_POST['name']);
    $specialization = $conn->real_escape_string($_POST['specialization']);
    $contact        = $conn->real_escape_string($_POST['contact']);
    $status         = $conn->real_escape_string($_POST['status']);

    $sql = "UPDATE doctors SET name='$name', specialization='$specialization', contact='$contact', status='$status' WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        header("Location: doctors.php?status=success&msg=Doctor+record+updated+successfully!");
        exit();
    } else {
        header("Location: doctors.php?status=error&msg=Error+updating+record.");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Doctor - CAMS</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden">
        <div class="bg-pink-500 p-6 text-white flex justify-between items-center">
            <div class="flex items-center gap-3">
                <i data-lucide="user-plus" class="w-6 h-6"></i>
                <h3 class="text-xl font-bold">Update Doctor Record</h3>
            </div>
            <a href="doctors.php" class="hover:bg-pink-600 p-1 rounded-full transition-colors">
                <i data-lucide="x" class="w-6 h-6"></i>
            </a>
        </div>

        <form method="POST" class="p-8">
            <input type="hidden" name="id" value="<?= (int) $data['id']; ?>">

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2 space-y-1">
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Doctor's Full Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($data['name']); ?>" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none transition-all text-sm">
                </div>
                <div class="col-span-2 space-y-1">
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Specialization</label>
                    <input type="text" name="specialization" value="<?= htmlspecialchars($data['specialization']); ?>" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none transition-all text-sm">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Contact Number</label>
                    <input type="text" name="contact" value="<?= htmlspecialchars($data['contact']); ?>" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none transition-all text-sm">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Current Status</label>
                    <select name="status" required class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-pink-300 outline-none transition-all text-sm">
                        <option value="available"   <?= $data['status'] === 'available'   ? 'selected' : ''; ?>>Available</option>
                        <option value="unavailable" <?= $data['status'] === 'unavailable' ? 'selected' : ''; ?>>Unavailable</option>
                    </select>
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <a href="doctors.php" class="flex-1 px-6 py-3 border border-gray-200 text-center text-gray-500 rounded-xl font-bold text-sm hover:bg-gray-50 transition-all">Cancel</a>
                <button type="submit" name="update_doctor" class="flex-1 px-6 py-3 bg-pink-500 text-white rounded-xl font-bold text-sm shadow-lg hover:bg-pink-600 active:scale-95 transition-all">Update Record</button>
            </div>
        </form>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>

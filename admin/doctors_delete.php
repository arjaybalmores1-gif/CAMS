<?php
session_start();
include("../conn.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM doctors WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: doctors.php?status=success&msg=Doctor+deleted+successfully!");
    } else {
        header("Location: doctors.php?status=error&msg=Could+not+delete+the+record.");
    }
    $stmt->close();
}

<?php
session_start();
include '../../config/db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'owner'])) {
    header("Location: ../../index.php");
    exit();
}

$announcement_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($announcement_id > 0) {
    $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $announcement_id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header("Location: ../../admin_panel.php");
exit();

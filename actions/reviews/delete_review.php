<?php
session_start();
include '../../config/db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'owner'])) {
    header("Location: ../../index.php");
    exit();
}

$review_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($review_id > 0) {
    $stmt = $conn->prepare("DELETE FROM ratings WHERE id = ?");
    $stmt->bind_param("i", $review_id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

$from_view = $_GET['from'] ?? 'opinions';

header("Location: ../../admin_panel.php?view=" . urlencode($from_view));
exit();

<?php
session_start();
include '../../config/db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'owner'])) {
    header("Location: ../../index.php");
    exit();
}

$movie_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($movie_id > 0) {
    $sql = "DELETE FROM movies WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

header("Location: ../../admin_panel.php");
exit();

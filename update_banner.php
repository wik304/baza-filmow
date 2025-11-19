<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Brak autoryzacji.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['banner_url'])) {
    echo json_encode(['status' => 'error', 'message' => 'Nieprawidłowe żądanie.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$banner_url = trim($_POST['banner_url']);

$banner_to_db = !empty($banner_url) ? $banner_url : NULL;

$sql = "UPDATE users SET profile_banner_url = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $banner_to_db, $user_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Błąd zapisu do bazy danych.']);
}

$stmt->close();
$conn->close();
?>
<?php
session_start();
include '../../config/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'owner'])) {
    echo json_encode(['success' => false, 'message' => 'Brak autoryzacji.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['user_id']) || !isset($_POST['new_role'])) {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowe żądanie.']);
    exit();
}

$user_id_to_change = (int)$_POST['user_id'];
$new_role = $_POST['new_role'];
$current_user_id = $_SESSION['user_id'];
$current_user_role = $_SESSION['user_role'];

if (!in_array($new_role, ['user', 'critic', 'admin', 'owner'])) {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowa rola.']);
    exit();
}

if ($user_id_to_change === $current_user_id) {
    echo json_encode(['success' => false, 'message' => 'Nie możesz zmienić własnej roli.']);
    exit();
}

$stmt_target = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt_target->bind_param("i", $user_id_to_change);
$stmt_target->execute();
$target_user_role = $stmt_target->get_result()->fetch_assoc()['role'];
$stmt_target->close();

if ($current_user_role === 'admin' && ($target_user_role === 'admin' || $target_user_role === 'owner')) {
    echo json_encode(['success' => false, 'message' => 'Admin nie może modyfikować ról innych adminów ani właściciela.']);
    exit();
}

$stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
$stmt->bind_param("si", $new_role, $user_id_to_change);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Błąd zapisu do bazy danych.']);
}
$stmt->close();
$conn->close();

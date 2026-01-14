<?php
session_start();
include '../../config/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Musisz być zalogowany.']);
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
    $target_dir = "../../uploads/avatars/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $original_filename = basename($_FILES["avatar"]["name"]);
    $imageFileType = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));

    $safe_filename = preg_replace('/[^A-Za-z0-9\._-]/', '', pathinfo($original_filename, PATHINFO_FILENAME));
    $unique_filename = $user_id . '_' . time() . '.' . $imageFileType;
    $target_file = $target_dir . $unique_filename;

    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($imageFileType, $allowed_types)) {
        echo json_encode(['status' => 'error', 'message' => 'Dozwolone są tylko pliki JPG, JPEG, PNG, GIF i WEBP.']);
        exit();
    }

    if ($_FILES["avatar"]["size"] > 2000000) { // 2MB
        echo json_encode(['status' => 'error', 'message' => 'Plik jest zbyt duży. Maksymalny rozmiar to 2MB.']);
        exit();
    }

    $sql_old_avatar = "SELECT avatar_url FROM users WHERE id = ?";
    $stmt_old = $conn->prepare($sql_old_avatar);
    $stmt_old->bind_param("i", $user_id);
    $stmt_old->execute();
    $old_avatar_result = $stmt_old->get_result()->fetch_assoc();
    $old_avatar_path = $old_avatar_result['avatar_url'] ?? null;
    $stmt_old->close();

    if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
        $db_avatar_url = "uploads/avatars/" . $unique_filename;
        $sql_update = "UPDATE users SET avatar_url = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $db_avatar_url, $user_id);

        if ($stmt_update->execute()) {
            if ($old_avatar_path && file_exists($old_avatar_path) && strpos($old_avatar_path, 'avatar-default.png') === false) {
                if (file_exists("../../" . $old_avatar_path)) unlink("../../" . $old_avatar_path);
            }
            $_SESSION['user_avatar_url'] = $db_avatar_url;

            echo json_encode(['status' => 'success', 'new_avatar_url' => $db_avatar_url]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Błąd zapisu do bazy danych.']);
        }
        $stmt_update->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Wystąpił błąd podczas przesyłania pliku.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Nie przesłano pliku lub wystąpił błąd.']);
}

$conn->close();

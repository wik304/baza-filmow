<?php
session_start();
include '../../config/db_connect.php';
include '../users/functions.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Nieprawidłowe żądanie.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$movie_id = isset($_POST['movie_id']) ? (int)$_POST['movie_id'] : 0;
$list_type = isset($_POST['list_type']) ? $_POST['list_type'] : '';
if ($movie_id === 0 || !in_array($list_type, ['favorite', 'watchlist'])) {
    echo json_encode(['status' => 'error', 'message' => 'Nieprawidłowe dane.']);
    exit();
}

if ($user_id) {
    $sql_check = "SELECT id FROM user_movie_lists WHERE user_id = ? AND movie_id = ? AND list_type = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("iis", $user_id, $movie_id, $list_type);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        $sql_delete = "DELETE FROM user_movie_lists WHERE user_id = ? AND movie_id = ? AND list_type = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("iis", $user_id, $movie_id, $list_type);
        $stmt_delete->execute();
        echo json_encode(['status' => 'success', 'action' => 'removed']);
        if (isset($stmt_delete)) $stmt_delete->close();
    } else {
        $sql_insert = "INSERT INTO user_movie_lists (user_id, movie_id, list_type) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("iis", $user_id, $movie_id, $list_type);
        $stmt_insert->execute();
        echo json_encode(['status' => 'success', 'action' => 'added']);
        if (isset($stmt_insert)) $stmt_insert->close();

        if ($list_type === 'favorite') {
            check_and_grant_achievements($user_id, 'add_to_favorites', $conn);
        } elseif ($list_type === 'watchlist') {
            check_and_grant_achievements($user_id, 'add_to_watchlist', $conn);
        }
    }
    $stmt_check->close();
} else {
    if (!isset($_SESSION['guest_lists'])) {
        $_SESSION['guest_lists'] = ['favorite' => [], 'watchlist' => []];
    }

    $key = array_search($movie_id, $_SESSION['guest_lists'][$list_type]);

    if ($key !== false) {
        unset($_SESSION['guest_lists'][$list_type][$key]);
        echo json_encode(['status' => 'success', 'action' => 'removed']);
    } else {
        $_SESSION['guest_lists'][$list_type][] = $movie_id;
        echo json_encode(['status' => 'success', 'action' => 'added']);
    }
}

$conn->close();

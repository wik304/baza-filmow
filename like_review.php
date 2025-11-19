<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Musisz być zalogowany.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['rating_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Nieprawidłowe żądanie.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$rating_id = (int)$_POST['rating_id'];

$sql_check = "SELECT id FROM review_likes WHERE user_id = ? AND rating_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $user_id, $rating_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Użytkownik już polubił - usuwamy polubienie
    $sql_delete = "DELETE FROM review_likes WHERE user_id = ? AND rating_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("ii", $user_id, $rating_id);
    $stmt_delete->execute();
    $stmt_delete->close();
    $action = 'unliked';
} else {
    // Użytkownik nie polubił - dodajemy polubienie
    $sql_insert = "INSERT INTO review_likes (user_id, rating_id) VALUES (?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("ii", $user_id, $rating_id);
    $stmt_insert->execute();
    $stmt_insert->close();
    $action = 'liked';
}
$stmt_check->close();

$sql_count = "SELECT COUNT(*) as like_count FROM review_likes WHERE rating_id = ?";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("i", $rating_id);
$stmt_count->execute();
$like_count = $stmt_count->get_result()->fetch_assoc()['like_count'];
$stmt_count->close();

echo json_encode(['status' => 'success', 'action' => $action, 'like_count' => $like_count]);
$conn->close();
?>
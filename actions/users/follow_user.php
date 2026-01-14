<?php
session_start();
include '../../config/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Musisz być zalogowany, aby obserwować.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['followed_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Nieprawidłowe żądanie.']);
    exit();
}

$follower_id = $_SESSION['user_id'];
$followed_id = (int)$_POST['followed_id'];

if ($follower_id === $followed_id) {
    echo json_encode(['status' => 'error', 'message' => 'Nie możesz obserwować samego siebie.']);
    exit();
}

$sql_check = "SELECT id FROM followers WHERE follower_id = ? AND followed_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $follower_id, $followed_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    $sql_delete = "DELETE FROM followers WHERE follower_id = ? AND followed_id = ?";
    $stmt_action = $conn->prepare($sql_delete);
    $action = 'unfollowed';
} else {
    $sql_insert = "INSERT INTO followers (follower_id, followed_id) VALUES (?, ?)";
    $stmt_action = $conn->prepare($sql_insert);
    $action = 'followed';
}

$stmt_action->bind_param("ii", $follower_id, $followed_id);
$stmt_action->execute();

echo json_encode(['status' => 'success', 'action' => $action]);
$conn->close();

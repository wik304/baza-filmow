<?php
session_start();
include '../../config/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'owner'])) {
    echo json_encode(['success' => false, 'message' => 'Brak autoryzacji.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id']) || !isset($_POST['is_active'])) {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowe żądanie.']);
    exit();
}

$id = (int)$_POST['id'];
$is_active = (int)$_POST['is_active'];

$conn->begin_transaction();

try {
    if ($is_active === 0) {
        $stmt = $conn->prepare("UPDATE announcements SET is_active = 0, display_order = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        $result = $conn->query("SELECT id FROM announcements WHERE is_active = 1 ORDER BY display_order ASC");
        $active_announcements = $result->fetch_all(MYSQLI_ASSOC);

        $update_stmt = $conn->prepare("UPDATE announcements SET display_order = ? WHERE id = ?");
        $new_order_data = [];
        foreach ($active_announcements as $index => $ann) {
            $new_display_order = $index + 1;
            $update_stmt->bind_param("ii", $new_display_order, $ann['id']);
            $update_stmt->execute();
            $new_order_data[] = ['id' => $ann['id'], 'order' => $new_display_order];
        }
        $update_stmt->close();

        $conn->commit();
        echo json_encode(['success' => true, 'newOrder' => $new_order_data]);
    } else {
        $result = $conn->query("SELECT MAX(display_order) as max_order FROM announcements WHERE is_active = 1");
        $max_order = (int)$result->fetch_assoc()['max_order'];
        $new_display_order = $max_order + 1;

        $stmt = $conn->prepare("UPDATE announcements SET is_active = 1, display_order = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_display_order, $id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        echo json_encode(['success' => true, 'newOrder' => $new_display_order]);
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Błąd transakcji: ' . $e->getMessage()]);
}
$conn->close();

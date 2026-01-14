<?php
session_start();
include '../../config/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'owner'])) {
    echo json_encode(['success' => false, 'message' => 'Brak autoryzacji.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['order']) && is_array($data['order'])) {
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE announcements SET display_order = ? WHERE id = ?");
        foreach ($data['order'] as $item) {
            $stmt->bind_param("ii", $item['order'], $item['id']);
            $stmt->execute();
        }
        $stmt->close();
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Błąd transakcji: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowe dane.']);
}

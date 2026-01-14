<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_user_id = $_SESSION['user_id'] ?? null;

if ($current_user_id && isset($_SESSION['guest_reviews']) && !empty($_SESSION['guest_reviews'])) {
    $stmt_migrate = $conn->prepare("INSERT INTO ratings (user_id, movie_id, rating, comment, rating_type) VALUES (?, ?, ?, ?, 'user') ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), rating_type = 'user'");
    
    foreach ($_SESSION['guest_reviews'] as $m_id => $review_data) {
        $r = (int)$review_data['rating'];
        $c = $review_data['comment'];
        $stmt_migrate->bind_param("iiis", $current_user_id, $m_id, $r, $c);
        $stmt_migrate->execute();
    }
    $stmt_migrate->close();
    unset($_SESSION['guest_reviews']);
}
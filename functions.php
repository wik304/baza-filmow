<?php

function update_movie_popularity($movie_id, $conn) {
    if (!$conn) {
        return;
    }

    $movie_id = (int)$movie_id;

    // Zlicz wystąpienia na listach "ulubione" i "do obejrzenia"
    $sql_lists = "SELECT 
                    SUM(CASE WHEN list_type = 'favorite' THEN 1 ELSE 0 END) as favorites_count,
                    SUM(CASE WHEN list_type = 'watchlist' THEN 1 ELSE 0 END) as watchlist_count
                  FROM user_movie_lists WHERE movie_id = ?";
    $stmt_lists = $conn->prepare($sql_lists);
    $stmt_lists->bind_param("i", $movie_id);
    $stmt_lists->execute();
    $list_counts = $stmt_lists->get_result()->fetch_assoc();
    $stmt_lists->close();

    $favorites_count = $list_counts['favorites_count'] ?? 0;
    $watchlist_count = $list_counts['watchlist_count'] ?? 0;

    // Zlicz recenzje (oceny z komentarzem)
    $sql_rev = "SELECT COUNT(*) as count FROM ratings WHERE movie_id = ? AND comment IS NOT NULL AND TRIM(comment) != ''";
    $stmt_rev = $conn->prepare($sql_rev);
    $stmt_rev->bind_param("i", $movie_id);
    $stmt_rev->execute();
    $reviews_count = $stmt_rev->get_result()->fetch_assoc()['count'] ?? 0;
    $stmt_rev->close();

    // Oblicz wynik popularności (można dostosować wagi)
    $popularity_score = ($favorites_count * 3) + ($reviews_count * 2) + ($watchlist_count * 1);

    // Zaktualizuj tabelę filmów
    $sql_update = "UPDATE movies SET popularity = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("di", $popularity_score, $movie_id);
    $stmt_update->execute();
    $stmt_update->close();
}

function check_and_grant_achievements($user_id, $action_type, $conn) {
    if (!$conn || !$user_id) {
        return;
    }

    // 1. Pobierz wszystkie osiągnięcia dla danej akcji, których użytkownik jeszcze nie ma
    $sql_achievements = "SELECT a.id, a.trigger_threshold
                         FROM achievements a
                         LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = ?
                         WHERE a.trigger_action = ? AND ua.id IS NULL";
    $stmt_achievements = $conn->prepare($sql_achievements);
    $stmt_achievements->bind_param("is", $user_id, $action_type);
    $stmt_achievements->execute();
    $achievements_to_check = $stmt_achievements->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_achievements->close();

    if (empty($achievements_to_check)) {
        return; // Brak nowych osiągnięć do sprawdzenia dla tej akcji
    }

    // 2. Policz, ile razy użytkownik wykonał daną akcję
    $count = 0;
    switch ($action_type) {
        case 'rate_movie':
            $sql_count = "SELECT COUNT(*) FROM ratings WHERE user_id = ? AND rating > 0";
            break;
        case 'write_review':
            $sql_count = "SELECT COUNT(*) FROM ratings WHERE user_id = ? AND comment IS NOT NULL AND TRIM(comment) != ''";
            break;
        case 'add_to_watchlist':
            $sql_count = "SELECT COUNT(*) FROM user_movie_lists WHERE user_id = ? AND list_type = 'watchlist'";
            break;
        case 'add_to_favorites':
            $sql_count = "SELECT COUNT(*) FROM user_movie_lists WHERE user_id = ? AND list_type = 'favorite'";
            break;
        default:
            return; // Nieznana akcja
    }

    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param("i", $user_id);
    $stmt_count->execute();
    $count = $stmt_count->get_result()->fetch_row()[0] ?? 0;
    $stmt_count->close();

    // 3. Sprawdź, czy próg został osiągnięty i przyznaj osiągnięcie
    foreach ($achievements_to_check as $achievement) {
        if ($count >= $achievement['trigger_threshold']) {
            $sql_grant = "INSERT IGNORE INTO user_achievements (user_id, achievement_id) VALUES (?, ?)";
            $stmt_grant = $conn->prepare($sql_grant);
            $stmt_grant->bind_param("ii", $user_id, $achievement['id']);
            $stmt_grant->execute();
            $stmt_grant->close();
        }
    }
}

function transfer_session_lists_to_db($user_id, $conn) {
    if (!$conn || !$user_id || !isset($_SESSION['guest_lists'])) {
        return;
    }

    $guest_lists = $_SESSION['guest_lists'];

    foreach ($guest_lists as $list_type => $movie_ids) {
        if (!empty($movie_ids) && ($list_type === 'favorite' || $list_type === 'watchlist')) {
            // Przygotuj zapytanie, które ignoruje duplikaty
            $sql = "INSERT IGNORE INTO user_movie_lists (user_id, movie_id, list_type) VALUES ";
            $placeholders = [];
            $values = [];
            foreach ($movie_ids as $movie_id) {
                $placeholders[] = "(?, ?, ?)";
                array_push($values, $user_id, (int)$movie_id, $list_type);
            }

            $sql .= implode(', ', $placeholders);
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(str_repeat('iis', count($movie_ids)), ...$values);
            $stmt->execute();
            $stmt->close();
        }
    }
    unset($_SESSION['guest_lists']);
}
?>
<?php
include 'db_connect.php';

header('Content-Type: application/json');

$results = [];

if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
    $search_query = trim($_GET['query']);

    if (strlen($search_query) < 2) {
        echo json_encode($results);
        exit;
    }

    $search_term = "%" . $search_query . "%";

    $sql = "SELECT id, title, poster_url, release_year
            FROM movies
            WHERE title LIKE ?
            ORDER BY popularity DESC, title ASC
            LIMIT 5";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $search_term);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
            $results[] = $row;
        }
        $stmt->close();
    } else {
        error_log("Błąd przygotowania zapytania SQL w autocomplete_movies.php: " . $conn->error);
    }
}

$conn->close();

echo json_encode($results);
?>
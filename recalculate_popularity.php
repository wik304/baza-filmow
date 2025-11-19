<?php
set_time_limit(300); // Ustawia limit czasu wykonania skryptu na 5 minut

session_start();
include 'db_connect.php';
include 'functions.php';

// Zabezpieczenie - tylko dla admina
if (!isset($_SESSION['user_id'])) {
    die("Dostęp zabroniony. Musisz być zalogowany.");
}

$user_id = $_SESSION['user_id'];
$sql_user = "SELECT role FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();

if (!$user || $user['role'] !== 'admin') {
    die("Brak uprawnień. Tylko administrator może uruchomić ten skrypt.");
}

echo "<h3>Przeliczanie popularności filmów...</h3>";

$sql_movies = "SELECT id FROM movies";
$result_movies = $conn->query($sql_movies);

if ($result_movies && $result_movies->num_rows > 0) {
    $movies_count = 0;
    while ($movie = $result_movies->fetch_assoc()) {
        $movie_id = $movie['id'];
        update_movie_popularity($movie_id, $conn);
        echo "Zaktualizowano popularność dla filmu o ID: " . $movie_id . "<br>";
        $movies_count++;
    }
    echo "<hr><strong>Gotowe!</strong> Zaktualizowano popularność dla " . $movies_count . " filmów.";
} else {
    echo "Nie znaleziono żadnych filmów w bazie danych.";
}

$conn->close();
?>
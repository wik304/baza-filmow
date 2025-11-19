<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';
include 'header.php';
?>

<head>
    <link rel="stylesheet" href="search_results.css">
</head>

<main>
    <div class="main-content">

        <?php
        $search_query = '';
        $movies_array = [];

        if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
            $search_query = trim($_GET['query']);

            $search_term = "%" . $search_query . "%";

            $sql = "SELECT m.id, m.title, m.poster_url, m.release_year,
                           AVG(r.rating) as user_rating
                    FROM movies m
                    LEFT JOIN ratings r ON m.id = r.movie_id AND r.rating > 0
                    WHERE m.title LIKE ?
                    GROUP BY m.id, m.title, m.poster_url, m.release_year, m.popularity
                    ORDER BY m.popularity DESC, m.title ASC";

            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("s", $search_term);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $movies_array[] = $row;
                    }
                }
                $stmt->close();
            } else {
                echo "<p style='color: red;'>Błąd przygotowania zapytania: " . $conn->error . "</p>";
            }
        }
        ?>

        <?php if (!empty($search_query)): ?>
            <h1>Wyniki wyszukiwania dla: "<span style="color: #0ccb4a;"><?php echo htmlspecialchars($search_query); ?></span>"</h1>
            <p>Znaleziono **<?php echo count($movies_array); ?>** pasujących tytułów.</p>
        <?php else: ?>
            <h1>Wyszukiwarka Filmów</h1>
            <p>Wpisz frazę w pasku wyszukiwania, aby znaleźć interesujące Cię filmy.</p>
        <?php endif; ?>

        <div class="search-results-container">
            <?php if (!empty($movies_array)): ?>
                <div class="search-results-grid">
                    <?php foreach ($movies_array as $movie): ?>
                        <div class="grid-item">
                            <div class="slide-content">
                                <a href="movie.php?id=<?php echo $movie['id']; ?>" class="movie-card">
                                    <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="Plakat <?php echo htmlspecialchars($movie['title']); ?>">
                                </a>
                                <div class="movie-info">
                                    <div class="ratings">
                                        <div class="rating-item">
                                            <span>Użytkownicy</span>
                                            <strong><?php echo number_format((float)($movie['user_rating'] ?? 0), 1); ?>/10</strong>
                                        </div>
                                    </div>
                                    <h3 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?> (<?php echo htmlspecialchars($movie['release_year']); ?>)</h3>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif (!empty($search_query)): ?>
                <p>Brak wyników pasujących do **"<?php echo htmlspecialchars($search_query); ?>"**. Spróbuj innej frazy.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php
$conn->close();
include 'footer.php';
?>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db_connect.php';
include 'includes/header.php';
?>

<head>
    <link rel="stylesheet" href="assets/css/search_results.css">
</head>

<main>
    <div class="main-content">
        <?php
        $genres_sql = "SELECT genre_id, name FROM genres ORDER BY name ASC";
        $genres_result = $conn->query($genres_sql);
        $genres_list = [];
        if ($genres_result->num_rows > 0) {
            while ($row = $genres_result->fetch_assoc()) {
                $genres_list[] = $row;
            }
        }

        $search_query = '';
        $genre_filter = '';
        $year_from_filter = '';
        $year_to_filter = '';
        $user_rating_from_filter = '';
        $user_rating_to_filter = '';
        $critic_rating_from_filter = '';
        $critic_rating_to_filter = '';
        $sort_by = '';
        $movies_array = [];

        if (isset($_GET['search']) || !empty(trim($_GET['query'] ?? ''))) {
            $search_query = isset($_GET['query']) ? trim(strip_tags($_GET['query'])) : '';
            $genre_filter = isset($_GET['genre']) ? $_GET['genre'] : '';
            $year_from_filter = isset($_GET['year_from']) && $_GET['year_from'] !== '' ? (int)$_GET['year_from'] : '';
            $year_to_filter = isset($_GET['year_to']) && $_GET['year_to'] !== '' ? (int)$_GET['year_to'] : '';
            $user_rating_from_filter = isset($_GET['user_rating_from']) && $_GET['user_rating_from'] !== '' ? (float)$_GET['user_rating_from'] : '';
            $user_rating_to_filter = isset($_GET['user_rating_to']) && $_GET['user_rating_to'] !== '' ? (float)$_GET['user_rating_to'] : '';
            $critic_rating_from_filter = isset($_GET['critic_rating_from']) && $_GET['critic_rating_from'] !== '' ? (float)$_GET['critic_rating_from'] : '';
            $critic_rating_to_filter = isset($_GET['critic_rating_to']) && $_GET['critic_rating_to'] !== '' ? (float)$_GET['critic_rating_to'] : '';
            $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : '';

            $sql_select = "SELECT m.id, m.title, m.poster_url, m.release_year, 
                                  (SELECT AVG(rating) FROM ratings WHERE movie_id = m.id AND rating > 0 AND rating_type = 'user') AS user_rating,
                                  (SELECT AVG(rating) FROM ratings WHERE movie_id = m.id AND rating > 0 AND rating_type = 'critic') AS critic_rating";
            $sql_from = " FROM movies m ";
            $sql_where = " WHERE m.status = 'available' ";
            $sql_group_by = " GROUP BY m.id";
            $sql_having = "";

            switch ($sort_by) {
                case 'year_desc':
                    $sql_order_by = " ORDER BY m.release_year DESC, m.popularity DESC";
                    break;
                case 'year_asc':
                    $sql_order_by = " ORDER BY m.release_year ASC, m.popularity DESC";
                    break;
                case 'user_rating_desc':
                    $sql_order_by = " ORDER BY user_rating DESC, m.popularity DESC";
                    break;
                case 'user_rating_asc':
                    $sql_order_by = " ORDER BY user_rating ASC, m.popularity DESC";
                    break;
                case 'critic_rating_desc':
                    $sql_order_by = " ORDER BY critic_rating DESC, m.popularity DESC";
                    break;
                case 'critic_rating_asc':
                    $sql_order_by = " ORDER BY critic_rating ASC, m.popularity DESC";
                    break;
                default:
                    $sql_order_by = " ORDER BY m.popularity DESC, m.title ASC";
            }

            $params = [];
            $types = "";

            if (!empty($search_query)) {
                $sql_where .= "AND m.title LIKE ?";
                $params[] = "%" . $search_query . "%";
                $types .= "s";
            }

            if (!empty($genre_filter)) {
                $sql_from .= " JOIN movie_genres mg ON m.id = mg.movie_id";
                $sql_where .= " AND mg.genre_id = ? ";
                $params[] = $genre_filter;
                $types .= "i";
            }

            if (!empty($year_from_filter)) {
                $sql_where .= " AND m.release_year >= ? ";
                $params[] = $year_from_filter;
                $types .= "i";
            }

            if (!empty($year_to_filter)) {
                $sql_where .= " AND m.release_year <= ? ";
                $params[] = $year_to_filter;
                $types .= "i";
            }

            $having_conditions = [];
            if (!empty($user_rating_from_filter)) {
                $having_conditions[] = "user_rating >= ?";
                $params[] = $user_rating_from_filter;
                $types .= "d";
            }
            if (!empty($user_rating_to_filter)) {
                $having_conditions[] = "user_rating <= ?";
                $params[] = $user_rating_to_filter;
                $types .= "d";
            }
            if (!empty($critic_rating_from_filter)) {
                $having_conditions[] = "critic_rating >= ?";
                $params[] = $critic_rating_from_filter;
                $types .= "d";
            }
            if (!empty($critic_rating_to_filter)) {
                $having_conditions[] = "critic_rating <= ?";
                $params[] = $critic_rating_to_filter;
                $types .= "d";
            }

            if (!empty($having_conditions)) {
                $sql_having = " HAVING " . implode(" AND ", $having_conditions);
            }

            $sql = $sql_select . $sql_from . $sql_where . $sql_group_by . $sql_having . $sql_order_by;

            $stmt = $conn->prepare($sql);
            if ($stmt) {
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
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

        <form action="search_results.php" method="GET" class="search-filters">
            <input type="text" name="query" placeholder="Wpisz tytuł filmu..." value="<?php echo htmlspecialchars($search_query); ?>">
            <input type="number" name="year_from" placeholder="Rok (od)" value="<?php echo htmlspecialchars($year_from_filter); ?>">
            <input type="number" name="year_to" placeholder="Rok (do)" value="<?php echo htmlspecialchars($year_to_filter); ?>">
            <select name="genre">
                <option value="">Wszystkie gatunki</option>
                <?php foreach ($genres_list as $genre): ?>
                    <option value="<?php echo $genre['genre_id']; ?>" <?php echo ($genre_filter == $genre['genre_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($genre['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="sort_by">
                <option value="">Sortuj domyślnie</option>
                <option value="year_desc" <?php echo ($sort_by == 'year_desc') ? 'selected' : ''; ?>>Rok: od najnowszego</option>
                <option value="year_asc" <?php echo ($sort_by == 'year_asc') ? 'selected' : ''; ?>>Rok: od najstarszego</option>
                <option value="user_rating_desc" <?php echo ($sort_by == 'user_rating_desc') ? 'selected' : ''; ?>>Oceny użytk.: od najwyższej</option>
                <option value="user_rating_asc" <?php echo ($sort_by == 'user_rating_asc') ? 'selected' : ''; ?>>Oceny użytk.: od najniższej</option>
                <option value="critic_rating_desc" <?php echo ($sort_by == 'critic_rating_desc') ? 'selected' : ''; ?>>Oceny krytyków: od najwyższej</option>
                <option value="critic_rating_asc" <?php echo ($sort_by == 'critic_rating_asc') ? 'selected' : ''; ?>>Oceny krytyków: od najniższej</option>
            </select>
            <input type="number" step="0.1" min="0" max="10" name="user_rating_from" placeholder="Ocena użytk. (od)" value="<?php echo htmlspecialchars($user_rating_from_filter); ?>">
            <input type="number" step="0.1" min="0" max="10" name="user_rating_to" placeholder="Ocena użytk. (do)" value="<?php echo htmlspecialchars($user_rating_to_filter); ?>">
            <input type="number" step="0.1" min="0" max="10" name="critic_rating_from" placeholder="Ocena kryt. (od)" value="<?php echo htmlspecialchars($critic_rating_from_filter); ?>">
            <input type="number" step="0.1" min="0" max="10" name="critic_rating_to" placeholder="Ocena kryt. (do)" value="<?php echo htmlspecialchars($critic_rating_to_filter); ?>">
            <button type="submit" name="search">Filtruj</button>
        </form>

        <?php if (isset($_GET['search']) || !empty(trim($_GET['query'] ?? ''))): ?>
            <?php if (!empty($search_query)): ?>
                <h1>Wyniki wyszukiwania dla: "<span style="color: #0ccb4a;"><?php echo htmlspecialchars($search_query); ?></span>"</h1>
            <?php else: ?>
                <h1>Wyniki filtrowania</h1>
            <?php endif; ?>
            <p style="padding-bottom: 0 !important;">Znaleziono <?php echo count($movies_array); ?> pasujących tytułów.</p>

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
                                            <div class="rating-item">
                                                <span>Krytycy</span>
                                                <strong><?php echo number_format((float)($movie['critic_rating'] ?? 0), 1); ?>/10</strong>
                                            </div>
                                        </div>
                                        <h3 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?> (<?php echo htmlspecialchars($movie['release_year']); ?>)</h3>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>Brak wyników pasujących do Twoich kryteriów. Spróbuj innej frazy lub zmień filtry.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p>Wpisz frazę lub wybierz filtry, aby znaleźć interesujące Cię filmy.</p>
        <?php endif; ?>

    </div>
</main>

<?php
$conn->close();
include 'includes/footer.php';
?>
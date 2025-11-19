<?php
session_start();
include 'db_connect.php';

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Sprawdzenie, czy użytkownik jest administratorem
$user_id = $_SESSION['user_id'];
$sql_user = "SELECT role FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();
$stmt_user->close();

if ($user['role'] !== 'admin') {
    // Jeśli użytkownik nie jest adminem, przekieruj go na stronę główną
    header("Location: index.php");
    exit();
}

include 'header.php';

$add_success_message = '';
$add_error_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $release_year = !empty($_POST['release_year']) ? (int)$_POST['release_year'] : null;
    $director = trim($_POST['director'] ?? '');
    $genre_input = trim($_POST['genre'] ?? '');
    $poster_url = ''; // Domyślnie pusta ścieżka plakatu

    if (empty($title) || empty($release_year) || empty($director)) {
        $add_error_message = 'Tytuł, rok wydania i reżyser są polami wymaganymi.';
    } else {
        // Sprawdzenie, czy film o tym tytule i roku już istnieje
        $sql_check = "SELECT id FROM movies WHERE title = ? AND release_year = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("si", $title, $release_year);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows > 0) {
            $add_error_message = 'Film o tytule "' . htmlspecialchars($title) . '" z roku ' . htmlspecialchars($release_year) . ' już istnieje w bazie danych.';
        }
        $stmt_check->close();

        if (empty($add_error_message)) {
            // Obsługa przesyłania pliku plakatu
            if (isset($_FILES['poster_file']) && $_FILES['poster_file']['error'] == 0) {
                $target_dir = "uploads/posters/";
                $original_filename = basename($_FILES["poster_file"]["name"]);
                $imageFileType = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
                
                $safe_filename = preg_replace('/[^A-Za-z0-9\._-]/', '', $original_filename);
                $unique_filename = uniqid() . '_' . $safe_filename;
                $target_file = $target_dir . $unique_filename;

                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($imageFileType, $allowed_types)) {
                    $add_error_message = "Dozwolone są tylko pliki graficzne (JPG, JPEG, PNG, GIF, WEBP).";
                } elseif ($_FILES["poster_file"]["size"] > 5000000) {
                    $add_error_message = "Plik jest zbyt duży. Maksymalny rozmiar to 5MB.";
                } else {
                    if (move_uploaded_file($_FILES["poster_file"]["tmp_name"], $target_file)) {
                        $poster_url = $target_file;
                    } else {
                        $add_error_message = "Wystąpił błąd podczas przesyłania pliku.";
                    }
                }
            }

            if (empty($add_error_message)) {
                $conn->begin_transaction();
                try {
                    // 1. Dodaj film do tabeli `movies`
                    $sql_insert_movie = "INSERT INTO movies (title, description, poster_url, release_year) VALUES (?, ?, ?, ?)";
                    $stmt_movie = $conn->prepare($sql_insert_movie);
                    $stmt_movie->bind_param("sssi", $title, $description, $poster_url, $release_year);
                    $stmt_movie->execute();
                    $new_movie_id = $stmt_movie->insert_id;
                    $stmt_movie->close();

                    // 2. Obsłuż reżyserów
                    $directors_array = array_map('trim', explode(',', $director));
                    foreach ($directors_array as $dir_name) {
                        if (!empty($dir_name)) {
                            // Sprawdź, czy reżyser istnieje, jeśli nie - dodaj
                            $stmt_dir = $conn->prepare("INSERT INTO directors (full_name) VALUES (?) ON DUPLICATE KEY UPDATE full_name=full_name");
                            $stmt_dir->bind_param("s", $dir_name);
                            $stmt_dir->execute();
                            $stmt_dir->close();

                            // Pobierz ID reżysera i połącz z filmem
                            $stmt_get_dir_id = $conn->prepare("SELECT director_id FROM directors WHERE full_name = ?");
                            $stmt_get_dir_id->bind_param("s", $dir_name);
                            $stmt_get_dir_id->execute();
                            $dir_id = $stmt_get_dir_id->get_result()->fetch_assoc()['director_id'];
                            $stmt_get_dir_id->close();

                            $stmt_link_dir = $conn->prepare("INSERT INTO movie_directors (movie_id, director_id) VALUES (?, ?)");
                            $stmt_link_dir->bind_param("ii", $new_movie_id, $dir_id);
                            $stmt_link_dir->execute();
                            $stmt_link_dir->close();
                        }
                    }

                    // 3. Obsłuż gatunki
                    $genres_array = array_map('trim', explode(',', $genre_input));
                    foreach ($genres_array as $gen_name) {
                        if (!empty($gen_name)) {
                            $stmt_gen = $conn->prepare("INSERT INTO genres (name) VALUES (?) ON DUPLICATE KEY UPDATE name=name");
                            $stmt_gen->bind_param("s", $gen_name);
                            $stmt_gen->execute();
                            $stmt_gen->close();

                            $stmt_get_gen_id = $conn->prepare("SELECT genre_id FROM genres WHERE name = ?");
                            $stmt_get_gen_id->bind_param("s", $gen_name);
                            $stmt_get_gen_id->execute();
                            $gen_id = $stmt_get_gen_id->get_result()->fetch_assoc()['genre_id'];
                            $stmt_get_gen_id->close();

                            $stmt_link_gen = $conn->prepare("INSERT INTO movie_genres (movie_id, genre_id) VALUES (?, ?)");
                            $stmt_link_gen->bind_param("ii", $new_movie_id, $gen_id);
                            $stmt_link_gen->execute();
                            $stmt_link_gen->close();
                        }
                    }

                    $conn->commit();
                    $add_success_message = 'Film "' . htmlspecialchars($title) . '" został pomyślnie dodany!';
                } catch (Exception $e) {
                    $conn->rollback();
                    $add_error_message = 'Wystąpił błąd transakcji: ' . $e->getMessage();
                }
            }
        }
    }
}

// Pobranie istniejących gatunków do listy wyboru
$genres = [];
$sql_genres = "SELECT name FROM genres ORDER BY name ASC";
$result_genres = $conn->query($sql_genres);
if ($result_genres && $result_genres->num_rows > 0) {
    while ($row = $result_genres->fetch_assoc()) {
        $genres[] = $row['name'];
    }
}

$conn->close();
?>

<link rel="stylesheet" href="add_movie.css">

<main>
    <div class="form-container">
        <div class="settings-header">
            <a href="settings.php" class="back-link" title="Wróć do ustawień"><i class="fa-solid fa-arrow-left"></i></a>
            <h2>Dodaj nowy film</h2>
        </div>

        <?php
        if (!empty($add_success_message)) echo '<div class="message success-message">' . $add_success_message . '</div>';
        if (!empty($add_error_message)) echo '<div class="message error-message">' . $add_error_message . '</div>';
        ?>

        <form action="add_movie.php" method="POST" class="add-movie-form" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="title">Tytuł filmu</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="release_year">Rok wydania</label>
                    <input type="number" id="release_year" name="release_year" min="1800" max="<?php echo date('Y') + 5; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Opis fabuły</label>
                <textarea id="description" name="description" rows="4"></textarea>
            </div>

            <div class="form-group">
                <label for="poster_file">Plakat filmu</label>
                <input type="file" id="poster_file" name="poster_file" accept="image/*">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="director">Reżyser / Reżyserzy</label>
                    <input type="text" id="director" name="director" required>
                    <small>Wielu reżyserów oddziel przecinkami.</small>
                </div>
                <div class="form-group">
                    <label for="genre">Gatunek / Gatunki</label>
                    <input type="text" id="genre" name="genre" list="genre-list" autocomplete="off">
                    <small>Wiele gatunków oddziel przecinkami.</small>
                    <datalist id="genre-list">
                        <?php foreach ($genres as $g): ?>
                            <option value="<?php echo htmlspecialchars($g); ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">Dodaj film do bazy</button>
            </div>
        </form>
    </div>
</main>

<?php include 'footer.php'; ?>

</body>
</html>
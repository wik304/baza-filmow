<?php
session_start();
include '../../config/db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'owner'])) {
    header("Location: ../../index.php");
    exit();
}

$movie_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($movie_id === 0) {
    header("Location: ../../admin_panel.php");
    exit();
}

$base_url = '../../';
include '../../includes/header.php';

$update_success_message = '';
$update_error_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $release_year = !empty($_POST['release_year']) ? (int)$_POST['release_year'] : null;
    $status = trim($_POST['status'] ?? 'available');
    $director_input = trim($_POST['director'] ?? '');
    $genre_input = trim($_POST['genre'] ?? '');

    if (empty($title) || empty($release_year) || empty($director_input)) {
        $update_error_message = 'Tytuł, rok wydania i reżyser są polami wymaganymi.';
    } else {
        $conn->begin_transaction();
        try {
            $poster_url_to_update = null;
            if (isset($_FILES['poster_file']) && $_FILES['poster_file']['error'] == 0) {
                $target_dir = "../../uploads/posters/";
                $original_filename = basename($_FILES["poster_file"]["name"]);
                $imageFileType = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));

                $safe_filename = preg_replace('/[^A-Za-z0-9\._-]/', '', pathinfo($original_filename, PATHINFO_FILENAME));
                $unique_filename = $safe_filename . '_' . uniqid() . '.' . $imageFileType;
                $target_file = $target_dir . $unique_filename;

                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($imageFileType, $allowed_types)) {
                    throw new Exception("Dozwolone są tylko pliki graficzne (JPG, JPEG, PNG, GIF, WEBP).");
                } elseif ($_FILES["poster_file"]["size"] > 5000000) { // 5 MB
                    throw new Exception("Plik jest zbyt duży. Maksymalny rozmiar to 5MB.");
                }

                $sql_old_poster = "SELECT poster_url FROM movies WHERE id = ?";
                $stmt_old = $conn->prepare($sql_old_poster);
                $stmt_old->bind_param("i", $movie_id);
                $stmt_old->execute();
                $old_poster_url = $stmt_old->get_result()->fetch_assoc()['poster_url'];
                $stmt_old->close();
                if (!empty($old_poster_url) && file_exists($old_poster_url) && strpos($old_poster_url, 'placeholder.jpg') === false) {
                    if (file_exists("../../" . $old_poster_url)) unlink("../../" . $old_poster_url);
                }

                if (move_uploaded_file($_FILES["poster_file"]["tmp_name"], $target_file)) {
                    $poster_url_to_update = "uploads/posters/" . $unique_filename;
                } else {
                    throw new Exception("Wystąpił błąd podczas przesyłania pliku plakatu.");
                }
            }

            $sql_update_movie = "UPDATE movies SET title = ?, description = ?, release_year = ?, status = ? WHERE id = ?";
            $stmt_movie = $conn->prepare($sql_update_movie);
            $stmt_movie->bind_param("ssisi", $title, $description, $release_year, $status, $movie_id);
            $stmt_movie->execute();
            $stmt_movie->close();

            if ($poster_url_to_update !== null) {
                $sql_update_poster = "UPDATE movies SET poster_url = ? WHERE id = ?";
                $stmt_poster = $conn->prepare($sql_update_poster);
                $stmt_poster->bind_param("si", $poster_url_to_update, $movie_id);
                $stmt_poster->execute();
                $stmt_poster->close();
            }

            $stmt_del_dir = $conn->prepare("DELETE FROM movie_directors WHERE movie_id = ?");
            $stmt_del_dir->bind_param("i", $movie_id);
            $stmt_del_dir->execute();
            $stmt_del_dir->close();
            $directors_array = array_map('trim', explode(',', $director_input));
            foreach ($directors_array as $dir_name) {
                if (!empty($dir_name)) {
                    $stmt_dir = $conn->prepare("INSERT INTO directors (full_name) VALUES (?) ON DUPLICATE KEY UPDATE full_name=full_name");
                    $stmt_dir->bind_param("s", $dir_name);
                    $stmt_dir->execute();
                    $stmt_dir->close();

                    $stmt_get_dir_id = $conn->prepare("SELECT director_id FROM directors WHERE full_name = ?");
                    $stmt_get_dir_id->bind_param("s", $dir_name);
                    $stmt_get_dir_id->execute();
                    $dir_id = $stmt_get_dir_id->get_result()->fetch_assoc()['director_id'];
                    $stmt_get_dir_id->close();

                    $stmt_link_dir = $conn->prepare("INSERT INTO movie_directors (movie_id, director_id) VALUES (?, ?)");
                    $stmt_link_dir->bind_param("ii", $movie_id, $dir_id);
                    $stmt_link_dir->execute();
                    $stmt_link_dir->close();
                }
            }

            $stmt_del_gen = $conn->prepare("DELETE FROM movie_genres WHERE movie_id = ?");
            $stmt_del_gen->bind_param("i", $movie_id);
            $stmt_del_gen->execute();
            $stmt_del_gen->close();
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
                    $stmt_link_gen->bind_param("ii", $movie_id, $gen_id);
                    $stmt_link_gen->execute();
                    $stmt_link_gen->close();
                }
            }

            $conn->commit();
            $update_success_message = 'Film "' . htmlspecialchars($title) . '" został pomyślnie zaktualizowany!';
        } catch (Exception $e) {
            $conn->rollback();
            $update_error_message = 'Wystąpił błąd transakcji: ' . $e->getMessage();
        }
    }
}

$sql_movie = "SELECT m.title, m.description, m.release_year, m.status, m.poster_url,
                     GROUP_CONCAT(DISTINCT d.full_name ORDER BY d.full_name SEPARATOR ', ') AS directors,
                     GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') AS genres
              FROM movies m
              LEFT JOIN movie_directors md ON m.id = md.movie_id
              LEFT JOIN directors d ON md.director_id = d.director_id
              LEFT JOIN movie_genres mg ON m.id = mg.movie_id
              LEFT JOIN genres g ON mg.genre_id = g.genre_id
              WHERE m.id = ?
              GROUP BY m.id";
$stmt_get_movie = $conn->prepare($sql_movie);
$stmt_get_movie->bind_param("i", $movie_id);
$stmt_get_movie->execute();
$movie = $stmt_get_movie->get_result()->fetch_assoc();
$stmt_get_movie->close();

$all_genres = [];
$sql_all_genres = "SELECT genre_id, name FROM genres ORDER BY name ASC";
$result_all_genres = $conn->query($sql_all_genres);
if ($result_all_genres) {
    while ($row = $result_all_genres->fetch_assoc()) {
        $all_genres[] = $row;
    }
}

$all_directors = [];
$sql_all_directors = "SELECT full_name FROM directors ORDER BY full_name ASC";
$result_all_directors = $conn->query($sql_all_directors);
if ($result_all_directors) {
    while ($row = $result_all_directors->fetch_assoc()) {
        $all_directors[] = $row;
    }
}

if (!$movie) {
    echo "<main><div class='main-content'><p>Nie znaleziono filmu o podanym ID.</p></div></main>";
    include '../../includes/footer.php';
    exit();
}

$conn->close();
?>

<link rel="stylesheet" href="../../assets/css/add_movie.css">
<style>
    .tag-input-container {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        cursor: text;
    }

    .tag-input-container:focus-within {
        border-color: #0ccb4a;
    }

    .tag {
        display: inline-flex;
        align-items: center;
        background-color: #0ccb4a;
        color: white;
        padding: 0.3rem 0.7rem;
        border-radius: 15px;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .tag .remove-tag {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        margin-left: 0.5rem;
        font-size: 1rem;
        line-height: 1;
    }

    .tag-input-field {
        flex-grow: 1;
        border: none;
        outline: none;
        padding: 0.5rem;
        font-size: 1rem;
        min-width: 150px;
        background: transparent;
    }

    .add-movie-form select {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
        font-family: 'Inter', sans-serif;
        background-color: white;
    }

    .search-form {
        display: flex;
        align-items: stretch;
    }

    .search-form button {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .actions-cell {
        min-height: 75px;
    }
</style>

<main>
    <div class="form-container">
        <div class="settings-header">
            <a href="../../admin_panel.php?view=movies" class="back-link" title="Wróć do panelu admina"><i class="fa-solid fa-arrow-left"></i></a>
            <h2>Edytuj film</h2>
        </div>

        <?php
        if (!empty($update_success_message)) echo '<div class="message success-message">' . $update_success_message . '</div>';
        if (!empty($update_error_message)) echo '<div class="message error-message">' . $update_error_message . '</div>';
        ?>

        <form action="edit_movie.php?id=<?php echo $movie_id; ?>" method="POST" class="add-movie-form" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="title">Tytuł filmu</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($movie['title']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="release_year">Rok wydania</label>
                    <input type="number" id="release_year" name="release_year" value="<?php echo htmlspecialchars($movie['release_year']); ?>" min="1800" max="<?php echo date('Y') + 5; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Opis fabuły</label>
                <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($movie['description']); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Reżyser / Reżyserzy</label>
                    <div class="tag-input-container" data-for="directors-hidden">
                        <input type="text" class="tag-input-field" list="directors-datalist" placeholder="Dodaj reżysera...">
                    </div>
                    <input type="hidden" name="director" id="directors-hidden" value="<?php echo htmlspecialchars($movie['directors']); ?>">
                    <datalist id="directors-datalist">
                        <?php foreach ($all_directors as $director): ?>
                            <option value="<?php echo htmlspecialchars($director['full_name']); ?>">
                            <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="form-group">
                    <label>Gatunek / Gatunki</label>
                    <div class="tag-input-container" data-for="genres-hidden">
                        <input type="text" class="tag-input-field" list="genres-datalist" placeholder="Dodaj gatunek...">
                    </div>
                    <input type="hidden" name="genre" id="genres-hidden" value="<?php echo htmlspecialchars($movie['genres']); ?>">
                    <datalist id="genres-datalist">
                        <?php foreach ($all_genres as $genre): ?>
                            <option value="<?php echo htmlspecialchars($genre['name']); ?>">
                            <?php endforeach; ?>
                    </datalist>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="status">Status filmu</label>
                    <select id="status" name="status">
                        <option value="available" <?php if ($movie['status'] === 'available') echo 'selected'; ?>>Dostępny (Available)</option>
                        <option value="upcoming" <?php if ($movie['status'] === 'upcoming') echo 'selected'; ?>>Nadchodzący (Upcoming)</option>
                    </select>
                    <small>Status 'Dostępny' pozwala na ocenianie filmu i wyświetla go w wyszukiwarce. 'Nadchodzący' ukrywa te opcje.</small>
                </div>
                <div class="form-group">
                    <label for="poster_file">Zmień plakat filmu</label>
                    <input type="file" id="poster_file" name="poster_file" accept="image/*">
                    <small>Obecny plakat: <a href="<?php echo $base_url . htmlspecialchars($movie['poster_url']); ?>" target="_blank">zobacz</a>. Wybierz nowy plik, aby go nadpisać.</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">Zapisz zmiany</button>
            </div>
        </form>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function createTagInput(container) {
            const hiddenInput = document.getElementById(container.dataset.for);
            const textInput = container.querySelector('.tag-input-field');
            let tags = hiddenInput.value ? hiddenInput.value.split(',').map(t => t.trim()).filter(Boolean) : [];

            function renderTags() {
                container.querySelectorAll('.tag').forEach(tagEl => tagEl.remove());

                tags.forEach(tag => {
                    const tagEl = document.createElement('div');
                    tagEl.className = 'tag';
                    tagEl.textContent = tag;

                    const removeBtn = document.createElement('button');
                    removeBtn.className = 'remove-tag';
                    removeBtn.innerHTML = '&times;';
                    removeBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        tags = tags.filter(t => t !== tag);
                        updateHiddenInput();
                        renderTags();
                    });

                    tagEl.appendChild(removeBtn);
                    container.insertBefore(tagEl, textInput);
                });
            }

            function updateHiddenInput() {
                hiddenInput.value = tags.join(', ');
            }

            function addTag(tag) {
                tag = tag.trim();
                if (tag && !tags.includes(tag)) {
                    tags.push(tag);
                    updateHiddenInput();
                    renderTags();
                }
                textInput.value = '';
            }

            textInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    addTag(textInput.value);
                }
            });

            textInput.addEventListener('blur', function() {
                addTag(textInput.value);
            });

            container.addEventListener('click', function() {
                textInput.focus();
            });

            renderTags();
        }

        document.querySelectorAll('.tag-input-container').forEach(container => {
            createTagInput(container);
        });
    });
</script>

</body>

</html>
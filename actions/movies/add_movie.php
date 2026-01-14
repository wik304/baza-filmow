<?php
session_start();
include '../../config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'owner'])) {
    header("Location: ../../index.php");
    exit();
}

$base_url = '../../';
include '../../includes/header.php';

$add_success_message = '';
$add_error_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $release_year = !empty($_POST['release_year']) ? (int)$_POST['release_year'] : null;
    $status = trim($_POST['status'] ?? 'available');
    $director_input = trim($_POST['director'] ?? '');
    $genre_input = trim($_POST['genre'] ?? '');
    $poster_url = 'uploads/posters/placeholder.jpg';

    if (empty($title) || empty($release_year) || empty($director_input)) {
        $add_error_message = 'Tytuł, rok wydania i reżyser to pola wymagane.';
    } else {
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
            if (isset($_FILES['poster_file']) && $_FILES['poster_file']['error'] == 0) {
                $target_dir = "../../uploads/posters/";
                $original_filename = basename($_FILES["poster_file"]["name"]);
                $imageFileType = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));

                $safe_filename = preg_replace('/[^A-Za-z0-9\._-]/', '', pathinfo($original_filename, PATHINFO_FILENAME));
                $unique_filename = $safe_filename . '_' . uniqid() . '.' . $imageFileType;
                $target_file = $target_dir . $unique_filename;

                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($imageFileType, $allowed_types)) {
                    $add_error_message = "Dozwolone są tylko pliki graficzne (JPG, JPEG, PNG, GIF, WEBP).";
                } elseif ($_FILES["poster_file"]["size"] > 5000000) {
                    $add_error_message = "Plik jest zbyt duży. Maksymalny rozmiar to 5MB.";
                } else {
                    if (move_uploaded_file($_FILES["poster_file"]["tmp_name"], $target_file)) {
                        $poster_url = "uploads/posters/" . $unique_filename;
                    } else {
                        $add_error_message = "Wystąpił błąd podczas przesyłania pliku.";
                    }
                }
            }

            if (empty($add_error_message)) {
                $conn->begin_transaction();
                try {
                    $sql_insert_movie = "INSERT INTO movies (title, description, poster_url, release_year) VALUES (?, ?, ?, ?)";
                    $stmt_movie = $conn->prepare($sql_insert_movie);
                    $stmt_movie->bind_param("sssi", $title, $description, $poster_url, $release_year);
                    $stmt_movie->execute();
                    $new_movie_id = $stmt_movie->insert_id;
                    $stmt_movie->close();

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
                            $stmt_link_dir->bind_param("ii", $new_movie_id, $dir_id);
                            $stmt_link_dir->execute();
                            $stmt_link_dir->close();
                        }
                    }

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

$conn->close();
?>

<link rel="stylesheet" href="../../assets/css/admin_panel.css">
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
</style>

<main>
    <div class="form-container">
        <div class="settings-header">
            <a href="../../admin_panel.php?view=movies" class="back-link" title="Wróć do panelu admina"><i class="fa-solid fa-arrow-left"></i></a>
            <h2>Dodaj Film</h2>
        </div>

        <?php
        if (!empty($add_success_message)) echo '<div class="message success-message">' . $add_success_message . '</div>';
        if (!empty($add_error_message)) echo '<div class="message error-message">' . $add_error_message . '</div>';
        ?>

        <form action="add_movie.php" method="POST" class="add-movie-form" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="title">Tytuł filmu</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="release_year">Rok wydania</label>
                    <input type="number" id="release_year" name="release_year" value="<?php echo htmlspecialchars($_POST['release_year'] ?? ''); ?>" min="1800" max="<?php echo date('Y') + 5; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Opis fabuły</label>
                <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Reżyser / Reżyserzy</label>
                    <div class="tag-input-container" data-for="directors-hidden">
                        <input type="text" class="tag-input-field" list="directors-datalist" placeholder="Dodaj reżysera...">
                    </div>
                    <input type="hidden" name="director" id="directors-hidden" value="<?php echo htmlspecialchars($_POST['director'] ?? ''); ?>">
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
                    <input type="hidden" name="genre" id="genres-hidden" value="<?php echo htmlspecialchars($_POST['genre'] ?? ''); ?>">
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
                        <option value="available" <?php if (isset($_POST['status']) && $_POST['status'] === 'available') echo 'selected'; ?>>Dostępny (Available)</option>
                        <option value="upcoming" <?php if (isset($_POST['status']) && $_POST['status'] === 'upcoming') echo 'selected'; ?>>Nadchodzący (Upcoming)</option>
                    </select>
                    <small>Status 'Dostępny' pozwala na ocenianie filmu. 'Nadchodzący' ukrywa te opcje.</small>
                </div>
                <div class="form-group">
                    <label for="poster_file">Plakat filmu</label>
                    <input type="file" id="poster_file" name="poster_file" accept="image/*">
                    <small>Jeśli nie wybierzesz pliku, zostanie użyty domyślny plakat.</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">Dodaj Film</button>
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
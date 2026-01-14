<?php
session_start();
include '../../config/db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'owner'])) {
    header("Location: ../../index.php");
    exit();
}

$base_url = '../../';
include '../../includes/header.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $movie_id = (int)$_POST['movie_id'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $background_image_url = '';
    $display_order = 0;

    if ($is_active) {
        $result = $conn->query("SELECT MAX(display_order) as max_order FROM announcements");
        $max_order = (int)$result->fetch_assoc()['max_order'];
        $display_order = $max_order + 1;
    }

    if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/hero-images/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES['background_image']['name']);
        $target_file = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['background_image']['tmp_name'], $target_file)) {
            $background_image_url = 'uploads/hero-images/' . $file_name;
        } else {
            $message = '<div class="message error-message">Błąd podczas przesyłania pliku.</div>';
        }
    }

    if (empty($message)) {
        $stmt = $conn->prepare("INSERT INTO announcements (movie_id, background_image_url, is_active, display_order) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isii", $movie_id, $background_image_url, $is_active, $display_order);
        if ($stmt->execute()) {
            $message = '<div class="message success-message">Ogłoszenie zostało pomyślnie dodane.</div>';
        } else {
            $message = '<div class="message error-message">Błąd: ' . $stmt->error . '</div>';
        }
        $stmt->close();
    }
}

$movies_list_result = $conn->query("SELECT id, title FROM movies ORDER BY title ASC");
$movies_list = $movies_list_result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<link rel="stylesheet" href="../../assets/css/admin_panel.css">
<link rel="stylesheet" href="../../assets/css/add_movie.css">

<main>
    <div class="form-container">
        <div class="settings-header">
            <a href="../../admin_panel.php?view=announcements" class="back-link" title="Wróć do panelu admina"><i class="fa-solid fa-arrow-left"></i></a>
            <h2>Dodaj Ogłoszenie</h2>
        </div>

        <?php echo $message; ?>

        <form action="add_announcement.php" method="POST" class="add-movie-form" enctype="multipart/form-data">
            <div class="form-group">
                <label for="movie_search">Powiązany film</label>
                <input type="text" id="movie_search" list="movies-datalist" class="tag-input-field" placeholder="Zacznij pisać, aby wyszukać film..." required autocomplete="off">
                <datalist id="movies-datalist">
                    <?php foreach ($movies_list as $movie): ?>
                        <option data-id="<?php echo $movie['id']; ?>" value="<?php echo htmlspecialchars($movie['title']); ?>">
                        <?php endforeach; ?>
                </datalist>
                <input type="hidden" name="movie_id" id="movie_id_hidden" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="background_image">Tło (obraz)</label>
                    <input type="file" id="background_image" name="background_image" accept="image/*" required>
                </div>
            </div>

            <div class="form-group form-group-checkbox">
                <input type="checkbox" id="is_active" name="is_active" value="1" checked>
                <label for="is_active" style="margin: 0 !important;">Aktywne</label>
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">Dodaj Ogłoszenie</button>
            </div>
        </form>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const movieSearchInput = document.getElementById('movie_search');
        const moviesDatalist = document.getElementById('movies-datalist');
        const hiddenMovieIdInput = document.getElementById('movie_id_hidden');

        movieSearchInput.addEventListener('input', function(e) {
            const value = e.target.value;
            const option = Array.from(moviesDatalist.options).find(opt => opt.value === value);

            if (option) {
                hiddenMovieIdInput.value = option.dataset.id;
            } else {
                hiddenMovieIdInput.value = '';
            }
        });
    });
</script>

<?php include '../../includes/footer.php'; ?>

</body>

</html>
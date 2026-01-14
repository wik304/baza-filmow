<?php
session_start();
include '../../config/db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'owner'])) {
    header("Location: ../../index.php");
    exit();
}

$announcement_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($announcement_id === 0) {
    header("Location: ../../admin_panel.php");
    exit();
}

$base_url = '../../';
include '../../includes/header.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $movie_id = (int)$_POST['movie_id'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $background_image_url = $_POST['existing_background_image_url'];

    if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/hero-images/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $file_name = time() . '_' . basename($_FILES['background_image']['name']);
        $target_file = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['background_image']['tmp_name'], $target_file)) {
            if (!empty($background_image_url) && file_exists("../../" . $background_image_url)) {
                // unlink("../../" . $background_image_url);
            }
            $background_image_url = 'uploads/hero-images/' . $file_name;
        } else {
            $message = '<div class="message error-message">Błąd podczas przesyłania nowego pliku.</div>';
        }
    }

    if (empty($message)) {
        $stmt = $conn->prepare("UPDATE announcements SET movie_id = ?, background_image_url = ?, is_active = ? WHERE id = ?");
        $stmt->bind_param("isii", $movie_id, $background_image_url, $is_active, $announcement_id);
        if ($stmt->execute()) {
            $message = '<div class="message success-message">Ogłoszenie zostało pomyślnie zaktualizowane.</div>';
        } else {
            $message = '<div class="message error-message">Błąd: ' . $stmt->error . '</div>';
        }
        $stmt->close();
    }
}

$stmt = $conn->prepare("SELECT * FROM announcements WHERE id = ?");
$stmt->bind_param("i", $announcement_id);
$stmt->execute();
$result = $stmt->get_result();
$announcement = $result->fetch_assoc();
$stmt->close();

if (!$announcement) {
    echo "Nie znaleziono ogłoszenia.";
    exit();
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
            <h2>Edytuj Ogłoszenie</h2>
        </div>

        <?php echo $message; ?>

        <form action="edit_announcement.php?id=<?php echo $announcement_id; ?>" method="POST" class="add-movie-form" enctype="multipart/form-data">
            <div class="form-group">
                <label for="movie_search">Powiązany film</label>
                <input type="text" id="movie_search" list="movies-datalist" class="tag-input-field" placeholder="Zacznij pisać, aby wyszukać film..." required autocomplete="off" value="<?php
                                                                                                                                                                                            foreach ($movies_list as $movie) {
                                                                                                                                                                                                if ($movie['id'] == $announcement['movie_id']) {
                                                                                                                                                                                                    echo htmlspecialchars($movie['title']);
                                                                                                                                                                                                    break;
                                                                                                                                                                                                }
                                                                                                                                                                                            }
                                                                                                                                                                                            ?>">
                <datalist id="movies-datalist">
                    <?php foreach ($movies_list as $movie): ?>
                        <option data-id="<?php echo $movie['id']; ?>" value="<?php echo htmlspecialchars($movie['title']); ?>">
                        <?php endforeach; ?>
                </datalist>
                <input type="hidden" name="movie_id" id="movie_id_hidden" value="<?php echo $announcement['movie_id']; ?>" required>
            </div>

            <div class="form-group">
                <label for="background_image">Zmień tło (obraz)</label>
                <input type="file" id="background_image" name="background_image" accept="image/*">
                <input type="hidden" name="existing_background_image_url" value="<?php echo htmlspecialchars($announcement['background_image_url']); ?>">
                <img src="<?php echo $base_url . htmlspecialchars($announcement['background_image_url']); ?>" alt="Podgląd tła" class="announcement-bg-preview" style="margin-top: 10px; max-width: 400px; height: auto;">
            </div>

            <div class="form-group form-group-checkbox">
                <input type="checkbox" id="is_active" name="is_active" value="1" <?php if ($announcement['is_active']) echo 'checked'; ?>>
                <label for="is_active" style="margin: 0 !important;">Aktywne</label>
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">Zapisz zmiany</button>
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
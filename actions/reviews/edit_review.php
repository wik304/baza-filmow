<?php
session_start();
include '../../config/db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'owner'])) {
    header("Location: ../../index.php");
    exit();
}

$review_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($review_id === 0) {
    header("Location: ../../admin_panel.php?view=opinions");
    exit();
}
$from_view = $_GET['from'] ?? 'opinions';

$base_url = '../../';
include '../../includes/header.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $comment = trim(strip_tags($_POST['comment']));
    $rating = (int)$_POST['rating'];

    $stmt = $conn->prepare("UPDATE ratings SET comment = ?, rating = ? WHERE id = ?");
    $stmt->bind_param("sii", $comment, $rating, $review_id);
    if ($stmt->execute()) {
        $message = '<div class="message success-message">Opinia została pomyślnie zaktualizowana.</div>';
    } else {
        $message = '<div class="message error-message">Błąd: ' . $stmt->error . '</div>';
    }
    $stmt->close();
}

$stmt = $conn->prepare("SELECT r.comment, r.rating, u.username, m.title as movie_title FROM ratings r JOIN users u ON r.user_id = u.id JOIN movies m ON r.movie_id = m.id WHERE r.id = ?");
$stmt->bind_param("i", $review_id);
$stmt->execute();
$review = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$review) {
    echo "Nie znaleziono opinii.";
    exit();
}

$conn->close();
?>

<link rel="stylesheet" href="../../assets/css/admin_panel.css">
<link rel="stylesheet" href="../../assets/css/add_movie.css">

<main>
    <div class="form-container">
        <div class="settings-header">
            <a href="../../admin_panel.php?view=<?php echo urlencode($from_view); ?>" class="back-link" title="Wróć do panelu admina"><i class="fa-solid fa-arrow-left"></i></a>
            <h2>Edytuj Opinię</h2>
        </div>

        <?php echo $message; ?>

        <div style="background: #f9f9f9; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #eee;">
            <p><strong>Użytkownik:</strong> <?php echo htmlspecialchars($review['username']); ?></p>
            <p><strong>Film:</strong> <?php echo htmlspecialchars($review['movie_title']); ?></p>
        </div>

        <form action="edit_review.php?id=<?php echo $review_id; ?>&from=<?php echo urlencode($from_view); ?>" method="POST" class="add-movie-form">
            <div class="form-group">
                <label for="rating">Ocena (1-10)</label>
                <input type="number" id="rating" name="rating" step="1" min="1" max="10" value="<?php echo (int)$review['rating']; ?>" required>
            </div>

            <div class="form-group">
                <label for="comment">Treść opinii</label>
                <textarea id="comment" name="comment" rows="8" required><?php echo htmlspecialchars($review['comment']); ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">Zapisz zmiany</button>
            </div>
        </form>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>

</body>

</html>
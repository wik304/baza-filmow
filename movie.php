<?php
session_start();
include 'db_connect.php';
include 'header.php';

$user_id = $_SESSION['user_id'] ?? null;
$movie_id = (int)($_GET['id'] ?? 0);
$rating_message = '';

if ($movie_id === 0) {
    echo "<main><div class='main-content'><p>Nieprawidłowy adres. Nie znaleziono filmu.</p></div></main>";
    include 'footer.php';
    exit();
}

$user_current_rating = 0.0;
$user_current_comment = '';
if ($user_id) {
    $sql_user_rate = "SELECT rating, comment FROM ratings WHERE user_id = ? AND movie_id = ?";
    $stmt_user_rate = $conn->prepare($sql_user_rate);
    $stmt_user_rate->bind_param("ii", $user_id, $movie_id);
    $stmt_user_rate->execute();
    $result_user_rate = $stmt_user_rate->get_result();
    if ($result_user_rate->num_rows > 0) {
        $user_review = $result_user_rate->fetch_assoc();
        $user_current_rating = (float)($user_review['rating'] ?? 0.0);
        $user_current_comment = $user_review['comment'] ?? '';
    }
    $stmt_user_rate->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $user_id) {
    if (isset($_POST['rating']) || isset($_POST['comment'])) {

        if (isset($_POST['rating']) && !empty($_POST['rating'])) {
            $ocena_z_formularza = (float)$_POST['rating'];
        } else {
            $ocena_z_formularza = $user_current_rating;
        }

        $komentarz_z_formularza = trim($_POST['comment'] ?? '');

        $sql_rate = "INSERT INTO ratings (user_id, movie_id, rating, comment) 
                     VALUES (?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE rating = ?, comment = ?";

        $stmt_rate = $conn->prepare($sql_rate);
        $stmt_rate->bind_param(
            "iiddss",
            $user_id,
            $movie_id,
            $ocena_z_formularza,
            $komentarz_z_formularza,
            $ocena_z_formularza,
            $komentarz_z_formularza
        );

        if ($stmt_rate->execute()) {
            if ($ocena_z_formularza > 0) {
                $sql_avg = "SELECT AVG(rating) AS average_rating FROM ratings WHERE movie_id = ? AND rating > 0";
                $stmt_avg = $conn->prepare($sql_avg);
                $stmt_avg->bind_param("i", $movie_id);
                $stmt_avg->execute();
                $result_avg = $stmt_avg->get_result();
                $new_average = $result_avg->fetch_assoc()['average_rating'];
                $stmt_avg->close();

                $sql_update_movie = "UPDATE movies SET user_rating = ? WHERE id = ?";
                $stmt_update = $conn->prepare($sql_update_movie);
                $stmt_update->bind_param("di", $new_average, $movie_id);
                $stmt_update->execute();
                $stmt_update->close();
            }
            $rating_message = "Twoja recenzja została zapisana!";

            $user_current_rating = $ocena_z_formularza;
            $user_current_comment = $komentarz_z_formularza;
        } else {
            $rating_message = "Wystąpił błąd. Spróbuj ponownie.";
        }
        $stmt_rate->close();
    }
}


$sql = "SELECT * FROM movies WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();

if (!$movie) {
    echo "<main><div class='main-content'><p>Film o podanym ID nie istnieje w bazie danych.</p></div></main>";
    $stmt->close();
    $conn->close();
    include 'footer.php';
    exit();
}
$stmt->close();

$all_reviews = [];
$sql_all_reviews = "SELECT r.rating, r.comment, r.created_at, u.username 
                    FROM ratings r
                    JOIN users u ON r.user_id = u.id
                    WHERE r.movie_id = ? AND r.comment IS NOT NULL AND r.comment != ''
                    ORDER BY r.created_at DESC";
$stmt_all_reviews = $conn->prepare($sql_all_reviews);
$stmt_all_reviews->bind_param("i", $movie_id);
$stmt_all_reviews->execute();
$result_all_reviews = $stmt_all_reviews->get_result();
if ($result_all_reviews->num_rows > 0) {
    while ($row = $result_all_reviews->fetch_assoc()) {
        $all_reviews[] = $row;
    }
}
$stmt_all_reviews->close();
?>

<style>
    .movie-hero {
        position: relative;
        width: 100%;
        min-height: 500px;
        padding: 4rem 0;
        display: flex;
        align-items: center;
        color: #ffffff;
        overflow: hidden;
    }

    .hero-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        filter: blur(20px) brightness(0.4);
        transform: scale(1.1);
        z-index: 1;
    }

    .hero-content {
        position: relative;
        z-index: 2;
        display: flex;
        gap: 2.5rem;
        align-items: center;
    }

    .hero-poster img {
        width: 300px;
        height: auto;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        border: 2px solid rgba(255, 255, 255, 0.1);
    }

    .hero-info {
        flex: 1;
        color: #ffffff;
    }

    .hero-info h1 {
        font-size: 3rem;
        font-weight: 700;
        margin-top: 0;
        margin-bottom: 0.5rem;
        color: #ffffff !important;
        text-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
    }

    .meta-info {
        display: flex;
        gap: 1.5rem;
        font-size: 1rem;
        color: #eee;
        margin-bottom: 1.5rem;
    }

    .meta-info span {
        font-weight: 500;
    }

    .hero-info .ratings {
        justify-content: flex-start;
        gap: 2.5rem;
        margin-bottom: 2rem;
    }

    .hero-info .rating-item span {
        font-size: 0.9rem;
        color: #ccc;
    }

    .hero-info .rating-item strong {
        font-size: 1.2rem;
        color: #ffffff;
    }

    .plot-summary h3 {
        font-size: 1.3rem;
        font-weight: 600;
        margin-top: 0;
        margin-bottom: 0.5rem;
        border-bottom: 2px solid #0ccb4a;
        padding-bottom: 5px;
        display: inline-block;
    }

    .plot-summary p {
        font-size: 1rem;
        line-height: 1.7;
        color: #ffffff !important;
    }

    @media (max-width: 768px) {
        .hero-content {
            flex-direction: column;
            text-align: center;
        }

        .hero-poster img {
            width: 70%;
            max-width: 300px;
        }

        .meta-info,
        .hero-info .ratings {
            justify-content: center;
        }

        .hero-info h1 {
            font-size: 2.2rem;
        }
    }

    .rating-section {
        padding: 2.5rem 0;
    }

    .rating-box {
        max-width: 450px;
        margin: 0;
        padding: 1.5rem;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        text-align: left;
        background-color: #ffffff;
    }

    .rating-box h3 {
        font-size: 1.5rem;
        color: #2c2c2c;
        margin-top: 0;
        margin-bottom: 1.5rem;
        text-align: left;
    }

    .star-rating {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
        gap: 0.2rem;
        margin-bottom: 0.5rem;
    }

    .star-rating input[type="radio"] {
        display: none;
    }

    .star-rating label {
        font-size: 2rem;
        color: #ccc;
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .star-rating:not(:hover) input[type="radio"]:checked~label,
    .star-rating:hover input[type="radio"]:hover~label,
    .star-rating input[type="radio"]:checked~label,
    .star-rating label:hover,
    .star-rating label:hover~label {
        color: #f39c12;
    }

    .rating-form .form-group {
        text-align: left;
        margin-bottom: 1rem;
    }

    .rating-form label {
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #555;
        display: block;
    }

    .rating-form textarea {
        width: 100%;
        min-height: 100px;
        padding: 10px;
        font-family: 'Inter', sans-serif;
        font-size: 1rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        resize: vertical;
    }

    .rating-form button {
        margin-top: 1rem;
    }

    .login-prompt {
        text-align: left;
        font-size: 1.1rem;
        color: #555;
    }

    .login-prompt a {
        color: #0ccb4a;
        font-weight: 600;
        text-decoration: none;
    }

    .login-prompt a:hover {
        text-decoration: underline;
    }

    .rating-message {
        text-align: left;
        font-size: 1.1rem;
        font-weight: 600;
        color: #0ccb4a;
        margin-bottom: 1rem;
    }

    .reviews-section {
        padding: 3rem 0;
    }

    .reviews-list h3 {
        font-size: 1.8rem;
        color: #2c2c2c;
        text-align: center;
        margin-top: 0;
        margin-bottom: 2rem;
    }

    .review-items-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .review-item {
        background-color: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .review-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .review-author {
        font-weight: 700;
        font-size: 1.1rem;
        color: #2c2c2c;
    }

    .review-rating-simple {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        font-weight: 700;
        font-size: 1.1rem;
    }

    .fa-solid.fa-star {
        color: #f39c12;
    }

    .review-rating-simple i {
        font-size: 0.9rem;
    }

    .review-stars {
        display: none;
    }

    .review-comment {
        font-size: 1rem;
        line-height: 1.6;
        color: #333;
        flex-grow: 1;
        margin-bottom: 1rem;
    }

    .review-footer {
        margin-top: auto;
    }

    .review-date {
        font-size: 0.85rem;
        color: #777;
        text-align: right;
    }
</style>

<main>
    <section class="movie-hero">
        <div class="hero-background" style="background-image: url('<?php echo htmlspecialchars($movie['poster_url']); ?>');"></div>
        <div class="main-content">
            <div class="hero-content">
                <div class="hero-poster">
                    <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="Plakat filmu <?php echo htmlspecialchars($movie['title']); ?>">
                </div>
                <div class="hero-info">
                    <h1><?php echo htmlspecialchars($movie['title']); ?></h1>
                    <div class="meta-info">
                        <span>Rok: <strong><?php echo htmlspecialchars($movie['release_year']); ?></strong></span>
                        <span>Reżyser: <strong><?php echo htmlspecialchars($movie['director']); ?></strong></span>
                        <span>Gatunek: <strong><?php echo htmlspecialchars($movie['genre'] ?? 'Brak'); ?></strong></span>
                    </div>
                    <div class="ratings">
                        <div class="rating-item">
                            <span>Użytkownicy</span>
                            <strong><?php echo number_format((float)($movie['user_rating'] ?? 0), 0); ?>/10</strong>
                        </div>
                        <div class="rating-item">
                            <span>Krytycy</span>
                            <strong><?php echo number_format((float)($movie['critic_rating'] ?? 0), 0); ?>/10</strong>
                        </div>
                    </div>
                    <div class="plot-summary">
                        <h3>Opis fabuły</h3>
                        <p><?php echo htmlspecialchars($movie['description']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="rating-section">
        <div class="main-content">
            <div class="rating-box">
                <?php if ($user_id): ?>
                    <h3>Twoja recenzja</h3>
                    <?php if ($rating_message): ?>
                        <p class="rating-message"><?php echo $rating_message; ?></p>
                    <?php endif; ?>

                    <form class="rating-form" action="movie.php?id=<?php echo $movie_id; ?>" method="POST">
                        <div class="form-group">
                            <label for="rating-stars">Twoja ocena:</label>
                            <div class="star-rating" id="rating-stars">
                                <?php for ($i = 10; $i >= 1; $i--):
                                    $checked = (round($user_current_rating) == $i) ? 'checked' : '';
                                ?>
                                    <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" <?php echo $checked; ?>>
                                    <label for="star<?php echo $i; ?>" title="<?php echo $i; ?> gwiazdek">&#9733;</label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="comment">Twój komentarz (opcjonalnie):</label>
                            <textarea id="comment" name="comment" placeholder="Napisz, co myślisz o tym filmie..."><?php echo htmlspecialchars($user_current_comment); ?></textarea>
                        </div>
                        <button type="submit" class="submit-btn">Zapisz recenzję</button>
                    </form>
                <?php else: ?>
                    <div class="login-prompt">
                        <p><a href="login.php?redirect=movie.php?id=<?php echo $movie_id; ?>">Zaloguj się</a>, aby dodać recenzję.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="reviews-section">
        <div class="main-content">
            <div class="reviews-list">
                <h3>Recenzje o filmie <?php echo htmlspecialchars($movie['title']); ?> (<?php echo count($all_reviews); ?>)</h3>

                <?php if (!empty($all_reviews)): ?>
                    <div class="review-items-container">
                        <?php foreach ($all_reviews as $review): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <span class="review-author"><?php echo htmlspecialchars($review['username']); ?></span>
                                    <?php if ($review['rating'] > 0):
                                    ?>
                                        <div class="review-rating-dot">&middot;</div>
                                        <div class="review-rating-simple">
                                            <span><?php echo number_format((float)$review['rating'], 0); ?></span>
                                            <i class="fa-solid fa-star"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <p class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>

                                <div class="review-footer">
                                    <span class="review-date">
                                        <?php
                                        $date = new DateTime($review['created_at']);
                                        echo $date->format('d.m.Y H:i');
                                        ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: #555;">Brak recenzji dla tego filmu. Bądź pierwszy!</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

</main>

<?php
$conn->close();
include 'footer.php';
?>
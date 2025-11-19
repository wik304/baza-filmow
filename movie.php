<?php
session_start();
include 'db_connect.php';
include 'header.php';
include 'functions.php';

$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_role'] ?? 'user';
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

        if (isset($_POST['rating']) && (float)$_POST['rating'] === 0.0) {
            $sql_delete = "DELETE FROM ratings WHERE user_id = ? AND movie_id = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("ii", $user_id, $movie_id);
            if ($stmt_delete->execute()) {
                $rating_message = "Twoja ocena została usunięta.";
            } else {
                $rating_message = "Wystąpił błąd podczas usuwania oceny.";
            }
            $stmt_delete->close();
            $user_current_rating = 0.0;
            $user_current_comment = '';
            update_movie_popularity($movie_id, $conn);
        } else {

            if (isset($_POST['rating']) && !empty($_POST['rating'])) {
                $rating_from_form = (float)$_POST['rating'];
            } else {
                $rating_from_form = $user_current_rating;
            }
            $comment_from_form = isset($_POST['comment']) ? trim($_POST['comment']) : $user_current_comment;

            $rating_type = ($user_role === 'critic') ? 'critic' : 'user';

            $sql_rate = "INSERT INTO ratings (user_id, movie_id, rating, comment, rating_type) 
                     VALUES (?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE rating = ?, comment = ?, rating_type = ?";

            $stmt_rate = $conn->prepare($sql_rate);
            $stmt_rate->bind_param(
                "iidssdss",
                $user_id,
                $movie_id,
                $rating_from_form,
                $comment_from_form,
                $rating_type,
                $rating_from_form,
                $comment_from_form,
                $rating_type
            );

            if ($stmt_rate->execute()) {

                $rating_message = "Twoja recenzja została zapisana!";

                $user_current_rating = $rating_from_form;
                $user_current_comment = $comment_from_form;

                $sql_refresh = "SELECT * FROM movies WHERE id = ?";
                $stmt_refresh = $conn->prepare($sql_refresh);
                $stmt_refresh->bind_param("i", $movie_id);
                $stmt_refresh->execute();
                $movie = $stmt_refresh->get_result()->fetch_assoc();
                $stmt_refresh->close();
            } else {
                $rating_message = "Wystąpił błąd. Spróbuj ponownie.";
            }
            $stmt_rate->close();
            update_movie_popularity($movie_id, $conn);

            // Sprawdź osiągnięcia
            check_and_grant_achievements($user_id, 'rate_movie', $conn);
            if (!empty($comment_from_form)) {
                check_and_grant_achievements($user_id, 'write_review', $conn);
            }
        }
    }
}


$all_reviews = [];
$sql_all_reviews = "SELECT r.id AS rating_id, r.rating, r.comment, r.created_at, u.username,
                           (SELECT COUNT(*) FROM user_movie_lists uml WHERE uml.user_id = r.user_id AND uml.movie_id = ? AND uml.list_type = 'favorite') AS is_favorite,
                           (SELECT COUNT(*) FROM review_likes rl WHERE rl.rating_id = r.id) AS like_count,
                           (SELECT COUNT(*) FROM review_likes rl WHERE rl.rating_id = r.id AND rl.user_id = ?) AS user_liked
                    FROM ratings r
                    JOIN users u ON r.user_id = u.id
                    WHERE r.movie_id = ? AND r.rating_type = 'user' AND r.comment IS NOT NULL AND r.comment != ''
                    ORDER BY r.created_at DESC";
$stmt_all_reviews = $conn->prepare($sql_all_reviews);
if ($user_id) {
    $stmt_all_reviews->bind_param("iii", $movie_id, $user_id, $movie_id);
} else {
    $stmt_all_reviews->bind_param("iii", $movie_id, $movie_id, $movie_id);
}
$stmt_all_reviews->execute();
$result_all_reviews = $stmt_all_reviews->get_result();
if ($result_all_reviews->num_rows > 0) {
    while ($row = $result_all_reviews->fetch_assoc()) {
        $all_reviews[] = $row;
    }
}
$stmt_all_reviews->close();

$critic_reviews = [];
$sql_critic_reviews = "SELECT r.rating, r.comment, u.username, u.id AS user_id, u.critic_description,
                              (SELECT COUNT(*) FROM followers WHERE follower_id = ? AND followed_id = u.id) as is_followed
                       FROM ratings r
                       JOIN users u ON r.user_id = u.id
                       WHERE r.movie_id = ? AND r.rating_type = 'critic' AND r.comment IS NOT NULL AND r.comment != ''
                       ORDER BY r.created_at DESC LIMIT 5";
$stmt_critic_reviews = $conn->prepare($sql_critic_reviews);
$stmt_critic_reviews->bind_param("ii", $user_id, $movie_id);
$stmt_critic_reviews->execute();
$result_critic_reviews = $stmt_critic_reviews->get_result();
if ($result_critic_reviews->num_rows > 0) {
    while ($row = $result_critic_reviews->fetch_assoc()) {
        $critic_reviews[] = $row;
    }
}
?>
<?php
$sql = "SELECT m.*,
               (SELECT AVG(rating) FROM ratings WHERE movie_id = m.id AND rating > 0 AND rating_type = 'user') AS dynamic_user_rating,
               (SELECT AVG(rating) FROM ratings WHERE movie_id = m.id AND rating > 0 AND rating_type = 'critic') AS dynamic_critic_rating,
               GROUP_CONCAT(DISTINCT d.full_name SEPARATOR ', ') AS director,
               GROUP_CONCAT(DISTINCT g.name SEPARATOR ', ') AS genre
        FROM movies m
        LEFT JOIN movie_directors md ON m.id = md.movie_id
        LEFT JOIN directors d ON md.director_id = d.director_id
        LEFT JOIN movie_genres mg ON m.id = mg.movie_id
        LEFT JOIN genres g ON mg.genre_id = g.genre_id
        WHERE m.id = ?
        GROUP BY m.id";

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

$movie['user_rating'] = $movie['dynamic_user_rating'] ?? 0.0;
$movie['critic_rating'] = $movie['dynamic_critic_rating'] ?? 0.0;
?>
<?php
$is_favorite = false;
$is_on_watchlist = false;
if ($user_id) {
    $sql_lists = "SELECT list_type FROM user_movie_lists WHERE user_id = ? AND movie_id = ?";
    $stmt_lists = $conn->prepare($sql_lists);
    $stmt_lists->bind_param("ii", $user_id, $movie_id);
    $stmt_lists->execute();
    $result_lists = $stmt_lists->get_result();
    while ($row = $result_lists->fetch_assoc()) {
        if ($row['list_type'] === 'favorite') $is_favorite = true;
        if ($row['list_type'] === 'watchlist') $is_on_watchlist = true;
    }
    $stmt_lists->close();
} else {
    // Sprawdź sesję dla gości
    if (isset($_SESSION['guest_lists']['favorite']) && in_array($movie_id, $_SESSION['guest_lists']['favorite'])) {
        $is_favorite = true;
    }
    if (isset($_SESSION['guest_lists']['watchlist']) && in_array($movie_id, $_SESSION['guest_lists']['watchlist'])) {
        $is_on_watchlist = true;
    }
}
?>

<style>
    .hero-wrapper {
        position: relative;
        margin-bottom: 4rem;
    }

    .movie-hero {
        position: relative;
        width: 100%;
        min-height: 500px;
        padding: 4rem 0 3rem;
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

    .plot-box {
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translate(-50%, 50%);
        background-color: #ffffff;
        color: #2c2c2c;
        border-radius: 4px;
        padding: 1rem 1rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: 1px solid #e0e0e0;
        text-align: left;
        z-index: 5;
    }

    .plot-box .box-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }

    .box-header-left {
        display: flex;
        align-items: center;
        margin-bottom: 0;
    }

    .plot-box .box-avatar {
        width: 48px;
        height: 48px;
        border-radius: 6px;
        object-fit: cover;
        flex: 0 0 48px;
        margin-right: 0.75rem
    }

    .plot-box .box-rate-text {
        color: #555;
        font-size: 14px;
        font-weight: 600;
    }

    .remove-rating-btn {
        background: none;
        border: none;
        color: #999;
        font-size: 1.5rem;
        font-weight: 600;
        line-height: 1;
        padding: 0 5px;
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .remove-rating-btn:hover {
        color: #555;
    }

    .remove-rating-btn.hidden {
        display: none;
    }

    .box-header-actions {
        display: flex;
        gap: 0.5rem;
    }

    .action-btn {
        background: none;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #555;
        font-size: 1rem;
        transition: all 0.2s ease;
    }

    .action-btn:hover {
        background-color: #f5f5f5;
        border-color: #ccc;
        transform: scale(1.1);
    }

    .action-btn.btn-favorite:hover {
        color: red;
    }

    .action-btn.btn-watchlist:hover {
        color: #0ccb4a;
        /* Zielony */
    }

    /* Styl dla aktywnego przycisku (gdy film jest na liście) */
    .action-btn.active.btn-favorite {
        background-color: #ffebee;
        color: red;
    }

    .action-btn.active.btn-watchlist {
        background-color: #e8f5e9;
        color: #0ccb4a;
    }

    .action-btn .fa-eye {
        color: inherit;
    }

    .star-rating-form {
        margin-top: 1rem;
    }

    .star-rating-container {
        display: flex;
        flex-direction: row-reverse;
        justify-content: center;
        gap: 2px;
    }

    .star-rating-container input[type="radio"] {
        display: none;
    }

    .star-rating-container label {
        font-size: 1.5rem;
        color: #ccc;
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .star-rating-container input[type="radio"]:checked~label {
        color: #f39c12;
    }

    .star-rating-container:hover label {
        color: #ccc;
    }

    .star-rating-container label:hover,
    .star-rating-container label:hover~label {
        color: #f39c12;
    }

    .star-rating-container label:hover {
        transform: scale(1.1);
    }

    .fake-textarea {
        cursor: pointer;
    }

    .comment-section {
        display: none;
        margin-top: 1rem;
        animation: fadeIn 0.5s ease;
    }

    .comment-section.visible {
        display: block;
    }

    .fake-textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        margin-bottom: 0.5rem;
        background-color: #f9f9f9;
        color: #777;
        transition: background-color 0.2s ease;
    }

    .fake-textarea:hover {
        background-color: #f0f0f0;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .comment-textarea {
        width: 100%;
        height: 90px;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        resize: none;
        overflow-y: auto;
        margin-bottom: 0.5rem;
    }

    .comment-textarea:focus {
        outline: none;
        border-color: #0ccb4a;
    }

    .modal-movie-info {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .modal-movie-poster {
        width: 80px;
        height: 120px;
        object-fit: cover;
        border-radius: 4px;
    }

    .modal-movie-details {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex: 1;
    }

    .modal-rating-display {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    #modal-rating-number {
        font-size: 16px;
        font-weight: 700;
        color: #2c2c2c;
    }

    #modal-user-rating {
        flex-direction: row;
    }

    #modal-user-rating .fa-star {
        color: #ccc;
    }

    #modal-user-rating .fa-star.rated {
        color: #f39c12;
    }

    .modal-review-date {
        font-size: 0.8rem;
        color: #777;
        margin-top: 0.25rem;
    }

    body.modal-open {
        overflow: hidden;
    }

    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }

    .modal-overlay:not(.hidden) {
        opacity: 1;
        visibility: visible;
    }

    .modal-box {
        background: #ffffff;
        padding: 1rem;
        border-radius: 8px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        width: 90%;
        max-width: 500px;
        position: relative;
        transform: scale(0.95);
        transition: transform 0.3s ease;
    }

    .modal-overlay:not(.hidden) .modal-box {
        transform: scale(1);
    }

    .modal-close-btn {
        position: absolute;
        top: 10px;
        right: 15px;
        background: none;
        border: none;
        font-size: 2rem;
        color: #999;
        cursor: pointer;
        line-height: 1;
    }

    .modal-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1rem;
    }

    .char-counter {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.7rem;
        color: #777;
        font-weight: 500;
    }

    .char-counter.hidden {
        visibility: hidden;
    }

    #save-comment-btn {
        padding: 8px 16px;
        font-size: 0.9rem;
        width: auto;
    }

    /* Styl dla nieaktywnego przycisku */
    #save-comment-btn:disabled {
        background-color: transparent;
        color: #aaa;
        border: 1px solid #e0e0e0;
        cursor: not-allowed;
    }


    @media (max-width: 768px) {
        .hero-wrapper {
            margin-bottom: 0;
        }

        .plot-box {
            position: relative;
            left: auto;
            bottom: auto;
            transform: none;
            margin: -2rem auto 2rem auto;
            width: calc(100% - 2rem);
        }
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

        .hero-info {
            text-align: center;
        }

        .hero-info h1 {
            font-size: 2.2rem;
        }
    }

    .rating-section {
        display: none;
    }

    .reviews-section {
        padding: 3rem 0;
        padding-top: 5rem;
    }
</style>

<main>
    <div class="hero-wrapper">
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
                                <strong><?php echo number_format((float)($movie['user_rating'] ?? 0), 1); ?>/10</strong>
                            </div>
                            <div class="rating-item">
                                <span>Krytycy</span>
                                <strong><?php echo number_format((float)($movie['critic_rating'] ?? 0), 1); ?>/10</strong>
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

        <div class="plot-box">
            <div class="box-header">
                <div class="box-header-left">
                    <img src="uploads/avatar-default.png" alt="avatar" class="box-avatar">
                    <span class="box-rate-text">Oceń film</span>
                    <button type="button" class="remove-rating-btn <?php if (!($user_id && $user_current_rating > 0)) echo 'hidden'; ?>" id="remove-rating-btn" title="Usuń ocenę">&times;</button>
                </div>
                <div class="box-header-actions">
                    <button class="action-btn btn-favorite <?php if ($is_favorite) echo 'active'; ?>" title="Dodaj do ulubionych" data-list-type="favorite">
                        <i class="fa-solid fa-heart"></i>
                    </button>
                    <button class="action-btn btn-watchlist <?php if ($is_on_watchlist) echo 'active'; ?>" title="Chcę obejrzeć" data-list-type="watchlist">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>
            <form action="movie.php?id=<?php echo $movie_id; ?>" method="POST" class="star-rating-form" id="rating-form">
                <div class="star-rating-container">
                    <?php for ($i = 10; $i >= 1; $i--): ?>
                        <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" <?php if ($user_current_rating == $i) echo 'checked'; ?>>
                        <label for="star<?php echo $i; ?>"><i class="fa-solid fa-star"></i></label>
                    <?php endfor; ?>
                </div>
                <div class="comment-section <?php if ($user_current_rating > 0) echo 'visible'; ?>" id="comment-section">
                    <div class="fake-textarea" id="open-modal-trigger" role="button" tabindex="0">Podziel się swoją opinią...</div>
                </div>
            </form>
        </div>
    </div>

    <section class="reviews-section">
        <div class="main-content">
            <div class="reviews-list">
                <div style="text-align: center;">
                    <h3>Opinie o filmie <?php echo htmlspecialchars($movie['title']); ?></h3>
                </div>

                <?php
                $total_reviews_count = count($all_reviews);
                $reviews_to_display = array_slice($all_reviews, 0, 3);
                ?>

                <?php if (!empty($reviews_to_display)): ?>
                    <div class="review-items-container">
                        <?php foreach ($reviews_to_display as $review): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <img src="uploads/avatar-default.png" alt="Avatar" class="review-avatar">
                                    <span class="review-author"><?php echo htmlspecialchars($review['username']); ?></span>
                                    <?php if ($review['rating'] > 0): ?>
                                        <div class="review-rating-dot">&middot;</div>
                                        <div class="review-rating-simple">
                                            <span><?php echo number_format((float)$review['rating'], 0); ?></span>
                                            <i class="fa-solid fa-star"></i>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($review['is_favorite']): ?>
                                        <div class="review-rating-dot">&middot;</div>
                                        <i class="fa-solid fa-heart review-favorite-icon" title="Ulubiony film użytkownika"></i>
                                    <?php endif; ?>
                                </div>
                                <p class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div class="review-actions">
                                        <div class="like-btn <?php if ($review['user_liked']) echo 'liked'; ?>" data-rating-id="<?php echo $review['rating_id']; ?>" role="button" tabindex="0">
                                            <span class="like-text">Lubię to!</span>
                                            <span class="like-count"><?php echo $review['like_count']; ?></span>
                                            <i class="fa-solid fa-thumbs-up"></i>
                                        </div>
                                    </div>
                                    <div class="review-footer">
                                        <span class="review-date">
                                            <?php $date = new DateTime($review['created_at']);
                                            echo $date->format('d.m.Y H:i'); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($total_reviews_count > 3): ?>
                        <div class="see-more-reviews">
                            <a href="all_reviews.php?movie_id=<?php echo $movie_id; ?>" class="see-more-reviews-link">
                                <span>Zobacz więcej opinii</span>
                                <span class="review-count-badge"><?php echo $total_reviews_count; ?></span>
                            </a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p style="color: #555;">Brak opinii dla tego filmu. Bądź pierwszy!</p>
                <?php endif; ?>
            </div>
            <section class="critics-reviews-section">
                <div style="text-align: left;">
                    <h3>Recenzje krytyków</h3>
                </div>
                <!-- Wrapper, który obejmuje slider i strzałki -->
                <div class="simple-slider-wrapper">

                    <!-- Przycisk "Poprzedni" -->
                    <button class="slider-arrow slider-arrow-prev" aria-label="Poprzedni slajd" disabled>&lt;</button>

                    <!-- Kontener slidera, który będzie przewijany -->
                    <div class="simple-slider-container">
                        <div class="simple-slider-track">
                            <?php if (!empty($critic_reviews)): ?>
                                <?php foreach ($critic_reviews as $critic_review): ?>
                                    <div class="simple-slider-item">
                                        <div class="critic-review-info-box">
                                            <a href="profile.php?id=<?php echo $critic_review['user_id']; ?>">
                                                <img src="uploads/avatar-default.png" alt="critic-avatar" class="critic-box-avatar">
                                            </a>
                                            <div class="critic-review-content">
                                                <div class="critic-review-author-name-box">
                                                    <a href="profile.php?id=<?php echo $critic_review['user_id']; ?>" style="text-decoration: none;">
                                                        <span class="critic-review-author"><?php echo htmlspecialchars($critic_review['username']); ?></span>
                                                    </a>
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <div class="critic-review-description-box">
                                                    <p class="critic-review-description">
                                                        <?php echo !empty($critic_review['critic_description']) ? htmlspecialchars($critic_review['critic_description']) : 'Krytyk filmowy'; ?>
                                                    </p>
                                                </div>
                                                <div style="display: flex;">
                                                    <?php if ($user_id && $user_id != $critic_review['user_id']): ?>
                                                        <button class="critic-review-follow-button <?php if ($critic_review['is_followed']) echo 'followed'; ?>" data-followed-id="<?php echo $critic_review['user_id']; ?>">
                                                            <?php echo $critic_review['is_followed'] ? 'Obserwujesz' : 'Obserwuj'; ?>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="critic-review-rating-box">
                                            <span class="critic-review-star-number"><?php echo number_format((float)$critic_review['rating'], 1); ?></span>
                                            <div>
                                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                                    <?php if ($i <= (int)$critic_review['rating']): ?>
                                                        <i class="fa-solid fa-star critic-review-filled"></i>
                                                    <?php else: ?>
                                                        <i class="fa-solid fa-star critic-review-empty"></i>
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <div class="critic-review-description">
                                            <span><?php echo htmlspecialchars($critic_review['comment']); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="color: #555;">Brak recenzji krytyków dla tego filmu.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Przycisk "Następny" -->
                    <button class="slider-arrow slider-arrow-next" aria-label="Następny slajd">&gt;</button>

                </div>
            </section>
        </div>
    </section>
</main>

<div id="review-modal-overlay" class="modal-overlay hidden">
    <div id="review-modal" class="modal-box">
        <button id="close-modal-btn" class="modal-close-btn" title="Zamknij">&times;</button>
        <h3 style="margin-bottom: 1rem;">Dodaj opinię</h3>
        <div class="modal-movie-info">
            <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="Plakat filmu" class="modal-movie-poster">
            <div class="modal-movie-details">
                <div>
                    <h4 style="margin: 0; font-size: 1.2rem;"><?php echo htmlspecialchars($movie['title']); ?></h4>
                    <p class="modal-review-date" style="margin: 0.25rem 0 0;"><?php echo htmlspecialchars($movie['release_year']); ?></p>
                </div>
                <div class="modal-rating-display">
                    <strong id="modal-rating-number"></strong>
                    <div id="modal-user-rating" class="star-rating-container" style="justify-content: flex-start; font-size: 1rem; gap: 2px;"></div>
                </div>
            </div>
        </div>
        <form id="modal-review-form">
            <textarea id="real-comment-textarea" name="comment" class="comment-textarea" placeholder="Podziel się swoją opinią..."></textarea>
            <div class="modal-actions">
                <div id="char-counter" class="char-counter hidden">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span id="chars-left">1000</span>
                </div>
                <button type="button" id="save-comment-btn" class="submit-btn" disabled>Zapisz</button>
            </div>
        </form>
    </div>
</div>

<?php
$conn->close();
include 'footer.php';
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const openModalTrigger = document.getElementById('open-modal-trigger');
        const modalOverlay = document.getElementById('review-modal-overlay');
        const closeModalBtn = document.getElementById('close-modal-btn');
        const saveCommentBtn = document.getElementById('save-comment-btn');
        const realCommentTextarea = document.getElementById('real-comment-textarea');
        let userCurrentComment = <?php echo json_encode($user_current_comment); ?>;

        const ratingForm = document.getElementById('rating-form');
        const starInputs = ratingForm.querySelectorAll('input[name="rating"]');
        const commentSection = document.getElementById('comment-section');
        const removeRatingBtn = document.getElementById('remove-rating-btn');
        const actionButtons = document.querySelectorAll('.action-btn[data-list-type]');
        const likeButtons = document.querySelectorAll('.like-btn');
        const followButtons = document.querySelectorAll('.critic-review-follow-button');

        starInputs.forEach(input => {
            input.addEventListener('change', function() {
                commentSection.classList.add('visible');

                removeRatingBtn.classList.remove('hidden');
            });
        });

        if (removeRatingBtn) {
            removeRatingBtn.addEventListener('click', function() {
                const checkedStar = ratingForm.querySelector('input[name="rating"]:checked');
                if (checkedStar) {
                    checkedStar.checked = false;
                }

                commentSection.classList.remove('visible');
                removeRatingBtn.classList.add('hidden');

                updateRatingText();

                const formData = new FormData();
                formData.append('rating', '0');
                formData.append('comment', '');

                fetch(ratingForm.action, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            console.error('Błąd podczas usuwania oceny.');
                        }
                    })
                    .catch(error => {
                        console.error('Błąd sieci:', error);
                    });
            });
        }

        function openModal() {
            updateCharCounter(); // Zaktualizuj licznik i przycisk przy otwarciu
            document.body.classList.add('modal-open'); // Blokuj scrollowanie tła
            realCommentTextarea.value = userCurrentComment; // Ustaw aktualny komentarz w polu

            const modalRatingContainer = document.getElementById('modal-user-rating');
            const currentRatingInput = ratingForm.querySelector('input[name="rating"]:checked');
            const modalRatingNumber = document.getElementById('modal-rating-number');
            const ratingValue = currentRatingInput ? parseInt(currentRatingInput.value) : 0;

            modalRatingContainer.innerHTML = ''; // Wyczyść poprzednie gwiazdki
            modalRatingNumber.textContent = ratingValue > 0 ? `${ratingValue}` : '';

            for (let i = 1; i <= 10; i++) {
                const starIcon = document.createElement('i');
                starIcon.className = `fa-solid fa-star ${i <= ratingValue ? 'rated' : ''}`;
                starIcon.style.color = i <= ratingValue ? '#f39c12' : '#ccc';
                modalRatingContainer.appendChild(starIcon);
            }

            modalOverlay.classList.remove('hidden');
        }

        function closeModal() {
            document.body.classList.remove('modal-open'); // Odblokuj scrollowanie tła
            modalOverlay.classList.add('hidden');
        }

        openModalTrigger.addEventListener('click', openModal);
        closeModalBtn.addEventListener('click', closeModal);
        modalOverlay.addEventListener('click', function(event) {
            if (event.target === modalOverlay) {
                closeModal();
            }
        });

        saveCommentBtn.addEventListener('click', function() {
            const newComment = realCommentTextarea.value;
            userCurrentComment = newComment; // Zaktualizuj zmienną JS

            const formData = new FormData();
            formData.append('comment', newComment);

            const currentRatingInput = ratingForm.querySelector('input[name="rating"]:checked');
            if (currentRatingInput) {
                formData.append('rating', currentRatingInput.value);
            } else {
                console.error("Brak oceny do zapisania.");
                return;
            }

            fetch(ratingForm.action, {
                method: 'POST',
                body: formData
            }).then(response => {
                if (response.ok) {
                    window.location.reload();
                }
            });

            closeModal(); // Zamknij okno po zapisie
        });

        actionButtons.forEach(button => {
            button.addEventListener('click', function() {
                const listType = this.dataset.listType;
                const movieId = <?php echo $movie_id; ?>;
                const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

                this.classList.toggle('active');

                const formData = new FormData();
                formData.append('movie_id', movieId);
                formData.append('list_type', listType);

                fetch('update_list.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            if (!isLoggedIn && data.action === 'added') {
                                setTimeout(() => {
                                    window.location.href = 'login.php?notice=guest_action';
                                }, 200);
                            }
                        } else {
                            this.classList.toggle('active');
                            alert(data.message || 'Wystąpił błąd.');
                        }
                    }).catch(error => {
                        console.error('Błąd sieci:', error);
                    });
            });
        });




        likeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const ratingId = this.dataset.ratingId;
                const likeCountSpan = this.querySelector('.like-count');

                const formData = new FormData();
                formData.append('rating_id', ratingId);

                fetch('like_review.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            likeCountSpan.textContent = data.like_count;
                            if (data.action === 'liked') {
                                this.classList.add('liked');
                            } else {
                                this.classList.remove('liked');
                            }
                        } else {
                            alert(data.message || 'Wystąpił błąd.');
                        }
                    }).catch(error => console.error('Błąd sieci:', error));
            });
        });

        followButtons.forEach(button => {
            button.addEventListener('click', function() {
                const followedId = this.dataset.followedId;

                const formData = new FormData();
                formData.append('followed_id', followedId);

                fetch('follow_user.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            if (data.action === 'followed') {
                                this.textContent = 'Obserwujesz';
                                this.classList.add('followed');
                            } else {
                                this.textContent = 'Obserwuj';
                                this.classList.remove('followed');
                            }
                        } else {
                            alert(data.message || 'Wystąpił błąd.');
                        }
                    }).catch(error => console.error('Błąd sieci:', error));
            });
        });

        const maxChars = 1000;
        const charCounter = document.getElementById('char-counter');
        const charsLeftSpan = document.getElementById('chars-left');

        function updateCharCounter() {
            const currentLength = realCommentTextarea.value.length;
            const charsLeft = maxChars - currentLength;

            // Domyślnie włącz przycisk zapisu.
            // Użytkownik może chcieć zapisać pusty komentarz (lub tylko ocenę).
            saveCommentBtn.disabled = false;

            if (currentLength > 0) {
                // Pokaż licznik tylko wtedy, gdy coś jest wpisane
                charCounter.classList.remove('hidden');
                charsLeftSpan.textContent = charsLeft;
            } else {
                // Ukryj licznik, gdy pole jest puste
                charCounter.classList.add('hidden');
            }

            // Wyłącz przycisk TYLKO wtedy, gdy przekroczono limit znaków
            if (charsLeft < 0) {
                saveCommentBtn.disabled = true;
                charsLeftSpan.textContent = '0'; // Pokaż 0, gdy przekroczono
                charCounter.classList.remove('hidden'); // Upewnij się, że licznik jest widoczny
            }
        }

        realCommentTextarea.addEventListener('input', updateCharCounter);



        const ratingTexts = [
            "Oceń film",
            "Tragedia",
            "Bardzo zły",
            "Słaby",
            "Ujdzie",
            "Średni",
            "Niezły",
            "Dobry",
            "Bardzo dobry",
            "Rewelacyjny",
            "Arcydzieło!"
        ];

        const rateTextSpan = document.querySelector('.box-rate-text');
        const starContainer = document.querySelector('.star-rating-container');
        const starRatingLabels = starContainer.querySelectorAll('label');

        function updateRatingText() {
            const checkedInput = starContainer.querySelector('input[name="rating"]:checked');
            const ratingValue = checkedInput ? parseInt(checkedInput.value) : 0;
            rateTextSpan.textContent = ratingTexts[ratingValue] || ratingTexts[0];
        }

        starRatingLabels.forEach(label => {
            label.addEventListener('mouseover', function() {
                const starValue = parseInt(this.getAttribute('for').replace('star', ''));
                rateTextSpan.textContent = ratingTexts[starValue] || ratingTexts[0];
            });
        });

        starContainer.addEventListener('mouseleave', function() {
            updateRatingText();
        });

        updateRatingText();

        const sliderWrappers = document.querySelectorAll('.simple-slider-wrapper');

        sliderWrappers.forEach(wrapper => {
            const container = wrapper.querySelector('.simple-slider-container');
            const prevButton = wrapper.querySelector('.slider-arrow-prev');
            const nextButton = wrapper.querySelector('.slider-arrow-next');

            if (!container || !prevButton || !nextButton) return;

            const updateButtons = () => {
                prevButton.disabled = container.scrollLeft <= 0;
                const maxScrollLeft = container.scrollWidth - container.clientWidth;
                nextButton.disabled = container.scrollLeft >= maxScrollLeft - 1;
            };

            nextButton.addEventListener('click', () => {
                const slideWidth = container.querySelector('.simple-slider-item').offsetWidth;
                const gap = parseInt(window.getComputedStyle(container.querySelector('.simple-slider-track')).gap);
                container.scrollLeft += slideWidth + gap;
            });

            prevButton.addEventListener('click', () => {
                const slideWidth = container.querySelector('.simple-slider-item').offsetWidth;
                const gap = parseInt(window.getComputedStyle(container.querySelector('.simple-slider-track')).gap);
                container.scrollLeft -= slideWidth + gap;
            });

            container.addEventListener('scroll', updateButtons);
            updateButtons();
        });
    });
</script>

</body>

</html>
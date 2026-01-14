<?php
session_start();
include 'config/db_connect.php';
include 'includes/header.php';

$logged_in_user_id = $_SESSION['user_id'] ?? null;
$page_context = isset($_GET['movie_id']) ? 'movie' : (isset($_GET['user_id']) ? 'user' : 'none');
$id = (int)($_GET['movie_id'] ?? $_GET['user_id'] ?? 0);
$review_type = (isset($_GET['type']) && $_GET['type'] === 'critic') ? 'critic' : 'user';

$reviews_per_page = 20;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}

if ($id === 0) {
    echo "<main><div class='main-content'><p>Nieprawidłowy adres. Nie znaleziono filmu.</p></div></main>";
    include 'includes/footer.php';
    exit();
}

$offset = ($current_page - 1) * $reviews_per_page;
$all_reviews = [];

if ($page_context === 'movie') {
    $sql_movie = "SELECT title FROM movies WHERE id = ?";
    $stmt_movie = $conn->prepare($sql_movie);
    $stmt_movie->bind_param("i", $id);
    $stmt_movie->execute();
    $page_subject = $stmt_movie->get_result()->fetch_assoc();
    $stmt_movie->close();
    $review_type_text = ($review_type === 'critic') ? 'recenzje krytyków' : 'opinie';
    $page_title = 'Wszystkie ' . $review_type_text . ' dla <span class="movie-title">"' . htmlspecialchars($page_subject['title']) . '"</span>';
    $back_link = 'movie.php?id=' . $id;
    $back_link_text = 'Wróć do strony filmu';

    $where_clause = "r.movie_id = ?";
    $sql_all_reviews = "SELECT r.id AS rating_id, r.rating, r.comment, r.created_at, u.id as user_id, u.username, u.avatar_url, m.title as movie_title, m.id as movie_id,
                               (SELECT COUNT(*) FROM user_movie_lists uml WHERE uml.user_id = r.user_id AND uml.movie_id = r.movie_id AND uml.list_type = 'favorite') AS is_favorite,
                               (SELECT COUNT(*) FROM review_likes rl WHERE rl.rating_id = r.id) AS like_count,
                               (SELECT COUNT(*) FROM review_likes rl WHERE rl.rating_id = r.id AND rl.user_id = ?) AS user_liked
                        FROM ratings r
                        JOIN users u ON r.user_id = u.id
                        JOIN movies m ON r.movie_id = m.id
                        WHERE $where_clause AND r.rating_type = ? AND r.comment IS NOT NULL AND r.comment != ''
                        ORDER BY r.created_at DESC
                        LIMIT ? OFFSET ?";
    $stmt_all_reviews = $conn->prepare($sql_all_reviews);
    $stmt_all_reviews->bind_param("iisii", $logged_in_user_id, $id, $review_type, $reviews_per_page, $offset);
} elseif ($page_context === 'user') {
    $sql_user = "SELECT username FROM users WHERE id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $id);
    $stmt_user->execute();
    $page_subject = $stmt_user->get_result()->fetch_assoc();
    $stmt_user->close();
    $page_title = 'Wszystkie opinie użytkownika <span class="movie-title">' . htmlspecialchars($page_subject['username']) . '</span>';
    $back_link = 'profile.php?id=' . $id;
    $back_link_text = 'Wróć do profilu';

    $where_clause = "r.user_id = ?";
    $sql_all_reviews = "SELECT r.id AS rating_id, r.rating, r.comment, r.created_at, u.id as user_id, u.username, u.avatar_url, m.title as movie_title, m.id as movie_id,
                               (SELECT COUNT(*) FROM user_movie_lists uml WHERE uml.user_id = r.user_id AND uml.movie_id = r.movie_id AND uml.list_type = 'favorite') AS is_favorite,
                               (SELECT COUNT(*) FROM review_likes rl WHERE rl.rating_id = r.id) AS like_count,
                               (SELECT COUNT(*) FROM review_likes rl WHERE rl.rating_id = r.id AND rl.user_id = ?) AS user_liked
                        FROM ratings r
                        JOIN users u ON r.user_id = u.id
                        JOIN movies m ON r.movie_id = m.id
                        WHERE $where_clause AND r.rating_type = 'user' AND r.comment IS NOT NULL AND r.comment != ''
                        ORDER BY r.created_at DESC
                        LIMIT ? OFFSET ?";
    $stmt_all_reviews = $conn->prepare($sql_all_reviews);
    $stmt_all_reviews->bind_param("iiii", $logged_in_user_id, $id, $reviews_per_page, $offset);
} else {
    echo "<main><div class='main-content'><p>Nieprawidłowy typ strony.</p></div></main>";
    include 'includes/footer.php';
    exit();
}

$sql_count = "SELECT COUNT(*) AS total FROM ratings r WHERE $where_clause AND r.rating_type = ? AND r.comment IS NOT NULL AND r.comment != ''";
$stmt_count = $conn->prepare($sql_count);
$count_review_type = ($page_context === 'user') ? 'user' : $review_type;
$stmt_count->bind_param("is", $id, $review_type);
$stmt_count->execute();
$total_reviews = $stmt_count->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_count->close();

$total_pages = ceil($total_reviews / $reviews_per_page);

$stmt_all_reviews->execute();
$result_all_reviews = $stmt_all_reviews->get_result();
if ($result_all_reviews->num_rows > 0) {
    while ($row = $result_all_reviews->fetch_assoc()) {
        $all_reviews[] = $row;
    }
}
$stmt_all_reviews->close();
$conn->close();
?>

<link rel="stylesheet" href="assets/css/all_reviews.css">

<main>
    <div class="main-content" style="padding-top: 2rem; padding-bottom: 2rem;">
        <div class="reviews-list">
            <h1 class="all-reviews-header"><?php echo $page_title; ?></h1>
            <div class="back-link-div">
                <a href="<?php echo $back_link; ?>" class="back-link">&larr; <?php echo $back_link_text; ?></a>
            </div>

            <?php if (!empty($all_reviews)): ?>
                <div class="review-items-container">
                    <?php foreach ($all_reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header" style="justify-content: flex-start;">
                                <?php if ($page_context === 'movie'): ?>
                                    <a href="profile.php?id=<?php echo $review['user_id']; ?>" class="review-author-link">
                                        <img src="<?php echo htmlspecialchars($review['avatar_url'] ?? 'assets/img/avatar-default.png'); ?>" alt="Avatar" class="review-avatar">
                                    </a>
                                    <a href="profile.php?id=<?php echo $review['user_id']; ?>" class="review-author-link">
                                        <span class="review-author"><?php echo htmlspecialchars($review['username']); ?></span>
                                    </a>
                                <?php else: ?>
                                    <span class="review-author">
                                        Recenzja dla filmu <a href="movie.php?id=<?php echo $review['movie_id']; ?>" style="text-decoration: none; color: #2c2c2c; font-weight: 600;"><?php echo htmlspecialchars($review['movie_title']); ?></a>
                                    </span>
                                <?php endif; ?>

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
                            <p class="review-comment" style="margin-bottom: 0;"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
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
                                        <?php
                                        $date = new DateTime($review['created_at']);
                                        echo $date->format('d.m.Y H:i');
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Brak recenzji dla tego filmu.</p>
            <?php endif; ?>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php $base_url = "?" . ($page_context === 'movie' ? 'movie_id=' . $id . '&type=' . $review_type : 'user_id=' . $id); ?>

                    <?php if ($current_page > 1): ?>
                        <a href="<?php echo $base_url; ?>&page=<?php echo $current_page - 1; ?>">Poprzednia</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $current_page): ?>
                            <span class="current-page"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="<?php echo $base_url; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="<?php echo $base_url; ?>&page=<?php echo $current_page + 1; ?>">Następna</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php
include 'includes/footer.php';
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const likeButtons = document.querySelectorAll('.like-btn');

        likeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const ratingId = this.dataset.ratingId;
                const likeCountSpan = this.querySelector('.like-count');

                const formData = new FormData();
                formData.append('rating_id', ratingId);

                fetch('actions/reviews/like_review.php', {
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
    });
</script>
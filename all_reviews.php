<?php
session_start();
include 'db_connect.php';
include 'header.php';

$user_id = $_SESSION['user_id'] ?? null;
$page_type = isset($_GET['movie_id']) ? 'movie' : (isset($_GET['user_id']) ? 'user' : 'none');
$id = (int)($_GET['movie_id'] ?? $_GET['user_id'] ?? 0);

$reviews_per_page = 20;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}

if ($id === 0) {
    echo "<main><div class='main-content'><p>Nieprawidłowy adres. Nie znaleziono filmu.</p></div></main>";
    include 'footer.php';
    exit();
}

$offset = ($current_page - 1) * $reviews_per_page;
$all_reviews = [];

if ($page_type === 'movie') {
    $sql_movie = "SELECT title FROM movies WHERE id = ?";
    $stmt_movie = $conn->prepare($sql_movie);
    $stmt_movie->bind_param("i", $id);
    $stmt_movie->execute();
    $page_subject = $stmt_movie->get_result()->fetch_assoc();
    $stmt_movie->close();
    $page_title = 'Wszystkie opinie dla <span class="movie-title">"' . htmlspecialchars($page_subject['title']) . '"</span>';
    $back_link = 'movie.php?id=' . $id;
    $back_link_text = 'Wróć do strony filmu';

    $where_clause = "r.movie_id = ?";
    $sql_all_reviews = "SELECT r.id AS rating_id, r.rating, r.comment, r.created_at, u.username, m.title as movie_title, m.id as movie_id,
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
    $stmt_all_reviews->bind_param("iiii", $user_id, $id, $reviews_per_page, $offset);

} elseif ($page_type === 'user') {
    $sql_user = "SELECT username FROM users WHERE id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $id);
    $stmt_user->execute();
    $page_subject = $stmt_user->get_result()->fetch_assoc();
    $stmt_user->close();
    $page_title = 'Wszystkie opinie użytkownika <span class="movie-title">' . htmlspecialchars($page_subject['username']) . '</span>';
    $back_link = 'profile.php';
    $back_link_text = 'Wróć do profilu';

    $where_clause = "r.user_id = ?";
    $sql_all_reviews = "SELECT r.id AS rating_id, r.rating, r.comment, r.created_at, u.username, m.title as movie_title, m.id as movie_id,
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
    $stmt_all_reviews->bind_param("iiii", $user_id, $id, $reviews_per_page, $offset);
} else {
    echo "<main><div class='main-content'><p>Nieprawidłowy typ strony.</p></div></main>";
    include 'footer.php';
    exit();
}

$sql_count = "SELECT COUNT(*) AS total FROM ratings r WHERE $where_clause AND r.rating_type = 'user' AND r.comment IS NOT NULL AND r.comment != ''";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("i", $id);
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

<style>
    .reviews-list {
        max-width: 800px;
        margin-left: 0;
        margin: 0 auto;
    }

    .all-reviews-header {
        font-size: 2rem;
        color: #2c2c2c;
        margin-bottom: 0.5rem;
    }

    .all-reviews-header .movie-title {
        color: #0ccb4a;
    }

    .back-link-div {
        width: 100%;
        text-align: left;
    }

    .back-link {
        display: inline-block;
        margin-bottom: 2rem;
        color: #555;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
    }

    .back-link:hover {
        color: #0ccb4a;
    }

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        margin-top: 3rem;
    }

    .pagination a,
    .pagination span {
        display: inline-block;
        padding: 8px 14px;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-decoration: none;
        color: #333;
        font-weight: 600;
        transition: background-color 0.2s, color 0.2s;
    }

    .pagination a:hover {
        background-color: #f5f5f5;
        border-color: #ccc;
    }

    .pagination .current-page {
        background-color: #0ccb4a;
        color: #ffffff;
        border-color: #0ccb4a;
    }

    .pagination .disabled {
        color: #aaa;
        pointer-events: none;
    }
</style>

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
                                <?php if ($page_type === 'movie'): ?>
                                    <img src="uploads/avatar-default.png" alt="Avatar" class="review-avatar">
                                    <span class="review-author"><?php echo htmlspecialchars($review['username']); ?></span>
                                <?php else: ?>
                                    <span class="review-author">
                                        Recenzja dla filmu <?php echo htmlspecialchars($review['movie_title']); ?>
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
                    <?php $base_url = "?".($page_type === 'movie' ? 'movie_id='.$id : 'user_id='.$id); ?>

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
include 'footer.php';
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
    });
</script>
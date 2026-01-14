<?php
session_start();

include 'config/db_connect.php';
include 'includes/header.php';

echo '<link rel="stylesheet" href="assets/css/profile.css">';

$logged_in_user_id = $_SESSION['user_id'] ?? null;
$profile_user_id = 0;

if (isset($_GET['id']) && (int)$_GET['id'] > 0) {
    $profile_user_id = (int)$_GET['id'];
} elseif ($logged_in_user_id) {
    $profile_user_id = $logged_in_user_id;
}

if ($profile_user_id === 0) {
    echo "<main><div class='main-content'><p>Nie znaleziono użytkownika.</p></div></main>";
    include 'includes/footer.php';
    exit();
}

$update_description_success = false;
$update_description_error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_critic_description']) && $logged_in_user_id === $profile_user_id) {
    $sql_check_role = "SELECT role FROM users WHERE id = ?";
    $stmt_check_role = $conn->prepare($sql_check_role);
    $stmt_check_role->bind_param("i", $logged_in_user_id);
    $stmt_check_role->execute();
    $user_role_result = $stmt_check_role->get_result()->fetch_assoc();

    if ($user_role_result && $user_role_result['role'] === 'critic') {
        $new_description = trim(strip_tags($_POST['critic_description']));
        if (strlen($new_description) <= 100) {
            $sql_update = "UPDATE users SET critic_description = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $new_description, $logged_in_user_id);
            if ($stmt_update->execute()) {
                $update_description_success = true;
            } else {
                $update_description_error = 'Błąd podczas aktualizacji opisu.';
            }
        } else {
            $update_description_error = 'Opis nie może przekraczać 100 znaków.';
        }
    }
}
$sql = "SELECT username, email, phone_number, role, created_at, profile_banner_url, critic_description, avatar_url FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profile_user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$stats = [
    'ratings' => 0,
    'watchlist' => 0,
    'favorites' => 0
];

$sql_ratings = "SELECT COUNT(*) as count FROM ratings WHERE user_id = ? AND rating > 0";
$stmt_ratings = $conn->prepare($sql_ratings);
$stmt_ratings->bind_param("i", $profile_user_id);
$stmt_ratings->execute();
$stats['ratings'] = $stmt_ratings->get_result()->fetch_assoc()['count'];
$stmt_ratings->close();

$sql_lists = "SELECT list_type, COUNT(*) as count FROM user_movie_lists WHERE user_id = ? GROUP BY list_type";
$stmt_lists = $conn->prepare($sql_lists);
$stmt_lists->bind_param("i", $profile_user_id);
$stmt_lists->execute();
$result_lists = $stmt_lists->get_result();
while ($row = $result_lists->fetch_assoc()) {
    if ($row['list_type'] === 'watchlist') $stats['watchlist'] = $row['count'];
    if ($row['list_type'] === 'favorite') $stats['favorites'] = $row['count'];
}
$stmt_lists->close();

$all_movies_for_banner = [];
$sql_all_movies = "SELECT id, title, poster_url FROM movies ORDER BY popularity DESC, title ASC";
$result_all_movies = $conn->query($sql_all_movies);
if ($result_all_movies) {
    while ($row = $result_all_movies->fetch_assoc()) {
        $all_movies_for_banner[] = $row;
    }
}

$rating_type_to_fetch = ($user['role'] === 'critic') ? 'critic' : 'user';

$recent_reviews = [];
$sql_reviews = "SELECT r.id as rating_id, r.rating, r.comment, r.created_at, m.title, m.id as movie_id,
                       (SELECT COUNT(*) FROM user_movie_lists uml WHERE uml.user_id = r.user_id AND uml.movie_id = r.movie_id AND uml.list_type = 'favorite') as is_favorite,
                       (SELECT COUNT(*) FROM review_likes rl WHERE rl.rating_id = r.id) AS like_count,
                       (SELECT COUNT(*) FROM review_likes rl WHERE rl.rating_id = r.id AND rl.user_id = ?) AS user_liked
                FROM ratings r
                JOIN movies m ON r.movie_id = m.id
                WHERE r.user_id = ? AND r.rating_type = ? AND r.comment IS NOT NULL AND r.comment != ''
                ORDER BY r.created_at DESC";
$stmt_reviews = $conn->prepare($sql_reviews);
$stmt_reviews->bind_param("iis", $logged_in_user_id, $profile_user_id, $rating_type_to_fetch);
$stmt_reviews->execute();
$result_reviews = $stmt_reviews->get_result();
while ($row = $result_reviews->fetch_assoc()) {
    $recent_reviews[] = $row;
}
$stmt_reviews->close();

$watchlist_movies = [];
$sql_watchlist = "SELECT m.id, m.title, m.poster_url, m.release_year
                  FROM user_movie_lists uml
                  JOIN movies m ON uml.movie_id = m.id
                  WHERE uml.user_id = ? AND uml.list_type = 'watchlist'
                  ORDER BY m.popularity DESC";
$stmt_watchlist = $conn->prepare($sql_watchlist);
$stmt_watchlist->bind_param("i", $profile_user_id);
$stmt_watchlist->execute();
$result_watchlist = $stmt_watchlist->get_result();
while ($row = $result_watchlist->fetch_assoc()) {
    $watchlist_movies[] = $row;
}
$stmt_watchlist->close();

$favorite_movies = [];
$sql_favorites = "SELECT m.id, m.title, m.poster_url, m.release_year
                  FROM user_movie_lists uml
                  JOIN movies m ON uml.movie_id = m.id
                  WHERE uml.user_id = ? AND uml.list_type = 'favorite'
                  ORDER BY m.popularity DESC";
$stmt_favorites = $conn->prepare($sql_favorites);
$stmt_favorites->bind_param("i", $profile_user_id);
$stmt_favorites->execute();
$result_favorites = $stmt_favorites->get_result();
while ($row = $result_favorites->fetch_assoc()) {
    $favorite_movies[] = $row;
}
$stmt_favorites->close();

$following_users = [];
$sql_following = "SELECT u.id, u.username, u.avatar_url
                  FROM followers f
                  JOIN users u ON f.followed_id = u.id
                  WHERE f.follower_id = ?
                  ORDER BY u.username ASC";
$stmt_following = $conn->prepare($sql_following);
$stmt_following->bind_param("i", $profile_user_id);
$stmt_following->execute();
$result_following = $stmt_following->get_result();
while ($row = $result_following->fetch_assoc()) {
    $following_users[] = $row;
}
$stmt_following->close();

$achievements = [];
$sql_achievements = "SELECT a.name, a.description, a.icon_url
                     FROM user_achievements ua
                     JOIN achievements a ON ua.achievement_id = a.id
                     WHERE ua.user_id = ?
                     ORDER BY ua.earned_at DESC";
$stmt_achievements = $conn->prepare($sql_achievements);
$stmt_achievements->bind_param("i", $profile_user_id);
$stmt_achievements->execute();
$result_achievements = $stmt_achievements->get_result();
while ($row = $result_achievements->fetch_assoc()) {
    $achievements[] = $row;
}
$conn->close();
?>

<main>
    <div class="profile-banner-wrapper">
        <div class="profile-banner">
            <div id="profile-banner-background" class="profile-banner-background" style="<?php if (!empty($user['profile_banner_url'])) echo 'background-image: url(\'' . htmlspecialchars($user['profile_banner_url']) . '\');'; ?>"></div>
            <?php
            if ($update_description_success) echo '<div class="inline-message success">Opis został zaktualizowany!</div>';
            if (!empty($update_description_error)) echo '<div class="inline-message error">' . htmlspecialchars($update_description_error) . '</div>';
            ?>
            <?php if ($logged_in_user_id === $profile_user_id): ?>
                <div class="btns-div-avatar">
                    <button id="change-avatar-btn" class="change-banner-btn"><i class="fa-solid fa-camera"></i> <span>Zmień awatar</span></button>
                    <input type="file" id="avatar-upload-input" style="display: none;" accept="image/*">
                    <button id="change-banner-btn" class="change-banner-btn"><i class="fa-solid fa-image"></i> <span>Zmień tło</span></button>
                </div>
            <?php endif; ?>
            <div class="main-content">
                <div class="form-container">
                    <div class="avatar-card">
                        <div class="avatar-container" id="avatar-container-clickable">
                            <img src="<?php echo htmlspecialchars($user['avatar_url'] ?? 'assets/img/avatar-default.png'); ?>" alt="Avatar użytkownika" class="profile-avatar" id="profile-avatar-img">
                        </div>
                        <div class="avatar-info">
                            <div class="profile-username"><?php echo htmlspecialchars($user['username']); ?></div>
                            <?php if ($user['role'] === 'critic'): ?>
                                <div id="critic-description-wrapper" class="critic-description-wrapper">
                                    <?php
                                    $is_own_profile = ($logged_in_user_id === $profile_user_id);
                                    $description_text = '';
                                    if (!empty($user['critic_description'])) {
                                        $description_text = nl2br(htmlspecialchars($user['critic_description']));
                                    } elseif ($is_own_profile) {
                                        $description_text = 'Kliknij, aby dodać opis...';
                                    }
                                    ?>
                                    <?php if (!empty($description_text)): ?>
                                        <span id="critic-description-text" class="critic-description-text"><?php echo $description_text; ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="profile-stats-box">
            <div class="stats-item">
                <div class="stat-number"><?php echo $stats['ratings']; ?></div>
                <div class="stat-label">Oceny</div>
            </div>
            <div class="stats-item">
                <div class="stat-number"><?php echo $stats['watchlist']; ?></div>
                <div class="stat-label">Chce zobaczyć</div>
            </div>
            <div class="stats-item">
                <div class="stat-number"><?php echo $stats['favorites']; ?></div>
                <div class="stat-label">Ulubione</div>
            </div>
        </div>
    </div>
</main>

<div style="max-width: 1200px; margin: 0 auto;">
    <div style="margin: 16px;">
        <?php if (!empty($achievements)): ?>
            <section class="profile-content-section">
                <h3>Osiągnięcia</h3>
                <div class="achievements-list">
                    <?php foreach ($achievements as $achievement): ?>
                        <div class="achievement-item">
                            <img src="<?php echo htmlspecialchars($achievement['icon_url']); ?>" alt="<?php echo htmlspecialchars($achievement['name']); ?>">
                            <div class="achievement-info">
                                <h4><?php echo htmlspecialchars($achievement['name']); ?></h4>
                                <p><?php echo htmlspecialchars($achievement['description']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($recent_reviews)): ?>
            <section class="profile-content-section" style="max-width: 800px; margin-bottom: 2rem;">
                <h3>Oceny</h3>
                <div class="user-reviews-container">
                    <?php
                    $reviews_to_show = array_slice($recent_reviews, 0, 3);
                    foreach ($reviews_to_show as $review):
                    ?>
                        <div class="user-review-item">
                            <div class="user-review-header" style="margin-bottom: 1rem;">
                                <span class="review-author">
                                    Recenzja filmu <?php echo htmlspecialchars($review['title']); ?>
                                </span>
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
                <?php if (count($recent_reviews) > 3): ?>
                    <div style="text-align: center; margin-top: 2rem;">
                        <a href="all_reviews.php?user_id=<?php echo $profile_user_id; ?>" class="see-more-reviews-link">
                            <span>Pokaż wszystkie opinie</span>
                            <span class="review-count-badge"><?php echo count($recent_reviews); ?></span>
                        </a>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if (!empty($favorite_movies)): ?>
            <section class="profile-content-section">
                <h3>Ulubione</h3>
                <div class="movies-grid">
                    <?php foreach ($favorite_movies as $movie): ?>
                        <div class="movie-grid-item">
                            <a href="movie.php?id=<?php echo $movie['id']; ?>">
                                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="Plakat filmu <?php echo htmlspecialchars($movie['title']); ?>">
                                <h4><?php echo htmlspecialchars($movie['title']); ?></h4>
                                <p><?php echo htmlspecialchars($movie['release_year']); ?></p>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($watchlist_movies)): ?>
            <section class="profile-content-section">
                <h3>Chcę obejrzeć</h3>
                <div class="movies-grid">
                    <?php foreach ($watchlist_movies as $movie): ?>
                        <div class="movie-grid-item">
                            <a href="movie.php?id=<?php echo $movie['id']; ?>">
                                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="Plakat filmu <?php echo htmlspecialchars($movie['title']); ?>">
                                <h4><?php echo htmlspecialchars($movie['title']); ?></h4>
                                <p><?php echo htmlspecialchars($movie['release_year']); ?></p>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($following_users)): ?>
            <section class="profile-content-section">
                <h3>Obserwowani użytkownicy</h3>
                <div class="users-grid">
                    <?php foreach ($following_users as $followed_user): ?>
                        <div class="user-grid-item">
                            <a href="profile.php?id=<?php echo $followed_user['id']; ?>">
                                <img src="<?php echo htmlspecialchars($followed_user['avatar_url'] ?? 'assets/img/avatar-default.png'); ?>" alt="Avatar użytkownika <?php echo htmlspecialchars($followed_user['username']); ?>">
                                <h4><?php echo htmlspecialchars($followed_user['username']); ?></h4>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>

<?php if ($logged_in_user_id === $profile_user_id && $user['role'] === 'critic'): ?>
    <div id="description-modal-overlay" class="modal-overlay">
        <div class="modal-box description-modal">
            <h3 style="margin-bottom: 1rem;">Edytuj swój opis</h3>
            <form id="critic-description-form" action="profile.php?id=<?php echo $profile_user_id; ?>" method="POST">
                <textarea name="critic_description" class="critic-description-textarea" placeholder="Napisz kilka słów o sobie, swoich zainteresowaniach filmowych..." maxlength="100"><?php echo htmlspecialchars($user['critic_description'] ?? ''); ?></textarea>
                <div class="form-actions">
                    <button type="submit" name="update_critic_description" class="submit-btn">Zapisz</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php if ($logged_in_user_id === $profile_user_id): ?>
    <div id="banner-modal-overlay" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title-group">
                    <h3>Wybierz tło</h3>
                    <button id="remove-banner-btn" class="remove-banner-icon <?php if (empty($user['profile_banner_url'])) echo 'hidden'; ?>" title="Usuń tło">&times;</button>
                </div>
                <div class="modal-search-container">
                    <input type="search" id="modal-search-input" placeholder="Szukaj">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
            </div>
            <div id="posters-grid" class="posters-grid">
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
include 'includes/footer.php';
?>

<script>
    <?php if ($logged_in_user_id === $profile_user_id): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const allMovies = <?php echo json_encode($all_movies_for_banner); ?>;
            const changeAvatarBtn = document.getElementById('change-avatar-btn');
            const avatarUploadInput = document.getElementById('avatar-upload-input');
            const profileAvatarImg = document.getElementById('profile-avatar-img');
            const avatarContainer = document.getElementById('avatar-container-clickable');

            if (changeAvatarBtn && avatarUploadInput) {
                changeAvatarBtn.addEventListener('click', () => {
                    avatarUploadInput.click();
                });
            }

            if (avatarContainer && avatarUploadInput) {
                avatarContainer.addEventListener('click', () => {
                    avatarUploadInput.click();
                });

                avatarUploadInput.addEventListener('change', () => {
                    const file = avatarUploadInput.files[0];
                    if (file) {
                        const formData = new FormData();
                        formData.append('avatar', file);

                        fetch('actions/users/update_avatar.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    profileAvatarImg.src = data.new_avatar_url + '?' + new Date().getTime();
                                } else {
                                    alert('Błąd: ' + data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Błąd sieci:', error);
                                alert('Wystąpił błąd sieciowy.');
                            });
                    }
                });
            }




            const changeBannerBtn = document.getElementById('change-banner-btn');
            const modalOverlay = document.getElementById('banner-modal-overlay');
            const profileBannerBg = document.getElementById('profile-banner-background');
            const removeBannerBtn = document.getElementById('remove-banner-btn');
            const searchInput = document.getElementById('modal-search-input');
            const postersGrid = document.getElementById('posters-grid');

            changeBannerBtn.addEventListener('click', () => {
                modalOverlay.classList.add('visible');
            });

            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) {
                    modalOverlay.classList.remove('visible');
                }
            });

            function renderPosters(moviesToShow) {
                postersGrid.innerHTML = '';
                const moviesToDisplay = moviesToShow.slice(0, 12);

                if (moviesToDisplay.length === 0) {
                    postersGrid.innerHTML = '<p style="grid-column: 1 / -1; text-align: center;">Brak pasujących filmów.</p>';
                    return;
                }

                moviesToDisplay.forEach(movie => {
                    const posterDiv = document.createElement('div');
                    posterDiv.className = 'poster-item';
                    posterDiv.dataset.posterUrl = movie.poster_url;

                    const posterImg = document.createElement('img');
                    posterImg.src = movie.poster_url;
                    posterImg.alt = `Plakat filmu ${movie.title}`;

                    posterDiv.appendChild(posterImg);
                    postersGrid.appendChild(posterDiv);
                });
            }

            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                if (query.length > 0) {
                    const filteredMovies = allMovies.filter(movie =>
                        movie.title.toLowerCase().includes(query)
                    );
                    renderPosters(filteredMovies);
                } else {
                    renderPosters(allMovies);
                }
            });

            postersGrid.addEventListener('click', function(e) {
                const posterItem = e.target.closest('.poster-item');
                if (posterItem) {
                    const posterUrl = posterItem.dataset.posterUrl;
                    updateBanner(posterUrl);
                }
            });


            removeBannerBtn.addEventListener('click', function() {
                updateBanner('');
            });

            function updateBanner(url) {
                profileBannerBg.style.backgroundImage = url ? `url('${url}')` : 'none';

                const formData = new FormData();
                formData.append('banner_url', url);

                fetch('actions/users/update_banner.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status !== 'success') {
                            console.error('Błąd aktualizacji tła:', data.message);
                        }
                        if (url) {
                            removeBannerBtn.classList.remove('hidden');
                        } else {
                            removeBannerBtn.classList.add('hidden');
                        }
                    });

                modalOverlay.classList.remove('visible');
            }

            renderPosters(allMovies);
        });
    <?php endif; ?>

    <?php if ($logged_in_user_id === $profile_user_id && $user['role'] === 'critic'): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const descriptionText = document.getElementById('critic-description-text');
            const modalOverlay = document.getElementById('description-modal-overlay');

            descriptionText.addEventListener('click', () => {
                modalOverlay.classList.add('visible');
            });

            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) {
                    modalOverlay.classList.remove('visible');
                }
            });
        });
    <?php endif; ?>

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

</body>

</html>
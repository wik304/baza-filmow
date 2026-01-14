<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db_connect.php';
include 'includes/header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">
<link rel="stylesheet" href="assets/css/index.css">

<main>

    <?php
    $user_id = $_SESSION['user_id'] ?? null;

    $sql_announcements = "SELECT
                                m.title,
                                m.description,
                                a.background_image_url,
                                a.movie_id,
                                (SELECT COUNT(*) FROM user_movie_lists WHERE movie_id = m.id AND list_type = 'watchlist') as watchlist_count,
                                " . ($user_id ? "(SELECT COUNT(*) FROM user_movie_lists WHERE movie_id = m.id AND list_type = 'watchlist' AND user_id = {$user_id}) > 0" : "0") . " as is_on_watchlist_db
                          FROM announcements a
                          JOIN movies m ON a.movie_id = m.id
                          WHERE a.is_active = 1
                          ORDER BY a.display_order ASC";
    $stmt_announcements = $conn->prepare($sql_announcements);
    $stmt_announcements->execute();
    $result_announcements = $stmt_announcements->get_result();
    $announcements = [];
    if ($result_announcements && $result_announcements->num_rows > 0) {
        while ($row = $result_announcements->fetch_assoc()) {
            $announcements[] = $row;
        }
    }
    ?>
    <div class="announcement-slider-div">
        <section id="announcement-slider" class="splide" aria-label="Ogłoszenia i polecane">
            <div class="splide__track">
                <ul class="splide__list">
                    <?php foreach ($announcements as $announcement): ?>
                        <li class="splide__slide" style="background-image: url('<?php echo htmlspecialchars($announcement['background_image_url']); ?>');">
                            <div class="hero-slider-overlay"></div>
                            <div class="main-content">
                                <div class="hero-slider-content">
                                    <h2 class="hero-title"><?php echo htmlspecialchars($announcement['title']); ?></h2>
                                    <p><?php echo htmlspecialchars($announcement['description']); ?></p>
                                    <div class="hero-actions">
                                        <?php
                                        $is_on_watchlist_guest = !$user_id && isset($_SESSION['guest_lists']['watchlist']) && in_array($announcement['movie_id'], (array)$_SESSION['guest_lists']['watchlist']);
                                        $is_active = ($announcement['is_on_watchlist_db'] ?? 0) || $is_on_watchlist_guest;
                                        $total_count = (int)$announcement['watchlist_count'];
                                        $button_text = '';
                                        if ($is_active) {
                                            $others_count = $total_count - 1;
                                            $button_text = 'Ty i ' . number_format($others_count) . ' innych osób chce zobaczyć';
                                        } else {
                                            $button_text = number_format($total_count) . ' osób chce zobaczyć';
                                        }
                                        ?>
                                        <button
                                            class="watchlist-stats-button <?php if ($is_active) echo 'active'; ?>"
                                            data-movie-id="<?php echo $announcement['movie_id']; ?>"
                                            data-list-type="watchlist"
                                            data-initial-count="<?php echo $total_count; ?>"
                                            title="<?php echo $is_active ? 'Usuń z listy' : 'Chcę obejrzeć'; ?>">
                                            <div class="watchlist-stats">
                                                <i class="fa-solid fa-eye"></i>
                                                <span class="watchlist-text">&nbsp;<?php echo $button_text; ?></span>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>
    </div>

    <?php
    $slider_id = 'popular-slider';
    $slider_title = 'Popularne teraz';
    $slider_subtitle = 'Przeglądaj najgorętsze tytuły ostatnich miesięcy!';

    $sql_popular = "SELECT m.id, m.title, m.poster_url,
                           (SELECT AVG(rating) FROM ratings WHERE movie_id = m.id AND rating_type = 'user' AND rating > 0) AS user_rating,
                           (SELECT AVG(rating) FROM ratings WHERE movie_id = m.id AND rating_type = 'critic' AND rating > 0) AS critic_rating
                    FROM movies m
                    WHERE m.status = 'available'
                    ORDER BY m.popularity DESC 
                    LIMIT 10";

    $result_popular = $conn->query($sql_popular);
    $movies_array = [];
    if ($result_popular->num_rows > 0) {
        while ($row = $result_popular->fetch_assoc()) {
            $movies_array[] = $row;
        }
    }
    include 'includes/movie_slider.php';
    ?>


    <?php
    $slider_id = 'top-rated-slider';
    $slider_title = 'Najwyżej Oceniane';
    $slider_subtitle = 'Filmy z najlepszymi ocenami użytkowników';

    $sql_top = "SELECT m.id, m.title, m.poster_url,
                       (SELECT AVG(rating) FROM ratings WHERE movie_id = m.id AND rating_type = 'user' AND rating > 0) AS user_rating,
                       (SELECT AVG(rating) FROM ratings WHERE movie_id = m.id AND rating_type = 'critic' AND rating > 0) AS critic_rating
                FROM movies m
                WHERE m.status = 'available'
                ORDER BY user_rating DESC 
                LIMIT 10";

    $result_top = $conn->query($sql_top);
    $movies_array = [];
    if ($result_top->num_rows > 0) {
        while ($row = $result_top->fetch_assoc()) {
            $movies_array[] = $row;
        }
    }
    include 'includes/movie_slider.php';
    ?>

    <?php
    $slider_id = 'critics-choice-slider';
    $slider_title = 'Uznane przez Krytyków';
    $slider_subtitle = 'Filmy z najlepszymi ocenami recenzentów';

    $sql_critics = "SELECT m.id, m.title, m.poster_url,
                           (SELECT AVG(rating) FROM ratings WHERE movie_id = m.id AND rating_type = 'user' AND rating > 0) AS user_rating,
                           (SELECT AVG(rating) FROM ratings WHERE movie_id = m.id AND rating_type = 'critic' AND rating > 0) AS critic_rating
                    FROM movies m
                    WHERE m.status = 'available'
                    ORDER BY critic_rating DESC 
                    LIMIT 10";

    $result_critics = $conn->query($sql_critics);
    $movies_array = [];
    if ($result_critics->num_rows > 0) {
        while ($row = $result_critics->fetch_assoc()) {
            $movies_array[] = $row;
        }
    }
    include 'includes/movie_slider.php';
    ?>
</main>

<?php
$conn->close();
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const actionButtons = document.querySelectorAll('.announcement-slider-div .watchlist-stats-button');

        actionButtons.forEach(button => {
            button.addEventListener('click', function() {
                const listType = this.dataset.listType;
                const movieId = this.dataset.movieId;
                const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

                const wasActive = this.classList.contains('active');
                let totalCount = parseInt(this.dataset.initialCount);
                const textSpan = this.querySelector('.watchlist-text');

                this.classList.toggle('active');

                if (wasActive) {
                    const newCount = totalCount > 0 ? totalCount - 1 : 0;
                    textSpan.innerHTML = `&nbsp;${newCount.toLocaleString('pl-PL')} osób chce zobaczyć`;
                    this.dataset.initialCount = newCount;
                } else {
                    const newCount = totalCount + 1;
                    const othersCount = newCount - 1;
                    let newText = '';
                    newText += `Ty i  ${othersCount.toLocaleString('pl-PL')} innych osób chce zobaczyć`;
                    textSpan.innerHTML = `&nbsp;${newText}`;
                    this.dataset.initialCount = newCount;
                }

                const formData = new FormData();
                formData.append('movie_id', movieId);
                formData.append('list_type', listType);

                fetch('actions/movies/update_list.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            if (!isLoggedIn && data.action === 'added') {

                            }
                        } else {
                            window.location.reload();
                        }
                    }).catch(error => {
                        console.error('Błąd sieci:', error);
                        window.location.reload();
                    });
            });
        });
    });
</script>

<?php
include 'includes/footer.php';
?>
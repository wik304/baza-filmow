<?php
$slider_id = $slider_id ?? 'slider-' . uniqid();
$slider_title = $slider_title ?? 'Tytuł domyślny';
$slider_subtitle = $slider_subtitle ?? '';
$movies_array = $movies_array ?? [];
?>

<div class="main-content">
    <h1><?php echo htmlspecialchars($slider_title); ?></h1>
    <p><?php echo htmlspecialchars($slider_subtitle); ?></p>

    <section id="<?php echo $slider_id; ?>" class="splide movie-splide-slider" aria-label="<?php echo htmlspecialchars($slider_title); ?>">
        <div class="splide__track">
            <ul class="splide__list">
                <?php if (!empty($movies_array)): ?>
                    <?php foreach ($movies_array as $movie): ?>
                        <li class="splide__slide">
                            <div class="slide-content">
                                <a href="movie.php?id=<?php echo $movie['id']; ?>" class="movie-card">
                                    <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="Plakat filmu <?php echo htmlspecialchars($movie['title']); ?>">
                                </a>
                                <div class="movie-info">
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
                                    <h3 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h3>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="splide__slide" style="width: 100%; text-align: center;">
                        <p>Brak filmów do wyświetlenia.</p>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </section>
</div>
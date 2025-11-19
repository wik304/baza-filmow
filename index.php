<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';
include 'header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">

<main>

    <?php
    ?>
    <div class="announcement-slider-div">
        <section id="announcement-slider" class="splide" aria-label="Ogłoszenia i polecane">
            <div class="splide__track">
                <ul class="splide__list">

                    <li class="splide__slide" style="background-image: url('uploads/hero-image-1.jpg');">
                        <div class="hero-slider-overlay"></div>
                        <div class="main-content">
                            <div class="hero-slider-content">
                                <h2 class="hero-title">Iluzja 3</h2>
                                <p>Legendarni Czterej Jeźdźcy łączą siły z grupą młodych, zbuntowanych iluzjonistów, by dokonać zuchwałej kradzieży bezcennego diamentu i pokrzyżować szyki międzynarodowej organizacji przestępczej.</p>
                                <div class="watchlist-stats">
                                    <i class="fa-solid fa-eye"></i>
                                    <span>12,1 tys. chce zobaczyć</span>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="splide__slide" style="background-image: url('uploads/hero-image-2.jpg');">
                        <div class="hero-slider-overlay"></div>
                        <div class="main-content">
                            <div class="hero-slider-content">
                                <h2 class="hero-title">
                                    Wicked: Na dobre</h2>
                                <p>Ostatni rozdział nieopowiedzianej historii czarownic z Oz rozpoczyna się, gdy Elphaba i Glinda są skłócone i żyją z konsekwencjami swoich decyzji.</p>
                                <div class="watchlist-stats">
                                    <i class="fa-solid fa-eye"></i>
                                    <span>3,6 tys. chce zobaczyć</span>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="splide__slide" style="background-image: url('uploads/hero-image-3.jpg');">
                        <div class="hero-slider-overlay"></div>
                        <div class="main-content">
                            <div class="hero-slider-content">
                                <h2 class="hero-title">Norymberga</h2>
                                <p>Amerykański psychiatra wchodzi w psychologiczną grę z Hermannem Göringiem, by zdobyć niezbite dowody jego winy.</p>
                                <div class="watchlist-stats">
                                    <i class="fa-solid fa-eye"></i>
                                    <span>5,0 tys. chce zobaczyć</span>
                                </div>
                            </div>
                        </div>
                    </li>

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
                    ORDER BY m.popularity DESC 
                    LIMIT 10";

    $result_popular = $conn->query($sql_popular);
    $movies_array = [];
    if ($result_popular->num_rows > 0) {
        while ($row = $result_popular->fetch_assoc()) {
            $movies_array[] = $row;
        }
    }
    include 'movie_slider.php';
    ?>


    <?php
    $slider_id = 'top-rated-slider';
    $slider_title = 'Najwyżej Oceniane';
    $slider_subtitle = 'Filmy z najlepszymi ocenami użytkowników';

    $sql_top = "SELECT m.id, m.title, m.poster_url,
                       (SELECT AVG(rating) FROM ratings WHERE movie_id = m.id AND rating_type = 'user' AND rating > 0) AS user_rating,
                       (SELECT AVG(rating) FROM ratings WHERE movie_id = m.id AND rating_type = 'critic' AND rating > 0) AS critic_rating
                FROM movies m
                ORDER BY user_rating DESC 
                LIMIT 10";

    $result_top = $conn->query($sql_top);
    $movies_array = [];
    if ($result_top->num_rows > 0) {
        while ($row = $result_top->fetch_assoc()) {
            $movies_array[] = $row;
        }
    }
    include 'movie_slider.php';
    ?>

    <?php
    $slider_id = 'critics-choice-slider';
    $slider_title = 'Uznane przez Krytyków';
    $slider_subtitle = 'Filmy z najlepszymi ocenami recenzentów';

    $sql_critics = "SELECT m.id, m.title, m.poster_url,
                           (SELECT AVG(rating) FROM ratings WHERE movie_id = m.id AND rating_type = 'user' AND rating > 0) AS user_rating,
                           (SELECT AVG(rating) FROM ratings WHERE movie_id = m.id AND rating_type = 'critic' AND rating > 0) AS critic_rating
                    FROM movies m
                    ORDER BY critic_rating DESC 
                    LIMIT 10";

    $result_critics = $conn->query($sql_critics);
    $movies_array = [];
    if ($result_critics->num_rows > 0) {
        while ($row = $result_critics->fetch_assoc()) {
            $movies_array[] = $row;
        }
    }
    include 'movie_slider.php';
    ?>
</main>

<?php
$conn->close();
?>

<?php
include 'footer.php';
?>

<script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sliders = document.querySelectorAll('.movie-splide-slider');

        sliders.forEach(sliderElement => {
            new Splide(sliderElement, {
                type: 'loop',
                perPage: 6,
                perMove: 1,
                gap: '1rem',
                autoplay: true,
                interval: 3000,
                pauseOnHover: true,
                pagination: false,
                arrows: true,
                breakpoints: {
                    1200: {
                        perPage: 5
                    },
                    1000: {
                        perPage: 4
                    },
                    800: {
                        perPage: 3
                    },
                    600: {
                        perPage: 2
                    },
                }
            }).mount();
        });
    });
</script>

</body>

</html>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kinoteka - Najlepsza baza ocen filmów</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="reviews.css">
    <style>
        header .search-form {
            position: relative;
            border: none;
            box-shadow: none;
            background-color: transparent;
            height: auto;
        }
        header .search-input {
            width: 100%;
            padding: 10px 35px 10px 10px;
            border: none;
            border-bottom: 2px solid #0ccb4a;
            border-radius: 0;
            font-size: 1rem;
            background-color: transparent;
        }
        header .search-form .fa-magnifying-glass {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #0ccb4a;
        }
        header .search-button {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            width: 40px;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
        }
        header .search-form:focus-within {
             border-color: transparent;
             box-shadow: none;
        }
    </style>
</head>

<body>
    <header>
        <nav>
            <div class="logo-div">
                <img src="uploads/logo_icon.png" alt="Logo PoSeansie" class="logo-icon">
                <a href="index.php" class="logo">Kinoteka</a>
            </div>

            <div class="search-container">
                <form action="search_results.php" method="GET" class="search-form">
                    <input type="text" name="query" id="search-input" class="search-input" placeholder="Szukaj" aria-label="Szukaj" autocomplete="off" required>
                    <div id="autocomplete-results" class="autocomplete-results"></div>
                    <button type="submit" class="search-button" aria-label="Szukaj">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </form>
            </div>

            <button class="hamburger-button" id="mobile-menu-toggle" aria-label="Menu" aria-expanded="false">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>

            <ul class="nav-links" id="nav-links-list">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="profile.php" class="nav-button login-button"><i class="fa-solid fa-user"></i> Profil</a></li>
                    <li><a href="logout.php" class="nav-button register-button"><i class="fa-solid fa-right-from-bracket"></i> Wyloguj</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="nav-button login-button"><i class="fa-solid fa-right-to-bracket"></i> Logowanie</a></li>
                    <li><a href="register.php" class="nav-button register-button"><i class="fa-solid fa-user-plus"></i> Rejestracja</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
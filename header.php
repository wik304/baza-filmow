<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baza Filmów i Seriali</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <nav>
            <a href="index.php" class="logo">BazaFilmów</a>

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
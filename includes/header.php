<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($base_url)) {
    $base_url = '';
}

if (isset($_SESSION['user_id'])) {
    if (!isset($_SESSION['avatar_checked_at']) || (time() - $_SESSION['avatar_checked_at'] > 60)) {
        include_once $base_url . 'config/db_connect.php';
        $sql_session_data = "SELECT role, avatar_url, is_banned FROM users WHERE id = ?";
        $stmt_session_data = $conn->prepare($sql_session_data);
        if ($stmt_session_data) {
            $stmt_session_data->bind_param("i", $_SESSION['user_id']);
            $stmt_session_data->execute();
            $session_data = $stmt_session_data->get_result()->fetch_assoc();

            if ($session_data && $session_data['is_banned'] == 1) {
                session_unset();
                session_destroy();
                header("Location: " . $base_url . "login.php?error=banned");
                exit();
            }

            $_SESSION['user_role'] = $session_data['role'] ?? 'user';
            $_SESSION['user_avatar_url'] = $session_data['avatar_url'] ?? $base_url . 'assets/img/avatar-default.png';
            $stmt_session_data->close();
        }
        $_SESSION['avatar_checked_at'] = time();
    }
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
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/header.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/reviews.css">
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

        .user-menu-container {
            position: relative;
        }

        .user-menu-button {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background-color: transparent;
            border: none;
            border-radius: 20px;
            padding: 4px 12px 4px 4px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .user-menu-button:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-name {
            font-weight: 600;
            color: #2c2c2c;
        }

        .user-menu-button .fa-chevron-down {
            color: #aaa;
            font-size: 0.8rem;
            transition: transform 0.2s ease;
        }

        .user-dropdown-menu {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
            width: 200px;
            padding: 0.5rem 0;
            z-index: 100;
            display: none;
            border: 1px solid #e0e0e0;
        }

        .user-dropdown-menu.show {
            display: block;
        }

        .user-dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.25rem;
            color: #2c2c2c;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .user-dropdown-menu a:hover {
            background-color: #0ccb4a;
            color: #ffffff;
        }

        .user-dropdown-menu a i {
            width: 16px;
            text-align: center;
        }

        .dropdown-divider {
            height: 1px;
            background-color: #f0f0f0;
            margin: 0.5rem 0;
        }
    </style>
</head>

<body>
    <script>
        const BASE_URL = '<?php echo $base_url; ?>';
    </script>
    <header>
        <nav>
            <div class="logo-div">
                <img src="<?php echo $base_url; ?>assets/img/logo_icon.png" alt="Logo PoSeansie" class="logo-icon">
                <a href="<?php echo $base_url; ?>index.php" class="logo">Kinoteka</a>
            </div>

            <div class="search-container">
                <form action="<?php echo $base_url; ?>search_results.php" method="GET" class="search-form">
                    <input type="text" name="query" id="search-input" class="search-input" placeholder="Szukaj" aria-label="Szukaj" autocomplete="off" required>
                    <input type="hidden" name="search" value="1">
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
                    <li class="user-menu-container">
                        <button class="user-menu-button" id="user-menu-button" aria-haspopup="true" aria-expanded="false">
                            <img src="<?php echo htmlspecialchars($_SESSION['user_avatar_url'] ?? $base_url . 'assets/img/avatar-default.png'); ?>" alt="Avatar" class="user-avatar">
                            <span class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>
                        <div class="user-dropdown-menu" id="user-dropdown-menu" role="menu">
                            <a href="<?php echo $base_url; ?>profile.php"><i class="fa-solid fa-user"></i> Mój profil</a>
                            <a href="<?php echo $base_url; ?>settings.php"><i class="fa-solid fa-cog"></i> Ustawienia</a>
                            <?php if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'owner'])): ?>
                                <a href="<?php echo $base_url; ?>admin_panel.php"><i class="fa-solid fa-user-shield"></i> Panel Admina</a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a href="<?php echo $base_url; ?>actions/auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Wyloguj</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li><a href="<?php echo $base_url; ?>login.php" class="nav-button login-button"><i class="fa-solid fa-right-to-bracket"></i> Logowanie</a></li>
                    <li><a href="<?php echo $base_url; ?>register.php" class="nav-button register-button"><i class="fa-solid fa-user-plus"></i> Rejestracja</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userMenuButton = document.getElementById('user-menu-button');
            const userDropdownMenu = document.getElementById('user-dropdown-menu');

            if (userMenuButton && userDropdownMenu) {
                userMenuButton.addEventListener('click', function(event) {
                    event.stopPropagation();
                    const isExpanded = userMenuButton.getAttribute('aria-expanded') === 'true';
                    userDropdownMenu.classList.toggle('show');
                    userMenuButton.setAttribute('aria-expanded', !isExpanded);
                });

                window.addEventListener('click', function(event) {
                    if (!userMenuButton.contains(event.target) && !userDropdownMenu.contains(event.target)) {
                        if (userDropdownMenu.classList.contains('show')) {
                            userDropdownMenu.classList.remove('show');
                            userMenuButton.setAttribute('aria-expanded', 'false');
                        }
                    }
                });
            }
        });
    </script>
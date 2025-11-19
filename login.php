<?php
include 'header.php';
?>
<main>
    <div class="form-container">
        <h2>Logowanie</h2>

        <?php
        if (isset($_GET['error'])) {
            echo '<div class="error-message">';
            if ($_GET['error'] == 'invalidemail') {
                echo 'Podano niepoprawny adres e-mail.';
            } else if ($_GET['error'] == 'wrongpassword') {
                echo 'Nieprawidłowe hasło.';
            } else if ($_GET['error'] == 'nouser') {
                echo 'Użytkownik o podanym adresie e-mail nie istnieje.';
            }
            echo '</div>';
        }
        ?>

        <form action="process_login.php" method="POST">
            <div class="form-group">
                <label for="email">Adres e-mail</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Hasło</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="submit-btn">Zaloguj się</button>
        </form>

        <div class="form-footer">
            <p>Nie masz jeszcze konta? <a href="register.php">Zarejestruj się</a></p>
        </div>
    </div>
</main>

<style>
    .error-message {
        background-color: #ffdddd;
        color: #b30000;
        border: 1px solid #ff5c5c;
        padding: 12px 15px;
        margin: 15px 0;
        border-radius: 8px;
        font-weight: 600;
        text-align: center;
        animation: fadeIn 0.5s ease-in-out;
    }

    .info-message {
        background-color: #e6f7ff;
        color: #005f8d;
        border: 1px solid #99d6ff;
        background-color: #ffdddd;
        color: #b30000;
        border: 1px solid #ff5c5c;
        padding: 12px 15px;
        margin: 15px 0;
        border-radius: 8px;
        font-weight: 600;
        text-align: center;
        animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

</body>

</html>
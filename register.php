<?php
include 'header.php';
?>
<main>
    <div class="form-container">
        <h2>Rejestracja</h2>

        <?php
        if (isset($_GET['error'])) {
            echo '<div class="error-message">';
            if ($_GET['error'] == 'invalidemail') {
                echo 'Proszę podać poprawny adres e-mail.';
            } else if ($_GET['error'] == 'passwordcheck') {
                echo 'Hasła nie są identyczne.';
            } else if ($_GET['error'] == 'passwordshort') {
                echo 'Hasło musi mieć co najmniej 12 znaków.';
            } else if ($_GET['error'] == 'usernamelong') {
                echo 'Nazwa użytkownika nie może być dłuższa niż 32 znaki.';
            } else if ($_GET['error'] == 'passwordlong') {
                echo 'Hasło nie może być dłuższe niż 256 znaków.';
            } else if ($_GET['error'] == 'usertaken') {
                echo 'Nazwa użytkownika lub e-mail są już zajęte.';
            }
            echo '</div>';
        }

        if (isset($_GET['status']) && $_GET['status'] == 'success') {
            echo '<div class="success-message">Rejestracja zakończona pomyślnie! Możesz się teraz zalogować.</div>';
        }
        ?>

        <form action="process_registration.php" method="POST">
            <div class="form-group">
                <label for="username">
                    Nazwa użytkownika
                    <span class="tooltip-container" data-tooltip="Nazwa użytkownika musi zawierać:&#10;- minimum 4 znaki&#10;- maksymalnie 32 znaki">
                        <i class="fas fa-info-circle"></i>
                    </span>
                </label>
                <input type="text" id="username" name="username" required minlength="4" maxlength="32">
            </div>

            <div class="form-group">
                <label for="email">Adres e-mail</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group password-group">
                <label for="password">
                    Hasło
                    <span class="tooltip-container" data-tooltip="Hasło musi zawierać:&#10;- co najmniej 12 znaków&#10;- maksymalnie 256 znaków&#10;- minimum jedną dużą literę&#10;- minimum jedną cyfrę&#10;- minimum jeden znak specjalny">
                        <i class="fas fa-info-circle"></i>
                    </span>
                </label>
                <input type="password" id="password" name="password" required minlength="12" maxlength="256">
            </div>

            <div class="form-group">
                <label for="confirm_password">Potwierdź hasło</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="12" maxlength="256">
            </div>

            <button type="submit" class="submit-btn">Zarejestruj się</button>
        </form>

        <div class="form-footer">
            <p>Masz już konto? <a href="login.php">Zaloguj się</a></p>
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

    .success-message {
        background-color: #e6ffe6;
        color: #006600;
        border: 1px solid #5cd65c;
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
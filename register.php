<?php
include 'includes/header.php';

$old_data = $_SESSION['register_form_data'] ?? [];
unset($_SESSION['register_form_data']);
?>

<link rel="stylesheet" href="assets/css/auth_forms.css">
<link rel="stylesheet" href="assets/css/forms.css">
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
            } else if ($_GET['error'] == 'terms') {
                echo 'Musisz zaakceptować regulamin, aby kontynuować.';
            } else if ($_GET['error'] == 'usertaken') {
                echo 'Nazwa użytkownika lub e-mail są już zajęte.';
            }
            echo '</div>';
        }
        ?>

        <form action="actions/auth/process_registration.php" method="POST">
            <div class="form-group">
                <label for="username">
                    Nazwa użytkownika
                    <span class="tooltip-container" data-tooltip="Nazwa użytkownika musi zawierać:&#10;- minimum 4 znaki&#10;- maksymalnie 32 znaki">
                        <i class="fas fa-info-circle"></i>
                    </span>
                </label>
                <input type="text" id="username" name="username" required minlength="4" maxlength="32" value="<?php echo htmlspecialchars(trim($old_data['username'] ?? '')); ?>">
            </div>

            <div class="form-group">
                <label for="email">Adres e-mail</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars(trim($old_data['email'] ?? '')); ?>">
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

            <div class="form-group form-group-checkbox">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms" class="checkbox-label">
                    Akceptuję <a href="terms.php" target="_blank">regulamin</a> serwisu.
                </label>
            </div>

            <button type="submit" class="submit-btn">Zarejestruj się</button>
        </form>

        <div class="form-footer">
            <p>Masz już konto? <a href="login.php">Zaloguj się</a></p>
        </div>
    </div>
</main>

</body>

</html>
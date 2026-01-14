<?php
include 'includes/header.php';
?>
<?php
$old_data = $_SESSION['login_form_data'] ?? [];
unset($_SESSION['login_form_data']);
?>

<link rel="stylesheet" href="assets/css/auth_forms.css">

<main>
    <div class="form-container">
        <h2>Logowanie</h2>

        <?php
        if (isset($_GET['error'])) {
            echo '<div class="error-message">';
            if ($_GET['error'] == 'invalidemail') {
                echo 'Podano niepoprawny adres e-mail.';
            } else if ($_GET['error'] == 'wrongpassword') {
                echo 'Podano nieprawidłowe hasło.';
            } else if ($_GET['error'] == 'nouser') {
                echo 'Użytkownik o podanym adresie e-mail nie istnieje.';
            } else if ($_GET['error'] == 'banned') {
                echo 'Twoje konto zostało zablokowane. Skontaktuj się z administratorem.';
            }
            echo '</div>';
        }
        ?>

        <form action="actions/auth/process_login.php" method="POST">
            <div class="form-group">
                <label for="email">Adres e-mail</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars(trim($old_data['email'] ?? '')); ?>">
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

</body>

</html>
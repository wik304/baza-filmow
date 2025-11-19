<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';
include 'header.php';

$user_id = $_SESSION['user_id'];

$sql = "SELECT username, email, phone_number, role, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$phone_update_success = false;
$phone_update_error = '';
$email_update_success = false;
$email_update_error = '';
$password_update_success = false;
$password_update_error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST['phone_number'])) {
        $phone_number = trim($_POST['phone_number']);

        if (!empty($phone_number) && !preg_match('/^[0-9 \-+]{9,15}$/', $phone_number)) {
            $phone_update_error = 'Nieprawidłowy format numeru telefonu.';
        } else {
            $sql_update = "UPDATE users SET phone_number = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $phone_to_db = !empty($phone_number) ? $phone_number : NULL;
            $stmt_update->bind_param("si", $phone_to_db, $user_id);

            if ($stmt_update->execute()) {
                $phone_update_success = true;
                $user['phone_number'] = $phone_to_db;
            } else {
                $phone_update_error = 'Błąd podczas aktualizacji. Spróbuj ponownie.';
            }
            $stmt_update->close();
        }
    }

    if (isset($_POST['new_email'])) {
        $new_email = trim($_POST['new_email']);

        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $email_update_error = 'Nieprawidłowy format adresu e-mail.';
        } else {
            $sql_check = "SELECT id FROM users WHERE email = ? AND id != ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("si", $new_email, $user_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                $email_update_error = 'Ten adres e-mail jest już używany przez inne konto.';
            } else {
                $sql_update = "UPDATE users SET email = ? WHERE id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("si", $new_email, $user_id);

                if ($stmt_update->execute()) {
                    $email_update_success = true;
                    $user['email'] = $new_email;
                } else {
                    $email_update_error = 'Błąd podczas aktualizacji. Spróbuj ponownie.';
                }
                $stmt_update->close();
            }
            $stmt_check->close();
        }
    }

    if (isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_new_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_new_password = $_POST['confirm_new_password'];

        $sql_pass = "SELECT password FROM users WHERE id = ?";
        $stmt_pass = $conn->prepare($sql_pass);
        $stmt_pass->bind_param("i", $user_id);
        $stmt_pass->execute();
        $user_data = $stmt_pass->get_result()->fetch_assoc();
        $stmt_pass->close();

        if (!$user_data || !password_verify($current_password, $user_data['password'])) {
            $password_update_error = 'Obecne hasło jest nieprawidłowe.';
        } elseif (strlen($new_password) < 8) {
            $password_update_error = 'Nowe hasło musi mieć co najmniej 8 znaków.';
        } elseif ($new_password !== $confirm_new_password) {
            $password_update_error = 'Nowe hasła nie są identyczne.';
        } else {
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql_update_pass = "UPDATE users SET password = ? WHERE id = ?";
            $stmt_update_pass = $conn->prepare($sql_update_pass);
            $stmt_update_pass->bind_param("si", $hashed_new_password, $user_id);

            if ($stmt_update_pass->execute()) {
                $password_update_success = true;
            } else {
                $password_update_error = 'Wystąpił błąd podczas zmiany hasła. Spróbuj ponownie.';
            }
            $stmt_update_pass->close();
        }
    }
}

$conn->close();
?>

<style>
    main .form-container {
        max-width: 700px;
        background: #ffffff;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        padding: 2rem;
        margin: 2rem auto;
    }

    .settings-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 1rem;
        margin-bottom: 2rem;
    }

    .settings-header h2 {
        margin: 0;
        font-size: 1.8rem;
        color: #2c2c2c;
    }

    .settings-header .back-link {
        font-size: 1.5rem;
        color: #555;
        text-decoration: none;
        transition: color 0.2s;
    }

    .settings-header .back-link:hover {
        color: #0ccb4a;
    }

    .profile-card {
        background: #ffffff;
        border: 1px solid #f0f0f0;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
        margin-bottom: 1.5rem;
    }

    .profile-card h3 {
        font-size: 1.2rem;
        padding: 1.5rem;
        margin: 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .profile-card h3 i {
        margin-right: 0.75rem;
        color: #555;
    }

    .info-group {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f0f0f0;
    }

    .info-group:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: #555;
    }

    .info-value {
        color: #2c2c2c;
    }

    .edit-btn,
    .cancel-btn,
    .submit-btn[style*="width: auto"] {
        background-color: #0ccb4a;
        color: #2c2c2c;
        border: none;
        border-radius: 4px;
        padding: 8px 16px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .edit-btn:hover,
    .submit-btn[style*="width: auto"]:hover {
        background-color: #0ab340;
    }

    .edit-form {
        padding: 1.5rem;
    }

    .form-group label {
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-group input[type="text"],
    .form-group input[type="email"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
    }

    .form-actions {
        display: flex;
        gap: 0.5rem;
        justify-content: flex-start;
        margin-top: 1rem;
    }

    .cancel-btn {
        background-color: #f0f0f0;
    }

    .cancel-btn:hover {
        background-color: #e0e0e0;
    }

    .message {
        padding: 1rem;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 1.5rem;
        font-weight: 500;
    }

    .success-message {
        background-color: #e6ffe6;
        color: #006600;
        border: 1px solid #5cd65c;
    }

    .error-message {
        background-color: #ffeeee;
        color: #cc0000;
        border: 1px solid #ff9999;
    }
</style>

<main>
    <div class="form-container">
        <div class="settings-header">
            <a href="profile.php" class="back-link" title="Wróć do profilu"><i class="fa-solid fa-arrow-left"></i></a>
            <h2>Ustawienia konta</h2>
        </div>

        <?php
        if ($phone_update_success) echo '<div class="message success-message">Numer telefonu został zaktualizowany!</div>';
        if (!empty($phone_update_error)) echo '<div class="message error-message">' . htmlspecialchars($phone_update_error) . '</div>';
        if ($email_update_success) echo '<div class="message success-message">Adres e-mail został zaktualizowany!</div>';
        if ($password_update_success) echo '<div class="message success-message">Hasło zostało pomyślnie zmienione!</div>';
        if (!empty($password_update_error)) echo '<div class="message error-message">' . htmlspecialchars($password_update_error) . '</div>';
        if (!empty($email_update_error)) echo '<div class="message error-message">' . htmlspecialchars($email_update_error) . '</div>';
        ?>

        <div class="profile-card">
            <h3>Informacje o koncie</h3>

            <div class="info-group" id="email-display-group">
                <span class="info-label">Adres e-mail</span>
                <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                <button type="button" class="edit-btn" id="edit-email-btn">Edytuj</button>
            </div>

            <form action="settings.php" method="POST" class="edit-form" id="email-edit-form" style="display: none;">
                <div class="form-group">
                    <label for="new_email">Nowy adres e-mail:</label>
                    <input type="email" id="new_email" name="new_email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                </div>
                <div class="form-actions">
                    <button type="submit" class="submit-btn">Zapisz zmiany</button>
                    <button type="button" class="cancel-btn" id="cancel-email-btn">Anuluj</button>
                </div>
            </form>

            <div class="info-group" id="phone-display-group">
                <span class="info-label">Numer telefonu</span>
                <span class="info-value"><?php echo htmlspecialchars($user['phone_number'] ?? 'Nie podano'); ?></span>
                <button type="button" class="edit-btn" id="edit-phone-btn">Edytuj</button>
            </div>

            <form action="settings.php" method="POST" class="edit-form" id="phone-edit-form" style="display: none;">
                <div class="form-group">
                    <label for="phone_number">Numer telefonu:</label>
                    <input type="text" id="phone_number" name="phone_number" placeholder="np. 123 456 789" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>">
                </div>
                <div class="form-actions">
                    <button type="submit" class="submit-btn">Zapisz zmiany</button>
                    <button type="button" class="cancel-btn" id="cancel-phone-btn">Anuluj</button>
                </div>
            </form>

            <div class="info-group">
                <span class="info-label">Typ konta</span>
                <span class="info-value"><?php echo htmlspecialchars(ucfirst($user['role'])); ?></span>
            </div>
        </div>

        <div class="profile-card">
            <h3><i class="fa-solid fa-lock"></i>Zmiana hasła</h3>
            <form action="settings.php" method="POST" class="edit-form">
                <div class="form-group">
                    <label for="current_password">Obecne hasło</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">Nowe hasło</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_new_password">Potwierdź nowe hasło</label>
                    <input type="password" id="confirm_new_password" name="confirm_new_password" required>
                </div>
                <div class="form-actions" style="justify-content: flex-end;">
                    <button type="submit" class="submit-btn" style="width: auto; font-size: 14px; padding: 8px 16px;">Zmień hasło</button>
                </div>
            </form>
        </div>

        <div class="profile-card">
            <h3>Status konta</h3>
            <div class="info-group">
                <span class="info-label">Data dołączenia</span>
                <span class="info-value">
                    <?php
                    $date = new DateTime($user['created_at']);
                    echo $date->format('d F Y');
                    ?>
                </span>
            </div>
        </div>

        <?php if ($user['role'] === 'admin'): ?>
        <div class="profile-card">
            <h3><i class="fa-solid fa-user-shield"></i>Panel Administratora</h3>
            <div class="info-group">
                <span class="info-label">Zarządzanie bazą filmów</span>
                <a href="add_movie.php" class="submit-btn" style="width: auto; font-size: 14px; padding: 8px 16px; text-decoration: none;">Dodaj film</a>
            </div>
            <div class="info-group">
                <span class="info-label">Przelicz popularność filmów</span>
                <a href="recalculate_popularity.php" target="_blank" class="submit-btn" style="width: auto; font-size: 14px; padding: 8px 16px; text-decoration: none;">Uruchom skrypt</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editPhoneBtn = document.getElementById('edit-phone-btn');
        const cancelPhoneBtn = document.getElementById('cancel-phone-btn');
        const displayPhoneGroup = document.getElementById('phone-display-group');
        const editPhoneForm = document.getElementById('phone-edit-form');

        if (editPhoneBtn) {
            editPhoneBtn.addEventListener('click', () => {
                displayPhoneGroup.style.display = 'none';
                editPhoneForm.style.display = 'block';
            });
            cancelPhoneBtn.addEventListener('click', () => {
                editPhoneForm.style.display = 'none';
                displayPhoneGroup.style.display = 'flex';
            });
        }

        const editEmailBtn = document.getElementById('edit-email-btn');
        const cancelEmailBtn = document.getElementById('cancel-email-btn');
        const displayEmailGroup = document.getElementById('email-display-group');
        const editEmailForm = document.getElementById('email-edit-form');

        if (editEmailBtn) {
            editEmailBtn.addEventListener('click', () => {
                displayEmailGroup.style.display = 'none';
                editEmailForm.style.display = 'block';
            });
            cancelEmailBtn.addEventListener('click', () => {
                editEmailForm.style.display = 'none';
                displayEmailGroup.style.display = 'flex';
            });
        }
    });
</script>
</body>
</html>
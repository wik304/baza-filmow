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
}

$conn->close();
?>

<style>
    main .form-container {
        max-width: 900px;
        background: none;
        box-shadow: none;
        padding: 0;
        margin: 2rem auto;
    }

    .profile-page {
        display: flex;
        gap: 2rem;
    }

    .profile-sidebar {
        flex-basis: 30%;
        min-width: 200px;
    }

    .avatar-card {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        padding: 2rem;
        text-align: center;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 1rem;
        border: 4px solid #f0f0f0;
    }

    .avatar-card h2 {
        font-size: 1.4rem;
        color: #2c2c2c;
        margin: 0;
    }

    .avatar-card .user-role {
        font-size: 0.9rem;
        color: #777;
        text-transform: capitalize;
    }

    .profile-content {
        flex-basis: 70%;
    }

    .profile-card {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }

    .profile-card h3 {
        font-size: 1.2rem;
        padding: 1.5rem;
        margin: 0;
        border-bottom: 1px solid #f0f0f0;
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
    .cancel-btn {
        background: none;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 0.3rem 0.8rem;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .edit-btn:hover {
        background: #f4f4f4;
        border-color: #bbb;
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
        <div class="profile-page">
            <aside class="profile-sidebar">
                <div class="avatar-card">
                    <img src="uploads/avatar-default.png" alt="Avatar użytkownika" class="profile-avatar">
                    <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                    <p class="user-role"><?php echo htmlspecialchars($user['role']); ?></p>
                </div>
            </aside>

            <section class="profile-content">

                <?php
                if ($phone_update_success) {
                    echo '<div class="message success-message">Numer telefonu został zaktualizowany!</div>';
                }
                if (!empty($phone_update_error)) {
                    echo '<div class="message error-message">' . htmlspecialchars($phone_update_error) . '</div>';
                }
                if ($email_update_success) {
                    echo '<div class="message success-message">Adres e-mail został zaktualizowany!</div>';
                }
                if (!empty($email_update_error)) {
                    echo '<div class="message error-message">' . htmlspecialchars($email_update_error) . '</div>';
                }
                ?>

                <div class="profile-card">
                    <h3>Informacje o koncie</h3>

                    <div class="info-group" id="email-display-group">
                        <span class="info-label">Adres e-mail</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                        <button type="button" class="edit-btn" id="edit-email-btn">Edytuj</button>
                    </div>

                    <form action="profile.php" method="POST" class="edit-form" id="email-edit-form" style="display: none;">
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

                    <form action="profile.php" method="POST" class="edit-form" id="phone-edit-form" style="display: none;">
                        <div class="form-group">
                            <label for="phone_number">Numer telefonu:</label>
                            <input type="text" id="phone_number" name="phone_number" placeholder="np. 123 456 789" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>">
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="submit-btn">Zapisz zmiany</button>
                            <button type="button" class="cancel-btn" id="cancel-phone-btn">Anuluj</button>
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

            </section>
        </div>
    </div>
</main>

<?php
include 'footer.php';
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editPhoneBtn = document.getElementById('edit-phone-btn');
        const cancelPhoneBtn = document.getElementById('cancel-phone-btn');
        const displayPhoneGroup = document.getElementById('phone-display-group');
        const editPhoneForm = document.getElementById('phone-edit-form');

        if (editPhoneBtn && cancelPhoneBtn && displayPhoneGroup && editPhoneForm) {
            editPhoneBtn.addEventListener('click', function() {
                displayPhoneGroup.style.display = 'none';
                editPhoneForm.style.display = 'block';
            });

            cancelPhoneBtn.addEventListener('click', function() {
                editPhoneForm.style.display = 'none';
                displayPhoneGroup.style.display = 'flex';
            });
        }

        const editEmailBtn = document.getElementById('edit-email-btn');
        const cancelEmailBtn = document.getElementById('cancel-email-btn');
        const displayEmailGroup = document.getElementById('email-display-group');
        const editEmailForm = document.getElementById('email-edit-form');

        if (editEmailBtn && cancelEmailBtn && displayEmailGroup && editEmailForm) {
            editEmailBtn.addEventListener('click', function() {
                displayEmailGroup.style.display = 'none';
                editEmailForm.style.display = 'block';
            });

            cancelEmailBtn.addEventListener('click', function() {
                editEmailForm.style.display = 'none';
                displayEmailGroup.style.display = 'flex';
            });
        }
    });
</script>

</body>

</html>
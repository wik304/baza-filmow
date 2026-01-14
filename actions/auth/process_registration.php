<?php
session_start();
include '../../config/db_connect.php';
include '../../includes/functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (!isset($_POST['terms'])) {
        $_SESSION['register_form_data'] = $_POST;
        header("Location: ../../register.php?error=terms");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_form_data'] = $_POST;
        header("Location: ../../register.php?error=invalidemail");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['register_form_data'] = $_POST;
        header("Location: ../../register.php?error=passwordcheck");
        exit();
    }

    if (strlen($password) < 12) {
        $_SESSION['register_form_data'] = $_POST;
        header("Location: ../../register.php?error=passwordshort");
        exit();
    }

    if (strlen($username) > 32) {
        $_SESSION['register_form_data'] = $_POST;
        header("Location: ../../register.php?error=usernamelong");
        exit();
    }
    if (strlen($password) > 256) {
        $_SESSION['register_form_data'] = $_POST;
        header("Location: ../../register.php?error=passwordlong");
        exit();
    }

    $sql_check = "SELECT id FROM users WHERE username = ? OR email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ss", $username, $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $stmt_check->close();
        $_SESSION['register_form_data'] = $_POST;
        header("Location: ../../register.php?error=usertaken");
        exit();
    }
    $stmt_check->close();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql_insert = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("sss", $username, $email, $hashed_password);

    if ($stmt_insert->execute()) {
        $new_user_id = $stmt_insert->insert_id;

        $_SESSION['user_id'] = $new_user_id;
        $_SESSION['username'] = $username;
        $_SESSION['user_role'] = 'user';
        $_SESSION['user_avatar_url'] = 'assets/img/avatar-default.png';

        transfer_session_lists_to_db($new_user_id, $conn);

        header("Location: ../../index.php");
        exit();
    } else {
        echo "Błąd: " . $stmt_insert->error;
    }

    $stmt_insert->close();
    $conn->close();
} else {
    header("Location: ../../index.php");
    exit();
}

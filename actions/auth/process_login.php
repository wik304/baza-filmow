<?php
session_start();
include '../../config/db_connect.php';
include '../../includes/functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['login_form_data'] = $_POST;
        header("Location: ../../login.php?error=invalidemail");
        exit();
    }

    $sql = "SELECT id, username, password, role, avatar_url, is_banned FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if ($row['is_banned'] == 1) {
            $_SESSION['login_form_data'] = $_POST;
            header("Location: ../../login.php?error=banned");
            exit();
        }

        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['user_role'] = $row['role'];
            $_SESSION['user_avatar_url'] = $row['avatar_url'];

            transfer_session_lists_to_db($row['id'], $conn);
            include 'migrate_guest_reviews.php';

            header("Location: ../../index.php?action=login_success");
            exit();
        } else {
            $_SESSION['login_form_data'] = $_POST;
            header("Location: ../../login.php?error=wrongpassword");
            exit();
        }
    } else {
        $_SESSION['login_form_data'] = $_POST;
        header("Location: ../../login.php?error=nouser");
        exit();
    }
} else {
    header("Location: ../../login.php");
    exit();
}

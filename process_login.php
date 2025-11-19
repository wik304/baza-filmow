<?php
session_start();
include 'db_connect.php';
include 'functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: login.php?error=invalidemail");
        exit();
    }

    $sql = "SELECT id, username, password, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['user_role'] = $row['role'];

            // Przenieś filmy z sesji gościa do bazy danych
            transfer_session_lists_to_db($row['id'], $conn);

            header("Location: index.php");
            exit();
        } else {
            header("Location: login.php?error=wrongpassword");
            exit();
        }
    } else {
        header("Location: login.php?error=nouser");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}

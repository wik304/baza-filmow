<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: register.php?error=invalidemail");
        exit();
    }

    if ($password !== $confirm_password) {
        header("Location: register.php?error=passwordcheck");
        exit();
    }

    if (strlen($password) < 12) {
        header("Location: register.php?error=passwordshort");
        exit();
    }

    $sql_check = "SELECT id FROM users WHERE username = ? OR email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ss", $username, $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $stmt_check->close();
        $conn->close();
        header("Location: register.php?error=usertaken");
        exit();
    }
    $stmt_check->close();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql_insert = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("sss", $username, $email, $hashed_password);

    if ($stmt_insert->execute()) {
        header("Location: login.php?status=success");
        exit();
    } else {
        echo "Błąd: " . $stmt_insert->error;
    }

    $stmt_insert->close();
    $conn->close();
} else {
    header("Location: index.php");
    exit();
}

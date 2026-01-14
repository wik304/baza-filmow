<?php
session_start();
include '../../config/db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$movie_id = isset($_POST['movie_id']) ? (int)$_POST['movie_id'] : 0;

if ($movie_id > 0 && isset($_FILES['poster_file']) && $_FILES['poster_file']['error'] == 0) {
    $target_dir = "../../uploads/posters/";
    $original_filename = basename($_FILES["poster_file"]["name"]);
    $imageFileType = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));

    $safe_filename = preg_replace('/[^A-Za-z0-9\._-]/', '', pathinfo($original_filename, PATHINFO_FILENAME));
    $unique_filename = $safe_filename . '_' . uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $unique_filename;

    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($imageFileType, $allowed_types)) {
    } elseif ($_FILES["poster_file"]["size"] > 5000000) {
    } else {
        $sql_old_poster = "SELECT poster_url FROM movies WHERE id = ?";
        $stmt_old = $conn->prepare($sql_old_poster);
        $stmt_old->bind_param("i", $movie_id);
        $stmt_old->execute();
        $old_poster_url = $stmt_old->get_result()->fetch_assoc()['poster_url'];
        $stmt_old->close();
        if (!empty($old_poster_url) && file_exists($old_poster_url) && strpos($old_poster_url, 'placeholder.jpg') === false) {
            if (file_exists("../../" . $old_poster_url)) unlink("../../" . $old_poster_url);
        }

        if (move_uploaded_file($_FILES["poster_file"]["tmp_name"], $target_file)) {
            $db_poster_url = "uploads/posters/" . $unique_filename;
            $sql_update = "UPDATE movies SET poster_url = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $db_poster_url, $movie_id);
            $stmt_update->execute();
            $stmt_update->close();
        }
    }
}

$conn->close();
header("Location: edit_movie.php?id=" . $movie_id);
exit();

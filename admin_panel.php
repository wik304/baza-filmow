<?php
session_start();
include 'config/db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'owner'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'move' && isset($_GET['id']) && isset($_GET['direction'])) {
    $announcement_id_to_move = (int)$_GET['id'];
    $direction = $_GET['direction'];

    $stmt_current = $conn->prepare("SELECT display_order FROM announcements WHERE id = ?");
    $stmt_current->bind_param("i", $announcement_id_to_move);
    $stmt_current->execute();
    $current_order_result = $stmt_current->get_result();

    if ($current_order_result->num_rows > 0) {
        $current_order = $current_order_result->fetch_assoc()['display_order'];
        $stmt_current->close();

        if ($current_order !== null) {
            $swap_with_order = ($direction === 'up') ? $current_order - 1 : $current_order + 1;

            if ($swap_with_order > 0) {
                $stmt_swap = $conn->prepare("SELECT id FROM announcements WHERE display_order = ?");
                $stmt_swap->bind_param("i", $swap_with_order);
                $stmt_swap->execute();
                $swap_result = $stmt_swap->get_result();

                if ($swap_result->num_rows > 0) {
                    $announcement_id_to_swap = $swap_result->fetch_assoc()['id'];
                    $stmt_swap->close();

                    $conn->begin_transaction();
                    try {
                        $stmt1 = $conn->prepare("UPDATE announcements SET display_order = ? WHERE id = ?");
                        $stmt1->bind_param("ii", $swap_with_order, $announcement_id_to_move);
                        $stmt1->execute();
                        $stmt1->close();

                        $stmt2 = $conn->prepare("UPDATE announcements SET display_order = ? WHERE id = ?");
                        $stmt2->bind_param("ii", $current_order, $announcement_id_to_swap);
                        $stmt2->execute();
                        $stmt2->close();

                        $conn->commit();
                    } catch (mysqli_sql_exception $exception) {
                        $conn->rollback();
                    }
                }
            }
        }
    }
    header("Location: admin_panel.php?view=announcements");
    exit();
}

include 'includes/header.php';

$current_view = $_GET['view'] ?? 'movies';
$filters = $_GET['filters'] ?? [];

function get_opinion_sort_link($column, $current_column, $current_order, $view_name)
{
    $order = ($current_column === $column && $current_order === 'asc') ? 'desc' : 'asc';
    $page_param = ($view_name === 'opinions') ? 'opinion_page' : 'review_page';
    $sort_param = ($view_name === 'opinions') ? 'opinion_sort' : 'review_sort';
    $order_param = ($view_name === 'opinions') ? 'opinion_order' : 'review_order';

    $params = array_merge($_GET, [
        $sort_param => $column,
        $order_param => $order,
        $page_param => null
    ]);

    return "?" . http_build_query($params);
}

if ($current_view === 'opinions') {
    $opinions_page = isset($_GET['opinion_page']) ? (int)$_GET['opinion_page'] : 1;
    if ($opinions_page < 1) $opinions_page = 1;
    $opinions_limit = 15;
    $opinions_offset = ($opinions_page - 1) * $opinions_limit;

    $opinion_search_query = '';

    $opinion_sort_column = $_GET['opinion_sort'] ?? 'created_at';
    $opinion_sort_order = $_GET['opinion_order'] ?? 'desc';
    $opinion_allowed_sort_columns = ['id', 'username', 'movie_title', 'rating', 'comment', 'created_at'];
    $opinion_sort_column_sql = 'r.created_at';
    if (in_array($opinion_sort_column, $opinion_allowed_sort_columns)) {
        switch ($opinion_sort_column) {
            case 'username':
                $opinion_sort_column_sql = 'u.username';
                break;
            case 'movie_title':
                $opinion_sort_column_sql = 'm.title';
                break;
            default:
                $opinion_sort_column_sql = 'r.' . $opinion_sort_column;
        }
    }
    $opinion_sort_order_sql = (strtolower($opinion_sort_order) === 'asc') ? 'ASC' : 'DESC';

    $opinions_base_sql = "FROM ratings r JOIN users u ON r.user_id = u.id JOIN movies m ON r.movie_id = m.id";
    $opinions_where_sql = " WHERE r.comment IS NOT NULL AND r.comment != '' AND r.rating_type = 'user'";
    $opinion_params = [];
    $opinion_types = '';

    if (!empty($filters)) {
        $conditions = [];
        foreach ($filters as $f) {
            $val = trim(strip_tags($f));
            if (empty($val)) continue;
            $term = "%" . $val . "%";
            $sub_conds = [];
            
            $sub_conds[] = "u.username LIKE ?";
            $sub_conds[] = "u.email LIKE ?";
            $sub_conds[] = "m.title LIKE ?";
            $sub_conds[] = "r.comment LIKE ?";
            $opinion_params[] = $term; $opinion_params[] = $term; $opinion_params[] = $term; $opinion_params[] = $term;
            $opinion_types .= 'ssss';

            if (is_numeric($val)) {
                $sub_conds[] = "r.id = ?";
                $opinion_params[] = (int)$val;
                $opinion_types .= 'i';

                if ($val <= 10) {
                    $sub_conds[] = "r.rating = ?";
                    $opinion_params[] = (int)$val;
                    $opinion_types .= 'i';
                }
            }

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
                $sub_conds[] = "DATE(r.created_at) = ?";
                $opinion_params[] = $val;
                $opinion_types .= 's';
            }
            $conditions[] = "(" . implode(" OR ", $sub_conds) . ")";
        }
        if (!empty($conditions)) {
            $opinions_where_sql .= " AND " . implode(" AND ", $conditions);
        }
    }

    $count_opinions_sql = "SELECT COUNT(r.id) as total " . $opinions_base_sql . $opinions_where_sql;
    $stmt_count_opinions = $conn->prepare($count_opinions_sql);
    if (!empty($opinion_params)) {
        $stmt_count_opinions->bind_param($opinion_types, ...$opinion_params);
    }
    $stmt_count_opinions->execute();
    $total_opinions = $stmt_count_opinions->get_result()->fetch_assoc()['total'];
    $total_opinions_pages = ceil($total_opinions / $opinions_limit);
    $stmt_count_opinions->close();

    $opinions_sql = "SELECT r.id, r.rating, r.comment, r.created_at, u.username, u.email, m.title as movie_title, m.id as movie_id " . $opinions_base_sql . $opinions_where_sql . " ORDER BY $opinion_sort_column_sql $opinion_sort_order_sql LIMIT ? OFFSET ?";
    $stmt_opinions = $conn->prepare($opinions_sql);
    $opinion_params[] = $opinions_limit;
    $opinion_params[] = $opinions_offset;
    $opinion_types .= 'ii';
    $stmt_opinions->bind_param($opinion_types, ...$opinion_params);
    $stmt_opinions->execute();
    $opinions_result = $stmt_opinions->get_result();
    $opinions = $opinions_result->fetch_all(MYSQLI_ASSOC);
}

if ($current_view === 'reviews') {
    $reviews_page = isset($_GET['review_page']) ? (int)$_GET['review_page'] : 1;
    if ($reviews_page < 1) $reviews_page = 1;
    $reviews_limit = 15;
    $reviews_offset = ($reviews_page - 1) * $reviews_limit;

    $review_search_query = '';

    $review_sort_column = $_GET['review_sort'] ?? 'created_at';
    $review_sort_order = $_GET['review_order'] ?? 'desc';
    $review_allowed_sort_columns = ['id', 'username', 'movie_title', 'rating', 'comment', 'created_at'];
    $review_sort_column_sql = 'r.created_at';
    if (in_array($review_sort_column, $review_allowed_sort_columns)) {
        switch ($review_sort_column) {
            case 'username':
                $review_sort_column_sql = 'u.username';
                break;
            case 'movie_title':
                $review_sort_column_sql = 'm.title';
                break;
            default:
                $review_sort_column_sql = 'r.' . $review_sort_column;
        }
    }
    $review_sort_order_sql = (strtolower($review_sort_order) === 'asc') ? 'ASC' : 'DESC';

    $reviews_base_sql = "FROM ratings r JOIN users u ON r.user_id = u.id JOIN movies m ON r.movie_id = m.id";
    $reviews_where_sql = " WHERE r.comment IS NOT NULL AND r.comment != '' AND r.rating_type = 'critic'";
    $review_params = [];
    $review_types = '';

    if (!empty($filters)) {
        $conditions = [];
        foreach ($filters as $f) {
            $val = trim(strip_tags($f));
            if (empty($val)) continue;
            $term = "%" . $val . "%";
            $sub_conds = [];
            
            $sub_conds[] = "u.username LIKE ?";
            $sub_conds[] = "u.email LIKE ?";
            $sub_conds[] = "m.title LIKE ?";
            $sub_conds[] = "r.comment LIKE ?";
            $review_params[] = $term; $review_params[] = $term; $review_params[] = $term; $review_params[] = $term;
            $review_types .= 'ssss';

            if (is_numeric($val)) {
                $sub_conds[] = "r.id = ?";
                $review_params[] = (int)$val;
                $review_types .= 'i';

                if ($val <= 10) {
                    $sub_conds[] = "r.rating = ?";
                    $review_params[] = (int)$val;
                    $review_types .= 'i';
                }
            }

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
                $sub_conds[] = "DATE(r.created_at) = ?";
                $review_params[] = $val;
                $review_types .= 's';
            }
            $conditions[] = "(" . implode(" OR ", $sub_conds) . ")";
        }
        if (!empty($conditions)) {
            $reviews_where_sql .= " AND " . implode(" AND ", $conditions);
        }
    }

    $count_reviews_sql = "SELECT COUNT(r.id) as total " . $reviews_base_sql . $reviews_where_sql;
    $stmt_count_reviews = $conn->prepare($count_reviews_sql);
    if (!empty($review_params)) {
        $stmt_count_reviews->bind_param($review_types, ...$review_params);
    }
    $stmt_count_reviews->execute();
    $total_reviews = $stmt_count_reviews->get_result()->fetch_assoc()['total'];
    $total_reviews_pages = ceil($total_reviews / $reviews_limit);
    $stmt_count_reviews->close();

    $reviews_sql = "SELECT r.id, r.rating, r.comment, r.created_at, u.username, u.email, m.title as movie_title, m.id as movie_id " . $reviews_base_sql . $reviews_where_sql . " ORDER BY $review_sort_column_sql $review_sort_order_sql LIMIT ? OFFSET ?";
    $stmt_reviews = $conn->prepare($reviews_sql);
    $review_params[] = $reviews_limit;
    $review_params[] = $reviews_offset;
    $review_types .= 'ii';
    $stmt_reviews->bind_param($review_types, ...$review_params);
    $stmt_reviews->execute();
    $reviews_result = $stmt_reviews->get_result();
    $reviews = $reviews_result->fetch_all(MYSQLI_ASSOC);
}

$ann_sort_column = $_GET['ann_sort'] ?? 'display_order';
$ann_sort_order = $_GET['ann_order'] ?? 'asc';
$ann_allowed_sort_columns = ['id', 'movie_title', 'release_year', 'is_active', 'background_image_url', 'display_order'];
$ann_sort_column_sql = 'a.display_order';
if (in_array($ann_sort_column, $ann_allowed_sort_columns)) {
    if ($ann_sort_column === 'movie_title') {
        $ann_sort_column_sql = 'm.title';
    } elseif ($ann_sort_column === 'release_year') {
        $ann_sort_column_sql = 'm.release_year';
    } else {
        $ann_sort_column_sql = 'a.' . $ann_sort_column;
    }
}
$ann_sort_order_sql = (strtolower($ann_sort_order) === 'asc') ? 'ASC' : 'DESC';

$announcements_result = $conn->query("
    SELECT a.id, a.background_image_url, a.is_active, a.display_order, m.title as movie_title, m.poster_url, m.release_year 
    FROM announcements a 
    LEFT JOIN movies m ON a.movie_id = m.id 
    ORDER BY $ann_sort_column_sql $ann_sort_order_sql");
$announcements = $announcements_result->fetch_all(MYSQLI_ASSOC);

$user_sort_column = $_GET['user_sort'] ?? 'id';
$user_sort_order = $_GET['user_order'] ?? 'asc';
$user_allowed_sort_columns = ['id', 'username', 'email', 'role', 'created_at'];
$user_sort_column_sql = 'id';
if (in_array($user_sort_column, $user_allowed_sort_columns)) {
    $user_sort_column_sql = $user_sort_column;
}
$user_sort_order_sql = (strtolower($user_sort_order) === 'asc') ? 'ASC' : 'DESC';

$users_page = isset($_GET['user_page']) ? (int)$_GET['user_page'] : 1;
if ($users_page < 1) $users_page = 1;
$users_limit = 10;
$users_offset = ($users_page - 1) * $users_limit;

$user_search_query = '';
$users_base_sql = "FROM users u";
$users_where_sql = "";
$user_params = [];
$user_types = '';
if ($current_view === 'users' && !empty($filters)) {
    $conditions = [];
    foreach ($filters as $f) {
        $val = trim(strip_tags($f));
        if (empty($val)) continue;
        $term = "%" . $val . "%";
        $sub_conds = [];
        
        $sub_conds[] = "u.username LIKE ?";
        $sub_conds[] = "u.email LIKE ?";
        $sub_conds[] = "u.role LIKE ?";
        $user_params[] = $term; $user_params[] = $term; $user_params[] = $term;
        $user_types .= 'sss';

        if (is_numeric($val)) {
            $sub_conds[] = "u.id = ?";
            $user_params[] = (int)$val;
            $user_types .= 'i';
        }

        if (stripos($val, 'zban') !== false) {
            $sub_conds[] = "u.is_banned = 1";
        } elseif (stripos($val, 'aktyw') !== false) {
            $sub_conds[] = "u.is_banned = 0";
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
            $sub_conds[] = "DATE(u.created_at) = ?";
            $user_params[] = $val;
            $user_types .= 's';
        }
        $conditions[] = "(" . implode(" OR ", $sub_conds) . ")";
    }
    if (!empty($conditions)) {
        $users_where_sql = " WHERE " . implode(" AND ", $conditions);
    }
}

$count_users_sql = "SELECT COUNT(*) as total " . $users_base_sql . $users_where_sql;
$stmt_count_users = $conn->prepare($count_users_sql);
if (!empty($user_params)) {
    $stmt_count_users->bind_param($user_types, ...$user_params);
}
$stmt_count_users->execute();
$total_users = $stmt_count_users->get_result()->fetch_assoc()['total'];
$total_users_pages = ceil($total_users / $users_limit);
$stmt_count_users->close();

$users_sql = "SELECT u.id, u.username, u.email, u.role, u.is_banned, u.created_at, u.avatar_url
              " . $users_base_sql . $users_where_sql . "
              ORDER BY $user_sort_column_sql $user_sort_order_sql
              LIMIT ? OFFSET ?";
$stmt_users = $conn->prepare($users_sql);
$user_params[] = $users_limit;
$user_params[] = $users_offset;
$user_types .= 'ii';
$stmt_users->bind_param($user_types, ...$user_params);
$stmt_users->execute();
$users_result = $stmt_users->get_result();
$users = $users_result->fetch_all(MYSQLI_ASSOC);

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$sort_column = $_GET['sort'] ?? 'id';
$sort_order = $_GET['order'] ?? 'desc';

$allowed_sort_columns = ['id', 'title', 'release_year', 'status', 'directors', 'genres'];
$sort_column_sql = 'm.id';
if (in_array($sort_column, $allowed_sort_columns)) {
    if ($sort_column === 'directors' || $sort_column === 'genres') {
        $sort_column_sql = $sort_column;
    } else {
        $sort_column_sql = 'm.' . $sort_column;
    }
}

$sort_order_sql = (strtolower($sort_order) === 'asc') ? 'ASC' : 'DESC';

function get_sort_link($column, $current_column, $current_order)
{
    $order = ($current_column === $column && $current_order === 'asc') ? 'desc' : 'asc';
    $params = http_build_query(array_merge($_GET, ['sort' => $column, 'order' => $order, 'page' => null]));
    return "?" . $params;
}

function get_ann_sort_link($column, $current_column, $current_order)
{
    $order = ($current_column === $column && $current_order === 'asc') ? 'desc' : 'asc';
    return "?view=announcements&ann_sort=$column&ann_order=$order";
}

function get_user_sort_link($column, $current_column, $current_order)
{
    $order = ($current_column === $column && $current_order === 'asc') ? 'desc' : 'asc';
    $params = http_build_query(array_merge($_GET, ['user_sort' => $column, 'user_order' => $order, 'user_page' => null]));
    return "?" . $params;
}

function get_pagination_url()
{
    $params = http_build_query(array_merge($_GET, ['page' => null]));
    return "?" . $params;
}

if ($page < 1) $page = 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$movies = [];
$total_pages = 1;

if ($current_view === 'movies') {
$sql_base = "FROM movies m 
             LEFT JOIN movie_directors md ON m.id = md.movie_id
             LEFT JOIN directors d ON md.director_id = d.director_id
             LEFT JOIN movie_genres mg ON m.id = mg.movie_id
             LEFT JOIN genres g ON mg.genre_id = g.genre_id";
$params = [];
$types = '';

$where_conditions = [];

if (!empty($filters)) {
    foreach ($filters as $type => $values) {
        $value = trim(strip_tags($values));
        if (empty($value)) continue;

        $term_conditions = [];
        $term = "%" . $value . "%";

        $term_conditions[] = "m.title LIKE ?";
        $params[] = $term;
        $types .= 's';

        $status_value = null;
        if (stripos('dostępny', $value) !== false || stripos('dostepny', $value) !== false) {
            $status_value = 'available';
        } elseif (stripos('nadchodzący', $value) !== false || stripos('nadchodzacy', $value) !== false) {
            $status_value = 'upcoming';
        }
        if ($status_value) {
            $term_conditions[] = "m.status = ?";
            $params[] = $status_value;
            $types .= 's';
        }

        $term_conditions[] = "m.id IN (SELECT md.movie_id FROM movie_directors md JOIN directors d ON md.director_id = d.director_id WHERE d.full_name LIKE ?)";
        $params[] = $term;
        $types .= 's';

        $term_conditions[] = "m.id IN (SELECT mg.movie_id FROM movie_genres mg JOIN genres g ON mg.genre_id = g.genre_id WHERE g.name LIKE ?)";
        $params[] = $term;
        $types .= 's';

        if (is_numeric($value)) {
            $term_conditions[] = "m.id = ?";
            $params[] = (int)$value;
            $types .= 'i';
            if (strlen($value) == 4) {
                $term_conditions[] = "m.release_year = ?";
                $params[] = (int)$value;
                $types .= 'i';
            }
        }
        $where_conditions[] = "(" . implode(' OR ', $term_conditions) . ")";
    }
}

$where_clause = !empty($where_conditions) ? " WHERE " . implode(' AND ', $where_conditions) : '';

$sql_count = "SELECT COUNT(DISTINCT m.id) as total " . $sql_base . $where_clause;
$stmt_count = $conn->prepare($sql_count);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_movies = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_movies / $limit);
$stmt_count->close();

$sql_movies = "SELECT m.id, m.title, m.release_year, m.status, m.poster_url,
                      GROUP_CONCAT(DISTINCT d.full_name SEPARATOR ', ') AS directors,
                      GROUP_CONCAT(DISTINCT g.name SEPARATOR ', ') AS genres
               " . $sql_base . $where_clause . "
               GROUP BY m.id, m.title, m.release_year, m.status, m.poster_url
               ORDER BY " . $sort_column_sql . " " . $sort_order_sql . "
               LIMIT ? OFFSET ?";

$stmt_movies = $conn->prepare($sql_movies);
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';
$stmt_movies->bind_param($types, ...$params);
$stmt_movies->execute();
$result_movies = $stmt_movies->get_result();
$movies = [];
if ($result_movies) {
    while ($row = $result_movies->fetch_assoc()) {
        $movies[] = $row;
    }
}
$stmt_movies->close();
}
$conn->close();
?>

<link rel="stylesheet" href="assets/css/admin_panel.css">

<main>
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fa-solid fa-user-shield"></i> Panel Administratora</h1>
        </div>

        <div class="view-toggle-buttons">
            <a href="?view=announcements" class="view-toggle-btn <?php if ($current_view === 'announcements') echo 'active'; ?>">
                <i class="fa-solid fa-bullhorn"></i> Ogłoszenia
            </a>
            <a href="?view=movies" class="view-toggle-btn <?php if ($current_view === 'movies') echo 'active'; ?>">
                <i class="fa-solid fa-film"></i> Filmy
            </a>
            <a href="?view=users" class="view-toggle-btn <?php if ($current_view === 'users') echo 'active'; ?>">
                <i class="fa-solid fa-users-cog"></i> Użytkownicy
            </a>
            <a href="?view=opinions" class="view-toggle-btn <?php if ($current_view === 'opinions') echo 'active'; ?>">
                <i class="fa-solid fa-comments"></i> Opinie
            </a>
            <a href="?view=reviews" class="view-toggle-btn <?php if ($current_view === 'reviews') echo 'active'; ?>">
                <i class="fa-solid fa-feather-alt"></i> Recenzje
            </a>
        </div>

        <?php if ($current_view === 'users'): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Zarządzanie użytkownikami</h2>
                </div>
                <div class="filter-container">
                    <form action="admin_panel.php" method="GET" id="filter-form" class="filter-form">
                        <input type="hidden" name="view" value="users">
                        <div class="filter-input-group">
                            <input type="text" id="filter-value" placeholder="Szukaj (ID, nazwa, email)...">
                            <button type="button" id="add-filter-btn"><i class="fa-solid fa-plus"></i> Dodaj filtr</button>
                        </div>
                        <div id="filter-tags-container" class="filter-tags-container"></div>
                        <div id="hidden-filters-container"></div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="movies-table users-table">
                        <thead>
                            <tr>
                                <th class="sortable-header">
                                    <a href="<?php echo get_user_sort_link('id', $user_sort_column, $user_sort_order); ?>">
                                        ID <?php if ($user_sort_column === 'id') echo $user_sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th>Awatar</th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_user_sort_link('username', $user_sort_column, $user_sort_order); ?>">
                                        Nazwa użytkownika <?php if ($user_sort_column === 'username') echo $user_sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_user_sort_link('email', $user_sort_column, $user_sort_order); ?>">
                                        Email <?php if ($user_sort_column === 'email') echo $user_sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_user_sort_link('role', $user_sort_column, $user_sort_order); ?>">
                                        Rola <?php if ($user_sort_column === 'role') echo $user_sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th>Status</th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_user_sort_link('created_at', $user_sort_column, $user_sort_order); ?>">
                                        Data dołączenia <?php if ($user_sort_column === 'created_at') echo $user_sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td data-label="ID"><?php echo $user['id']; ?></td>
                                    <td data-label="Awatar"><img src="<?php echo htmlspecialchars($user['avatar_url'] ?? 'assets/img/avatar-default.png'); ?>" alt="Awatar" class="table-avatar"></td>
                                    <td data-label="Nazwa użytkownika"><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td data-label="Email"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td data-label="Rola">
                                        <?php
                                        $is_disabled = false;
                                        if ($user['id'] == $_SESSION['user_id']) $is_disabled = true;
                                        if ($_SESSION['user_role'] === 'admin' && ($user['role'] === 'admin' || $user['role'] === 'owner')) $is_disabled = true;
                                        ?>
                                        <select class="user-role-select" data-user-id="<?php echo $user['id']; ?>" <?php if ($is_disabled) echo 'disabled'; ?>>
                                            <option value="user" <?php if ($user['role'] === 'user') echo 'selected'; ?>>User</option>
                                            <option value="critic" <?php if ($user['role'] === 'critic') echo 'selected'; ?>>Critic</option>
                                            <option value="admin" <?php if ($user['role'] === 'admin') echo 'selected'; ?>>Admin</option>
                                            <option value="owner" <?php if ($user['role'] === 'owner') echo 'selected'; ?>>Owner</option>
                                        </select>
                                    </td>
                                    <td data-label="Status">
                                        <?php
                                        $is_ban_disabled = false;
                                        if ($user['id'] == $_SESSION['user_id']) $is_ban_disabled = true;
                                        if ($_SESSION['user_role'] === 'admin' && ($user['role'] === 'admin' || $user['role'] === 'owner')) $is_ban_disabled = true;
                                        if ($user['role'] === 'owner') $is_ban_disabled = true;
                                        ?>
                                        <select class="user-role-select user-status-select" data-user-id="<?php echo $user['id']; ?>" <?php if ($is_ban_disabled) echo 'disabled'; ?>>
                                            <?php if ($is_ban_disabled): ?>
                                                <option value="0" selected>Chroniony</option>
                                            <?php else: ?>
                                                <option value="0" <?php if (!$user['is_banned']) echo 'selected'; ?>>Aktywny</option>
                                                <option value="1" <?php if ($user['is_banned']) echo 'selected'; ?>>Zbanowany</option>
                                            <?php endif; ?>
                                        </select>
                                    </td>
                                    <td data-label="Data dołączenia"><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($total_users_pages > 1): ?>
                    <div class="pagination">
                        <?php $base_url = "?view=users&user_sort=" . $user_sort_column . "&user_order=" . $user_sort_order . (empty($filters) ? '' : '&' . http_build_query(['filters' => $filters])); ?>
                        <?php if ($users_page > 1): ?>
                            <a href="<?php echo $base_url; ?>&user_page=<?php echo $users_page - 1; ?>">&laquo; Poprzednia</a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $total_users_pages; $i++): ?>
                            <a href="<?php echo $base_url; ?>&user_page=<?php echo $i; ?>" class="<?php if ($i == $users_page) echo 'current-page'; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        <?php if ($users_page < $total_users_pages): ?>
                            <a href="<?php echo $base_url; ?>&user_page=<?php echo $users_page + 1; ?>">Następna &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($current_view === 'opinions'): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Zarządzanie opiniami użytkowników</h2>
                </div>
                <div class="filter-container">
                    <form action="admin_panel.php" method="GET" id="filter-form" class="filter-form">
                        <input type="hidden" name="view" value="opinions">
                        <div class="filter-input-group">
                            <input type="text" id="filter-value" placeholder="Szukaj (ID, użytkownik, film)...">
                            <button type="button" id="add-filter-btn"><i class="fa-solid fa-plus"></i> Dodaj filtr</button>
                        </div>
                        <div id="filter-tags-container" class="filter-tags-container"></div>
                        <div id="hidden-filters-container"></div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="admin-table reviews-table">
                        <thead>
                            <tr>
                                <th class="sortable-header">
                                    <a href="<?php echo get_opinion_sort_link('id', $opinion_sort_column, $opinion_sort_order, 'opinions'); ?>">
                                        ID <?php if ($opinion_sort_column === 'id') echo $opinion_sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_opinion_sort_link('username', $opinion_sort_column, $opinion_sort_order, 'opinions'); ?>">
                                        Użytkownik <?php if ($opinion_sort_column === 'username') echo $opinion_sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_opinion_sort_link('movie_title', $opinion_sort_column, $opinion_sort_order, 'opinions'); ?>">
                                        Film <?php if ($opinion_sort_column === 'movie_title') echo $opinion_sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_opinion_sort_link('rating', $opinion_sort_column, $opinion_sort_order, 'opinions'); ?>">
                                        Ocena <?php if ($opinion_sort_column === 'rating') echo $opinion_sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_opinion_sort_link('comment', $opinion_sort_column, $opinion_sort_order, 'opinions'); ?>">
                                        Treść recenzji <?php if ($opinion_sort_column === 'comment') echo $opinion_sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_opinion_sort_link('created_at', $opinion_sort_column, $opinion_sort_order, 'opinions'); ?>">
                                        Data <?php if ($opinion_sort_column === 'created_at') echo $opinion_sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($opinions as $opinion): ?>
                                <tr>
                                    <td data-label="ID"><?php echo $opinion['id']; ?></td>
                                    <td data-label="Użytkownik"><?php echo htmlspecialchars($opinion['username']); ?><br><small><?php echo htmlspecialchars($opinion['email']); ?></small></td>
                                    <td data-label="Film"><a href="movie.php?id=<?php echo $opinion['movie_id']; ?>" target="_blank" class="movie-link"><?php echo htmlspecialchars($opinion['movie_title']); ?></a></td>
                                    <td data-label="Ocena"><?php echo (int)$opinion['rating']; ?>/10</td>
                                    <td data-label="Treść opinii" class="comment-cell"><?php echo htmlspecialchars($opinion['comment']); ?></td>
                                    <td data-label="Data"><?php echo date('d.m.Y H:i', strtotime($opinion['created_at'])); ?></td>
                                    <td data-label="Akcje" class="actions-cell">
                                        <a href="actions/reviews/edit_review.php?id=<?php echo $opinion['id']; ?>&from=opinions" class="action-btn edit-btn" title="Edytuj"><i class="fa-solid fa-pen-to-square"></i></a>
                                        <a href="actions/reviews/delete_review.php?id=<?php echo $opinion['id']; ?>&from=opinions" class="action-btn delete-btn" title="Usuń" onclick="return confirm('Czy na pewno chcesz usunąć tę opinię?');"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($total_opinions_pages > 1): ?>
                    <div class="pagination">
                        <?php $base_url = "?view=opinions&opinion_sort=" . $opinion_sort_column . "&opinion_order=" . $opinion_sort_order . (empty($filters) ? '' : '&' . http_build_query(['filters' => $filters])); ?>
                        <?php if ($opinions_page > 1): ?>
                            <a href="<?php echo $base_url; ?>&opinion_page=<?php echo $opinions_page - 1; ?>">&laquo; Poprzednia</a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $total_opinions_pages; $i++): ?>
                            <a href="<?php echo $base_url; ?>&opinion_page=<?php echo $i; ?>" class="<?php if ($i == $opinions_page) echo 'current-page'; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        <?php if ($opinions_page < $total_opinions_pages): ?>
                            <a href="<?php echo $base_url; ?>&opinion_page=<?php echo $opinions_page + 1; ?>">Następna &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($current_view === 'reviews'): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Zarządzanie recenzjami krytyków</h2>
                </div>
                <div class="filter-container">
                    <form action="admin_panel.php" method="GET" id="filter-form" class="filter-form">
                        <input type="hidden" name="view" value="reviews">
                        <div class="filter-input-group">
                            <input type="text" id="filter-value" placeholder="Szukaj (ID, krytyk, film)...">
                            <button type="button" id="add-filter-btn"><i class="fa-solid fa-plus"></i> Dodaj filtr</button>
                        </div>
                        <div id="filter-tags-container" class="filter-tags-container"></div>
                        <div id="hidden-filters-container"></div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="admin-table reviews-table">
                        <thead>
                            <tr>
                                <th class="sortable-header">
                                    <a href="<?php echo get_opinion_sort_link('id', $review_sort_column, $review_sort_order, 'reviews'); ?>">
                                        ID <?php if ($review_sort_column === 'id') echo $review_sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_opinion_sort_link('username', $review_sort_column, $review_sort_order, 'reviews'); ?>">
                                        Krytyk <?php if ($review_sort_column === 'username') echo $review_sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_opinion_sort_link('movie_title', $review_sort_column, $review_sort_order, 'reviews'); ?>">
                                        Film <?php if ($review_sort_column === 'movie_title') echo $review_sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_opinion_sort_link('rating', $review_sort_column, $review_sort_order, 'reviews'); ?>">
                                        Ocena <?php if ($review_sort_column === 'rating') echo $review_sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_opinion_sort_link('comment', $review_sort_column, $review_sort_order, 'reviews'); ?>">
                                        Treść recenzji <?php if ($review_sort_column === 'comment') echo $review_sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_opinion_sort_link('created_at', $review_sort_column, $review_sort_order, 'reviews'); ?>">
                                        Data <?php if ($review_sort_column === 'created_at') echo $review_sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $review): ?>
                                <tr>
                                    <td data-label="ID"><?php echo $review['id']; ?></td>
                                    <td data-label="Krytyk"><?php echo htmlspecialchars($review['username']); ?><br><small><?php echo htmlspecialchars($review['email']); ?></small></td>
                                    <td data-label="Film"><a href="movie.php?id=<?php echo $review['movie_id']; ?>" target="_blank" class="movie-link"><?php echo htmlspecialchars($review['movie_title']); ?></a></td>
                                    <td data-label="Ocena"><?php echo (int)$review['rating']; ?>/10</td>
                                    <td data-label="Treść recenzji" class="comment-cell"><?php echo htmlspecialchars($review['comment']); ?></td>
                                    <td data-label="Data"><?php echo date('d.m.Y H:i', strtotime($review['created_at'])); ?></td>
                                    <td data-label="Akcje" class="actions-cell">
                                        <a href="actions/reviews/edit_review.php?id=<?php echo $review['id']; ?>&from=reviews" class="action-btn edit-btn" title="Edytuj"><i class="fa-solid fa-pen-to-square"></i></a>
                                        <a href="actions/reviews/delete_review.php?id=<?php echo $review['id']; ?>&from=reviews" class="action-btn delete-btn" title="Usuń" onclick="return confirm('Czy na pewno chcesz usunąć tę recenzję?');"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($total_reviews_pages > 1): ?>
                    <div class="pagination">
                        <?php $base_url = "?view=reviews&review_sort=" . $review_sort_column . "&review_order=" . $review_sort_order . (empty($filters) ? '' : '&' . http_build_query(['filters' => $filters])); ?>
                        <?php if ($reviews_page > 1): ?>
                            <a href="<?php echo $base_url; ?>&review_page=<?php echo $reviews_page - 1; ?>">&laquo; Poprzednia</a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $total_reviews_pages; $i++): ?>
                            <a href="<?php echo $base_url; ?>&review_page=<?php echo $i; ?>" class="<?php if ($i == $reviews_page) echo 'current-page'; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        <?php if ($reviews_page < $total_reviews_pages): ?>
                            <a href="<?php echo $base_url; ?>&review_page=<?php echo $reviews_page + 1; ?>">Następna &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($current_view === 'announcements'): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Zarządzanie ogłoszeniami</h2>
                    <a href="actions/announcements/add_announcement.php" class="admin-btn add-new-btn"><i class="fa-solid fa-plus"></i> Dodaj nowe ogłoszenie</a>
                </div>
                <div class="table-responsive">
                    <table class="movies-table announcements-table">
                        <thead>
                            <tr>
                                <th class="sortable-header">
                                    <a href="<?php echo get_ann_sort_link('id', $ann_sort_column, $ann_sort_order); ?>">
                                        ID <?php if ($ann_sort_column === 'id') echo $ann_sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th>Plakat</th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_ann_sort_link('movie_title', $ann_sort_column, $ann_sort_order); ?>">
                                        Film <?php if ($ann_sort_column === 'movie_title') echo $ann_sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_ann_sort_link('release_year', $ann_sort_column, $ann_sort_order); ?>">
                                        Rok <?php if ($ann_sort_column === 'release_year') echo $ann_sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_ann_sort_link('is_active', $ann_sort_column, $ann_sort_order); ?>">
                                        Aktywne <?php if ($ann_sort_column === 'is_active') echo $ann_sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_ann_sort_link('background_image_url', $ann_sort_column, $ann_sort_order); ?>">
                                        Ścieżka do grafiki <?php if ($ann_sort_column === 'background_image_url') echo $ann_sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_ann_sort_link('display_order', $ann_sort_column, $ann_sort_order); ?>">
                                        Kolejność <?php if ($ann_sort_column === 'display_order') echo $ann_sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody id="announcements-tbody">
                            <?php foreach ($announcements as $index => $ann): ?>
                                <tr data-id="<?php echo $ann['id']; ?>" class="<?php if (!$ann['is_active']) echo 'inactive-announcement'; ?>">
                                    <td data-label="ID"><?php echo $ann['id']; ?></td>
                                    <td data-label="Plakat"><img src="<?php echo htmlspecialchars($ann['poster_url'] ?? 'uploads/posters/placeholder.jpg'); ?>" alt="Plakat" class="table-poster"></td>
                                    <td data-label="Film"><?php echo htmlspecialchars($ann['movie_title']); ?></td>
                                    <td data-label="Rok"><?php echo htmlspecialchars($ann['release_year']); ?></td>
                                    <td data-label="Aktywne">
                                        <div class="status-toggle" data-announcement-id="<?php echo $ann['id']; ?>">
                                            <span class="toggle-option <?php if ($ann['is_active']) echo 'active'; ?>" data-status="1">Tak</span>
                                            <span class="toggle-option <?php if (!$ann['is_active']) echo 'active'; ?>" data-status="0">Nie</span>
                                        </div>
                                    </td>
                                    <td data-label="Ścieżka do grafiki"><?php echo htmlspecialchars($ann['background_image_url']); ?></td>
                                    <td data-label="Kolejność">
                                        <div class="order-controls">
                                            <span><?php echo $ann['is_active'] ? $ann['display_order'] : '-'; ?></span>
                                            <a href="?view=announcements&action=move&id=<?php echo $ann['id']; ?>&direction=up" class="order-btn d-lg-none" title="Przesuń w górę">↑</a>
                                            <a href="?view=announcements&action=move&id=<?php echo $ann['id']; ?>&direction=down" class="order-btn d-lg-none" title="Przesuń w dół">↓</a>
                                        </div>
                                    </td>
                                    <td data-label="Akcje" class="actions-cell">
                                        <a href="actions/announcements/edit_announcement.php?id=<?php echo $ann['id']; ?>" class="action-btn edit-btn" title="Edytuj"><i class="fa-solid fa-pen-to-square"></i></a>
                                        <a href="actions/announcements/delete_announcement.php?id=<?php echo $ann['id']; ?>" class="action-btn delete-btn" title="Usuń" onclick="return confirm('Czy na pewno chcesz usunąć to ogłoszenie?');"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($current_view === 'movies'): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Zarządzanie filmami</h2>
                    <a href="actions/movies/add_movie.php" class="admin-btn add-new-btn"><i class="fa-solid fa-plus"></i> Dodaj nowy film</a>
                </div>
                <div class="filter-container">
                    <form action="admin_panel.php" method="GET" id="filter-form" class="filter-form">
                        <input type="hidden" name="view" value="movies">
                        <div class="filter-input-group">
                            <input type="text" id="filter-value" placeholder="Wpisz frazę i naciśnij Enter...">
                            <button type="button" id="add-filter-btn"><i class="fa-solid fa-plus"></i> Dodaj filtr</button>
                        </div>
                        <div id="filter-tags-container" class="filter-tags-container"></div>
                        <div id="hidden-filters-container"></div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="movies-table">
                        <thead>
                            <tr>
                                <th class="sortable-header">
                                    <a href="<?php echo get_sort_link('id', $sort_column, $sort_order); ?>">
                                        ID <?php if ($sort_column === 'id') echo $sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th>Plakat</th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_sort_link('title', $sort_column, $sort_order); ?>">
                                        Tytuł <?php if ($sort_column === 'title') echo $sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_sort_link('release_year', $sort_column, $sort_order); ?>">
                                        Rok <?php if ($sort_column === 'release_year') echo $sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_sort_link('status', $sort_column, $sort_order); ?>">
                                        Status <?php if ($sort_column === 'status') echo $sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_sort_link('directors', $sort_column, $sort_order); ?>">
                                        Reżyserzy <?php if ($sort_column === 'directors') echo $sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th class="sortable-header">
                                    <a href="<?php echo get_sort_link('genres', $sort_column, $sort_order); ?>">
                                        Gatunki <?php if ($sort_column === 'genres') echo $sort_order === 'asc' ? '▲' : '▼'; ?>
                                    </a>
                                </th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($movies)): ?>
                                <?php foreach ($movies as $movie): ?>
                                    <tr>
                                        <td data-label="ID"><?php echo $movie['id']; ?></td>
                                        <td data-label="Plakat"><img src="<?php echo htmlspecialchars($movie['poster_url'] ?? 'uploads/posters/placeholder.jpg'); ?>" alt="Plakat" class="table-poster"></td>
                                        <td data-label="Tytuł"><?php echo htmlspecialchars($movie['title']); ?></td>
                                        <td data-label="Rok"><?php echo htmlspecialchars($movie['release_year']); ?></td>
                                        <td data-label="Status">
                                            <span class="status-badge status-<?php echo htmlspecialchars($movie['status']); ?>">
                                                <?php echo $movie['status'] === 'available' ? 'Dostępny' : 'Nadchodzący'; ?>
                                            </span>
                                        </td>
                                        <td data-label="Reżyserzy"><?php echo htmlspecialchars($movie['directors'] ?? 'Brak'); ?></td>
                                        <td data-label="Gatunki"><?php echo htmlspecialchars($movie['genres'] ?? 'Brak'); ?></td>
                                        <td data-label="Akcje" class="actions-cell">
                                            <a href="actions/movies/edit_movie.php?id=<?php echo $movie['id']; ?>" class="action-btn edit-btn" title="Edytuj"><i class="fa-solid fa-pen-to-square"></i></a>
                                            <a href="actions/movies/delete_movie.php?id=<?php echo $movie['id']; ?>" class="action-btn delete-btn" title="Usuń" onclick="return confirm('Czy na pewno chcesz usunąć ten film? Tej operacji nie można cofnąć.');"><i class="fa-solid fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 2rem;">Nie znaleziono filmów pasujących do kryteriów.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="<?php echo get_pagination_url(); ?>&page=<?php echo $page - 1; ?>">&laquo; Poprzednia</a>
                        <?php endif; ?>

                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        $base_url = get_pagination_url();

                        if ($start_page > 1) {
                            echo '<a href="' . $base_url . '&page=1">1</a>';
                            if ($start_page > 2) {
                                echo '<span>...</span>';
                            }
                        }

                        for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <a href="<?php echo $base_url; ?>&page=<?php echo $i; ?>" class="<?php if ($i == $page) echo 'current-page'; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>

                        <?php
                        if ($end_page < $total_pages) {
                            if ($end_page < $total_pages - 1) {
                                echo '<span>...</span>';
                            }
                            echo '<a href="' . $base_url . '&page=' . $total_pages . '">' . $total_pages . '</a>';
                        }
                        ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="<?php echo get_pagination_url(); ?>&page=<?php echo $page + 1; ?>">Następna &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
    window.appConfig = {
        currentFilters: <?php echo json_encode($filters); ?>
    };
</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="assets/js/admin_panel.js"></script>

<?php include 'includes/footer.php'; ?>

</body>

</html>
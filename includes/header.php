<?php


if (!isset($pageTitle)) $pageTitle = 'Flood Relief Management System';
$role = $_SESSION['role'] ?? '';
$name = htmlspecialchars($_SESSION['full_name'] ?? 'User');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — FRMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-inner">
        <a class="nav-brand" href="<?= BASE_URL ?>/index.php">
            <span class="brand-icon">⛵</span>
            <span>FRMS</span>
        </a>

        <ul class="nav-links">
            <?php if ($role === 'admin'): ?>
                <li><a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
                <li><a href="<?= BASE_URL ?>/admin/users.php">Users</a></li>
                <li><a href="<?= BASE_URL ?>/admin/reports.php">Reports</a></li>
            <?php elseif ($role === 'user'): ?>
                <li><a href="<?= BASE_URL ?>/user/dashboard.php">Dashboard</a></li>
                <li><a href="<?= BASE_URL ?>/user/create_request.php">New Request</a></li>
                <li><a href="<?= BASE_URL ?>/user/view_requests.php">My Requests</a></li>
            <?php endif; ?>
        </ul>

        <div class="nav-user">
            <span class="nav-name"><?= $name ?></span>
            <span class="nav-badge <?= $role ?>"><?= ucfirst($role) ?></span>
            <a class="btn-logout" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
        </div>
    </div>
</nav>

<main class="main-content">

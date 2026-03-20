<?php

session_start();
define('BASE_URL', '/flood_relief');

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/admin_check.php';
require_once __DIR__ . '/../db.php';

$id = (int)($_GET['id'] ?? 0);


if ($id === (int)$_SESSION['user_id']) {
    $_SESSION['popup'] = ['message' => 'You cannot delete your own account.', 'type' => 'error'];
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}


$check = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$check->execute([$id]);
$target = $check->fetch();

if (!$target) {
    $_SESSION['popup'] = ['message' => 'User not found.', 'type' => 'error'];
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

if ($target['role'] === 'admin') {
    $_SESSION['popup'] = ['message' => 'Admin accounts cannot be deleted from here.', 'type' => 'error'];
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}


$del = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
$del->execute([$id]);

if ($del->rowCount() > 0) {
    $_SESSION['popup'] = ['message' => 'User and all associated requests have been deleted.', 'type' => 'success'];
} else {
    $_SESSION['popup'] = ['message' => 'Could not delete the user. Please try again.', 'type' => 'error'];
}

header('Location: ' . BASE_URL . '/admin/users.php');
exit;

<?php

session_start();
define('BASE_URL', '/flood_relief');


if (!empty($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
    } else {
        header('Location: ' . BASE_URL . '/user/dashboard.php');
    }
} else {
    header('Location: ' . BASE_URL . '/auth/login.php');
}
exit;

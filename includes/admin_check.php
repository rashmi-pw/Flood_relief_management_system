<?php
// ============================================================
// includes/admin_check.php — require admin role
// Include AFTER auth_check.php on every admin page
// ============================================================

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/user/dashboard.php');
    exit;
}

<?php

session_start();
define('BASE_URL', '/flood_relief');

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../db.php';

$uid = $_SESSION['user_id'];
$id  = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('DELETE FROM relief_requests WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $uid]);

if ($stmt->rowCount() > 0) {
    $_SESSION['popup'] = ['message' => 'Relief request deleted successfully.', 'type' => 'success'];
} else {
    $_SESSION['popup'] = ['message' => 'Request not found or you do not have permission to delete it.', 'type' => 'error'];
}

header('Location: ' . BASE_URL . '/user/view_requests.php');
exit;

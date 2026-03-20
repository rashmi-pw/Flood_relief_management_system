<?php

session_start();
define('BASE_URL', '/flood_relief');

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/admin_check.php';
require_once __DIR__ . '/../db.php';

$pageTitle = 'Registered Users';

$search = trim($_GET['q'] ?? '');
$params = [];
$where  = "WHERE role = 'user'";

if ($search !== '') {
    $where   .= ' AND (full_name LIKE ? OR email LIKE ? OR nic LIKE ?)';
    $like     = '%' . $search . '%';
    $params   = [$like, $like, $like];
}

$stmt = $pdo->prepare("
    SELECT u.id, u.full_name, u.email, u.nic, u.phone, u.created_at,
           COUNT(r.id) AS request_count
    FROM users u
    LEFT JOIN relief_requests r ON r.user_id = u.id
    $where
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$stmt->execute($params);
$users = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Registered Users</h1>
        <p class="page-sub"><?= count($users) ?> user<?= count($users) !== 1 ? 's' : '' ?> found</p>
    </div>
</div>

<!-- Search bar -->
<div class="card search-card">
    <form method="GET" action="">
        <div class="search-row">
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
                   placeholder="Search by name, email or NIC…" class="search-input">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search): ?>
                <a href="users.php" class="btn btn-outline">Clear</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <?php if (empty($users)): ?>
        <div class="empty-state"><p>No users found.</p></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>NIC</th>
                        <th>Phone</th>
                        <th>Requests</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['full_name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['nic']) ?></td>
                        <td><?= htmlspecialchars($u['phone']) ?></td>
                        <td><span class="badge badge-count"><?= $u['request_count'] ?></span></td>
                        <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                        <td class="action-cell">
                            <a href="<?= BASE_URL ?>/admin/user_detail.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline">View</a>
                            <a href="<?= BASE_URL ?>/admin/delete_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-danger"
                               onclick="return confirmDelete(event, 'Delete user &quot;<?= addslashes(htmlspecialchars($u['full_name'])) ?>&quot; and all their requests?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

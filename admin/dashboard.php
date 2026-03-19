<?php

session_start();
define('BASE_URL', '/flood_relief');

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/admin_check.php';
require_once __DIR__ . '/../db.php';

$pageTitle = 'Admin Dashboard';

$stats = $pdo->query('
    SELECT
        (SELECT COUNT(*) FROM users WHERE role = "user")             AS total_users,
        (SELECT COUNT(*) FROM relief_requests)                       AS total_requests,
        (SELECT COUNT(*) FROM relief_requests WHERE severity = "High")   AS high_sev,
        (SELECT COUNT(*) FROM relief_requests WHERE severity = "Medium") AS medium_sev,
        (SELECT COUNT(*) FROM relief_requests WHERE severity = "Low")    AS low_sev,
        (SELECT COUNT(*) FROM relief_requests WHERE relief_type = "Food")     AS food,
        (SELECT COUNT(*) FROM relief_requests WHERE relief_type = "Water")    AS water,
        (SELECT COUNT(*) FROM relief_requests WHERE relief_type = "Medicine") AS medicine,
        (SELECT COUNT(*) FROM relief_requests WHERE relief_type = "Shelter")  AS shelter
')->fetch();

$newUsers = $pdo->query('
    SELECT id, full_name, email, created_at
    FROM users WHERE role = "user"
    ORDER BY created_at DESC LIMIT 5
')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Admin Dashboard</h1>
        <p class="page-sub">System overview — <?= date('d F Y') ?></p>
    </div>
    <a href="<?= BASE_URL ?>/admin/reports.php" class="btn btn-primary">View Reports</a>
</div>

<!-- System stat cards -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-value"><?= (int)$stats['total_users'] ?></div>
        <div class="stat-label">Registered Users</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= (int)$stats['total_requests'] ?></div>
        <div class="stat-label">Total Requests</div>
    </div>
    <div class="stat-card stat-danger">
        <div class="stat-value"><?= (int)$stats['high_sev'] ?></div>
        <div class="stat-label">High Severity</div>
    </div>
    <div class="stat-card stat-warning">
        <div class="stat-value"><?= (int)$stats['medium_sev'] ?></div>
        <div class="stat-label">Medium Severity</div>
    </div>
</div>

<!-- Relief type breakdown -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Requests by Type</h2>
    </div>
    <div class="type-grid">
        <?php
        $types = [
            'Food'     => ['icon' => '🍚', 'count' => $stats['food'],     'color' => 'amber'],
            'Water'    => ['icon' => '💧', 'count' => $stats['water'],    'color' => 'blue'],
            'Medicine' => ['icon' => '💊', 'count' => $stats['medicine'], 'color' => 'green'],
            'Shelter'  => ['icon' => '🏠', 'count' => $stats['shelter'],  'color' => 'purple'],
        ];
        foreach ($types as $label => $data): ?>
            <div class="type-card type-<?= $data['color'] ?>">
                <div class="type-icon"><?= $data['icon'] ?></div>
                <div class="type-count"><?= (int)$data['count'] ?></div>
                <div class="type-label"><?= $label ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Recent users -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Recent Registrations</h2>
        <a href="<?= BASE_URL ?>/admin/users.php" class="link-sm">View all users →</a>
    </div>
    <?php if (empty($newUsers)): ?>
        <div class="empty-state"><p>No users registered yet.</p></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th>#</th><th>Name</th><th>Email</th><th>Registered</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($newUsers as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['full_name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                        <td class="action-cell">
                            <a href="<?= BASE_URL ?>/admin/user_detail.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

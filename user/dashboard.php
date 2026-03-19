<?php

session_start();
define('BASE_URL', '/flood_relief');

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../db.php';

$pageTitle = 'My Dashboard';
$uid = $_SESSION['user_id'];

$counts = $pdo->prepare('
    SELECT
        COUNT(*)                                      AS total,
        SUM(severity = "High")                        AS high,
        SUM(severity = "Medium")                      AS medium,
        SUM(severity = "Low")                         AS low
    FROM relief_requests WHERE user_id = ?
');
$counts->execute([$uid]);
$stats = $counts->fetch();

$latest = $pdo->prepare('
    SELECT id, relief_type, district, severity, created_at
    FROM relief_requests
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 5
');
$latest->execute([$uid]);
$requests = $latest->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">My Dashboard</h1>
        <p class="page-sub">Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?></p>
    </div>
    <a href="<?= BASE_URL ?>/user/create_request.php" class="btn btn-primary">+ New Request</a>
</div>

<!-- Stats -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-value"><?= (int)$stats['total'] ?></div>
        <div class="stat-label">Total Requests</div>
    </div>
    <div class="stat-card stat-danger">
        <div class="stat-value"><?= (int)$stats['high'] ?></div>
        <div class="stat-label">High Severity</div>
    </div>
    <div class="stat-card stat-warning">
        <div class="stat-value"><?= (int)$stats['medium'] ?></div>
        <div class="stat-label">Medium Severity</div>
    </div>
    <div class="stat-card stat-success">
        <div class="stat-value"><?= (int)$stats['low'] ?></div>
        <div class="stat-label">Low Severity</div>
    </div>
</div>

<!-- Recent requests -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Recent Requests</h2>
        <a href="<?= BASE_URL ?>/user/view_requests.php" class="link-sm">View all →</a>
    </div>

    <?php if (empty($requests)): ?>
        <div class="empty-state">
            <p>You have not submitted any relief requests yet.</p>
            <a href="<?= BASE_URL ?>/user/create_request.php" class="btn btn-primary">Submit your first request</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>District</th>
                        <th>Severity</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $r): ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><span class="badge badge-type"><?= htmlspecialchars($r['relief_type']) ?></span></td>
                        <td><?= htmlspecialchars($r['district']) ?></td>
                        <td><span class="badge badge-severity badge-<?= strtolower($r['severity']) ?>"><?= $r['severity'] ?></span></td>
                        <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                        <td class="action-cell">
                            <a href="<?= BASE_URL ?>/user/edit_request.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                            <a href="<?= BASE_URL ?>/user/delete_request.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-danger"
                               onclick="return confirmDelete(event, 'Are you sure you want to delete this request?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

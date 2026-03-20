<?php
session_start();
define('BASE_URL', '/flood_relief');

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../db.php';

$pageTitle = 'My Relief Requests';
$uid = $_SESSION['user_id'];

$stmt = $pdo->prepare('
    SELECT id, relief_type, district, divisional_sec, gn_division,
           contact_name, family_members, severity, created_at
    FROM relief_requests
    WHERE user_id = ?
    ORDER BY created_at DESC
');
$stmt->execute([$uid]);
$requests = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">My Relief Requests</h1>
        <p class="page-sub">All requests you have submitted</p>
    </div>
    <a href="<?= BASE_URL ?>/user/create_request.php" class="btn btn-primary">+ New Request</a>
</div>

<div class="card">
    <?php if (empty($requests)): ?>
        <div class="empty-state">
            <p>You have not submitted any requests yet.</p>
            <a href="<?= BASE_URL ?>/user/create_request.php" class="btn btn-primary">Submit a request</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>District</th>
                        <th>Div. Secretariat</th>
                        <th>GN Division</th>
                        <th>Contact</th>
                        <th>Family</th>
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
                        <td><?= htmlspecialchars($r['divisional_sec']) ?></td>
                        <td><?= htmlspecialchars($r['gn_division']) ?></td>
                        <td><?= htmlspecialchars($r['contact_name']) ?></td>
                        <td><?= $r['family_members'] ?></td>
                        <td><span class="badge badge-severity badge-<?= strtolower($r['severity']) ?>"><?= $r['severity'] ?></span></td>
                        <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                        <td class="action-cell">
                            <a href="<?= BASE_URL ?>/user/edit_request.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                            <a href="<?= BASE_URL ?>/user/delete_request.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-danger"
                               onclick="return confirmDelete(event, 'Delete this relief request? This cannot be undone.')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

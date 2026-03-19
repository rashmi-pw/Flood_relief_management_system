<?php

session_start();
define('BASE_URL', '/flood_relief');

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/admin_check.php';
require_once __DIR__ . '/../db.php';

$id = (int)($_GET['id'] ?? 0);

$uStmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'user'");
$uStmt->execute([$id]);
$user = $uStmt->fetch();

if (!$user) {
    $_SESSION['popup'] = ['message' => 'User not found.', 'type' => 'error'];
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}


$rStmt = $pdo->prepare('SELECT * FROM relief_requests WHERE user_id = ? ORDER BY created_at DESC');
$rStmt->execute([$id]);
$requests = $rStmt->fetchAll();

$sumStmt = $pdo->prepare('
    SELECT
        COUNT(*)                                         AS total,
        SUM(severity = "High")                           AS high,
        SUM(severity = "Medium")                         AS medium,
        SUM(severity = "Low")                            AS low,
        SUM(relief_type = "Food")                        AS food,
        SUM(relief_type = "Water")                       AS water,
        SUM(relief_type = "Medicine")                    AS medicine,
        SUM(relief_type = "Shelter")                     AS shelter,
        SUM(family_members)                              AS total_family
    FROM relief_requests WHERE user_id = ?
');
$sumStmt->execute([$id]);
$summary = $sumStmt->fetch();

$pageTitle = 'User Report — ' . htmlspecialchars($user['full_name']);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">User Report</h1>
        <p class="page-sub">Detailed summary for <?= htmlspecialchars($user['full_name']) ?></p>
    </div>
    <div class="header-actions">
        <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-outline">← Back to Users</a>
        <a href="<?= BASE_URL ?>/admin/delete_user.php?id=<?= $user['id'] ?>" class="btn btn-danger"
           onclick="return confirmDelete(event, 'Permanently delete this user and all their data?')">Delete User</a>
    </div>
</div>

<!-- User info card -->
<div class="report-card">
    <div class="report-header">
        <div class="report-avatar"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></div>
        <div class="report-title-block">
            <h2 class="report-name"><?= htmlspecialchars($user['full_name']) ?></h2>
            <span class="badge badge-role-user">Affected Person</span>
        </div>
    </div>

    <div class="report-info-grid">
        <div class="report-info-item">
            <span class="info-label">Email</span>
            <span class="info-value"><?= htmlspecialchars($user['email']) ?></span>
        </div>
        <div class="report-info-item">
            <span class="info-label">NIC</span>
            <span class="info-value"><?= htmlspecialchars($user['nic']) ?></span>
        </div>
        <div class="report-info-item">
            <span class="info-label">Phone</span>
            <span class="info-value"><?= htmlspecialchars($user['phone']) ?></span>
        </div>
        <div class="report-info-item">
            <span class="info-label">Registered</span>
            <span class="info-value"><?= date('d F Y, g:i a', strtotime($user['created_at'])) ?></span>
        </div>
    </div>
</div>

<!-- Request summary -->
<div class="report-card">
    <h3 class="report-section-title">Request Summary</h3>
    <div class="stat-grid">
        <div class="stat-card"><div class="stat-value"><?= (int)$summary['total'] ?></div><div class="stat-label">Total Requests</div></div>
        <div class="stat-card stat-danger"><div class="stat-value"><?= (int)$summary['high'] ?></div><div class="stat-label">High Severity</div></div>
        <div class="stat-card stat-warning"><div class="stat-value"><?= (int)$summary['medium'] ?></div><div class="stat-label">Medium Severity</div></div>
        <div class="stat-card stat-success"><div class="stat-value"><?= (int)$summary['low'] ?></div><div class="stat-label">Low Severity</div></div>
    </div>

    <div class="summary-row" style="margin-top:1.5rem">
        <div class="summary-chip">🍚 Food: <strong><?= (int)$summary['food'] ?></strong></div>
        <div class="summary-chip">💧 Water: <strong><?= (int)$summary['water'] ?></strong></div>
        <div class="summary-chip">💊 Medicine: <strong><?= (int)$summary['medicine'] ?></strong></div>
        <div class="summary-chip">🏠 Shelter: <strong><?= (int)$summary['shelter'] ?></strong></div>
        <div class="summary-chip">👥 Total family members affected: <strong><?= (int)$summary['total_family'] ?></strong></div>
    </div>
</div>

<!-- Full request list -->
<div class="report-card">
    <h3 class="report-section-title">All Relief Requests</h3>

    <?php if (empty($requests)): ?>
        <div class="empty-state"><p>This user has not submitted any requests.</p></div>
    <?php else: ?>
        <?php foreach ($requests as $idx => $r): ?>
        <div class="request-detail-block">
            <div class="request-detail-header">
                <span class="rdb-num">Request #<?= $r['id'] ?></span>
                <span class="badge badge-type"><?= htmlspecialchars($r['relief_type']) ?></span>
                <span class="badge badge-severity badge-<?= strtolower($r['severity']) ?>"><?= $r['severity'] ?></span>
                <span class="rdb-date"><?= date('d M Y, g:i a', strtotime($r['created_at'])) ?></span>
            </div>
            <div class="rdb-grid">
                <div class="rdb-item"><span class="info-label">District</span><span class="info-value"><?= htmlspecialchars($r['district']) ?></span></div>
                <div class="rdb-item"><span class="info-label">Div. Secretariat</span><span class="info-value"><?= htmlspecialchars($r['divisional_sec']) ?></span></div>
                <div class="rdb-item"><span class="info-label">GN Division</span><span class="info-value"><?= htmlspecialchars($r['gn_division']) ?></span></div>
                <div class="rdb-item"><span class="info-label">Contact Person</span><span class="info-value"><?= htmlspecialchars($r['contact_name']) ?></span></div>
                <div class="rdb-item"><span class="info-label">Contact Number</span><span class="info-value"><?= htmlspecialchars($r['contact_number']) ?></span></div>
                <div class="rdb-item"><span class="info-label">Address</span><span class="info-value"><?= htmlspecialchars($r['address']) ?></span></div>
                <div class="rdb-item"><span class="info-label">Family Members</span><span class="info-value"><?= $r['family_members'] ?></span></div>
                <?php if ($r['updated_at'] !== $r['created_at']): ?>
                <div class="rdb-item"><span class="info-label">Last Updated</span><span class="info-value"><?= date('d M Y, g:i a', strtotime($r['updated_at'])) ?></span></div>
                <?php endif; ?>
            </div>
            <?php if (!empty($r['description'])): ?>
            <div class="rdb-desc">
                <span class="info-label">Description</span>
                <p><?= nl2br(htmlspecialchars($r['description'])) ?></p>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

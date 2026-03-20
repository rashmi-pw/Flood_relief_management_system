<?php

session_start();
define('BASE_URL', '/flood_relief');

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/admin_check.php';
require_once __DIR__ . '/../db.php';

$pageTitle = 'System Reports';

$districts = [
    '','Ampara','Anuradhapura','Badulla','Batticaloa','Colombo',
    'Galle','Gampaha','Hambantota','Jaffna','Kalutara',
    'Kandy','Kegalle','Kilinochchi','Kurunegala','Mannar',
    'Matale','Matara','Monaragala','Mullaitivu','Nuwara Eliya',
    'Polonnaruwa','Puttalam','Ratnapura','Trincomalee','Vavuniya'
];

$fDistrict = $_GET['district']     ?? '';
$fType     = $_GET['relief_type']  ?? '';
$fSeverity = $_GET['severity']     ?? '';

$allowed_types    = ['Food','Water','Medicine','Shelter'];
$allowed_severity = ['Low','Medium','High'];

if (!in_array($fType, $allowed_types))       $fType     = '';
if (!in_array($fSeverity, $allowed_severity)) $fSeverity = '';
if (!in_array($fDistrict, $districts))        $fDistrict = '';

$whereParts = [];
$params     = [];

if ($fDistrict !== '') { $whereParts[] = 'r.district = ?';     $params[] = $fDistrict; }
if ($fType     !== '') { $whereParts[] = 'r.relief_type = ?';  $params[] = $fType; }
if ($fSeverity !== '') { $whereParts[] = 'r.severity = ?';     $params[] = $fSeverity; }

$where = $whereParts ? 'WHERE ' . implode(' AND ', $whereParts) : '';

$summarySQL = "
    SELECT
        COUNT(DISTINCT u.id)                                  AS total_users,
        COUNT(r.id)                                           AS total_requests,
        SUM(r.severity = 'High')                              AS high_sev,
        SUM(r.severity = 'Medium')                            AS medium_sev,
        SUM(r.severity = 'Low')                               AS low_sev,
        SUM(r.relief_type = 'Food')                           AS food,
        SUM(r.relief_type = 'Water')                          AS water,
        SUM(r.relief_type = 'Medicine')                       AS medicine,
        SUM(r.relief_type = 'Shelter')                        AS shelter,
        COALESCE(SUM(r.family_members), 0)                    AS total_family
    FROM users u
    LEFT JOIN relief_requests r ON r.user_id = u.id
    $where
    AND u.role = 'user'
";

$summaryWhere = [];
$summaryParams = [];
if ($fDistrict !== '') { $summaryWhere[] = 'r.district = ?';    $summaryParams[] = $fDistrict; }
if ($fType     !== '') { $summaryWhere[] = 'r.relief_type = ?'; $summaryParams[] = $fType; }
if ($fSeverity !== '') { $summaryWhere[] = 'r.severity = ?';    $summaryParams[] = $fSeverity; }

$summarySQL = "
    SELECT
        (SELECT COUNT(*) FROM users WHERE role = 'user') AS total_users,
        COUNT(r.id)                                      AS total_requests,
        SUM(r.severity = 'High')                         AS high_sev,
        SUM(r.severity = 'Medium')                       AS medium_sev,
        SUM(r.severity = 'Low')                          AS low_sev,
        SUM(r.relief_type = 'Food')                      AS food,
        SUM(r.relief_type = 'Water')                     AS water,
        SUM(r.relief_type = 'Medicine')                  AS medicine,
        SUM(r.relief_type = 'Shelter')                   AS shelter,
        COALESCE(SUM(r.family_members), 0)               AS total_family
    FROM relief_requests r
" . ($summaryWhere ? 'WHERE ' . implode(' AND ', $summaryWhere) : '');

$sumStmt = $pdo->prepare($summarySQL);
$sumStmt->execute($summaryParams);
$summary = $sumStmt->fetch();

$byDistrictSQL = "
    SELECT district,
           COUNT(*)                    AS total,
           SUM(severity = 'High')      AS high,
           SUM(severity = 'Medium')    AS medium,
           SUM(severity = 'Low')       AS low,
           SUM(family_members)         AS families
    FROM relief_requests
    " . ($where ? str_replace('r.', '', $where) : '') . "
    GROUP BY district
    ORDER BY total DESC
";
$byDistrictStmt = $pdo->prepare($byDistrictSQL);
$byDistrictStmt->execute($params);
$byDistrict = $byDistrictStmt->fetchAll();

$byTypeSQL = "
    SELECT relief_type,
           COUNT(*)                    AS total,
           SUM(severity = 'High')      AS high,
           SUM(family_members)         AS families
    FROM relief_requests
    " . ($where ? str_replace('r.', '', $where) : '') . "
    GROUP BY relief_type
    ORDER BY total DESC
";
$byTypeStmt = $pdo->prepare($byTypeSQL);
$byTypeStmt->execute($params);
$byType = $byTypeStmt->fetchAll();


$detailSQL = "
    SELECT r.id, r.relief_type, r.district, r.divisional_sec, r.gn_division,
           r.contact_name, r.contact_number, r.family_members, r.severity,
           r.created_at, u.full_name AS user_name
    FROM relief_requests r
    JOIN users u ON u.id = r.user_id
    $where
    ORDER BY r.created_at DESC
    LIMIT 100
";
$detailStmt = $pdo->prepare($detailSQL);
$detailStmt->execute($params);
$details = $detailStmt->fetchAll();

$isFiltered = ($fDistrict !== '' || $fType !== '' || $fSeverity !== '');

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">System Reports</h1>
        <p class="page-sub">Filter and view summarized relief data</p>
    </div>
    <div class="header-actions">
        <?php if ($isFiltered): ?>
            <a href="reports.php" class="btn btn-outline">Clear Filters</a>
        <?php endif; ?>
    </div>
</div>

<!-- Filter form -->
<div class="card filter-card">
    <h3 class="filter-title">Filter Options</h3>
    <form method="GET" action="">
        <div class="filter-row">
            <div class="form-group">
                <label for="district">District</label>
                <select id="district" name="district">
                    <option value="">All Districts</option>
                    <?php foreach (array_filter($districts) as $d): ?>
                        <option value="<?= $d ?>" <?= $fDistrict === $d ? 'selected' : '' ?>><?= $d ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="relief_type">Relief Type</label>
                <select id="relief_type" name="relief_type">
                    <option value="">All Types</option>
                    <?php foreach (['Food','Water','Medicine','Shelter'] as $t): ?>
                        <option value="<?= $t ?>" <?= $fType === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="severity">Severity</label>
                <select id="severity" name="severity">
                    <option value="">All Severities</option>
                    <?php foreach (['Low','Medium','High'] as $s): ?>
                        <option value="<?= $s ?>" <?= $fSeverity === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-btn-wrap">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </div>
    </form>
</div>

<?php if ($isFiltered): ?>
<div class="filter-active-bar">
    <span>Showing results for:</span>
    <?php if ($fDistrict): ?><span class="filter-chip">📍 <?= htmlspecialchars($fDistrict) ?></span><?php endif; ?>
    <?php if ($fType):     ?><span class="filter-chip">📦 <?= htmlspecialchars($fType) ?></span><?php endif; ?>
    <?php if ($fSeverity): ?><span class="filter-chip">⚠️ <?= htmlspecialchars($fSeverity) ?> Severity</span><?php endif; ?>
</div>
<?php endif; ?>

<!-- Summary counts -->
<div class="report-card">
    <h3 class="report-section-title">Summary</h3>
    <div class="summary-kpi-grid">
        <div class="kpi-block">
            <div class="kpi-value"><?= (int)$summary['total_users'] ?></div>
            <div class="kpi-label">Total Registered Users</div>
        </div>
        <div class="kpi-block">
            <div class="kpi-value"><?= (int)$summary['total_requests'] ?></div>
            <div class="kpi-label">Matching Requests</div>
        </div>
        <div class="kpi-block kpi-danger">
            <div class="kpi-value"><?= (int)$summary['high_sev'] ?></div>
            <div class="kpi-label">High Severity Households</div>
        </div>
        <div class="kpi-block kpi-warning">
            <div class="kpi-value"><?= (int)$summary['medium_sev'] ?></div>
            <div class="kpi-label">Medium Severity Households</div>
        </div>
        <div class="kpi-block kpi-success">
            <div class="kpi-value"><?= (int)$summary['low_sev'] ?></div>
            <div class="kpi-label">Low Severity Households</div>
        </div>
        <div class="kpi-block">
            <div class="kpi-value"><?= (int)$summary['total_family'] ?></div>
            <div class="kpi-label">Total Family Members Affected</div>
        </div>
    </div>

    <div class="summary-row" style="margin-top:1.5rem">
        <div class="summary-chip">🍚 Food Requests: <strong><?= (int)$summary['food'] ?></strong></div>
        <div class="summary-chip">💧 Water Requests: <strong><?= (int)$summary['water'] ?></strong></div>
        <div class="summary-chip">💊 Medicine Requests: <strong><?= (int)$summary['medicine'] ?></strong></div>
        <div class="summary-chip">🏠 Shelter Requests: <strong><?= (int)$summary['shelter'] ?></strong></div>
    </div>
</div>

<!-- By district -->
<?php if (!empty($byDistrict)): ?>
<div class="report-card">
    <h3 class="report-section-title">Breakdown by District</h3>
    <div class="table-wrap">
        <table class="data-table report-table">
            <thead>
                <tr>
                    <th>District</th>
                    <th>Total Requests</th>
                    <th>High</th>
                    <th>Medium</th>
                    <th>Low</th>
                    <th>Families Affected</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($byDistrict as $row): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['district']) ?></strong></td>
                    <td><?= (int)$row['total'] ?></td>
                    <td><span class="badge badge-severity badge-high"><?= (int)$row['high'] ?></span></td>
                    <td><span class="badge badge-severity badge-medium"><?= (int)$row['medium'] ?></span></td>
                    <td><span class="badge badge-severity badge-low"><?= (int)$row['low'] ?></span></td>
                    <td><?= (int)$row['families'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- By type -->
<?php if (!empty($byType)): ?>
<div class="report-card">
    <h3 class="report-section-title">Breakdown by Relief Type</h3>
    <div class="table-wrap">
        <table class="data-table report-table">
            <thead>
                <tr>
                    <th>Relief Type</th>
                    <th>Total Requests</th>
                    <th>High Severity</th>
                    <th>Families Affected</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($byType as $row): ?>
                <tr>
                    <td><span class="badge badge-type"><?= htmlspecialchars($row['relief_type']) ?></span></td>
                    <td><?= (int)$row['total'] ?></td>
                    <td><span class="badge badge-severity badge-high"><?= (int)$row['high'] ?></span></td>
                    <td><?= (int)$row['families'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Detailed records -->
<div class="report-card">
    <h3 class="report-section-title">
        Individual Records
        <span class="count-label"><?= count($details) ?> record<?= count($details) !== 1 ? 's' : '' ?></span>
    </h3>
    <?php if (empty($details)): ?>
        <div class="empty-state"><p>No records match the selected filters.</p></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table report-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Submitted By</th>
                        <th>Type</th>
                        <th>District</th>
                        <th>Div. Secretariat</th>
                        <th>GN Division</th>
                        <th>Contact</th>
                        <th>Family</th>
                        <th>Severity</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($details as $r): ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><?= htmlspecialchars($r['user_name']) ?></td>
                        <td><span class="badge badge-type"><?= htmlspecialchars($r['relief_type']) ?></span></td>
                        <td><?= htmlspecialchars($r['district']) ?></td>
                        <td><?= htmlspecialchars($r['divisional_sec']) ?></td>
                        <td><?= htmlspecialchars($r['gn_division']) ?></td>
                        <td><?= htmlspecialchars($r['contact_name']) ?><br><small><?= htmlspecialchars($r['contact_number']) ?></small></td>
                        <td><?= $r['family_members'] ?></td>
                        <td><span class="badge badge-severity badge-<?= strtolower($r['severity']) ?>"><?= $r['severity'] ?></span></td>
                        <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

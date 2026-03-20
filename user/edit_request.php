<?php
session_start();
define('BASE_URL', '/flood_relief');

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../db.php';

$pageTitle = 'Edit Relief Request';
$uid = $_SESSION['user_id'];
$id  = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM relief_requests WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $uid]);
$req = $stmt->fetch();

if (!$req) {
    $_SESSION['popup'] = ['message' => 'Request not found or access denied.', 'type' => 'error'];
    header('Location: ' . BASE_URL . '/user/view_requests.php');
    exit;
}

$districts = [
    'Ampara','Anuradhapura','Badulla','Batticaloa','Colombo',
    'Galle','Gampaha','Hambantota','Jaffna','Kalutara',
    'Kandy','Kegalle','Kilinochchi','Kurunegala','Mannar',
    'Matale','Matara','Monaragala','Mullaitivu','Nuwara Eliya',
    'Polonnaruwa','Puttalam','Ratnapura','Trincomalee','Vavuniya'
];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = [
        'relief_type'    => $_POST['relief_type']    ?? '',
        'district'       => $_POST['district']       ?? '',
        'divisional_sec' => trim($_POST['divisional_sec'] ?? ''),
        'gn_division'    => trim($_POST['gn_division']    ?? ''),
        'contact_name'   => trim($_POST['contact_name']   ?? ''),
        'contact_number' => trim($_POST['contact_number'] ?? ''),
        'address'        => trim($_POST['address']        ?? ''),
        'family_members' => (int)($_POST['family_members'] ?? 0),
        'severity'       => $_POST['severity']       ?? '',
        'description'    => trim($_POST['description']    ?? ''),
    ];

    $allowed_types    = ['Food','Water','Medicine','Shelter'];
    $allowed_severity = ['Low','Medium','High'];

    if (!in_array($values['relief_type'], $allowed_types)) {
        $error = 'Please select a valid type of relief.';
    } elseif (!in_array($values['district'], $districts)) {
        $error = 'Please select a valid district.';
    } elseif ($values['divisional_sec'] === '' || $values['gn_division'] === '') {
        $error = 'Divisional Secretariat and GN Division are required.';
    } elseif ($values['contact_name'] === '' || $values['contact_number'] === '') {
        $error = 'Contact person name and number are required.';
    } elseif ($values['address'] === '') {
        $error = 'Address is required.';
    } elseif ($values['family_members'] < 1) {
        $error = 'Number of family members must be at least 1.';
    } elseif (!in_array($values['severity'], $allowed_severity)) {
        $error = 'Please select a valid severity level.';
    } else {
        $upd = $pdo->prepare('
            UPDATE relief_requests SET
                relief_type    = ?,
                district       = ?,
                divisional_sec = ?,
                gn_division    = ?,
                contact_name   = ?,
                contact_number = ?,
                address        = ?,
                family_members = ?,
                severity       = ?,
                description    = ?
            WHERE id = ? AND user_id = ?
        ');
        $upd->execute([
            $values['relief_type'],
            $values['district'],
            $values['divisional_sec'],
            $values['gn_division'],
            $values['contact_name'],
            $values['contact_number'],
            $values['address'],
            $values['family_members'],
            $values['severity'],
            $values['description'],
            $id,
            $uid,
        ]);

        $_SESSION['popup'] = ['message' => 'Your relief request has been updated successfully.', 'type' => 'success'];
        header('Location: ' . BASE_URL . '/user/view_requests.php');
        exit;
    }

    $req = array_merge($req, $values);
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Edit Request #<?= $id ?></h1>
        <p class="page-sub">Update your relief request details</p>
    </div>
    <a href="<?= BASE_URL ?>/user/view_requests.php" class="btn btn-outline">← Back</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <form method="POST" action="" novalidate id="requestForm">

        <div class="form-section">
            <h3 class="form-section-title">Relief Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="relief_type">Type of Relief <span class="req">*</span></label>
                    <select id="relief_type" name="relief_type" required>
                        <option value="">— Select type —</option>
                        <?php foreach (['Food','Water','Medicine','Shelter'] as $t): ?>
                            <option value="<?= $t ?>" <?= $req['relief_type'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="severity">Flood Severity Level <span class="req">*</span></label>
                    <select id="severity" name="severity" required>
                        <option value="">— Select severity —</option>
                        <?php foreach (['Low','Medium','High'] as $s): ?>
                            <option value="<?= $s ?>" <?= $req['severity'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3 class="form-section-title">Location Details</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="district">District <span class="req">*</span></label>
                    <select id="district" name="district" required>
                        <option value="">— Select district —</option>
                        <?php foreach ($districts as $d): ?>
                            <option value="<?= $d ?>" <?= $req['district'] === $d ? 'selected' : '' ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="divisional_sec">Divisional Secretariat <span class="req">*</span></label>
                    <input type="text" id="divisional_sec" name="divisional_sec"
                           value="<?= htmlspecialchars($req['divisional_sec']) ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="gn_division">GN Division <span class="req">*</span></label>
                    <input type="text" id="gn_division" name="gn_division"
                           value="<?= htmlspecialchars($req['gn_division']) ?>" required>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3 class="form-section-title">Household Details</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="contact_name">Contact Person Name <span class="req">*</span></label>
                    <input type="text" id="contact_name" name="contact_name"
                           value="<?= htmlspecialchars($req['contact_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="contact_number">Contact Number <span class="req">*</span></label>
                    <input type="tel" id="contact_number" name="contact_number"
                           value="<?= htmlspecialchars($req['contact_number']) ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="address">Address <span class="req">*</span></label>
                    <input type="text" id="address" name="address"
                           value="<?= htmlspecialchars($req['address']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="family_members">Number of Family Members <span class="req">*</span></label>
                    <input type="number" id="family_members" name="family_members"
                           value="<?= (int)$req['family_members'] ?>" min="1" max="50" required>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3 class="form-section-title">Additional Details</h3>
            <div class="form-group">
                <label for="description">Description / Special Requirements</label>
                <textarea id="description" name="description" rows="4"><?= htmlspecialchars($req['description']) ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <a href="<?= BASE_URL ?>/user/view_requests.php" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>

    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

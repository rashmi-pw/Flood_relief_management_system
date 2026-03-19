<?php

session_start();
define('BASE_URL', '/flood_relief');

require_once __DIR__ . '/../db.php';

$error  = '';
$values = [];

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email'     => trim($_POST['email']     ?? ''),
        'nic'       => trim($_POST['nic']        ?? ''),
        'phone'     => trim($_POST['phone']      ?? ''),
        'password'  => $_POST['password']        ?? '',
        'confirm'   => $_POST['confirm']         ?? '',
    ];

    
    if (in_array('', [$values['full_name'], $values['email'], $values['nic'], $values['phone'], $values['password']], true)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($values['password']) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($values['password'] !== $values['confirm']) {
        $error = 'Passwords do not match.';
    } else {
        
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR nic = ?');
        $stmt->execute([$values['email'], $values['nic']]);
        if ($stmt->fetch()) {
            $error = 'An account with that email or NIC already exists.';
        } else {
            $hash = password_hash($values['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $ins  = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, nic, phone, role) VALUES (?,?,?,?,?,?)');
            $ins->execute([$values['full_name'], $values['email'], $hash, $values['nic'], $values['phone'], 'user']);

            
            $uid = $pdo->lastInsertId();
            session_regenerate_id(true);
            $_SESSION['user_id']   = $uid;
            $_SESSION['full_name'] = $values['full_name'];
            $_SESSION['role']      = 'user';
            $_SESSION['popup']     = ['message' => 'Registration successful. Welcome, ' . $values['full_name'] . '!', 'type' => 'success'];

            header('Location: ' . BASE_URL . '/user/dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — FRMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="auth-body">

<div class="auth-split">
    <div class="auth-panel auth-brand">
        <div class="brand-content">
            <div class="brand-logo">⛵</div>
            <h1 class="brand-title">Flood Relief<br>Management<br>System</h1>
            <p class="brand-sub">University of Sri Jayewardenepura<br>CSC 1051 — Full-Stack Fundamentals</p>
        </div>
        <div class="brand-wave"></div>
    </div>

    <div class="auth-panel auth-form-panel">
        <div class="auth-form-wrap">
            <h2 class="auth-heading">Create an account</h2>
            <p class="auth-sub">Register to submit flood relief requests</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="" novalidate id="registerForm">

                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name"
                               value="<?= htmlspecialchars($values['full_name'] ?? '') ?>"
                               placeholder="Kamal Perera" required>
                    </div>
                    <div class="form-group">
                        <label for="nic">NIC Number</label>
                        <input type="text" id="nic" name="nic"
                               value="<?= htmlspecialchars($values['nic'] ?? '') ?>"
                               placeholder="200012345678" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email"
                               value="<?= htmlspecialchars($values['email'] ?? '') ?>"
                               placeholder="you@example.com" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone"
                               value="<?= htmlspecialchars($values['phone'] ?? '') ?>"
                               placeholder="07XXXXXXXX" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <input type="password" id="password" name="password"
                                   placeholder="Min. 8 characters" required>
                            <button type="button" class="toggle-pw" onclick="togglePw('password', this)" aria-label="Show password">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirm">Confirm Password</label>
                        <div class="input-wrap">
                            <input type="password" id="confirm" name="confirm"
                                   placeholder="Repeat password" required>
                            <button type="button" class="toggle-pw" onclick="togglePw('confirm', this)" aria-label="Show password">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-full">Create Account</button>
            </form>

            <p class="auth-switch">Already have an account? <a href="login.php">Sign in</a></p>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>

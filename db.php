<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'flood_relief');
define('DB_USER', 'root');         
define('DB_PASS', '');             
define('DB_CHAR', 'utf8mb4');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHAR,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('<div style="font-family:sans-serif;padding:2rem;color:#c0392b;">
            <strong>Database connection failed.</strong><br>
            ' . htmlspecialchars($e->getMessage()) . '
         </div>');
}

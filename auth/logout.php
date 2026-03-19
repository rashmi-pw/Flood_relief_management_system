<?php

session_start();
define('BASE_URL', '/flood_relief');

session_unset();
session_destroy();

header('Location: ' . BASE_URL . '/auth/login.php');
exit;

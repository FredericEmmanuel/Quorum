<?php
declare(strict_types=1); // "int" != int
define('ROOT', dirname(__DIR__));

require_once ROOT . '/src/config.php';
require_once ROOT . '/src/db.php';
require_once ROOT . '/src/helpers.php';
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
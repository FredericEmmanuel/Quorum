<?php 
require_once __DIR__ . '/../src/bootstrap.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $csrf_token = $_POST['csrf_token'] ?? "";
    
    if (hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $_SESSION = [];
        session_destroy();
    }
    
}

header('Location: login.php');
exit;
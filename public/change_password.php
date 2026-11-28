<?php
require_once __DIR__ . '/../src/bootstrap.php';
$user = require_user();
$pw_error = "";
$pw_success = "";
$back = "/dashboard.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $csrf_token = $_POST['csrf_token'] ?? "";
    $old_pass = $_POST['old_pass'] ?? "";
    $new_pass = $_POST['new_pass'] ?? "";
    $conf = $_POST['conf'] ?? "";
    
    $back = $_POST['back'] ?? "";
    if (!preg_match('#^/[A-Za-z0-9_]+\.php(\?[A-Za-z0-9_=&]*)?$#D', $back)) {
        $back = "/dashboard.php";
    } 

    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $pw_error = "Terjadi Masalah";
    } elseif ($old_pass === "" || $new_pass === "" || $conf === "") {
        $pw_error = "Semua Field Wajib Diisi.";
    } elseif (strlen($new_pass) < 8) {
        $pw_error = "Password Minimal 8 Karakter";
    } elseif ($new_pass !== $conf) {
        $pw_error = "Password Dan Konfirmasi Berbeda";
    } 

    if ($pw_error === "") {
        $query = db()->prepare("SELECT password_hash FROM users WHERE id = ?");
        $query->execute([$user['id']]);
        $hash = $query->fetchColumn();
        if (!password_verify($old_pass, $hash)) {
            $pw_error = "Password Lama Salah";
        } else {
            $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $update = db()->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $update->execute([$new_hash, $user['id']]);
            session_regenerate_id(true);
            $pw_success = "Password Berhasil Diubah";
        }
    }
}

$_SESSION['pw_flash'] = ['error' => $pw_error, 'success' => $pw_success];
header("Location: " . $back);
exit;

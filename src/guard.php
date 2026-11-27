<?php
function require_user(): array {
    if (empty($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $query = db()->prepare("SELECT id, name, is_admin FROM users WHERE id = ?");
    $query->execute([$_SESSION['user_id']]);
    $user = $query->fetch();

    if ($user === false) {
        session_destroy();
        header("Location: login.php");
        exit;
    }

    return $user;
}

function require_ukm_role(int $ukm_id, array $allowed): array {
    $query = db()->prepare("SELECT role, status FROM anggota_ukm WHERE user_id = ? AND ukm_id = ?");
    $query->execute([$_SESSION['user_id'], $ukm_id]);
    $role = $query->fetch();

    if ($role === false || !in_array($role['role'], $allowed, true)) {
        http_response_code(403);
        echo "Akun Tidak Memiliki Hak Akses";
        exit;
    } elseif ($role['status'] !== 'active') {
        http_response_code(403);
        echo "Menunggu Konfirmasi";
        exit;
    }
    return $role;
}
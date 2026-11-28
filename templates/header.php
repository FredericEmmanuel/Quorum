<?php
$ukm_id = $ukm_id ?? 0;
$role = $role ?? "";
$pw_error = "";
$pw_success = "";
if (isset($_SESSION['pw_flash'])) {
    $pw_error = $_SESSION['pw_flash']['error'];
    $pw_success = $_SESSION['pw_flash']['success'];
    unset($_SESSION['pw_flash']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title); ?></title>
</head>
<body>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="ukm.php">Daftar UKM</a>
        <button type="button" onclick="document.getElementById('pw-dialog').showModal()">Ganti Password</button>
        <?php if ($ukm_id !== 0 && $role !== ""): ?>
            <a href="kegiatan.php?ukm_id=<?php echo e((string) $ukm_id); ?>">Kegiatan</a>

            <?php if ($role === 'pengurus' || $role === 'ketua'): ?>
                <a href="create_kegiatan.php?ukm_id=<?php echo e((string) $ukm_id); ?>">Buat Kegiatan</a>
                <a href="anggota_manage.php?ukm_id=<?php echo e((string) $ukm_id); ?>">Kelola Anggota</a>
                <a href="kehadiran_manage.php?ukm_id=<?php echo e((string) $ukm_id); ?>">Kehadiran</a>
                <a href="laporan.php?ukm_id=<?php echo e((string) $ukm_id); ?>">Laporan</a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>

    <form action="logout.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
        <input type="submit" value="Logout">
    </form>
    
    <dialog id="pw-dialog"> 
        <?php if ($pw_error !== ""): ?>
            <p><?php echo e($pw_error); ?></p>
        <?php endif; ?>

        <?php if ($pw_success !== ""): ?>
            <p><?php echo e($pw_success); ?></p>
        <?php endif; ?>

        <form method="post" action="change_password.php">
            <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="back" value="<?php echo e($_SERVER['REQUEST_URI']); ?>">
            <label for="old_pass">Password Lama: </label>
                <input type="password" name="old_pass" id="old_pass" required> <br>
            <label for="new_pass">Password Baru: </label>
                <input type="password" name="new_pass" id="new_pass" required minlength="8"> <br>
            <label for="conf">Konfirmasi Password Baru: </label>
                <input type="password" name="conf" id="conf" required minlength="8">
            
            <input type="submit" value="Simpan">
        </form>

        <form method="dialog">
            <button>Close</button>
        </form>
    </dialog>

    <?php if ($pw_error !== "" || $pw_success !== ""): ?>
        <script>document.getElementById('pw-dialog').showModal();</script>
    <?php endif; ?>
<?php
$ukm_id = $ukm_id ?? 0;
$role = $role ?? "";
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



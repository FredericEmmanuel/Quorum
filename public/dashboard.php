<?php 
require_once __DIR__ . '/../src/bootstrap.php';
$user = require_user();
$ukm_id = (int) ($_GET['ukm_id'] ?? 0);
$role = "";
$score = 0;

if ($ukm_id !== 0) {
    $row = require_ukm_role($ukm_id, ['anggota', 'pengurus', 'ketua']);
    $role = $row['role'];

    $score_count = db()->prepare("SELECT COUNT(*) FROM kehadiran k JOIN kegiatan kg ON kg.id = k.kegiatan_id WHERE k.user_id = ? AND kg.ukm_id = ? AND k.status = 'approved'");
    $score_count->execute([$user['id'], $ukm_id]);
    $score = (int) $score_count->fetchColumn();
}

$query = db()->prepare("SELECT ukm.id, ukm.name, aukm.role FROM anggota_ukm aukm JOIN ukm ON ukm.id = aukm.ukm_id WHERE aukm.user_id = ? AND aukm.status = 'active'");
$query->execute([$user['id']]);
$joined_ukm = $query->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quorum Dashboard</title>
</head>
<body>
    <h1>Selamat datang <?php echo e($user['name']); ?></h1>
    <?php if (count($joined_ukm) === 0): ?>
        <p>Belum mengikut UKM</p>
    <?php else: ?>
        <ul>
            <?php foreach ($joined_ukm as $my_ukm): ?>
                <li>
                    <a href="dashboard.php?ukm_id=<?php echo e((string) $my_ukm['id']); ?>">
                        <?php echo e($my_ukm['name']); ?>
                    </a>
                    <?php echo e($my_ukm['role']); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <?php if ($role === 'anggota'): ?>
        <a href="kegiatan.php?ukm_id=<?php echo e((string) $ukm_id); ?>">Kegiatan</a>
        <p>tampilan anggota</p>
        <p>Skor Partisipasi: <?php echo e((string) $score); ?></p>
    <?php elseif ($role === 'pengurus'): ?>
        <a href="kegiatan.php?ukm_id=<?php echo e((string) $ukm_id); ?>">Kegiatan</a>
        <a href="kegiatan_create.php?ukm_id=<?php echo e((string) $ukm_id); ?>">Buat Kegiatan</a>
        <a href="anggota_manage.php?ukm_id=<?php echo e((string) $ukm_id); ?>">Kelola Anggota</a>
        <a href="kehadiran_manage.php?ukm_id=<?php echo e((string) $ukm_id); ?>">Kehadiran</a>
        <p>tampilan pengurus</p>
    <?php elseif ($role === 'ketua'): ?>
        <a href="kegiatan.php?ukm_id=<?php echo e((string) $ukm_id); ?>">Kegiatan</a>
        <a href="kegiatan_create.php?ukm_id=<?php echo e((string) $ukm_id); ?>">Buat Kegiatan</a>
        <a href="anggota_manage.php?ukm_id=<?php echo e((string) $ukm_id); ?>">Kelola Anggota</a>
        <a href="kehadiran_manage.php?ukm_id=<?php echo e((string) $ukm_id); ?>">Kehadiran</a>
        <p>tampilan ketua</p>
    <?php elseif ($user['is_admin']): ?>
        <p>tampilan admin</p>
    <?php endif; ?>
    <form action="logout.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
        <input type="submit" value="Logout">
    </form>
</body>
</html>
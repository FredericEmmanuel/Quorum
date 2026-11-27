<?php 
require_once __DIR__ . '/../src/bootstrap.php';
$user = require_user();
$ukm_id = (int) ($_GET['ukm_id'] ?? 0);
$role = "";

if ($ukm_id !== 0) {
    $row = require_ukm_role($ukm_id, ['anggota', 'pengurus', 'ketua']);
    $role = $row['role'];
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
        <p>tampilan anggota</p>
    <?php elseif ($role === 'pengurus'): ?>
        <p>tampilan pengurus</p>
    <?php elseif ($role === 'ketua'): ?>
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
<?php 
require_once __DIR__ . '/../src/bootstrap.php';
require_user();
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ukm_id = (int) ($_POST['ukm_id'] ?? 0);
    $csrf_token = $_POST['csrf_token'] ?? "";
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $error = "Terjadi Kesalahan";
    } else {
        $search = db()->prepare("SELECT id FROM ukm WHERE id = ?");
        $search->execute([$ukm_id]);

        if ($search->fetch() === false) {
            $error = "UKM Tidak Ditemukan";
        } else {
            try {
                $join = db()->prepare("INSERT INTO anggota_ukm (user_id, ukm_id) VALUES (?, ?)");
                $join->execute([$_SESSION['user_id'], $ukm_id]);
                $success = "Pendaftaran Berhasil";
            } catch (PDOException $err) {
                if ($err->errorInfo[1] === 1062) {
                    $error = "Sudah Ada Pendaftaran";
                } else {
                    throw $err;
                }
            }
        }
    }
}

$join_status = db()->prepare("SELECT ukm_id, status FROM anggota_ukm WHERE user_id = ?");
$join_status->execute([$_SESSION['user_id']]);
$rows = $join_status->fetchAll();

$my_status = [];
foreach ($rows as $row) {
    $my_status[$row['ukm_id']] = $row['status'];
}

$labels = [
    'pending' => 'Menunggu Konfirmasi',
    'active' => 'Anda diterima',
];

$query = db()->query("SELECT id, name FROM ukm ORDER BY name ASC");
$ukm_list = $query->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quorum UKM</title>
</head>
<body>
    <?php if ($success !== ""): ?>
        <p><?php echo e($success); ?></p>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <p><?php echo e($error); ?></p>
    <?php endif; ?>

    <?php if (count($ukm_list) === 0): ?>
        <p>Belum ada UKM</p>
    <?php else: ?>
        <ul>
            <?php foreach ($ukm_list as $ukm): ?>
                <li>
                    <?php echo e($ukm['name']); ?> <br>
                    <?php if (isset($my_status[$ukm['id']])): ?>
                        <span>Status: <?php echo e($labels[$my_status[$ukm['id']]] ?? 'Tidak Diketahui'); ?></span>
                    <?php else: ?>  
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="ukm_id" value="<?php echo e((string) $ukm['id']); ?>">
                            <input type="submit" value="Gabung UKM">
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
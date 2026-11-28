<?php 
require_once __DIR__ . '/../src/bootstrap.php';
$user = require_user();
$ukm_id = (int) ($_GET['ukm_id'] ?? 0);
require_ukm_role($ukm_id, ['pengurus', 'ketua']);
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $csrf_token = $_POST['csrf_token'] ?? "";
    $action = $_POST['action'] ?? "";
    $kegiatan_id = ((int) ($_POST['kegiatan_id'] ?? 0));
    $user_id = ((int) ($_POST['user_id'] ?? 0));

    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $error = "Terjadi Kesalahan";
    } else {
        $find = db()->prepare("SELECT k.user_id FROM kehadiran k JOIN kegiatan kg ON kg.id = k.kegiatan_id WHERE k.kegiatan_id = ? AND k.user_id = ? AND k.status = 'pending' AND kg.ukm_id = ?");
        $find->execute([$kegiatan_id, $user_id, $ukm_id]);
        $attend_req = $find->fetch();

        if ($attend_req === false) {
            $error = "Kehadiran Tidak Ditemukan";
        } elseif ($action === 'accept') {   
            $acceptance = db()->prepare("UPDATE kehadiran SET status = 'approved', approved_by = ?, decided_at = NOW() WHERE kegiatan_id = ? AND user_id = ?");
            $acceptance->execute([$user['id'], $kegiatan_id, $user_id]);
            $success = "Berhasil Disetujui";
        } elseif ($action === 'decline') {
            $rejection = db()->prepare("UPDATE kehadiran SET status = 'rejected', approved_by = ?, decided_at = NOW() WHERE kegiatan_id = ? AND user_id = ?");
            $rejection->execute([$user['id'], $kegiatan_id, $user_id]);
            $success = "Berhasil Ditolak";
        } else {
            $error = "Tindakan Tidak Dikenal";
        }
    }
}

$query = db()->prepare(
    "SELECT k.kegiatan_id, k.user_id, u.name, kg.title
     FROM kehadiran k
     JOIN users u ON u.id = k.user_id
     JOIN kegiatan kg ON kg.id = k.kegiatan_id
     WHERE kg.ukm_id = ? AND k.status = 'pending'"
);
$query->execute([$ukm_id]);
$pending_attend = $query->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quorum Manage kehadiran</title>
</head>
<body>
    <?php if ($success !== ""): ?>
        <p><?php echo e($success); ?></p>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <p><?php echo e($error); ?></p>
    <?php endif; ?>

    <?php if (count($pending_attend) === 0): ?>
        <p><?php echo "Belum Ada Absen"; ?></p>
    <?php else: ?>
        <ul>
            <?php foreach ($pending_attend as $pending): ?>
                <li>
                    <?php echo e($pending['name']); ?> - <?php echo e($pending['title']); ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="kegiatan_id" value="<?php echo e((string) $pending['kegiatan_id']); ?>">
                        <input type="hidden" name="user_id" value="<?php echo e((string) $pending['user_id']); ?>">
                        <input type="hidden" name="action" value="accept">
                        <input type="submit" value="Terima">
                    </form>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="kegiatan_id" value="<?php echo e((string) $pending['kegiatan_id']); ?>">
                        <input type="hidden" name="user_id" value="<?php echo e((string) $pending['user_id']); ?>">
                        <input type="hidden" name="action" value="decline">
                        <input type="submit" value="Tolak">
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
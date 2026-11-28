<?php 
require_once __DIR__ . '/../src/bootstrap.php';
$user = require_user();
$error = "";
$success = "";

$ukm_id = (int) ($_GET['ukm_id'] ?? 0);
$row = require_ukm_role($ukm_id, ['pengurus', 'ketua']);
$role = $row['role'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $csrf_token = $_POST['csrf_token'] ?? "";
    $user_id = (int) ($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? "";
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $error = "Terjadi Kesalahan";
    } else {
        $check = db()->prepare("SELECT user_id FROM anggota_ukm WHERE user_id = ? AND ukm_id = ? AND status = 'pending'");
        $check->execute([$user_id, $ukm_id]);

        if ($check->fetch() === false) {
            $error = "Anggota Tidak ditemukan";
        } else {
            if ($action === 'accept') {
                $approve = db()->prepare("UPDATE anggota_ukm SET status = 'active', joined_at = NOW() WHERE user_id = ? AND ukm_id = ?");
                $approve->execute([$user_id, $ukm_id]);
                $success =  "Berhasil Diterima";
            } elseif ($action === 'decline') {
                $decline = db()->prepare("DELETE FROM anggota_ukm WHERE user_id = ? AND ukm_id = ?");
                $decline->execute([$user_id, $ukm_id]);
                $success =  "Berhasil Ditolak";
            } else {
                $error = "Aksi Tidak Dikenal";
            }
            
        }

    }
}
$pending_query = db()->prepare("SELECT u.id, u.name FROM users u JOIN anggota_ukm aukm ON u.id=aukm.user_id WHERE aukm.status = 'pending' AND aukm.ukm_id = ?");
$pending_query->execute([$ukm_id]);
$pending = $pending_query->fetchAll();

$page_title = "Quorum Kelola";
require __DIR__ . '/../templates/header.php';
?>

<?php if ($error !== ""): ?>
    <p><?php echo e($error); ?></p>
<?php endif; ?> 

<?php if ($success !== ""): ?>
    <p><?php echo e($success); ?></p>
<?php endif; ?>

<h1>Kelola UKM</h1>
<?php if (count($pending) === 0): ?>
    <p>Tidak Ada Anggota Baru</p>
<?php else: ?>     
    <h2>Status Pending: </h2>
    <ul>
        <?php foreach ($pending as $pending_members): ?>
            <li>
                <?php echo e($pending_members['name']); ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="user_id" value="<?php echo e((string) $pending_members['id']); ?>">
                    <input type="hidden" name="action" value="accept">
                    <input type="submit" value="Terima">
                </form>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="user_id" value="<?php echo e((string) $pending_members['id']); ?>">
                    <input type="hidden" name="action" value="decline">
                    <input type="submit" value="Tolak">
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php require __DIR__ . '/../templates/footer.php'; ?>

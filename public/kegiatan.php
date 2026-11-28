<?php 
require_once __DIR__ . "/../src/bootstrap.php";
$user = require_user();
$error = "";
$success = "";
$ukm_id = (int) ($_GET['ukm_id'] ?? 0);
$row = require_ukm_role($ukm_id, ['anggota', 'pengurus', 'ketua']);
$role = $row['role'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $csrf_token = $_POST['csrf_token'] ?? "";
    $action = $_POST['action'] ?? "";
    $kegiatan_id = ((int) ($_POST['kegiatan_id'] ?? 0));

    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $error = "Terjadi Kesalahan";
    } else {
        $find = db()->prepare("SELECT id, quota, event_date FROM kegiatan WHERE id = ? AND ukm_id = ?");
        $find->execute([$kegiatan_id, $ukm_id]);
        $event = $find->fetch();
        if ($event === false) {
            $error = "Kegiatan Tidak Ditemukan";
        } else {
            $count = db()->prepare("SELECT COUNT(*) FROM pendaftaran WHERE kegiatan_id = ?");
            $count->execute([$kegiatan_id]);
            $taken = (int) $count->fetchColumn();

            if ($action === 'register') {
                if ($taken >= $event['quota']) {
                    $error = "Kuota Habis";
                } else {
                    try {
                        $stmt = db()->prepare("INSERT INTO pendaftaran (kegiatan_id, user_id) VALUES (?, ?)");
                        $stmt->execute([$kegiatan_id, $_SESSION['user_id']]);
                        $success = "Berhasil";
                    } catch (PDOException $err) {
                        if ($err->errorInfo[1] === 1062) {
                            $error = "Anda Sudah Terdaftar";
                        } else {
                            throw $err;
                        }
                    }
                }
            } elseif ($action === 'attend') {
                $query_attended = db()->prepare("SELECT user_id FROM pendaftaran WHERE kegiatan_id = ? AND user_id = ?");
                $query_attended->execute([$kegiatan_id, $_SESSION['user_id']]);
                $attended = $query_attended->fetch();
                if ($attended === false) {
                    $error = "Belum Terdaftar"; 
                } else {   
                    if (new DateTime() < new DateTime($event['event_date'])) {
                        $error = "Kegiatan Belum Dimulai";
                    } else {
                        try {
                            $query_attend = db()->prepare("INSERT INTO kehadiran (kegiatan_id, user_id) VALUES (?, ?)");
                            $query_attend->execute([$kegiatan_id, $_SESSION['user_id']]);
                            $success = "Berhasil";
                        } catch (PDOException $err) {
                            if ($err->errorInfo[1] === 1062) {
                                $error = "Anda Sudah Absen";
                            } else {
                                throw $err;
                            }
                        }
                    }    
                }
            } elseif ($action === 'cancel') {
                $has = db()->prepare("SELECT user_id FROM kehadiran WHERE kegiatan_id = ? AND user_id = ?");
                $has->execute([$kegiatan_id, $_SESSION['user_id']]);

                if ($has->fetch() !== false) {
                    $error = "Sudah Absen";
                } else {
                    $del = db()->prepare("DELETE FROM pendaftaran WHERE kegiatan_id = ? AND user_id = ?");
                    $del->execute([$kegiatan_id, $_SESSION['user_id']]);
                    $success = "Berhasil";
                }
            } else {
                $error = "Terjadi Masalah";
            }
        }
    }
}

$regist = db()->prepare("SELECT kegiatan_id FROM pendaftaran WHERE user_id = ?");
$regist->execute([$_SESSION['user_id']]);
$my_registered = [];
foreach ($regist->fetchAll() as $row) {
    $my_registered[$row['kegiatan_id']] = true;
}

$attend = db()->prepare("SELECT kegiatan_id, status FROM kehadiran WHERE user_id = ?");
$attend->execute([$_SESSION['user_id']]);
$my_attend = [];
foreach ($attend->fetchAll() as $row) {
    $my_attend[$row['kegiatan_id']] = $row['status'];
}

$labels = [
    'pending' => 'Menunggu Persetujuan',
    'approved' => 'Absen Disetujui',
    'rejected' => 'Ditolak',
];

$query = db()->prepare("SELECT id, title, event_date, quota FROM kegiatan WHERE ukm_id = ? ORDER BY event_date");
$query->execute([$ukm_id]);
$kegiatan_list = $query->fetchAll();

$page_title = "Quorum Kegiatan";
require __DIR__ . '/../templates/header.php';
?>

<?php if ($success !== ""): ?>
    <p><?php echo e($success); ?></p>
<?php endif; ?>

<?php if ($error !== ""): ?>
    <p><?php echo e($error); ?></p>
<?php endif; ?>

<?php if (count($kegiatan_list) === 0): ?>
    <p><?php echo 'Belum Ada Kegiatan'; ?></p>
<?php else: ?>
    <ul>
        <?php foreach ($kegiatan_list as $kegiatan): ?>
            <li>
                <h2><?php echo e($kegiatan['title']); ?></h2>
                <p><?php echo 'Tanggal Kegiatan ' . e($kegiatan['event_date']); ?> <br>
                <?php echo 'Kuota: ' . e((string) $kegiatan['quota']); ?></p>

                <?php if (!isset($my_registered[$kegiatan['id']])): ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="kegiatan_id" value="<?php echo e((string) $kegiatan['id']); ?>">
                        <input type="hidden" name="action" value="register">
                        <input type="submit" value="Daftar">
                    </form>
                <?php else: ?>
                    <span>Sudah Terdaftar</span>
                    <?php if (isset($my_attend[$kegiatan['id']])): ?>
                        <span>Kehadiran: <?php echo e($labels[$my_attend[$kegiatan['id']]] ?? 'Tidak Diketahui'); ?></span>
                    <?php else: ?>
                        <?php if (strtotime($kegiatan['event_date']) <= time()): ?>
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="kegiatan_id" value="<?php echo e((string) $kegiatan['id']); ?>">
                                <input type="hidden" name="action" value="attend">
                                <input type="submit" value="Absen">
                            </form>
                        <?php endif; ?>
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="kegiatan_id" value="<?php echo e((string) $kegiatan['id']); ?>">
                                <input type="hidden" name="action" value="cancel">
                                <input type="submit" value="Batal">
                            </form>
                    <?php endif; ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php require __DIR__ . '/../templates/footer.php'; ?>

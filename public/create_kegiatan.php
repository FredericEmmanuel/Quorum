<?php 
require_once __DIR__ . "/../src/bootstrap.php";
$user = require_user();
$error = "";
$success = "";

$ukm_id = (int) ($_GET['ukm_id'] ?? 0);
require_ukm_role($ukm_id, ['pengurus', 'ketua']);

if (isset($_GET['saved'])) {
    $success = "Kegiatan Tersimpan";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $csrf_token = $_POST['csrf_token'] ?? "";
    $title = trim($_POST['title'] ?? "");
    $event_date = trim($_POST['event_date'] ?? "");
    $desc = trim($_POST['desc'] ?? "");
    $quota = (int) ($_POST['quota'] ?? 0);
    $when = DateTime::createFromFormat('Y-m-d\TH:i', $event_date);
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $error = "Terjadi Kesalahan";
    } elseif ($title === "" || $event_date === "") {
        $error = "Semua Field Wajib Diisi";
    } elseif(mb_strlen($title) > 100) {
        $error = "Nama Kegiatan Maksimal 100 Karakter";
    } elseif ($quota < 1 || $quota > 100) {
        $error = "Jumlah Kuota Invalid";
    } elseif ($when === false) {
        $error = "Format Tanggal Salah";
    }

    if ($error === "") {
        $query = db()->prepare("INSERT INTO kegiatan (ukm_id, title, description, event_date, quota, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $query->execute([$ukm_id, $title, $desc, $when->format('Y-m-d H:i:s'), $quota, $user['id']]);
        header("Location: create_kegiatan.php?ukm_id=" . $ukm_id . "&saved=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quorum create kegiatan</title>
</head>
<body>
    <?php if ($success !== ""): ?>
        <p><?php echo e($success); ?></p>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <p><?php echo e($error); ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
        <label for="title">Nama kegiatan: </label>
            <input type="text" name="title" id="title" required> <br>
        <label for="desc">deskripsi: </label>
            <textarea name="desc" id="desc"></textarea> <br>
        <label for="event_date">Tanggal: </label>
            <input type="datetime-local" name="event_date" id="event_date" required> <br>
        <label for="quota">Kuota: </label>
            <input type="number" name="quota" id="quota" min="1" required> <br>
        
        <input type="submit" value="Simpan">
    </form>
</body>
</html>
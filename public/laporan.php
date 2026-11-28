<?php 
require_once __DIR__ . "/../src/bootstrap.php";
$user = require_user();
$ukm_id = (int) ($_GET['ukm_id'] ?? 0);
$row = require_ukm_role($ukm_id, ['pengurus', 'ketua']);
$role = $row['role'];

$report_query = db()->prepare("SELECT kg.id, kg.title, kg.event_date,
        COUNT(DISTINCT p.user_id) AS registered,
        COUNT(DISTINCT CASE WHEN k.status = 'approved' THEN k.user_id END) AS attended
        FROM kegiatan kg
        LEFT JOIN pendaftaran p ON p.kegiatan_id = kg.id
        LEFT JOIN kehadiran k ON k.kegiatan_id = kg.id
        WHERE kg.ukm_id = ?
        GROUP BY kg.id, kg.title, kg.event_date
        ORDER BY kg.event_date");
$report_query->execute([$ukm_id]);
$report_list = $report_query->fetchAll();

$page_title = "Quorum Laporan";
require __DIR__ . '/../templates/header.php';
?>
<?php if (count($report_list) === 0): ?>
    <p><?php echo "Belum Ada Kegiatan"; ?></p>
<?php else: ?>
    <?php foreach ($report_list as $report): ?>
        <h2><?php  echo e($report['title']) . ', ' .  e($report['event_date']); ?></h2>
        <p>Terdaftar: <?php echo e((string) $report['registered']); ?> | Hadir: <?php echo e((string) $report['attended']); ?></p>    
<?php endforeach; ?>
<?php endif; ?> 


<?php require __DIR__ . '/../templates/footer.php'; ?>
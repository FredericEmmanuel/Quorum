<?php 
require_once __DIR__ . '/../src/bootstrap.php';
$user = require_user();
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
    <form action="logout.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
        <input type="submit" value="Logout">
    </form>
</body>
</html>
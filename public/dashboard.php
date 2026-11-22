<?php 
require_once __DIR__ . '/../src/bootstrap.php';

if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$query = db()->prepare("SELECT name FROM users WHERE id = ?");
$query->execute([$_SESSION['user_id']]);
$user = $query->fetch();

if ($user === false) {
    session_destroy();
    header("Location: login.php");
    exit;
}
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
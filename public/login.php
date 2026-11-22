<?php 
require_once __DIR__ . '/../src/bootstrap.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";
    $csrf_token = $_POST['csrf_token'] ?? "";
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $error = "Terjadi Kesalahan";
    } elseif ($email === "" || $password === "") {
        $error = "Semua Field Wajib Diisi.";
    }

    if ($error === "") {
        $login = db()->prepare('SELECT id, name, password_hash FROM users WHERE email = ?');
        $login->execute([$email]);
        $user = $login->fetch();

        if ($user === false || !password_verify($password, $user['password_hash'])) {
            $error = "Email Atau Password Salah"; 
        }
    }

    if ($error === "") {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        header("Location: dashboard.php");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quorum Login</title>
</head>
<body>
    <?php if ($error !== ""): ?>
        <p><?php echo e($error); ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
        <label for="email">Email: </label>
            <input type="email" name="email" id="email" required> <br>
        <label for="password">Password: </label>
            <input type="password" name="password" id="password" required> <br>

        <input type="submit" value="Login">
    </form>
</body>
</html>
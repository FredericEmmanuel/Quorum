<?php
require_once __DIR__ . '/../src/bootstrap.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";
    $conf = $_POST['conf'] ?? "";
    $csrf_token = $_POST['csrf_token'] ?? "";
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $error = "Terjadi Kesalahan";
    } elseif ($name === "" || $email === "" || $password === "" || $conf === "") {
        $error = "Semua Field Wajib Diisi.";
    } elseif ($password !== $conf) {
        $error = "Password Dan Konfirmasi Berbeda";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Gunakan Email Valid";
    } 

    if ($error === "") {
        $hash = password_hash($password, PASSWORD_DEFAULT); 
        try {
            $regis = db()->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
            $regis->execute([$name, $email, $hash]);  
            header("Location: login.php");
            exit;
        } catch (PDOException $err) {
            if ($err->errorInfo[1] === 1062) {
                $error = "Sudah Ada Akun Dengan Email.";
            } else {
                throw $err;
            }  
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quorum Register</title>
</head>
<body>  
    <?php if ($error !== ""): ?>
        <p><?php echo e($error); ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
        <label for="name">Nama: </label>
            <input type="text" name="name" id="name" required> <br>
        <label for="email">Email: </label>
            <input type="email" name="email" id="email" required> <br>
        <label for="password">Password: </label>
            <input type="password" name="password" id="password" required> <br>
        <label for="conf">Konfirmasi password: </label>
            <input type="password" name="conf" id="conf" required> <br>

        <input type="submit" value="Register">
    </form>
</body>
</html>
<?php 
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $settings = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // alarm database jika ada kesalahan
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // label nama saat mengambil column, dan menjadi default saat memanggil fetch()
            PDO::ATTR_EMULATE_PREPARES => false // supaya database yang mengecek value(value tidak terhitung sebagai code untuk dijalankan)
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $settings);
        } catch (PDOException $err) { 
            error_log($err->getMessage()); //log error
            http_response_code(500); //catat error
            echo 'Terjadi masalah';
            exit;
        }
    }
    return $pdo;
}
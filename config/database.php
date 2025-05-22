<?php
// autoparts/config/database.php

// Получаем значения из переменных окружения,
// с фоллбэком на значения по умолчанию, если переменная не установлена.
// Для Docker Compose переменные окружения БУДУТ установлены.
$db_host    = getenv('DB_HOST') ?: 'localhost';
$db_name    = getenv('DB_NAME') ?: 'danchous';
$db_user    = getenv('DB_USER') ?: 'danchous';
$db_pass    = getenv('DB_PASS') ?: 'danchous';
$db_port    = getenv('DB_PORT') ?: '3306'; // Добавим порт
$db_charset = getenv('DB_CHARSET') ?: 'utf8mb4';


$dsn = "mysql:host=" . $db_host . ";port=" . $db_port . ";dbname=" . $db_name . ";charset=" . $db_charset;
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
    // Для отладки можно вывести больше информации
    $error_message = "Database Connection Error: " . $e->getMessage() .
                     " (DSN: " . $dsn . ", User: " . $db_user . ")";
    error_log($error_message);
    // В продакшене можно просто die() или показать кастомную страницу ошибки
    die("Ошибка подключения к базе данных. Пожалуйста, попробуйте позже. Детали в логе сервера.");
}

// $pdo теперь доступен для использования.
?>
<?php
// autoparts/config/database.php

// Эти константы лучше определить в constants.php или здесь, если они специфичны для БД
if (!defined('DB_HOST')) define('DB_HOST', '77.110.122.14'); // Или ваш IP 77.110.122.14, если БД там же
if (!defined('DB_NAME')) define('DB_NAME', 'danchous'); // Название вашей БД
if (!defined('DB_USER')) define('DB_USER', 'danchous'); // Пользователь БД
if (!defined('DB_PASS')) define('DB_PASS', 'danchous'); // Пароль БД
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Выбрасывать исключения при ошибках
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Возвращать ассоциативные массивы
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Использовать настоящие подготовленные выражения
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    // В режиме разработки можно выводить ошибку, в продакшене - логировать и показывать общее сообщение
    error_log("Database Connection Error: " . $e->getMessage());
    die("Ошибка подключения к базе данных. Пожалуйста, попробуйте позже.");
    // throw new \PDOException($e->getMessage(), (int)$e->getCode()); // Можно так, если обрабатывать выше
}

// $pdo теперь доступен для использования.
// Вы можете сделать его глобальным (не рекомендуется для больших проектов)
// global $pdo;
// или передавать его в конструкторы классов, или использовать через статический метод класса Database.
?>
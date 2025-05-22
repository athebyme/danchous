<?php
// autoparts/config/init.php

// Включаем отображение всех ошибок для разработки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Запускаем сессию, если еще не запущена
}

// Подключаем константы
require_once __DIR__ . '/constants.php';

// Подключаем файл для работы с БД (создает переменную $pdo)
// Если вы будете использовать класс Database, то это подключение может быть не нужно здесь,
// а объект PDO будет получаться через метод класса Database.
require_once __DIR__ . '/database.php';

// Простой автозагрузчик классов из папки /core
spl_autoload_register(function ($class_name) {
    // Предполагаем, что классы не используют пространства имен и находятся прямо в /core
    $file = CORE_PATH . '/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Подключаем файл с общими функциями
require_once CORE_PATH . '/functions.php';

// Глобальный объект PDO, если вы решили не использовать класс Database или хотите иметь прямой доступ
// global $pdo; // Переменная $pdo уже создана в database.php

// Пример создания объекта Database, если вы его используете
// require_once CORE_PATH . '/Database.php';
// $db_instance = new Database();
// $pdo_from_class = $db_instance->connect(); // Теперь $pdo_from_class содержит соединение
?>
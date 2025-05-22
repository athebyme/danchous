<?php
// autoparts/core/functions.php

/**
 * Перенаправляет пользователя на указанный URL.
 * @param string $url URL для перенаправления.
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Очищает входные данные от HTML-тегов и лишних пробелов.
 * @param string $data Входная строка.
 * @return string Очищенная строка.
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data); // Если magic_quotes_gpc включен (устаревшее)
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Форматирует цену.
 * @param float $price Цена.
 * @param string $currency Символ валюты (по умолчанию ₽).
 * @return string Отформатированная цена.
 */
function format_price($price, $currency = '₽') {
    return number_format((float)$price, 2, '.', ' ') . ' ' . $currency;
}

/**
 * Генерирует slug из строки (для ЧПУ).
 * @param string $string Входная строка.
 * @return string Сгенерированный slug.
 */
function generate_slug($string) {
    $string = mb_strtolower($string, 'UTF-8'); // в нижний регистр
    $string = preg_replace('~[^-a-z0-9_]+~u', '-', $string); // заменяем все небуквенноцифровое на -
    $string = trim($string, "-"); // убираем начальные и конечные тире
    return $string;
}

/**
 * Для отладки: выводит переменную и завершает выполнение скрипта.
 * @param mixed $data Данные для вывода.
 */
function dd($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}

/**
 * Получает PDO соединение.
 * Используйте эту функцию, если вы не используете класс Database или хотите получить $pdo напрямую.
 * ВАЖНО: $pdo должна быть определена в области видимости (например, global $pdo; из init.php).
 * Или лучше передавать $pdo как параметр в функции/методы, где он нужен.
 *
 * @global PDO $pdo
 * @return PDO Объект PDO соединения.
 */
function get_db_connection() {
    global $pdo; // Предполагает, что $pdo доступна глобально после подключения database.php
    if (!isset($pdo)) {
        // Попытка инициализировать, если еще не сделано (менее предпочтительно)
        // require_once CONFIG_PATH . '/database.php';
        // или
        // $db = new Database();
        // $pdo = $db->connect();
        die('Соединение с БД не установлено.');
    }
    return $pdo;
}
?>
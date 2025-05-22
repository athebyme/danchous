<?php
// autoparts/config/constants.php

// URL сайта (замените на ваш реальный URL)
define('SITE_URL', 'http://77.110.122.14:8081/autoparts'); // или http://localhost/autoparts

// Пути к директориям (от корня сервера или относительно)
define('ROOT_PATH', dirname(__DIR__)); // Корневая папка проекта (autoparts)
define('CONFIG_PATH', ROOT_PATH . '/config');
define('CORE_PATH', ROOT_PATH . '/core');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('PAGES_PATH', ROOT_PATH . '/pages');
define('UPLOADS_PATH', ROOT_PATH . '/uploads'); // Путь для загрузки файлов

// Относительные URL для ассетов
define('ASSETS_URL', SITE_URL . '/assets');
define('CSS_URL', ASSETS_URL . '/css');
define('JS_URL', ASSETS_URL . '/js');
define('IMAGES_URL', ASSETS_URL . '/images');
define('UPLOADS_URL', SITE_URL . '/uploads'); // URL для доступа к загруженным файлам

// Другие константы
define('ADMIN_EMAIL', 'admin@example.com');
define('ITEMS_PER_PAGE', 12); // Для пагинации
?>
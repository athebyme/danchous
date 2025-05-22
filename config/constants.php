<?php
// autoparts/config/constants.php

// URL сайта (исправленный)
define("SITE_URL", "http://77.110.122.14:8082");

// Пути к директориям
define("ROOT_PATH", dirname(__DIR__));
define("CONFIG_PATH", ROOT_PATH . "/config");
define("CORE_PATH", ROOT_PATH . "/core");
define("INCLUDES_PATH", ROOT_PATH . "/includes");
define("ADMIN_PATH", ROOT_PATH . "/admin");
define("ASSETS_PATH", ROOT_PATH . "/assets");
define("PAGES_PATH", ROOT_PATH . "/pages");
define("UPLOADS_PATH", ROOT_PATH . "/uploads");

// Относительные URL для ассетов
define("ASSETS_URL", SITE_URL . "/assets");
define("CSS_URL", ASSETS_URL . "/css");
define("JS_URL", ASSETS_URL . "/js");
define("IMAGES_URL", ASSETS_URL . "/images");
define("UPLOADS_URL", SITE_URL . "/uploads");

// Другие константы
define("ADMIN_EMAIL", "admin@example.com");
define("ITEMS_PER_PAGE", 12);
?>
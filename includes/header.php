<?php
// autoparts/includes/header.php

// Подключаем инициализацию (сессии, константы, автозагрузчик, функции)
// __DIR__ указывает на текущую папку (includes), поэтому ../config/init.php
require_once __DIR__ . '/../config/init.php';

// Получаем объект PDO. Лучше передавать его явно, чем использовать global.
// Но для простоты примера, если $pdo глобальна из init.php:
// global $pdo;
// Или если используете класс Database:
// $db = new Database();
// $pdo = $db->connect();

// Инициализируем классы, которые нужны в шапке
$auth = new Auth(get_db_connection()); // Передаем соединение в конструктор
$cart = new Cart(); // Корзина обычно работает с сессиями, PDO может не требоваться в конструкторе
$category_model = new Category(get_db_connection());
$top_categories = $category_model->getTopLevelCategories(5); // Пример метода для получения категорий верхнего уровня
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - Автозапчасти' : 'Автозапчасти'; ?></title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/style.css?v=<?php echo time(); // для сброса кэша при разработке ?>">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/responsive.css?v=<?php echo time(); ?>">
    <!-- Другие CSS файлы, если нужны -->
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script> <!-- Если используете FontAwesome -->
</head>
<body>
<header class="site-header">
    <div class="container header-container">
        <div class="logo-area">
            <a href="<?php echo SITE_URL; ?>/" class="logo">
                <img src="<?php echo IMAGES_URL; ?>/logo.png" alt="Логотип Автозапчасти">
                <span>АвтоДетали</span>
            </a>
        </div>

        <nav class="main-navigation">
            <ul>
                <li><a href="<?php echo SITE_URL; ?>/pages/catalog.php">Каталог</a></li>
                <?php if (!empty($top_categories)): ?>
                    <?php foreach ($top_categories as $category): ?>
                        <li>
                            <a href="<?php echo SITE_URL; ?>/pages/catalog.php?category=<?php echo htmlspecialchars($category['slug']); ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
                <li><a href="<?php echo SITE_URL; ?>/pages/contact.php">Контакты</a></li>
                <!-- Добавьте другие пункты меню, например, "О нас", "Доставка" -->
            </ul>
        </nav>

        <div class="header-actions">
            <div class="search-form-header">
                <form action="<?php echo SITE_URL; ?>/search_handler.php" method="GET">
                    <input type="search" name="q" placeholder="Поиск запчастей..." aria-label="Поиск запчастей">
                    <button type="submit" aria-label="Найти"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <a href="<?php echo SITE_URL; ?>/pages/cart.php" class="cart-icon" aria-label="Корзина">
                <i class="fas fa-shopping-cart"></i>
                <span id="cart-item-count-header" class="cart-count"><?php echo $cart->getTotalItems(); ?></span>
            </a>
            <div class="user-actions">
                <?php if ($auth->isLoggedIn()): ?>
                    <a href="<?php echo SITE_URL; ?>/pages/profile.php" aria-label="Профиль пользователя"><i class="fas fa-user"></i> Профиль</a>
                    <a href="<?php echo SITE_URL; ?>/logout.php" aria-label="Выход"><i class="fas fa-sign-out-alt"></i> Выход</a>
                    <?php if ($auth->isAdmin()): ?>
                        <a href="<?php echo SITE_URL; ?>/admin/" aria-label="Админ-панель"><i class="fas fa-tachometer-alt"></i> Админка</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/login.php" aria-label="Вход"><i class="fas fa-sign-in-alt"></i> Вход</a>
                    <a href="<?php echo SITE_URL; ?>/register.php" aria-label="Регистрация"><i class="fas fa-user-plus"></i> Регистрация</a>
                <?php endif; ?>
            </div>
        </div>
        <button class="mobile-menu-toggle" aria-label="Открыть меню">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</header>
<main class="site-main container">
    <!-- Начало основного контента страницы -->
<?php
// autoparts/logout.php

require_once __DIR__ . '/config/init.php';

$auth = new Auth(get_db_connection());

// Выполняем выход из системы
$auth->logout();

// Удаляем cookie "запомнить меня" если есть
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

// Устанавливаем сообщение об успешном выходе
$_SESSION['flash_message'] = ['text' => 'Вы успешно вышли из системы', 'type' => 'success'];

// Перенаправляем на главную страницу
redirect(SITE_URL . '/');
?>
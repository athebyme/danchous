<?php
// autoparts/api/placeholder.php
// Генератор placeholder изображений

// Получаем размеры из параметров URL
$width = isset($_GET['w']) && is_numeric($_GET['w']) ? (int)$_GET['w'] : 300;
$height = isset($_GET['h']) && is_numeric($_GET['h']) ? (int)$_GET['h'] : $width;

// Альтернативный способ через PATH_INFO
if (!isset($_GET['w']) && isset($_SERVER['PATH_INFO'])) {
    $path_parts = explode('/', trim($_SERVER['PATH_INFO'], '/'));
    $width = isset($path_parts[0]) && is_numeric($path_parts[0]) ? (int)$path_parts[0] : 300;
    $height = isset($path_parts[1]) && is_numeric($path_parts[1]) ? (int)$path_parts[1] : $width;
}

// Ограничиваем размеры
$width = min(max($width, 50), 1200);
$height = min(max($height, 50), 1200);

// Устанавливаем заголовки
header('Content-Type: image/png');
header('Cache-Control: public, max-age=86400');

// Создаем изображение
$image = imagecreate($width, $height);

// Цвета для автозапчастей
$bg_color = imagecolorallocate($image, 248, 249, 250); // Светло-серый фон
$text_color = imagecolorallocate($image, 108, 117, 125); // Серый текст
$border_color = imagecolorallocate($image, 233, 236, 239); // Граница
$accent_color = imagecolorallocate($image, 255, 107, 53); // Оранжевый акцент

// Заливаем фон
imagefill($image, 0, 0, $bg_color);

// Рисуем границу
imagerectangle($image, 0, 0, $width-1, $height-1, $border_color);

// Рисуем иконку автозапчасти (гаечный ключ)
$icon_size = min($width, $height) / 4;
$icon_x = ($width - $icon_size) / 2;
$icon_y = ($height - $icon_size) / 2 - 10;

if ($icon_size > 20) {
    // Простая иконка гаечного ключа
    $tool_width = $icon_size * 0.8;
    $tool_height = $icon_size * 0.2;
    $tool_x = ($width - $tool_width) / 2;
    $tool_y = ($height - $tool_height) / 2 - 5;

    imagefilledrectangle($image, $tool_x, $tool_y, $tool_x + $tool_width, $tool_y + $tool_height, $accent_color);

    // Добавляем "зубчики"
    $tooth_size = $tool_height / 3;
    imagefilledrectangle($image, $tool_x - $tooth_size, $tool_y - $tooth_size, $tool_x, $tool_y + $tool_height + $tooth_size, $accent_color);
    imagefilledrectangle($image, $tool_x + $tool_width, $tool_y - $tooth_size, $tool_x + $tool_width + $tooth_size, $tool_y + $tool_height + $tooth_size, $accent_color);
}

// Текст с размерами
$text = $width . 'x' . $height;
$font_size = min($width, $height) / 20;
$font_size = max(2, min(5, $font_size));

$text_width = imagefontwidth($font_size) * strlen($text);
$text_height = imagefontheight($font_size);
$x = ($width - $text_width) / 2;
$y = ($height + $icon_size) / 2 + 10;

imagestring($image, $font_size, $x, $y, $text, $text_color);

// Выводим изображение
imagepng($image);
imagedestroy($image);
?>
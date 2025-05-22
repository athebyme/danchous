<?php
// autoparts/api/placeholder.php
// Генератор placeholder изображений

// Получаем размеры из URL: /api/placeholder/300/200
$path_parts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$width = isset($path_parts[2]) && is_numeric($path_parts[2]) ? (int)$path_parts[2] : 300;
$height = isset($path_parts[3]) && is_numeric($path_parts[3]) ? (int)$path_parts[3] : $width;

// Ограничиваем размеры
$width = min(max($width, 50), 1200);
$height = min(max($height, 50), 1200);

// Устанавливаем заголовки
header('Content-Type: image/png');
header('Cache-Control: public, max-age=86400'); // Кэш на сутки

// Создаем изображение
$image = imagecreate($width, $height);

// Цвета
$bg_color = imagecolorallocate($image, 245, 245, 245); // Светло-серый фон
$text_color = imagecolorallocate($image, 150, 150, 150); // Серый текст
$border_color = imagecolorallocate($image, 220, 220, 220); // Граница

// Заливаем фон
imagefill($image, 0, 0, $bg_color);

// Рисуем границу
imagerectangle($image, 0, 0, $width-1, $height-1, $border_color);

// Текст
$text = $width . 'x' . $height;
$font_size = min($width, $height) / 15; // Адаптивный размер шрифта
$font_size = max(2, min(5, $font_size)); // Ограничиваем размер

// Позиция текста по центру
$text_width = imagefontwidth($font_size) * strlen($text);
$text_height = imagefontheight($font_size);
$x = ($width - $text_width) / 2;
$y = ($height - $text_height) / 2;

// Добавляем текст
imagestring($image, $font_size, $x, $y, $text, $text_color);

// Рисуем простую иконку (квадрат)
$icon_size = min($width, $height) / 6;
$icon_x = ($width - $icon_size) / 2;
$icon_y = ($height - $icon_size) / 2 - 20;

if ($icon_size > 10) {
    imagerectangle($image, $icon_x, $icon_y, $icon_x + $icon_size, $icon_y + $icon_size, $text_color);
}

// Выводим изображение
imagepng($image);
imagedestroy($image);
?>
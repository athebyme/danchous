<?php
// autoparts/api/cart.php

// Устанавливаем заголовок ответа как JSON
header('Content-Type: application/json');

// Подключаем инициализацию (сессии, константы, автозагрузчик)
require_once __DIR__ . '/../config/init.php';

// Получаем соединение с БД
$pdo = get_db_connection(); // Используем нашу функцию-хелпер или $GLOBALS['pdo']

// Инициализируем необходимые классы
$cart = new Cart(); // Класс Cart обычно работает с $_SESSION
$product_model = new Product($pdo); // Класс Product для получения информации о товаре

$response = ['success' => false, 'message' => 'Неверный запрос.'];

// Проверяем, что это POST запрос и есть параметр 'action'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    if ($quantity <= 0) $quantity = 1; // Минимальное количество - 1

    try {
        switch ($action) {
            case 'add':
                if ($product_id) {
                    $product_data = $product_model->getById($product_id); // Метод для получения товара по ID
                    if ($product_data && $product_data['stock_quantity'] >= $quantity) {
                        $cart->addItem($product_id, $quantity, $product_data['price'], $product_data['name'], $product_data['image_url_main'] ?? '');
                        $response = [
                            'success' => true,
                            'message' => 'Товар "' . htmlspecialchars($product_data['name']) . '" добавлен в корзину!',
                            'total_items' => $cart->getTotalItems(),
                            'total_price_formatted' => format_price($cart->getTotalPrice())
                        ];
                    } elseif ($product_data && $product_data['stock_quantity'] < $quantity) {
                        $response['message'] = 'Недостаточно товара на складе.';
                    } else {
                        $response['message'] = 'Товар не найден.';
                    }
                } else {
                    $response['message'] = 'Не указан ID товара.';
                }
                break;

            case 'update':
                if ($product_id) {
                    // Дополнительно можно проверить наличие товара на складе перед обновлением
                    $product_data = $product_model->getById($product_id);
                    if ($product_data && $product_data['stock_quantity'] >= $quantity) {
                        $cart->updateItemQuantity($product_id, $quantity);
                        $response = [
                            'success' => true,
                            'message' => 'Количество товара обновлено.',
                            'total_items' => $cart->getTotalItems(),
                            'total_price_formatted' => format_price($cart->getTotalPrice()),
                            'item_subtotal_formatted' => isset($_SESSION['cart'][$product_id]) ? format_price($_SESSION['cart'][$product_id]['price'] * $_SESSION['cart'][$product_id]['quantity']) : '0.00 ₽'
                        ];
                    } elseif ($product_data && $product_data['stock_quantity'] < $quantity) {
                         $response['message'] = 'Недостаточно товара на складе для указанного количества.';
                         // Можно вернуть текущее количество в корзине, если обновление не удалось
                         $response['current_quantity_in_cart'] = $cart->getItemQuantity($product_id);
                    } else {
                        $response['message'] = 'Товар не найден для обновления.';
                    }
                } else {
                    $response['message'] = 'Не указан ID товара для обновления.';
                }
                break;

            case 'remove':
                if ($product_id) {
                    $removed_item_name = $cart->getItemName($product_id); // Получить имя перед удалением для сообщения
                    $cart->removeItem($product_id);
                    $response = [
                        'success' => true,
                        'message' => 'Товар "' . htmlspecialchars($removed_item_name) . '" удален из корзины.',
                        'total_items' => $cart->getTotalItems(),
                        'total_price_formatted' => format_price($cart->getTotalPrice())
                    ];
                } else {
                    $response['message'] = 'Не указан ID товара для удаления.';
                }
                break;

            case 'clear':
                 $cart->clearCart();
                 $response = [
                    'success' => true,
                    'message' => 'Корзина очищена.',
                    'total_items' => 0,
                    'total_price_formatted' => format_price(0)
                 ];
                break;

            default:
                $response['message'] = 'Неизвестное действие.';
                break;
        }
    } catch (Exception $e) {
        // Логирование ошибки
        error_log("API Cart Error: " . $e->getMessage());
        $response['message'] = 'Произошла ошибка при обработке запроса к корзине.';
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') { // Для получения данных о корзине
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        if ($action === 'get_cart_data') {
            $response = [
                'success' => true,
                'items' => $cart->getItems(),
                'total_items' => $cart->getTotalItems(),
                'total_price' => $cart->getTotalPrice(),
                'total_price_formatted' => format_price($cart->getTotalPrice())
            ];
        }
    }
}

// Отправляем JSON ответ
echo json_encode($response);
exit;
?>
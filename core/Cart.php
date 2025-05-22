<?php
// autoparts/core/Cart.php

class Cart {
    private $session_key = 'cart';

    public function __construct() {
        if (!isset($_SESSION[$this->session_key])) {
            $_SESSION[$this->session_key] = [];
        }
    }

    /**
     * Добавление товара в корзину
     */
    public function addItem($product_id, $quantity = 1, $price = 0, $name = '', $image = '') {
        $product_id = (int)$product_id;
        $quantity = (int)$quantity;

        if ($quantity <= 0) {
            return false;
        }

        if (isset($_SESSION[$this->session_key][$product_id])) {
            // Если товар уже в корзине, увеличиваем количество
            $_SESSION[$this->session_key][$product_id]['quantity'] += $quantity;
        } else {
            // Добавляем новый товар
            $_SESSION[$this->session_key][$product_id] = [
                'id' => $product_id,
                'name' => $name,
                'price' => (float)$price,
                'quantity' => $quantity,
                'image' => $image,
                'added_at' => time()
            ];
        }

        return true;
    }

    /**
     * Обновление количества товара в корзине
     */
    public function updateItemQuantity($product_id, $quantity) {
        $product_id = (int)$product_id;
        $quantity = (int)$quantity;

        if ($quantity <= 0) {
            return $this->removeItem($product_id);
        }

        if (isset($_SESSION[$this->session_key][$product_id])) {
            $_SESSION[$this->session_key][$product_id]['quantity'] = $quantity;
            return true;
        }

        return false;
    }

    /**
     * Удаление товара из корзины
     */
    public function removeItem($product_id) {
        $product_id = (int)$product_id;

        if (isset($_SESSION[$this->session_key][$product_id])) {
            unset($_SESSION[$this->session_key][$product_id]);
            return true;
        }

        return false;
    }

    /**
     * Очистка корзины
     */
    public function clearCart() {
        $_SESSION[$this->session_key] = [];
        return true;
    }

    /**
     * Получение всех товаров в корзине
     */
    public function getItems() {
        return $_SESSION[$this->session_key] ?? [];
    }

    /**
     * Получение количества товара в корзине
     */
    public function getItemQuantity($product_id) {
        $product_id = (int)$product_id;
        return $_SESSION[$this->session_key][$product_id]['quantity'] ?? 0;
    }

    /**
     * Получение имени товара в корзине
     */
    public function getItemName($product_id) {
        $product_id = (int)$product_id;
        return $_SESSION[$this->session_key][$product_id]['name'] ?? '';
    }

    /**
     * Проверка наличия товара в корзине
     */
    public function hasItem($product_id) {
        $product_id = (int)$product_id;
        return isset($_SESSION[$this->session_key][$product_id]);
    }

    /**
     * Получение общего количества товаров в корзине
     */
    public function getTotalItems() {
        $total = 0;
        foreach ($_SESSION[$this->session_key] as $item) {
            $total += $item['quantity'];
        }
        return $total;
    }

    /**
     * Получение общей стоимости корзины
     */
    public function getTotalPrice() {
        $total = 0;
        foreach ($_SESSION[$this->session_key] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    /**
     * Получение количества уникальных товаров в корзине
     */
    public function getUniqueItemsCount() {
        return count($_SESSION[$this->session_key]);
    }

    /**
     * Проверка пустоты корзины
     */
    public function isEmpty() {
        return empty($_SESSION[$this->session_key]);
    }

    /**
     * Получение данных для оформления заказа
     */
    public function getOrderData() {
        $items = [];
        foreach ($_SESSION[$this->session_key] as $item) {
            $items[] = [
                'product_id' => $item['id'],
                'name' => $item['name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'subtotal' => $item['price'] * $item['quantity']
            ];
        }

        return [
            'items' => $items,
            'total_items' => $this->getTotalItems(),
            'total_price' => $this->getTotalPrice(),
            'unique_items' => $this->getUniqueItemsCount()
        ];
    }

    /**
     * Валидация корзины (проверка наличия товаров на складе)
     */
    public function validateCart($pdo) {
        $errors = [];
        $updated = false;

        foreach ($_SESSION[$this->session_key] as $product_id => $item) {
            // Проверяем существование товара
            $sql = "SELECT id, name, price, stock_quantity, is_active FROM products WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();

            if (!$product || !$product['is_active']) {
                // Товар не найден или неактивен
                $errors[] = "Товар \"{$item['name']}\" больше не доступен";
                $this->removeItem($product_id);
                $updated = true;
                continue;
            }

            // Проверяем наличие на складе
            if ($product['stock_quantity'] < $item['quantity']) {
                if ($product['stock_quantity'] > 0) {
                    // Уменьшаем количество до доступного
                    $errors[] = "Количество товара \"{$item['name']}\" уменьшено до {$product['stock_quantity']} (недостаточно на складе)";
                    $this->updateItemQuantity($product_id, $product['stock_quantity']);
                } else {
                    // Удаляем товар из корзины
                    $errors[] = "Товар \"{$item['name']}\" закончился на складе и удален из корзины";
                    $this->removeItem($product_id);
                }
                $updated = true;
            }

            // Проверяем цену (если изменилась)
            if (abs($product['price'] - $item['price']) > 0.01) {
                $errors[] = "Цена товара \"{$item['name']}\" изменилась с " . format_price($item['price']) . " на " . format_price($product['price']);
                $_SESSION[$this->session_key][$product_id]['price'] = $product['price'];
                $updated = true;
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'updated' => $updated
        ];
    }

    /**
     * Сохранение корзины в базу данных (для авторизованных пользователей)
     */
    public function saveToDatabase($pdo, $user_id) {
        // Реализация сохранения корзины в БД для авторизованных пользователей
        // Можно создать таблицу cart_items для этого
        // Здесь пока заглушка
        return true;
    }

    /**
     * Загрузка корзины из базы данных
     */
    public function loadFromDatabase($pdo, $user_id) {
        // Реализация загрузки корзины из БД
        // Здесь пока заглушка
        return true;
    }
}
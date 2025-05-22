<?php
// autoparts/core/Product.php

class Product {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Получение товара по ID
     */
    public function getById($id) {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                       b.name as brand_name, b.slug as brand_slug
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE p.id = ? AND p.is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Получение товара по slug
     */
    public function getBySlug($slug) {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                       b.name as brand_name, b.slug as brand_slug
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE p.slug = ? AND p.is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    /**
     * Получение списка товаров с фильтрацией
     */
    public function getList($filters = []) {
        $where_conditions = ['p.is_active = 1'];
        $params = [];

        // Фильтр по категории
        if (!empty($filters['category_id'])) {
            $where_conditions[] = 'p.category_id = ?';
            $params[] = $filters['category_id'];
        }

        // Фильтр по бренду
        if (!empty($filters['brand_id'])) {
            $where_conditions[] = 'p.brand_id = ?';
            $params[] = $filters['brand_id'];
        }

        // Поиск по названию
        if (!empty($filters['search'])) {
            $where_conditions[] = '(p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)';
            $search_term = '%' . $filters['search'] . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }

        // Фильтр по цене
        if (!empty($filters['price_min'])) {
            $where_conditions[] = 'p.price >= ?';
            $params[] = $filters['price_min'];
        }

        if (!empty($filters['price_max'])) {
            $where_conditions[] = 'p.price <= ?';
            $params[] = $filters['price_max'];
        }

        // Только товары в наличии
        if (!empty($filters['in_stock'])) {
            $where_conditions[] = 'p.stock_quantity > 0';
        }

        // Рекомендуемые товары
        if (!empty($filters['featured'])) {
            $where_conditions[] = 'p.is_featured = 1';
        }

        $where_clause = implode(' AND ', $where_conditions);

        // Сортировка
        $order_by = 'p.created_at DESC';
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_asc':
                    $order_by = 'p.price ASC';
                    break;
                case 'price_desc':
                    $order_by = 'p.price DESC';
                    break;
                case 'name_asc':
                    $order_by = 'p.name ASC';
                    break;
                case 'name_desc':
                    $order_by = 'p.name DESC';
                    break;
                case 'popular':
                    $order_by = 'p.views_count DESC';
                    break;
            }
        }

        // Пагинация
        $limit = $filters['limit'] ?? 12;
        $offset = ($filters['page'] ?? 1 - 1) * $limit;

        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                       b.name as brand_name, b.slug as brand_slug
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE {$where_clause}
                ORDER BY {$order_by}
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Подсчет общего количества товаров с учетом фильтров
     */
    public function getCount($filters = []) {
        $where_conditions = ['p.is_active = 1'];
        $params = [];

        // Те же фильтры что и в getList
        if (!empty($filters['category_id'])) {
            $where_conditions[] = 'p.category_id = ?';
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['brand_id'])) {
            $where_conditions[] = 'p.brand_id = ?';
            $params[] = $filters['brand_id'];
        }

        if (!empty($filters['search'])) {
            $where_conditions[] = '(p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)';
            $search_term = '%' . $filters['search'] . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }

        if (!empty($filters['price_min'])) {
            $where_conditions[] = 'p.price >= ?';
            $params[] = $filters['price_min'];
        }

        if (!empty($filters['price_max'])) {
            $where_conditions[] = 'p.price <= ?';
            $params[] = $filters['price_max'];
        }

        if (!empty($filters['in_stock'])) {
            $where_conditions[] = 'p.stock_quantity > 0';
        }

        if (!empty($filters['featured'])) {
            $where_conditions[] = 'p.is_featured = 1';
        }

        $where_clause = implode(' AND ', $where_conditions);

        $sql = "SELECT COUNT(*) FROM products p WHERE {$where_clause}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Получение рекомендуемых товаров
     */
    public function getFeatured($limit = 8) {
        $sql = "SELECT p.*, c.name as category_name, b.name as brand_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE p.is_active = 1 AND p.is_featured = 1
                ORDER BY p.created_at DESC
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Получение популярных товаров
     */
    public function getPopular($limit = 8) {
        $sql = "SELECT p.*, c.name as category_name, b.name as brand_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE p.is_active = 1
                ORDER BY p.views_count DESC
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Получение новых товаров
     */
    public function getNew($limit = 8) {
        $sql = "SELECT p.*, c.name as category_name, b.name as brand_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE p.is_active = 1
                ORDER BY p.created_at DESC
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Получение похожих товаров
     */
    public function getSimilar($product_id, $category_id, $limit = 4) {
        $sql = "SELECT p.*, c.name as category_name, b.name as brand_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE p.is_active = 1 AND p.category_id = ? AND p.id != ?
                ORDER BY RAND()
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$category_id, $product_id, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Увеличение счетчика просмотров
     */
    public function incrementViews($id) {
        $sql = "UPDATE products SET views_count = views_count + 1 WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Проверка наличия товара на складе
     */
    public function checkStock($id, $quantity = 1) {
        $sql = "SELECT stock_quantity FROM products WHERE id = ? AND is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $stock = $stmt->fetchColumn();

        return $stock !== false && $stock >= $quantity;
    }

    /**
     * Обновление остатков товара
     */
    public function updateStock($id, $quantity) {
        $sql = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$quantity, $id]);
    }

    /**
     * Получение диапазона цен
     */
    public function getPriceRange() {
        $sql = "SELECT MIN(price) as min_price, MAX(price) as max_price FROM products WHERE is_active = 1";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch();
    }
}
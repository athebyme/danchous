<?php
// autoparts/core/Category.php

class Category {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Получение категории по ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM categories WHERE id = ? AND is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Получение категории по slug
     */
    public function getBySlug($slug) {
        $sql = "SELECT * FROM categories WHERE slug = ? AND is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    /**
     * Получение всех активных категорий
     */
    public function getAll() {
        $sql = "SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Получение категорий верхнего уровня
     */
    public function getTopLevelCategories($limit = null) {
        $sql = "SELECT c.*,
                       (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_active = 1) as products_count
                FROM categories c
                WHERE c.parent_id IS NULL AND c.is_active = 1
                ORDER BY c.sort_order ASC, c.name ASC";

        if ($limit) {
            $sql .= " LIMIT ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$limit]);
        } else {
            $stmt = $this->pdo->query($sql);
        }

        return $stmt->fetchAll();
    }

    /**
     * Получение подкategорий
     */
    public function getSubcategories($parent_id) {
        $sql = "SELECT c.*,
                       (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_active = 1) as products_count
                FROM categories c
                WHERE c.parent_id = ? AND c.is_active = 1
                ORDER BY c.sort_order ASC, c.name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$parent_id]);
        return $stmt->fetchAll();
    }

    /**
     * Получение иерархии категорий (breadcrumb)
     */
    public function getCategoryPath($category_id) {
        $path = [];
        $current_id = $category_id;

        while ($current_id) {
            $sql = "SELECT id, name, slug, parent_id FROM categories WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$current_id]);
            $category = $stmt->fetch();

            if ($category) {
                array_unshift($path, $category);
                $current_id = $category['parent_id'];
            } else {
                break;
            }
        }

        return $path;
    }

    /**
     * Получение всех подкатегорий (включая вложенные)
     */
    public function getAllSubcategories($parent_id) {
        $subcategories = [];
        $this->collectSubcategories($parent_id, $subcategories);
        return $subcategories;
    }

    /**
     * Рекурсивный сбор подкатегорий
     */
    private function collectSubcategories($parent_id, &$subcategories) {
        $sql = "SELECT id FROM categories WHERE parent_id = ? AND is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$parent_id]);
        $children = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($children as $child_id) {
            $subcategories[] = $child_id;
            $this->collectSubcategories($child_id, $subcategories);
        }
    }

    /**
     * Построение дерева категорий
     */
    public function getCategoryTree() {
        // Получаем все категории
        $sql = "SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC";
        $stmt = $this->pdo->query($sql);
        $categories = $stmt->fetchAll();

        // Индексируем по ID
        $indexed = [];
        foreach ($categories as $category) {
            $category['children'] = [];
            $indexed[$category['id']] = $category;
        }

        // Строим дерево
        $tree = [];
        foreach ($indexed as $id => $category) {
            if ($category['parent_id']) {
                $indexed[$category['parent_id']]['children'][] = &$indexed[$id];
            } else {
                $tree[] = &$indexed[$id];
            }
        }

        return $tree;
    }

    /**
     * Получение популярных категорий (с наибольшим количеством товаров)
     */
    public function getPopularCategories($limit = 6) {
        $sql = "SELECT c.*, COUNT(p.id) as products_count
                FROM categories c
                LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
                WHERE c.is_active = 1
                GROUP BY c.id
                HAVING products_count > 0
                ORDER BY products_count DESC, c.name ASC
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Поиск категорий
     */
    public function search($query) {
        $sql = "SELECT * FROM categories
                WHERE is_active = 1 AND (name LIKE ? OR description LIKE ?)
                ORDER BY name ASC";
        $search_term = '%' . $query . '%';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$search_term, $search_term]);
        return $stmt->fetchAll();
    }

    /**
     * Создание новой категории
     */
    public function create($data) {
        $sql = "INSERT INTO categories (name, slug, description, parent_id, image_url, sort_order)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['description'] ?? null,
            $data['parent_id'] ?? null,
            $data['image_url'] ?? null,
            $data['sort_order'] ?? 0
        ]);
    }

    /**
     * Обновление категории
     */
    public function update($id, $data) {
        $sql = "UPDATE categories
                SET name = ?, slug = ?, description = ?, parent_id = ?, image_url = ?, sort_order = ?, updated_at = NOW()
                WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['description'] ?? null,
            $data['parent_id'] ?? null,
            $data['image_url'] ?? null,
            $data['sort_order'] ?? 0,
            $id
        ]);
    }

    /**
     * Проверка уникальности slug
     */
    public function isSlugUnique($slug, $exclude_id = null) {
        $sql = "SELECT COUNT(*) FROM categories WHERE slug = ?";
        $params = [$slug];

        if ($exclude_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() == 0;
    }
}
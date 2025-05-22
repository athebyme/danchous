<?php
// autoparts/core/Brand.php

class Brand {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Получение бренда по ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM brands WHERE id = ? AND is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Получение бренда по slug
     */
    public function getBySlug($slug) {
        $sql = "SELECT * FROM brands WHERE slug = ? AND is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    /**
     * Получение всех активных брендов
     */
    public function getAll($limit = null) {
        $sql = "SELECT b.*,
                       (SELECT COUNT(*) FROM products p WHERE p.brand_id = b.id AND p.is_active = 1) as products_count
                FROM brands b
                WHERE b.is_active = 1
                ORDER BY b.name ASC";

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
     * Получение популярных брендов (с наибольшим количеством товаров)
     */
    public function getPopular($limit = 10) {
        $sql = "SELECT b.*, COUNT(p.id) as products_count
                FROM brands b
                LEFT JOIN products p ON b.id = p.brand_id AND p.is_active = 1
                WHERE b.is_active = 1
                GROUP BY b.id
                HAVING products_count > 0
                ORDER BY products_count DESC, b.name ASC
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Поиск брендов
     */
    public function search($query, $limit = 10) {
        $sql = "SELECT * FROM brands
                WHERE is_active = 1 AND (name LIKE ? OR description LIKE ?)
                ORDER BY name ASC
                LIMIT ?";
        $search_term = '%' . $query . '%';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$search_term, $search_term, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Создание нового бренда
     */
    public function create($data) {
        $sql = "INSERT INTO brands (name, slug, description, logo_url)
                VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['description'] ?? null,
            $data['logo_url'] ?? null
        ]);
    }

    /**
     * Обновление бренда
     */
    public function update($id, $data) {
        $sql = "UPDATE brands
                SET name = ?, slug = ?, description = ?, logo_url = ?
                WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['description'] ?? null,
            $data['logo_url'] ?? null,
            $id
        ]);
    }

    /**
     * Проверка уникальности slug
     */
    public function isSlugUnique($slug, $exclude_id = null) {
        $sql = "SELECT COUNT(*) FROM brands WHERE slug = ?";
        $params = [$slug];

        if ($exclude_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() == 0;
    }

    /**
     * Деактивация бренда
     */
    public function deactivate($id) {
        $sql = "UPDATE brands SET is_active = 0 WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Активация бренда
     */
    public function activate($id) {
        $sql = "UPDATE brands SET is_active = 1 WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
}
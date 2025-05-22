<?php
// autoparts/api/search.php

header('Content-Type: application/json');

require_once __DIR__ . '/../config/init.php';

$response = ['success' => false, 'results' => [], 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['q'])) {
    $query = trim($_GET['q']);
    $limit = isset($_GET['limit']) ? min(20, max(1, (int)$_GET['limit'])) : 10;

    if (strlen($query) < 2) {
        $response['message'] = 'Запрос должен содержать минимум 2 символа';
        echo json_encode($response);
        exit;
    }

    try {
        $pdo = get_db_connection();

        // Поиск товаров
        $sql = "SELECT p.id, p.name, p.slug, p.price, p.image_url_main, p.short_description,
                       b.name as brand_name, c.name as category_name
                FROM products p
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.is_active = 1
                AND (p.name LIKE :query
                     OR p.description LIKE :query
                     OR p.short_description LIKE :query
                     OR p.sku LIKE :query
                     OR b.name LIKE :query)
                ORDER BY
                    CASE
                        WHEN p.name LIKE :exact_query THEN 1
                        WHEN p.name LIKE :start_query THEN 2
                        WHEN p.sku LIKE :exact_query THEN 3
                        ELSE 4
                    END,
                    p.views_count DESC,
                    p.name ASC
                LIMIT :limit";

        $stmt = $pdo->prepare($sql);
        $search_term = '%' . $query . '%';
        $exact_term = $query . '%';

        $stmt->bindParam(':query', $search_term, PDO::PARAM_STR);
        $stmt->bindParam(':exact_query', $exact_term, PDO::PARAM_STR);
        $stmt->bindParam(':start_query', $exact_term, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

        $stmt->execute();
        $products = $stmt->fetchAll();

        $results = [];
        foreach ($products as $product) {
            $results[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'slug' => $product['slug'],
                'price' => $product['price'],
                'price_formatted' => format_price($product['price']),
                'image_url_main' => $product['image_url_main'],
                'short_description' => $product['short_description'],
                'brand_name' => $product['brand_name'],
                'category_name' => $product['category_name'],
                'url' => SITE_URL . '/pages/product.php?slug=' . $product['slug']
            ];
        }

        $response = [
            'success' => true,
            'results' => $results,
            'query' => $query,
            'total' => count($results)
        ];

    } catch (Exception $e) {
        error_log("Search Error: " . $e->getMessage());
        $response['message'] = 'Ошибка при выполнении поиска';
    }
} else {
    $response['message'] = 'Неверный запрос';
}

echo json_encode($response);
exit;
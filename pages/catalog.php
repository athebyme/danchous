<?php
// autoparts/pages/catalog.php

$page_title = "Каталог запчастей";
require_once __DIR__ . '/../includes/header.php';

// Получаем модели
$product_model = new Product(get_db_connection());
$category_model = new Category(get_db_connection());
// Временно отключаем бренды до создания класса
// $brand_model = new Brand(get_db_connection());

// Получаем параметры фильтрации
$filters = [
    'category' => $_GET['category'] ?? '',
    'brand' => $_GET['brand'] ?? '',
    'search' => $_GET['q'] ?? '',
    'price_min' => isset($_GET['price_min']) && is_numeric($_GET['price_min']) ? (float)$_GET['price_min'] : null,
    'price_max' => isset($_GET['price_max']) && is_numeric($_GET['price_max']) ? (float)$_GET['price_max'] : null,
    'in_stock' => isset($_GET['in_stock']) ? true : false,
    'featured' => isset($_GET['featured']) ? true : false,
    'sort' => $_GET['sort'] ?? 'newest',
    'page' => isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1,
    'limit' => 12
];

// Получаем ID категории по slug
$current_category = null;
if ($filters['category']) {
    $current_category = $category_model->getBySlug($filters['category']);
    if ($current_category) {
        $filters['category_id'] = $current_category['id'];
    }
}

// Получаем ID бренда по slug (временно отключено)
$current_brand = null;
/*
if ($filters['brand']) {
    $current_brand = $brand_model->getBySlug($filters['brand']);
    if ($current_brand) {
        $filters['brand_id'] = $current_brand['id'];
    }
}
*/

// Получаем товары и общее количество
$products = $product_model->getList($filters);
$total_products = $product_model->getCount($filters);
$total_pages = ceil($total_products / $filters['limit']);

// Получаем данные для фильтров
$categories = $category_model->getTopLevelCategories();
$brands = []; // Временно пустой массив брендов
$price_range = $product_model->getPriceRange();

// Хлебные крошки
$breadcrumbs = [
    ['name' => 'Главная', 'url' => SITE_URL . '/'],
    ['name' => 'Каталог', 'url' => SITE_URL . '/pages/catalog.php']
];

if ($current_category) {
    $category_path = $category_model->getCategoryPath($current_category['id']);
    foreach ($category_path as $cat) {
        $breadcrumbs[] = [
            'name' => $cat['name'],
            'url' => SITE_URL . '/pages/catalog.php?category=' . $cat['slug']
        ];
    }
}

// Заголовок страницы
$page_heading = 'Каталог запчастей';
if ($current_category) {
    $page_heading = $current_category['name'];
    $page_title = $current_category['name'] . ' - Каталог запчастей';
} elseif ($filters['search']) {
    $page_heading = 'Результаты поиска: "' . htmlspecialchars($filters['search']) . '"';
    $page_title = 'Поиск: ' . htmlspecialchars($filters['search']);
} elseif ($filters['featured']) {
    $page_heading = 'Рекомендуемые товары';
    $page_title = 'Рекомендуемые товары';
}
?>

<div class="catalog-page">
    <!-- Хлебные крошки -->
    <nav class="breadcrumbs">
        <div class="container">
            <ol style="display: flex; list-style: none; padding: 0; margin: 1rem 0; gap: 0.5rem; font-size: 14px;">
                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                    <li>
                        <?php if ($index < count($breadcrumbs) - 1): ?>
                            <a href="<?php echo $crumb['url']; ?>" style="color: var(--medium-gray);">
                                <?php echo htmlspecialchars($crumb['name']); ?>
                            </a>
                            <span style="margin: 0 0.5rem; color: var(--medium-gray);">/</span>
                        <?php else: ?>
                            <span style="color: var(--dark-color); font-weight: 600;">
                                <?php echo htmlspecialchars($crumb['name']); ?>
                            </span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </nav>

    <div class="container">
        <!-- Заголовок и информация -->
        <div class="catalog-header">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div>
                    <h1><?php echo htmlspecialchars($page_heading); ?></h1>
                    <?php if ($current_category && $current_category['description']): ?>
                        <p style="color: var(--medium-gray); margin-top: 0.5rem;">
                            <?php echo htmlspecialchars($current_category['description']); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div style="font-size: 14px; color: var(--medium-gray);">
                    Найдено товаров: <strong><?php echo $total_products; ?></strong>
                </div>
            </div>
        </div>

        <div class="catalog-content" style="display: grid; grid-template-columns: 280px 1fr; gap: 2rem;">
            <!-- Боковая панель с фильтрами -->
            <aside class="catalog-sidebar">
                <div class="filters-container" style="background: white; border-radius: var(--border-radius); padding: 1.5rem; box-shadow: var(--shadow-soft);">
                    <h3 style="margin-bottom: 1.5rem; color: var(--dark-color);">Фильтры</h3>

                    <form method="GET" action="" class="filters-form">
                        <!-- Сохраняем текущие параметры -->
                        <?php if ($filters['search']): ?>
                            <input type="hidden" name="q" value="<?php echo htmlspecialchars($filters['search']); ?>">
                        <?php endif; ?>

                        <!-- Категории -->
                        <?php if (!$current_category && !empty($categories)): ?>
                        <div class="filter-group" style="margin-bottom: 2rem;">
                            <h4 style="margin-bottom: 1rem; font-size: 1rem;">Категории</h4>
                            <?php foreach ($categories as $category): ?>
                                <label style="display: flex; align-items: center; margin-bottom: 0.5rem; cursor: pointer;">
                                    <input type="radio" name="category" value="<?php echo htmlspecialchars($category['slug']); ?>"
                                           <?php echo ($filters['category'] === $category['slug']) ? 'checked' : ''; ?>
                                           style="margin-right: 0.5rem;">
                                    <span style="font-size: 14px;">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                        <small style="color: var(--medium-gray);">(<?php echo $category['products_count']; ?>)</small>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Бренды -->
                        <?php if (!empty($brands)): ?>
                        <div class="filter-group" style="margin-bottom: 2rem;">
                            <h4 style="margin-bottom: 1rem; font-size: 1rem;">Бренды</h4>
                            <div style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($brands as $brand): ?>
                                    <label style="display: flex; align-items: center; margin-bottom: 0.5rem; cursor: pointer;">
                                        <input type="checkbox" name="brand" value="<?php echo htmlspecialchars($brand['slug']); ?>"
                                               <?php echo ($filters['brand'] === $brand['slug']) ? 'checked' : ''; ?>
                                               style="margin-right: 0.5rem;">
                                        <span style="font-size: 14px;"><?php echo htmlspecialchars($brand['name']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Цена -->
                        <?php if ($price_range): ?>
                        <div class="filter-group" style="margin-bottom: 2rem;">
                            <h4 style="margin-bottom: 1rem; font-size: 1rem;">Цена, ₽</h4>
                            <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                                <input type="number" name="price_min" placeholder="От"
                                       value="<?php echo $filters['price_min'] ?? ''; ?>"
                                       min="<?php echo floor($price_range['min_price']); ?>"
                                       max="<?php echo ceil($price_range['max_price']); ?>"
                                       class="form-control" style="flex: 1;">
                                <input type="number" name="price_max" placeholder="До"
                                       value="<?php echo $filters['price_max'] ?? ''; ?>"
                                       min="<?php echo floor($price_range['min_price']); ?>"
                                       max="<?php echo ceil($price_range['max_price']); ?>"
                                       class="form-control" style="flex: 1;">
                            </div>
                            <div style="font-size: 12px; color: var(--medium-gray);">
                                Диапазон: <?php echo format_price($price_range['min_price']); ?> - <?php echo format_price($price_range['max_price']); ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Дополнительные фильтры -->
                        <div class="filter-group" style="margin-bottom: 2rem;">
                            <h4 style="margin-bottom: 1rem; font-size: 1rem;">Дополнительно</h4>
                            <label style="display: flex; align-items: center; margin-bottom: 0.5rem; cursor: pointer;">
                                <input type="checkbox" name="in_stock" value="1"
                                       <?php echo $filters['in_stock'] ? 'checked' : ''; ?>
                                       style="margin-right: 0.5rem;">
                                <span style="font-size: 14px;">Только в наличии</span>
                            </label>
                            <label style="display: flex; align-items: center; margin-bottom: 0.5rem; cursor: pointer;">
                                <input type="checkbox" name="featured" value="1"
                                       <?php echo $filters['featured'] ? 'checked' : ''; ?>
                                       style="margin-right: 0.5rem;">
                                <span style="font-size: 14px;">Рекомендуемые</span>
                            </label>
                        </div>

                        <div style="display: flex; gap: 0.5rem;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                <i class="fas fa-filter"></i> Применить
                            </button>
                            <a href="<?php echo SITE_URL; ?>/pages/catalog.php" class="btn btn-outline" style="flex: 1;">
                                <i class="fas fa-times"></i> Сбросить
                            </a>
                        </div>
                    </form>
                </div>
            </aside>

            <!-- Основной контент -->
            <main class="catalog-main">
                <!-- Сортировка -->
                <div class="catalog-toolbar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: white; padding: 1rem 1.5rem; border-radius: var(--border-radius); box-shadow: var(--shadow-soft);">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <span style="font-size: 14px; color: var(--medium-gray);">Сортировка:</span>
                        <select name="sort" onchange="changeSorting(this.value)" class="form-control" style="width: auto; min-width: 200px;">
                            <option value="newest" <?php echo ($filters['sort'] === 'newest') ? 'selected' : ''; ?>>Сначала новые</option>
                            <option value="popular" <?php echo ($filters['sort'] === 'popular') ? 'selected' : ''; ?>>По популярности</option>
                            <option value="price_asc" <?php echo ($filters['sort'] === 'price_asc') ? 'selected' : ''; ?>>Цена: по возрастанию</option>
                            <option value="price_desc" <?php echo ($filters['sort'] === 'price_desc') ? 'selected' : ''; ?>>Цена: по убыванию</option>
                            <option value="name_asc" <?php echo ($filters['sort'] === 'name_asc') ? 'selected' : ''; ?>>По названию А-Я</option>
                            <option value="name_desc" <?php echo ($filters['sort'] === 'name_desc') ? 'selected' : ''; ?>>По названию Я-А</option>
                        </select>
                    </div>

                    <div style="font-size: 14px; color: var(--medium-gray);">
                        Показано <?php echo count($products); ?> из <?php echo $total_products; ?> товаров
                    </div>
                </div>

                <!-- Список товаров -->
                <?php if (!empty($products)): ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <div class="product-card-image">
                                    <img src="<?php echo $product['image_url_main'] ?: '/api/placeholder/300/300'; ?>"
                                         alt="<?php echo htmlspecialchars($product['name']); ?>">

                                    <?php if ($product['old_price'] && $product['old_price'] > $product['price']): ?>
                                        <?php $discount = round(($product['old_price'] - $product['price']) / $product['old_price'] * 100); ?>
                                        <span class="product-badge sale">-<?php echo $discount; ?>%</span>
                                    <?php elseif ($product['is_featured']): ?>
                                        <span class="product-badge">Хит</span>
                                    <?php endif; ?>
                                </div>

                                <div class="product-card-content">
                                    <h3 class="product-title">
                                        <a href="<?php echo SITE_URL; ?>/pages/product.php?slug=<?php echo htmlspecialchars($product['slug']); ?>">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </a>
                                    </h3>

                                    <?php if ($product['brand_name']): ?>
                                        <p style="font-size: 12px; color: var(--medium-gray); margin-bottom: 0.5rem;">
                                            Бренд: <strong><?php echo htmlspecialchars($product['brand_name']); ?></strong>
                                        </p>
                                    <?php endif; ?>

                                    <?php if ($product['short_description']): ?>
                                        <p class="product-description"><?php echo htmlspecialchars($product['short_description']); ?></p>
                                    <?php endif; ?>

                                    <div class="product-price">
                                        <span class="price-current"><?php echo format_price($product['price']); ?></span>
                                        <?php if ($product['old_price'] && $product['old_price'] > $product['price']): ?>
                                            <span class="price-old"><?php echo format_price($product['old_price']); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="product-actions">
                                        <?php if ($product['stock_quantity'] > 0): ?>
                                            <button class="btn btn-primary btn-add-to-cart"
                                                    data-product-id="<?php echo $product['id']; ?>"
                                                    data-product-name="<?php echo htmlspecialchars($product['name']); ?>">
                                                <i class="fas fa-cart-plus"></i> В корзину
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-secondary" disabled>
                                                <i class="fas fa-times"></i> Нет в наличии
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Пагинация -->
                    <?php if ($total_pages > 1): ?>
                        <nav class="pagination-nav" style="margin-top: 3rem; text-align: center;">
                            <div class="pagination" style="display: inline-flex; gap: 0.5rem; align-items: center;">
                                <?php if ($filters['page'] > 1): ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $filters['page'] - 1])); ?>"
                                       class="btn btn-outline">
                                        <i class="fas fa-arrow-left"></i> Назад
                                    </a>
                                <?php endif; ?>

                                <?php
                                $start_page = max(1, $filters['page'] - 2);
                                $end_page = min($total_pages, $filters['page'] + 2);
                                ?>

                                <?php if ($start_page > 1): ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>"
                                       class="btn btn-outline">1</a>
                                    <?php if ($start_page > 2): ?>
                                        <span style="padding: 0 0.5rem;">...</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <?php if ($i == $filters['page']): ?>
                                        <span class="btn btn-primary"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
                                           class="btn btn-outline"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($end_page < $total_pages): ?>
                                    <?php if ($end_page < $total_pages - 1): ?>
                                        <span style="padding: 0 0.5rem;">...</span>
                                    <?php endif; ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>"
                                       class="btn btn-outline"><?php echo $total_pages; ?></a>
                                <?php endif; ?>

                                <?php if ($filters['page'] < $total_pages): ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $filters['page'] + 1])); ?>"
                                       class="btn btn-outline">
                                        Вперед <i class="fas fa-arrow-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </nav>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- Сообщение о том, что товары не найдены -->
                    <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: var(--border-radius); box-shadow: var(--shadow-soft);">
                        <i class="fas fa-search" style="font-size: 4rem; color: var(--medium-gray); margin-bottom: 1rem;"></i>
                        <h3>Товары не найдены</h3>
                        <p style="color: var(--medium-gray); margin-bottom: 2rem;">
                            <?php if ($filters['search']): ?>
                                По вашему запросу "<?php echo htmlspecialchars($filters['search']); ?>" ничего не найдено.
                            <?php else: ?>
                                В данной категории пока нет товаров.
                            <?php endif; ?>
                        </p>
                        <div style="display: flex; gap: 1rem; justify-content: center;">
                            <a href="<?php echo SITE_URL; ?>/pages/catalog.php" class="btn btn-primary">
                                <i class="fas fa-list"></i> Весь каталог
                            </a>
                            <a href="<?php echo SITE_URL; ?>/" class="btn btn-outline">
                                <i class="fas fa-home"></i> На главную
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</div>

<style>
/* Адаптивность для каталога */
@media (max-width: 768px) {
    .catalog-content {
        grid-template-columns: 1fr !important;
    }

    .catalog-sidebar {
        order: 2;
    }

    .catalog-main {
        order: 1;
    }

    .catalog-toolbar {
        flex-direction: column !important;
        gap: 1rem;
        align-items: stretch !important;
    }

    .products-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}

@media (max-width: 480px) {
    .products-grid {
        grid-template-columns: 1fr !important;
    }

    .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }
}
</style>

<script>
function changeSorting(sort) {
    const url = new URL(window.location);
    url.searchParams.set('sort', sort);
    url.searchParams.delete('page'); // Сбрасываем страницу при смене сортировки
    window.location.href = url.toString();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
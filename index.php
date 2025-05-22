<?php
// autoparts/index.php

$page_title = "Главная страница";
require_once __DIR__ . '/includes/header.php';

// Получаем данные для главной страницы
$product_model = new Product(get_db_connection());
$category_model = new Category(get_db_connection());

$featured_products = $product_model->getFeatured(8);
$new_products = $product_model->getNew(8);
$popular_products = $product_model->getPopular(8);
$main_categories = $category_model->getTopLevelCategories(8);
?>

<!-- Герой секция -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">Качественные автозапчасти для вашего автомобиля</h1>
            <p class="hero-subtitle">Более 10 000 оригинальных запчастей от ведущих производителей. Быстрая доставка по всей стране.</p>
            <div class="hero-actions">
                <a href="<?php echo SITE_URL; ?>/pages/catalog.php" class="btn btn-primary btn-large">
                    <i class="fas fa-search"></i> Перейти в каталог
                </a>
                <a href="#categories" class="btn btn-outline btn-large">
                    <i class="fas fa-list"></i> Категории товаров
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Основные категории -->
<section class="section" id="categories">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Категории запчастей</h2>
            <p class="section-subtitle">Выберите нужную категорию для быстрого поиска запчастей</p>
        </div>

        <?php if (!empty($main_categories)): ?>
        <div class="categories-grid">
            <?php foreach ($main_categories as $category): ?>
            <a href="<?php echo SITE_URL; ?>/pages/catalog.php?category=<?php echo htmlspecialchars($category['slug']); ?>" class="category-card">
                <div class="category-icon">
                    <i class="fas fa-<?php echo $category['icon'] ?? 'cog'; ?>"></i>
                </div>
                <h3 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h3>
                <p class="category-count"><?php echo $category['products_count'] ?? 0; ?> товаров</p>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Рекомендуемые товары -->
<?php if (!empty($featured_products)): ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Рекомендуемые товары</h2>
            <p class="section-subtitle">Лучшие предложения от наших экспертов</p>
        </div>

        <div class="products-grid">
            <?php foreach ($featured_products as $product): ?>
            <div class="product-card">
                <div class="product-card-image">
                    <img src="<?php echo $product['image_url_main'] ?: '/api/placeholder/300/300'; ?>"
                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php if ($product['old_price'] && $product['old_price'] > $product['price']): ?>
                        <span class="product-badge sale">Скидка</span>
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

        <div style="text-align: center; margin-top: 2rem;">
            <a href="<?php echo SITE_URL; ?>/pages/catalog.php?featured=1" class="btn btn-outline">
                <i class="fas fa-arrow-right"></i> Все рекомендуемые товары
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Новые товары -->
<?php if (!empty($new_products)): ?>
<section class="section" style="background: var(--light-gray);">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Новые поступления</h2>
            <p class="section-subtitle">Последние новинки в нашем каталоге</p>
        </div>

        <div class="products-grid">
            <?php foreach ($new_products as $product): ?>
            <div class="product-card">
                <div class="product-card-image">
                    <img src="<?php echo $product['image_url_main'] ?: '/api/placeholder/300/300'; ?>"
                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <span class="product-badge new">Новинка</span>
                </div>
                <div class="product-card-content">
                    <h3 class="product-title">
                        <a href="<?php echo SITE_URL; ?>/pages/product.php?slug=<?php echo htmlspecialchars($product['slug']); ?>">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </a>
                    </h3>

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

        <div style="text-align: center; margin-top: 2rem;">
            <a href="<?php echo SITE_URL; ?>/pages/catalog.php?sort=newest" class="btn btn-outline">
                <i class="fas fa-arrow-right"></i> Все новинки
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Преимущества -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Почему выбирают нас</h2>
            <p class="section-subtitle">Наши преимущества делают покупку запчастей простой и выгодной</p>
        </div>

        <div class="categories-grid">
            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <h3 class="category-name">Быстрая доставка</h3>
                <p class="category-count">Доставка по городу в день заказа</p>
            </div>

            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="category-name">Гарантия качества</h3>
                <p class="category-count">Только оригинальные запчасти</p>
            </div>

            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-tools"></i>
                </div>
                <h3 class="category-name">Экспертная поддержка</h3>
                <p class="category-count">Поможем подобрать нужную деталь</p>
            </div>

            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h3 class="category-name">Удобная оплата</h3>
                <p class="category-count">Наличные, карта, безналичный расчет</p>
            </div>
        </div>
    </div>
</section>

<!-- Популярные товары -->
<?php if (!empty($popular_products)): ?>
<section class="section" style="background: var(--light-gray);">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Популярные товары</h2>
            <p class="section-subtitle">Товары, которые чаще всего покупают наши клиенты</p>
        </div>

        <div class="products-grid">
            <?php foreach ($popular_products as $product): ?>
            <div class="product-card">
                <div class="product-card-image">
                    <img src="<?php echo $product['image_url_main'] ?: '/api/placeholder/300/300'; ?>"
                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php if ($product['old_price'] && $product['old_price'] > $product['price']): ?>
                        <span class="product-badge sale">Скидка</span>
                    <?php endif; ?>
                </div>
                <div class="product-card-content">
                    <h3 class="product-title">
                        <a href="<?php echo SITE_URL; ?>/pages/product.php?slug=<?php echo htmlspecialchars($product['slug']); ?>">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </a>
                    </h3>

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

        <div style="text-align: center; margin-top: 2rem;">
            <a href="<?php echo SITE_URL; ?>/pages/catalog.php?sort=popular" class="btn btn-outline">
                <i class="fas fa-arrow-right"></i> Все популярные товары
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
/* Дополнительные стили для главной страницы */
.hero-section {
    position: relative;
    overflow: hidden;
}

.hero-section::after {
    content: '';
    position: absolute;
    top: 0;
    right: -50%;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
    opacity: 0.3;
    transform: rotate(15deg);
}

.categories-grid .category-card:nth-child(odd) .category-icon {
    background: linear-gradient(135deg, var(--secondary-color), #1e6b8c);
}

.categories-grid .category-card:nth-child(3n) .category-icon {
    background: linear-gradient(135deg, var(--accent-color), #e08218);
}

@media (max-width: 768px) {
    .hero-title {
        font-size: 2.2rem;
    }

    .hero-subtitle {
        font-size: 1.1rem;
    }

    .categories-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .products-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
}

@media (max-width: 480px) {
    .categories-grid {
        grid-template-columns: 1fr;
    }

    .products-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
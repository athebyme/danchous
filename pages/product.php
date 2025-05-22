<?php
// autoparts/pages/product.php

require_once __DIR__ . '/../includes/header.php';

$product_model = new Product(get_db_connection());
$category_model = new Category(get_db_connection());

// Получаем товар по slug
$product_slug = $_GET['slug'] ?? '';
if (empty($product_slug)) {
    redirect(SITE_URL . '/pages/catalog.php');
}

$product = $product_model->getBySlug($product_slug);
if (!$product) {
    // Товар не найден
    http_response_code(404);
    $page_title = "Товар не найден";
    ?>
    <div style="text-align: center; padding: 4rem 2rem;">
        <i class="fas fa-search" style="font-size: 4rem; color: var(--medium-gray); margin-bottom: 1rem;"></i>
        <h1>Товар не найден</h1>
        <p style="color: var(--medium-gray); margin-bottom: 2rem;">Запрашиваемый товар не существует или был удален.</p>
        <a href="<?php echo SITE_URL; ?>/pages/catalog.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Вернуться в каталог
        </a>
    </div>
    <?php
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Увеличиваем счетчик просмотров
$product_model->incrementViews($product['id']);

// Устанавливаем заголовок страницы
$page_title = $product['name'];

// Получаем похожие товары
$similar_products = $product_model->getSimilar($product['id'], $product['category_id'], 4);

// Хлебные крошки
$breadcrumbs = [
    ['name' => 'Главная', 'url' => SITE_URL . '/'],
    ['name' => 'Каталог', 'url' => SITE_URL . '/pages/catalog.php']
];

if ($product['category_id']) {
    $category_path = $category_model->getCategoryPath($product['category_id']);
    foreach ($category_path as $cat) {
        $breadcrumbs[] = [
            'name' => $cat['name'],
            'url' => SITE_URL . '/pages/catalog.php?category=' . $cat['slug']
        ];
    }
}

$breadcrumbs[] = ['name' => $product['name'], 'url' => ''];

// Дополнительные изображения
$additional_images = [];
if ($product['images']) {
    $images_data = json_decode($product['images'], true);
    if (is_array($images_data)) {
        $additional_images = $images_data;
    }
}

// Характеристики товара
$specifications = [];
if ($product['specifications']) {
    $specs_data = json_decode($product['specifications'], true);
    if (is_array($specs_data)) {
        $specifications = $specs_data;
    }
}
?>

<div class="product-page">
    <!-- Хлебные крошки -->
    <nav class="breadcrumbs">
        <div class="container">
            <ol style="display: flex; list-style: none; padding: 0; margin: 1rem 0; gap: 0.5rem; font-size: 14px; flex-wrap: wrap;">
                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                    <li style="display: flex; align-items: center;">
                        <?php if ($index < count($breadcrumbs) - 1 && !empty($crumb['url'])): ?>
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
        <!-- Основная информация о товаре -->
        <div class="product-main" style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-bottom: 3rem;">

            <!-- Изображения товара -->
            <div class="product-gallery">
                <div class="main-image" style="position: relative; aspect-ratio: 1; border-radius: var(--border-radius); overflow: hidden; background: var(--light-gray); margin-bottom: 1rem;">
                    <img id="mainProductImage"
                         src="<?php echo $product['image_url_main'] ?: '/api/placeholder/500/500'; ?>"
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         style="width: 100%; height: 100%; object-fit: cover;">

                    <?php if ($product['old_price'] && $product['old_price'] > $product['price']): ?>
                        <?php $discount = round(($product['old_price'] - $product['price']) / $product['old_price'] * 100); ?>
                        <div style="position: absolute; top: 1rem; left: 1rem; background: var(--danger-color); color: white; padding: 0.5rem 1rem; border-radius: var(--border-radius-small); font-weight: 600;">
                            -<?php echo $discount; ?>%
                        </div>
                    <?php endif; ?>

                    <?php if ($product['is_featured']): ?>
                        <div style="position: absolute; top: 1rem; right: 1rem; background: var(--primary-color); color: white; padding: 0.5rem 1rem; border-radius: var(--border-radius-small); font-weight: 600; font-size: 14px;">
                            ХИТ
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Миниатюры -->
                <?php if (!empty($additional_images) || $product['image_url_main']): ?>
                <div class="image-thumbnails" style="display: flex; gap: 0.5rem; overflow-x: auto;">
                    <?php if ($product['image_url_main']): ?>
                        <img src="<?php echo $product['image_url_main']; ?>"
                             alt="Главное фото"
                             onclick="changeMainImage('<?php echo $product['image_url_main']; ?>')"
                             style="width: 80px; height: 80px; object-fit: cover; border-radius: var(--border-radius-small); cursor: pointer; border: 2px solid var(--primary-color); flex-shrink: 0;">
                    <?php endif; ?>

                    <?php foreach ($additional_images as $img): ?>
                        <img src="<?php echo $img; ?>"
                             alt="Дополнительное фото"
                             onclick="changeMainImage('<?php echo $img; ?>')"
                             style="width: 80px; height: 80px; object-fit: cover; border-radius: var(--border-radius-small); cursor: pointer; border: 2px solid var(--border-color); flex-shrink: 0;">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Информация о товаре -->
            <div class="product-info">
                <div style="margin-bottom: 1rem;">
                    <?php if ($product['brand_name']): ?>
                        <div style="font-size: 14px; color: var(--primary-color); font-weight: 600; margin-bottom: 0.5rem;">
                            <?php echo htmlspecialchars($product['brand_name']); ?>
                        </div>
                    <?php endif; ?>

                    <h1 style="font-size: 2rem; line-height: 1.2; margin-bottom: 1rem;">
                        <?php echo htmlspecialchars($product['name']); ?>
                    </h1>

                    <div style="font-size: 14px; color: var(--medium-gray); margin-bottom: 1rem;">
                        Артикул: <strong><?php echo htmlspecialchars($product['sku']); ?></strong>
                        <?php if ($product['category_name']): ?>
                            | Категория: <strong><?php echo htmlspecialchars($product['category_name']); ?></strong>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Цена -->
                <div class="product-pricing" style="margin-bottom: 2rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color);">
                            <?php echo format_price($product['price']); ?>
                        </div>

                        <?php if ($product['old_price'] && $product['old_price'] > $product['price']): ?>
                            <div style="font-size: 1.2rem; color: var(--medium-gray); text-decoration: line-through;">
                                <?php echo format_price($product['old_price']); ?>
                            </div>
                            <div style="background: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 14px; font-weight: 600;">
                                Экономия <?php echo format_price($product['old_price'] - $product['price']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Наличие -->
                <div class="product-availability" style="margin-bottom: 2rem;">
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--success-color); font-weight: 600;">
                            <i class="fas fa-check-circle"></i>
                            В наличии
                            <?php if ($product['stock_quantity'] < 5): ?>
                                <span style="color: var(--warning-color);">(остается <?php echo $product['stock_quantity']; ?> шт.)</span>
                            <?php else: ?>
                                (<?php echo $product['stock_quantity']; ?> шт.)
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--danger-color); font-weight: 600;">
                            <i class="fas fa-times-circle"></i>
                            Нет в наличии
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Действия -->
                <div class="product-actions" style="margin-bottom: 2rem;">
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem;">
                            <div class="quantity-selector" style="display: flex; align-items: center; border: 2px solid var(--border-color); border-radius: var(--border-radius-small); overflow: hidden;">
                                <button onclick="changeQuantity(-1)" style="width: 40px; height: 50px; border: none; background: white; cursor: pointer; color: var(--medium-gray);">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" id="productQuantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>"
                                       style="width: 60px; height: 50px; text-align: center; border: none; border-left: 1px solid var(--border-color); border-right: 1px solid var(--border-color); font-weight: 600;">
                                <button onclick="changeQuantity(1)" style="width: 40px; height: 50px; border: none; background: white; cursor: pointer; color: var(--medium-gray);">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>

                            <button class="btn btn-primary btn-large btn-add-to-cart"
                                    data-product-id="<?php echo $product['id']; ?>"
                                    data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                    style="flex: 1;">
                                <i class="fas fa-cart-plus"></i> Добавить в корзину
                            </button>
                        </div>

                        <div style="display: flex; gap: 1rem;">
                            <button class="btn btn-outline" style="flex: 1;">
                                <i class="fas fa-heart"></i> В избранное
                            </button>
                            <button class="btn btn-outline" style="flex: 1;">
                                <i class="fas fa-balance-scale"></i> Сравнить
                            </button>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem; background: var(--light-gray); border-radius: var(--border-radius);">
                            <h4 style="margin-bottom: 1rem;">Товар временно отсутствует</h4>
                            <p style="margin-bottom: 1.5rem; color: var(--medium-gray);">Сообщим, когда товар появится в наличии</p>
                            <button class="btn btn-secondary">
                                <i class="fas fa-bell"></i> Уведомить о поступлении
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Дополнительная информация -->
                <div class="product-features" style="background: var(--light-gray); padding: 1.5rem; border-radius: var(--border-radius);">
                    <h4 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-info-circle" style="color: var(--primary-color);"></i>
                        Дополнительно
                    </h4>
                    <ul style="list-style: none; margin: 0; padding: 0; font-size: 14px; line-height: 1.6;">
                        <li style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <i class="fas fa-truck" style="color: var(--success-color);"></i>
                            Бесплатная доставка по городу
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <i class="fas fa-shield-alt" style="color: var(--success-color);"></i>
                            Гарантия качества
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <i class="fas fa-undo" style="color: var(--success-color);"></i>
                            Возврат в течение 14 дней
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-headset" style="color: var(--success-color);"></i>
                            Консультация специалиста
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Дополнительная информация -->
        <div class="product-details" style="background: white; border-radius: var(--border-radius); box-shadow: var(--shadow-soft); overflow: hidden; margin-bottom: 3rem;">
            <div class="tabs-navigation" style="display: flex; border-bottom: 1px solid var(--border-color);">
                <button class="tab-button active" onclick="showTab('description')" style="flex: 1; padding: 1rem; border: none; background: white; cursor: pointer; font-weight: 600; border-bottom: 3px solid transparent;">
                    Описание
                </button>
                <?php if (!empty($specifications)): ?>
                <button class="tab-button" onclick="showTab('specifications')" style="flex: 1; padding: 1rem; border: none; background: white; cursor: pointer; font-weight: 600; border-bottom: 3px solid transparent;">
                    Характеристики
                </button>
                <?php endif; ?>
                <button class="tab-button" onclick="showTab('delivery')" style="flex: 1; padding: 1rem; border: none; background: white; cursor: pointer; font-weight: 600; border-bottom: 3px solid transparent;">
                    Доставка
                </button>
            </div>

            <div class="tab-content" style="padding: 2rem;">
                <!-- Описание -->
                <div id="description" class="tab-panel">
                    <?php if ($product['description']): ?>
                        <div style="line-height: 1.6; color: var(--dark-color);">
                            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                        </div>
                    <?php else: ?>
                        <p style="color: var(--medium-gray); font-style: italic;">Подробное описание товара пока не добавлено.</p>
                    <?php endif; ?>
                </div>

                <!-- Характеристики -->
                <?php if (!empty($specifications)): ?>
                <div id="specifications" class="tab-panel" style="display: none;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <?php foreach ($specifications as $key => $value): ?>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 1rem 0; font-weight: 600; color: var(--dark-color); width: 30%;">
                                <?php echo htmlspecialchars($key); ?>
                            </td>
                            <td style="padding: 1rem 0; color: var(--medium-gray);">
                                <?php echo htmlspecialchars($value); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>

                <!-- Доставка -->
                <div id="delivery" class="tab-panel" style="display: none;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                        <div>
                            <h4 style="color: var(--primary-color); margin-bottom: 1rem;">
                                <i class="fas fa-shipping-fast"></i> Быстрая доставка
                            </h4>
                            <ul style="line-height: 1.6; color: var(--medium-gray);">
                                <li>Доставка по городу в день заказа</li>
                                <li>Доставка по области 1-2 дня</li>
                                <li>Точное время доставки по SMS</li>
                            </ul>
                        </div>
                        <div>
                            <h4 style="color: var(--primary-color); margin-bottom: 1rem;">
                                <i class="fas fa-hand-holding-usd"></i> Способы оплаты
                            </h4>
                            <ul style="line-height: 1.6; color: var(--medium-gray);">
                                <li>Наличными при получении</li>
                                <li>Банковской картой</li>
                                <li>Безналичный расчет</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Похожие товары -->
        <?php if (!empty($similar_products)): ?>
        <section class="similar-products">
            <h2 style="margin-bottom: 2rem;">Похожие товары</h2>
            <div class="products-grid">
                <?php foreach ($similar_products as $similar): ?>
                    <div class="product-card">
                        <div class="product-card-image">
                            <img src="<?php echo $similar['image_url_main'] ?: '/api/placeholder/300/300'; ?>"
                                 alt="<?php echo htmlspecialchars($similar['name']); ?>">
                            <?php if ($similar['is_featured']): ?>
                                <span class="product-badge">Хит</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-card-content">
                            <h3 class="product-title">
                                <a href="<?php echo SITE_URL; ?>/pages/product.php?slug=<?php echo htmlspecialchars($similar['slug']); ?>">
                                    <?php echo htmlspecialchars($similar['name']); ?>
                                </a>
                            </h3>
                            <div class="product-price">
                                <span class="price-current"><?php echo format_price($similar['price']); ?></span>
                            </div>
                            <div class="product-actions">
                                <button class="btn btn-primary btn-add-to-cart"
                                        data-product-id="<?php echo $similar['id']; ?>"
                                        data-product-name="<?php echo htmlspecialchars($similar['name']); ?>">
                                    <i class="fas fa-cart-plus"></i> В корзину
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
</div>

<style>
/* Стили для страницы товара */
.tab-button.active {
    background: var(--light-gray) !important;
    border-bottom-color: var(--primary-color) !important;
    color: var(--primary-color) !important;
}

.tab-button:hover {
    background: var(--light-gray);
}

.image-thumbnails img:hover {
    border-color: var(--primary-color) !important;
}

/* Адаптивность */
@media (max-width: 768px) {
    .product-main {
        grid-template-columns: 1fr !important;
        gap: 2rem !important;
    }

    .tabs-navigation {
        flex-direction: column !important;
    }

    .tab-button {
        flex: none !important;
    }

    .product-actions > div:first-child {
        flex-direction: column !important;
    }

    .quantity-selector {
        align-self: flex-start !important;
    }
}

@media (max-width: 480px) {
    .image-thumbnails {
        justify-content: center;
    }

    .product-pricing {
        text-align: center;
    }

    .product-pricing > div {
        flex-direction: column !important;
        align-items: center !important;
        gap: 0.5rem !important;
    }
}
</style>

<script>
function changeMainImage(src) {
    document.getElementById('mainProductImage').src = src;

    // Обновляем активную миниатюру
    document.querySelectorAll('.image-thumbnails img').forEach(img => {
        if (img.src === src) {
            img.style.borderColor = 'var(--primary-color)';
        } else {
            img.style.borderColor = 'var(--border-color)';
        }
    });
}

function changeQuantity(delta) {
    const input = document.getElementById('productQuantity');
    const currentValue = parseInt(input.value);
    const newValue = Math.max(1, Math.min(parseInt(input.max), currentValue + delta));
    input.value = newValue;

    // Обновляем data-quantity для кнопки добавления в корзину
    const addToCartBtn = document.querySelector('.btn-add-to-cart');
    addToCartBtn.setAttribute('data-quantity', newValue);
}

function showTab(tabName) {
    // Скрываем все панели
    document.querySelectorAll('.tab-panel').forEach(panel => {
        panel.style.display = 'none';
    });

    // Убираем активный класс у всех кнопок
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
    });

    // Показываем нужную панель
    document.getElementById(tabName).style.display = 'block';

    // Добавляем активный класс к кнопке
    event.target.classList.add('active');
}

// Обновляем количество при добавлении в корзину
document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.getElementById('productQuantity');
    const addToCartBtn = document.querySelector('.btn-add-to-cart');

    if (quantityInput && addToCartBtn) {
        quantityInput.addEventListener('change', function() {
            addToCartBtn.setAttribute('data-quantity', this.value);
        });

        // Устанавливаем начальное значение
        addToCartBtn.setAttribute('data-quantity', quantityInput.value);
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<?php
// autoparts/pages/cart.php

$page_title = "Корзина";
require_once __DIR__ . '/../includes/header.php';

$cart = new Cart();
$product_model = new Product(get_db_connection());

// Получаем товары из корзины
$cart_items = $cart->getItems();
$cart_data = $cart->getOrderData();
$validation_errors = [];

// Валидация корзины (проверка наличия на складе, актуальности цен)
if (!empty($cart_items)) {
    $validation = $cart->validateCart(get_db_connection());
    if (!$validation['valid']) {
        $validation_errors = $validation['errors'];
        // Обновляем данные корзины после валидации
        $cart_items = $cart->getItems();
        $cart_data = $cart->getOrderData();
    }
}
?>

<div class="cart-page">
    <div class="container">
        <!-- Заголовок -->
        <div class="page-header" style="margin-bottom: 2rem;">
            <h1><i class="fas fa-shopping-cart"></i> Корзина</h1>

            <!-- Хлебные крошки -->
            <nav style="margin-top: 1rem;">
                <ol style="display: flex; list-style: none; padding: 0; margin: 0; gap: 0.5rem; font-size: 14px;">
                    <li><a href="<?php echo SITE_URL; ?>/" style="color: var(--medium-gray);">Главная</a></li>
                    <li style="color: var(--medium-gray);">/</li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/catalog.php" style="color: var(--medium-gray);">Каталог</a></li>
                    <li style="color: var(--medium-gray);">/</li>
                    <li style="color: var(--dark-color); font-weight: 600;">Корзина</li>
                </ol>
            </nav>
        </div>

        <!-- Ошибки валидации -->
        <?php if (!empty($validation_errors)): ?>
            <div class="alert alert-warning" style="margin-bottom: 2rem;">
                <h4 style="margin-bottom: 1rem;"><i class="fas fa-exclamation-triangle"></i> Внимание!</h4>
                <ul style="margin: 0; padding-left: 1.2rem;">
                    <?php foreach ($validation_errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($cart_items)): ?>
            <div class="cart-content" style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem;">
                <!-- Список товаров -->
                <div class="cart-items-container">
                    <div style="background: white; border-radius: var(--border-radius); overflow: hidden; box-shadow: var(--shadow-soft);">
                        <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color); background: var(--light-gray);">
                            <h3 style="margin: 0; display: flex; justify-content: space-between; align-items: center;">
                                <span>Товары в корзине</span>
                                <button class="btn btn-outline btn-clear-cart" style="font-size: 14px; padding: 8px 16px;">
                                    <i class="fas fa-trash"></i> Очистить корзину
                                </button>
                            </h3>
                        </div>

                        <div class="cart-items-list">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="cart-item" data-product-id="<?php echo $item['id']; ?>">
                                    <div class="cart-item-content">
                                        <div class="cart-item-image">
                                            <img src="<?php echo $item['image'] ?: '/api/placeholder/80/80'; ?>"
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        </div>

                                        <div class="cart-item-details">
                                            <h4 class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></h4>
                                            <div class="cart-item-price">
                                                <?php echo format_price($item['price']); ?> за штуку
                                            </div>
                                        </div>

                                        <div class="cart-item-actions">
                                            <div class="quantity-controls">
                                                <button class="quantity-btn quantity-btn-minus" data-product-id="<?php echo $item['id']; ?>">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number"
                                                       class="quantity-input"
                                                       value="<?php echo $item['quantity']; ?>"
                                                       min="1"
                                                       max="99"
                                                       data-product-id="<?php echo $item['id']; ?>">
                                                <button class="quantity-btn quantity-btn-plus" data-product-id="<?php echo $item['id']; ?>">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>

                                            <div class="item-subtotal" style="font-weight: 600; color: var(--primary-color); min-width: 100px; text-align: center;">
                                                <?php echo format_price($item['price'] * $item['quantity']); ?>
                                            </div>

                                            <button class="btn-remove-from-cart"
                                                    data-product-id="<?php echo $item['id']; ?>"
                                                    style="background: none; border: none; color: var(--danger-color); padding: 8px; border-radius: 4px; cursor: pointer; transition: background-color 0.2s;"
                                                    onmouseover="this.style.backgroundColor='rgba(220, 53, 69, 0.1)'"
                                                    onmouseout="this.style.backgroundColor='transparent'">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Рекомендуемые товары -->
                    <div style="margin-top: 2rem;">
                        <h3 style="margin-bottom: 1rem;">Рекомендуем также</h3>
                        <?php
                        $recommended = $product_model->getFeatured(4);
                        if (!empty($recommended)):
                        ?>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                            <?php foreach ($recommended as $product): ?>
                                <div class="product-card" style="background: white;">
                                    <div class="product-card-image">
                                        <img src="<?php echo $product['image_url_main'] ?: '/api/placeholder/200/200'; ?>"
                                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    </div>
                                    <div class="product-card-content">
                                        <h4 style="font-size: 14px; margin-bottom: 0.5rem; line-height: 1.3;">
                                            <a href="<?php echo SITE_URL; ?>/pages/product.php?slug=<?php echo $product['slug']; ?>">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </a>
                                        </h4>
                                        <div style="font-weight: 600; color: var(--primary-color); margin-bottom: 0.5rem;">
                                            <?php echo format_price($product['price']); ?>
                                        </div>
                                        <button class="btn btn-primary btn-add-to-cart"
                                                style="width: 100%; font-size: 12px; padding: 8px;"
                                                data-product-id="<?php echo $product['id']; ?>"
                                                data-product-name="<?php echo htmlspecialchars($product['name']); ?>">
                                            <i class="fas fa-cart-plus"></i> В корзину
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Итоги заказа -->
                <div class="cart-summary">
                    <div style="background: white; border-radius: var(--border-radius); padding: 1.5rem; box-shadow: var(--shadow-soft); position: sticky; top: 20px;">
                        <h3 style="margin-bottom: 1.5rem; color: var(--dark-color);">
                            <i class="fas fa-calculator"></i> Итоги заказа
                        </h3>

                        <div class="summary-details">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
                                <span>Товаров:</span>
                                <span class="cart-total-items"><?php echo $cart_data['total_items']; ?> шт.</span>
                            </div>

                            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                                <span>Сумма товаров:</span>
                                <span class="cart-total-price"><?php echo format_price($cart_data['total_price']); ?></span>
                            </div>

                            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; color: var(--success-color);">
                                <span>Доставка:</span>
                                <span>Бесплатно</span>
                            </div>

                            <div style="border-top: 2px solid var(--primary-color); padding-top: 1rem; margin-top: 1rem;">
                                <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 700; color: var(--dark-color);">
                                    <span>Итого:</span>
                                    <span class="cart-total-price"><?php echo format_price($cart_data['total_price']); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="checkout-buttons" style="margin-top: 1.5rem;">
                            <a href="<?php echo SITE_URL; ?>/pages/checkout.php"
                               class="btn btn-primary btn-large"
                               style="width: 100%; margin-bottom: 1rem; justify-content: center;">
                                <i class="fas fa-credit-card"></i> Оформить заказ
                            </a>

                            <a href="<?php echo SITE_URL; ?>/pages/catalog.php"
                               class="btn btn-outline"
                               style="width: 100%; justify-content: center;">
                                <i class="fas fa-arrow-left"></i> Продолжить покупки
                            </a>
                        </div>

                        <!-- Информация о доставке -->
                        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                            <h4 style="font-size: 14px; margin-bottom: 1rem; color: var(--dark-color);">
                                <i class="fas fa-truck"></i> Информация о доставке
                            </h4>
                            <ul style="font-size: 12px; color: var(--medium-gray); line-height: 1.4; margin: 0; padding-left: 1rem;">
                                <li>Бесплатная доставка по городу</li>
                                <li>Доставка в день заказа</li>
                                <li>Возможность самовывоза</li>
                                <li>Оплата при получении</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Пустая корзина -->
            <div class="cart-empty-message">
                <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: var(--border-radius); box-shadow: var(--shadow-soft);">
                    <i class="fas fa-shopping-cart" style="font-size: 4rem; color: var(--medium-gray); margin-bottom: 1.5rem;"></i>
                    <h2 style="margin-bottom: 1rem;">Ваша корзина пуста</h2>
                    <p style="color: var(--medium-gray); margin-bottom: 2rem; max-width: 400px; margin-left: auto; margin-right: auto;">
                        Добавьте товары из каталога, чтобы оформить заказ. У нас большой выбор качественных автозапчастей по выгодным ценам.
                    </p>
                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <a href="<?php echo SITE_URL; ?>/pages/catalog.php" class="btn btn-primary btn-large">
                            <i class="fas fa-shopping-bag"></i> Перейти в каталог
                        </a>
                        <a href="<?php echo SITE_URL; ?>/" class="btn btn-outline btn-large">
                            <i class="fas fa-home"></i> На главную
                        </a>
                    </div>
                </div>

                <!-- Популярные товары для пустой корзины -->
                <?php
                $popular_products = $product_model->getPopular(4);
                if (!empty($popular_products)):
                ?>
                <div style="margin-top: 3rem;">
                    <h3 style="text-align: center; margin-bottom: 2rem;">Популярные товары</h3>
                    <div class="products-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                        <?php foreach ($popular_products as $product): ?>
                            <div class="product-card">
                                <div class="product-card-image">
                                    <img src="<?php echo $product['image_url_main'] ?: '/api/placeholder/250/250'; ?>"
                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                </div>
                                <div class="product-card-content">
                                    <h4 class="product-title">
                                        <a href="<?php echo SITE_URL; ?>/pages/product.php?slug=<?php echo $product['slug']; ?>">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </a>
                                    </h4>
                                    <div class="product-price">
                                        <span class="price-current"><?php echo format_price($product['price']); ?></span>
                                    </div>
                                    <div class="product-actions">
                                        <button class="btn btn-primary btn-add-to-cart"
                                                data-product-id="<?php echo $product['id']; ?>"
                                                data-product-name="<?php echo htmlspecialchars($product['name']); ?>">
                                            <i class="fas fa-cart-plus"></i> В корзину
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Стили для страницы корзины */
.cart-item {
    border-bottom: 1px solid var(--border-color);
    transition: background-color 0.2s ease;
}

.cart-item:hover {
    background-color: rgba(255, 107, 53, 0.02);
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item-content {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    gap: 1rem;
}

.cart-item-image {
    width: 80px;
    height: 80px;
    border-radius: var(--border-radius-small);
    overflow: hidden;
    flex-shrink: 0;
    background: var(--light-gray);
}

.cart-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cart-item-details {
    flex: 1;
    min-width: 0;
}

.cart-item-name {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    line-height: 1.3;
    color: var(--dark-color);
}

.cart-item-price {
    font-size: 14px;
    color: var(--medium-gray);
}

.cart-item-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-shrink: 0;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0;
    border: 2px solid var(--border-color);
    border-radius: var(--border-radius-small);
    overflow: hidden;
}

.quantity-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    color: var(--medium-gray);
    transition: var(--transition-fast);
}

.quantity-btn:hover {
    background: var(--light-gray);
    color: var(--primary-color);
}

.quantity-input {
    width: 50px;
    height: 36px;
    text-align: center;
    border: none;
    border-left: 1px solid var(--border-color);
    border-right: 1px solid var(--border-color);
    font-weight: 600;
    font-size: 14px;
}

.quantity-input:focus {
    outline: none;
    background: var(--light-gray);
}

/* Адаптивность */
@media (max-width: 992px) {
    .cart-content {
        grid-template-columns: 1fr !important;
    }

    .cart-summary {
        order: -1;
    }
}

@media (max-width: 768px) {
    .cart-item-content {
        flex-direction: column;
        align-items: flex-start;
        padding: 1rem;
    }

    .cart-item-image {
        width: 60px;
        height: 60px;
        align-self: center;
    }

    .cart-item-details {
        text-align: center;
        width: 100%;
    }

    .cart-item-actions {
        width: 100%;
        justify-content: space-between;
        margin-top: 1rem;
    }

    .quantity-controls {
        order: 1;
    }

    .item-subtotal {
        order: 2;
        min-width: auto !important;
    }

    .btn-remove-from-cart {
        order: 3;
    }
}

@media (max-width: 480px) {
    .cart-item-actions {
        flex-direction: column;
        gap: 1rem;
        align-items: center;
    }

    .quantity-controls {
        order: 1;
    }

    .item-subtotal {
        order: 2;
        font-size: 1.1rem;
    }

    .btn-remove-from-cart {
        order: 3;
    }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
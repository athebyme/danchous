// autoparts/assets/js/cart_ajax.js

class CartManager {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.updateCartDisplay();
    }

    bindEvents() {
        // Добавление в корзину
        document.addEventListener('click', (e) => {
            if (e.target.matches('.btn-add-to-cart, .btn-add-to-cart *')) {
                e.preventDefault();
                const button = e.target.closest('.btn-add-to-cart');
                this.addToCart(button);
            }
        });

        // Обновление количества в корзине
        document.addEventListener('change', (e) => {
            if (e.target.matches('.quantity-input')) {
                this.updateQuantity(e.target);
            }
        });

        // Кнопки увеличения/уменьшения количества
        document.addEventListener('click', (e) => {
            if (e.target.matches('.quantity-btn-plus')) {
                e.preventDefault();
                this.changeQuantity(e.target, 1);
            } else if (e.target.matches('.quantity-btn-minus')) {
                e.preventDefault();
                this.changeQuantity(e.target, -1);
            }
        });

        // Удаление из корзины
        document.addEventListener('click', (e) => {
            if (e.target.matches('.btn-remove-from-cart, .btn-remove-from-cart *')) {
                e.preventDefault();
                const button = e.target.closest('.btn-remove-from-cart');
                this.removeFromCart(button);
            }
        });

        // Очистка корзины
        document.addEventListener('click', (e) => {
            if (e.target.matches('.btn-clear-cart')) {
                e.preventDefault();
                if (confirm('Вы уверены, что хотите очистить корзину?')) {
                    this.clearCart();
                }
            }
        });
    }

    async addToCart(button) {
        const productId = button.dataset.productId;
        const productName = button.dataset.productName;
        const quantity = button.dataset.quantity || 1;

        if (!productId) {
            this.showMessage('Ошибка: не указан ID товара', 'error');
            return;
        }

        // Показываем loading состояние
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Добавление...';
        button.disabled = true;

        try {
            const response = await fetch(window.location.origin + '/api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'add',
                    product_id: productId,
                    quantity: quantity
                })
            });

            const data = await response.json();

            if (data.success) {
                this.showMessage(data.message, 'success');
                this.updateCartCounters(data.total_items, data.total_price_formatted);

                // Меняем кнопку на "В корзине"
                button.innerHTML = '<i class="fas fa-check"></i> В корзине';
                button.classList.remove('btn-primary');
                button.classList.add('btn-secondary');

                // Возвращаем кнопку в исходное состояние через 2 секунды
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('btn-secondary');
                    button.classList.add('btn-primary');
                    button.disabled = false;
                }, 2000);
            } else {
                this.showMessage(data.message, 'error');
                button.innerHTML = originalText;
                button.disabled = false;
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            this.showMessage('Произошла ошибка при добавлении товара в корзину', 'error');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    }

    async updateQuantity(input) {
        const productId = input.dataset.productId;
        const quantity = parseInt(input.value);

        if (!productId || quantity < 1) {
            return;
        }

        try {
            const response = await fetch(window.location.origin + '/api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'update',
                    product_id: productId,
                    quantity: quantity
                })
            });

            const data = await response.json();

            if (data.success) {
                this.updateCartCounters(data.total_items, data.total_price_formatted);

                // Обновляем промежуточную сумму для товара
                if (data.item_subtotal_formatted) {
                    const subtotalElement = input.closest('.cart-item').querySelector('.item-subtotal');
                    if (subtotalElement) {
                        subtotalElement.textContent = data.item_subtotal_formatted;
                    }
                }

                this.showMessage(data.message, 'success');
            } else {
                this.showMessage(data.message, 'error');
                // Возвращаем старое значение
                if (data.current_quantity_in_cart) {
                    input.value = data.current_quantity_in_cart;
                }
            }
        } catch (error) {
            console.error('Error updating quantity:', error);
            this.showMessage('Произошла ошибка при обновлении количества', 'error');
        }
    }

    changeQuantity(button, delta) {
        const input = button.parentElement.querySelector('.quantity-input');
        const currentValue = parseInt(input.value) || 1;
        const newValue = Math.max(1, currentValue + delta);

        input.value = newValue;
        this.updateQuantity(input);
    }

    async removeFromCart(button) {
        const productId = button.dataset.productId;

        if (!productId) {
            return;
        }

        // Показываем loading состояние
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;

        try {
            const response = await fetch(window.location.origin + '/api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'remove',
                    product_id: productId
                })
            });

            const data = await response.json();

            if (data.success) {
                this.showMessage(data.message, 'success');
                this.updateCartCounters(data.total_items, data.total_price_formatted);

                // Удаляем товар из DOM
                const cartItem = button.closest('.cart-item');
                if (cartItem) {
                    cartItem.style.transition = 'opacity 0.3s ease';
                    cartItem.style.opacity = '0';
                    setTimeout(() => {
                        cartItem.remove();
                        this.checkEmptyCart();
                    }, 300);
                }
            } else {
                this.showMessage(data.message, 'error');
                button.innerHTML = originalText;
                button.disabled = false;
            }
        } catch (error) {
            console.error('Error removing from cart:', error);
            this.showMessage('Произошла ошибка при удалении товара', 'error');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    }

    async clearCart() {
        try {
            const response = await fetch(window.location.origin + '/api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'clear'
                })
            });

            const data = await response.json();

            if (data.success) {
                this.showMessage(data.message, 'success');
                this.updateCartCounters(0, data.total_price_formatted);

                // Удаляем все товары из DOM
                const cartItems = document.querySelectorAll('.cart-item');
                cartItems.forEach(item => item.remove());
                this.checkEmptyCart();
            } else {
                this.showMessage(data.message, 'error');
            }
        } catch (error) {
            console.error('Error clearing cart:', error);
            this.showMessage('Произошла ошибка при очистке корзины', 'error');
        }
    }

    updateCartCounters(totalItems, totalPriceFormatted) {
        // Обновляем счетчик в шапке
        const headerCounter = document.getElementById('cart-item-count-header');
        if (headerCounter) {
            headerCounter.textContent = totalItems;
            headerCounter.style.display = totalItems > 0 ? 'inline' : 'none';
        }

        // Обновляем общую сумму на странице корзины
        const totalPriceElement = document.querySelector('.cart-total-price');
        if (totalPriceElement && totalPriceFormatted) {
            totalPriceElement.textContent = totalPriceFormatted;
        }

        // Обновляем счетчик товаров на странице корзины
        const totalItemsElement = document.querySelector('.cart-total-items');
        if (totalItemsElement) {
            totalItemsElement.textContent = totalItems;
        }
    }

    checkEmptyCart() {
        const cartContainer = document.querySelector('.cart-items-container');
        const emptyMessage = document.querySelector('.cart-empty-message');

        if (cartContainer && !cartContainer.querySelector('.cart-item')) {
            if (emptyMessage) {
                emptyMessage.style.display = 'block';
            } else {
                // Создаем сообщение о пустой корзине
                const emptyDiv = document.createElement('div');
                emptyDiv.className = 'cart-empty-message';
                emptyDiv.innerHTML = `
                    <div style="text-align: center; padding: 3rem;">
                        <i class="fas fa-shopping-cart" style="font-size: 4rem; color: var(--medium-gray); margin-bottom: 1rem;"></i>
                        <h3>Ваша корзина пуста</h3>
                        <p>Добавьте товары из каталога, чтобы оформить заказ</p>
                        <a href="/autoparts/pages/catalog.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i> Перейти в каталог
                        </a>
                    </div>
                `;
                cartContainer.appendChild(emptyDiv);
            }

            // Скрываем кнопки оформления заказа
            const checkoutButtons = document.querySelectorAll('.checkout-buttons');
            checkoutButtons.forEach(btn => btn.style.display = 'none');
        }
    }

    async updateCartDisplay() {
        try {
            const response = await fetch(window.location.origin + '/api/cart.php?action=get_cart_data');
            const data = await response.json();

            if (data.success) {
                this.updateCartCounters(data.total_items, data.total_price_formatted);
            }
        } catch (error) {
            console.error('Error updating cart display:', error);
        }
    }

    showMessage(message, type = 'info') {
        // Удаляем существующие уведомления
        const existingAlerts = document.querySelectorAll('.alert-notification');
        existingAlerts.forEach(alert => alert.remove());

        // Создаем новое уведомление
        const alert = document.createElement('div');
        alert.className = `alert alert-${type === 'error' ? 'error' : type === 'success' ? 'success' : 'warning'} alert-notification`;
        alert.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 500px;
            animation: slideIn 0.3s ease;
        `;
        alert.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; margin-left: 1rem;">&times;</button>
            </div>
        `;

        document.body.appendChild(alert);

        // Автоматически удаляем через 5 секунд
        setTimeout(() => {
            if (alert.parentElement) {
                alert.style.animation = 'slideOut 0.3s ease forwards';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
    }
}

// Добавляем стили для анимации
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Инициализируем менеджер корзины при загрузке DOM
document.addEventListener('DOMContentLoaded', () => {
    window.cartManager = new CartManager();
});
// autoparts/assets/js/main.js

document.addEventListener('DOMContentLoaded', function() {
    // Инициализация основного функционала
    initMobileMenu();
    initSearchForm();
    initScrollToTop();
    initImageLazyLoading();
    initTooltips();
    initFormValidation();
});

// Мобильное меню
function initMobileMenu() {
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const navigation = document.querySelector('.main-navigation');

    if (mobileToggle && navigation) {
        mobileToggle.addEventListener('click', function() {
            navigation.classList.toggle('mobile-menu-open');

            // Анимация иконки бургера
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-bars');
                icon.classList.toggle('fa-times');
            }
        });

        // Закрытие меню при клике на ссылку
        navigation.addEventListener('click', function(e) {
            if (e.target.tagName === 'A') {
                navigation.classList.remove('mobile-menu-open');
                const icon = mobileToggle.querySelector('i');
                if (icon) {
                    icon.classList.add('fa-bars');
                    icon.classList.remove('fa-times');
                }
            }
        });
    }
}

// Поиск
function initSearchForm() {
    const searchInputs = document.querySelectorAll('input[type="search"]');

    searchInputs.forEach(input => {
        // Автодополнение поиска
        let searchTimeout;
        input.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length >= 3) {
                searchTimeout = setTimeout(() => {
                    performSearch(query, this);
                }, 300);
            } else {
                hideSearchSuggestions();
            }
        });

        // Закрытие подсказок при клике вне области поиска
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-form-header')) {
                hideSearchSuggestions();
            }
        });
    });
}

async function performSearch(query, inputElement) {
    try {
        const response = await fetch(`/autoparts/api/search.php?q=${encodeURIComponent(query)}&limit=5`);
        const data = await response.json();

        if (data.success && data.results.length > 0) {
            showSearchSuggestions(data.results, inputElement);
        } else {
            hideSearchSuggestions();
        }
    } catch (error) {
        console.error('Search error:', error);
        hideSearchSuggestions();
    }
}

function showSearchSuggestions(results, inputElement) {
    // Удаляем существующие подсказки
    hideSearchSuggestions();

    const container = inputElement.closest('.search-form-header');
    if (!container) return;

    const suggestions = document.createElement('div');
    suggestions.className = 'search-suggestions';
    suggestions.style.cssText = `
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-small);
        box-shadow: var(--shadow-medium);
        z-index: 1000;
        max-height: 300px;
        overflow-y: auto;
    `;

    results.forEach(item => {
        const suggestionItem = document.createElement('a');
        suggestionItem.href = `/autoparts/pages/product.php?slug=${item.slug}`;
        suggestionItem.className = 'search-suggestion-item';
        suggestionItem.style.cssText = `
            display: flex;
            align-items: center;
            padding: 12px 16px;
            text-decoration: none;
            color: var(--dark-color);
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s ease;
        `;

        suggestionItem.innerHTML = `
            <img src="${item.image_url_main || '/api/placeholder/40/40'}" 
                 alt="${item.name}" 
                 style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; margin-right: 12px;">
            <div>
                <div style="font-weight: 600; margin-bottom: 4px;">${item.name}</div>
                <div style="font-size: 14px; color: var(--primary-color); font-weight: 600;">${item.price_formatted}</div>
            </div>
        `;

        suggestionItem.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'var(--light-gray)';
        });

        suggestionItem.addEventListener('mouseleave', function() {
            this.style.backgroundColor = 'transparent';
        });

        suggestions.appendChild(suggestionItem);
    });

    container.style.position = 'relative';
    container.appendChild(suggestions);
}

function hideSearchSuggestions() {
    const suggestions = document.querySelector('.search-suggestions');
    if (suggestions) {
        suggestions.remove();
    }
}

// Кнопка "Наверх"
function initScrollToTop() {
    // Создаем кнопку
    const scrollButton = document.createElement('button');
    scrollButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
    scrollButton.className = 'scroll-to-top';
    scrollButton.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 1000;
        box-shadow: var(--shadow-medium);
    `;

    document.body.appendChild(scrollButton);

    // Показ/скрытие кнопки при прокрутке
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollButton.style.opacity = '1';
            scrollButton.style.visibility = 'visible';
        } else {
            scrollButton.style.opacity = '0';
            scrollButton.style.visibility = 'hidden';
        }
    });

    // Плавная прокрутка наверх
    scrollButton.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Hover эффект
    scrollButton.addEventListener('mouseenter', function() {
        this.style.background = 'var(--primary-dark)';
        this.style.transform = 'translateY(-2px)';
    });

    scrollButton.addEventListener('mouseleave', function() {
        this.style.background = 'var(--primary-color)';
        this.style.transform = 'translateY(0)';
    });
}

// Ленивая загрузка изображений
function initImageLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');

    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback для старых браузеров
        images.forEach(img => {
            img.src = img.dataset.src;
        });
    }
}

// Всплывающие подсказки
function initTooltips() {
    const elementsWithTooltips = document.querySelectorAll('[data-tooltip]');

    elementsWithTooltips.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const element = e.target;
    const tooltipText = element.dataset.tooltip;

    if (!tooltipText) return;

    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = tooltipText;
    tooltip.style.cssText = `
        position: absolute;
        background: var(--dark-color);
        color: white;
        padding: 8px 12px;
        border-radius: var(--border-radius-small);
        font-size: 14px;
        z-index: 9999;
        white-space: nowrap;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.2s ease;
    `;

    document.body.appendChild(tooltip);

    // Позиционирование
    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';

    // Анимация появления
    setTimeout(() => {
        tooltip.style.opacity = '1';
    }, 10);

    element._tooltip = tooltip;
}

function hideTooltip(e) {
    const tooltip = e.target._tooltip;
    if (tooltip) {
        tooltip.remove();
        delete e.target._tooltip;
    }
}

// Валидация форм
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');

    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });

        // Валидация полей в реальном времени
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });

            input.addEventListener('input', function() {
                // Убираем ошибку при вводе
                removeFieldError(this);
            });
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');

    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });

    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';

    // Проверка обязательных полей
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'Это поле обязательно для заполнения';
    }

    // Проверка email
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            errorMessage = 'Введите корректный email адрес';
        }
    }

    // Проверка телефона
    if (field.type === 'tel' && value) {
        const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
        if (!phoneRegex.test(value)) {
            isValid = false;
            errorMessage = 'Введите корректный номер телефона';
        }
    }

    // Проверка минимальной длины пароля
    if (field.type === 'password' && value && value.length < 6) {
        isValid = false;
        errorMessage = 'Пароль должен содержать минимум 6 символов';
    }

    // Проверка подтверждения пароля
    if (field.name === 'password_confirm') {
        const passwordField = field.form.querySelector('input[name="password"]');
        if (passwordField && value !== passwordField.value) {
            isValid = false;
            errorMessage = 'Пароли не совпадают';
        }
    }

    if (isValid) {
        removeFieldError(field);
    } else {
        showFieldError(field, errorMessage);
    }

    return isValid;
}

function showFieldError(field, message) {
    removeFieldError(field);

    field.classList.add('error');

    const errorElement = document.createElement('div');
    errorElement.className = 'field-error';
    errorElement.textContent = message;
    errorElement.style.cssText = `
        color: var(--danger-color);
        font-size: 14px;
        margin-top: 4px;
    `;

    field.parentNode.appendChild(errorElement);
}

function removeFieldError(field) {
    field.classList.remove('error');

    const errorElement = field.parentNode.querySelector('.field-error');
    if (errorElement) {
        errorElement.remove();
    }
}

// Утилиты
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Экспорт функций для использования в других скриптах
window.AppUtils = {
    debounce,
    throttle,
    showTooltip,
    hideTooltip,
    validateForm,
    validateField
};
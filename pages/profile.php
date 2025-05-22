<?php
// autoparts/pages/profile.php

$page_title = "Личный кабинет";
require_once __DIR__ . '/../includes/header.php';

$auth = new Auth(get_db_connection());

// Проверяем авторизацию
if (!$auth->isLoggedIn()) {
    redirect(SITE_URL . '/login.php');
}

$user = $auth->getCurrentUser();
$errors = [];
$success_message = '';

// Обработка обновления профиля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $form_data = [
        'first_name' => sanitize_input($_POST['first_name'] ?? ''),
        'last_name' => sanitize_input($_POST['last_name'] ?? ''),
        'phone' => sanitize_input($_POST['phone'] ?? '')
    ];

    // Проверка CSRF токена
    if (!$auth->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Ошибка безопасности. Попробуйте еще раз.';
    }

    // Валидация
    if (empty($form_data['first_name'])) {
        $errors[] = 'Введите имя';
    }

    if (empty($form_data['last_name'])) {
        $errors[] = 'Введите фамилию';
    }

    // Обновление данных
    if (empty($errors)) {
        if ($auth->updateCurrentUser($form_data)) {
            $success_message = 'Профиль успешно обновлен!';
            $user = $auth->getCurrentUser(); // Обновляем данные пользователя
        } else {
            $errors[] = 'Произошла ошибка при обновлении профиля.';
        }
    }
}
?>

<div class="profile-page">
    <div class="container">
        <!-- Заголовок -->
        <div class="page-header" style="margin-bottom: 2rem;">
            <h1><i class="fas fa-user"></i> Личный кабинет</h1>
            <p style="color: var(--medium-gray);">Добро пожаловать, <?php echo htmlspecialchars($auth->getUserName()); ?>!</p>
        </div>

        <div class="profile-content" style="display: grid; grid-template-columns: 280px 1fr; gap: 2rem;">

            <!-- Боковое меню -->
            <aside class="profile-sidebar">
                <div style="background: white; border-radius: var(--border-radius); padding: 1.5rem; box-shadow: var(--shadow-soft);">
                    <h3 style="margin-bottom: 1.5rem; color: var(--dark-color);">Меню</h3>
                    <nav class="profile-nav">
                        <ul style="list-style: none; margin: 0; padding: 0;">
                            <li style="margin-bottom: 0.5rem;">
                                <a href="#profile" class="nav-link active" onclick="showTab('profile')"
                                   style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; text-decoration: none; color: var(--dark-color); border-radius: 8px; transition: 0.2s;">
                                    <i class="fas fa-user"></i> Мои данные
                                </a>
                            </li>
                            <li style="margin-bottom: 0.5rem;">
                                <a href="#orders" class="nav-link" onclick="showTab('orders')"
                                   style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; text-decoration: none; color: var(--dark-color); border-radius: 8px; transition: 0.2s;">
                                    <i class="fas fa-shopping-bag"></i> Мои заказы
                                </a>
                            </li>
                            <li style="margin-bottom: 0.5rem;">
                                <a href="#favorites" class="nav-link" onclick="showTab('favorites')"
                                   style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; text-decoration: none; color: var(--dark-color); border-radius: 8px; transition: 0.2s;">
                                    <i class="fas fa-heart"></i> Избранное
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo SITE_URL; ?>/logout.php"
                                   style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; text-decoration: none; color: var(--danger-color); border-radius: 8px; transition: 0.2s;">
                                    <i class="fas fa-sign-out-alt"></i> Выйти
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </aside>

            <!-- Основной контент -->
            <main class="profile-main">

                <!-- Мои данные -->
                <div id="profile" class="tab-content active" style="background: white; border-radius: var(--border-radius); padding: 2rem; box-shadow: var(--shadow-soft);">
                    <h2 style="margin-bottom: 1.5rem;">Мои данные</h2>

                    <!-- Сообщения -->
                    <?php if ($success_message): ?>
                        <div class="alert alert-success" style="margin-bottom: 1.5rem;">
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                            <ul style="margin: 0; padding-left: 1.2rem;">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" data-validate>
                        <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
                        <input type="hidden" name="update_profile" value="1">

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                            <div class="form-group">
                                <label for="first_name" class="form-label">Имя *</label>
                                <input type="text"
                                       id="first_name"
                                       name="first_name"
                                       class="form-control"
                                       value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>"
                                       required>
                            </div>

                            <div class="form-group">
                                <label for="last_name" class="form-label">Фамилия *</label>
                                <input type="text"
                                       id="last_name"
                                       name="last_name"
                                       class="form-control"
                                       value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>"
                                       required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email"
                                   id="email"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                                   disabled>
                            <small style="font-size: 12px; color: var(--medium-gray);">Email нельзя изменить</small>
                        </div>

                        <div class="form-group">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="tel"
                                   id="phone"
                                   name="phone"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                   placeholder="+7 (999) 123-45-67">
                        </div>

                        <div style="margin-top: 2rem;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Сохранить изменения
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Мои заказы -->
                <div id="orders" class="tab-content" style="display: none; background: white; border-radius: var(--border-radius); padding: 2rem; box-shadow: var(--shadow-soft);">
                    <h2 style="margin-bottom: 1.5rem;">Мои заказы</h2>
                    <div style="text-align: center; padding: 3rem;">
                        <i class="fas fa-shopping-bag" style="font-size: 3rem; color: var(--medium-gray); margin-bottom: 1rem;"></i>
                        <h3>У вас пока нет заказов</h3>
                        <p style="color: var(--medium-gray); margin-bottom: 2rem;">Оформите первый заказ в нашем каталоге</p>
                        <a href="<?php echo SITE_URL; ?>/pages/catalog.php" class="btn btn-primary">
                            <i class="fas fa-shopping-cart"></i> Перейти в каталог
                        </a>
                    </div>
                </div>

                <!-- Избранное -->
                <div id="favorites" class="tab-content" style="display: none; background: white; border-radius: var(--border-radius); padding: 2rem; box-shadow: var(--shadow-soft);">
                    <h2 style="margin-bottom: 1.5rem;">Избранные товары</h2>
                    <div style="text-align: center; padding: 3rem;">
                        <i class="fas fa-heart" style="font-size: 3rem; color: var(--medium-gray); margin-bottom: 1rem;"></i>
                        <h3>У вас нет избранных товаров</h3>
                        <p style="color: var(--medium-gray); margin-bottom: 2rem;">Добавляйте понравившиеся товары в избранное</p>
                        <a href="<?php echo SITE_URL; ?>/pages/catalog.php" class="btn btn-primary">
                            <i class="fas fa-shopping-cart"></i> Перейти в каталог
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<style>
/* Стили для профиля */
.profile-nav .nav-link:hover,
.profile-nav .nav-link.active {
    background: var(--light-gray);
    color: var(--primary-color) !important;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* Адаптивность */
@media (max-width: 768px) {
    .profile-content {
        grid-template-columns: 1fr !important;
    }

    .profile-sidebar {
        order: 2;
    }

    .profile-main {
        order: 1;
    }

    .profile-nav ul {
        display: flex;
        overflow-x: auto;
        gap: 0.5rem;
    }

    .profile-nav li {
        margin-bottom: 0 !important;
        flex-shrink: 0;
    }
}
</style>

<script>
function showTab(tabName) {
    // Скрываем все вкладки
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
        tab.style.display = 'none';
    });

    // Убираем активный класс у всех ссылок
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });

    // Показываем нужную вкладку
    const activeTab = document.getElementById(tabName);
    if (activeTab) {
        activeTab.classList.add('active');
        activeTab.style.display = 'block';
    }

    // Добавляем активный класс к текущей ссылке
    event.target.classList.add('active');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<?php
// autoparts/login.php

$page_title = "Вход в личный кабинет";
require_once __DIR__ . '/includes/header.php';

$auth = new Auth(get_db_connection());

// Если пользователь уже авторизован, перенаправляем
if ($auth->isLoggedIn()) {
    redirect(SITE_URL . '/pages/profile.php');
}

$errors = [];
$success_message = '';

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Проверка CSRF токена
    if (!$auth->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Ошибка безопасности. Попробуйте еще раз.';
    }

    // Валидация
    if (empty($email)) {
        $errors[] = 'Введите email адрес';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email адрес';
    }

    if (empty($password)) {
        $errors[] = 'Введите пароль';
    }

    // Попытка авторизации
    if (empty($errors)) {
        if ($auth->login($email, $password)) {
            // Устанавливаем "запомнить меня" cookie если нужно
            if ($remember) {
                setcookie('remember_user', $auth->getUserId(), time() + (30 * 24 * 60 * 60), '/'); // 30 дней
            }

            // Перенаправляем пользователя
            $redirect_to = $_GET['redirect'] ?? SITE_URL . '/pages/profile.php';
            redirect($redirect_to);
        } else {
            $errors[] = 'Неверный email или пароль';
        }
    }
}

// Проверяем, есть ли сообщение об успешной регистрации
if (isset($_SESSION['registration_success'])) {
    $success_message = $_SESSION['registration_success'];
    unset($_SESSION['registration_success']);
}
?>

<div class="auth-page">
    <div class="container">
        <div class="auth-container" style="max-width: 400px; margin: 3rem auto; background: white; border-radius: var(--border-radius); box-shadow: var(--shadow-medium); overflow: hidden;">

            <!-- Заголовок -->
            <div class="auth-header" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); color: white; padding: 2rem; text-align: center;">
                <i class="fas fa-sign-in-alt" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                <h1 style="margin: 0; font-size: 1.5rem;">Вход в личный кабинет</h1>
                <p style="margin: 0.5rem 0 0; opacity: 0.9; font-size: 14px;">Введите ваши данные для входа</p>
            </div>

            <!-- Форма -->
            <div class="auth-body" style="padding: 2rem;">

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

                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i> Email адрес
                        </label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               placeholder="Введите ваш email"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> Пароль
                        </label>
                        <div style="position: relative;">
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="form-control"
                                   placeholder="Введите ваш пароль"
                                   required>
                            <button type="button"
                                    onclick="togglePassword('password')"
                                    style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--medium-gray); cursor: pointer;">
                                <i class="fas fa-eye" id="password-toggle-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group" style="display: flex; align-items: center; justify-content: space-between;">
                        <label style="display: flex; align-items: center; cursor: pointer; font-size: 14px;">
                            <input type="checkbox" name="remember" style="margin-right: 0.5rem;">
                            Запомнить меня
                        </label>
                        <a href="<?php echo SITE_URL; ?>/forgot-password.php" style="font-size: 14px; color: var(--primary-color);">
                            Забыли пароль?
                        </a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-large" style="width: 100%; margin-bottom: 1rem;">
                        <i class="fas fa-sign-in-alt"></i> Войти
                    </button>

                    <div style="text-align: center; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                        <p style="margin: 0; font-size: 14px; color: var(--medium-gray);">
                            Нет аккаунта?
                            <a href="<?php echo SITE_URL; ?>/register.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>"
                               style="color: var(--primary-color); font-weight: 600;">
                                Зарегистрироваться
                            </a>
                        </p>
                    </div>

                </form>
            </div>
        </div>

        <!-- Дополнительная информация -->
        <div class="auth-benefits" style="max-width: 600px; margin: 2rem auto; text-align: center;">
            <h3 style="margin-bottom: 1.5rem; color: var(--dark-color);">Преимущества регистрации</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1.5rem;">
                <div>
                    <i class="fas fa-shopping-bag" style="font-size: 2rem; color: var(--primary-color); margin-bottom: 0.5rem;"></i>
                    <h4 style="margin-bottom: 0.5rem; font-size: 1rem;">История заказов</h4>
                    <p style="font-size: 14px; color: var(--medium-gray); margin: 0;">Отслеживайте статус ваших заказов</p>
                </div>
                <div>
                    <i class="fas fa-heart" style="font-size: 2rem; color: var(--primary-color); margin-bottom: 0.5rem;"></i>
                    <h4 style="margin-bottom: 0.5rem; font-size: 1rem;">Избранные товары</h4>
                    <p style="font-size: 14px; color: var(--medium-gray); margin: 0;">Сохраняйте понравившиеся товары</p>
                </div>
                <div>
                    <i class="fas fa-truck" style="font-size: 2rem; color: var(--primary-color); margin-bottom: 0.5rem;"></i>
                    <h4 style="margin-bottom: 0.5rem; font-size: 1rem;">Быстрое оформление</h4>
                    <p style="font-size: 14px; color: var(--medium-gray); margin: 0;">Сохраненные адреса доставки</p>
                </div>
                <div>
                    <i class="fas fa-percent" style="font-size: 2rem; color: var(--primary-color); margin-bottom: 0.5rem;"></i>
                    <h4 style="margin-bottom: 0.5rem; font-size: 1rem;">Персональные скидки</h4>
                    <p style="font-size: 14px; color: var(--medium-gray); margin: 0;">Специальные предложения для вас</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.auth-page {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
}

@media (max-width: 768px) {
    .auth-container {
        margin: 1rem auto !important;
        border-radius: 0 !important;
    }

    .auth-page {
        align-items: flex-start;
        padding-top: 2rem;
    }

    .auth-benefits > div {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(inputId + '-toggle-icon');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Автофокус на первое поле
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    if (emailInput && !emailInput.value) {
        emailInput.focus();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
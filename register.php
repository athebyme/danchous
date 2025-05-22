<?php
// autoparts/register.php

$page_title = "Регистрация";
require_once __DIR__ . '/includes/header.php';

$auth = new Auth(get_db_connection());
$user_model = new User(get_db_connection());

// Если пользователь уже авторизован, перенаправляем
if ($auth->isLoggedIn()) {
    redirect(SITE_URL . '/pages/profile.php');
}

$errors = [];
$form_data = [];

// Обработка формы регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'email' => sanitize_input($_POST['email'] ?? ''),
        'first_name' => sanitize_input($_POST['first_name'] ?? ''),
        'last_name' => sanitize_input($_POST['last_name'] ?? ''),
        'phone' => sanitize_input($_POST['phone'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'password_confirm' => $_POST['password_confirm'] ?? ''
    ];

    // Проверка CSRF токена
    if (!$auth->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Ошибка безопасности. Попробуйте еще раз.';
    }

    // Валидация
    if (empty($form_data['email'])) {
        $errors[] = 'Введите email адрес';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email адрес';
    } elseif ($user_model->emailExists($form_data['email'])) {
        $errors[] = 'Пользователь с таким email уже существует';
    }

    if (empty($form_data['first_name'])) {
        $errors[] = 'Введите имя';
    } elseif (strlen($form_data['first_name']) < 2) {
        $errors[] = 'Имя должно содержать минимум 2 символа';
    }

    if (empty($form_data['last_name'])) {
        $errors[] = 'Введите фамилию';
    } elseif (strlen($form_data['last_name']) < 2) {
        $errors[] = 'Фамилия должна содержать минимум 2 символа';
    }

    if (!empty($form_data['phone'])) {
        if (!preg_match('/^[\+]?[0-9\s\-\(\)]{10,}$/', $form_data['phone'])) {
            $errors[] = 'Введите корректный номер телефона';
        }
    }

    if (empty($form_data['password'])) {
        $errors[] = 'Введите пароль';
    } elseif (strlen($form_data['password']) < 6) {
        $errors[] = 'Пароль должен содержать минимум 6 символов';
    }

    if ($form_data['password'] !== $form_data['password_confirm']) {
        $errors[] = 'Пароли не совпадают';
    }

    if (!isset($_POST['agree_terms'])) {
        $errors[] = 'Вы должны согласиться с условиями использования';
    }

    // Попытка регистрации
    if (empty($errors)) {
        if ($auth->register($form_data)) {
            $_SESSION['registration_success'] = 'Регистрация прошла успешно! Добро пожаловать!';

            // Перенаправляем пользователя
            $redirect_to = $_GET['redirect'] ?? SITE_URL . '/pages/profile.php';
            redirect($redirect_to);
        } else {
            $errors[] = 'Произошла ошибка при регистрации. Попробуйте еще раз.';
        }
    }
}
?>

<div class="auth-page">
    <div class="container">
        <div class="auth-container" style="max-width: 500px; margin: 3rem auto; background: white; border-radius: var(--border-radius); box-shadow: var(--shadow-medium); overflow: hidden;">

            <!-- Заголовок -->
            <div class="auth-header" style="background: linear-gradient(135deg, var(--secondary-color), #1e6b8c); color: white; padding: 2rem; text-align: center;">
                <i class="fas fa-user-plus" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                <h1 style="margin: 0; font-size: 1.5rem;">Регистрация</h1>
                <p style="margin: 0.5rem 0 0; opacity: 0.9; font-size: 14px;">Создайте свой личный кабинет</p>
            </div>

            <!-- Форма -->
            <div class="auth-body" style="padding: 2rem;">

                <!-- Ошибки -->
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

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="first_name" class="form-label">
                                <i class="fas fa-user"></i> Имя *
                            </label>
                            <input type="text"
                                   id="first_name"
                                   name="first_name"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($form_data['first_name'] ?? ''); ?>"
                                   placeholder="Ваше имя"
                                   required>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="last_name" class="form-label">
                                <i class="fas fa-user"></i> Фамилия *
                            </label>
                            <input type="text"
                                   id="last_name"
                                   name="last_name"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($form_data['last_name'] ?? ''); ?>"
                                   placeholder="Ваша фамилия"
                                   required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i> Email адрес *
                        </label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control"
                               value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                               placeholder="Введите ваш email"
                               required>
                        <small style="font-size: 12px; color: var(--medium-gray);">На этот адрес будет отправлена информация о заказах</small>
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label">
                            <i class="fas fa-phone"></i> Телефон
                        </label>
                        <input type="tel"
                               id="phone"
                               name="phone"
                               class="form-control"
                               value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>"
                               placeholder="+7 (999) 123-45-67">
                        <small style="font-size: 12px; color: var(--medium-gray);">Для связи по заказам (необязательно)</small>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> Пароль *
                        </label>
                        <div style="position: relative;">
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="form-control"
                                   placeholder="Минимум 6 символов"
                                   required>
                            <button type="button"
                                    onclick="togglePassword('password')"
                                    style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--medium-gray); cursor: pointer;">
                                <i class="fas fa-eye" id="password-toggle-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm" class="form-label">
                            <i class="fas fa-lock"></i> Подтверждение пароля *
                        </label>
                        <div style="position: relative;">
                            <input type="password"
                                   id="password_confirm"
                                   name="password_confirm"
                                   class="form-control"
                                   placeholder="Повторите пароль"
                                   required>
                            <button type="button"
                                    onclick="togglePassword('password_confirm')"
                                    style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--medium-gray); cursor: pointer;">
                                <i class="fas fa-eye" id="password_confirm-toggle-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: flex-start; cursor: pointer; font-size: 14px; line-height: 1.4;">
                            <input type="checkbox" name="agree_terms" required style="margin-right: 0.5rem; margin-top: 0.2rem; flex-shrink: 0;">
                            <span>
                                Я согласен с
                                <a href="<?php echo SITE_URL; ?>/terms.php" target="_blank" style="color: var(--primary-color);">условиями использования</a>
                                и
                                <a href="<?php echo SITE_URL; ?>/privacy.php" target="_blank" style="color: var(--primary-color);">политикой конфиденциальности</a>
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-secondary btn-large" style="width: 100%; margin-bottom: 1rem;">
                        <i class="fas fa-user-plus"></i> Зарегистрироваться
                    </button>

                    <div style="text-align: center; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                        <p style="margin: 0; font-size: 14px; color: var(--medium-gray);">
                            Уже есть аккаунт?
                            <a href="<?php echo SITE_URL; ?>/login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>"
                               style="color: var(--secondary-color); font-weight: 600;">
                                Войти
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>

        <!-- Политика регистрации -->
        <div class="auth-info" style="max-width: 500px; margin: 2rem auto; background: white; border-radius: var(--border-radius); padding: 1.5rem; box-shadow: var(--shadow-soft);">
            <h3 style="margin-bottom: 1rem; color: var(--dark-color); font-size: 1.1rem;">
                <i class="fas fa-shield-alt" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                Защита ваших данных
            </h3>
            <ul style="font-size: 14px; color: var(--medium-gray); line-height: 1.6; margin: 0; padding-left: 1.2rem;">
                <li>Мы не передаем ваши данные третьим лицам</li>
                <li>Вся информация надежно защищена</li>
                <li>Вы можете удалить аккаунт в любое время</li>
                <li>Минимум рекламной рассылки</li>
            </ul>
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
    border-color: var(--secondary-color);
    box-shadow: 0 0 0 3px rgba(46, 134, 171, 0.1);
}

.form-control.error {
    border-color: var(--danger-color);
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

    .auth-body > form > div:first-of-type {
        grid-template-columns: 1fr !important;
        gap: 0 !important;
    }

    .auth-body > form > div:first-of-type .form-group {
        margin-bottom: 1.5rem !important;
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

// Проверка совпадения паролей в реальном времени
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const passwordConfirm = document.getElementById('password_confirm');

    function checkPasswordMatch() {
        if (passwordConfirm.value && password.value !== passwordConfirm.value) {
            passwordConfirm.classList.add('error');
            if (!passwordConfirm.parentNode.querySelector('.field-error')) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'field-error';
                errorDiv.textContent = 'Пароли не совпадают';
                errorDiv.style.cssText = 'color: var(--danger-color); font-size: 14px; margin-top: 4px;';
                passwordConfirm.parentNode.appendChild(errorDiv);
            }
        } else {
            passwordConfirm.classList.remove('error');
            const errorDiv = passwordConfirm.parentNode.querySelector('.field-error');
            if (errorDiv) {
                errorDiv.remove();
            }
        }
    }

    passwordConfirm.addEventListener('input', checkPasswordMatch);
    password.addEventListener('input', checkPasswordMatch);

    // Автофокус на первое поле
    const firstNameInput = document.getElementById('first_name');
    if (firstNameInput && !firstNameInput.value) {
        firstNameInput.focus();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php
// autoparts/core/Auth.php

class Auth {
    private $pdo;
    private $user_model;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->user_model = new User($pdo);
    }

    /**
     * Авторизация пользователя
     */
    public function login($email, $password) {
        $user = $this->user_model->findByEmail($email);

        if ($user && $this->user_model->verifyPassword($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            return true;
        }

        return false;
    }

    /**
     * Регистрация пользователя
     */
    public function register($data) {
        // Проверяем, не существует ли уже такой email
        if ($this->user_model->emailExists($data['email'])) {
            return false;
        }

        // Создаем пользователя
        if ($this->user_model->create($data)) {
            // Автоматически авторизуем после регистрации
            return $this->login($data['email'], $data['password']);
        }

        return false;
    }

    /**
     * Выход из системы
     */
    public function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_role']);
        unset($_SESSION['user_name']);

        // Можно также полностью уничтожить сессию
        // session_destroy();
    }

    /**
     * Проверка авторизации
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Получение ID текущего пользователя
     */
    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Получение email текущего пользователя
     */
    public function getUserEmail() {
        return $_SESSION['user_email'] ?? null;
    }

    /**
     * Получение роли текущего пользователя
     */
    public function getUserRole() {
        return $_SESSION['user_role'] ?? 'user';
    }

    /**
     * Получение имени текущего пользователя
     */
    public function getUserName() {
        return $_SESSION['user_name'] ?? 'Пользователь';
    }

    /**
     * Проверка роли администратора
     */
    public function isAdmin() {
        return $this->getUserRole() === 'admin';
    }

    /**
     * Получение данных текущего пользователя
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return $this->user_model->findById($this->getUserId());
    }

    /**
     * Обновление данных текущего пользователя
     */
    public function updateCurrentUser($data) {
        if (!$this->isLoggedIn()) {
            return false;
        }

        if ($this->user_model->update($this->getUserId(), $data)) {
            // Обновляем данные в сессии
            $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];
            return true;
        }

        return false;
    }

    /**
     * Принудительная авторизация (для middleware)
     */
    public function requireAuth($redirect_url = null) {
        if (!$this->isLoggedIn()) {
            $redirect_url = $redirect_url ?: SITE_URL . '/login.php';
            redirect($redirect_url);
        }
    }

    /**
     * Принудительная проверка роли администратора
     */
    public function requireAdmin($redirect_url = null) {
        $this->requireAuth();

        if (!$this->isAdmin()) {
            $redirect_url = $redirect_url ?: SITE_URL . '/';
            redirect($redirect_url);
        }
    }

    /**
     * Генерация CSRF токена
     */
    public function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Проверка CSRF токена
     */
    public function verifyCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
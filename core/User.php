<?php
// autoparts/core/User.php

class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Создание нового пользователя
     */
    public function create($data) {
        $sql = "INSERT INTO users (email, password, first_name, last_name, phone) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);

        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

        return $stmt->execute([
            $data['email'],
            $hashed_password,
            $data['first_name'],
            $data['last_name'],
            $data['phone'] ?? null
        ]);
    }

    /**
     * Поиск пользователя по email
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ? AND is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Поиск пользователя по ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = ? AND is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Проверка пароля
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Обновление данных пользователя
     */
    public function update($id, $data) {
        $sql = "UPDATE users SET first_name = ?, last_name = ?, phone = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['phone'] ?? null,
            $id
        ]);
    }

    /**
     * Проверка существования email
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Получение всех пользователей (для админки)
     */
    public function getAll($limit = 50, $offset = 0) {
        $sql = "SELECT id, email, first_name, last_name, phone, role, is_active, created_at
                FROM users
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Подсчет общего количества пользователей
     */
    public function getTotalCount() {
        $sql = "SELECT COUNT(*) FROM users";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchColumn();
    }

    /**
     * Деактивация пользователя
     */
    public function deactivate($id) {
        $sql = "UPDATE users SET is_active = 0, updated_at = NOW() WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Активация пользователя
     */
    public function activate($id) {
        $sql = "UPDATE users SET is_active = 1, updated_at = NOW() WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
}
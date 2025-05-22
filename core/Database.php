<?php
// autoparts/core/Database.php

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $charset;
    private $conn;

    public function __construct() {
        // Используем константы, определенные в config/database.php или config/constants.php
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->charset = DB_CHARSET;
    }

    public function connect() {
        $this->conn = null; // Сбрасываем предыдущее соединение

        $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch(PDOException $e) {
            error_log('Database Connection Error (Class): ' . $e->getMessage());
            // В реальном приложении здесь лучше выбросить исключение или вернуть false/null,
            // чтобы вызывающий код мог обработать ошибку.
            die('Ошибка подключения к базе данных через класс. Пожалуйста, попробуйте позже.');
        }
        return $this->conn;
    }

    // Можно добавить метод для получения уже установленного соединения, если оно есть
    public function getConnection() {
        if ($this->conn) {
            return $this->conn;
        }
        return $this->connect(); // Если соединения нет, устанавливаем новое
    }
}
?>
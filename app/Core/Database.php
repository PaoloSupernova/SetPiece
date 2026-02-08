<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Singleton PDO Database Wrapper
 * 
 * Provides a single database connection instance throughout the application
 * with secure defaults and helper methods for common operations.
 */
class Database
{
    private static ?Database $instance = null;
    private \PDO $connection;

    /**
     * Private constructor to enforce singleton pattern
     */
    private function __construct()
    {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $dbname = $_ENV['DB_DATABASE'] ?? 'steward';
        $username = $_ENV['DB_USERNAME'] ?? 'steward';
        $password = $_ENV['DB_PASSWORD'] ?? 'steward';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->connection = new \PDO($dsn, $username, $password, $options);
    }

    /**
     * Prevent cloning of the instance
     */
    private function __clone(): void
    {
    }

    /**
     * Prevent unserializing of the instance
     */
    public function __wakeup(): void
    {
        throw new \Exception("Cannot unserialize singleton");
    }

    /**
     * Get the singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get the PDO connection
     */
    public function connection(): \PDO
    {
        return $this->connection;
    }

    /**
     * Execute a query and return the statement
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch all rows from a query
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Fetch a single row from a query
     */
    public function fetch(string $sql, array $params = []): array|false
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Get the last inserted ID
     */
    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }
}

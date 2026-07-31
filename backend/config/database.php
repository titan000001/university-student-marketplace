<?php
/**
 * Database Connection Wrapper
 * UniMarket - University Student Marketplace
 */

require_once __DIR__ . '/config.php';

class Database
{
    /**
     * Singleton PDO connection instance.
     *
     * @var PDO|null
     */
    private ?PDO $connection = null;

    /**
     * Establish and return the PDO database connection.
     *
     * @return PDO
     */
    public function connect(): PDO
    {
        if ($this->connection === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                DB_HOST,
                DB_NAME
            );

            $this->connection = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }

        return $this->connection;
    }
}

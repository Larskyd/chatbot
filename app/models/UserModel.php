<?php
require_once __DIR__ . '/../lib/Database.php';

class UserModel
{
    protected \PDO $pdo;

    /**
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->pdo = $db->pdo();
    }

    /**
     * Finn bruker etter epost.
     *
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, email, password FROM users WHERE email = :e LIMIT 1');
        $stmt->execute([':e' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Opprett ny bruker.
     *
     * @param string $email
     * @param string $hashedPassword
     * @return bool
     */
    public function createUser(string $email, string $hashedPassword): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (email, password, created_at) VALUES (:e, :p, NOW())');
        try {
            return (bool)$stmt->execute([':e' => $email, ':p' => $hashedPassword]);
        } catch (\PDOException $ex) {
            error_log('DB insert user error: ' . $ex->getMessage());
            return false;
        }
    }
}
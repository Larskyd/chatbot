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
     * Finn bruker på epost.
     *
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, email, password, failed_attempts, locked_until FROM users WHERE email = :e LIMIT 1');
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

    /**
     * Inkrementer failed_attempts og returner ny verdi.
     * 
     * @param int $userId
     * @return int
     */
    public function incrementFailedAttempts(int $userId): int
    {
        $sql = "UPDATE users SET failed_attempts = failed_attempts + 1 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $userId]);

        $stmt = $this->pdo->prepare("SELECT failed_attempts FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch();
        return isset($row['failed_attempts']) ? (int)$row['failed_attempts'] : 0;
    }

    /**
     * Reset failed attempts og fjern lock.
     *
     * @param int $userId
     * @return bool
     */
    public function resetFailedAttempts(int $userId): bool
    {
        $sql = "UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return (bool)$stmt->execute([':id' => $userId]);
    }

    /**
     * Sett locked_until (DATETIME string).
     * 
     * @param int $userId
     * @param string $lockedUntil
     * @return bool
     */
    public function lockUserUntil(int $userId, string $lockedUntil): bool
    {
        $sql = "UPDATE users SET locked_until = :locked_until WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return (bool)$stmt->execute([':locked_until' => $lockedUntil, ':id' => $userId]);
    }

    /**
     * Hent locked_until og failed_attempts
     * 
     * @param int $userId
     * @return array
     */
    public function getLockInfo(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT failed_attempts, locked_until FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: ['failed_attempts' => 0, 'locked_until' => null];
    }
}

<?php
/**
 * QueryLogModel
 * Logger chatbot-spørringer til query_log tabellen.
 *
 * Funksjonalitet:
 * - insertLog($userId, $queryText, $responseText, $metadata): logg en spørring
 * - getByUserId($userId, $limit): hent siste N spørringer for en bruker
 * - getRecent($limit): hent siste N spørringer totalt
 */
class QueryLogModel
{
    /** @var \PDO */
    protected $pdo;

    /**
     * $db kan være en PDO-instans eller et Database-objekt med pdo()-metode.
     *
     * @param mixed $db
     */
    public function __construct($db)
    {
        if ($db instanceof \PDO) {
            $this->pdo = $db;
        } elseif (is_object($db) && method_exists($db, 'pdo')) {
            $this->pdo = $db->pdo();
        } else {
            throw new \InvalidArgumentException('Forventet PDO eller Database-objekt');
        }
    }

    /**
     * Logg en spørring.
     *
     * @param int|null $userId
     * @param string $queryText
     * @param string|null $responseText
     * @param array|null $metadata
     * @return int Inserted id
     */
    public function insertLog(?int $userId, string $queryText, ?string $responseText = null, ?string $responseType = null, ?string $responseSummary = null, ?array $metadata = null): int
    {
        $sql = "INSERT INTO query_log (user_id, query_text, response_text, response_type, response_summary, metadata, created_at)
                VALUES (:user_id, :query_text, :response_text, :response_type, :response_summary, :metadata, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $jsonMeta = $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null;
        $stmt->execute([
            ':user_id' => $userId,
            ':query_text' => $queryText,
            ':response_text' => $responseText,
            ':response_type' => $responseType,
            ':response_summary' => $responseSummary,
            ':metadata' => $jsonMeta
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Hent siste N logs for en gitt bruker. Returnerer tom array hvis userId er null.
     *
     * @param int|null $userId
     * @param int $limit
     * @return array
     */
    public function getByUserId(?int $userId, int $limit = 100): array
    {
        if ($userId === null) return [];
        try {
            $sql = "SELECT id, query_text, response_type, response_summary, created_at
                    FROM query_log
                    WHERE user_id = :user_id
                    ORDER BY created_at DESC
                    LIMIT :limit";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
            $stmt->bindValue(':limit', max(1, (int)$limit), \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            error_log('QueryLog getByUserId error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Hent siste N logs.
     *
     * @param int $limit
     * @return array
     */
    public function getRecent(int $limit = 5): array
    {
        $limit = (int)$limit;
        $sql = "SELECT id, user_id, query_text, response_type, response_summary, created_at FROM query_log ORDER BY created_at DESC LIMIT {$limit}";
        $stmt = $this->pdo->query($sql);
        return $stmt ? $stmt->fetchAll() : [];
    }

    /**
     * Hent full detalj (response_text + metadata) for en loggpost
     * 
     * @param int $id
     * @return array|null
     */
    public function getDetail(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT id, user_id, query_text, response_text, response_type, response_summary, metadata, created_at FROM query_log WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}

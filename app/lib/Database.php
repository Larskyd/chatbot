<?php
/**
 * PDO-wrapper for prosjektet.
 */
class Database
{
    /** @var \PDO */
    protected $pdo;

    /**
     * @param array $config Konfiguren fra app/config.php
     *                      format: ['db' => ['host','dbname','user','pass','charset']]
     * @throws \PDOException
     */
    public function __construct(array $config)
    {
        $db = $config['db'];
        $dsn = "mysql:host={$db['host']};dbname={$db['dbname']};charset={$db['charset']}";
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $this->pdo = new \PDO($dsn, $db['user'], $db['pass'], $options);
    }

    /**
     * Returnerer PDO-instansen
     * @return \PDO
     */
    public function pdo(): \PDO
    {
        return $this->pdo;
    }
}

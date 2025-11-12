<?php
require_once __DIR__ . '/../models/QueryLogModel.php';

class HistoryController
{
    protected $logModel;

    public function __construct($db)
    {
        $this->logModel = new QueryLogModel($db);
    }

    /**
     * Sørg for at session er startet.
     */
    private function ensureSessionStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Hent ID for nåværende bruker fra session.
     */
    private function getCurrentUserId(): ?int
    {
        $this->ensureSessionStarted();
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    /**
     * Hent epost for nåværende bruker fra session.
     */
    private function getCurrentUserEmail(): ?string
    {
        $this->ensureSessionStarted();
        return $_SESSION['user_email'] ?? null;
    }

    /**
     * Håndter request, sett opp variabler for viewet
     */
    public function handleRequest(): void
    {
        $this->ensureSessionStarted();

        $userId = $this->getCurrentUserId();
        $userEmail = $this->getCurrentUserEmail();

        // Hvis ikke innlogget, redirect til login
        if ($userEmail === null) {
            $_SESSION['flash_error'] = 'Du må være logget inn for å se denne siden.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=login');
            exit;
        }

        // Hvis epost inneholder "@admin" → vis alle logs, ellers kun brukerens logs
        if (stripos($userEmail, '@admin') !== false) {
            $history = $this->logModel->getRecent(200);
        } else {
            $history = $this->logModel->getByUserId((int)$userId, 200);
        }

        // Inkluder view (history.php forventer $history)
        include __DIR__ . '/../views/history.php';
    }
}

<?php
require_once __DIR__ . '/../models/RecipeModel.php';
require_once __DIR__ . '/../models/QueryLogModel.php';
require_once __DIR__ . '/../services/ChatbotService.php';

/**
 * ChatbotController
 * Håndterer chatbot-forespørsler og interaksjoner.
 */
class ChatbotController
{
    protected RecipeModel $recipeModel;
    protected QueryLogModel $logModel;

    public function __construct($db)
    {
        $this->recipeModel = new RecipeModel();
        $this->logModel = new QueryLogModel($db);
    }

    private function ensureSessionStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function getCurrentUserId(): ?int
    {
        $this->ensureSessionStarted();
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public function handleRequest(): void
    {
        $this->ensureSessionStarted();

        $userId = $this->getCurrentUserId();
        if ($userId === null) {
            $_SESSION['flash_error'] = 'Du må være logget inn for å se denne siden.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['q'])) {
            $this->handleChat();
            return;
        }

        include __DIR__ . '/../views/chatbot.php';
    }

    /**
     * Behandle chat-spørring (tynn controller — logikk i ChatbotService)
     * 
     * @return void
     */
    public function handleChat()
    {
        $this->ensureSessionStarted();
        $userId = $this->getCurrentUserId();

        $query = trim($_POST['q'] ?? '');
        $responseType = 'text';
        $responseData = null;
        $responseMessage = '';

        if ($query !== '') {
            $service = new ChatbotService($this->recipeModel);
            $res = $service->routeQuery($query, $_SESSION['last_recipes'] ?? null);

            // Normaliser resultat
            $responseType = $res['type'] ?? 'text';
            $responseData = $res['data'] ?? null;
            $responseMessage = $res['message'] ?? '';

            // Lagre minimal liste i session ved cards
            if ($responseType === 'cards' && !empty($responseData['items']) && is_array($responseData['items'])) {
                $_SESSION['last_recipes'] = array_map(function ($it) {
                    return [
                        'id' => $it['id'] ?? null,
                        'name' => $it['name'] ?? null,
                        'thumbnail' => $it['thumbnail'] ?? null
                    ];
                }, $responseData['items']);

                $_SESSION['last_recipes_area'] = $responseData['area'] ?? null;
                if (isset($responseData['area'])) {
                    $_SESSION['last_list_type'] = 'area';
                } elseif (isset($responseData['category'])) {
                    $_SESSION['last_list_type'] = 'recipes';
                } else {
                    $_SESSION['last_list_type'] = 'categories';
                }
            }

            // Bygg kort summary for log
            $responseSummary = '';
            if ($responseType === 'cards' && !empty($responseData['items'])) {
                $names = array_map(fn($it) => $it['name'] ?? '', array_slice($responseData['items'], 0, 5));
                $responseSummary = implode(', ', array_filter($names));
            } elseif ($responseType === 'detail' && !empty($responseData['name'])) {
                $responseSummary = $responseData['name'];
            } else {
                $responseSummary = mb_substr(strip_tags((string)$responseMessage), 0, 240);
            }

            $metadata = ['area' => $_SESSION['last_recipes_area'] ?? '', 'type' => $responseType];

            if ($userId !== null && method_exists($this->logModel, 'insertLog')) {
                $this->logModel->insertLog($userId, $query, (string)$responseMessage, $responseType, $responseSummary, $metadata);
            }
        }

        // expose to view
        include __DIR__ . '/../views/chatbot.php';
    }
}

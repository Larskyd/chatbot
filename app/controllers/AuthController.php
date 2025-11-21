<?php
require_once __DIR__ . '/../models/UserModel.php';

/**
 * Controller for autentisering 
 * 
 * Ansvar: validering av input, opprette/sjekke brukere via UserModel,
 * og sette/rydde session.
 */
class AuthController
{
    protected UserModel $userModel;
    protected int $maxAttempts = 3;
    protected string $lockInterval = '+1 hour';

    /**
     * Constructor.
     * @param UserModel $userModel
     */
    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * Sørg for at session er startet.
     */
    private function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Forsøk å registrere bruker.
     *
     * @return array ['success'=>bool, 'errors'=>[]]
     */
    public function register(string $email, string $password): array
    {
        $this->ensureSession();
        $errors = [];

        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Ugyldig e-postadresse.';
        }
        if (mb_strlen($password) < 8) {
            $errors[] = 'Passord må være minst 8 tegn.';
        }

        if ($this->userModel->findByEmail($email)) {
            $errors[] = 'E-post er allerede registrert.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $ok = $this->userModel->createUser($email, $hash);

        if (!$ok) {
            $errors[] = 'Kunne ikke lagre bruker. Prøv igjen senere.';
        }

        // Setter session med epost og id
        $_SESSION['user_email'] = $email;
        $_SESSION['user_id'] = $this->userModel->findByEmail($email)['id'];

        return ['success' => $ok, 'errors' => $errors];
    }

    /**
     * Forsøk å logge inn bruker.
     * 
     * @param string $email
     * @param string $password
     * 
     * @return array ['success'=>bool, 'errors'=>[]]
     */
    public function login(string $email, string $password): array
    {
        $this->ensureSession();
        $errors = [];

        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Ugyldig e-post.';
            return ['success' => false, 'errors' => $errors];
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            $errors[] = 'Feil e-post eller passord.';
            return ['success' => false, 'errors' => $errors];
        }

        // -- sjekk om konto er låst ---
        if (!empty($user['locked_until'])) {
            $lockedTs = strtotime($user['locked_until']);
            if ($lockedTs !== false && $lockedTs > time()) {
                $minutes = (int)ceil(($lockedTs - time()) / 60);
                $errors[] = "Konto låst. Prøv igjen om ca. {$minutes} minutter.";
                return ['success' => false, 'errors' => $errors];
            } else {
                // låsetiden er passert eller ugyldig → resett
                $this->userModel->resetFailedAttempts((int)$user['id']);
                $user['failed_attempts'] = 0;
                $user['locked_until'] = null;
            }
        }

        // verifiser passord
        if (!password_verify($password, $user['password'])) {
            $attempts = $this->userModel->incrementFailedAttempts((int)$user['id']);
            if ($attempts >= $this->maxAttempts) {
                $lockedUntil = date('Y-m-d H:i:s', time() + 3600); // +1 time
                error_log('DEBUG: locking user ' . $user['id'] . ' until ' . $lockedUntil);
                $this->userModel->lockUserUntil((int)$user['id'], $lockedUntil);
                $errors[] = 'For mange mislykkede forsøk — kontoen er låst i 1 time.';
            } else {
                $left = $this->maxAttempts - $attempts;
                $errors[] = "Feil e-post eller passord. {$left} forsøk igjen før konto låses.";
            }
            return ['success' => false, 'errors' => $errors];
        }

        // suksess: reset attempts og sett session
        $this->userModel->resetFailedAttempts((int)$user['id']);
        $_SESSION['user_email'] = $user['email'] ?? $email;
        $_SESSION['user_id'] = $user['id'] ?? null;

        return ['success' => true, 'errors' => []];
    }

    /**
     * Logg ut bruker.
     */
    public function logout(): void
    {
        $this->ensureSession();
        unset($_SESSION['user_email'], $_SESSION['user_id']);
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'] ?? false,
                $params['httponly'] ?? false
            );
        }
        session_destroy();
    }
}

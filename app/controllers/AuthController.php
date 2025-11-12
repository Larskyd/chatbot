<?php
require_once __DIR__ . '/../models/UserModel.php';

class AuthController
{
    protected UserModel $userModel;

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
     * @return array ['success'=>bool, 'errors'=>[]]
     */
    public function login(string $email, string $password): array
    {
        $this->ensureSession();
        $errors = [];

        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Ugyldig e-postadresse.';
            return ['success' => false, 'errors' => $errors];
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Feil e-post eller passord.';
            return ['success' => false, 'errors' => $errors];
        }

        // Sett session med epost og id
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
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'] ?? false, $params['httponly'] ?? false
            );
        }
        session_destroy();
    }
}

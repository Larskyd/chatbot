<?php
require_once __DIR__ . '/../models/UserModel.php';

class AuthController
{
    protected UserModel $userModel;

    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * Forsøk å registrere bruker.
     *
     * @return array ['success'=>bool, 'errors'=>[]]
     */
    public function register(string $email, string $password): array
    {
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

        return ['success' => $ok, 'errors' => $errors];
    }
    /**
     * Forsøk å logge inn bruker.
     * 
     * @return array ['success'=>bool, 'errors'=>[]]
     */
    public function login(string $email, string $password): array
    {
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

        // Sett session eller lignende for å logge inn brukeren
        $_SESSION['user_id'] = $user['id'];

        return ['success' => true, 'errors' => []];
    }
}
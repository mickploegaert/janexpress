<?php

require_once __DIR__ . '/../../datalayer/entities/user.php';

class CustomerSession
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    public static function login(User $user): void
    {
        self::start();
        session_regenerate_id(true);

        $_SESSION['customer_id'] = $user->getId();
        $_SESSION['customer_first_name'] = $user->getFirstName();
    }

    public static function logout(): void
    {
        self::start();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }

    public static function isLoggedIn(): bool
    {
        self::start();

        return is_string($_SESSION['customer_id'] ?? null) && $_SESSION['customer_id'] !== '';
    }

    public static function id(): ?string
    {
        self::start();
        $id = $_SESSION['customer_id'] ?? null;

        return is_string($id) && $id !== '' ? $id : null;
    }

    public static function firstName(): ?string
    {
        self::start();
        $name = $_SESSION['customer_first_name'] ?? null;

        return is_string($name) && $name !== '' ? $name : null;
    }

    public static function token(): string
    {
        self::start();

        if (!is_string($_SESSION['csrf_token'] ?? null) || $_SESSION['csrf_token'] === '') {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function matchesToken(?string $token): bool
    {
        self::start();
        $known = $_SESSION['csrf_token'] ?? '';

        return is_string($token) && is_string($known) && $known !== '' && hash_equals($known, $token);
    }

    public static function flash(string $message): void
    {
        self::start();
        $_SESSION['flash'] = $message;
    }

    public static function pullFlash(): ?string
    {
        self::start();
        $message = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        return is_string($message) && $message !== '' ? $message : null;
    }
}

<?php

namespace App\Core;

class Auth
{
    public static function user(): ?array
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }

        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $_SESSION['user_id']]);
        return $stmt->fetch() ?: null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function attempt(string $email, string $password): bool
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (! $user || ! password_verify($password, $user['password'])) {
            return false;
        }

        $_SESSION['user_id'] = (int) $user['id'];
        do_action('onUserLogin', [
            'user_id' => (int) $user['id'],
            'user' => $user,
        ]);
        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id']);
    }

    public static function requireRole(array|string $roles): void
    {
        $roles = (array) $roles;
        $user = self::user();

        if (! $user) {
            flash('error', 'Please login first.');
            redirect('/login');
        }

        if (! in_array($user['role'], $roles, true)) {
            flash('error', 'You do not have access to that area.');
            redirect('/');
        }

    }
}

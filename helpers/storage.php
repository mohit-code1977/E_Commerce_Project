<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Consent {
    public static function mode(): string {
        if (!empty($_SESSION['id'])) {
            return 'db'; // logged-in user → DB is source of truth
        }
        return $_COOKIE['userChoice'] ?? 'none'; // cookies | session | none
    }

    public static function hasChosen(): bool {
        return isset($_COOKIE['userChoice']);
    }
}

class Storage {
    public static function set(string $key, $value): void {
        $mode = Consent::mode();

        if ($mode === 'cookies') {
            setcookie($key, json_encode($value), time() + 86400 * 30, "/", "", false, true);
            $_COOKIE[$key] = json_encode($value); // immediate availability
        } 
        elseif ($mode === 'session') {
            $_SESSION[$key] = $value;
        }
        // mode === 'none' or 'db' → do nothing
    }

    public static function get(string $key) {
        $mode = Consent::mode();

        if ($mode === 'cookies' && isset($_COOKIE[$key])) {
            return json_decode($_COOKIE[$key], true);
        } 
        elseif ($mode === 'session') {
            return $_SESSION[$key] ?? null;
        }
        return null;
    }

    public static function clear(string $key): void {
        setcookie($key, "", time() - 3600, "/");
        unset($_SESSION[$key]);
    }
}

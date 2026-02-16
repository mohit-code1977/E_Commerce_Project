<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Consent {
    public static function mode(): string {
        return $_COOKIE['userChoice'] ?? 'none';
    }

    public static function hasChosen(): bool {
        return self::mode() !== 'none';
    }
}

class Storage {
    public static function set(string $key, $value): void {
        $mode = Consent::mode();

        if ($mode === 'cookies') {
            setcookie($key, json_encode($value), time() + 86400, "/", "", false, true);
            $_COOKIE[$key] = json_encode($value);
        } 
        elseif ($mode === 'session') {
            $_SESSION[$key] = $value;
        }
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

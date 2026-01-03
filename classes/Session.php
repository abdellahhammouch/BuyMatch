<?php

class Session
{
    private static function ensureStarted()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set($key, $value)
    {
        self::ensureStarted();
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null)
    {
        self::ensureStarted();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    public static function has($key)
    {
        self::ensureStarted();
        return isset($_SESSION[$key]);
    }

    public static function remove($key)
    {
        self::ensureStarted();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function destroy()
    {
        self::ensureStarted();
        session_unset();
        session_destroy();
    }

    public static function setFlash($key, $message)
    {
        self::ensureStarted();
        $_SESSION["_flash"][$key] = $message;
    }

    public static function getFlash($key, $default = null)
    {
        self::ensureStarted();
        if (!isset($_SESSION["_flash"][$key])) return $default;

        $msg = $_SESSION["_flash"][$key];
        unset($_SESSION["_flash"][$key]);
        return $msg;
    }
}

<?php
namespace Amirsarhang;

//session_start();
use Facebook\PersistentData\PersistentDataInterface;

class SessionPersistentDataHandler implements PersistentDataInterface
{
    public function get($key)
    {
        return $_SESSION[$key] ?? null;
    }

    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }
}

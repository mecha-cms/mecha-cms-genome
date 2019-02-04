<?php

class Cookie extends Genome {

    const config = [
        'expire' => '1 day',
        'path' => '/',
        'domain' => "",
        'secure' => false,
        'http_only' => false
    ];

    public static $config = self::config;

    public static function set(string $key, $value = "", $expire = null) {
        if (is_scalar($expire)) {
            $expire = ['expire' => $expire];
        }
        $c = extend(self::$config, $expire);
        if (is_string($c['expire'])) {
            $c['expire'] = strtotime($c['expire'], $t = time()) - $t;
        }
        setcookie(self::key($key), self::x($value), ...array_values($c));
        return new static;
    }

    public static function get(string $key = null, $fail = null) {
        if (!isset($key)) {
            $o = [];
            foreach ($_COOKIE as $k => $v) {
                $o[$k] = e(strpos($k, '_') === 0 ? self::v($v) : $v);
            }
            return $o;
        }
        $key = self::key($key);
        $value = self::v($_COOKIE[$key] ?? 'bnVsbA==');
        return $value !== null ? $value : $fail;
    }

    public static function reset($key = null) {
        if (!isset($key)) {
            foreach ($_COOKIE as $k => $v) {
                setcookie($k, null, -1);
                setcookie($k, null, -1, '/');
            }
        } else if (is_array($key)) {
            foreach ($key as $v) {
                self::reset($v);
            }
        } else {
            $key = self::key($key);
            setcookie($key, null, -1);
            setcookie($key, null, -1, '/');
        }
        return new static;
    }

    private static function x($value) {
        return base64_encode(json_encode($value));
    }

    private static function v($value) {
        return json_decode(base64_decode($value), true);
    }

    private static function key($key) {
        return '_' . dechex(crc32(static::class . ':' . $key));
    }

}
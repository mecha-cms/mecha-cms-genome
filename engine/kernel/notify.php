<?php

class Notify extends Genome {

    public static $id = 'message';
    public static $x = 0;

    public static $config = [
        'message' => '<p class="message message-%1$s cl cf">%2$s</p>',
        'messages' => '<div class="messages p cl cf">%1$s</div>'
    ];

    public static function set(...$lot) {
        $count = count($lot);
        $kin = array_shift($lot);
        $text = array_shift($lot);
        $s = array_shift($lot) ?? "";
        $c2f = __c2f__(static::class);
        $text = Language::get($c2f . '_' . $kin . '_' . $text, (array) $s);
        if ($count === 1) {
            self::set('default', $kin);
        } else {
            Session::set(self::$id, Session::get(self::$id, "") . sprintf(self::$config['message'], $kin, $text));
        }
        return new static;
    }

    public static function reset($error_x = true) {
        Session::reset(self::$id);
        if ($error_x) self::$x = 0;
    }

    public static function errors($fail = false) {
        return self::$x > 0 ? self::$x : $fail;
    }

    public static function get($session_x = true) {
        $output = Session::get(self::$id, "") !== "" ? HTML::$begin . sprintf(self::$config['messages'], Session::get(self::$id)) . HTML::$end : "";
        if ($session_x) self::reset();
        return $output;
    }

    public static function send($from, $to, $subject, $message) {
        if (Is::void($to) || Is::email($to)) return false;
        $meta  = 'MIME-Version: 1.0' . N;
        $meta .= 'Content-Type: text/html; charset=ISO-8859-1' . N;
        $meta .= 'From: ' . $from . N;
        $meta .= 'Reply-To: ' . $from . N;
        $meta .= 'Return-Path: ' . $from . N;
        $meta .= 'X-Mailer: PHP/' . phpversion();
        $s = __c2f__(static::class) . ':' . __METHOD__;
        $meta = Hook::NS($s . '.meta', $meta);
        $data = Hook::NS($s . '.data', $data);
        return mail($to, $subject, $data, $meta);
    }

    public static function __callStatic($kin, $lot) {
        if (!self::kin($kin)) {
            array_unshift($lot, $kin);
            return call_user_func_array('self::set', $lot);
        }
        return parent::__callStatic($kin, $lot);
    }

}
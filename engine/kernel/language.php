<?php

class Language extends Config {

    public static function set($a, $b = null) {
        if (!_is_anemon_($a)) {
            parent::set('__i18n.' . $a, $b);
        } else {
            foreach ($a as $k => $v) {
                $aa['__i18n.' . $k] = $v;
            }
            parent::set($aa, $b);
        }
    }

    public static function get($k = null, $a = []) {
        if ($k === null) return parent::get('__i18n', []);
        return vsprintf(parent::get('__i18n.' . $k, $k), $a + [""]);
    }

}
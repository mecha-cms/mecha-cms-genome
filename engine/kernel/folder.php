<?php

class Folder extends File {

    public static function create($input, $consent = 0777) {
        foreach((array) $input as $k => $v) {
            if(!file_exists($v)) {
                if (is_array($consent)) {
                    $c = $consent[$k] ?? end($consent);
                } else {
                    $c = $consent;
                }
                mkdir(To::path($v), $c, true);
            }
        }
    }

    public static function exist($input, $fail = false) {
        $input = To::path($input);
        return is_dir($input) ? $input : $fail;
    }

}
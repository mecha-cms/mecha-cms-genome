<?php

class Shield extends Genome {

    public static $lot = [];

    public static function version($id, $v = null) {
        return Mecha::version($v, self::info($id)->version('0.0.0'));
    }

    public static function lot($key = null, $fail = false) {
        if (isset($key)) {
            return Anemon::get(self::$lot, $key, $fail);
        }
        return !empty(self::$lot) ? self::$lot : $fail;
    }

    protected static function X($input) {
        $x = substr($input, -4) !== '.php' ? '.php' : "";
        return $input . $x;
    }

    public static function path($input, $fail = false) {
        extract(Lot::get(null, []));
        // Full path, be quick!
        if (is_string($input) && strpos($input, ROOT) === 0) {
            return File::exist(self::X($input), $fail);
        } else if (is_string($input)) {
            $input = str_replace(DS, '/', $input);
        }
        $input = Anemon::step($input, '/');
        array_unshift($input, str_replace('/', '.', $input[0]));
        foreach ($input as $k => $v) {
            $v = To::path($v);
            $input[$k] = self::X(SHIELD . DS . $config->shield . DS . trim($v, DS));
        }
        return File::exist($input, $fail);
    }

    public static function info($id = null) {
        extract(Lot::get(null, []));
        $id = $id ?: $config->shield;
        // Check whether the localized “about” file is available
        $f = SHIELD . DS . $id . DS;
        if (!$info = File::exist($f . 'about.' . $config->language . '.txt')) {
            $info = $f . 'about.txt';
        }
        return new Page($info, [
            'id' => Folder::exist($f) ? $id : null,
            'title' => To::title($id),
            'author' => $language->anonymous,
            'type' => 'HTML',
            'version' => '0.0.0',
            'content' => $language->_message_avail($language->description)
        ], strtolower(static::class));
    }

    public static function get($input, $fail = false, $buffer = true) {
        if ($path__ = self::path($input, $fail)) {
            $G = ['source' => $input];
            $NS = strtolower(static::class) . Anemon::NS . 'get' . Anemon::NS;
            $lot__ = self::lot(null, []);
            $path__ = Hook::NS($NS . 'path', $path__);
            $G['lot'] = $lot__;
            $G['path'] = $path__;
            $out = "";
            // Begin shield part
            Hook::NS($NS . 'lot' . Anemon::NS . 'before', [null, $G, $G]);
            extract(Lot::get(null, []));
            extract(Hook::NS($NS . 'lot', $lot__));
            Hook::NS($NS . 'lot' . Anemon::NS . 'after', [null, $G, $G]);
            Hook::NS($NS . 'before', [null, $G, $G]);
            $state = self::state($config->shield, []);
            if ($buffer) {
                ob_start(function($content) use($path__, $NS, &$out) {
                    $content = Hook::NS($NS . 'input', [$content, $path__]);
                    $out = Hook::NS($NS . 'output', [$content, $path__]);
                    return $out;
                });
                require $path__;
                ob_end_flush();
            } else {
                require $path__;
            }
            $G['content'] = $out;
            // Reset shield part lot
            self::$lot = [];
            // End shield part
            Hook::NS($NS . 'after', [null, $G, $G]);
        }
    }

    public static function attach($input, $fail = false, $buffer = true) {
        $path__ = self::path($input, $fail);
        $G = ['source' => $input];
        $NS = strtolower(static::class) . Anemon::NS;
        $lot__ = self::lot(null, []);
        $path__ = Hook::NS($NS . 'path', $path__);
        $G['lot'] = $lot__;
        $G['path'] = $path__;
        $out = "";
        // Begin shield
        Hook::NS($NS . 'lot' . Anemon::NS . 'before', [null, $G, $G]);
        extract(Lot::get(null, []));
        extract(Hook::NS($NS . 'lot', $lot__));
        Hook::NS($NS . 'lot' . Anemon::NS . 'after', [null, $G, $G]);
        Hook::NS($NS . 'before', [null, $G, $G]);
        $state = self::state($config->shield, []);
        if ($path__) {
            if ($buffer) {
                ob_start(function($content) use($path__, $NS, &$out) {
                    $content = Hook::NS($NS . 'input', [$content, $path__]);
                    $out = Hook::NS($NS . 'output', [$content, $path__]);
                    return $out;
                });
                require $path__;
                ob_end_flush();
            } else {
                require $path__;
            }
        }
        $G['content'] = $out;
        // Reset shield lot
        self::$lot = [];
        // End shield
        Hook::NS($NS . 'after', [null, $G, $G]);
        exit;
    }

    public static function abort($code = '404', $fail = false, $buffer = true) {
        $path = self::path($code);
        $s = explode(Anemon::NS, $path);
        $s = array_pop($s);
        $s = is_numeric($s) ? $s : '404';
        HTTP::status((int) $s);
        self::attach($path, $fail, $buffer);
    }

    public static function exist($input, $fail = false) {
        return Folder::exist(SHIELD . DS . $input, $fail);
    }

    public static function state(...$lot) {
        $id = basename(array_shift($lot));
        $key = array_shift($lot);
        $fail = array_shift($lot) ?: false;
        $folder = (is_array($key) ? $fail : array_shift($lot)) ?: SHIELD;
        $state = $folder . DS . $id . DS . 'state' . DS . 'config.php';
        if (!file_exists($state)) {
            return is_array($key) ? $key : $fail;
        }
        $state = include $state;
        if (is_array($key)) {
            return array_replace_recursive($key, $state);
        }
        return isset($key) ? (array_key_exists($key, $state) ? $state[$key] : $fail) : $state;
    }

}
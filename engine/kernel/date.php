<?php

final class Date extends Genome {

    private static $locale;
    private static $zone;

    public $o;
    public $parent;
    public $source;

    public function ISO8601() {
        return $this->format('c');
    }

    public function __call(string $kin, array $lot = []) {
        if ($v = self::_($kin)) {
            if (is_string($v = $v[0]) && strpos($v, '%') !== false) {
                return $this->f($v);
            }
        }
        return parent::__call($kin, $lot);
    }

    public function __construct($date) {
        if (is_numeric($date)) {
            $this->source = date('Y-m-d H:i:s', $date);
        } else if (strlen($date) >= 19 && substr_count($date, '-') === 5) {
            $this->source = \DateTime::createFromFormat('Y-m-d-H-i-s', $date)->format('Y-m-d H:i:s');
        } else {
            $this->source = date('Y-m-d H:i:s', strtotime($date));
        }
    }

    public function __invoke(string $pattern = '%Y-%m-%d %H:%I:%S') {
        return $this->f($pattern);
    }

    public function __toString() {
        return (string) $this->source;
    }

    public function date() {
        return $this->format('d');
    }

    public function day($type = null) {
        return $this->f(is_string($type) ? '%A' : '%u');
    }

    public function f(string $pattern = '%Y-%m-%d %H:%I:%S') {
        return strftime($pattern, strtotime($this->source));
    }

    public function format(string $format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->source)); // Generic PHP date formatter
    }

    public function hour($type = null) {
        return $this->format($type === 12 ? 'h' : 'H');
    }

    public function minute() {
        return $this->format('i');
    }

    public function month($type = null) {
        return $this->f(is_string($type) ? '%B' : '%m');
    }

    public function second() {
        return $this->format('s');
    }

    public function slug($separator = '-') {
        return strtr($this->source, '- :', str_repeat($separator, 3));
    }

    public function to(string $zone = 'UTC') {
        $date = new \DateTime($this->source);
        $date->setTimeZone(new \DateTimeZone($zone));
        if (!isset($this->o[$zone])) {
            $this->o[$zone] = new static($date->format('Y-m-d H:i:s'));
            $this->o[$zone]->parent = $this;
        }
        return $this->o[$zone];
    }

    public function year() {
        return $this->format('Y');
    }

    public static function from($in) {
        return new static($in);
    }

    public static function locale($locale = null) {
        if (!isset($locale)) {
            return self::$locale ?? locale_get_default();
        }
        setlocale(LC_TIME, self::$locale = (array) ($locale ?? locale_get_default()));
    }

    public static function zone(string $zone = null) {
        if (!isset($zone)) {
            return self::$zone ?? date_default_timezone_get();
        }
        return date_default_timezone_set(self::$zone = $zone ?? date_default_timezone_get());
    }

}
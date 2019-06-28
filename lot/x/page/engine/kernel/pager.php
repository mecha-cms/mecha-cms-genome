<?php

abstract class Pager extends Genome {

    const next = '&#x25B6;';
    const parent = '&#x25C6;';
    const prev = '&#x25C0;';

    public $next;
    public $parent;
    public $prev;

    public function __toString() {
        return $this->prev(self::prev) . ' ' . $this->parent(self::parent) . ' ' . $this->next(self::next);
    }

    public function next(string $text = null) {
        $next = isset($this->next) ? $this->next . strtr($GLOBALS['URL']['query'], ['&' => '&amp;']) . $GLOBALS['URL']['hash'] : null;
        if (isset($text)) {
            return $next !== null ? '<a href="' . $next . '" rel="next">' . $text . '</a>' : '<span>' . $text . '</span>';
        }
        return $next;
    }

    public function parent(string $text = null) {
        $parent = isset($this->parent) ? $this->parent . strtr($GLOBALS['URL']['query'], ['&' => '&amp;']) . $GLOBALS['URL']['hash'] : null;
        if (isset($text)) {
            return $parent !== null ? '<a href="' . $parent . '">' . $text . '</a>' : '<span>' . $text . '</span>';
        }
        return $parent;
    }

    public function prev(string $text = null) {
        $prev = isset($this->prev) ? $this->prev . strtr($GLOBALS['URL']['query'], ['&' => '&amp;']) . $GLOBALS['URL']['hash'] : null;
        if (isset($text)) {
            return $prev !== null ? '<a href="' . $prev . '" rel="prev">' . $text . '</a>' : '<span>' . $text . '</span>';
        }
        return $prev;
    }

}
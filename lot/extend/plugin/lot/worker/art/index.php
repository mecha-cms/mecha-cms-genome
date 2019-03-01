<?php

namespace fn\art {
    function css($content) {
        $content = \trim($content);
        if ($content && \strpos($content, '</style>') === false && \strpos($content, '<link ') === false) {
            return '<style media="screen">' . $content . '</style>';
        }
        return $content;
    }
    function js($content) {
        $content = \trim($content);
        if ($content && \strpos($content, '</script>') === false && \strpos($content, '<script ') === false) {
            return '<script>' . $content . '</script>';
        }
        return $content;
    }
    function start() {
        global $config, $url;
        $folder = PAGE . DS . ($url->path ?: $config->path);
        $i = $url->i ?: 1;
        if ($path = \File::exist([
            $folder . DS . $i . '.page',
            $folder . DS . $i . '.archive',
            $folder . '.page',
            $folder . '.archive'
        ])) {
            extract(\Page::open($path)->get([
                'css' => null,
                'js' => null
            ]));
            \Config::set('has', [
                'css' => !!$css,
                'js' => !!$js
            ]);
            \Config::set('is.art', $css || $js);
            \Config::set('not.art', !$css && !$js);
        }
    }
    if (!\HTTP::is('get', 'art') || \HTTP::get('art')) {
        \Hook::set('content', __NAMESPACE__, 1);
        \Hook::set('page.css', __NAMESPACE__ . "\\css", 2);
        \Hook::set('page.js', __NAMESPACE__ . "\\js", 2);
        \Hook::set('start', __NAMESPACE__ . "\\start", 0);
    }
}

namespace fn {
    function art($content) {
        if (!$page = \Lot::get('page')) {
            return $content;
        }
        // Append custom CSS before `</head>`…
        $content = \str_replace('</head>', $page->css . '</head>', $content);
        // Append custom JS before `</body>`…
        $content = \str_replace('</body>', $page->js . '</body>', $content);
        return $content;
    }
}
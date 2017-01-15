<?php

// Require the plug manually …
require __DIR__ . DS . 'engine' . DS . 'plug' . DS . 'get.php';

// Store page state to registry …
if ($state = Extend::state(__DIR__)) {
    Config::extend($state);
}

$path = $url->path;
$path_array = explode('/', $path);

$site->type = '404'; // default is `404`
$site->state = 'page'; // default is `page`

if ($path === "" || $path === $site->path) {
    $site->type = ""; // default is ``
}

$n = DS . Path::B($path);
$folder = PAGE . DS . $path;

if ($file = File::exist([
    $folder . '.page',
    $folder . '.archive',
    $folder . $n . '.page',
    $folder . $n . '.archive'
])) {
    $site->type = 'page';
    $site->state = Path::X($file);
    if (!File::exist($folder . $n . '.page') && Get::pages($folder, 'page')) {
        $site->type = 'pages';
    }
}

function fn_page_url($content, $lot) {
    $path = Path::F($lot['path'], PAGE);
    return rtrim(__url__('url') . '/' . ltrim(To::url($path), '/'), '/');
}

Hook::set('page.url', 'fn_page_url', 1);

Route::set(['%*%/%i%', '%*%', ""], function($path = "", $step = 1) {
    extract(Lot::get(null, []));
    // Prevent directory traversal attack <https://en.wikipedia.org/wiki/Directory_traversal_attack>
    $path = str_replace('../', "", urldecode($path));
    if ($path === $site->path) {
        Guardian::kick(""); // Redirect to home page …
    }
    $step = $step - 1; // 0–based index …
    $path_alt = ltrim($path === "" ? $site->path : $path, '/');
    $folder = rtrim(PAGE . DS . To::path($path_alt), DS);
    $folder_shield = rtrim(SHIELD . DS . To::path($config->shield), DS);
    $name = Path::B($folder);
    // Horizontal elevator …
    $elevator = [
        'direction' => [
            '-1' => 'previous',
             '1' => 'next'
        ],
        'union' => [
            '-2' => [
                2 => ['rel' => null]
            ],
            '-1' => [
                1 => '&#x25C0;',
                2 => ['rel' => 'prev']
            ],
             '1' => [
                1 => '&#x25B6;',
                2 => ['rel' => 'next']
            ]
        ]
    ];
    Lot::set([
        'pager' => new Elevator([], 1, $step, '/', $elevator, 'pager'),
        'page' => new Page
    ]);
    $pages = $page = [];
    Config::set('page.title', new Anemon([$site->title], ' &#x00B7; '));
    if ($file = File::exist([
        $folder . '.page', // `lot\page\page-slug.page`
        $folder . '.archive', // `lot\page\page-slug.archive`
        $folder . DS . $name . '.page', // `lot\page\page-slug\page-slug.page`
        $folder . DS . $name . '.archive' // `lot\page\page-slug\page-slug.archive`
    ])) { // File does exist, then …
        // Load user function(s) from the current shield folder if any
        if ($fn = File::exist($folder_shield . DS . 'index.php')) include $fn;
        if ($fn = File::exist($folder_shield . DS . 'index__.php')) include $fn;
        // Load user function(s) from the current page folder if any, stacked from the parent page(s)
        $s = PAGE;
        foreach (explode('/', '/' . $path) as $ss) {
            $s .= $ss ? DS . $ss : "";
            if ($fn = File::exist($s . DS . 'index.php')) include $fn;
            if ($fn = File::exist($s . DS . 'index__.php')) include $fn;
        }
        $page = new Page($file);
        $sort = $page->sort($site->sort);
        $chunk = $page->chunk($site->chunk);
        // Create elevator for single page mode
        $folder_parent = Path::D($folder);
        $path_parent = Path::D($path);
        $name_parent = Path::B($folder_parent);
        if ($file_parent = File::exist([
            $folder_parent . '.page',
            $folder_parent . '.archive',
            $folder_parent . DS . $name_parent . '.page',
            $folder_parent . DS . $name_parent . '.archive'
        ])) {
            $sort_parent = Page::open($file_parent)->get('sort', $site->sort);
            $files_parent = fn_get_pages($folder_parent, 'page', $sort_parent[0], $sort_parent[1], 'slug');
        } else {
            $files_parent = [];
        }
        Lot::set([
            'pager' => new Elevator($files_parent, null, $page->slug, $url . '/' . $path_parent, $elevator, 'pager'),
            'page' => $page
        ]);
        Config::set('page.title', new Anemon([$page->title, $site->title], ' &#x00B7; '));
        if (!File::exist($folder . DS . $name . '.' . $page->state)) {
            if ($files = fn_get_pages($folder, 'page', $sort[0], $sort[1], 'path')) {
                foreach (Anemon::eat($files)->chunk($chunk, $step) as $file) {
                    $pages[] = new Page($file);
                }
                if (empty($pages)) {
                    Shield::abort(['204/' . $path_alt, '404/' . $path_alt, '204', '404']);
                }
                Lot::set([
                    'pager' => new Elevator($files, $chunk, $step, $url . '/' . $path, $elevator, 'pager'),
                    'pages' => $pages
                ]);
                Shield::attach('pages/' . $path_alt);
            } else if ($name === $name_parent && File::exist($folder . '.' . $page->state)) {
                Guardian::kick($path_parent);  // Redirect to parent page if user tries to access the placeholder page …
            }
        }
        Shield::attach('page/' . $path_alt);
    }
}, 20);
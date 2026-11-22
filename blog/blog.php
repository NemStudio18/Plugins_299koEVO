<?php

/**
 * @copyright (C) 2024, 299Ko, based on code (2010-2021) 99ko https://github.com/99kocms/
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Jonathan Coulet <j.coulet@gmail.com>
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * @author Frédéric Kaplon <frederic.kaplon@me.com>
 * @author Florent Fortat <florent.fortat@maxgun.fr>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('Access denied!');

use Blog\Hooks\BlogHooks;

// 1. Autoloader for Blog namespace (maps to src/)
spl_autoload_register(function ($class) {
    if (strpos($class, 'Blog\\') === 0) {
        $path = str_replace('\\', '/', substr($class, 5));
        $file = __DIR__ . '/src/' . $path . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// 2. Backward compatibility aliases (migration from legacy entities/ to src/Entities/)
if (!class_exists('news', false)) {
    class_alias(\Blog\Entities\News::class, 'news');
}
if (!class_exists('newsManager', false)) {
    class_alias(\Blog\Entities\NewsManager::class, 'newsManager');
}
if (!class_exists('newsComment', false)) {
    class_alias(\Blog\Entities\Comment::class, 'newsComment');
}
if (!class_exists('BlogCategory', false)) {
    class_alias(\Blog\Entities\Category::class, 'BlogCategory');
}
if (!class_exists('BlogCategoriesManager', false)) {
    class_alias(\Blog\Entities\CategoriesManager::class, 'BlogCategoriesManager');
}
if (!class_exists('Blog\\Entities\\NewsComment', false)) {
    class_alias(\Blog\Entities\Comment::class, 'Blog\\Entities\\NewsComment');
}
if (!class_exists('Blog\\Entities\\BlogCategory', false)) {
    class_alias(\Blog\Entities\Category::class, 'Blog\\Entities\\BlogCategory');
}

// 2. Register Hooks
// Hooks are now registered via param/hooks.json

// 3. Wrappers for legacy Core calls

function blogInstall()
{
    if (class_exists('\Blog\Hooks\BlogHooks')) {
        BlogHooks::install();
    }
}


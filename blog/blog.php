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

require_once PLUGINS . 'blog/entities/news.php';
require_once PLUGINS . 'blog/entities/newsComment.php';
require_once PLUGINS . 'blog/entities/newsManager.php';
require_once PLUGINS . 'blog/entities/BlogCategoriesManager.php';
require_once PLUGINS . 'blog/entities/BlogCategory.php';



## Fonction d'installation

function blogInstall() {

}

## Hooks

function blogEndFrontHead() {
    $core = \Core\Core::getInstance();
    echo '<link rel="alternate" type="application/rss+xml" href="' . \Core\Router\Router::getInstance()->generate('blog-rss') . '" title="' . $core->getConfigVal('siteName') . '">' . "\n";
}

function blogLinkShortcode(array $attributes):string {
    $newsManager = new newsManager();
    $news = $newsManager->create((int) $attributes['id']);
    if (!$news) {
        return '';
    }
    if (!isset($attributes['name'])) {
        $attributes['name'] = $news->getName();
    }
    return '<a href="' . \Core\Router\Router::getInstance()->generate('blog-read', ['id' => $news->getId(), 'name' => \Utils\Util::strToUrl($news->getName())]) . '">' . $attributes['name'] . '</a>';
}

function blogBeforeRunPlugin() {
    \Content\ContentParser::addShortcode('blogLink', 'BlogLinkShortcode');
}

function BlogAdminCategoriesTemplates() {
    global $runPlugin;
    if ($runPlugin === null || $runPlugin->getName() !== 'blog') {
        return;
    }

    $catsManager = new BlogCategoriesManager();

    echo '<a title="' . \Core\Lang::get('blog-categories-management-title') . '" id="cat_link" data-fancybox data-type="ajax" href="#" data-src="' .  $catsManager->getAjaxDisplayListUrl() . '"><i class="fa-regular fa-folder-open"></i></a>';
}


## Code relatif au plugin


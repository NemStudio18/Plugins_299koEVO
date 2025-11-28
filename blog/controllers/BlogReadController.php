<?php

use Core\Controllers\PublicController;
use Core\Responses\PublicResponse;
use Utils\Show;
use Core\Lang;
use Utils\Util;
use Core\Router\Router;

/**
 * @copyright (C) 2023, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 *
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('Access denied!');

class BlogReadController extends PublicController
{

    public function read($name, $id)
    {
        $antispam = ($this->pluginsManager->isActivePlugin('antispam')) ? new antispam() : false;
        $newsManager = new newsManager();
        $categoriesManager = new BlogCategoriesManager();

        $item = $newsManager->create($id);
        if (!$item) {
            $this->core->error404();
        }

        $newsManager->loadComments($item->getId());
        $this->addMetas($item);

        $antispamField = ($antispam) ? $antispam->show() : '';
        // Traitements divers : métas, fil d'ariane...
        $this->runPlugin->setMainTitle($item->getName());
        $this->runPlugin->setTitleTag($item->getName());

        $generatedHTML = Util::generateIdForTitle(htmlspecialchars_decode($item->getParsedContent()));
        $toc = $this->generateTOC($generatedHTML);

        $categories = [];
        $itemId = (int) $item->getId();
        foreach ($categoriesManager->getCategories() as $cat) {
            // Convertir les items en entiers pour la comparaison stricte
            $catItems = array_map('intval', $cat->items ?? []);
            if (in_array($itemId, $catItems, true)) {
                $categories[] = [
                    'label' => $cat->label,
                    'url' => $this->router->generate('blog-category', ['name' => Util::strToUrl($cat->label), 'id' => $cat->id]),
                ];
            }
        }

        $response = new PublicResponse();
        $tpl = $response->createPluginTemplate('blog', 'read');

        Show::addSidebarPublicModule('Catégories du blog', $this->generateCategoriesSidebar());
        Show::addSidebarPublicModule('Derniers commentaires', $this->generateLastCommentsSidebar());

        $tpl->set('antispam', $antispam);
        $tpl->set('antispamField', $antispamField);
        $tpl->set('item', $item);
        $tpl->set('generatedHtml', $generatedHTML);
        $tpl->set('TOC', $toc);
        $tpl->set('categories', $categories);
        $tpl->set('newsManager', $newsManager);
        $tpl->set('commentSendUrl', $this->router->generate('blog-send'));
        // Vérifier si le plugin SEO est activé et si au moins un réseau est configuré
        $seoActive = $this->pluginsManager->isActivePlugin('seo');
        $tpl->set('seoActive', $seoActive);

        $response->addTemplate($tpl);
        return $response;

    }

    protected function generateTOC($html)
    {
        $displayTOC = $this->runPlugin->getConfigVal('displayTOC');
        $toc = false;

        if ($displayTOC === 'content') {
            $toc = Util::generateTableOfContents($html, Lang::get('blog-toc-title'));
            if (!$toc) {
                return false;
            }
        } elseif ($displayTOC === 'sidebar') {
            $toc = Util::generateTableOfContentAsModule($html);
            if ($toc) {
                Show::addSidebarPublicModule(Lang::get('blog-toc-title'), $toc);
                return false;
            }
        }
        return $toc;
    }

    protected function addMetas($item)
    {
        $this->core->addMeta('<meta property="og:url" content="' . Util::getCurrentURL() . '" />');
        $this->core->addMeta('<meta property="twitter:url" content="' . Util::getCurrentURL() . '" />');
        $this->core->addMeta('<meta property="og:type" content="article" />');
        $this->core->addMeta('<meta property="og:title" content="' . $item->getName() . '" />');
        $this->core->addMeta('<meta name="twitter:card" content="summary" />');
        $this->core->addMeta('<meta name="twitter:title" content="' . $item->getName() . '" />');
        $this->core->addMeta('<meta property="og:description" content="' . $item->getSEODesc() . '" />');
        $this->core->addMeta('<meta name="twitter:description" content="' . $item->getSEODesc() . '" />');

        if ($this->pluginsManager->isActivePlugin('galerie') && galerie::searchByfileName($item->getImg())) {
            $this->core->addMeta('<meta property="og:image" content="' . Util::urlBuild(UPLOAD . 'galerie/' . $item->getImg()) . '" />');
            $this->core->addMeta('<meta name="twitter:image" content="' . Util::urlBuild(UPLOAD . 'galerie/' . $item->getImg()) . '" />');
        }
    }

    protected function generateLastCommentsSidebar(int $nbComments = 10) {
        $comments = newsManager::getLatestComments($nbComments);
        $str = '<ul class="comments-recent-list">';
        foreach ($comments as $comment) {
            $str .= "<li class='comment-recent'>";
            $str .= "<span class='comment-recent-author'>";
            if ($comment['comment']->getAuthorWebsite()) {
                $str .= "<a href='" . $comment['comment']->getAuthorWebsite() . "'>" . $comment['comment']->getAuthor() . "</a>";
            } else {
                $str .= $comment['comment']->getAuthor();
            }
            $str .= "</span> ";
            $str .= Lang::get('blog.comments.in');
            $str .= " <span class='comment-recent-news'>";
            $str .= "<a href='" . $comment['news']->getUrl() . "#comment". $comment['comment']->getId() ."'>" .$comment['news']->getName() . "</a></span>";
            $str .= "</li>";
        }
        $str .= "</ul>";
        return $str;
    }

    public function send()
    {
        $antispam = ($this->pluginsManager->isActivePlugin('antispam')) ? new antispam() : false;
        $newsManager = new newsManager();
        // quelques contrôle et temps mort volontaire avant le send...
        sleep(2);
        if ($this->runPlugin->getConfigVal('comments') && $_POST['_author'] == '') {
            if (($antispam && $antispam->isValid()) || !$antispam) {
                $idNews = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?? 0;
                $item = $newsManager->create($idNews);
                if ($item && $item->getCommentsOff() == false) {
                    $newsManager->loadComments($idNews);
                    $comment = new newsComment();
                    $comment->setIdNews($idNews);
                    $comment->setAuthor($_POST['author']);
                    $email = filter_input(INPUT_POST,'authorEmail', FILTER_VALIDATE_EMAIL);
                    if ($email !== false) {
                        $comment->setAuthorEmail($_POST['authorEmail']);
                    } else {
                        Show::msg(Lang::get("blog.comments.bad-mail"), 'error');
                        header('location:' . $_POST['back']);
                        die();
                    }

                    $comment->setAuthorWebsite(filter_input(INPUT_POST, 'authorWebsite', FILTER_VALIDATE_URL) ?? null);
                    $comment->setDate('');
                    $comment->setContent($_POST['commentContent']);
                    $parentId = filter_input(INPUT_POST, 'commentParentId', FILTER_VALIDATE_INT) ?? 0;
                    if ($parentId !== 0) {
                        $newsManager->addReplyToComment($comment, $parentId);
                    }
                    if ($newsManager->saveComment($comment)) {
                        header('location:' . $_POST['back'] . '#comment' . $comment->getId());
                        die();
                    }
                }

            }
            Show::msg(Lang::get("antispam.invalid-captcha"), 'error');
        }
        header('location:' . $_POST['back']);
        die();
    }

    public function rss()
    {
        $newsManager = new newsManager();
        echo $newsManager->rss();
        die();
    }

    protected function generateCategoriesSidebar() {
        $content = '';
        $categoriesManager = new BlogCategoriesManager();
        $categories = $categoriesManager->getNestedCategories();
        if (empty($categories)) {
            return false;
        }
        $content .= '<ul>';
        foreach ($categories as $category) {
            $content .= $this->generateCategorySidebar($category);
        }
        $content .= '</ul>';
        return $content;
    }

    protected function generateCategorySidebar($category) {
        $router = Router::getInstance();
        $content = '<li><a href="' . $router->generate('blog-category', ['name' => Util::strToUrl($category->label), 'id' => $category->id]) . '">' .
            $category->label . '</a>';
        if (!empty($category->children)) {
            $content .= '<ul>';
            foreach ($category->children as $child) {
                $content .= $this->generateCategorySidebar($child);
            }
            $content .= '</ul>';
        }
        $content .= '</li>';
        return $content;
    }
}
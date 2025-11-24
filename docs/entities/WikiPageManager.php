<?php

namespace Docs\Entities;

use Core\Core;
use Core\Plugin\PluginsManager;
use Core\Router\Router;
use Utils\Util;

/**
 * @copyright (C) 2022, 299Ko, based on code (2010-2021) 99ko https://github.com/99kocms/
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Jonathan Coulet <j.coulet@gmail.com>
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * @author Maxime Blanc <maximeblanc@flexcb.fr>
 * @author Frédéric Kaplon <frederic.kaplon@me.com>
 * @author Florent Fortat <florent.fortat@maxgun.fr>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('Access denied!');

class WikiPageManager
{
    private const DATA_DIR = DATA_PLUGIN . 'docs/';
    private const PAGES_FILE = self::DATA_DIR . 'pages.json';
    private const HISTORY_FILE = self::DATA_DIR . 'history.json';

    private array $items = [];
    private array $history = [];

    private int $nbItemsToPublic;

    public function __construct()
    {
        $this->ensureDataDirectory();
        $categoriesManager = new WikiCategoriesManager();
        $i = 0;
        $data = [];
        if (file_exists(self::PAGES_FILE)) {
            $temp = Util::readJsonFile(self::PAGES_FILE);
            if (!is_array($temp)) {
                $temp = [];
            }
            // Sort by position ASC, then by date DESC
            usort($temp, function($a, $b) {
                $posA = $a['position'] ?? 0;
                $posB = $b['position'] ?? 0;
                if ($posA !== $posB) {
                    return $posA - $posB;
                }
                return strtotime($b['date']) - strtotime($a['date']);
            });
            foreach ($temp as $k => $v) {
                $categories = [];
                foreach ($categoriesManager->getCategories() as $cat) {
                    if (in_array($v['id'], $cat->items)) {
                        $categories['categories'][$cat->id] = [
                            'label' => $cat->label,
                            'url' => Router::getInstance()->generate('docs-category', ['name' => Util::strToUrl($cat->label), 'id' => $cat->id]),
                            'id' => $cat->id
                        ];
                    }
                }
                $v = array_merge($v, $categories);
                $data[] = new WikiPage($v);
                if ($v['draft'] === "0") {
                    $i++;
                }
            }
        }
        $this->nbItemsToPublic = $i;
        $this->items = $data;
        $this->loadHistory();
    }
    
    /**
     * Retrieves all wiki pages.
     *
     * @return WikiPage[] An array of wiki page objects.
     */

    public function getItems() {
        return $this->items;
    }


    /**
     * Summary of create
     * @param mixed $id
     * @return \WikiPage | boolean
     */
    public function create($id) {
        foreach ($this->items as $obj) {
            if ($obj->getId() == $id)
                return $obj;
        }
        return false;
    }

    public function saveWikiPage(WikiPage $obj, $changeDescription = '')
    {
        $id = intval($obj->getId());
        $isNew = ($id < 1);
        
        if ($isNew) {
            $obj->setId($this->makeId());
            $obj->setVersion(1);
            $obj->setLastModified(date('Y-m-d H:i:s'));
            $this->items[] = $obj;
            
            // Sauvegarder la première version dans l'historique
            $this->saveToHistory($obj, $changeDescription ?: 'Création de la page');
        } else {
            // Sauvegarder l'ancienne version dans l'historique
            $oldPage = $this->create($id);
            if ($oldPage) {
                $this->saveToHistory($oldPage, $changeDescription);
            }
            
            // Incrémenter la version
            $obj->incrementVersion();
            
            foreach ($this->items as $k => $v) {
                if ($id == $v->getId())
                    $this->items[$k] = $obj;
            }
        }
        
        // Enregistrer l'activité
        $this->recordActivity($obj, $isNew ? 'add' : 'edit');
        
        return $this->save();
    }

    /**
     * Delete a WikiPage from wiki & her comments
     * @param WikiPage $obj
     * @return bool WikiPage correctly deleted
     */
    public function delWikiPage(WikiPage $obj): bool
    {
        $id = $obj->getId();
        foreach ($this->items as $k => $v) {
            if ($id == $v->getId())
                unset($this->items[$k]);
        }
        if ($this->save()) {
            // Enregistrer l'activité de suppression
            $this->recordActivity($obj, 'delete');
            return true;
        }
        return false;
    }

    public function count() {
        return count($this->items);
    }

    /**
     * Return number of wiki pages who can be displayed in public mode
     */
    public function getNbItemsToPublic() {
        return $this->nbItemsToPublic;
    }

    public function rss() {
        $core = Core::getInstance();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rss version="2.0">';
        $xml .= '<channel>';
        $xml .= ' <title>' . $core->getConfigVal('siteName') . ' - ' . PluginsManager::getPluginConfVal('docs', 'label') . '</title>';
        $xml .= ' <link>' . $core->getConfigVal('siteUrl') . '/</link>';
        $xml .= ' <description>' . $core->getConfigVal('siteDescription') . '</description>';
        $xml .= ' <language>' . $core->getConfigVal('siteLang') . '</language>';
        foreach ($this->getItems() as $k => $v)
            if (!$v->getDraft()) {
                $xml .= '<item>';
                $xml .= '<title><![CDATA[' . $v->getName() . ']]></title>';
                $xml .= '<link>' . $core->getConfigVal('siteUrl') . Router::getInstance()->generate('docs-read', ['name' => $v->getSlug(), 'id' => $v->getId()]) . '</link>';
                $xml .= '<pubDate>' . (date("D, d M Y H:i:s O", strtotime($v->getDate()))) . '</pubDate>';
                $xml .= '<description><![CDATA[' . $v->getContent() . ']]></description>';
                $xml .= '</item>';
            }
        $xml .= '</channel>';
        $xml .= '</rss>';
        header('Cache-Control: must-revalidate, pre-check=0, post-check=0, max-age=0');
        header('Content-Type: application/rss+xml; charset=utf-8');
        echo $xml;
        die();
    }

    private function makeId() {
        $ids = array(0);
        foreach ($this->items as $obj) {
            $ids[] = $obj->getId();
        }
        return max($ids) + 1;
    }

    private function save() {
        $data = array();
        foreach ($this->items as $k => $v) {
            $data[] = array(
                'id' => $v->getId(),
                'name' => $v->getName(),
                'content' => $v->getContent(),
                'intro' => $v->getIntro(),
                'seoDesc' => $v->getSEODesc(),
                'date' => $v->getDate(),
                'draft' => $v->getDraft(),
                'img' => $v->getImg(),
                'position' => $v->getPosition(),
                'version' => $v->getVersion(),
                'lastModified' => $v->getLastModified(),
                'modifiedBy' => $v->getModifiedBy(),
                'slug' => $v->getSlug(),
                'categories' => $v->categories,
            );
        }
        if (Util::writeJsonFile(self::PAGES_FILE, $data))
            return true;
        return false;
    }

    /**
     * Sauvegarder l'ordre des pages
     * 
     * @param array $order Tableau des IDs dans l'ordre souhaité
     * @return void
     */
    public function savePagesOrder(array $order) {
        error_log('WikiPageManager::savePagesOrder() appelée avec: ' . json_encode($order));
        
        if (empty($order)) {
            error_log('Ordre vide, sortie');
            return;
        }
        
        // Créer un mapping des nouvelles positions
        $positionMap = [];
        foreach ($order as $position => $pageId) {
            $positionMap[$pageId] = $position + 1;
            error_log("Page {$pageId} mise à position " . ($position + 1));
        }
        
        // Mettre à jour les positions des pages
        foreach ($this->items as $page) {
            if (isset($positionMap[$page->getId()])) {
                $page->setPosition($positionMap[$page->getId()]);
                error_log("Page {$page->getId()} position mise à jour: " . $positionMap[$page->getId()]);
            }
        }
        
        // Sauvegarder les changements
        $this->save();
        error_log('Pages sauvegardées');
    }

    // History management
    private function loadHistory() {
        $this->history = [];
        if (file_exists(self::HISTORY_FILE)) {
            $temp = Util::readJsonFile(self::HISTORY_FILE);
            if (is_array($temp)) {
                $nextId = 1;
                foreach ($temp as $v) {
                    // Corriger les IDs null existants
                    if (!isset($v['id']) || $v['id'] === null) {
                        $v['id'] = $nextId++;
                    } else {
                        $nextId = max($nextId, $v['id'] + 1);
                    }
                    $this->history[] = new WikiPageHistory($v);
                }
                // Sauvegarder les corrections si nécessaire
                if ($nextId > 1) {
                    $this->saveHistory();
                }
            }
        }
    }

    private function saveToHistory($page, $changeDescription = '') {
        // Générer un ID unique pour l'entrée d'historique
        $historyId = $this->makeHistoryId();
        
        $historyEntry = new WikiPageHistory([
            'id' => $historyId,
            'pageId' => $page->getId(),
            'version' => $page->getVersion(),
            'name' => $page->getName(),
            'content' => $page->getContent(),
            'intro' => $page->getIntro(),
            'seoDesc' => $page->getSEODesc(),
            'modifiedBy' => $page->getModifiedBy(),
            'modifiedAt' => $page->getLastModified(),
            'changeDescription' => $changeDescription
        ]);
        
        $this->history[] = $historyEntry;
        
        $this->saveHistory();
    }

    private function makeHistoryId() {
        $ids = array(0);
        foreach ($this->history as $entry) {
            $ids[] = $entry->getId();
        }
        return max($ids) + 1;
    }

    private function saveHistory() {
        $data = [];
        foreach ($this->history as $entry) {
            $data[] = [
                'id' => $entry->getId(),
                'pageId' => $entry->getPageId(),
                'version' => $entry->getVersion(),
                'name' => $entry->getName(),
                'content' => $entry->getContent(),
                'intro' => $entry->getIntro(),
                'seoDesc' => $entry->getSEODesc(),
                'modifiedBy' => $entry->getModifiedBy(),
                'modifiedAt' => $entry->getModifiedAt(),
                'changeDescription' => $entry->getChangeDescription()
            ];
        }
        
        return Util::writeJsonFile(self::HISTORY_FILE, $data);
    }

    public function getPageHistory($pageId) {
        $pageHistory = [];
        foreach ($this->history as $entry) {
            if ($entry->getPageId() == $pageId) {
                $pageHistory[] = $entry;
            }
        }
        // Trier par version décroissante
        usort($pageHistory, function($a, $b) {
            return $b->getVersion() - $a->getVersion();
        });
        return $pageHistory;
    }

    public function getPageVersion($pageId, $version) {
        foreach ($this->history as $entry) {
            if ($entry->getPageId() == $pageId && $entry->getVersion() == $version) {
                return new WikiPage([
                    'id' => $entry->getPageId(),
                    'name' => $entry->getName(),
                    'content' => html_entity_decode($entry->getContent(), ENT_QUOTES, 'UTF-8'),
                    'intro' => html_entity_decode($entry->getIntro(), ENT_QUOTES, 'UTF-8'),
                    'seoDesc' => $entry->getSEODesc(),
                    'date' => $entry->getModifiedAt(),
                    'draft' => '0',
                    'img' => '',

                    'version' => $entry->getVersion(),
                    'lastModified' => $entry->getModifiedAt(),
                    'modifiedBy' => $entry->getModifiedBy(),
                    'slug' => Util::strToUrl($entry->getName())
                ]);
            }
        }
        return false;
    }



    /**
     * Enregistrer une activité dans le gestionnaire d'activités
     */
    private function recordActivity($page, $action) {
        if (class_exists(__NAMESPACE__ . '\\WikiActivityManager')) {
            $activityManager = new WikiActivityManager();
            
            // Trouver la catégorie principale de la page
            $categoryName = '';
            $categoriesManager = new WikiCategoriesManager();
            foreach ($categoriesManager->getCategories() as $cat) {
                if (in_array($page->getId(), $cat->items)) {
                    $categoryName = $cat->label;
                    break;
                }
            }
            
            $activityManager->addActivity($action, $page->getName(), $categoryName, $page->getId());
        }
    }

    private function ensureDataDirectory(): void
    {
        if (!is_dir(self::DATA_DIR)) {
            mkdir(self::DATA_DIR, 0755, true);
        }
    }
}
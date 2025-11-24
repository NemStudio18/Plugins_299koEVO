<?php

namespace Docs\Entities;

use Utils\Util;

defined('ROOT') or exit('Access denied!');

class WikiHistoryManager
{
    private const DATA_DIR = DATA_PLUGIN . 'docs/';
    private const HISTORY_FILE = self::DATA_DIR . 'history.json';
    private $history = [];

    public function __construct() {
        $this->ensureDataDirectory();
        $this->loadHistory();
    }

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

    public function addHistoryEntry($page, $changeDescription = '') {
        // Vérifier si cette version existe déjà dans l'historique
        foreach ($this->history as $entry) {
            if ($entry->getPageId() == $page->getId() && $entry->getVersion() == $page->getVersion()) {
                return; // Doublon évité
            }
        }
        
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
        return $this->saveHistory();
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
        
        // Trier par version décroissante (plus récente en premier)
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
    private function ensureDataDirectory(): void
    {
        if (!is_dir(self::DATA_DIR)) {
            mkdir(self::DATA_DIR, 0755, true);
        }
    }
}
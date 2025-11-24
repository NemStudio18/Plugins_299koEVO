<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

class GuestbookManager
{
    private $items;
    private $dataFile;

    public function __construct()
    {
        $this->dataFile = DATA_PLUGIN . 'guestbook/entries.json';
        $this->items = [];
        
        if (file_exists($this->dataFile)) {
            // Lire le fichier directement et nettoyer le BOM UTF-8 si présent
            $rawContent = @file_get_contents($this->dataFile);
            // Nettoyer le BOM UTF-8 (peut être présent au début du fichier)
            $rawContent = ltrim($rawContent, "\xEF\xBB\xBF");
            $rawContent = ltrim($rawContent, "\xFE\xFF"); // UTF-16 BE BOM
            $rawContent = ltrim($rawContent, "\xFF\xFE"); // UTF-16 LE BOM
            $temp = json_decode($rawContent, true);
            
            if ($temp === false || $temp === null || !is_array($temp)) {
                $temp = [];
            }
            
            // Trier par date décroissante
            usort($temp, function($a, $b) {
                return strcmp($b['date'] ?? '', $a['date'] ?? '');
            });
            
            foreach ($temp as $k => $v) {
                if (is_array($v)) {
                    // S'assurer que l'ID est présent (peut être null pour nouveau message)
                    if (!isset($v['id'])) {
                        continue;
                    }
                    $this->items[] = new GuestbookEntry($v);
                }
            }
        }
    }

    public function getItems()
    {
        return $this->items;
    }

    public function getApprovedItems()
    {
        $approved = [];
        foreach ($this->items as $item) {
            if ($item->isApproved()) {
                $approved[] = $item;
            }
        }
        return $approved;
    }

    public function getPendingItems()
    {
        $pending = [];
        foreach ($this->items as $item) {
            if (!$item->isApproved()) {
                $pending[] = $item;
            }
        }
        return $pending;
    }

    public function create($id)
    {
        foreach ($this->items as $obj) {
            if ($obj->getId() == $id) {
                return $obj;
            }
        }
        return false;
    }

    public function saveEntry($obj)
    {
        $id = intval($obj->getId());
        if ($id < 1) {
            $obj->setId($this->makeId());
            $this->items[] = $obj;
        } else {
            foreach ($this->items as $k => $v) {
                if ($id == $v->getId()) {
                    $this->items[$k] = $obj;
                }
            }
        }
        return $this->save();
    }

    public function delEntry($obj)
    {
        $id = $obj->getId();
        foreach ($this->items as $k => $v) {
            if ($id == $v->getId()) {
                unset($this->items[$k]);
                $this->items = array_values($this->items);
                return $this->save();
            }
        }
        return false;
    }

    private function makeId()
    {
        $max = 0;
        foreach ($this->items as $obj) {
            if ($obj->getId() > $max) {
                $max = $obj->getId();
            }
        }
        return $max + 1;
    }

    private function save()
    {
        $data = [];
        foreach ($this->items as $obj) {
            $data[] = $obj->toArray();
        }
        return \Utils\Util::writeJsonFile($this->dataFile, $data);
    }

    public function count()
    {
        return count($this->items);
    }

    public function countApproved()
    {
        return count($this->getApprovedItems());
    }

    public function countPending()
    {
        return count($this->getPendingItems());
    }
}

<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

class SondageManager
{
    private $items;
    private $dataFile;

    public function __construct()
    {
        $this->dataFile = DATA_PLUGIN . 'sondage/sondages.json';
        $this->items = [];
        
        if (file_exists($this->dataFile)) {
            $temp = \Utils\Util::readJsonFile($this->dataFile);
            if (!is_array($temp)) {
                $temp = [];
            }
            foreach ($temp as $k => $v) {
                $this->items[] = new Sondage($v);
            }
        }
    }

    public function getItems()
    {
        return $this->items;
    }

    public function getActiveItems()
    {
        $active = [];
        foreach ($this->items as $item) {
            if ($item->getActive() == '1') {
                $active[] = $item;
            }
        }
        return $active;
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

    public function saveSondage($obj)
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

    public function delSondage($obj)
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
}



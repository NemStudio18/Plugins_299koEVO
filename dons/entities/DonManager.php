<?php

/**
 * @copyright (C) 2025, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

class DonManager
{
    private $dataFile;

    public function __construct()
    {
        $this->dataFile = DATA_PLUGIN . 'dons/dons.json';
        if (!is_dir(dirname($this->dataFile))) {
            mkdir(dirname($this->dataFile), 0755, true);
        }
        if (!file_exists($this->dataFile)) {
            file_put_contents($this->dataFile, json_encode([]));
        }
    }

    public function getDons()
    {
        $content = file_get_contents($this->dataFile);
        $content = trim($content, "\xEF\xBB\xBF");
        $data = json_decode($content, true);
        if (!is_array($data)) {
            return [];
        }
        $dons = [];
        foreach ($data as $don) {
            $dons[] = new Don($don);
        }
        return $dons;
    }

    public function getCompletedDons()
    {
        $dons = $this->getDons();
        return array_filter($dons, function($don) {
            return $don->isCompleted();
        });
    }

    public function getTotalAmount()
    {
        $dons = $this->getCompletedDons();
        $total = 0;
        foreach ($dons as $don) {
            $total += $don->getAmount();
        }
        return $total;
    }

    public function create($id = null)
    {
        $dons = $this->getDons();
        if ($id === null) {
            return new Don();
        }
        foreach ($dons as $don) {
            if ($don->getId() == $id) {
                return $don;
            }
        }
        return null;
    }

    public function findByTransactionId($transactionId)
    {
        $dons = $this->getDons();
        foreach ($dons as $don) {
            if ($don->getTransactionId() === $transactionId) {
                return $don;
            }
        }
        return null;
    }

    public function saveDon($don)
    {
        $dons = $this->getDons();
        
        if ($don->getId() === null) {
            // Nouveau don
            $maxId = 0;
            foreach ($dons as $d) {
                if ($d->getId() > $maxId) {
                    $maxId = $d->getId();
                }
            }
            $don->setId($maxId + 1);
            $don->setDate(date('Y-m-d H:i:s'));
            $dons[] = $don;
        } else {
            // Modification
            foreach ($dons as $key => $d) {
                if ($d->getId() == $don->getId()) {
                    $dons[$key] = $don;
                    break;
                }
            }
        }

        $data = [];
        foreach ($dons as $d) {
            $data[] = $d->toArray();
        }

        return file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
    }
}




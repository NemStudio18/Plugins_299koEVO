<?php

/**
 * @copyright (C) 2025, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

require_once PLUGINS . 'dons/entities/Don.php';
require_once PLUGINS . 'dons/entities/DonManager.php';

## Fonction d'installation

function donsInstall() {
    $dataFile = DATA_PLUGIN . 'dons/dons.json';
    $dataDir = dirname($dataFile);
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    if (!file_exists($dataFile)) {
        file_put_contents($dataFile, json_encode([]));
    }
}

## Code relatif au plugin




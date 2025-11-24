<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

require_once PLUGINS . 'guestbook/entities/GuestbookEntry.php';
require_once PLUGINS . 'guestbook/entities/GuestbookManager.php';

## Fonction d'installation

function guestbookInstall() {
    \Utils\Util::writeJsonFile(DATA_PLUGIN . 'guestbook/entries.json', []);
}

## Hooks

## Code relatif au plugin



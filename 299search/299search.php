<?php
defined('ROOT') or exit('Access denied!');

require_once __DIR__ . '/src/Hooks/SearchHooks.php';

use Core\Plugin\Plugin;

class Plugin299search extends Plugin
{
    public function __construct()
    {
        parent::__construct('299search');
    }
}

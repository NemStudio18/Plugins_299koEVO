<?php

defined('ROOT') or exit('Access denied!');

/**
 * Plugin Pipeline Communautaire pour 299ko EVO
 * Portail de soumission et audit automatisé des plugins et thèmes
 */

function communitypipelineBeforeRunPlugin(): void
{
    foreach (glob(PLUGINS . 'communitypipeline/controllers/' . '*.php') as $file) {
        require_once $file;
    }
}

function communitypipelineInstall(): void
{
    $dataDir = DATA_PLUGIN . 'communitypipeline' . DS;
    if (!is_dir($dataDir)) {
        @mkdir($dataDir, 0755, true);
    }
}

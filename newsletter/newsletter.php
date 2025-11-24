<?php

/**
 * @copyright (C) 2025, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

require_once PLUGINS . 'newsletter/entities/NewsletterSubscriber.php';
require_once PLUGINS . 'newsletter/entities/NewsletterManager.php';

## Fonction d'installation

function newsletterInstall() {
    $dataFile = DATA_PLUGIN . 'newsletter/subscribers.json';
    $dataDir = dirname($dataFile);
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    if (!file_exists($dataFile)) {
        file_put_contents($dataFile, json_encode([]));
    }
}

## Hooks

function newsletterEndFrontBody() {
    $pluginsManager = \Core\Plugin\PluginsManager::getInstance();
    if (!$pluginsManager->isActivePlugin('newsletter')) {
        return;
    }
    
    $router = \Core\Router\Router::getInstance();
    $subscribeUrl = $router->generate('newsletter-subscribe');
    
    $templateFile = PLUGINS . 'newsletter/template/modal.tpl';
    if (!file_exists($templateFile)) {
        return;
    }
    
    $tpl = new \Template\Template($templateFile);
    $tpl->set('subscribeUrl', $subscribeUrl);
    
    echo $tpl->output();
}

## Code relatif au plugin


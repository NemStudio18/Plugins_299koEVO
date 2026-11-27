<?php

/**
 * @copyright (C) 2026, 299Ko EVO
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author 299Ko Team
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('No direct script access allowed');

require_once PLUGINS . 'game/entities/GameScore.php';

/**
 * Routine d'installation du plugin Game
 */
function gameInstall()
{
    $dataDir = DATA_PLUGIN . 'game';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }

    $scoresFile = $dataDir . '/scores.json';
    if (!file_exists($scoresFile)) {
        file_put_contents($scoresFile, json_encode([], JSON_PRETTY_PRINT));
    }
}

/**
 * Hook de construction de la navigation (NavigationTree)
 *
 * @param \Core\Navigation\NavigationTree $tree
 */
function gameNavigation($tree)
{
    $plugin = \Core\Plugin\PluginsManager::getInstance()->getPlugin('game');
    if (!$plugin || $plugin->getConfigVal('activate') != 1) {
        return;
    }

    $label = $plugin->getConfigVal('nav_label') ?: 'Tetris';

    if (is_object($tree) && method_exists($tree, 'addItem')) {
        $tree->addItem(\Core\Navigation\NavigationItem::fromArray([
            'id' => 'plugin.game.main',
            'parentId' => null,
            'label' => $label,
            'target' => '/game',
            'position' => 75,
            'source' => 'plugin:game',
            'visibility' => ['public' => true],
            'icon' => 'fa-solid fa-gamepad'
        ]));
    }

    // Compatibilité thèmes avec navigation legacy
    if (method_exists($plugin, 'addToNavigation')) {
        $plugin->addToNavigation($label, '/game', '_self', 1, 0, 'fa-solid fa-gamepad');
    }
}

/**
 * Hook de chargement d'entête CSS
 */
function gameEndFrontHead()
{
    $core = \Core\Core::getInstance();
    $plugin = \Core\Plugin\PluginsManager::getInstance()->getPlugin('game');
    if (!$plugin || $plugin->getConfigVal('activate') != 1) {
        return '';
    }

    $siteUrl = $core->getConfigVal('siteUrl');
    $cssUrl = $siteUrl . '/plugin/game/template/public.css';

    return '<link rel="stylesheet" href="' . $cssUrl . '?v=' . VERSION . '">';
}

/**
 * Enregistrement du shortcode [tetris] pour intégration dans n'importe quelle page
 */
if (class_exists('\Content\ContentParser')) {
    \Content\ContentParser::addShortcode('tetris', function (array $attributes = []) {
        $palette = $attributes['palette'] ?? 'gbc_color';
        return '<div class="evo-tetris-embed" data-default-palette="' . htmlspecialchars($palette, ENT_QUOTES, 'UTF-8') . '">
            <p style="text-align:center;"><a href="/game" class="button"><i class="fa-solid fa-gamepad"></i> Jouer à Game Boy Tetris en plein écran</a></p>
        </div>';
    });
}

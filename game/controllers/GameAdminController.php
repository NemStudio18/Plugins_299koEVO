<?php

namespace Game\Controllers;

use Core\Controllers\AdminController;
use Core\Responses\AdminResponse;
use Utils\Show;
use Core\Lang;
use Utils\Util;
use Game\Entities\GameScore;

defined('ROOT') or exit('No direct script access allowed');

class GameAdminController extends AdminController
{
    /**
     * Dashboard d'administration du jeu Tetris
     */
    public function index()
    {
        $evaluator = \Core\Navigation\AccessPolicyEvaluator::getInstance();
        $ctx = new \Core\Navigation\ConditionContext($this->user);
        if (!$evaluator->isAdmin($ctx)) {
            Show::msg(Lang::get('core-not-authorized'), 'error');
            $this->core->redirect($this->router->generate('admin-home'));
            return;
        }

        $this->runPlugin->setMainTitle('Gestion du Jeu Tetris & Leaderboard');
        $this->runPlugin->setTitleTag('Administration Tetris | 299ko EVO');

        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('game', 'admin');

        $allScores = GameScore::all();
        usort($allScores, function ($a, $b) {
            return $b->getScore() <=> $a->getScore();
        });

        // Statistiques
        $totalGames = count($allScores);
        $highScore = !empty($allScores) ? $allScores[0]->getScore() : 0;
        $totalLines = array_reduce($allScores, function ($acc, $s) {
            return $acc + $s->getLines();
        }, 0);
        $uniquePlayers = count(array_unique(array_map(function ($s) {
            return $s->getPlayerToken();
        }, $allScores)));

        $tpl->set('scores', array_slice($allScores, 0, 50));
        $tpl->set('totalGames', $totalGames);
        $tpl->set('highScore', $highScore);
        $tpl->set('totalLines', $totalLines);
        $tpl->set('uniquePlayers', $uniquePlayers);
        $tpl->set('config', $this->runPlugin->getConfig());
        $tpl->set('csrfToken', \Core\Security\Csrf::token());

        $response->addTemplate($tpl);
        return $response;
    }

    /**
     * Sauvegarde de la configuration du plugin
     */
    public function saveConfig()
    {
        $evaluator = \Core\Navigation\AccessPolicyEvaluator::getInstance();
        $ctx = new \Core\Navigation\ConditionContext($this->user);
        if (!$evaluator->isAdmin($ctx)) {
            Show::msg(Lang::get('core-not-authorized'), 'error');
            $this->core->redirect($this->router->generate('admin-home'));
            return;
        }

        $token = $_POST['_csrf'] ?? '';
        if (!\Core\Security\Csrf::validate($token) && $token !== $this->user->token) {
            Show::msg('Token de sécurité invalide.', 'error');
            $this->core->redirect($this->router->generate('admin-game-index'));
            return;
        }

        $navLabel = trim(filter_var($_POST['nav_label'] ?? 'Tetris', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $defaultPalette = in_array($_POST['default_palette'] ?? '', ['gb_classic', 'gbc_color', 'gb_pocket']) 
            ? $_POST['default_palette'] 
            : 'gbc_color';
        $soundEnabled = isset($_POST['sound_enabled']) ? 1 : 0;
        $maxLeaderboard = max(5, min(50, (int) ($_POST['max_leaderboard'] ?? 10)));

        $config = $this->runPlugin->getConfig();
        $config['nav_label'] = $navLabel;
        $config['default_palette'] = $defaultPalette;
        $config['sound_enabled'] = $soundEnabled;
        $config['max_leaderboard'] = $maxLeaderboard;

        $this->pluginsManager->savePluginConfig($this->runPlugin);
        Show::msg('Configuration de Tetris enregistrée avec succès !', 'success');

        $this->core->redirect($this->router->generate('admin-game-index'));
    }

    /**
     * Suppression d'un score précis
     */
    public function deleteScore($id, $token)
    {
        if (!\Core\Security\Csrf::validate($token) && $token !== $this->user->token) {
            Show::msg('Token CSRF invalide', 'error');
            $this->core->redirect($this->router->generate('admin-game-index'));
            return;
        }

        $score = GameScore::findPK((int) $id);
        if ($score) {
            $score->delete();
            Show::msg('Score supprimé avec succès.', 'success');
        } else {
            Show::msg('Score introuvable.', 'error');
        }

        $this->core->redirect($this->router->generate('admin-game-index'));
    }

    /**
     * Réinitialisation complète du leaderboard
     */
    public function resetScores($token)
    {
        if (!\Core\Security\Csrf::validate($token) && $token !== $this->user->token) {
            Show::msg('Token CSRF invalide', 'error');
            $this->core->redirect($this->router->generate('admin-game-index'));
            return;
        }

        $all = GameScore::all();
        foreach ($all as $score) {
            $score->delete();
        }

        Show::msg('Le classement a été entièrement réinitialisé.', 'success');
        $this->core->redirect($this->router->generate('admin-game-index'));
    }
}

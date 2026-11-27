<?php

namespace Game\Controllers;

use Core\Controllers\PublicController;
use Core\Responses\PublicResponse;
use Core\Core;
use Game\Entities\GameScore;

defined('ROOT') or exit('No direct script access allowed');

class GameController extends PublicController
{
    /**
     * Page publique de jeu
     */
    public function play()
    {
        $this->runPlugin->setMainTitle($this->runPlugin->getConfigVal('nav_label') ?: 'Game Boy Tetris');
        $this->runPlugin->setTitleTag('Game Boy Tetris & Leaderboard | 299ko EVO');

        $response = new PublicResponse();
        $tpl = $response->createPluginTemplate('game', 'public');

        $topScores = GameScore::getTopScores(10);
        $todayTop = GameScore::getTodayTop(5);

        $tpl->set('topScores', $topScores);
        $tpl->set('todayTop', $todayTop);
        $tpl->set('defaultPalette', $this->runPlugin->getConfigVal('default_palette') ?: 'gbc_color');
        $tpl->set('soundEnabled', (int) ($this->runPlugin->getConfigVal('sound_enabled') ?? 1));

        $response->addTemplate($tpl);
        return $response;
    }

    /**
     * API REST JSON : Récupération des classements (Top 10, Aujourd'hui, et Mes Records)
     */
    public function apiScores()
    {
        header('Content-Type: application/json; charset=utf-8');

        $token = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) ($_GET['player_token'] ?? ($_GET['token'] ?? '')));

        $top = array_map(function ($s) {
            return [
                'name' => $s->getPlayerName(),
                'score' => $s->getScore(),
                'lines' => $s->getLines(),
                'level' => $s->getLevel(),
                'palette' => $s->getPalette(),
                'date' => date('d/m/Y H:i', strtotime($s->getCreatedAt()))
            ];
        }, GameScore::getTopScores(10));

        $today = array_map(function ($s) {
            return [
                'name' => $s->getPlayerName(),
                'score' => $s->getScore(),
                'lines' => $s->getLines(),
                'level' => $s->getLevel(),
                'palette' => $s->getPalette(),
                'date' => date('H:i', strtotime($s->getCreatedAt()))
            ];
        }, GameScore::getTodayTop(10));

        $player = [];
        if (!empty($token)) {
            $player = array_map(function ($s) {
                return [
                    'name' => $s->getPlayerName(),
                    'score' => $s->getScore(),
                    'lines' => $s->getLines(),
                    'level' => $s->getLevel(),
                    'palette' => $s->getPalette(),
                    'date' => date('d/m/Y H:i', strtotime($s->getCreatedAt()))
                ];
            }, GameScore::getPlayerScores($token, 5));
        }

        echo json_encode([
            'ok' => true,
            'top' => $top,
            'today' => $today,
            'player' => $player
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * API REST JSON : Enregistrement d'un score avec contrôles d'intégrité
     */
    public function apiSaveScore()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'error' => 'Method Not Allowed']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: [];

        $rawName = trim((string) ($input['name'] ?? ''));
        $name = mb_strtoupper(preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $rawName));
        $name = mb_substr($name, 0, 12);
        if ($name === '') {
            $name = 'ANON';
        }

        $score = (int) ($input['score'] ?? 0);
        $lines = (int) ($input['lines'] ?? 0);
        $level = (int) ($input['level'] ?? 0);
        $palette = in_array($input['palette'] ?? '', ['gb_classic', 'gbc_color', 'gb_pocket']) 
            ? $input['palette'] 
            : 'gbc_color';

        $token = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) ($input['player_token'] ?? ($input['token'] ?? '')));
        if (empty($token)) {
            $token = bin2hex(random_bytes(16));
        }

        // Garde-fous d'intégrité anti-triche :
        // 1. Plafonds standard Game Boy Tetris
        if ($score <= 0 || $score > 999999 || $lines < 0 || $lines > 999 || $level < 0 || $level > 30) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Score hors limites réalistes']);
            exit;
        }

        // 2. Cohérence lignes / score : un Tetris à 100 000 pts avec 0 ligne est impossible
        if ($lines === 0 && $score > 300) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Incohérence lignes / score']);
            exit;
        }

        try {
            $gameScore = new GameScore([
                'player_token' => $token,
                'player_name' => $name,
                'score' => $score,
                'lines' => $lines,
                'level' => $level,
                'palette' => $palette,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $gameScore->save();

            // Vérifier si c'est un record personnel
            $playerScores = GameScore::getPlayerScores($token, 10);
            $isBest = (!empty($playerScores) && $playerScores[0]->getScore() === $score);

            echo json_encode([
                'ok' => true,
                'token' => $token,
                'is_personal_best' => $isBest,
                'score' => $score,
                'name' => $name
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Erreur lors de la sauvegarde']);
            exit;
        }
    }
}

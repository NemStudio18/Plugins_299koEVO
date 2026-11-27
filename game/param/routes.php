<?php

defined('ROOT') or exit('No direct script access allowed');

$router = \Core\Router\Router::getInstance();

$router->map('GET', '/game[/?]', 'Game\\Controllers\\GameController#play', 'game-play');
$router->map('GET', '/game/api/scores[/?]', 'Game\\Controllers\\GameController#apiScores', 'game-api-scores');
$router->map('POST', '/game/api/score[/?]', 'Game\\Controllers\\GameController#apiSaveScore', 'game-api-save-score');

$router->map('GET', '/admin/game[/?]', 'Game\\Controllers\\GameAdminController#index', 'admin-game-index');
$router->map('POST', '/admin/game/config[/?]', 'Game\\Controllers\\GameAdminController#saveConfig', 'admin-game-save-config');
$router->map('GET', '/admin/game/delete/[i:id]/[a:token]', 'Game\\Controllers\\GameAdminController#deleteScore', 'admin-game-delete-score');
$router->map('GET', '/admin/game/reset/[a:token]', 'Game\\Controllers\\GameAdminController#resetScores', 'admin-game-reset-scores');

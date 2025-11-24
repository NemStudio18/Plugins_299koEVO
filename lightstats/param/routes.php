<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('No direct script access allowed');

$router = \Core\Router\Router::getInstance();

$router->map('GET|POST', '/admin/lightstats[/?]', 'LightStats\Controllers\LightStatsAdminController#home', 'admin-lightstats-home');

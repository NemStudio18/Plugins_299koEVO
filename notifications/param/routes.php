<?php

defined('ROOT') or exit('Access denied!');

$router = \Core\Router\Router::getInstance();

// Routes publiques
$router->map('GET', '/notifications[/?]', 'Notifications\Controllers\NotificationsPublicController#list', 'notifications-list');
$router->map('GET', '/notifications/view/[*:id][/?]', 'Notifications\Controllers\NotificationsPublicController#view', 'notifications-view');
$router->map('GET', '/notifications/[*:id][/?]', 'Notifications\Controllers\NotificationsPublicController#view', 'notifications-view-alias');
$router->map('POST', '/notifications/mark-read/[*:id][/?]', 'Notifications\Controllers\NotificationsPublicController#markRead', 'notifications-mark-read');
$router->map('POST', '/notifications/mark-all-read[/?]', 'Notifications\Controllers\NotificationsPublicController#markAllAsRead', 'notifications-mark-all-read');

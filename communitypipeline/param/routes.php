<?php

defined('ROOT') or exit('No direct script access allowed');

$router = \Core\Router\Router::getInstance();

// Routes publiques
$router->map('GET', '/contribuer[/?]', 'CommunityPipelinePublicController#form', 'communitypipeline-form');
$router->map('POST', '/contribuer/submit', 'CommunityPipelinePublicController#submit', 'communitypipeline-submit');
$router->map('GET', '/contribuer/suivi/[*:submissionId]', 'CommunityPipelinePublicController#status', 'communitypipeline-status');
$router->map('GET', '/contribuer/api/status/[*:submissionId]', 'CommunityPipelinePublicController#apiStatus', 'communitypipeline-api-status');

// Routes administration
$router->map('GET', '/admin/communitypipeline[/?]', 'CommunityPipelineAdminController#list', 'admin-communitypipeline');
$router->map('GET', '/admin/communitypipeline/view/[*:submissionId]', 'CommunityPipelineAdminController#view', 'admin-communitypipeline-view');


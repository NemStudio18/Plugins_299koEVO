<?php

defined('ROOT') or exit('No direct script access allowed');

$router = \Core\Router\Router::getInstance();

// Admin routes for search-maps
$router->map('GET|HEAD', '/admin/searchmaps[/?]', 'SearchMaps\\Controllers\\SearchMapsAdminController#home', 'admin-SearchMaps-home');
$router->map('GET|HEAD', '/admin/SearchMaps[/?]', 'SearchMaps\\Controllers\\SearchMapsAdminController#home', 'admin-SearchMaps-home-alt');
$router->map('POST', '/admin/searchmaps/save[/?]', 'SearchMaps\\Controllers\\SearchMapsAdminController#save', 'admin-SearchMaps-save');
$router->map('POST', '/admin/searchmaps/poi/save[/?]', 'SearchMaps\\Controllers\\SearchMapsAdminController#savePoi', 'admin-SearchMaps-poi-save');
$router->map('POST', '/admin/searchmaps/poi/delete/[*:id][/?]', 'SearchMaps\\Controllers\\SearchMapsAdminController#deletePoi', 'admin-SearchMaps-poi-delete');

// Public API & views
$router->map('GET|HEAD', '/api/searchmaps/pois[/?]', 'SearchMaps\\Controllers\\SearchMapsPublicController#apiPois', 'api-searchmaps-pois');
$router->map('GET|HEAD', '/carte[/?]', 'SearchMaps\\Controllers\\SearchMapsPublicController#publicMap', 'searchmaps-public-map');
$router->map('GET|HEAD', '/searchmaps/static[/?]', 'SearchMaps\\Controllers\\AdminController#staticMap', 'searchmaps-static');

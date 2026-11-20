<?php

defined('ROOT') OR exit('Access denied!');

$router = \Core\Router\Router::getInstance();

// Routes publiques consultation & réservation
$router->map('GET', '/annonces[/?]', 'Annonces\\Controllers\\AnnoncesController#home', 'annonces-home');
$router->map('GET', '/annonces/[a:id]-[*:slug][/?]', 'Annonces\\Controllers\\AnnoncesController#view', 'annonces-view');
$router->map('POST', '/annonces/[a:id]/reserver[/?]', 'Annonces\\Controllers\\AnnoncesController#reserve', 'annonces-reserve');

// Routes publiques C2C : Dépôt & Espace Membre
$router->map('GET', '/annonces/deposer[/?]', 'Annonces\\Controllers\\AnnoncesController#deposer', 'annonces-deposer');
$router->map('POST', '/annonces/deposer/save[/?]', 'Annonces\\Controllers\\AnnoncesController#savePublic', 'annonces-save-public');
$router->map('GET', '/annonces/mes-annonces[/?]', 'Annonces\\Controllers\\AnnoncesController#mesAnnonces', 'annonces-mes-annonces');
$router->map('GET', '/annonces/modifier/[a:id][/?]', 'Annonces\\Controllers\\AnnoncesController#editPublic', 'annonces-edit-public');
$router->map('POST', '/annonces/modifier/[a:id]/save[/?]', 'Annonces\\Controllers\\AnnoncesController#savePublicEdit', 'annonces-save-public-edit');
$router->map('GET', '/annonces/statut/[a:id]/[a:status]/[a:token][/?]', 'Annonces\\Controllers\\AnnoncesController#changeStatusPublic', 'annonces-change-status-public');
$router->map('GET', '/annonces/supprimer/[a:id]/[a:token][/?]', 'Annonces\\Controllers\\AnnoncesController#deletePublic', 'annonces-delete-public');

// Routes administration
$router->map('GET', '/admin/annonces[/?]', 'Annonces\\Controllers\\AnnoncesAdminController#home', 'admin-annonces-home');
$router->map('GET', '/admin/annonces/edit[/?]', 'Annonces\\Controllers\\AnnoncesAdminController#edit', 'admin-annonces-edit');
$router->map('GET', '/admin/annonces/edit/[a:id][/?]', 'Annonces\\Controllers\\AnnoncesAdminController#editId', 'admin-annonces-edit-id');
$router->map('POST', '/admin/annonces/save[/?]', 'Annonces\\Controllers\\AnnoncesAdminController#save', 'admin-annonces-save');
$router->map('GET', '/admin/annonces/delete/[a:id]/[a:token][/?]', 'Annonces\\Controllers\\AnnoncesAdminController#delete', 'admin-annonces-delete');
$router->map('POST', '/admin/annonces/moderate/[a:id][/?]', 'Annonces\\Controllers\\AnnoncesAdminController#moderate', 'admin-annonces-moderate');
$router->map('POST', '/admin/annonces/reservation/[a:id]/status[/?]', 'Annonces\\Controllers\\AnnoncesAdminController#updateReservationStatus', 'admin-annonces-reservation-status');
$router->map('GET', '/admin/annonces/reservation/delete/[a:id]/[a:token][/?]', 'Annonces\\Controllers\\AnnoncesAdminController#deleteReservation', 'admin-annonces-reservation-delete');
$router->map('POST', '/admin/annonces/saveConf[/?]', 'Annonces\\Controllers\\AnnoncesAdminController#saveConf', 'admin-annonces-save-config');


<?php

/**
 * @copyright (C) 2025, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

$router = \Core\Router\Router::getInstance();

// Public
$router->map('GET', '/dons[/?]', 'DonController#home', 'dons-home');
$router->map('POST', '/dons/paypal/create', 'DonController#createPayPalPayment', 'dons-paypal-create');
$router->map('POST', '/dons/paypal/capture', 'DonController#capturePayPalPayment', 'dons-paypal-capture');
$router->map('POST', '/dons/stripe/create', 'DonController#createStripePayment', 'dons-stripe-create');
$router->map('GET', '/dons/stripe/success', 'DonController#stripeSuccess', 'dons-stripe-success');
$router->map('GET', '/dons/stripe/cancel', 'DonController#stripeCancel', 'dons-stripe-cancel');

// Admin
$router->map('GET', '/admin/dons[/?]', 'DonAdminController#list', 'dons-admin-list');
$router->map('GET', '/admin/dons/stats', 'DonAdminController#stats', 'dons-admin-stats');
$router->map('GET', '/admin/dons/params', 'DonAdminController#params', 'dons-admin-params');
$router->map('POST', '/admin/dons/save-params', 'DonAdminController#saveParams', 'dons-admin-save-params');




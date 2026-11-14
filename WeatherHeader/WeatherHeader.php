<?php

defined('ROOT') or exit('Access denied!');

use Core\Core;
use Template\Template;

/**
 * Injection des styles CSS dans <head>
 */
function weatherHeaderEndFrontHead($param = ''): string
{
    $output = (string)$param;
    $cssFile = PLUGINS . 'WeatherHeader/assets/css/weather-header.css';
    $v = file_exists($cssFile) ? filemtime($cssFile) : '2.0.0';
    $output .= '<link rel="stylesheet" href="/plugin/WeatherHeader/assets/css/weather-header.css?v=' . $v . '" type="text/css"/>' . "\n";
    return $output;
}

/**
 * Injection des scripts JS avant </body>
 */
function weatherHeaderEndFrontBody($param = ''): string
{
    $output = (string)$param;
    $jsFile = PLUGINS . 'WeatherHeader/assets/js/weather-header.js';
    $v = file_exists($jsFile) ? filemtime($jsFile) : '2.0.0';
    $output .= '<script src="/plugin/WeatherHeader/assets/js/weather-header.js?v=' . $v . '" defer></script>' . "\n";
    return $output;
}

/**
 * Rendu du bandeau d'en-tête météo vectoriel vivant
 */
function weatherHeaderRenderBanner($param = ''): string
{
    $tplPath = PLUGINS . 'WeatherHeader/template/header.tpl';
    if (!file_exists($tplPath)) {
        return (string)$param;
    }

    $core = Core::getInstance();
    $config = [];
    $configFile = PLUGINS . 'WeatherHeader/param/config.json';
    if (file_exists($configFile)) {
        $config = json_decode((string)@file_get_contents($configFile), true) ?: [];
    }

    $city = !empty($config['city']) ? $config['city'] : ($core->getConfigVal('siteCity') ?: 'Paris');
    $country = !empty($config['country']) ? $config['country'] : 'FR';
    $lat = !empty($config['latitude']) ? (float)$config['latitude'] : 48.8566;
    $lon = !empty($config['longitude']) ? (float)$config['longitude'] : 2.3522;

    $tpl = new Template($tplPath);
    $tpl->set('defaultCity', htmlspecialchars($city, ENT_QUOTES, 'UTF-8'));
    $tpl->set('defaultCountry', htmlspecialchars($country, ENT_QUOTES, 'UTF-8'));
    $tpl->set('latitude', $lat);
    $tpl->set('longitude', $lon);
    $tpl->set('showPicker', !empty($config['show_picker']));

    return (string)$param . $tpl->output();
}

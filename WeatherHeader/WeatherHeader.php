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
 * Rendu du bandeau d'en-tête météo vectoriel vivant.
 * Se greffe automatiquement sur afterHeader quel que soit le thème actif.
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

    // Affichage limité à la page d'accueil par défaut
    $homepageOnly = isset($config['homepage_only']) ? (bool)$config['homepage_only'] : true;
    if ($homepageOnly) {
        $pluginCalled = $core->getPluginToCall();
        $isHomepage = empty($pluginCalled) || $pluginCalled === 'page';
        $q = isset($_GET['q']) ? trim((string)$_GET['q'], '/') : '';
        if ($isHomepage && !empty($q) && $q !== 'home' && $q !== 'index') {
            $isHomepage = false;
        }
        if (!$isHomepage) {
            return (string)$param;
        }
    }

    // 1. Ville & coordonnées par défaut
    $city = !empty($config['city']) ? $config['city'] : '';
    $country = !empty($config['country']) ? $config['country'] : '';
    $lat = !empty($config['latitude']) ? (float)$config['latitude'] : 0.0;
    $lon = !empty($config['longitude']) ? (float)$config['longitude'] : 0.0;

    // 2. Synergie intelligente : si non renseigné dans WeatherHeader, récupérer depuis le plugin 'meteo'
    if (empty($city) || empty($lat) || empty($lon)) {
        $meteoFile = defined('DATA_PLUGIN') ? DATA_PLUGIN . 'meteo/config.json' : null;
        if ($meteoFile && file_exists($meteoFile)) {
            $meteoConf = json_decode((string)@file_get_contents($meteoFile), true) ?: [];
            if (!empty($meteoConf['defaultCity']) && empty($city)) {
                $city = $meteoConf['defaultCity'];
            }
            if (!empty($meteoConf['defaultCountry']) && empty($country)) {
                $country = $meteoConf['defaultCountry'];
            }
            if (!empty($meteoConf['latitude']) && empty($lat)) {
                $lat = (float)$meteoConf['latitude'];
            }
            if (!empty($meteoConf['longitude']) && empty($lon)) {
                $lon = (float)$meteoConf['longitude'];
            }
        }
    }

    // 3. Fallbacks globaux CMS
    if (empty($city)) {
        $city = $core->getConfigVal('siteCity') ?: 'Paris';
    }
    if (empty($country)) {
        $country = 'FR';
    }
    if (empty($lat) || empty($lon)) {
        $lat = 48.8566;
        $lon = 2.3522;
    }

    $tpl = new Template($tplPath);
    $tpl->set('defaultCity', htmlspecialchars($city, ENT_QUOTES, 'UTF-8'));
    $tpl->set('defaultCountry', htmlspecialchars($country, ENT_QUOTES, 'UTF-8'));
    $tpl->set('latitude', $lat);
    $tpl->set('longitude', $lon);
    $tpl->set('showPicker', !empty($config['show_picker']));

    return (string)$param . $tpl->output();
}

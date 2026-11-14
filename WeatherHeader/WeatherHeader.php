<?php

defined('ROOT') or exit('Access denied!');

use Core\Core;
use Template\Template;
use Core\Auth\CurrentUser;
use Core\Auth\UsersManager;

$GLOBALS['weatherHeaderRendered'] = false;

/**
 * Injection des styles CSS dans <head>
 */
function weatherHeaderEndFrontHead($param = ''): string
{
    $output = (string)$param;
    $cssFile = PLUGINS . 'WeatherHeader/assets/css/weather-header.css';
    $v = file_exists($cssFile) ? filemtime($cssFile) : '2.1.0';
    $output .= '<link rel="stylesheet" href="/plugin/WeatherHeader/assets/css/weather-header.css?v=' . $v . '" type="text/css"/>' . "\n";
    return $output;
}

/**
 * Détermine si la page en cours est la page d'accueil (homepage)
 */
function weatherHeaderIsHomepage(): bool
{
    $core = Core::getInstance();
    $pluginCalled = $core->getPluginToCall();
    if (!empty($pluginCalled) && $pluginCalled !== 'page') {
        return false;
    }
    $q = isset($_GET['q']) ? trim((string)$_GET['q'], '/') : '';
    if (!empty($q) && $q !== 'home' && $q !== 'index') {
        return false;
    }
    return true;
}

/**
 * Rendu du bandeau d'en-tête météo vectoriel vivant.
 */
function weatherHeaderRenderBanner($param = ''): string
{
    $core = Core::getInstance();

    // Priorité à la configuration sauvegardée par l'admin dans DATA_PLUGIN
    $config = [];
    $dataConfigFile = defined('DATA_PLUGIN') ? DATA_PLUGIN . 'WeatherHeader/config.json' : null;
    $defaultConfigFile = PLUGINS . 'WeatherHeader/param/config.json';

    if ($dataConfigFile && file_exists($dataConfigFile)) {
        $config = json_decode((string)@file_get_contents($dataConfigFile), true) ?: [];
    } elseif (file_exists($defaultConfigFile)) {
        $config = json_decode((string)@file_get_contents($defaultConfigFile), true) ?: [];
    }

    // Vérifier si le plugin est activé (1 ou absent = actif)
    if (isset($config['activate']) && !$config['activate']) {
        return (string)$param;
    }

    // Affichage limité à la page d'accueil par défaut
    $homepageOnly = isset($config['homepage_only']) ? (bool)$config['homepage_only'] : true;
    if ($homepageOnly && !weatherHeaderIsHomepage()) {
        return (string)$param;
    }

    $tplPath = PLUGINS . 'WeatherHeader/template/header.tpl';
    if (!file_exists($tplPath)) {
        return (string)$param;
    }

    $GLOBALS['weatherHeaderRendered'] = true;

    // 1. Détection du plugin meteo
    $hasMeteoPlugin = false;
    $meteoFile = defined('DATA_PLUGIN') ? DATA_PLUGIN . 'meteo/config.json' : null;
    $city = !empty($config['city']) ? $config['city'] : '';
    $country = !empty($config['country']) ? $config['country'] : '';
    $lat = !empty($config['latitude']) ? (float)$config['latitude'] : 0.0;
    $lon = !empty($config['longitude']) ? (float)$config['longitude'] : 0.0;

    if ($meteoFile && file_exists($meteoFile)) {
        $meteoConf = json_decode((string)@file_get_contents($meteoFile), true) ?: [];
        if (!isset($meteoConf['activate']) || $meteoConf['activate']) {
            $hasMeteoPlugin = true;
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

    // Si le plugin meteo est absent, WeatherHeader demandera de lui-même la géolocalisation
    $autoGeolocate = !$hasMeteoPlugin;

    // Fallbacks globaux CMS (Bourges préfecture du Cher par défaut)
    if (empty($city) || $city === 'Paris') {
        $city = $core->getConfigVal('siteCity') ?: 'Bourges';
    }
    if (empty($country)) {
        $country = 'FR';
    }
    if (empty($lat) || empty($lon) || ($lat == 48.8566 && $lon == 2.3522)) {
        $lat = 47.0810;
        $lon = 2.3988;
    }

    $tpl = new Template($tplPath);
    $tpl->set('defaultCity', htmlspecialchars($city, ENT_QUOTES, 'UTF-8'));
    $tpl->set('defaultCountry', htmlspecialchars($country, ENT_QUOTES, 'UTF-8'));
    $tpl->set('latitude', $lat);
    $tpl->set('longitude', $lon);
    $tpl->set('autoGeolocate', $autoGeolocate);

    return (string)$param . $tpl->output();
}

/**
 * Injection des scripts JS avant </body> et montage fallback si le thème n'a pas de hook direct
 */
function weatherHeaderEndFrontBody($param = ''): string
{
    $output = (string)$param;

    if (empty($GLOBALS['weatherHeaderRendered'])) {
        $bannerHtml = weatherHeaderRenderBanner('');
        if (!empty($bannerHtml)) {
            $output .= '<div id="weather_header_fallback_host" style="display:none;" aria-hidden="true">' . $bannerHtml . '</div>' . "\n";
        }
    }

    $jsFile = PLUGINS . 'WeatherHeader/assets/js/weather-header.js';
    $v = file_exists($jsFile) ? filemtime($jsFile) : '2.1.0';
    $output .= '<script src="/plugin/WeatherHeader/assets/js/weather-header.js?v=' . $v . '" defer></script>' . "\n";
    return $output;
}

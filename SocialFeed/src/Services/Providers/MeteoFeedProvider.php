<?php
namespace SocialFeed\Services\Providers;

use Core\Plugin\PluginsManager;

class MeteoFeedProvider implements FeedProviderInterface {

    public function getId(): string {
        return 'meteo';
    }

    public function getTitle(): string {
        return 'Météo';
    }

    public function getIcon(): string {
        return 'fa-solid fa-cloud-sun';
    }

    public function isAvailable(): bool {
        return PluginsManager::isActivePlugin('meteo');
    }

    public function fetchItems(?int $currentUserId, int $page = 1, int $limit = 20, array $options = []): array {
        if (!$this->isAvailable() || $page > 1) {
            // La météo ne s'affiche qu'une fois en tête de fil
            return [];
        }

        // Tenter de charger le cache météo existant
        $cacheFile = (defined('DATA') ? DATA : dirname(__DIR__, 5) . '/data/') . 'plugin/meteo/cache.json';
        $meteoData = file_exists($cacheFile) ? json_decode(@file_get_contents($cacheFile), true) : null;

        $temp = $meteoData['current']['temp'] ?? 15;
        $desc = $meteoData['current']['description'] ?? 'Ensoleillé';
        $city = $meteoData['city'] ?? 'Bourges';

        return [[
            'type' => 'meteo',
            'id' => 'meteo-today',
            'title' => "Météo du jour à {$city}",
            'content' => "Actuellement {$temp}°C, conditions : {$desc}.",
            'author_name' => 'Service Météo',
            'author_avatar' => null,
            'author_url' => '/meteo',
            'media_url' => null,
            'permalink' => '/meteo',
            'created_at' => date('Y-m-d H:i:s'),
            'timestamp' => time() + 3600, // Légèrement prioritaire en haut de flux
            'likes_count' => 0,
            'comments_count' => 0,
            'shares_count' => 0,
            'has_liked' => false,
            'pinned' => true,
            'meta' => [
                'temp' => $temp,
                'desc' => $desc,
                'city' => $city
            ]
        ]];
    }
}

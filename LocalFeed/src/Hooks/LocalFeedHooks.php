<?php

namespace LocalFeed\Hooks;

defined('ROOT') or exit('Access denied!');

class LocalFeedHooks
{
    /**
     * Hook endMainNavigation — Ajoute le lien "Vie locale" dans le menu principal
     */
    public static function onEndMainNavigation(string $param = ''): string
    {
        $link = '<li class="nav-item nav-local-feed">'
              . '<a href="/mur" class="nav-link nav-link-feed" title="Fil d\'actualité et vie locale en direct">'
              . 'Vie locale'
              . '</a></li>';
        
        return $param . $link;
    }

    /**
     * Hook poi:after-delete — Nettoie en cascade applicative toutes les publications,
     * réactions, partages et commentaires associés à un POI supprimé.
     * Idempotent : ne lève aucune exception si le POI n'a pas de publications.
     */
    public static function onPoiDeleted(mixed $param = null): void
    {
        $poiId = 0;
        if (is_int($param) || is_numeric($param)) {
            $poiId = (int)$param;
        } elseif (is_array($param) && isset($param['id'])) {
            $poiId = (int)$param['id'];
        } elseif (is_object($param) && isset($param->id)) {
            $poiId = (int)$param->id;
        }

        if ($poiId > 0) {
            try {
                \LocalFeed\Services\LocalFeedService::getInstance()->deletePostsByPoi($poiId);
            } catch (\Throwable $e) {
                error_log("LocalFeed onPoiDeleted error: " . $e->getMessage());
            }
        }
    }
}

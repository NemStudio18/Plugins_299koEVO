<?php
namespace SocialFeed\Hooks;

use SocialFeed\Services\SocialFeedService;

class SocialFeedHooks {

    /**
     * Ajoute un lien vers le Hub Social dans la navigation principale
     */
    public static function onEndMainNavigation(): string {
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';
        $isActive = (strpos($currentUri, '/social') === 0 || strpos($currentUri, '/feed') === 0) ? ' active' : '';

        return '<li class="nav-item' . $isActive . '"><a class="nav-link' . $isActive . '" href="/social" title="Fil d\'actualités et communauté"><i class="fa-solid fa-users"></i> <span>Communauté</span></a></li>';
    }

    /**
     * Nettoie les données d'un utilisateur supprimé
     */
    public static function onUserDeleted($userId): void {
        if (!empty($userId)) {
            SocialFeedService::getInstance()->onUserDeleted((int)$userId);
        }
    }
}

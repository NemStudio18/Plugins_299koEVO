<?php
namespace SocialFeed\Hooks;

use SocialFeed\Services\SocialFeedService;

class SocialFeedHooks {

    /**
     * Ajoute les liens vers le Hub Social et le Profil membre dans la navigation principale
     */
    public static function onEndMainNavigation(): string {
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';
        $isFeedActive = (strpos($currentUri, '/social') === 0 && strpos($currentUri, '/social/user') === false) ? ' active' : '';

        $html = '<li class="nav-item' . $isFeedActive . '"><a class="nav-link' . $isFeedActive . '" href="/social" title="Fil d\'actualités et communauté"><i class="fa-solid fa-comments"></i> <span>Communauté</span></a></li>';

        $user = \Core\Auth\UsersManager::getCurrentUser();
        if ($user && !empty($user->id)) {
            $isProfileActive = (strpos($currentUri, '/social/user/' . (int)$user->id) === 0) ? ' active' : '';
            $html .= '<li class="nav-item' . $isProfileActive . '"><a class="nav-link' . $isProfileActive . '" href="/social/user/' . (int)$user->id . '" title="Mon Mur & Profil Public"><i class="fa-solid fa-user"></i> <span>Mon Profil</span></a></li>';
        }

        return $html;
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

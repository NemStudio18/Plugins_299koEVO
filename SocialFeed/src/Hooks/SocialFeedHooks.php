<?php
namespace SocialFeed\Hooks;

use SocialFeed\Services\SocialFeedService;

class SocialFeedHooks {

    /**
     * Injecte les assets CSS et JS dans le <head> sur toutes les pages sociales et profils
     */
    public static function onEndFrontHead(): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, '/social') === 0 || strpos($uri, '/profile') === 0) {
            return '<link rel="stylesheet" href="/plugin/SocialFeed/public/css/socialfeed.css?v=6" />' . "\n" .
                   '<script src="/plugin/SocialFeed/public/js/socialfeed.js?v=6" defer></script>' . "\n";
        }
        return '';
    }

    /**
     * Ajoute les liens vers le Hub Social et le Profil membre dans la navigation principale
     */
    public static function onEndMainNavigation(): string {
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';
        $isFeedActive = (strpos($currentUri, '/social') === 0 && strpos($currentUri, '/social/user') === false && strpos($currentUri, '/social/group') === false) ? ' active' : '';

        $html = '<li class="nav-item' . $isFeedActive . '"><a class="nav-link' . $isFeedActive . '" href="/social" title="Fil d\'actualités et communauté"><i class="fa-solid fa-comments"></i> <span>Communauté</span></a></li>';

        $isGroupsActive = (strpos($currentUri, '/social/group') === 0) ? ' active' : '';
        $html .= '<li class="nav-item' . $isGroupsActive . '"><a class="nav-link' . $isGroupsActive . '" href="/social/groups" title="Groupes et Pages"><i class="fa-solid fa-users-rectangle"></i> <span>Groupes & Pages</span></a></li>';

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

    /**
     * Hook: user:profile-tabs
     * Ajoute un bouton d'accès direct vers le Mur Social public dans les onglets du profil
     */
    public static function userProfileTabs($user): string {
        $id = $user->attributes['id'] ?? ($user->id ?? null);
        if (!empty($id)) {
            return '<a href="/social/user/' . (int)$id . '" class="profile-tab-button" role="tab" title="Consulter mon mur public social"><i class="fa-solid fa-stream"></i> <span>Mon mur public</span></a>';
        }
        return '';
    }

    /**
     * Hook: user:profile-edit-dynamic-fields
     * Injecte les champs d'édition du profil social (Bio, Ville, Site, Bannière) dans /profile/edit
     */
    public static function userProfileEditDynamicFields($user): string {
        $attrs = $user->attributes ?? [];
        $bio = htmlspecialchars($attrs['bio'] ?? ($attrs['social_bio'] ?? ''));
        $location = htmlspecialchars($attrs['location'] ?? ($attrs['social_location'] ?? ''));
        $website = htmlspecialchars($attrs['website'] ?? ($attrs['social_website'] ?? ''));
        $cover = htmlspecialchars($attrs['cover_image'] ?? ($attrs['social_cover'] ?? ''));
        $id = (int)($attrs['id'] ?? ($user->id ?? 1));

        $html = '<div class="social-profile-fields-section" style="margin-top: 2rem; border-top: 1px solid #e2e8f0; padding-top: 1.5rem;">';
        $html .= '<h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem; font-size: 1.15rem; color: #1e293b;"><i class="fa-solid fa-users" style="color: #1d9bf0;"></i> Profil Public Social</h3>';
        $html .= '<p style="color: #64748b; font-size: 0.88rem; margin-bottom: 1.25rem;">Personnalisez votre mur public visible par la communauté (<a href="/social/user/' . $id . '" target="_blank" style="color: #1d9bf0; text-decoration: underline;">voir mon mur public</a>).</p>';

        $html .= '<div class="form-group" style="margin-bottom: 1.25rem;">';
        $html .= '<label for="social_bio" style="display: block; font-weight: 600; margin-bottom: 0.35rem;"><i class="fa-solid fa-quote-left" style="color: #1d9bf0;"></i> Bio / Description</label>';
        $html .= '<textarea id="social_bio" name="social_bio" rows="3" class="form-control" placeholder="Parlez de vous, de vos passions..." maxlength="300" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.6rem; font-family: inherit;">' . $bio . '</textarea>';
        $html .= '<small style="color: #94a3b8; font-size: 0.8rem;">Visible sur votre profil public. 300 caractères max.</small>';
        $html .= '</div>';

        $html .= '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">';
        $html .= '<div class="form-group">';
        $html .= '<label for="social_location" style="display: block; font-weight: 600; margin-bottom: 0.35rem;"><i class="fa-solid fa-location-dot" style="color: #ef4444;"></i> Ville / Région</label>';
        $html .= '<input type="text" id="social_location" name="social_location" class="form-control" value="' . $location . '" placeholder="Ex: Bourges, Cher" maxlength="80" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.6rem;">';
        $html .= '</div>';

        $html .= '<div class="form-group">';
        $html .= '<label for="social_website" style="display: block; font-weight: 600; margin-bottom: 0.35rem;"><i class="fa-solid fa-globe" style="color: #10b981;"></i> Site Web ou Réseau</label>';
        $html .= '<input type="url" id="social_website" name="social_website" class="form-control" value="' . $website . '" placeholder="https://monsite.fr" maxlength="150" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.6rem;">';
        $html .= '</div>';
        $html .= '</div>';

        $html .= '<div class="form-group" style="margin-bottom: 1rem;">';
        $html .= '<label for="social_cover" style="display: block; font-weight: 600; margin-bottom: 0.35rem;"><i class="fa-solid fa-image" style="color: #8b5cf6;"></i> Image de couverture (Bannière)</label>';
        $html .= '<input type="url" id="social_cover" name="social_cover" class="form-control" value="' . $cover . '" placeholder="https://exemple.com/banniere.jpg" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.6rem;">';
        $html .= '<small style="color: #94a3b8; font-size: 0.8rem;">URL de l\'image d\'en-tête de votre mur public (ratio 16:9 ou 3:1 conseillé).</small>';
        $html .= '</div>';

        $html .= '</div>';
        return $html;
    }

    /**
     * Hook: user:profile-save
     * Sauvegarde les champs sociaux lors de la mise à jour du profil utilisateur
     */
    public static function userProfileSave($user): void {
        if (!$user) return;

        if (isset($_POST['social_bio'])) {
            $user->attributes['bio'] = trim($_POST['social_bio']);
        }
        if (isset($_POST['social_location'])) {
            $user->attributes['location'] = trim($_POST['social_location']);
        }
        if (isset($_POST['social_website'])) {
            $user->attributes['website'] = trim($_POST['social_website']);
        }
        if (isset($_POST['social_cover'])) {
            $user->attributes['cover_image'] = trim($_POST['social_cover']);
        }
    }

    /**
     * Hook: user:profile-dynamic-fields
     * Affiche les informations sociales sur la page de consultation du profil privé (/profile)
     */
    public static function userProfileDynamicFields(array $fields): array {
        $user = \Core\Auth\UsersManager::getCurrentUser();
        if ($user) {
            $attrs = $user->attributes ?? [];
            if (!empty($attrs['bio'])) {
                $fields[] = [
                    'label' => 'Bio',
                    'value' => htmlspecialchars($attrs['bio']),
                    'icon' => 'fa-solid fa-quote-left'
                ];
            }
            if (!empty($attrs['location'])) {
                $fields[] = [
                    'label' => 'Ville',
                    'value' => htmlspecialchars($attrs['location']),
                    'icon' => 'fa-solid fa-location-dot'
                ];
            }
            if (!empty($attrs['website'])) {
                $cleanUrl = htmlspecialchars(preg_replace('#^https?://#', '', $attrs['website']));
                $fields[] = [
                    'label' => 'Site Web',
                    'value' => '<a href="' . htmlspecialchars($attrs['website']) . '" target="_blank" rel="noopener">' . $cleanUrl . '</a>',
                    'icon' => 'fa-solid fa-globe'
                ];
            }
        }
        return $fields;
    }

    /**
     * Hook: user:profile-stats
     * Injecte les statistiques sociales (publications, abonnés) dans l'onglet des contributions
     */
    public static function userProfileStats(array $stats): array {
        $user = \Core\Auth\UsersManager::getCurrentUser();
        $userId = $user ? (int)($user->attributes['id'] ?? ($user->id ?? 0)) : 0;
        if ($userId > 0) {
            try {
                $db = \Core\Database::getInstance();
                $pCount = $db->query("SELECT COUNT(*) as cnt FROM social_posts WHERE user_id = :uid AND status = 'published'", [':uid' => $userId]);
                $posts = !empty($pCount) ? (int)((array)$pCount[0])['cnt'] : 0;

                $flCount = $db->query("SELECT COUNT(*) as cnt FROM social_follows WHERE following_id = :uid", [':uid' => $userId]);
                $followers = !empty($flCount) ? (int)((array)$flCount[0])['cnt'] : 0;

                $stats[] = [
                    'label' => 'Publications sociales',
                    'value' => $posts,
                    'color' => '#1d9bf0',
                    'icon' => 'fa-solid fa-feather-pointed'
                ];
                $stats[] = [
                    'label' => 'Abonnés communauté',
                    'value' => $followers,
                    'color' => '#10b981',
                    'icon' => 'fa-solid fa-users'
                ];
            } catch (\Exception $e) {}
        }
        return $stats;
    }
}

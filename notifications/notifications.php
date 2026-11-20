<?php

namespace Notifications {

    use Core\Plugin\PluginsManager;
    use Core\Core;

    defined('ROOT') or exit('No direct script access allowed');

    class Hooks
    {
        public static function install(): void
        {
            // La création de la structure est gérée par Notification::query() (via JsonActiveRecord et FallbackStorage)
            // lors de la première utilisation ou du scan.
            \Core\Core::getInstance()->getLogger()->log(\Core\Logger::LEVEL_INFO, "[notifications::install] Notification entity will handle schema.");
        }
    }
}

namespace {

    function notificationsInstall(): void
    {
        \Notifications\Hooks::install();
    }

    function notificationsBeforeRunPlugin(): void
    {
        // Charger services
        if (is_dir(PLUGINS . 'notifications/services')) {
            foreach (glob(PLUGINS . 'notifications/services/*.php') as $file) {
                require_once $file;
            }
        }

        if (is_dir(PLUGINS . 'notifications/entities')) {
            foreach (glob(PLUGINS . 'notifications/entities/*.php') as $file) {
                require_once $file;
            }
        }

        // Hooks are now declared in param/hooks.json
    }

    function notificationsEndFrontHead()
    {
        echo '<link rel="stylesheet" href="' . \Utils\Util::urlBuild(PLUGINS . 'notifications/template/public.css') . '">';
    }

    /**
     * Hook: user:profile-settings
     */
    function notificationsUserProfileSettings($user): string
    {
        $allowNotifications = (bool) ($user->attributes['allow_notifications'] ?? true);
        $notifPush = (bool) ($user->attributes['notif_push'] ?? false);
        $notifEmail = (bool) ($user->attributes['notif_email'] ?? false);
        
        $core = \Core\Core::getInstance();
        $pwaEnabled = \Core\Plugin\PluginsManager::isActivePlugin('pwa');
        
        $html = '<div class="notification-settings-group">';
        
        // Main Toggle
        $html .= '
        <div class="setting-item">
            <label class="toggle-switch">
                <input type="checkbox" class="toggle-input" data-setting="allow_notifications" ' . ($allowNotifications ? 'checked' : '') . '>
                <span class="toggle-slider"></span>
            </label>
            <div class="setting-info">
                <span class="setting-label">Recevoir les notifications</span>
                <span class="setting-description">Activez pour recevoir des alertes sur votre compte.</span>
            </div>
        </div>';

        if ($allowNotifications) {
            $html .= '<div id="notif-prefs-container" style="margin-top: 1.5rem;">';
            
            // Channels Flex (previously grid)
            $html .= '<div class="settings-list">';
            
            if ($pwaEnabled) {
                $html .= '
                <div class="setting-item">
                    <label class="toggle-switch">
                        <input type="checkbox" class="toggle-input" data-setting="notif_push" ' . ($notifPush ? 'checked' : '') . '>
                        <span class="toggle-slider"></span>
                    </label>
                    <div class="setting-info">
                        <span class="setting-label">Notifications Push</span>
                        <span class="setting-description">Mobile et Desktop (PWA)</span>
                    </div>
                </div>';
            }

            $html .= '
            <div class="setting-item">
                <label class="toggle-switch">
                    <input type="checkbox" class="toggle-input" data-setting="notif_email" ' . ($notifEmail ? 'checked' : '') . '>
                    <span class="toggle-slider"></span>
                </label>
                <div class="setting-info">
                    <span class="setting-label">Notifications par Email</span>
                    <span class="setting-description">Rapport par mail.</span>
                </div>
            </div>';
            
            $html .= '</div>'; // End settings-list

            // Specific Subscriptions Grid
            $typesFile = PLUGINS . 'notifications/notification_types.json';
            if (file_exists($typesFile)) {
                $types = \Utils\Util::readJsonFile($typesFile) ?: [];
                $html .= '<div class="notif-types-group" style="margin-top: 2rem;">';
                $html .= '<h3 style="font-size: 0.9rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 1rem; border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">Vos abonnements spécifiques</h3>';
                $html .= '<div class="notif-types-grid">';
                
                foreach ($types as $type) {
                    if (!($type['can_disable'] ?? true)) continue;
                    
                    $prefKey = 'notif_type_' . str_replace('.', '_', $type['type']);
                    $enabled = (bool) ($user->attributes[$prefKey] ?? ($type['default_enabled'] ?? true));
                    
                    $html .= '
                    <div class="notif-type-card">
                        <label class="toggle-switch">
                            <input type="checkbox" class="toggle-input" data-setting="' . $prefKey . '" ' . ($enabled ? 'checked' : '') . '>
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="toggle-label">' . ($type['description'] ?? $type['type']) . '</span>
                    </div>';
                }
                $html .= '</div>'; // End notif-types-grid
                $html .= '</div>'; // End notif-types-group
            }
            
            $html .= '</div>'; // End notif-prefs-container
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Hook: user:profile-allowed-settings
     */
    function notificationsUserProfileAllowedSettings(array $allowed): array
    {
        $allowed[] = 'allow_notifications';
        $allowed[] = 'notif_push';
        $allowed[] = 'notif_email';

        // Ajouter les types dynamiques depuis le fichier JSON
        $typesFile = PLUGINS . 'notifications/notification_types.json';
        if (file_exists($typesFile)) {
            $types = \Utils\Util::readJsonFile($typesFile) ?: [];
            foreach ($types as $type) {
                $allowed[] = 'notif_type_' . str_replace('.', '_', $type['type']);
            }
        }

        return $allowed;
    }

    function notificationsEndAdminHead(): void
    {
        echo '<link rel="stylesheet" href="' . \Utils\Util::urlBuild(PLUGINS . 'notifications/template/public.css') . '">';
    }

    /**
     * Hook: adminTopbarBadges
     * Cloche de notifications intégrée à la barre supérieure de l'administration refondue.
     */
    function notificationsAdminTopbarBadges(): void
    {
        $user = \Core\Auth\UsersManager::getCurrentUser();
        if (!$user) {
            return;
        }
        $evaluator = \Core\Navigation\AccessPolicyEvaluator::getInstance();
        $ctx = new \Core\Navigation\ConditionContext($user);
        if (!$evaluator->canAccess(new \Core\Navigation\GenericResource('notifications:access'), $ctx)) {
            return;
        }
        if (!($user->attributes['allow_notifications'] ?? true)) {
            return;
        }

        require_once PLUGINS . 'notifications/entities/NotificationManager.php';
        $manager = new \Notifications\Entities\NotificationManager();
        $userId = (int) ($user->id ?? ($user->attributes['id'] ?? 0));
        if ($userId <= 0) {
            return;
        }

        $unreadCount = $manager->getUnreadCount($userId);
        $items = $manager->getUserNotifications($userId, false, 8);
        $csrfToken = \Core\Security\Csrf::token();
        $router = \Core\Router\Router::getInstance();
        $siteUrl = rtrim((string) \Core\Core::getInstance()->getConfigVal('siteUrl'), '/');
        $allUrl = $router->generate('notifications-list');
        if ($allUrl === '#') {
            $allUrl = $siteUrl . '/notifications';
        }
        $prefsUrl = $router->generate('profile');
        $prefsUrl = ($prefsUrl !== '#' ? $prefsUrl : $siteUrl . '/users/profile') . '?tab=notifications';

        $badgeClass = $unreadCount > 0 ? ' has-unread' : '';
        echo '<div class="adm-notif-wrap adm-has-drop" id="admNotifWrap">';
        echo '<button type="button" class="adm-drop-btn adm-notif-btn' . $badgeClass . '" aria-expanded="false" aria-label="Notifications" title="Notifications">';
        echo '<i class="fa-solid fa-bell" aria-hidden="true"></i>';
        if ($unreadCount > 0) {
            echo '<span class="adm-notif-count" id="admNotifCount">' . ($unreadCount > 99 ? '99+' : (string) $unreadCount) . '</span>';
        }
        echo '</button>';

        echo '<div class="adm-drop-menu adm-drop-menu-right adm-notif-menu">';
        echo '<div class="adm-notif-head">';
        echo '<strong>Notifications</strong>';
        if ($unreadCount > 0) {
            echo '<button type="button" class="adm-notif-markall" data-token="' . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . '">Tout marquer lu</button>';
        }
        echo '</div>';

        echo '<div class="adm-notif-list">';
        if (empty($items)) {
            echo '<div class="adm-notif-empty"><i class="fa-regular fa-bell-slash"></i> Aucune notification récente.</div>';
        } else {
            foreach ($items as $notif) {
                $nid = (string) ($notif['id'] ?? '');
                $unread = empty($notif['is_read']);
                $title = (string) ($notif['title'] ?? 'Notification');
                $src = (string) ($notif['source_plugin'] ?? 'core');
                $url = (string) ($notif['link_url'] ?? '');
                if ($url === '') {
                    $url = $allUrl;
                } elseif (strpos($url, 'http') !== 0) {
                    $url = $siteUrl . '/' . ltrim($url, '/');
                }
                $createdAt = !empty($notif['created_at']) ? date('d/m H:i', strtotime((string) $notif['created_at'])) : '';

                $message = (string) ($notif['message'] ?? ($data['message'] ?? ''));
                $hasMessage = $message !== '' ? '1' : '0';
                $detailUrl = $siteUrl . '/notifications/view/' . rawurlencode($nid);

                echo '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" class="adm-notif-item' . ($unread ? ' is-unread' : '') . '" data-notif-id="' . htmlspecialchars($nid, ENT_QUOTES, 'UTF-8') . '" data-token="' . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . '" data-has-message="' . $hasMessage . '" data-title="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '" data-message="' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '" data-src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" data-date="' . htmlspecialchars($createdAt, ENT_QUOTES, 'UTF-8') . '" data-url="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" data-detail-url="' . htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8') . '">';
                echo '<span class="adm-notif-dot" aria-hidden="true"></span>';
                echo '<span class="adm-notif-body">';
                echo '<span class="adm-notif-title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</span>';
                if ($message !== '') {
                    $preview = mb_strlen($message) > 90 ? mb_substr($message, 0, 87) . '...' : $message;
                    echo '<span class="adm-notif-preview" style="display: block; font-size: 0.8rem; color: var(--adm-text-muted, #64748b); margin: 2px 0 4px; line-height: 1.3;">' . htmlspecialchars($preview, ENT_QUOTES, 'UTF-8') . '</span>';
                }
                echo '<span class="adm-notif-meta"><span>' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '</span>';
                if ($createdAt !== '') {
                    echo '<span>' . htmlspecialchars($createdAt, ENT_QUOTES, 'UTF-8') . '</span>';
                }
                echo '</span></span></a>';
            }
        }
        echo '</div>';

        echo '<div class="adm-notif-foot">';
        echo '<a href="' . htmlspecialchars($allUrl, ENT_QUOTES, 'UTF-8') . '"><i class="fa-solid fa-list"></i> Voir tout</a>';
        echo '<a href="' . htmlspecialchars($prefsUrl, ENT_QUOTES, 'UTF-8') . '"><i class="fa-solid fa-sliders"></i> Préférences</a>';
        echo '</div>';
        echo '</div></div>';

        // Modal for notification details in admin
        echo '<div id="admNotifModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(2px); z-index: 999999; align-items: center; justify-content: center; padding: 20px;">';
        echo '<div style="background: var(--section-bg-color, #ffffff); color: var(--font-color, #334155); border-radius: 12px; width: 100%; max-width: 620px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); display: flex; flex-direction: column; overflow: hidden; max-height: 85vh; border: 1px solid var(--adm-border, #e2e8f0);">';
        echo '<div style="padding: 18px 24px; border-bottom: 1px solid var(--adm-border, #e2e8f0); display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;">';
        echo '<div>';
        echo '<div style="display: flex; gap: 8px; align-items: center; margin-bottom: 6px;">';
        echo '<span id="admNotifModalSrc" style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; background: #e2e8f0; color: #475569; padding: 2px 8px; border-radius: 4px;"></span>';
        echo '<span id="admNotifModalDate" style="font-size: 0.8rem; color: #94a3b8;"></span>';
        echo '</div>';
        echo '<h3 id="admNotifModalTitle" style="margin: 0; font-size: 1.2rem; font-weight: 700; color: var(--adm-text-strong, #0f172a);"></h3>';
        echo '</div>';
        echo '<button type="button" id="admNotifModalClose" style="background: none; border: none; font-size: 1.6rem; cursor: pointer; color: #94a3b8; line-height: 1; padding: 0 4px;">&times;</button>';
        echo '</div>';
        echo '<div id="admNotifModalBody" style="padding: 20px 24px; overflow-y: auto; white-space: pre-wrap; line-height: 1.65; font-size: 0.95rem; color: var(--font-color, #334155); flex: 1;"></div>';
        echo '<div style="padding: 14px 24px; border-top: 1px solid var(--adm-border, #e2e8f0); background: var(--section-head-bg-color, #f8fafc); display: flex; justify-content: space-between; align-items: center;">';
        echo '<a id="admNotifModalDetailLink" href="#" style="font-size: 0.85rem; color: var(--primary-color, #1f62a9); text-decoration: underline;">Page de détail complète</a>';
        echo '<div style="display: flex; gap: 10px;">';
        echo '<a id="admNotifModalActionLink" href="#" style="display: none; padding: 7px 14px; background: var(--primary-color, #1f62a9); color: #fff; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.85rem;"><i class="fa-solid fa-arrow-up-right-from-square"></i> Ouvrir le lien</a>';
        echo '<button type="button" id="admNotifModalDismiss" style="padding: 7px 14px; background: #e2e8f0; color: #334155; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.85rem;">Fermer</button>';
        echo '</div>';
        echo '</div>';
        echo '</div></div>';

        echo '<script>
(function(){
    var wrap = document.getElementById("admNotifWrap");
    if (!wrap || wrap.dataset.bound) return;
    wrap.dataset.bound = "1";
    var modal = document.getElementById("admNotifModal");
    var modalClose = document.getElementById("admNotifModalClose");
    var modalDismiss = document.getElementById("admNotifModalDismiss");
    var modalTitle = document.getElementById("admNotifModalTitle");
    var modalBody = document.getElementById("admNotifModalBody");
    var modalSrc = document.getElementById("admNotifModalSrc");
    var modalDate = document.getElementById("admNotifModalDate");
    var modalAction = document.getElementById("admNotifModalActionLink");
    var modalDetail = document.getElementById("admNotifModalDetailLink");

    function closeModal() {
        if (modal) modal.style.display = "none";
    }
    if (modalClose) modalClose.addEventListener("click", closeModal);
    if (modalDismiss) modalDismiss.addEventListener("click", closeModal);
    if (modal) {
        modal.addEventListener("click", function(e) {
            if (e.target === modal) closeModal();
        });
    }

    var markAll = wrap.querySelector(".adm-notif-markall");
    if (markAll) {
        markAll.addEventListener("click", function(e){
            e.preventDefault();
            e.stopPropagation();
            var fd = new FormData();
            fd.append("token", markAll.dataset.token || "");
            fetch(' . json_encode($siteUrl . '/notifications/mark-all-read') . ', {
                method: "POST",
                headers: {"X-Requested-With": "XMLHttpRequest"},
                body: fd
            }).then(function(r){ return r.json(); }).then(function(res){
                if (res && res.success) {
                    wrap.querySelectorAll(".adm-notif-item.is-unread").forEach(function(el){ el.classList.remove("is-unread"); });
                    var cnt = document.getElementById("admNotifCount");
                    if (cnt) cnt.remove();
                    var btn = wrap.querySelector(".adm-notif-btn");
                    if (btn) btn.classList.remove("has-unread");
                    markAll.remove();
                }
            }).catch(function(){});
        });
    }
    wrap.querySelectorAll(".adm-notif-item").forEach(function(link){
        link.addEventListener("click", function(e){
            var nid = link.dataset.notifId;
            var hasMsg = link.dataset.hasMessage === "1";
            var msg = link.dataset.message || "";
            var title = link.dataset.title || "";
            var src = link.dataset.src || "";
            var date = link.dataset.date || "";
            var targetUrl = link.dataset.url || "";
            var detailUrl = link.dataset.detailUrl || "";

            // Marquer comme lue
            if (link.classList.contains("is-unread")) {
                link.classList.remove("is-unread");
                var cnt = document.getElementById("admNotifCount");
                if (cnt) {
                    var n = parseInt(cnt.textContent) - 1;
                    if (n <= 0) {
                        cnt.remove();
                        var btn = wrap.querySelector(".adm-notif-btn");
                        if (btn) btn.classList.remove("has-unread");
                    } else {
                        cnt.textContent = n;
                    }
                }
                var fd = new FormData();
                fd.append("id", nid);
                fd.append("token", link.dataset.token || "");
                if (navigator.sendBeacon) {
                    navigator.sendBeacon(' . json_encode($siteUrl . '/notifications/mark-read/') . ' + encodeURIComponent(nid), fd);
                } else {
                    fetch(' . json_encode($siteUrl . '/notifications/mark-read/') . ' + encodeURIComponent(nid), { method: "POST", body: fd, keepalive: true });
                }
            }

            // Si la notification contient un message détaillé, ouvrir la modale au lieu de naviguer bêtement
            if (hasMsg && modal) {
                e.preventDefault();
                e.stopPropagation();
                modalTitle.textContent = title;
                modalBody.textContent = msg;
                modalSrc.textContent = src;
                modalDate.textContent = date;
                modalDetail.href = detailUrl;

                var isCurrentOrAdmin = !targetUrl || targetUrl === "/admin" || targetUrl === "/admin/" || targetUrl === window.location.pathname;
                if (!isCurrentOrAdmin && targetUrl !== detailUrl) {
                    modalAction.href = targetUrl;
                    modalAction.style.display = "inline-flex";
                } else {
                    modalAction.style.display = "none";
                }

                modal.style.display = "flex";
            }
        });
    });
})();
</script>';
    }

    /**
     * Hook callback: Envoie une notification
     * 
     * @param array $data [
     *   'user_id' => int|array,  // ID utilisateur OU array d'IDs OU 0 pour broadcast
     *   'source_plugin' => string,
     *   'entity_type' => string,
     *   'entity_id' => int,
     *   'notification_type' => string,
     *   'title' => string,
     *   'message' => string,
     *   'link_url' => string
     * ]
     * @return bool
     */
    function notificationsSend(array $data): bool
    {
        try {
            if (!class_exists('\Notifications\Services\NotificationService')) {
                return false;
            }

            $logger = \Core\Core::getInstance()->getLogger();
            $service = new \Notifications\Services\NotificationService($logger);

            return $service->send($data);
        } catch (\Throwable $e) {
            \Core\Core::getInstance()->getLogger()->log(\Core\Logger::LEVEL_ERROR, "notificationsSend Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Hook: notifications:collect
     */
    function notificationsCollect(array $data): array
    {
        try {
            if (!class_exists('\Notifications\Services\NotificationService')) {
                return $data;
            }

            $logger = \Core\Core::getInstance()->getLogger();
            $service = new \Notifications\Services\NotificationService($logger);
            
            $userId = (int) ($data['userId'] ?? 0);
            if ($userId <= 0) return $data;

            $unreadCount = $service->getUnreadCount($userId);
            
            $data['totalUnread'] += $unreadCount;
            if ($unreadCount > 0) {
                $data['breakdown']['notifications'] = $unreadCount;
            }

            return $data;
        } catch (\Exception $e) {
            return $data;
        }
    }

    /**
     * Hook: user:avatar-menu
     */
    function notificationsUserAvatarMenu(array $data): array
    {
        $items = $data['items'] ?? [];
        $user = \Core\Auth\UsersManager::getCurrentUser();
        if (!$user) return $data;

        // Si l'utilisateur a désactivé les notifications, on ne montre rien
        if (!($user->attributes['allow_notifications'] ?? true)) {
            return $data;
        }

        $logger = \Core\Core::getInstance()->getLogger();
        $service = new \Notifications\Services\NotificationService($logger);
        $count = $service->getUnreadCount((int)$user->id);

        $router = \Core\Router\Router::getInstance();
        try {
            $url = $router->generate('notifications-list');
        } catch (\Exception $e) {
            $url = '/notifications';
        }

        $items[] = [
            'label' => 'Notifications',
            'url' => $router->generate('profile') . '?tab=notifications',
            'icon' => 'fa-solid fa-bell',
            'order' => 20,
            'badge' => $count > 0 ? $count : null
        ];

        $data['items'] = $items;
        return $data;
    }

    /**
     * Hook: user:avatar-overlay
     */
    function notificationsUserAvatarOverlay(): string
    {
        $evaluator = \Core\Navigation\AccessPolicyEvaluator::getInstance();
        $ctx = new \Core\Navigation\ConditionContext(\Core\Auth\UsersManager::getCurrentUser());
        if (!$evaluator->canAccess(new \Core\Navigation\GenericResource('notifications:access'), $ctx)) return '';
        
        $user = \Core\Auth\UsersManager::getCurrentUser();
        if (!($user->attributes['allow_notifications'] ?? true)) return '';

        $logger = \Core\Core::getInstance()->getLogger();
        $service = new \Notifications\Services\NotificationService($logger);
        $count = $service->getUnreadCount((int)$user->id);

        try {
            $url = \Core\Router\Router::getInstance()->generate('notifications-list');
        } catch (\Exception $e) {
            $url = '/notifications';
        }

        $html = '<div class="nav-avatar-overlay-bell">';
        $html .= '<i class="fa-solid fa-bell"></i>';
        if ($count > 0) {
            $html .= '<span class="nav-badge">' . $count . '</span>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Hook: user:profile-tabs
     */
    function notificationsUserProfileTabs($user): string
    {
        if (!is_object($user) || !($user->attributes['allow_notifications'] ?? true)) return '';

        $logger = \Core\Core::getInstance()->getLogger();
        $service = new \Notifications\Services\NotificationService($logger);
        $count = $service->getUnreadCount((int)$user->id);

        $badgeHtml = '';
        if ($count > 0) {
            $badgeHtml = ' <span class="nav-badge" style="margin-left: 5px;">' . $count . '</span>';
        }

        return '
        <button type="button" class="profile-tab-button" role="tab" aria-selected="false" aria-controls="tab-notifications" id="tab-button-notifications" data-tab="notifications">
            <i class="fa-solid fa-bell"></i> <span>Notifications' . $badgeHtml . '</span>
        </button>';
    }

    /**
     * Hook: user:profile-tabs-content
     */
    function notificationsUserProfileTabsContent($user): string
    {
        if (!is_object($user) || !($user->attributes['allow_notifications'] ?? true)) return '';

        require_once PLUGINS . 'notifications/entities/NotificationManager.php';
        $manager = new \Notifications\Entities\NotificationManager();
        $notifications = $manager->getUserNotifications($user->id);
        $unreadCount = $manager->getUnreadCount($user->id);

        $html = '<div class="profile-tab-content" id="tab-notifications" role="tabpanel" aria-labelledby="tab-button-notifications">';
        $html .= '<div class="profile-stats">';
        $html .= '<div class="notif-profile-head">';
        $html .= '<h2>Mes Notifications</h2>';
        
        if ($unreadCount > 0) {
            $html .= '<form method="POST" action="/notifications/mark-all-read" class="notif-profile-markall-form">';
            $html .= '<input type="hidden" name="token" value="' . \Core\Security\Csrf::token() . '" />';
            $html .= '<button type="submit" class="btn-mark-all notif-profile-markall-btn">Tout marquer comme lu</button>';
            $html .= '</form>';
        }
        $html .= '</div>';

        if (empty($notifications)) {
            $html .= '<div class="notif-empty-box">';
            $html .= '<i class="fa-solid fa-bell-slash"></i>';
            $html .= '<p>Aucune notification pour le moment.</p>';
            $html .= '</div>';
        } else {
            $html .= '<div class="notifications-list notif-profile-list">';
            foreach ($notifications as $notif) {
                $isUnread = empty($notif['is_read']);
                $unreadClass = $isUnread ? 'unread' : '';
                $data = $notif['data'] ?? [];
                $title = (string) ($notif['title'] ?? \Notifications\Entities\NotificationManager::formatTitle((string) ($notif['type'] ?? ''), is_array($data) ? $data : []));
                
                $html .= '<div class="notification notif-profile-card ' . $unreadClass . '" id="profile-notif-' . htmlspecialchars((string) $notif['id']) . '">';
                
                // Badge source
                $html .= '<div class="notif-source notif-profile-source">' . htmlspecialchars((string) ($notif['source_plugin'] ?? 'core')) . '</div>';
                
                $html .= '<div class="notif-content">';
                $html .= '<strong class="notif-profile-title"><a href="/notifications/view/' . htmlspecialchars((string) $notif['id']) . '" style="color: inherit; text-decoration: none;">' . htmlspecialchars($title) . '</a></strong>';
                
                $msg = (string) ($notif['message'] ?? ($data['message'] ?? ''));
                if ($msg !== '') {
                    $html .= '<div class="notif-profile-message" style="margin: 6px 0 10px 0; color: var(--font-color, #475569); font-size: 0.9em; line-height: 1.5; white-space: pre-wrap;">' . htmlspecialchars($msg) . '</div>';
                }
                
                $html .= '<div class="notif-profile-actions" style="display: flex; gap: 12px; align-items: center; margin-top: 6px;">';
                $html .= '<a href="/notifications/view/' . htmlspecialchars((string) $notif['id']) . '" class="notif-link notif-profile-link" style="font-weight: 600;">Voir le détail</a>';
                if (!empty($data['url'])) {
                    $u = (string) $data['url'];
                    $lbl = ($u === '/admin' || $u === '/admin/') ? 'Tableau de bord' : 'Ouvrir le lien';
                    $html .= '<a href="' . htmlspecialchars($u) . '" class="notif-external-link" style="font-size: 0.85em; color: #64748b; text-decoration: underline;">' . $lbl . ' <i class="fa-solid fa-arrow-up-right-from-square"></i></a>';
                }
                $html .= '</div>';
                $html .= '</div>';
                
                $html .= '<div class="notif-meta notif-profile-meta">';
                $createdAt = $notif['created_at'] ?? null;
                $html .= '<span class="notif-profile-date">' . ($createdAt ? date('d/m/Y H:i', strtotime($createdAt)) : '—') . '</span>';

                
                if ($isUnread) {
                    $html .= '<button class="mark-read notif-profile-read-btn" data-id="' . htmlspecialchars((string) $notif['id']) . '" data-token="' . \Core\Security\Csrf::token() . '">Lue</button>';
                }
                $html .= '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';
        }

        $html .= '</div></div>';

        // Add AJAX script for "Mark as read" buttons
        $html .= '<script>
        (function() {
            document.querySelectorAll("#tab-notifications .mark-read").forEach(btn => {
                btn.addEventListener("click", function(e) {
                    e.preventDefault();
                    const id = this.dataset.id;
                    const token = this.dataset.token;
                    const card = document.getElementById("profile-notif-" + id);
                    
                    if (card) {
                        card.classList.remove("unread");
                        card.style.opacity = "0.7";
                        this.remove();
                    }
                    
                    const formData = new FormData();
                    formData.append("id", id);
                    formData.append("token", token);
                    
                    fetch("/notifications/mark-read/" + id, {
                        method: "POST",
                        headers: { "X-Requested-With": "XMLHttpRequest" },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) console.error("Error marking notification:", data.error);
                    })
                    .catch(error => console.error("AJAX Error:", error));
                });
            });
        })();
        </script>';

        return $html;
    }

    /**
     * Hook: notifications:mark-as-read-external
     */
    function notificationsMarkAsReadExternal(array $data): void
    {
        $userId = $data['user_id'] ?? 0;
        $externalId = $data['external_id'] ?? '';
        
        if ($userId > 0 && !empty($externalId)) {
            require_once PLUGINS . 'notifications/entities/NotificationManager.php';
            $manager = new \Notifications\Entities\NotificationManager();
            $manager->markAsReadByExternalId((int)$userId, (string)$externalId);
        }
    }
}

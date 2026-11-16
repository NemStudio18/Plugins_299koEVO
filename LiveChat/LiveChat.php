<?php

namespace LiveChat {

    defined('ROOT') or exit('No direct script access allowed');

    require_once PLUGINS . 'LiveChat/services/LiveChatService.php';

    class Hooks
    {
        public static function install(): void
        {
            $dir = DATA_PLUGIN . 'LiveChat';
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            $configFile = $dir . '/config.json';
            if (!file_exists($configFile)) {
                $initial = [
                    'activate' => 1,
                    'priority' => 10,
                    'discord_webhook' => '',
                    'admin_name' => 'Équipe Support',
                    'welcome_message' => 'Bonjour ! Une question sur votre établissement ou sur le site ? Discutons en direct.',
                    'auto_notify_discord' => true
                ];
                @file_put_contents($configFile, json_encode($initial, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            }
            try {
                $service = new \LiveChat\Services\LiveChatService();
                $service->ensureSchema();
            } catch (\Throwable $e) {}
        }

        /**
         * Injecte le script et les styles du widget de tchat dans le pied de page public
         */
        public static function addFooter(): void
        {
            // Ne pas injecter dans l'administration
            if ((defined('ADMIN_MODE') && ADMIN_MODE) || (defined('IS_ADMIN') && IS_ADMIN)) {
                return;
            }

            $config = self::getConfig();
            if (empty($config['activate'])) {
                return;
            }

            $cssFile = PLUGINS . 'LiveChat/assets/livechat.css';
            $jsFile = PLUGINS . 'LiveChat/assets/livechat.js';
            $cssVer = file_exists($cssFile) ? filemtime($cssFile) : '1.0';
            $jsVer = file_exists($jsFile) ? filemtime($jsFile) : '1.0';

            echo '<link rel="stylesheet" href="/plugin/LiveChat/assets/livechat.css?v=' . $cssVer . '">';
            echo '<script src="/plugin/LiveChat/assets/livechat.js?v=' . $jsVer . '" defer></script>';
        }

        public static function getConfig(): array
        {
            $configFile = DATA_PLUGIN . 'LiveChat/config.json';
            if (file_exists($configFile)) {
                $data = json_decode(file_get_contents($configFile), true);
                if (is_array($data)) {
                    return $data;
                }
            }
            return [
                'activate' => 1,
                'discord_webhook' => '',
                'admin_name' => 'Équipe Support',
                'welcome_message' => 'Bonjour ! Une question sur votre établissement ou sur le site ? Discutons en direct.'
            ];
        }
    }
}

namespace {
    if (!function_exists('LiveChatInstall')) {
        function LiveChatInstall(): void
        {
            \LiveChat\Hooks::install();
        }
    }
    if (!function_exists('LiveChatUninstall')) {
        function LiveChatUninstall(): void
        {
        }
    }
}

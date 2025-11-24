<?php

namespace LightStats {

use Utils\Util;

defined('ROOT') or exit('No direct script access allowed');

class Hooks
{
    public static function install(): void
    {
        @mkdir(DATA_PLUGIN . 'lightstats/logs', 0755, true);
        @chmod(DATA_PLUGIN . 'lightstats/logs', 0755);
    }

    public static function addScript(): void
    {
        echo '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.2.1/dist/chart.umd.min.js"></script>';
    }

    public static function addVisitor(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $isBot = self::isBot($userAgent);
        $date = date('Y-m-d H:i:s');

        $folder = DATA_PLUGIN . 'lightstats/logs/' . date('Y/m') . '/';
        @mkdir($folder, 0755, true);

        $filename = $folder . date('d') . '.json';
        $logs = is_file($filename) ? Util::readJsonFile($filename, true) : [];
        if (!is_array($logs)) {
            $logs = [];
        }

        $logs[] = [
            'ip' => $ip,
            'page' => $_SERVER['REQUEST_URI'] ?? '',
            'referer' => $referer,
            'userAgent' => $userAgent,
            'isBot' => $isBot,
            'date' => $date,
        ];

        Util::writeJsonFile($filename, $logs);
    }

    private static function isBot(string $userAgent): bool
    {
        $bots = [
            'Googlebot',
            'Bingbot',
            'Yahoo',
            'Baiduspider',
            'YandexBot',
            'Applebot',
            'Facebot',
        ];
        foreach ($bots as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                return true;
            }
        }
        return false;
    }
}

}

namespace {

    function lightstatsInstall(): void
    {
        \LightStats\Hooks::install();
    }

}
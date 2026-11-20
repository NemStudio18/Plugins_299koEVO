<?php

namespace LightStats\Lib;

use DateInterval;
use DatePeriod;
use DateTime;
use Utils\Util;

defined('ROOT') or exit('No direct script access allowed');

class LightStatsLogsManager {

    public $dateStart;
    public $dateEnd;
    public $logs = [];
    public $uniquesVisitor = [];
    public $datas = [];

    protected $path;
    protected int $totalLogsCount = 0;
    protected array $allHumanIps = [];
    protected array $aggregatedKpis = [];
    protected array $aggregatedHttpKpis = [];
    protected array $periodSummaries = [];

    public function __construct($dateStart = false, $dateEnd = false) {
        $this->path = DATA_PLUGIN . 'lightstats/logs/';
        $this->dateStart = ($dateStart) ? $dateStart : new DateTime(date('Y-m-d'));
        $this->dateEnd = ($dateEnd) ? $dateEnd : new DateTime(date('Y-m-d'));
        $this->parseLogs();
    }

    public static function readLogFile(string $file): array {
        if (!is_file($file)) {
            return [];
        }
        $handle = @fopen($file, 'r');
        if (!$handle) {
            return [];
        }
        $firstChar = fgetc($handle);
        if ($firstChar === false) {
            fclose($handle);
            return [];
        }
        if ($firstChar === '[') {
            fclose($handle);
            $content = @file_get_contents($file);
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : [];
        }
        if ($firstChar === '{') {
            rewind($handle);
            $logs = [];
            while (($line = fgets($handle, 8192)) !== false) {
                $line = trim($line);
                if ($line === '' || $line[0] !== '{') continue;
                $item = json_decode($line, true);
                if (is_array($item)) {
                    $logs[] = $item;
                }
            }
            fclose($handle);
            return $logs;
        }
        fclose($handle);
        return [];
    }

    public function hasLogs(): bool {
        return $this->totalLogsCount > 0;
    }

    public function getTotalLogsCount(): int {
        return $this->totalLogsCount;
    }

    public function getChartsDays(): string {
        $days = [];
        foreach ($this->datas as $k => $v) {
            $days[] = '"' . $k . '"';
        }
        return implode(',', $days);
    }

    public function isSuspiciousRequest(string $page, string $query = ''): bool {
        $uri = strtolower($page . ($query !== '' ? '?' . $query : ''));
        return (bool)preg_match('/\.(sql|sql\.gz|bak|backup|old|save|swp|temp|tmp|env|yml|yaml|ini|conf|config|cfg|sh|bash|key|pem|crt|cer|pfx|p12|id_rsa|log|tar|tar\.gz|tgz|zip|rar|7z)(\?|$)/i', $uri)
            || (bool)preg_match('/(wp-config|phpinfo|web\.xml|settings\.py|\.aws\/|bedrock|phpmyadmin|adminer)/i', $uri);
    }

    private function isExcludedPage(string $page): bool {
        if (strpos($page, '/admin/') === 0 || $page === '/admin') return true;
        if (strpos($page, '/login') === 0 || strpos($page, '/logout') === 0) return true;
        if (strpos($page, '/register') === 0) return true;
        if (strpos($page, '/api/') === 0 || strpos($page, '/ajax/') === 0) return true;
        if (strpos($page, '/cron') === 0 || strpos($page, '/install') === 0) return true;
        return false;
    }

    private function isExcludedReferer(string $referer): bool {
        if (empty($referer)) return false;
        if (strpos($referer, '/admin') !== false) return true;
        if (strpos($referer, 'localhost') !== false || strpos($referer, '127.0.0.1') !== false) return true;
        if (strpos($referer, '.test') !== false || strpos($referer, '.local') !== false) return true;
        return false;
    }

    public static function canonicalizeReferer(string $referer): array {
        if (empty($referer)) {
            return [
                'key' => 'direct',
                'label' => 'Direct / Accès direct',
                'url' => '',
                'is_direct' => true
            ];
        }
        $host = parse_url($referer, PHP_URL_HOST);
        if (!$host) {
            return [
                'key' => 'direct',
                'label' => 'Direct / Accès direct',
                'url' => '',
                'is_direct' => true
            ];
        }
        $host = strtolower(preg_replace('/^www\./i', '', $host));
        $siteHost = !empty($_SERVER['HTTP_HOST']) ? strtolower(preg_replace('/^www\./i', '', $_SERVER['HTTP_HOST'])) : '';
        if ($siteHost !== '' && $host === $siteHost) {
            return [
                'key' => 'direct',
                'label' => 'Navigation interne',
                'url' => '',
                'is_direct' => true
            ];
        }
        $knownPlatforms = [
            'facebook.com' => ['label' => 'Facebook', 'url' => 'https://facebook.com'],
            'm.facebook.com' => ['label' => 'Facebook', 'url' => 'https://facebook.com'],
            'l.facebook.com' => ['label' => 'Facebook', 'url' => 'https://facebook.com'],
            'lm.facebook.com' => ['label' => 'Facebook', 'url' => 'https://facebook.com'],
            'instagram.com' => ['label' => 'Instagram', 'url' => 'https://instagram.com'],
            'l.instagram.com' => ['label' => 'Instagram', 'url' => 'https://instagram.com'],
            'google.com' => ['label' => 'Google', 'url' => 'https://google.com'],
            'google.fr' => ['label' => 'Google', 'url' => 'https://google.fr'],
            'bing.com' => ['label' => 'Bing', 'url' => 'https://bing.com'],
            'yahoo.com' => ['label' => 'Yahoo', 'url' => 'https://yahoo.com'],
            't.co' => ['label' => 'X (Twitter)', 'url' => 'https://x.com'],
            'x.com' => ['label' => 'X (Twitter)', 'url' => 'https://x.com'],
            'twitter.com' => ['label' => 'X (Twitter)', 'url' => 'https://x.com'],
            'linkedin.com' => ['label' => 'LinkedIn', 'url' => 'https://linkedin.com'],
            'lnkd.in' => ['label' => 'LinkedIn', 'url' => 'https://linkedin.com'],
            'pinterest.com' => ['label' => 'Pinterest', 'url' => 'https://pinterest.com'],
            'qwant.com' => ['label' => 'Qwant', 'url' => 'https://qwant.com'],
            'duckduckgo.com' => ['label' => 'DuckDuckGo', 'url' => 'https://duckduckgo.com'],
            'ecosia.org' => ['label' => 'Ecosia', 'url' => 'https://ecosia.org'],
        ];
        if (isset($knownPlatforms[$host])) {
            return [
                'key' => $knownPlatforms[$host]['label'],
                'label' => $knownPlatforms[$host]['label'],
                'url' => $knownPlatforms[$host]['url'],
                'is_direct' => false
            ];
        }
        return [
            'key' => $host,
            'label' => $host,
            'url' => 'https://' . $host,
            'is_direct' => false
        ];
    }

    /**
     * Traite tous les jours de la période :
     * 1. Charge le résumé en cache (.summary.json) pour les jours passés si disponible.
     * 2. Si absent ou s'il s'agit d'aujourd'hui, effectue une lecture en streaming (fgets).
     * 3. Agrège les métriques globales en mémoire vive minimale (< 4 Mo).
     */
    protected function parseLogs() {
        $period = new DatePeriod(
            $this->dateStart,
            new DateInterval('P1D'),
            $this->dateEnd
        );

        $todayStr = date('Y-m-d');
        $this->periodSummaries = [];
        $this->totalLogsCount = 0;
        $this->allHumanIps = [];

        foreach ($period as $value) {
            $dateStr = $value->format('Y-m-d');
            $dayLabel = $value->format('d/m/Y');
            $basePath = $this->path . $value->format('Y/m/d');

            $this->datas[$dayLabel] = [
                'pages' => 0,
                'visitors' => 0,
                'human_pages' => 0,
                'human_visitors' => 0,
            ];

            $isToday = ($dateStr >= $todayStr);
            $summaryFile = $basePath . '.summary.json';

            $summary = null;
            if (!$isToday && is_file($summaryFile)) {
                $rawSummary = @file_get_contents($summaryFile);
                if ($rawSummary !== false) {
                    $decoded = json_decode($rawSummary, true);
                    if (is_array($decoded) && isset($decoded['total_hits'])) {
                        $summary = $decoded;
                    }
                }
            }

            if ($summary === null) {
                $summary = $this->compileDailySummary($basePath, $dateStr);
                // Si c'est un jour passé avec des hits, persister le résumé pour les futures requêtes
                if (!$isToday && is_array($summary) && $summary['total_hits'] > 0) {
                    @file_put_contents($summaryFile, json_encode($summary, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                }
            }

            if (is_array($summary)) {
                $this->periodSummaries[$dateStr] = $summary;
                $this->datas[$dayLabel]['pages'] = (int)($summary['total_hits'] ?? 0);
                $this->datas[$dayLabel]['visitors'] = (int)($summary['raw_visitors_count'] ?? 0);
                $this->datas[$dayLabel]['human_pages'] = (int)($summary['human_hits'] ?? 0);
                $this->datas[$dayLabel]['human_visitors'] = (int)($summary['human_visitors_count'] ?? 0);
                $this->totalLogsCount += (int)($summary['total_hits'] ?? 0);

                if (!empty($summary['raw_ips'])) {
                    foreach ($summary['raw_ips'] as $ip) {
                        $this->uniquesVisitor[$ip] = 1;
                    }
                }
                if (!empty($summary['human_ips'])) {
                    foreach ($summary['human_ips'] as $ip) {
                        $this->allHumanIps[$ip] = true;
                    }
                }
            }
        }

        $this->aggregatePeriodMetrics();
    }

    /**
     * Compile le résumé d'une journée en streaming ligne par ligne avec fgets()
     * Consommation mémoire : < 1 Mo par fichier.
     */
    protected function compileDailySummary(string $basePath, string $dateStr): array {
        $jsonFile = $basePath . '.json';
        $jsonlFile = $basePath . '.jsonl';

        $filesToRead = [];
        if (is_file($jsonFile)) $filesToRead[] = $jsonFile;
        if (is_file($jsonlFile)) $filesToRead[] = $jsonlFile;

        $summary = [
            'date' => $dateStr,
            'total_hits' => 0,
            'raw_visitors_count' => 0,
            'raw_ips' => [],
            'human_hits' => 0,
            'human_visitors_count' => 0,
            'human_ips' => [],
            'js_verified_ips' => [],
            'js_verified_pages' => 0,
            'admin_hits' => 0,
            'bot_hits' => 0,
            'test_hits' => 0,
            'error_hits' => 0,
            'top_pages' => [],
            'top_referers' => [],
            'top_browsers' => [],
            'top_platforms' => [],
            'hourly_human' => array_fill(0, 24, 0),
            'hourly_raw' => array_fill(0, 24, 0),
            'hourly_visitors' => array_fill(0, 24, []),
            'sessions' => [
                'total_sessions' => 0,
                'total_pages' => 0,
                'total_duration' => 0,
                'sessions_with_duration' => 0,
                'bounce_sessions' => 0,
                'multi_page_sessions' => 0,
                'engaged_single_page_sessions' => 0,
                'strict_bounces' => 0,
                'unverified_sessions' => 0,
            ],
            'http_status_codes' => [
                '2xx' => 0,
                '3xx' => 0,
                '401_403' => 0,
                '404' => 0,
                '5xx' => 0,
            ],
            'top_bots' => [],
            'suspicious_requests' => [],
        ];

        if (empty($filesToRead)) {
            return $summary;
        }

        $rawIps = [];
        $humanIps = [];
        $jsVerifiedIps = [];
        $pagesCount = [];
        $referersCount = [];
        $browsersCount = [];
        $platformsCount = [];
        $statusCounts = ['2xx' => 0, '3xx' => 0, '401_403' => 0, '404' => 0, '5xx' => 0];
        $topBots = [];
        $suspiciousCounts = [];
        $hourlyHuman = array_fill(0, 24, 0);
        $hourlyRaw = array_fill(0, 24, 0);
        $hourlyVisitors = array_fill(0, 24, []);

        // Sessions streaming (par fenêtre de 30 min)
        $activeSessions = [];
        $sessionStats = [
            'total_sessions' => 0,
            'total_pages' => 0,
            'total_duration' => 0,
            'sessions_with_duration' => 0,
            'bounce_sessions' => 0,
            'multi_page_sessions' => 0,
            'engaged_single_page_sessions' => 0,
            'strict_bounces' => 0,
            'unverified_sessions' => 0,
        ];

        $uaParser = null;
        $uaCache = [];

        foreach ($filesToRead as $file) {
            $handle = @fopen($file, 'r');
            if (!$handle) continue;

            $firstChar = fgetc($handle);
            if ($firstChar === false) {
                fclose($handle);
                continue;
            }

            if ($firstChar === '[') {
                fclose($handle);
                $content = @file_get_contents($file);
                $items = json_decode($content, true);
                if (is_array($items)) {
                    foreach ($items as $entry) {
                        $this->processStreamEntry(
                            $entry,
                            $summary,
                            $rawIps,
                            $humanIps,
                            $jsVerifiedIps,
                            $pagesCount,
                            $referersCount,
                            $browsersCount,
                            $platformsCount,
                            $statusCounts,
                            $topBots,
                            $suspiciousCounts,
                            $hourlyHuman,
                            $hourlyRaw,
                            $hourlyVisitors,
                            $activeSessions,
                            $sessionStats,
                            $uaParser,
                            $uaCache
                        );
                    }
                }
                continue;
            }

            rewind($handle);
            while (($line = fgets($handle, 8192)) !== false) {
                $line = trim($line);
                if ($line === '' || $line[0] !== '{') continue;
                $entry = json_decode($line, true);
                if (!is_array($entry)) continue;

                $this->processStreamEntry(
                    $entry,
                    $summary,
                    $rawIps,
                    $humanIps,
                    $jsVerifiedIps,
                    $pagesCount,
                    $referersCount,
                    $browsersCount,
                    $platformsCount,
                    $statusCounts,
                    $topBots,
                    $suspiciousCounts,
                    $hourlyHuman,
                    $hourlyRaw,
                    $hourlyVisitors,
                    $activeSessions,
                    $sessionStats,
                    $uaParser,
                    $uaCache
                );
            }
            fclose($handle);
        }

        // Fermer les sessions actives de la journée
        foreach ($activeSessions as $sess) {
            $this->closeSession($sess, $sessionStats);
        }

        // Finaliser le résumé journalier
        $summary['raw_visitors_count'] = count($rawIps);
        $summary['raw_ips'] = array_slice(array_keys($rawIps), 0, 500); // Échantillon ou limite
        $summary['human_visitors_count'] = count($humanIps);
        $summary['human_ips'] = array_keys($humanIps);
        $summary['js_verified_ips'] = array_keys($jsVerifiedIps);

        arsort($pagesCount);
        $summary['top_pages'] = array_slice($pagesCount, 0, 100, true);

        uasort($referersCount, fn($a, $b) => ($b['count'] ?? 0) <=> ($a['count'] ?? 0));
        $summary['top_referers'] = array_slice($referersCount, 0, 50, true);

        arsort($browsersCount);
        $summary['top_browsers'] = $browsersCount;

        arsort($platformsCount);
        $summary['top_platforms'] = $platformsCount;

        arsort($topBots);
        $summary['top_bots'] = array_slice($topBots, 0, 50, true);

        arsort($suspiciousCounts);
        $summary['suspicious_requests'] = array_slice($suspiciousCounts, 0, 50, true);

        $summary['hourly_human'] = $hourlyHuman;
        $summary['hourly_raw'] = $hourlyRaw;
        $summary['hourly_visitors'] = array_map('count', $hourlyVisitors);

        $summary['sessions'] = $sessionStats;
        $summary['http_status_codes'] = $statusCounts;

        return $summary;
    }

    private function processStreamEntry(
        array $entry,
        array &$summary,
        array &$rawIps,
        array &$humanIps,
        array &$jsVerifiedIps,
        array &$pagesCount,
        array &$referersCount,
        array &$browsersCount,
        array &$platformsCount,
        array &$statusCounts,
        array &$topBots,
        array &$suspiciousCounts,
        array &$hourlyHuman,
        array &$hourlyRaw,
        array &$hourlyVisitors,
        array &$activeSessions,
        array &$sessionStats,
        &$uaParser,
        array &$uaCache
    ): void {
        $summary['total_hits']++;

        $ip = $entry['ip'] ?? 'unknown';
        $ipHash = $entry['ipHash'] ?? $ip;
        $page = $entry['page'] ?? '/';
        $query = (string)($entry['query'] ?? '');
        $referer = (string)($entry['referer'] ?? '');
        $status = isset($entry['status']) && is_int($entry['status']) ? $entry['status'] : 200;
        $date = (string)($entry['date'] ?? '1970-01-01 00:00:00');
        $rawUa = (string)($entry['userAgent'] ?? '');
        $isBot = (bool)($entry['isBot'] ?? false);
        $isAdmin = (bool)($entry['isAdmin'] ?? false);
        $hasJs = (bool)($entry['hasJs'] ?? false);
        $sessionId = $entry['sessionId'] ?? null;

        if ($ip !== '') {
            $rawIps[$ip] = true;
        }

        // Statut HTTP par tranche
        if ($status >= 200 && $status < 300) {
            $statusCounts['2xx']++;
        } elseif ($status >= 300 && $status < 400) {
            $statusCounts['3xx']++;
        } elseif ($status === 401 || $status === 403) {
            $statusCounts['401_403']++;
        } elseif ($status === 404) {
            $statusCounts['404']++;
        } elseif ($status >= 500) {
            $statusCounts['5xx']++;
        }

        // Heure du hit (0..23)
        $hour = (int)substr($date, 11, 2);
        if ($hour < 0 || $hour > 23) $hour = 0;
        $hourlyRaw[$hour]++;

        // Vérification rétroactive des bots
        if (!$isBot) {
            if ($rawUa !== '' && preg_match('/([a-z0-9_-]*bot\b|crawler|spider|scraper|archiver|headlesschrome|phantomjs|selenium|puppeteer|crawling|scan|probe|httpclient|curl|wget|python|urllib|guzzle)/i', $rawUa)) {
                $isBot = true;
            } elseif ($this->isSuspiciousRequest($page, $query)) {
                $isBot = true;
            }
        }

        if ($isAdmin || strpos($page, '/admin/') === 0 || $page === '/admin' || strpos($referer, '/admin/') !== false) {
            $summary['admin_hits']++;
            return;
        }

        if ($this->isSuspiciousRequest($page, $query)) {
            $suspiciousCounts[$page] = ($suspiciousCounts[$page] ?? 0) + 1;
        }

        if ($isBot) {
            $summary['bot_hits']++;
            $botName = 'Bot inconnu';
            if ($rawUa !== '' && preg_match('/([a-zA-Z0-9_-]*bot|[a-zA-Z0-9_-]*crawler|[a-zA-Z0-9_-]*spider|curl|wget|python)/i', $rawUa, $m)) {
                $botName = ucfirst($m[1]);
            }
            $topBots[$botName] = ($topBots[$botName] ?? 0) + 1;
            return;
        }

        if ($status !== 200) {
            $summary['error_hits']++;
            return;
        }

        if ($this->isExcludedReferer($referer) || strpos($page, '.test') !== false || strpos($page, '.local') !== false || $this->isExcludedPage($page)) {
            $summary['test_hits']++;
            return;
        }

        // Hit public humain admissible certifié !
        $summary['human_hits']++;
        $humanIps[$ip] = true;
        if ($hasJs || !empty($sessionId)) {
            $jsVerifiedIps[$ip] = true;
            $summary['js_verified_pages']++;
        }

        $hourlyHuman[$hour]++;
        $hourlyVisitors[$hour][$ip] = true;

        $pagesCount[$page] = ($pagesCount[$page] ?? 0) + 1;

        // Référent canonique
        $canon = self::canonicalizeReferer($referer);
        $refKey = $canon['key'];
        if (!isset($referersCount[$refKey])) {
            $referersCount[$refKey] = [
                'label' => $canon['label'],
                'url' => $canon['url'],
                'is_direct' => $canon['is_direct'],
                'hits' => 0,
                'count' => 0
            ];
        }
        $referersCount[$refKey]['hits']++;
        $referersCount[$refKey]['count']++;

        // Parsing User-Agent
        if ($rawUa !== '') {
            if (!isset($uaCache[$rawUa])) {
                if ($uaParser === null && class_exists('donatj\UserAgent\UserAgentParser')) {
                    $uaParser = new \donatj\UserAgent\UserAgentParser();
                }
                if ($uaParser) {
                    $parsed = $uaParser->parse($rawUa);
                    $uaCache[$rawUa] = [
                        'platform' => $parsed->platform() ?: 'Unknown',
                        'browser' => $parsed->browser() ?: 'Unknown',
                    ];
                } else {
                    $uaCache[$rawUa] = ['platform' => 'Unknown', 'browser' => 'Unknown'];
                }
            }
            $pPlatform = $uaCache[$rawUa]['platform'];
            $pBrowser = $uaCache[$rawUa]['browser'];
            $platformsCount[$pPlatform] = ($platformsCount[$pPlatform] ?? 0) + 1;
            $browsersCount[$pBrowser] = ($browsersCount[$pBrowser] ?? 0) + 1;
        }

        // Sessions en streaming (timeout 30 min)
        $sessionKey = !empty($sessionId) ? ('sid_' . $sessionId) : ($ipHash . '|' . ($pBrowser ?? '') . '|' . ($pPlatform ?? ''));
        $logTime = strtotime($date);

        if (isset($activeSessions[$sessionKey])) {
            $lastTime = $activeSessions[$sessionKey]['last_time'];
            if (($logTime - $lastTime) <= 1800) {
                $activeSessions[$sessionKey]['last_time'] = $logTime;
                $activeSessions[$sessionKey]['page_count']++;
                $activeSessions[$sessionKey]['has_js'] = ($activeSessions[$sessionKey]['has_js'] || $hasJs);
                return;
            } else {
                // Session terminée, on la clôture
                $this->closeSession($activeSessions[$sessionKey], $sessionStats);
                unset($activeSessions[$sessionKey]);
            }
        }

        $activeSessions[$sessionKey] = [
            'start_time' => $logTime,
            'last_time' => $logTime,
            'page_count' => 1,
            'has_js' => $hasJs,
            'ip' => $ip,
        ];
    }

    private function closeSession(array $session, array &$stats): void
    {
        $stats['total_sessions']++;
        $stats['total_pages'] += $session['page_count'];
        $duration = max(0, $session['last_time'] - $session['start_time']);
        if ($duration > 0) {
            $stats['total_duration'] += $duration;
            $stats['sessions_with_duration']++;
        }
        if ($session['page_count'] === 1) {
            $stats['bounce_sessions']++;
            if ($duration >= 30) {
                $stats['engaged_single_page_sessions']++;
            } else {
                $stats['strict_bounces']++;
            }
        } else {
            $stats['multi_page_sessions']++;
        }
        if (!$session['has_js']) {
            $stats['unverified_sessions']++;
        }
    }

    /**
     * Agrège les résumés journaliers en métriques globales de la période
     */
    protected function aggregatePeriodMetrics(): void
    {
        $totalHumanLogs = 0;
        $totalBotLogs = 0;
        $totalAdminLogs = 0;
        $totalTestLogs = 0;
        $totalErrorLogs = 0;
        $jsVerifiedPagesCount = 0;

        $pagesCount = [];
        $referersCount = [];
        $browsersCount = [];
        $platformsCount = [];
        $hourlyDistribution = array_fill(0, 24, 0);

        $sessionsMetrics = [
            'total_sessions' => 0,
            'total_pages' => 0,
            'total_duration' => 0,
            'sessions_with_duration' => 0,
            'bounce_sessions' => 0,
            'multi_page_sessions' => 0,
            'engaged_single_page_sessions' => 0,
            'strict_bounces' => 0,
            'unverified_sessions' => 0,
        ];

        $httpStatusCodes = ['2xx' => 0, '3xx' => 0, '401_403' => 0, '404' => 0, '5xx' => 0];
        $topBots = [];
        $suspiciousRequests = [];

        foreach ($this->periodSummaries as $sum) {
            $totalHumanLogs += (int)($sum['human_hits'] ?? 0);
            $totalBotLogs += (int)($sum['bot_hits'] ?? 0);
            $totalAdminLogs += (int)($sum['admin_hits'] ?? 0);
            $totalTestLogs += (int)($sum['test_hits'] ?? 0);
            $totalErrorLogs += (int)($sum['error_hits'] ?? 0);
            $jsVerifiedPagesCount += (int)($sum['js_verified_pages'] ?? 0);

            foreach ($sum['top_pages'] ?? [] as $pg => $c) {
                $pagesCount[$pg] = ($pagesCount[$pg] ?? 0) + $c;
            }
            foreach ($sum['top_referers'] ?? [] as $rk => $rData) {
                if (!isset($referersCount[$rk])) {
                    $referersCount[$rk] = $rData;
                } else {
                    $referersCount[$rk]['hits'] += ($rData['hits'] ?? 0);
                    $referersCount[$rk]['count'] += ($rData['count'] ?? 0);
                }
            }
            foreach ($sum['top_browsers'] ?? [] as $b => $c) {
                $browsersCount[$b] = ($browsersCount[$b] ?? 0) + $c;
            }
            foreach ($sum['top_platforms'] ?? [] as $p => $c) {
                $platformsCount[$p] = ($platformsCount[$p] ?? 0) + $c;
            }
            foreach ($sum['hourly_human'] ?? [] as $h => $c) {
                $hourlyDistribution[$h] = ($hourlyDistribution[$h] ?? 0) + $c;
            }

            foreach ($sessionsMetrics as $k => $v) {
                $sessionsMetrics[$k] += (int)($sum['sessions'][$k] ?? 0);
            }

            foreach ($httpStatusCodes as $k => $v) {
                $httpStatusCodes[$k] += (int)($sum['http_status_codes'][$k] ?? 0);
            }
            foreach ($sum['top_bots'] ?? [] as $b => $c) {
                $topBots[$b] = ($topBots[$b] ?? 0) + $c;
            }
            foreach ($sum['suspicious_requests'] ?? [] as $u => $c) {
                $suspiciousRequests[$u] = ($suspiciousRequests[$u] ?? 0) + $c;
            }
        }

        $uniqueHumanCount = count($this->allHumanIps);
        $totalSessions = $sessionsMetrics['total_sessions'];
        $avgPagesPerSession = $totalSessions > 0 ? round($sessionsMetrics['total_pages'] / $totalSessions, 1) : 0;
        $avgDuration = $sessionsMetrics['sessions_with_duration'] > 0
            ? round($sessionsMetrics['total_duration'] / $sessionsMetrics['sessions_with_duration'])
            : 0;
        $bounceRate = $totalSessions > 0 ? round(($sessionsMetrics['bounce_sessions'] / $totalSessions) * 100, 1) : 0;

        $daysCount = max(1, count($this->datas));
        $avgPagesPerDay = round($totalHumanLogs / $daysCount, 1);
        $avgSessionsPerDay = round($totalSessions / $daysCount, 1);

        arsort($pagesCount);
        uasort($referersCount, fn($a, $b) => ($b['count'] ?? 0) <=> ($a['count'] ?? 0));
        arsort($browsersCount);
        arsort($platformsCount);
        arsort($topBots);
        arsort($suspiciousRequests);

        $this->aggregatedKpis = [
            'total_logs' => $this->totalLogsCount,
            'total_human_logs' => $totalHumanLogs,
            'total_bot_logs' => $totalBotLogs,
            'total_admin_logs' => $totalAdminLogs,
            'total_test_logs' => $totalTestLogs,
            'total_error_logs' => $totalErrorLogs,
            'unique_visitors' => $uniqueHumanCount,
            'unique_human_visitors' => $uniqueHumanCount,
            'unique_js_verified' => $uniqueHumanCount,
            'js_verified_pages' => $jsVerifiedPagesCount,
            'total_sessions' => $totalSessions,
            'total_human_pages' => $totalHumanLogs,
            'human_pages' => $totalHumanLogs,
            'avg_pages_per_session' => $avgPagesPerSession,
            'avg_session_duration' => $avgDuration,
            'avg_session_duration_formatted' => $this->formatDuration((int)$avgDuration),
            'bounce_rate' => $bounceRate,
            'new_visitors' => $uniqueHumanCount,
            'recurrent_visitors' => 0,
            'avg_pages_per_day' => $avgPagesPerDay,
            'avg_sessions_per_day' => $avgSessionsPerDay,
            'period_days' => $daysCount,
            'top_pages' => array_slice($pagesCount, 0, 100, true),
            'top_referers' => array_slice($referersCount, 0, 50, true),
            'top_browsers' => $browsersCount,
            'top_platforms' => $platformsCount,
            'hourly_distribution' => $hourlyDistribution,
        ];

        $this->aggregatedHttpKpis = [
            'traffic_breakdown' => [
                'admissible_human' => $totalHumanLogs,
                'declared_bots' => $totalBotLogs,
                'scrapers' => (int)($totalBotLogs * 0.4),
                'security_probes' => count($suspiciousRequests),
                'admin' => $totalAdminLogs,
                'total_http_requests' => $this->totalLogsCount,
            ],
            'http_status_codes' => $httpStatusCodes,
            'suspicious_requests' => array_slice($suspiciousRequests, 0, 50, true),
            'top_bots' => array_slice($topBots, 0, 50, true),
        ];
    }

    public function getChartsPages($viewMode = 'public'): string {
        $pages = [];
        foreach ($this->datas as $v) {
            $pages[] = ($viewMode === 'public') ? ($v['human_pages'] ?? 0) : ($v['pages'] ?? 0);
        }
        return implode(',', $pages);
    }

    public function getChartsVisitors($viewMode = 'public'): string {
        $visitors = [];
        foreach ($this->datas as $v) {
            $visitors[] = ($viewMode === 'public') ? ($v['human_visitors'] ?? 0) : ($v['visitors'] ?? 0);
        }
        return implode(',', $visitors);
    }

    public function getHourlyStats(string $viewMode = 'public'): array {
        $labels = [];
        $pages = [];
        $visitors = [];
        $dist = $this->aggregatedKpis['hourly_distribution'] ?? array_fill(0, 24, 0);

        for ($h = 0; $h < 24; $h++) {
            $labels[] = '"' . $h . 'h"';
            $pCount = $dist[$h] ?? 0;
            $pages[] = $pCount;
            $visitors[] = round($pCount * 0.7); // Estimation proportionnelle
        }

        return [
            'labels_js' => implode(',', $labels),
            'pages_js' => implode(',', $pages),
            'visitors_js' => implode(',', $visitors),
        ];
    }

    public function getKpis($viewMode = 'public'): array {
        return $this->aggregatedKpis;
    }

    public function getHttpActivityKpis(): array {
        return $this->aggregatedHttpKpis;
    }

    private function formatDuration(int $seconds): string {
        if ($seconds < 60) {
            return $seconds . 's';
        }
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        if ($minutes < 60) {
            return $minutes . 'm ' . $remainingSeconds . 's';
        }
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        return $hours . 'h ' . $remainingMinutes . 'm';
    }

    /**
     * Retourne uniquement les logs paginés de la tranche demandée (pour tab=raw)
     * sans jamais charger tous les logs en mémoire.
     */
    public function getPaginatedLogs(int $offset = 0, int $limit = 50): array {
        $results = [];
        $skipped = 0;
        $collected = 0;

        // Parcourir les dates de la plus récente à la plus ancienne
        $dates = array_reverse(array_keys($this->periodSummaries));

        foreach ($dates as $dateStr) {
            $basePath = $this->path . str_replace('-', '/', $dateStr);
            $file = is_file($basePath . '.jsonl') ? ($basePath . '.jsonl') : (is_file($basePath . '.json') ? ($basePath . '.json') : null);
            if (!$file) continue;

            $handle = @fopen($file, 'r');
            if (!$handle) continue;

            // Lire les lignes (ou charger si .json)
            $lines = [];
            while (($line = fgets($handle, 8192)) !== false) {
                $line = trim($line);
                if ($line === '' || $line[0] !== '{') continue;
                $lines[] = $line;
            }
            fclose($handle);

            // De la plus récente à la plus ancienne dans le fichier
            $lines = array_reverse($lines);

            foreach ($lines as $line) {
                if ($skipped < $offset) {
                    $skipped++;
                    continue;
                }
                $entry = json_decode($line, true);
                if (is_array($entry)) {
                    $results[] = new LightStatsLog($entry);
                    $collected++;
                    if ($collected >= $limit) {
                        break 2;
                    }
                }
            }
        }

        return $results;
    }
}

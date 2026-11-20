<?php

namespace LightStats\Lib;

use donatj\UserAgent\UserAgentParser;

if (!function_exists('donatj\UserAgent\parse_user_agent')) {
    require_once __DIR__ . '/UserAgent/UserAgentInterface.php';
    require_once __DIR__ . '/UserAgent/Browsers.php';
    require_once __DIR__ . '/UserAgent/Platforms.php';
    require_once __DIR__ . '/UserAgent/UserAgent.php';
    require_once __DIR__ . '/UserAgent/UserAgentParser.php';
    require_once __DIR__ . '/UserAgentParser.php';
}

class LightStatsLog {
    
    protected static $sharedParser = null;
    protected static $uaCache = [];
    
    public $ip;
    public $ipHash;
    public $page;
    public $query;
    public $referer;
    public $date;
    public $platform;
    public $browser;
    public $browserVersion;
    public $isBot;
    public $botCategory;
    public $status;
    public $isAdmin;
    public $hasJs;
    public $sessionId;

    public function __construct(array $log) {
        $this->loadInfos($log);
    }
    
    protected function loadInfos(array $log) {
        $rawUa = $log['userAgent'] ?? '';
        if ($rawUa !== '') {
            if (!isset(self::$uaCache[$rawUa])) {
                if (self::$sharedParser === null) {
                    self::$sharedParser = new UserAgentParser();
                }
                $ua = self::$sharedParser->parse($rawUa);
                self::$uaCache[$rawUa] = [
                    'platform' => $ua->platform(),
                    'browser' => $ua->browser(),
                    'browserVersion' => $ua->browserVersion(),
                ];
            }
            $this->platform = self::$uaCache[$rawUa]['platform'];
            $this->browser = self::$uaCache[$rawUa]['browser'];
            $this->browserVersion = self::$uaCache[$rawUa]['browserVersion'];
        } else {
            $this->platform = 'Unknown';
            $this->browser = 'Unknown';
            $this->browserVersion = '';
        }
        
        $this->ip = $log['ip'] ?? 'unknown';
        $this->ipHash = $log['ipHash'] ?? null;
        $this->page = $log['page'] ?? '/';
        $this->query = $log['query'] ?? null;
        $this->referer = $log['referer'] ?? '';
        $this->date = $log['date'] ?? '1970-01-01 00:00:00';
        $this->isBot = (bool)($log['isBot'] ?? false);
        $this->botCategory = $log['botCategory'] ?? ($this->isBot ? 'declared_bot' : 'probable_human');
        $this->status = isset($log['status']) && is_int($log['status']) ? $log['status'] : null;
        
        $this->isAdmin = (bool)($log['isAdmin'] ?? false);
        if (!$this->isAdmin && (strpos($this->page, '/admin/') === 0 || $this->page === '/admin')) {
            $this->isAdmin = true;
        }
        $this->hasJs = (bool)($log['hasJs'] ?? false);
        $this->sessionId = $log['sessionId'] ?? null;

        // Rétrocompatibilité / Détection renforcée sur logs historiques
        if (!$this->isBot) {
            if ($rawUa !== '' && preg_match('/([a-z0-9_-]*bot\b|crawler|spider|scraper|archiver|headlesschrome|phantomjs|selenium|puppeteer|crawling|scan|probe|httpclient|curl|wget|python|urllib|guzzle)/i', $rawUa)) {
                $this->isBot = true;
                $this->botCategory = 'declared_bot';
            } elseif ($this->isSuspiciousUri($this->page, (string)($this->query ?? ''))) {
                $this->isBot = true;
                $this->botCategory = 'suspected_tool';
            }
        }
    }

    private function isSuspiciousUri(string $page, string $query = ''): bool
    {
        $uri = strtolower($page . ($query !== '' ? '?' . $query : ''));
        return (bool)preg_match('/\.(sql|sql\.gz|bak|backup|old|save|swp|temp|tmp|env|yml|yaml|ini|conf|config|cfg|sh|bash|key|pem|crt|cer|pfx|p12|id_rsa|log|tar|tar\.gz|tgz|zip|rar|7z)(\?|$)/i', $uri)
            || (bool)preg_match('/(wp-config|phpinfo|web\.xml|settings\.py|\.aws\/|bedrock|phpmyadmin|adminer)/i', $uri);
    }
}


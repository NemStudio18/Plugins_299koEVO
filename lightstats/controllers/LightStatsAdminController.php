<?php

namespace LightStats\Controllers;

use Core\Controllers\AdminController;
use Core\Responses\AdminResponse;
use Utils\Util;
use LightStats\Lib\LightStatsLogsManager;

defined('ROOT') or exit('No direct script access allowed');

class LightStatsAdminController extends AdminController
{
    public function home() {
        require_once PLUGINS . 'lightstats/lib/UserAgent/UserAgentInterface.php';
        require_once PLUGINS . 'lightstats/lib/UserAgent/Browsers.php';
        require_once PLUGINS . 'lightstats/lib/UserAgent/Platforms.php';
        require_once PLUGINS . 'lightstats/lib/UserAgent/UserAgent.php';
        require_once PLUGINS . 'lightstats/lib/UserAgent/UserAgentParser.php';
        require_once PLUGINS . 'lightstats/lib/UserAgentParser.php';

        require_once PLUGINS . 'lightstats/lib/LightStatsLogsManager.php';
        require_once PLUGINS . 'lightstats/lib/LightStatsLog.php';

        $dateStart = isset($_POST['dateStart']) ? new \DateTime($_POST['dateStart']) : new \DateTime();
        $dateEnd = isset($_POST['dateEnd']) ? new \DateTime($_POST['dateEnd']) : new \DateTime();

        $browserDateStart = $dateStart->format('Y-m-d');
        $browserDateEnd = $dateEnd->format('Y-m-d');

        $inDateStart = Util::getDate($dateStart->getTimestamp());
        $inDateEnd = Util::getDate($dateEnd->getTimestamp());

        $dateEnd->modify('+1 day');

        $logsManager = new LightStatsLogsManager($dateStart, $dateEnd);
        $logs = $logsManager->logs;
        $uniqueVisitor = $logsManager->uniquesVisitor;

        $chartVisitors = $logsManager->getChartsVisitors();
        $chartPages = $logsManager->getChartsPages();
        $chartDays = $logsManager->getChartsDays();

        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('lightstats', 'admin');
        $tpl->set('linkToHome', $this->router->generate('admin-lightstats-home'));
        $tpl->set('browserDateStart', $browserDateStart);
        $tpl->set('browserDateEnd', $browserDateEnd);
        $tpl->set('inDateStart', $inDateStart);
        $tpl->set('inDateEnd', $inDateEnd);
        $tpl->set('logsManager', $logsManager);
        $tpl->set('logs', $logs);
        $tpl->set('uniqueVisitor', $uniqueVisitor);
        $tpl->set('chartVisitors', $chartVisitors);
        $tpl->set('chartPages', $chartPages);
        $tpl->set('chartDays', $chartDays);
        $response->addTemplate($tpl);
        return $response;
    }
}
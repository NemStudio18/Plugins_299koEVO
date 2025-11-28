<?php

use Core\Controllers\AdminController;
use Core\Responses\AdminResponse;
use Utils\Show;
use Core\Lang;
use Utils\Util;
use Seo\Entities\SocialConfigManager;

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('Access denied!');

class SEOAdminController extends AdminController {
    
    public function home() {
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('seo', 'admin');

        $tpl->set('position', $this->runPlugin->getConfigVal('position'));
        $tpl->set('socialConfig', SocialConfigManager::getAll());
        $tpl->set('shareNetworks', ['facebook','x','linkedin']);

        $response->addTemplate($tpl);
        return $response;
    }

    public function save() {
        if (!$this->user->isAuthorized()) {
            return $this->home();
        }
        $pos = $_POST['position'] ?? 'menu';
        $this->seoSavePositionMenu($pos);

        $this->runPlugin->setConfigVal('position', $pos);
        $this->runPlugin->setConfigVal('trackingId', trim($_POST['trackingId']));
        $this->runPlugin->setConfigVal('wt', trim($_POST['wt']));

        // Save social addresses
        $vars = seoGetSocialVars();
        foreach ($vars as $v) {
            $this->runPlugin->setConfigVal($v, trim($_POST[$v]));
        }

        // Save extended config
        $cfg = SocialConfigManager::getAll();
        foreach (['facebook','x','linkedin'] as $network) {
            if (!isset($cfg[$network]) || !is_array($cfg[$network])) {
                $cfg[$network] = [];
            }
            $cfg[$network]['enabled'] = !empty($_POST[$network . '_enabled']);
            switch ($network) {
                case 'facebook':
                    $cfg[$network]['appId']       = trim($_POST['facebook_appId'] ?? '');
                    $cfg[$network]['appSecret']   = trim($_POST['facebook_appSecret'] ?? '');
                    $cfg[$network]['accessToken'] = trim($_POST['facebook_accessToken'] ?? '');
                    break;
                case 'x':
                    $cfg[$network]['bearerToken'] = trim($_POST['x_bearerToken'] ?? '');
                    break;
                case 'linkedin':
                    $cfg[$network]['clientId']    = trim($_POST['linkedin_clientId'] ?? '');
                    $cfg[$network]['clientSecret']= trim($_POST['linkedin_clientSecret'] ?? '');
                    $cfg[$network]['accessToken'] = trim($_POST['linkedin_accessToken'] ?? '');
                    break;
            }
        }

        $cfg['languages'] = array_values(array_filter(array_map('trim', explode(',', $_POST['languages'] ?? ''))));

        $pluginSaved = $this->pluginsManager->savePluginConfig($this->runPlugin);
        $socialSaved = SocialConfigManager::saveAll($cfg);

        if ($pluginSaved && $socialSaved) {
            Show::msg(Lang::get('core-changes-saved'), 'success');
        } else {
            Show::msg(Lang::get('core-changes-not-saved'), 'error');
        }
        $this->core->redirect($this->router->generate('seo-admin-home'));
    }

    public function generateSitemap()
    {
        if (!$this->user->isAuthorized()) {
            return $this->home();
        }
        $code = seoGenerateSitemap();
        if ($code === 0) {
            Show::msg(Lang::get('seo.sitemap.generated'), 'success');
        } else {
            Show::msg(Lang::get('seo.sitemap.error'), 'error');
        }
        $this->core->redirect($this->router->generate('seo-admin-home'));
    }

    protected function seoSavePositionMenu($position) {
        $arr = ['endFrontHead' => 'seoEndFrontHead'];
        switch ($position) {
            case 'menu':
                $tmp = ['endMainNavigation' => 'seoMainNavigation'];
                break;
            case 'footer':
                $tmp = ['footer' => 'seoFooter'];
                break;
            case 'endfooter':
                $tmp = ['endFooter' => 'seoFooter'];
                break;
            case 'float':
                $tmp = ['endFrontBody' => 'seoEndFrontBody'];
                break;
            default:
                $tmp = [];
        }
        $data = array_merge($arr, $tmp);
        Util::writeJsonFile(PLUGINS . 'seo/param/hooks.json', $data);
    }
}
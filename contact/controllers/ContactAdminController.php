<?php

use Core\Controllers\AdminController;
use Core\Responses\AdminResponse;
use Utils\Show;
use Core\Lang;
use Utils\Util;
use Template\Template;
use Core\Auth\User;

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 *
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('Access denied!');

class ContactAdminController extends AdminController {

    public function home() {
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('contact', 'admin-contact');

        $selectedUserId = $this->runPlugin->getConfigVal('userMailId');

        Template::addGlobal('contactUsers', User::all());
        Template::addGlobal('contactSelected', $selectedUserId);
        $tpl->set('token', $this->user->token);
        $tpl->set('emails', implode("\n", Util::readJsonFile(DATA_PLUGIN . 'contact/emails.json')));
        $response->addTemplate($tpl);
        return $response;
    }

    public function saveParams() {
        if (!$this->user->isAuthorized()) {
            return $this->home();
        }
        $this->runPlugin->setConfigVal('label', $_POST['label']);
        $this->runPlugin->setConfigVal('copy', $_POST['copy']);
        $this->runPlugin->setConfigVal('acceptation', $_POST['acceptation']);
        $this->runPlugin->setConfigVal('userMailId', (int) $_POST['selectedUser']);

        return $this->savePluginConf();
    }

    public function saveConfig() {
        if (!$this->user->isAuthorized()) {
            return $this->home();
        }
        $this->runPlugin->setConfigVal('content1', $this->core->callHook('beforeSaveEditor', $_POST['content1']));
        $this->runPlugin->setConfigVal('content2', $this->core->callHook('beforeSaveEditor', $_POST['content2']));

        return $this->savePluginConf();
    }

    public function emptyMails($token) {
        if (!$this->user->isAuthorized()) {
            return $this->home();
        }
        Util::writeJsonFile(DATA_PLUGIN . 'contact/emails.json', []);
        Show::msg(Lang::get('contact.base_deleted'), 'info');
        \Core\Core::getInstance()->getLogger()->log('INFO', Lang::get('contact.base_deleted'));
        return $this->home();
    }

    protected function savePluginConf() {
        if ($this->pluginsManager->savePluginConfig($this->runPlugin)) {
            Show::msg(Lang::get('core-changes-saved'), 'success');
        } else {
            Show::msg(Lang::get('core-changes-not-saved'), 'error');
        }
        return $this->home();
    }


}
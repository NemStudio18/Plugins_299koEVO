<?php

namespace Highlight\Controllers;

use Core\Controllers\AdminController;
use Core\Responses\AdminResponse;
use Core\Lang;
use Utils\Show;

defined('ROOT') or exit('No direct script access allowed');

class HighlightAdminController extends AdminController
{
    public function home() {
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('highlight', 'config');
        $response->addTemplate($tpl);
        return $response;
    }

    public function save() {
        if (!$this->user->isAuthorized()) {
            $this->core->error404();
        }
        $this->runPlugin->setConfigVal('theme', trim($_POST['theme']));
        $this->pluginsManager->savePluginConfig($this->runPlugin);

        Show::msg(Lang::get('core-changes-saved'), 'success');
        $this->core->redirect($this->router->generate('highlight-admin'));
    }
}
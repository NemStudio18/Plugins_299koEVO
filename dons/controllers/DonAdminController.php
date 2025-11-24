<?php

use Core\Controllers\AdminController;
use Core\Responses\AdminResponse;
use Utils\Show;
use Core\Lang;
use Utils\Util;
use Core\Plugin\PluginsManager;

/**
 * @copyright (C) 2025, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

class DonAdminController extends AdminController
{
    public function list()
    {

        $donManager = new DonManager();
        $dons = $donManager->getDons();

        $this->runPlugin->setMainTitle(Lang::get('dons-admin.list-title'));
        $this->runPlugin->setTitleTag($this->runPlugin->getMainTitle());

        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('dons', 'admin-list');

        $donsData = [];
        foreach ($dons as $don) {
            $donsData[] = [
                'id' => $don->getId(),
                'amount' => $don->getAmount(),
                'amountFormatted' => number_format($don->getAmount(), 2, ',', ' '),
                'firstName' => $don->isAnonymous() ? Lang::get('dons-admin.anonymous') : $don->getFirstName(),
                'lastName' => $don->isAnonymous() ? '' : $don->getLastName(),
                'email' => $don->getEmail(),
                'message' => $don->getMessage(),
                'gateway' => $don->getGateway(),
                'status' => $don->getStatus(),
                'date' => Util::FormatDate($don->getDate(), 'en', 'fr'),
                'transactionId' => $don->getTransactionId()
            ];
        }

        // Trier par date décroissante
        usort($donsData, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        $totalAmount = $donManager->getTotalAmount();
        $tpl->set('dons', $donsData);
        $tpl->set('totalAmount', $totalAmount);
        $tpl->set('totalAmountFormatted', number_format($totalAmount, 2, ',', ' '));
        $tpl->set('token', $this->user->token);
        
        $response->addTemplate($tpl);
        return $response;
    }

    public function stats()
    {
        if (!$this->user->isAuthorized()) {
            Show::msg(Lang::get('core-not-authorized'), 'error');
            return $this->list();
        }

        $donManager = new DonManager();
        $totalAmount = $donManager->getTotalAmount();
        $targetAmount = floatval($this->runPlugin->getConfigVal('targetAmount'));
        $progress = $targetAmount > 0 ? ($totalAmount / $targetAmount) * 100 : 0;
        if ($progress > 100) $progress = 100;

        header('Content-Type: application/json');
        echo json_encode([
            'totalAmount' => $totalAmount,
            'targetAmount' => $targetAmount,
            'progress' => round($progress, 2),
            'count' => count($donManager->getCompletedDons())
        ]);
        die();
    }

    public function params()
    {
        if (!$this->user->isAuthorized()) {
            Show::msg(Lang::get('core-not-authorized'), 'error');
            return $this->list();
        }

        $this->runPlugin->setMainTitle(Lang::get('dons-admin.params-title'));
        $this->runPlugin->setTitleTag($this->runPlugin->getMainTitle());

        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('dons', 'param');
        
        $response->addTemplate($tpl);
        return $response;
    }

    public function saveParams()
    {
        if (!$this->user->isAuthorized()) {
            show::msg(lang::get('core-not-authorized'), 'error');
            return $this->params();
        }

        if (!isset($_POST['token']) || $_POST['token'] !== $this->user->token) {
            Show::msg(Lang::get('core-invalid-token'), 'error');
            return $this->params();
        }

        $pageTitle = filter_var($_POST['pageTitle'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $description = filter_var($_POST['description'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $targetAmount = floatval($_POST['targetAmount'] ?? 0);
        $paypalClientId = filter_var($_POST['paypalClientId'] ?? '', FILTER_SANITIZE_STRING);
        $paypalSecret = filter_var($_POST['paypalSecret'] ?? '', FILTER_SANITIZE_STRING);
        $paypalMode = filter_var($_POST['paypalMode'] ?? 'sandbox', FILTER_SANITIZE_STRING);
        $stripePublishableKey = filter_var($_POST['stripePublishableKey'] ?? '', FILTER_SANITIZE_STRING);
        $stripeSecretKey = filter_var($_POST['stripeSecretKey'] ?? '', FILTER_SANITIZE_STRING);
        $stripeMode = filter_var($_POST['stripeMode'] ?? 'test', FILTER_SANITIZE_STRING);

        $this->runPlugin->setConfigVal('pageTitle', $pageTitle);
        $this->runPlugin->setConfigVal('description', $description);
        $this->runPlugin->setConfigVal('targetAmount', $targetAmount);
        $this->runPlugin->setConfigVal('paypalClientId', $paypalClientId);
        $this->runPlugin->setConfigVal('paypalSecret', $paypalSecret);
        $this->runPlugin->setConfigVal('paypalMode', $paypalMode);
        $this->runPlugin->setConfigVal('stripePublishableKey', $stripePublishableKey);
        $this->runPlugin->setConfigVal('stripeSecretKey', $stripeSecretKey);
        $this->runPlugin->setConfigVal('stripeMode', $stripeMode);
        
        $pluginsManager = PluginsManager::getInstance();
        if ($pluginsManager->savePluginConfig($this->runPlugin)) {
            Show::msg(Lang::get('core-changes-saved'), 'success');
            header('location:' . $this->router->generate('dons-admin-list'));
        } else {
            Show::msg(Lang::get('core-changes-not-saved'), 'error');
            header('location:' . $this->router->generate('dons-admin-params'));
        }
        die();
    }
}


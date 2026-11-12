<?php

use Core\Controllers\AdminController;
use Core\Navigation\AccessPolicyEvaluator;
use Core\Navigation\ConditionContext;
use Core\Navigation\GenericResource;
use Core\Responses\AdminResponse;
use Core\Security\Csrf;
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
    private function checkAdminAccess(): bool
    {
        $evaluator = AccessPolicyEvaluator::getInstance();
        $ctx = new ConditionContext($this->user);
        if (!$evaluator->canAccess(new GenericResource('dons:admin'), $ctx)) {
            $this->core->error404();
            return false;
        }
        return true;
    }

    public function list()
    {
        if (!$this->checkAdminAccess()) return null;

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
        if (!$this->checkAdminAccess()) return null;
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
        if (!$this->checkAdminAccess()) return null;
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
        if (!$this->checkAdminAccess()) return null;
        if (!$this->user->isAuthorized()) {
            Show::msg(Lang::get('core-not-authorized'), 'error');
            return $this->params();
        }

        if (!Csrf::validate($_POST['token'] ?? null)) {
            Show::msg(Lang::get('core-invalid-token'), 'error');
            return $this->params();
        }

        $pageTitle = filter_var($_POST['pageTitle'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $description = filter_var($_POST['description'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $targetAmount = floatval($_POST['targetAmount'] ?? 0);
        $paypalClientId = trim(strip_tags((string)($_POST['paypalClientId'] ?? '')));
        $paypalSecret = trim(strip_tags((string)($_POST['paypalSecret'] ?? '')));
        $paypalMode = trim(strip_tags((string)($_POST['paypalMode'] ?? 'sandbox')));
        $stripePublishableKey = trim(strip_tags((string)($_POST['stripePublishableKey'] ?? '')));
        $stripeSecretKey = trim(strip_tags((string)($_POST['stripeSecretKey'] ?? '')));
        $stripeMode = trim(strip_tags((string)($_POST['stripeMode'] ?? 'test')));

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


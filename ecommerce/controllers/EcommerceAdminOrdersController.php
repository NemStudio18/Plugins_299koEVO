<?php

namespace Ecommerce\Controllers;

use Core\Controllers\AdminController;
use Core\Responses\AdminResponse;
use Core\Responses\ApiResponse;
use Utils\Show;
use Core\Lang;
use Utils\Util;

defined('ROOT') or exit('Access denied!');

class EcommerceAdminOrdersController extends AdminController
{
    private $orderManager;

    public function __construct()
    {
        parent::__construct();
        $this->orderManager = new \Ecommerce\Entities\OrderManager();
    }

    private function isValidToken(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }
        if (isset($this->user->token) && hash_equals((string)$this->user->token, (string)$token)) {
            return true;
        }
        return \Core\Security\Csrf::validate($token, false);
    }

    public function list()
    {
        $evaluator = \Core\Navigation\AccessPolicyEvaluator::getInstance();
        $ctx = new \Core\Navigation\ConditionContext($this->user);
        if (!$evaluator->canAccess(new \Core\Navigation\GenericResource('ecommerce:admin'), $ctx)) {
            \Utils\Show::msg(\Core\Lang::get('core-not-authorized'), 'error');
            $this->core->redirect($this->router->generate('admin-home'));
            return;
        }

        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('ecommerce', 'admin-orders-list');
        
        $orders = $this->orderManager->getAll();
        $tpl->set('orders', $orders);
        $tpl->set('token', $this->user->token);
        
        $response->addTemplate($tpl);
        return $response;
    }

    public function view($id = null)
    {
        $response = new AdminResponse();
        $id = (int)($id ?? $this->router->getParam('id') ?? $_REQUEST['id'] ?? 0);
        
        $order = $this->orderManager->getById($id);
        if (!$order) {
            return $this->core->redirect('/admin/ecommerce/orders');
        }
        
        $tpl = $response->createPluginTemplate('ecommerce', 'admin-orders-view');
        $tpl->set('order', $order);
        $tpl->set('token', $this->user->token);
        
        $response->addTemplate($tpl);
        return $response;
    }

    public function updateStatus($id = null)
    {
        $response = new ApiResponse();
        $evaluator = \Core\Navigation\AccessPolicyEvaluator::getInstance();
        $ctx = new \Core\Navigation\ConditionContext($this->user);
        if (!$evaluator->canAccess(new \Core\Navigation\GenericResource('ecommerce:admin'), $ctx)) {
            $response->status = ApiResponse::STATUS_NOT_AUTHORIZED;
            return $response;
        }
        
        $token = $_POST['token'] ?? ($_POST['_csrf'] ?? null);
        if (!$this->isValidToken($token)) {
            $response->status = ApiResponse::STATUS_BAD_REQUEST;
            $response->body = ['message' => Lang::get('core.bad-token')];
            return $response;
        }
        
        $id = (int)($id ?? $this->router->getParam('id') ?? $_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $trackingNumber = isset($_POST['tracking_number']) ? trim((string)$_POST['tracking_number']) : null;
        
        $allowedStatuses = ['pending_payment', 'pending', 'paid', 'processing', 'shipped', 'delivered', 'completed', 'cancelled'];
        if (!in_array($status, $allowedStatuses)) {
            $response->status = ApiResponse::STATUS_BAD_REQUEST;
            $response->body = ['message' => Lang::get('ecommerce.invalid-status')];
            return $response;
        }
        
        if ($this->orderManager->updateStatus($id, $status, $trackingNumber)) {
            $order = $this->orderManager->getById($id);
            if ($order) {
                $this->notifyCustomerStatusChange($order, $status, $trackingNumber);
            }
            $response->status = ApiResponse::STATUS_SUCCESS;
            $response->body = ['message' => Lang::get('ecommerce.status-updated')];
        } else {
            $response->status = ApiResponse::STATUS_ERROR;
            $response->body = ['message' => Lang::get('ecommerce.status-update-error')];
        }
        
        return $response;
    }

    private function notifyCustomerStatusChange(\Ecommerce\Entities\Order $order, string $status, ?string $trackingNumber): void
    {
        $customerEmail = $order->getCustomerEmail();
        if (empty($customerEmail) || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $siteName = (string)($this->core->getConfigVal('siteName') ?: 'Boutique');
        $siteEmail = (string)($this->core->getConfigVal('siteEmail') ?: 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        $orderNumber = $order->getOrderNumber();

        $statusTitles = [
            'paid'       => 'Paiement confirmé pour votre commande #' . $orderNumber,
            'processing' => 'Votre commande #' . $orderNumber . ' est en cours de préparation',
            'shipped'    => 'Votre commande #' . $orderNumber . ' a été expédiée !',
            'delivered'  => 'Votre commande #' . $orderNumber . ' a été livrée',
            'completed'  => 'Votre commande #' . $orderNumber . ' est terminée',
            'cancelled'  => 'Votre commande #' . $orderNumber . ' a été annulée',
        ];

        $subject = '[' . $siteName . '] ' . ($statusTitles[$status] ?? 'Mise à jour de votre commande #' . $orderNumber);
        $msg = "Bonjour " . ($order->getCustomerName() ?: 'Cher client') . ",\n\n";
        $msg .= "Le statut de votre commande #" . $orderNumber . " sur " . $siteName . " vient d'évoluer :\n\n";
        $msg .= "👉 Nouveau statut : " . $order->getStatusLabel() . "\n\n";

        if ($status === 'shipped' && !empty($trackingNumber)) {
            $msg .= "📦 Numéro de suivi de votre colis : " . $trackingNumber . "\n\n";
        }

        $msg .= "Retrouvez vos achats à tout moment dans votre espace client :\n";
        $msg .= Util::urlBuild('/ecommerce/mes-commandes') . "\n\n";
        $msg .= "Merci pour votre confiance,\nL'équipe " . $siteName . "\n";

        @Util::sendEmail($siteEmail, $siteEmail, $customerEmail, $subject, $msg);
    }
}

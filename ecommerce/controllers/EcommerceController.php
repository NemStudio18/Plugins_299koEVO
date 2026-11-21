<?php

namespace Ecommerce\Controllers;

use Core\Controllers\PublicController;
use Core\Responses\PublicResponse;
use Core\Responses\ApiResponse;
use Core\Core;
use Core\Lang;
use Utils\Util;
use Core\Auth\UsersManager;

defined('ROOT') or exit('Access denied!');

class EcommerceController extends PublicController
{
    private $productManager;
    private $categoryManager;
    private $orderManager;

    public function __construct()
    {
        parent::__construct();
        $this->productManager = new \Ecommerce\Entities\ProductManager();
        $this->categoryManager = new \Ecommerce\Entities\CategoryManager();
        $this->orderManager = new \Ecommerce\Entities\OrderManager();
    }

    private function getStripeDriver(): ?\StripeDriver
    {
        $stripeDriverPath = PLUGINS . 'paiement/drivers/StripeDriver.php';
        if (file_exists($stripeDriverPath)) {
            require_once $stripeDriverPath;
            if (class_exists('StripeDriver')) {
                $driver = new \StripeDriver();
                if ($driver->isConfigured()) {
                    return $driver;
                }
            }
        }
        return null;
    }

    public function shop()
    {
        $response = new PublicResponse();
        $tpl = $response->createPluginTemplate('ecommerce', 'shop');
        
        $activeOnly = !UsersManager::isLogged();
        $products = $this->productManager->getAll($activeOnly);
        $categories = $this->categoryManager->getAll();
        
        $tpl->set('products', $products);
        $tpl->set('categories', $categories);
        $tpl->set('isLogged', UsersManager::isLogged());
        $tpl->set('myOrdersUrl', Util::urlBuild('/ecommerce/mes-commandes'));
        
        $response->addTemplate($tpl);
        return $response;
    }

    public function product()
    {
        $response = new PublicResponse();
        $id = $this->router->getParam('id');
        if (is_numeric($id)) {
            $id = (int)$id;
        } else if ($id !== null) {
            $id = trim((string)$id);
        }
        
        $product = $this->productManager->getById($id);
        if (!$product || (!$product->isActive() && !UsersManager::isLogged())) {
            \Utils\Show::msg(Lang::get('ecommerce.product-unavailable') ?: 'Produit indisponible.', 'error');
            return Core::getInstance()->redirect('/shop');
        }
        
        $tpl = $response->createPluginTemplate('ecommerce', 'product');
        $tpl->set('product', $product);
        $tpl->set('token', \Core\Security\Csrf::hiddenField());
        $tpl->set('isLogged', UsersManager::isLogged());
        $tpl->set('myOrdersUrl', Util::urlBuild('/ecommerce/mes-commandes'));
        
        $response->addTemplate($tpl);
        return $response;
    }

    public function category()
    {
        $response = new PublicResponse();
        $id = $this->router->getParam('id');
        if (is_numeric($id)) {
            $id = (int)$id;
        } else if ($id !== null) {
            $id = trim((string)$id);
        }
        
        $category = $this->categoryManager->getById($id);
        if (!$category) {
            return Core::getInstance()->redirect('/shop');
        }
        
        $activeOnly = !UsersManager::isLogged();
        $products = $this->productManager->getByCategory((int)$id, $activeOnly);
        $categories = $this->categoryManager->getAll();
        
        $tpl = $response->createPluginTemplate('ecommerce', 'shop');
        $tpl->set('products', $products);
        $tpl->set('categories', $categories);
        $tpl->set('currentCategory', $category);
        $tpl->set('isLogged', UsersManager::isLogged());
        $tpl->set('myOrdersUrl', Util::urlBuild('/ecommerce/mes-commandes'));
        
        $response->addTemplate($tpl);
        return $response;
    }

    public function cart()
    {
        $response = new PublicResponse();
        $tpl = $response->createPluginTemplate('ecommerce', 'cart');
        
        $items = \Ecommerce\Entities\Cart::getItemsWithProducts();
        $total = \Ecommerce\Entities\Cart::getTotal();
        $plugin = \Core\Plugin\PluginsManager::getInstance()->getPlugin('ecommerce');
        $shippingCost = (float)$plugin->getConfigVal('shippingCost');
        $freeShippingThreshold = (float)$plugin->getConfigVal('freeShippingThreshold');
        
        $finalShipping = ($total >= $freeShippingThreshold) ? 0 : $shippingCost;
        $grandTotal = $total + $finalShipping;
        
        $tpl->set('items', $items);
        $tpl->set('subtotal', $total);
        $tpl->set('shipping', $finalShipping);
        $tpl->set('total', $grandTotal);
        $tpl->set('freeShippingThreshold', $freeShippingThreshold);
        $tpl->set('remainingForFreeShipping', max(0, $freeShippingThreshold - $total));
        $tpl->set('token', \Core\Security\Csrf::hiddenField());
        
        $response->addTemplate($tpl);
        return $response;
    }

    public function addToCart()
    {
        $token = $_POST['token'] ?? ($_POST['_csrf'] ?? '');
        $isAjax = $this->request->isAjax();
        
        if (!\Core\Security\Csrf::validate($token, false)) {
            if ($isAjax) {
                $response = new \Core\Responses\ApiResponse();
                $response->status = \Core\Responses\ApiResponse::STATUS_BAD_REQUEST;
                $response->message = Lang::get('core.bad-token');
                return $response;
            }
            \Utils\Show::msg(Lang::get('core.bad-token'), 'error');
            return Core::getInstance()->redirect('/shop');
        }
        
        $productId = $_POST['product_id'] ?? 0;
        if (is_numeric($productId)) {
            $productId = (int)$productId;
        } else if ($productId !== null) {
            $productId = trim((string)$productId);
        }
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        
        $product = $this->productManager->getById($productId);
        if (!$product) {
            if ($isAjax) {
                $response = new \Core\Responses\ApiResponse();
                $response->status = \Core\Responses\ApiResponse::STATUS_NOT_FOUND;
                $response->message = Lang::get('ecommerce.product-not-found') ?: 'Produit introuvable';
                return $response;
            }
            \Utils\Show::msg(Lang::get('ecommerce.product-not-found') ?: 'Produit introuvable.', 'error');
            return Core::getInstance()->redirect('/shop');
        }
        
        if (!$product->isInStock()) {
            if ($isAjax) {
                $response = new \Core\Responses\ApiResponse();
                $response->status = \Core\Responses\ApiResponse::STATUS_ERROR;
                $response->message = Lang::get('ecommerce.out-of-stock');
                return $response;
            }
            \Utils\Show::msg(Lang::get('ecommerce.out-of-stock') ?: 'Rupture de stock.', 'error');
            return Core::getInstance()->redirect($product->getUrl());
        }
        
        \Ecommerce\Entities\Cart::add((int)$productId, $quantity);
        
        if ($isAjax) {
            $response = new \Core\Responses\ApiResponse();
            $response->status = \Core\Responses\ApiResponse::STATUS_SUCCESS;
            $response->message = Lang::get('ecommerce.added-to-cart') ?: 'Produit ajouté au panier.';
            $response->body = [
                'cartCount' => \Ecommerce\Entities\Cart::getCount(),
                'cartTotal' => \Ecommerce\Entities\Cart::getTotal()
            ];
            return $response;
        }
        
        \Utils\Show::msg(Lang::get('ecommerce.product-added-to-cart') ?: 'Produit ajouté au panier.', 'success');
        return Core::getInstance()->redirect('/cart');
    }

    public function updateCart()
    {
        $response = new \Core\Responses\ApiResponse();
        
        $token = $_POST['token'] ?? ($_POST['_csrf'] ?? '');
        if (!\Core\Security\Csrf::validate($token, false)) {
            $response->status = \Core\Responses\ApiResponse::STATUS_BAD_REQUEST;
            $response->message = Lang::get('core.bad-token');
            return $response;
        }
        
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        if ($quantity <= 0) {
            \Ecommerce\Entities\Cart::remove($productId);
        } else {
            \Ecommerce\Entities\Cart::update($productId, $quantity);
        }
        
        $response->status = \Core\Responses\ApiResponse::STATUS_SUCCESS;
        $response->message = Lang::get('ecommerce.cart-updated');
        
        return $response;
    }

    public function removeFromCart()
    {
        $id = (int)$this->router->getParam('id');
        \Ecommerce\Entities\Cart::remove($id);
        return Core::getInstance()->redirect('/cart');
    }

    public function checkout()
    {
        $response = new PublicResponse();
        
        $items = \Ecommerce\Entities\Cart::getItemsWithProducts();
        if (empty($items)) {
            return Core::getInstance()->redirect('/cart');
        }
        
        $tpl = $response->createPluginTemplate('ecommerce', 'checkout');
        
        $total = \Ecommerce\Entities\Cart::getTotal();
        $plugin = \Core\Plugin\PluginsManager::getInstance()->getPlugin('ecommerce');
        $shippingCost = (float)$plugin->getConfigVal('shippingCost');
        $freeShippingThreshold = (float)$plugin->getConfigVal('freeShippingThreshold');
        
        $finalShipping = ($total >= $freeShippingThreshold) ? 0 : $shippingCost;
        $grandTotal = $total + $finalShipping;

        $stripeDriver = $this->getStripeDriver();
        $hasStripe = ($stripeDriver !== null);
        
        $currentUser = UsersManager::getCurrentUser();

        $tpl->set('items', $items);
        $tpl->set('subtotal', $total);
        $tpl->set('shipping', $finalShipping);
        $tpl->set('total', $grandTotal);
        $tpl->set('hasStripe', $hasStripe);
        $tpl->set('user', $currentUser);
        $tpl->set('token', \Core\Security\Csrf::hiddenField());
        
        $response->addTemplate($tpl);
        return $response;
    }

    public function processCheckout()
    {
        $response = new \Core\Responses\ApiResponse();
        
        $token = $_POST['token'] ?? ($_POST['_csrf'] ?? '');
        if (!\Core\Security\Csrf::validate($token, false)) {
            $response->status = \Core\Responses\ApiResponse::STATUS_BAD_REQUEST;
            $response->message = Lang::get('core.bad-token');
            return $response;
        }
        
        $items = \Ecommerce\Entities\Cart::getItemsWithProducts();
        if (empty($items)) {
            $response->status = \Core\Responses\ApiResponse::STATUS_ERROR;
            $response->message = Lang::get('ecommerce.cart-empty');
            return $response;
        }
        
        // Validation des stocks
        foreach ($items as $item) {
            $product = $item['product'];
            if (!$product->isInStock() || $item['quantity'] > $product->getStock()) {
                $response->status = \Core\Responses\ApiResponse::STATUS_ERROR;
                $response->message = Lang::get('ecommerce.insufficient-stock');
                return $response;
            }
        }
        
        $total = \Ecommerce\Entities\Cart::getTotal();
        $plugin = \Core\Plugin\PluginsManager::getInstance()->getPlugin('ecommerce');
        $shippingCost = (float)$plugin->getConfigVal('shippingCost');
        $freeShippingThreshold = (float)$plugin->getConfigVal('freeShippingThreshold');
        $finalShipping = ($total >= $freeShippingThreshold) ? 0 : $shippingCost;
        $grandTotal = $total + $finalShipping;

        $paymentMethod = $_POST['payment_method'] ?? 'bank_transfer';
        $currentUser = UsersManager::getCurrentUser();
        
        $orderData = [
            'user_id'          => $currentUser ? (int)$currentUser->id : null,
            'customer_name'    => trim((string)($_POST['customer_name'] ?? '')),
            'customer_email'   => trim((string)($_POST['customer_email'] ?? '')),
            'customer_address' => trim((string)($_POST['customer_address'] ?? '')),
            'customer_phone'   => trim((string)($_POST['customer_phone'] ?? '')),
            'total'            => $grandTotal,
            'status'           => ($paymentMethod === 'cash_on_delivery') ? 'processing' : 'pending_payment',
            'payment_method'   => $paymentMethod,
        ];
        
        $orderItems = [];
        foreach ($items as $item) {
            $orderItems[] = [
                'product_id'   => $item['product']->getId(),
                'product_name' => $item['product']->getName(),
                'quantity'     => $item['quantity'],
                'price'        => $item['product']->getPrice(),
            ];
        }
        
        $order = $this->orderManager->create($orderData, $orderItems);
        
        if ($order) {
            // Décrémentation immédiate du stock
            foreach ($items as $item) {
                $this->productManager->decreaseStock($item['product']->getId(), $item['quantity']);
            }
            
            \Ecommerce\Entities\Cart::clear();

            // Paiement par carte bancaire via Stripe si sélectionné
            if ($paymentMethod === 'stripe') {
                $stripeDriver = $this->getStripeDriver();
                if ($stripeDriver) {
                    $siteName = (string)($this->core->getConfigVal('siteName') ?: 'Boutique');
                    $successUrl = Util::urlBuild('/order/success/' . $order->getId());
                    $cancelUrl = Util::urlBuild('/checkout');
                    $description = "Commande #" . $order->getOrderNumber() . " sur " . $siteName;

                    $stripeUrl = $stripeDriver->createSession(
                        $grandTotal,
                        $description,
                        $successUrl,
                        $cancelUrl,
                        [
                            'type'     => 'ecommerce_order',
                            'order_id' => (string)$order->getId(),
                        ]
                    );

                    if ($stripeUrl) {
                        $response->status = \Core\Responses\ApiResponse::STATUS_SUCCESS;
                        $response->message = 'Redirection vers la passerelle de paiement sécurisée...';
                        $response->redirect = $stripeUrl;
                        return $response;
                    }
                }
            }

            // Envoi des emails de confirmation et notification
            $this->sendOrderEmails($order, $orderItems, $orderData, $plugin);
            
            $response->status = \Core\Responses\ApiResponse::STATUS_SUCCESS;
            $response->message = Lang::get('ecommerce.order-created');
            $response->redirect = Util::urlBuild('/order/success/' . $order->getId());
        } else {
            $response->status = \Core\Responses\ApiResponse::STATUS_ERROR;
            $response->message = Lang::get('ecommerce.order-create-error');
        }
        
        return $response;
    }

    public function orderSuccess($id)
    {
        $order = $this->orderManager->findById((int)$id);
        if (!$order) {
            \Utils\Show::msg('Commande introuvable.', 'error');
            return $this->core->redirect(Util::urlBuild('/shop'));
        }

        // Si retour de paiement Stripe (avec session_id dans l'URL)
        if (!empty($_GET['session_id']) && $order->getStatus() !== 'paid') {
            $this->orderManager->updateStatus($order->getId(), 'paid');
            $order = $this->orderManager->findById((int)$id);
            \Utils\Show::msg('Paiement par carte validé avec succès ! Votre commande est confirmée.', 'success');
        }

        $plugin = \Core\Plugin\PluginsManager::getInstance()->getPlugin('ecommerce');
        $currency = $plugin ? $plugin->getConfigVal('currency') : '€';

        $items = $this->orderManager->getOrderItems($order->getId());

        $response = new PublicResponse();
        $tpl = $response->createPluginTemplate('ecommerce', 'order-success');
        $tpl->set('order', $order);
        $tpl->set('items', $items);
        $tpl->set('currency', $currency ?: '€');

        $bankDetails = [
            'owner' => $this->core->getConfigVal('siteName') ?: 'Boutique en ligne',
            'iban' => $plugin ? ($plugin->getConfigVal('bank_iban') ?: 'FR76 1234 5678 9012 3456 7890 123') : '',
            'bic' => $plugin ? ($plugin->getConfigVal('bank_bic') ?: 'BNPAFRPPXXX') : '',
        ];
        $tpl->set('bankDetails', $bankDetails);

        $response->addTemplate($tpl);
        return $response;
    }

    /**
     * Espace Client : Mes commandes.
     */
    public function myOrders()
    {
        $currentUser = UsersManager::getCurrentUser();
        if (!$currentUser) {
            \Utils\Show::msg('Veuillez vous connecter pour consulter vos commandes.', 'info');
            return $this->core->redirect('/users/login?redirect=' . urlencode(Util::urlBuild('/ecommerce/mes-commandes')));
        }

        $orders = $this->orderManager->getByUser((int)$currentUser->id, (string)$currentUser->email);
        $plugin = \Core\Plugin\PluginsManager::getInstance()->getPlugin('ecommerce');
        $currency = $plugin ? $plugin->getConfigVal('currency') : '€';

        $response = new PublicResponse();
        $tpl = $response->createPluginTemplate('ecommerce', 'my-orders');
        $response->setTitle('<i class="fa-solid fa-box-archive"></i> Mes commandes');

        $tpl->set('orders', $orders);
        $tpl->set('currency', $currency ?: '€');
        $tpl->set('shopUrl', Util::urlBuild('/shop'));

        $response->addTemplate($tpl);
        return $response;
    }

    private function sendOrderEmails($order, array $orderItems, array $orderData, $plugin): void
    {
        $siteName = $this->core->getConfigVal('siteName') ?: '299koEVO';
        $siteEmail = $this->core->getConfigVal('siteEmail') ?: 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $customerEmail = trim($orderData['customer_email'] ?? '');
        $currency = $plugin ? $plugin->getConfigVal('currency') : '€';

        $itemsText = "";
        foreach ($orderItems as $item) {
            $lineTotal = (float)$item['price'] * (int)$item['quantity'];
            $itemsText .= "- " . $item['product_name'] . " x" . $item['quantity'] . " : " . number_format($lineTotal, 2) . " " . $currency . "\n";
        }

        // 1. Email au client
        if (!empty($customerEmail) && filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            $clientSubject = "[" . $siteName . "] Confirmation de votre commande #" . $order->getId();
            $clientMsg = "Bonjour " . ($orderData['customer_name'] ?: 'Cher client') . ",\n\n";
            $clientMsg .= "Nous vous remercions pour votre commande #" . $order->getId() . " passée sur notre boutique " . $siteName . ".\n\n";
            $clientMsg .= "Détail de vos articles :\n" . $itemsText . "\n";
            $clientMsg .= "Total TTC : " . number_format((float)$orderData['total'], 2) . " " . $currency . "\n";
            $clientMsg .= "Mode de paiement : " . $order->getPaymentMethodLabel() . "\n\n";
            $clientMsg .= "Adresse de livraison :\n" . $orderData['customer_address'] . "\n\n";
            if ($orderData['payment_method'] === 'bank_transfer') {
                $iban = $plugin ? ($plugin->getConfigVal('bank_iban') ?: 'FR76 1234 5678 9012 3456 7890 123') : '';
                $bic = $plugin ? ($plugin->getConfigVal('bank_bic') ?: 'BNPAFRPPXXX') : '';
                $clientMsg .= "Pour finaliser votre commande, merci d'effectuer votre virement avec la référence CMD-" . $order->getId() . " :\n";
                $clientMsg .= "IBAN : " . $iban . "\nBIC : " . $bic . "\n\n";
            }
            $clientMsg .= "À très bientôt sur " . $siteName . " !\n";

            @Util::sendEmail($siteEmail, $siteEmail, $customerEmail, $clientSubject, $clientMsg);
        }

        // 2. Notification à l'administrateur
        if (!empty($siteEmail) && filter_var($siteEmail, FILTER_VALIDATE_EMAIL)) {
            $adminSubject = "[Nouvelle Commande #" . $order->getId() . "] " . ($orderData['customer_name'] ?: 'Client');
            $adminMsg = "Une nouvelle commande #" . $order->getId() . " vient d'être passée sur votre boutique.\n\n";
            $adminMsg .= "Client : " . $orderData['customer_name'] . " (" . $orderData['customer_email'] . ")\n";
            $adminMsg .= "Téléphone : " . ($orderData['customer_phone'] ?: 'Non renseigné') . "\n";
            $adminMsg .= "Total : " . number_format((float)$orderData['total'], 2) . " " . $currency . "\n";
            $adminMsg .= "Mode de règlement : " . $order->getPaymentMethodLabel() . "\n\n";
            $adminMsg .= "Articles commandés :\n" . $itemsText . "\n";
            $adminMsg .= "Consultez la commande dans l'administration : " . Util::urlBuild('/admin/ecommerce/orders/view/' . $order->getId()) . "\n";

            @Util::sendEmail($siteEmail, $customerEmail ?: $siteEmail, $siteEmail, $adminSubject, $adminMsg);
        }
    }
}

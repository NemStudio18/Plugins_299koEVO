<?php

namespace Ecommerce\Controllers;

use Core\Controllers\AdminController;
use Core\Responses\AdminResponse;
use Core\Responses\ApiResponse;
use Utils\Show;
use Core\Lang;
use Utils\Util;

defined('ROOT') or exit('Access denied!');

class EcommerceAdminProductsController extends AdminController
{
    private $productManager;
    private $categoryManager;

    public function __construct()
    {
        parent::__construct();
        $this->productManager = new \Ecommerce\Entities\ProductManager();
        $this->categoryManager = new \Ecommerce\Entities\CategoryManager();
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
        $tpl = $response->createPluginTemplate('ecommerce', 'admin-products-list');
        
        $products = $this->productManager->getAll();
        $tpl->set('products', $products);
        $tpl->set('token', $this->user->token);
        
        $response->addTemplate($tpl);
        return $response;
    }

    public function add()
    {
        $evaluator = \Core\Navigation\AccessPolicyEvaluator::getInstance();
        $ctx = new \Core\Navigation\ConditionContext($this->user);
        if (!$evaluator->canAccess(new \Core\Navigation\GenericResource('ecommerce:admin'), $ctx)) {
            \Utils\Show::msg(\Core\Lang::get('core-not-authorized'), 'error');
            $this->core->redirect($this->router->generate('admin-home'));
            return;
        }
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('ecommerce', 'admin-products-edit');
        
        $categories = $this->categoryManager->getAll();
        $tpl->set('product', null);
        $tpl->set('categories', $categories);
        $tpl->set('token', $this->user->token);
        
        $response->addTemplate($tpl);
        return $response;
    }

    public function addSend()
    {
        $evaluator = \Core\Navigation\AccessPolicyEvaluator::getInstance();
        $ctx = new \Core\Navigation\ConditionContext($this->user);
        if (!$evaluator->canAccess(new \Core\Navigation\GenericResource('ecommerce:admin'), $ctx)) {
            if ($this->request->isAjax()) {
                $response = new ApiResponse();
                $response->status = ApiResponse::STATUS_NOT_AUTHORIZED;
                return $response;
            }
            \Utils\Show::msg(\Core\Lang::get('core-not-authorized'), 'error');
            $this->core->redirect($this->router->generate('admin-ecommerce-products'));
            return null;
        }
        
        $token = $_POST['token'] ?? ($_POST['_csrf'] ?? null);
        if (!$this->isValidToken($token)) {
            if ($this->request->isAjax()) {
                $response = new ApiResponse();
                $response->status = ApiResponse::STATUS_BAD_REQUEST;
                $response->body = ['message' => Lang::get('core.bad-token')];
                return $response;
            }
            Show::msg(Lang::get('core.bad-token'), 'error');
            $this->core->redirect($this->router->generate('admin-ecommerce-products-add'));
            return null;
        }
        
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'stock' => (int)($_POST['stock'] ?? 0),
            'image' => trim($_POST['image'] ?? ''),
            'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'active' => isset($_POST['active']) ? 1 : 0,
        ];
        
        if (empty($data['name']) || $data['price'] < 0) {
            if ($this->request->isAjax()) {
                $response = new ApiResponse();
                $response->status = ApiResponse::STATUS_BAD_REQUEST;
                $response->body = ['message' => Lang::get('ecommerce.invalid-data')];
                return $response;
            }
            Show::msg(Lang::get('ecommerce.invalid-data') ?: 'Données invalides.', 'error');
            $this->core->redirect($this->router->generate('admin-ecommerce-products-add'));
            return null;
        }
        
        $product = $this->productManager->create($data);
        if ($product) {
            if ($this->request->isAjax()) {
                $response = new ApiResponse();
                $response->status = ApiResponse::STATUS_SUCCESS;
                $response->body = [
                    'message' => Lang::get('ecommerce.product-created'),
                    'redirect' => Util::urlBuild('/admin/ecommerce/products')
                ];
                return $response;
            }
            Show::msg(Lang::get('ecommerce.product-created') ?: 'Produit créé avec succès.', 'success');
            $this->core->redirect($this->router->generate('admin-ecommerce-products'));
            return null;
        } else {
            if ($this->request->isAjax()) {
                $response = new ApiResponse();
                $response->status = ApiResponse::STATUS_ERROR;
                $response->body = ['message' => Lang::get('ecommerce.product-create-error')];
                return $response;
            }
            Show::msg(Lang::get('ecommerce.product-create-error') ?: 'Erreur lors de la création du produit.', 'error');
            $this->core->redirect($this->router->generate('admin-ecommerce-products-add'));
            return null;
        }
    }

    public function edit($id = null)
    {
        $evaluator = \Core\Navigation\AccessPolicyEvaluator::getInstance();
        $ctx = new \Core\Navigation\ConditionContext($this->user);
        if (!$evaluator->canAccess(new \Core\Navigation\GenericResource('ecommerce:admin'), $ctx)) {
            \Utils\Show::msg(\Core\Lang::get('core-not-authorized'), 'error');
            $this->core->redirect($this->router->generate('admin-home'));
            return null;
        }
        $id = $id ?? $this->router->getParam('id') ?? $_REQUEST['id'] ?? null;
        if (is_numeric($id)) {
            $id = (int)$id;
        } else if ($id !== null) {
            $id = trim((string)$id);
        }
        
        $product = $this->productManager->getById($id);
        if (!$product) {
            Show::msg('Produit introuvable.', 'error');
            $this->core->redirect($this->router->generate('admin-ecommerce-products'));
            return null;
        }
        
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('ecommerce', 'admin-products-edit');
        $categories = $this->categoryManager->getAll();
        
        $tpl->set('product', $product);
        $tpl->set('categories', $categories);
        $tpl->set('token', $this->user->token);
        
        $response->addTemplate($tpl);
        return $response;
    }

    public function editSend($id = null)
    {
        $evaluator = \Core\Navigation\AccessPolicyEvaluator::getInstance();
        $ctx = new \Core\Navigation\ConditionContext($this->user);
        if (!$evaluator->canAccess(new \Core\Navigation\GenericResource('ecommerce:admin'), $ctx)) {
            if ($this->request->isAjax()) {
                $response = new ApiResponse();
                $response->status = ApiResponse::STATUS_NOT_AUTHORIZED;
                return $response;
            }
            \Utils\Show::msg(\Core\Lang::get('core-not-authorized'), 'error');
            $this->core->redirect($this->router->generate('admin-ecommerce-products'));
            return null;
        }
        
        $id = $id ?? $this->router->getParam('id') ?? $_POST['id'] ?? null;
        if (is_numeric($id)) {
            $id = (int)$id;
        } else if ($id !== null) {
            $id = trim((string)$id);
        }
        $token = $_POST['token'] ?? ($_POST['_csrf'] ?? null);
        if (!$this->isValidToken($token)) {
            if ($this->request->isAjax()) {
                $response = new ApiResponse();
                $response->status = ApiResponse::STATUS_BAD_REQUEST;
                $response->body = ['message' => Lang::get('core.bad-token')];
                return $response;
            }
            Show::msg(Lang::get('core.bad-token'), 'error');
            $this->core->redirect($this->router->generate('admin-ecommerce-products-edit', ['id' => $id]));
            return null;
        }
        
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'stock' => (int)($_POST['stock'] ?? 0),
            'image' => trim($_POST['image'] ?? ''),
            'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'active' => isset($_POST['active']) ? 1 : 0,
        ];
        
        if ($this->productManager->update($id, $data)) {
            if ($this->request->isAjax()) {
                $response = new ApiResponse();
                $response->status = ApiResponse::STATUS_SUCCESS;
                $response->body = [
                    'message' => Lang::get('ecommerce.product-updated'),
                    'redirect' => Util::urlBuild('/admin/ecommerce/products')
                ];
                return $response;
            }
            Show::msg(Lang::get('ecommerce.product-updated') ?: 'Produit mis à jour avec succès.', 'success');
            $this->core->redirect($this->router->generate('admin-ecommerce-products'));
            return null;
        } else {
            if ($this->request->isAjax()) {
                $response = new ApiResponse();
                $response->status = ApiResponse::STATUS_ERROR;
                $response->body = ['message' => Lang::get('ecommerce.product-update-error')];
                return $response;
            }
            Show::msg(Lang::get('ecommerce.product-update-error') ?: 'Erreur lors de la mise à jour du produit.', 'error');
            $this->core->redirect($this->router->generate('admin-ecommerce-products-edit', ['id' => $id]));
            return null;
        }
    }

    public function delete($id = null)
    {
        $evaluator = \Core\Navigation\AccessPolicyEvaluator::getInstance();
        $ctx = new \Core\Navigation\ConditionContext($this->user);
        if (!$evaluator->canAccess(new \Core\Navigation\GenericResource('ecommerce:admin'), $ctx)) {
            if ($this->request->isAjax()) {
                $response = new ApiResponse();
                $response->status = ApiResponse::STATUS_NOT_AUTHORIZED;
                return $response;
            }
            \Utils\Show::msg(\Core\Lang::get('core-not-authorized'), 'error');
            $this->core->redirect($this->router->generate('admin-ecommerce-products'));
            return null;
        }
        
        $token = $_GET['token'] ?? ($_POST['token'] ?? ($_GET['_csrf'] ?? ($_POST['_csrf'] ?? null)));
        if (!$this->isValidToken($token)) {
            if ($this->request->isAjax()) {
                $response = new ApiResponse();
                $response->status = ApiResponse::STATUS_BAD_REQUEST;
                $response->body = ['message' => Lang::get('core.bad-token')];
                return $response;
            }
            Show::msg(Lang::get('core.bad-token') ?: 'Jeton de sécurité invalide ou session expirée.', 'error');
            $this->core->redirect($this->router->generate('admin-ecommerce-products'));
            return null;
        }

        $id = $id ?? $this->router->getParam('id') ?? $_REQUEST['id'] ?? null;
        if (is_numeric($id)) {
            $id = (int)$id;
        } else if ($id !== null) {
            $id = trim((string)$id);
        }
        $success = $this->productManager->delete($id);
        
        if ($this->request->isAjax()) {
            $response = new ApiResponse();
            if ($success) {
                $response->status = ApiResponse::STATUS_SUCCESS;
                $response->body = ['message' => Lang::get('ecommerce.product-deleted')];
            } else {
                $response->status = ApiResponse::STATUS_ERROR;
                $response->body = ['message' => Lang::get('ecommerce.product-delete-error')];
            }
            return $response;
        }
        
        if ($success) {
            Show::msg(Lang::get('ecommerce.product-deleted') ?: 'Produit supprimé avec succès.', 'success');
        } else {
            Show::msg(Lang::get('ecommerce.product-delete-error') ?: 'Erreur lors de la suppression du produit.', 'error');
        }
        $this->core->redirect($this->router->generate('admin-ecommerce-products'));
        return null;
    }
}

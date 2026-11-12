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

/**
 * @copyright (C) 2025, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

class FaqAdminController extends AdminController
{
    private function checkAdminAccess(): bool
    {
        $evaluator = AccessPolicyEvaluator::getInstance();
        $ctx = new ConditionContext($this->user);
        if (!$evaluator->canAccess(new GenericResource('faq:admin'), $ctx)) {
            $this->core->error404();
            return false;
        }
        return true;
    }

    public function list()
    {
        if (!$this->checkAdminAccess()) {
            return null;
        }

        $faqManager = new FaqManager();
        $questions = $faqManager->getQuestions();

        $this->runPlugin->setMainTitle(Lang::get('faq-admin.list-title'));
        $this->runPlugin->setTitleTag(Lang::get('faq-admin.list-title'));

        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('faq', 'admin-list');

        $questionsData = [];
        foreach ($questions as $q) {
            $questionsData[] = [
                'id' => $q->getId(),
                'question' => $q->getQuestion(),
                'answer' => $q->getAnswer(),
                'category' => $q->getCategory(),
                'order' => $q->getOrder(),
                'votes' => $q->getVotes(),
                'active' => $q->isActive(),
                'date' => Util::FormatDate($q->getDate(), 'en', 'fr')
            ];
        }

        $categories = $faqManager->getCategories();
        $configCategories = json_decode($this->runPlugin->getConfigVal('categories') ?: '[]', true);
        if (!is_array($configCategories)) {
            $configCategories = [];
        }

        $tpl->set('questions', $questionsData);
        $tpl->set('categories', array_unique(array_merge($categories, $configCategories)));
        $tpl->set('token', $this->user->token);
        
        $response->addTemplate($tpl);
        return $response;
    }

    public function edit($id = null)
    {
        if (!$this->checkAdminAccess()) {
            return null;
        }
        $faqManager = new FaqManager();
        $question = $id ? $faqManager->create($id) : new FaqQuestion();

        if (!$question) {
            Show::msg(Lang::get('core-item-not-found'), 'error');
            return $this->list();
        }

        $this->runPlugin->setMainTitle($id ? Lang::get('faq-admin.edit-title') : Lang::get('faq-admin.add-title'));
        $this->runPlugin->setTitleTag($this->runPlugin->getMainTitle());

        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('faq', 'admin-edit');

        $categories = $faqManager->getCategories();
        $configCategories = json_decode($this->runPlugin->getConfigVal('categories') ?: '[]', true);
        if (!is_array($configCategories)) {
            $configCategories = [];
        }
        $allCategories = array_unique(array_merge($categories, $configCategories));
        sort($allCategories);

        $tpl->set('question', $question);
        $tpl->set('categories', $allCategories);
        $tpl->set('saveUrl', $this->router->generate('faq-admin-save'));
        $tpl->set('token', $this->user->token);
        
        $response->addTemplate($tpl);
        return $response;
    }

    public function save()
    {
        if (!$this->checkAdminAccess()) {
            return null;
        }

        if ($this->user->token !== ($_POST['token'] ?? '') && !Csrf::validate($_POST['_csrf'] ?? null, false)) {
            Show::msg(Lang::get('core-invalid-token') ?: 'Jeton invalide.', 'error');
            return $this->list();
        }

        $faqManager = new FaqManager();
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : null;
        $question = $id ? $faqManager->create($id) : new FaqQuestion();

        if (!$question) {
            Show::msg(Lang::get('core-item-not-found'), 'error');
            return $this->list();
        }

        $question->setQuestion($_POST['question'] ?? '');
        $question->setAnswer($this->core->callHook('beforeSaveEditor', $_POST['answer'] ?? ''));
        $question->setCategory($_POST['category'] ?? '');
        $question->setOrder(intval($_POST['order'] ?? 0));
        $question->setActive(isset($_POST['active']) ? '1' : '0');

        if ($id) {
            $question->setId($id);
        }

        if ($faqManager->saveQuestion($question)) {
            Show::msg(Lang::get('core-changes-saved'), 'success');
        } else {
            Show::msg(Lang::get('core-changes-not-saved'), 'error');
        }

        header('location:' . $this->router->generate('faq-admin-list'));
        die();
    }

    public function delete($id)
    {
        if (!$this->checkAdminAccess()) {
            return null;
        }

        if ($this->user->token !== ($_POST['token'] ?? '') && !Csrf::validate($_POST['_csrf'] ?? null, false)) {
            Show::msg(Lang::get('core-invalid-token') ?: 'Jeton invalide.', 'error');
            return $this->list();
        }

        $faqManager = new FaqManager();
        if ($faqManager->deleteQuestion($id)) {
            Show::msg(Lang::get('faq-admin.delete-success'), 'success');
        } else {
            Show::msg(Lang::get('faq-admin.delete-error'), 'error');
        }

        header('location:' . $this->router->generate('faq-admin-list'));
        die();
    }
}


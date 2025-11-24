<?php

use Core\Controllers\PublicController;
use Core\Responses\PublicResponse;
use Utils\Show;
use Core\Lang;
use Core\Core;

/**
 * @copyright (C) 2025, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

class NewsletterController extends PublicController
{
    public function home()
    {
        $this->runPlugin->setMainTitle($this->runPlugin->getConfigVal('pageTitle') ?: Lang::get('newsletter.name'));
        $this->runPlugin->setTitleTag($this->runPlugin->getConfigVal('pageTitle') ?: Lang::get('newsletter.name'));

        $response = new PublicResponse();
        $tpl = $response->createPluginTemplate('newsletter', 'subscribe');

        $tpl->set('subscribeUrl', $this->router->generate('newsletter-subscribe'));
        $tpl->set('subscriptionMessage', $this->runPlugin->getConfigVal('subscriptionMessage'));
        
        $response->addTemplate($tpl);
        return $response;
    }

    public function subscribe()
    {
        $newsletterManager = new NewsletterManager();
        
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Show::msg(Lang::get('newsletter.invalid-email'), 'error');
            Core::getInstance()->redirect($this->router->generate('home'));
            return;
        }

        // Vérification RGPD
        $rgpdAccept = isset($_POST['rgpd_accept']) ? $_POST['rgpd_accept'] : '';
        if (empty($rgpdAccept) || $rgpdAccept !== '1') {
            Show::msg(Lang::get('newsletter.rgpd-required'), 'error');
            Core::getInstance()->redirect($this->router->generate('home'));
            return;
        }

        $existing = $newsletterManager->findByEmail($email);
        
        if ($existing && $existing->isActive()) {
            Show::msg($this->runPlugin->getConfigVal('alreadySubscribedMessage') ?: Lang::get('newsletter.already-subscribed'), 'error');
            Core::getInstance()->redirect($this->router->generate('home'));
            return;
        }

        if ($existing && !$existing->isActive()) {
            // Réactivation
            $existing->setActive('1');
            $newsletterManager->saveSubscriber($existing);
        } else {
            // Nouvel abonné
            $subscriber = new NewsletterSubscriber();
            $subscriber->setEmail($email);
            $newsletterManager->saveSubscriber($subscriber);
        }

        Show::msg($this->runPlugin->getConfigVal('subscriptionMessage') ?: Lang::get('newsletter.subscription-success'), 'success');
        Core::getInstance()->redirect($this->router->generate('home'));
    }

    public function unsubscribe($token)
    {
        $newsletterManager = new NewsletterManager();
        $subscriber = $newsletterManager->findByToken($token);
        
        if (!$subscriber) {
            Show::msg(Lang::get('newsletter.invalid-token'), 'error');
            Core::getInstance()->redirect($this->router->generate('newsletter-home'));
            return;
        }

        $response = new PublicResponse();
        $tpl = $response->createPluginTemplate('newsletter', 'unsubscribe');

        $this->runPlugin->setMainTitle(Lang::get('newsletter.unsubscribe-title'));
        $this->runPlugin->setTitleTag(Lang::get('newsletter.unsubscribe-title'));

        $tpl->set('token', $token);
        $tpl->set('email', $subscriber->getEmail());
        $tpl->set('unsubscribeUrl', $this->router->generate('newsletter-unsubscribe-post'));
        
        $response->addTemplate($tpl);
        return $response;
    }

    public function unsubscribePost()
    {
        $token = filter_var($_POST['token'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        if (empty($token)) {
            Show::msg(Lang::get('newsletter.invalid-token'), 'error');
            return $this->home();
        }

        $newsletterManager = new NewsletterManager();
        $subscriber = $newsletterManager->findByToken($token);
        
        if (!$subscriber) {
            Show::msg(Lang::get('newsletter.invalid-token'), 'error');
            return $this->home();
        }

        $subscriber->setActive('0');
        $newsletterManager->saveSubscriber($subscriber);

        Show::msg($this->runPlugin->getConfigVal('unsubscriptionMessage') ?: Lang::get('newsletter.unsubscription-success'), 'success');
        Core::getInstance()->redirect($this->router->generate('newsletter-home'));
    }
}


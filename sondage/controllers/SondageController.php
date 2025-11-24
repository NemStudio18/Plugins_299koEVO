<?php

use Core\Controllers\PublicController;
use Core\Responses\PublicResponse;
use Utils\Show;
use Core\Lang;
use Utils\VoteProtection;
use Core\Auth\UsersManager;
use Core\Plugin\PluginsManager;

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

class SondageController extends PublicController
{
    public function home()
    {
        $sondageManager = new SondageManager();
        $activeSondages = $sondageManager->getActiveItems();

        $this->runPlugin->setMainTitle(Lang::get('sondage.name'));
        $this->runPlugin->setTitleTag(Lang::get('sondage.name'));

        $response = new PublicResponse();
        $tpl = $response->createPluginTemplate('sondage', 'list');

        $sondages = [];
        foreach ($activeSondages as $sondage) {
            $totalVotes = $sondage->getTotalVotes();
            $hasVoted = $this->hasUserVoted($sondage);
            
            // Préparer les stats si déjà voté
            $optionsStats = [];
            if ($hasVoted && $totalVotes > 0) {
                $allOptions = $sondage->getOptions();
                foreach ($allOptions as $index => $option) {
                    $votes = $sondage->getVotesForOption($index);
                    $percentage = round(($votes / $totalVotes) * 100, 1);
                    $optionsStats[] = [
                        'text' => $option,
                        'votes' => $votes,
                        'percentage' => $percentage
                    ];
                }
            }
            
            $sondages[] = [
                'id' => $sondage->getId(),
                'title' => $sondage->getTitle(),
                'options' => $sondage->getOptions(),
                'totalVotes' => $totalVotes,
                'votesCountText' => Lang::get('sondage.votes-count', $totalVotes),
                'hasVoted' => $hasVoted,
                'optionsStats' => $optionsStats,
                'url' => $this->router->generate('sondage-read', ['id' => $sondage->getId()])
            ];
        }

        $tpl->set('sondages', $sondages);
        $tpl->set('sondageManager', $sondageManager);
        $response->addTemplate($tpl);
        return $response;
    }

    public function read($id)
    {
        $sondageManager = new SondageManager();
        $sondage = $sondageManager->create($id);

        if (!$sondage || $sondage->getActive() != '1') {
            $this->core->error404();
        }

        $this->runPlugin->setMainTitle($sondage->getTitle());
        $this->runPlugin->setTitleTag($sondage->getTitle());

        $response = new PublicResponse();
        $tpl = $response->createPluginTemplate('sondage', 'read');

        $optionsData = [];
        $allOptions = $sondage->getOptions();
        foreach ($allOptions as $index => $option) {
            $votes = $sondage->getVotesForOption($index);
            $total = $sondage->getTotalVotes();
            $percentage = $total > 0 ? round(($votes / $total) * 100, 1) : 0;
            
            $optionsData[] = [
                'index' => $index,
                'text' => $option,
                'votes' => $votes,
                'percentage' => $percentage
            ];
        }

        $totalVotes = $sondage->getTotalVotes();
        $hasVoted = $this->hasUserVoted($sondage);
        
        $tpl->set('sondage', $sondage);
        $tpl->set('optionsData', $optionsData);
        $tpl->set('hasVoted', $hasVoted);
        $tpl->set('voteUrl', $this->router->generate('sondage-vote', ['id' => $sondage->getId()]));
        $tpl->set('allOptions', $allOptions);
        $tpl->set('totalVotesText', Lang::get('sondage.votes-count', $totalVotes));
        $tpl->set('listUrl', $this->router->generate('sondage-home'));
        $response->addTemplate($tpl);
        return $response;
    }

    public function vote($id)
    {
        $sondageManager = new SondageManager();
        $sondage = $sondageManager->create($id);

        if (!$sondage || $sondage->getActive() != '1') {
            Show::msg(Lang::get('sondage.vote-error'), 'error');
            return $this->read($id);
        }

        // Vérifier si déjà voté
        if ($this->hasUserVoted($sondage)) {
            Show::msg(Lang::get('sondage.already-voted'), 'warning');
            return $this->read($id);
        }

        // Vérifier si login requis
        if ($this->runPlugin->getConfigVal('requireLogin') == '1' && !IS_LOGGED) {
            Show::msg(Lang::get('sondage.require-login'), 'error');
            return $this->read($id);
        }

        // Récupérer et valider l'email (obligatoire)
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        if (empty($email) || !VoteProtection::validateEmail($email)) {
            Show::msg(Lang::get('sondage.email-required'), 'error');
            return $this->read($id);
        }

        // Vérifier l'acceptation RGPD
        if (!VoteProtection::hasAcceptedRGPD($_POST)) {
            Show::msg(Lang::get('sondage.rgpd-required'), 'error');
            return $this->read($id);
        }

        // Récupérer l'option votée
        $optionIndex = isset($_POST['option']) ? intval($_POST['option']) : -1;
        if ($optionIndex < 0 || $optionIndex >= count($sondage->getOptions())) {
            Show::msg(Lang::get('sondage.vote-error'), 'error');
            return $this->read($id);
        }

        // Consentement pour stocker l'email en clair (pour les stats) ou seulement le hash
        $emailConsentStats = isset($_POST['email_consent_stats']) && $_POST['email_consent_stats'] == '1';
        $emailToStore = $emailConsentStats ? $email : null; // Si pas de consentement, null (seul le hash sera utilisé)

        // Ajouter le vote avec fingerprint (pas d'IP en clair)
        $userId = IS_LOGGED ? UsersManager::getCurrentUser()->id : null;
        $fingerprint = VoteProtection::getFingerprint();
        $sondage->addVote($optionIndex, $userId, $fingerprint, $emailToStore);
        $sondageManager->saveSondage($sondage);

        Show::msg(Lang::get('sondage.vote-success'), 'success');
        return $this->read($id);
    }

    private function hasUserVoted($sondage)
    {
        $runPlugin = PluginsManager::getInstance()->getPlugin('sondage');
        $allowMultiple = $runPlugin->getConfigVal('allowMultipleVotes') == '1';
        
        if ($allowMultiple) {
            return false;
        }

        $userId = IS_LOGGED ? UsersManager::getCurrentUser()->id : null;
        $fingerprint = VoteProtection::getFingerprint();
        
        return $sondage->hasUserVoted($userId, $fingerprint);
    }
}

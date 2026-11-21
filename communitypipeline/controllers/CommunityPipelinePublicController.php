<?php

use Core\Controllers\PublicController;
use Core\Responses\PublicResponse;
use Utils\Show;
use Utils\Util;

defined('ROOT') or exit('Access denied!');

class CommunityPipelinePublicController extends PublicController
{
    private string $hubApiUrl;

    public function __construct()
    {
        parent::__construct();
        $url = $this->core->getConfigVal('marketplaceUrl');
        $this->hubApiUrl = !empty($url) ? rtrim($url, '/') : 'https://299ko-api.flexcb.fr';
    }

    /**
     * Affiche le formulaire public de soumission
     */
    public function form()
    {
        $response = new PublicResponse();
        $tpl = $response->createPluginTemplate('communitypipeline', 'public-form');

        $tpl->set('hubApiUrl', $this->hubApiUrl);
        $tpl->set('submitUrl', $this->router->generate('communitypipeline-submit'));
        $tpl->set('tokenField', \Core\Security\Csrf::hiddenField());

        $response->setTitle('Soumettre un Plugin ou Thème - 299ko EVO');
        $response->addTemplate($tpl);
        return $response;
    }

    /**
     * Traite l'envoi du formulaire et transmet au Hub API
     */
    public function submit()
    {
        if (!\Core\Security\Csrf::validate($_POST['_csrf'] ?? null, false)) {
            Show::msg('Session expirée ou jeton CSRF invalide. Veuillez réessayer.', 'error');
            $this->core->redirect($this->router->generate('communitypipeline-form'));
            return;
        }

        $type = trim($_POST['type'] ?? 'plugin');
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $author = trim($_POST['author'] ?? '');
        $authorEmail = trim($_POST['author_email'] ?? '');
        $githubRepo = trim($_POST['github_repo'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            Show::msg("Le nom de l'extension est obligatoire.", 'error');
            $this->core->redirect($this->router->generate('communitypipeline-form'));
            return;
        }

        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            Show::msg("Veuillez sélectionner une archive ZIP valide.", 'error');
            $this->core->redirect($this->router->generate('communitypipeline-form'));
            return;
        }

        $tmpFile = $_FILES['file']['tmp_name'];
        $origName = $_FILES['file']['name'];

        // Préparer la requête multipart vers le Hub API
        $postData = [
            'type'         => $type,
            'name'         => $name,
            'slug'         => $slug,
            'author'       => $author,
            'author_email' => $authorEmail,
            'github_repo'  => $githubRepo,
            'description'  => $description,
            'file'         => new \CURLFile($tmpFile, 'application/zip', $origName),
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->hubApiUrl . '/repository/api/community/submit');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $responseBody = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($responseBody === false || !empty($curlError)) {
            Show::msg("Erreur de connexion avec le Hub API 299ko : " . $curlError, 'error');
            $this->core->redirect($this->router->generate('communitypipeline-form'));
            return;
        }

        $result = json_decode($responseBody, true);
        if (!is_array($result) || empty($result['submission_id'])) {
            $msg = $result['error'] ?? "Une erreur est survenue lors de l'enregistrement de votre soumission.";
            Show::msg($msg, 'error');
            $this->core->redirect($this->router->generate('communitypipeline-form'));
            return;
        }

        $submissionId = $result['submission_id'];
        $status = $result['status'] ?? 'queued';

        if ($status === 'approved') {
            Show::msg("Félicitations ! Votre extension a été auditée et validée avec succès (Score: {$result['score']}/100).", 'success');
        } elseif ($status === 'rejected') {
            Show::msg("L'audit automatique a relevé des problèmes de conformité ou de sécurité (Score: {$result['score']}/100). Consultez le rapport ci-dessous.", 'warning');
        } else {
            Show::msg("Votre soumission a été reçue et est en cours d'analyse.", 'info');
        }

        $this->core->redirect($this->router->generate('communitypipeline-status', ['submissionId' => $submissionId]));
    }

    /**
     * Affiche l'écran de suivi et de rapport d'audit détaillé
     */
    public function status(string $submissionId)
    {
        $response = new PublicResponse();
        $tpl = $response->createPluginTemplate('communitypipeline', 'public-status');

        $submission = $this->fetchSubmissionFromHub($submissionId);

        if (!$submission) {
            Show::msg("Soumission #{$submissionId} introuvable.", 'error');
            $this->core->redirect($this->router->generate('communitypipeline-form'));
            return;
        }

        $tpl->set('submission', $submission);
        $tpl->set('report', $submission['report'] ?? []);
        $tpl->set('formUrl', $this->router->generate('communitypipeline-form'));
        $tpl->set('apiStatusUrl', $this->router->generate('communitypipeline-api-status', ['submissionId' => $submissionId]));

        $response->setTitle("Statut de la soumission #{$submissionId} - 299ko EVO");
        $response->addTemplate($tpl);
        return $response;
    }

    /**
     * Endpoint API JSON de statut pour polling en direct
     */
    public function apiStatus(string $submissionId)
    {
        header('Content-Type: application/json; charset=utf-8');
        $submission = $this->fetchSubmissionFromHub($submissionId);

        if (!$submission) {
            http_response_code(404);
            echo json_encode(['error' => 'Soumission introuvable']);
            exit;
        }

        echo json_encode($submission, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    private function fetchSubmissionFromHub(string $submissionId): ?array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->hubApiUrl . '/repository/api/community/status?id=' . urlencode($submissionId));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || empty($body)) {
            return null;
        }

        $data = json_decode($body, true);
        return is_array($data) && !empty($data['submission_id']) ? $data : null;
    }
}

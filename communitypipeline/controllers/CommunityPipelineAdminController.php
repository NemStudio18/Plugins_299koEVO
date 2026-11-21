<?php

use Core\Controllers\AdminController;
use Core\Responses\AdminResponse;
use Utils\Show;

defined('ROOT') or exit('Access denied!');

class CommunityPipelineAdminController extends AdminController
{
    private string $hubApiUrl;

    public function __construct()
    {
        parent::__construct();
        $url = $this->core->getConfigVal('marketplaceUrl');
        $this->hubApiUrl = !empty($url) ? rtrim($url, '/') : 'https://299ko-api.flexcb.fr';
    }

    public function list()
    {
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('communitypipeline', 'admin-list');

        $statusFilter = $_GET['status'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));

        $data = $this->callHubApi('/repository/api/community/submissions?page=' . $page . (!empty($statusFilter) ? '&status=' . urlencode($statusFilter) : ''));

        $tpl->set('submissions', $data['submissions'] ?? []);
        $tpl->set('total', $data['total'] ?? 0);
        $tpl->set('page', $data['page'] ?? 1);
        $tpl->set('totalPages', $data['total_pages'] ?? 1);
        $tpl->set('statusFilter', $statusFilter);
        $tpl->set('hubApiUrl', $this->hubApiUrl);

        $response->setTitle('Pipeline Communautaire - Soumissions');
        $response->addTemplate($tpl);
        return $response;
    }

    public function view(string $submissionId)
    {
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('communitypipeline', 'admin-view');

        $submission = $this->callHubApi('/repository/api/community/status?id=' . urlencode($submissionId));

        if (empty($submission) || empty($submission['submission_id'])) {
            Show::msg('Soumission introuvable.', 'error');
            $this->core->redirect($this->router->generate('admin-communitypipeline'));
            return;
        }

        $tpl->set('submission', $submission);
        $tpl->set('report', $submission['report'] ?? []);
        $tpl->set('backUrl', $this->router->generate('admin-communitypipeline'));

        $response->setTitle('Détail Soumission #' . $submissionId);
        $response->addTemplate($tpl);
        return $response;
    }

    private function callHubApi(string $endpoint): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->hubApiUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || empty($body)) {
            return [];
        }

        $data = json_decode($body, true);
        return is_array($data) ? $data : [];
    }
}

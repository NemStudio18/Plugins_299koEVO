<?php

namespace ProRdv\Controllers;

use Core\Controllers\Controller;
use Core\Responses\AdminResponse;
use ProRdv\Services\RdvService;
use Template\Template;

defined('ROOT') or exit('Access denied!');

class AdminRdvController extends Controller
{
    private RdvService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = RdvService::getInstance();
    }

    /**
     * Tableau de bord Administrateur ProRdv (/admin/prordv)
     */
    public function index()
    {
        if (!defined('IS_ADMIN') || !IS_ADMIN) {
            header("Location: /");
            exit;
        }

        $commerces = $this->service->getAllCommerces();

        $response = new AdminResponse();
        $response->setTitle("Administration ProRdv");

        $tpl = new Template(ROOT . 'plugin/ProRdv/template/admin_overview.tpl');
        $tpl->set('commerces', $commerces);

        $response->addTemplate($tpl);
        return $response;
    }

    /**
     * Consultation / Supervision d'un commerce spécifique
     */
    public function viewCommerce(int $id)
    {
        if (!defined('IS_ADMIN') || !IS_ADMIN) {
            header("Location: /");
            exit;
        }

        $commerce = $this->service->getCommerceById($id);
        if (!$commerce) {
            exit('Commerce introuvable');
        }

        $prestas = $this->service->getPrestations($id);
        $collabs = $this->service->getCollaborateurs($id);
        $d = date('Y-m-d');
        $rdvs = $this->service->getProAgenda($id, $d);

        $response = new AdminResponse();
        $response->setTitle("Gestion RDV : {$commerce['nom']}");

        $tpl = new Template(ROOT . 'plugin/ProRdv/template/pro_agenda.tpl');
        $tpl->set('commerce', $commerce);
        $tpl->set('d', $d);
        $tpl->set('prev_date', date('Y-m-d', strtotime('-1 day')));
        $tpl->set('next_date', date('Y-m-d', strtotime('+1 day')));
        $tpl->set('liste', $rdvs);

        $response->addTemplate($tpl);
        return $response;
    }

    /**
     * Activation / Désactivation d'un commerce
     */
    public function toggleCommerce(int $id)
    {
        if (!defined('IS_ADMIN') || !IS_ADMIN) {
            exit('Accès refusé');
        }

        $pdo = \Core\Database::getInstance()->getPdo();
        $pdo->prepare("UPDATE `lbp_rdv_commerces` SET `is_active` = 1 - `is_active` WHERE `id` = ?")->execute([$id]);

        header("Location: /admin/prordv");
        exit;
    }
}

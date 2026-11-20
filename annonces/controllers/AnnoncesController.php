<?php

namespace Annonces\Controllers;

use Annonces\Entities\Annonce;
use Annonces\Entities\AnnonceReservation;
use Core\Auth\User;
use Core\Auth\UsersManager;
use Core\Controllers\PublicController;
use Core\Lang;
use Core\Navigation\AccessPolicyEvaluator;
use Core\Navigation\ConditionContext;
use Core\Plugin\PluginsManager;
use Core\Responses\PublicResponse;
use Core\Security\Csrf;
use Utils\Show;
use Utils\Util;
use Utils\ImageOptimizer;

defined('ROOT') OR exit('Access denied!');

class AnnoncesController extends PublicController
{
    private function pluginCfg(string $key, $default = '')
    {
        $plugin = $this->runPlugin ?? PluginsManager::getInstance()->getPlugin('annonces');
        if ($plugin) {
            $val = $plugin->getConfigVal($key);
            if ($val !== null && $val !== false) {
                return $val;
            }
        }
        return $default;
    }

    public function home()
    {
        $categoryFilter = trim((string) ($_GET['cat'] ?? ''));
        $statusFilter = trim((string) ($_GET['status'] ?? ''));
        $queryFilter = mb_strtolower(trim((string) ($_GET['q'] ?? '')));

        $allItems = Annonce::all();
        $categories = [];
        $items = [];
        $currentUser = UsersManager::getCurrentUser();
        $myCount = 0;

        foreach ($allItems as $item) {
            if ($currentUser && (int) ($item->attributes['user_id'] ?? 0) === (int) $currentUser->id) {
                $myCount++;
            }
            if (!$item->isPublic()) {
                continue;
            }
            $cat = trim((string) ($item->attributes['category'] ?? ''));
            if ($cat !== '') {
                $categories[$cat] = $cat;
            }
        }
        ksort($categories);

        // Tri du plus récent / mis en avant au plus ancien
        usort($allItems, static function (Annonce $a, Annonce $b): int {
            $fa = (int) ($a->attributes['is_featured'] ?? 0);
            $fb = (int) ($b->attributes['is_featured'] ?? 0);
            if ($fa !== $fb) {
                return $fb <=> $fa;
            }
            return strcmp((string) ($b->attributes['created_at'] ?? ''), (string) ($a->attributes['created_at'] ?? ''));
        });

        foreach ($allItems as $item) {
            if (!$item->isPublic()) {
                continue;
            }
            $attrs = $item->attributes;
            if ($categoryFilter !== '' && ($attrs['category'] ?? '') !== $categoryFilter) {
                continue;
            }
            if ($statusFilter !== '' && ($attrs['status'] ?? 'available') !== $statusFilter) {
                continue;
            }
            if ($queryFilter !== '') {
                $haystack = mb_strtolower(
                    ($attrs['title'] ?? '') . ' ' .
                    strip_tags((string) ($attrs['description'] ?? '')) . ' ' .
                    ($attrs['location'] ?? '') . ' ' .
                    ($attrs['category'] ?? '')
                );
                if (mb_strpos($haystack, $queryFilter) === false) {
                    continue;
                }
            }

            $descPlain = trim(strip_tags((string) ($attrs['description'] ?? '')));
            $excerpt = mb_strlen($descPlain) > 130 ? mb_substr($descPlain, 0, 127) . '…' : $descPlain;
            $images = $item->getImages();

            $items[] = [
                'id'             => $item->getId(),
                'title'          => (string) ($attrs['title'] ?? ''),
                'category'       => (string) ($attrs['category'] ?? ''),
                'price_label'    => trim((string) ($attrs['price_label'] ?? '')) ?: 'Prix non précisé',
                'location'       => $item->getCity(),
                'item_condition' => (string) ($attrs['item_condition'] ?? ''),
                'image'          => $item->getMainImage(),
                'images_count'   => count($images),
                'excerpt'        => $excerpt,
                'status'         => (string) ($attrs['status'] ?? 'available'),
                'status_label'   => $item->getStatusLabel(),
                'is_available'   => $item->isAvailable(),
                'is_reserved'    => $item->isReserved(),
                'is_sold'        => $item->isSold(),
                'is_featured'    => !empty($attrs['is_featured']),
                'author_name'    => $item->getAuthorName(),
                'created_at'     => (string) ($attrs['created_at'] ?? ''),
                'url'            => $item->getPublicUrl(),
            ];
        }

        $response = new PublicResponse();
        $tpl = $response->createPluginTemplate('annonces', 'public');
        $response->setTitle($this->pluginCfg('label', 'Petites Annonces') ?: 'Petites Annonces');
        $tpl->set('items', $items);
        $tpl->set('categories', array_values($categories));
        $tpl->set('categoryFilter', $categoryFilter);
        $tpl->set('statusFilter', $statusFilter);
        $tpl->set('queryFilter', trim((string) ($_GET['q'] ?? '')));
        $tpl->set('introText', (string) $this->pluginCfg('introText', ''));
        $tpl->set('listUrl', $this->router->generate('annonces-home'));
        $tpl->set('deposerUrl', $this->router->generate('annonces-deposer'));
        $tpl->set('mesAnnoncesUrl', $this->router->generate('annonces-mes-annonces'));
        $tpl->set('isLogged', $currentUser !== null);
        $tpl->set('myAnnoncesCount', $myCount);
        $tpl->set('allowPublicSubmission', (string) $this->pluginCfg('allowPublicSubmission', '1') === '1');
        $response->addTemplate($tpl);
        return $response;
    }

    public function view(string $id, string $slug = '')
    {
        $item = Annonce::findPK($id);
        $currentUser = UsersManager::getCurrentUser();

        // Si l'annonce n'est pas publique, autoriser l'accès uniquement à l'auteur ou l'admin
        if ($item === null || (!$item->isPublic() && !$item->canEdit($currentUser))) {
            $this->core->error404();
            return null;
        }

        $antispamField = '';
        $requireAntispam = (string) $this->pluginCfg('requireAntispam', '1') === '1';
        if ($requireAntispam && PluginsManager::isActivePlugin('antispam')) {
            $hookRes = $this->core->callHook('antispam:get-captcha', ['field_html' => '']);
            $antispamField = (string) ($hookRes['field_html'] ?? '');
        }

        $response = new PublicResponse();
        $tpl = $response->createPluginTemplate('annonces', 'view');
        $response->setTitle($item->attributes['title'] ?? 'Petite Annonce');

        $attrs = $item->attributes;
        $images = $item->getImages();
        $descHtml = trim((string) ($attrs['description'] ?? ''));
        if (strip_tags($descHtml) === $descHtml) {
            $descHtml = nl2br(htmlspecialchars($descHtml, ENT_QUOTES, 'UTF-8'));
        }

        $isAuthor = $item->isAuthor($currentUser);
        $canEdit = $item->canEdit($currentUser);

        $tpl->set('annonce', [
            'id'             => $item->getId(),
            'title'          => (string) ($attrs['title'] ?? ''),
            'category'       => (string) ($attrs['category'] ?? ''),
            'price_label'    => trim((string) ($attrs['price_label'] ?? '')) ?: 'Prix non précisé',
            'location'       => $item->getCity(),
            'city'           => (string) ($attrs['city'] ?? ''),
            'postal_code'    => (string) ($attrs['postal_code'] ?? ''),
            'phone'          => (!empty($attrs['show_phone']) && !empty($attrs['phone'])) ? (string) $attrs['phone'] : '',
            'item_condition' => (string) ($attrs['item_condition'] ?? ''),
            'image'          => $item->getMainImage(),
            'images'         => $images,
            'description'    => $descHtml,
            'contact_info'   => (string) ($attrs['contact_info'] ?? ''),
            'author_name'    => $item->getAuthorName(),
            'status'         => (string) ($attrs['status'] ?? 'available'),
            'status_label'   => $item->getStatusLabel(),
            'is_available'   => $item->isAvailable(),
            'is_reserved'    => $item->isReserved(),
            'is_sold'        => $item->isSold(),
            'created_at'     => (string) ($attrs['created_at'] ?? ''),
            'moderation_status' => (string) ($attrs['moderation_status'] ?? 'approved'),
            'moderation_label'  => $item->getModerationLabel(),
            'is_pending'        => $item->isPendingModeration(),
        ]);

        $tpl->set('isAuthor', $isAuthor);
        $tpl->set('canEdit', $canEdit);
        $tpl->set('editUrl', $this->router->generate('annonces-edit-public', ['id' => $item->getId()]));
        $tpl->set('mesAnnoncesUrl', $this->router->generate('annonces-mes-annonces'));
        $tpl->set('reservationNotice', (string) $this->pluginCfg('reservationNotice', ''));
        $tpl->set('reserveUrl', $this->router->generate('annonces-reserve', ['id' => $item->getId()]));
        $tpl->set('backUrl', $this->router->generate('annonces-home'));
        $tpl->set('antispam', $requireAntispam && PluginsManager::isActivePlugin('antispam'));
        $tpl->set('antispamField', $antispamField);
        $tpl->set('_csrfToken', Csrf::token());

        $response->addTemplate($tpl);
        return $response;
    }

    /**
     * Page de dépôt public d'une annonce.
     */
    public function deposer()
    {
        if ((string) $this->pluginCfg('allowPublicSubmission', '1') !== '1') {
            Show::msg("Le dépôt public d'annonces est actuellement désactivé.", 'warning');
            $this->core->redirect($this->router->generate('annonces-home'));
            return null;
        }

        $requireLogin = (string) $this->pluginCfg('requireLoginToPost', '1') === '1';
        $currentUser = UsersManager::getCurrentUser();
        if ($requireLogin && !$currentUser) {
            Show::msg("Vous devez être connecté à votre compte pour déposer une petite annonce.", 'info');
            $this->core->redirect('/users/login?redirect=' . urlencode($this->router->generate('annonces-deposer')));
            return null;
        }

        $response = new PublicResponse();
        $tpl = $response->createPluginTemplate('annonces', 'deposer');
        $response->setTitle('<i class="fa-solid fa-plus-circle"></i> Déposer une petite annonce');

        // Récupérer les catégories existantes
        $categories = $this->getExistingCategories();

        $tpl->set('isEdit', false);
        $tpl->set('categories', $categories);
        $tpl->set('user', $currentUser);
        $tpl->set('saveUrl', $this->router->generate('annonces-save-public'));
        $tpl->set('mesAnnoncesUrl', $this->router->generate('annonces-mes-annonces'));
        $tpl->set('maxPhotos', (int) $this->pluginCfg('maxPhotos', 5));
        $tpl->set('moderationMode', $this->pluginCfg('moderationMode', 'pre'));
        $tpl->set('csrf_token', Csrf::token());
        $tpl->set('item', [
            'id'             => 0,
            'title'          => '',
            'category'       => '',
            'price_label'    => '',
            'item_condition' => 'Très bon état',
            'city'           => '',
            'postal_code'    => '',
            'phone'          => '',
            'show_phone'     => 0,
            'description'    => '',
            'images'         => [],
        ]);

        $response->addTemplate($tpl);
        return $response;
    }

    /**
     * Traitement de la création d'une annonce par le public.
     */
    public function savePublic()
    {
        if (!Csrf::check($_POST['csrf_token'] ?? '')) {
            Show::msg('Session expirée ou jeton CSRF invalide.', 'error');
            $this->core->redirect($this->router->generate('annonces-deposer'));
            return null;
        }

        $currentUser = UsersManager::getCurrentUser();
        $requireLogin = (string) $this->pluginCfg('requireLoginToPost', '1') === '1';
        if ($requireLogin && !$currentUser) {
            Show::msg('Vous devez être connecté pour déposer une annonce.', 'error');
            $this->core->redirect('/users/login');
            return null;
        }

        $title = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? ''));
        $price = trim((string) ($_POST['price_label'] ?? ''));
        $city = trim((string) ($_POST['city'] ?? ''));
        $postalCode = trim((string) ($_POST['postal_code'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $showPhone = !empty($_POST['show_phone']) ? 1 : 0;
        $itemCondition = trim((string) ($_POST['item_condition'] ?? 'Bon état'));

        if ($title === '' || $description === '') {
            Show::msg('Veuillez renseigner au minimum un titre et une description détaillée.', 'error');
            $this->core->redirect($this->router->generate('annonces-deposer'));
            return null;
        }

        // Upload des photos
        $maxPhotos = (int) $this->pluginCfg('maxPhotos', 5);
        $uploadedImages = $this->handlePublicUploads($maxPhotos);

        $item = new Annonce();
        $item->title = $title;
        $item->slug = $this->slugify($title);
        $item->category = $category !== '' ? $category : 'Divers';
        $item->price_label = $price !== '' ? $price : 'Prix libre';
        $item->item_condition = $itemCondition;
        $item->city = $city;
        $item->postal_code = $postalCode;
        $item->location = trim($city . ' ' . $postalCode);
        $item->phone = $phone;
        $item->show_phone = $showPhone;
        $item->description = $description;
        $item->images = $uploadedImages;
        $item->image = $uploadedImages[0] ?? '';
        $item->is_featured = 0;

        // Auteur
        if ($currentUser) {
            $item->user_id = (int) $currentUser->id;
            $item->user_name = (string) ($currentUser->username() ?: $currentUser->email);
            $item->user_email = (string) $currentUser->email;
        } else {
            $item->user_id = 0;
            $item->user_name = trim((string) ($_POST['guest_name'] ?? 'Visiteur'));
            $item->user_email = trim((string) ($_POST['guest_email'] ?? ''));
        }

        // Détermination du statut de modération
        $modMode = $this->pluginCfg('moderationMode', 'pre');
        if ($modMode === 'pre') {
            $item->moderation_status = Annonce::MODERATION_PENDING;
            $item->status = 'draft';
            $successMsg = "Votre annonce « {$title} » a bien été enregistrée ! Elle sera publiée dès validation par l'équipe de modération.";
        } else {
            $item->moderation_status = Annonce::MODERATION_APPROVED;
            $item->status = 'available';
            $successMsg = "Votre annonce « {$title} » est désormais en ligne !";
        }

        $item->created_at = date('Y-m-d H:i:s');
        $item->updated_at = date('Y-m-d H:i:s');
        $item->save();

        // Notifier l'administrateur si modération requise
        if ($item->isPendingModeration()) {
            $this->notifyAdminsForModeration($item);
        }

        Show::msg($successMsg, 'success');
        $this->core->redirect($this->router->generate('annonces-mes-annonces'));
        return null;
    }

    /**
     * Espace Membre « Mes Annonces ».
     */
    public function mesAnnonces()
    {
        $currentUser = UsersManager::getCurrentUser();
        if (!$currentUser) {
            Show::msg('Veuillez vous connecter pour accéder à la gestion de vos annonces.', 'info');
            $this->core->redirect('/users/login?redirect=' . urlencode($this->router->generate('annonces-mes-annonces')));
            return null;
        }

        $myAnnonces = [];
        $myAnnonceIds = [];
        $allItems = Annonce::all();

        // Trier par date décroissante
        usort($allItems, static function (Annonce $a, Annonce $b): int {
            return strcmp((string) ($b->attributes['created_at'] ?? ''), (string) ($a->attributes['created_at'] ?? ''));
        });

        foreach ($allItems as $item) {
            if ($item->isAuthor($currentUser)) {
                $myAnnonces[] = $item;
                $myAnnonceIds[(string) $item->getId()] = $item;
            }
        }

        // Récupérer les réservations reçues pour mes annonces
        $myReservations = [];
        if (!empty($myAnnonceIds)) {
            $allRes = AnnonceReservation::all();
            foreach ($allRes as $res) {
                $aid = (string) ($res->attributes['annonce_id'] ?? '');
                if (isset($myAnnonceIds[$aid])) {
                    $myReservations[] = [
                        'id'              => $res->getId(),
                        'annonce_id'      => $aid,
                        'annonce_title'   => $myAnnonceIds[$aid]->attributes['title'] ?? '',
                        'requester_name'  => (string) ($res->attributes['requester_name'] ?? ''),
                        'requester_email' => (string) ($res->attributes['requester_email'] ?? ''),
                        'requester_phone' => (string) ($res->attributes['requester_phone'] ?? ''),
                        'message'         => (string) ($res->attributes['message'] ?? ''),
                        'status'          => (string) ($res->attributes['status'] ?? 'pending'),
                        'created_at'      => (string) ($res->attributes['created_at'] ?? ''),
                    ];
                }
            }
        }

        $response = new PublicResponse();
        $tpl = $response->createPluginTemplate('annonces', 'mes-annonces');
        $response->setTitle('<i class="fa-solid fa-tags"></i> Mon espace Petites Annonces');

        $tpl->set('annonces', $myAnnonces);
        $tpl->set('totalCount', count($myAnnonces));
        $tpl->set('reservations', $myReservations);
        $tpl->set('reservationsCount', count($myReservations));
        $tpl->set('deposerUrl', $this->router->generate('annonces-deposer'));
        $tpl->set('homeUrl', $this->router->generate('annonces-home'));
        $tpl->set('userToken', $currentUser->token ?? Csrf::token());
        $tpl->set('csrf_token', Csrf::token());

        $response->addTemplate($tpl);
        return $response;
    }

    /**
     * Édition d'une annonce par son auteur.
     */
    public function editPublic(string $id)
    {
        $currentUser = UsersManager::getCurrentUser();
        if (!$currentUser) {
            Show::msg('Connexion requise.', 'error');
            $this->core->redirect('/users/login');
            return null;
        }

        $item = Annonce::findPK($id);
        if (!$item || !$item->canEdit($currentUser)) {
            Show::msg("Vous n'êtes pas autorisé à modifier cette annonce.", 'error');
            $this->core->redirect($this->router->generate('annonces-mes-annonces'));
            return null;
        }

        $response = new PublicResponse();
        $tpl = $response->createPluginTemplate('annonces', 'deposer');
        $response->setTitle('<i class="fa-solid fa-pen-to-square"></i> Modifier l\'annonce : ' . ($item->attributes['title'] ?? ''));

        $categories = $this->getExistingCategories();
        $attrs = $item->attributes;

        $tpl->set('isEdit', true);
        $tpl->set('categories', $categories);
        $tpl->set('user', $currentUser);
        $tpl->set('saveUrl', $this->router->generate('annonces-save-public-edit', ['id' => $item->getId()]));
        $tpl->set('mesAnnoncesUrl', $this->router->generate('annonces-mes-annonces'));
        $tpl->set('maxPhotos', (int) $this->pluginCfg('maxPhotos', 5));
        $tpl->set('moderationMode', $this->pluginCfg('moderationMode', 'pre'));
        $tpl->set('csrf_token', Csrf::token());
        $tpl->set('item', [
            'id'             => $item->getId(),
            'title'          => (string) ($attrs['title'] ?? ''),
            'category'       => (string) ($attrs['category'] ?? ''),
            'price_label'    => (string) ($attrs['price_label'] ?? ''),
            'item_condition' => (string) ($attrs['item_condition'] ?? 'Très bon état'),
            'city'           => (string) ($attrs['city'] ?? ''),
            'postal_code'    => (string) ($attrs['postal_code'] ?? ''),
            'phone'          => (string) ($attrs['phone'] ?? ''),
            'show_phone'     => !empty($attrs['show_phone']) ? 1 : 0,
            'description'    => (string) ($attrs['description'] ?? ''),
            'images'         => $item->getImages(),
        ]);

        $response->addTemplate($tpl);
        return $response;
    }

    /**
     * Sauvegarde des modifications d'une annonce par son auteur.
     */
    public function savePublicEdit(string $id)
    {
        if (!Csrf::check($_POST['csrf_token'] ?? '')) {
            Show::msg('Session expirée.', 'error');
            $this->core->redirect($this->router->generate('annonces-mes-annonces'));
            return null;
        }

        $currentUser = UsersManager::getCurrentUser();
        $item = Annonce::findPK($id);
        if (!$item || !$item->canEdit($currentUser)) {
            Show::msg("Action non autorisée.", 'error');
            $this->core->redirect($this->router->generate('annonces-mes-annonces'));
            return null;
        }

        $title = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? ''));
        $price = trim((string) ($_POST['price_label'] ?? ''));
        $city = trim((string) ($_POST['city'] ?? ''));
        $postalCode = trim((string) ($_POST['postal_code'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $showPhone = !empty($_POST['show_phone']) ? 1 : 0;
        $itemCondition = trim((string) ($_POST['item_condition'] ?? 'Bon état'));

        if ($title === '' || $description === '') {
            Show::msg('Titre et description requis.', 'error');
            $this->core->redirect($this->router->generate('annonces-edit-public', ['id' => $id]));
            return null;
        }

        // Photos conservées
        $keptImages = [];
        if (!empty($_POST['kept_images']) && is_array($_POST['kept_images'])) {
            $existing = $item->getImages();
            foreach ($_POST['kept_images'] as $kImg) {
                if (in_array($kImg, $existing, true)) {
                    $keptImages[] = $kImg;
                }
            }
        }

        // Nouveaux uploads
        $maxPhotos = (int) $this->pluginCfg('maxPhotos', 5);
        $remainingSlots = max(0, $maxPhotos - count($keptImages));
        $newUploads = $this->handlePublicUploads($remainingSlots);
        $finalImages = array_merge($keptImages, $newUploads);

        $item->title = $title;
        $item->slug = $this->slugify($title);
        $item->category = $category !== '' ? $category : 'Divers';
        $item->price_label = $price !== '' ? $price : 'Prix libre';
        $item->item_condition = $itemCondition;
        $item->city = $city;
        $item->postal_code = $postalCode;
        $item->location = trim($city . ' ' . $postalCode);
        $item->phone = $phone;
        $item->show_phone = $showPhone;
        $item->description = $description;
        $item->images = $finalImages;
        $item->image = $finalImages[0] ?? '';
        $item->updated_at = date('Y-m-d H:i:s');

        // Si modération a priori, repasser en pending si réédition
        $modMode = $this->pluginCfg('moderationMode', 'pre');
        if ($modMode === 'pre' && $item->moderation_status !== Annonce::MODERATION_PENDING) {
            $item->moderation_status = Annonce::MODERATION_PENDING;
            $item->status = 'draft';
            $this->notifyAdminsForModeration($item);
            Show::msg("Votre annonce a été mise à jour et soumise à nouveau pour validation.", 'info');
        } else {
            Show::msg("Votre annonce « {$title} » a été mise à jour avec succès.", 'success');
        }

        $item->save();
        $this->core->redirect($this->router->generate('annonces-mes-annonces'));
        return null;
    }

    /**
     * Changement de statut rapide par l'auteur (marquer vendue, réactiver).
     */
    public function changeStatusPublic(string $id, string $status, string $token)
    {
        $currentUser = UsersManager::getCurrentUser();
        if (!$currentUser || $currentUser->token !== $token) {
            Show::msg('Sécurité : jeton de session invalide.', 'error');
            $this->core->redirect($this->router->generate('annonces-mes-annonces'));
            return null;
        }

        $item = Annonce::findPK($id);
        if (!$item || !$item->canEdit($currentUser)) {
            Show::msg("Action non autorisée.", 'error');
            $this->core->redirect($this->router->generate('annonces-mes-annonces'));
            return null;
        }

        $allowed = ['available', 'reserved', 'sold', 'closed'];
        if (in_array($status, $allowed, true)) {
            $item->status = $status;
            $item->updated_at = date('Y-m-d H:i:s');
            $item->save();
            Show::msg("Statut de l'annonce mis à jour : " . $item->getStatusLabel(), 'success');
        }

        $this->core->redirect($this->router->generate('annonces-mes-annonces'));
        return null;
    }

    /**
     * Suppression d'une annonce par l'auteur.
     */
    public function deletePublic(string $id, string $token)
    {
        $currentUser = UsersManager::getCurrentUser();
        if (!$currentUser || $currentUser->token !== $token) {
            Show::msg('Jeton invalide.', 'error');
            $this->core->redirect($this->router->generate('annonces-mes-annonces'));
            return null;
        }

        $item = Annonce::findPK($id);
        if (!$item || !$item->canEdit($currentUser)) {
            Show::msg("Action non autorisée.", 'error');
            $this->core->redirect($this->router->generate('annonces-mes-annonces'));
            return null;
        }

        $title = (string) ($item->attributes['title'] ?? 'Annonce');
        $item->delete();
        Show::msg("L'annonce « {$title} » a bien été supprimée.", 'info');
        $this->core->redirect($this->router->generate('annonces-mes-annonces'));
        return null;
    }

    /**
     * Traitement de la réservation par un visiteur.
     */
    public function reserve(string $id)
    {
        $item = Annonce::findPK($id);
        if ($item === null) {
            $this->core->error404();
            return null;
        }

        $redirectUrl = $item->getPublicUrl();

        if (!Csrf::check($_POST['_csrf'] ?? '')) {
            Show::msg('Session expirée, veuillez renvoyer votre demande.', 'error');
            $this->core->redirect($redirectUrl);
            return null;
        }

        // Honeypot antispam
        if (!empty($_POST['_hp_website'])) {
            Show::msg('Demande rejetée (antispam).', 'error');
            $this->core->redirect($redirectUrl);
            return null;
        }

        $requireAntispam = (string) $this->pluginCfg('requireAntispam', '1') === '1';
        if ($requireAntispam && PluginsManager::isActivePlugin('antispam')) {
            $res = $this->core->callHook('antispam:validate', ['is_valid' => true]);
            if (empty($res['is_valid'])) {
                Show::msg(Lang::get('antispam.invalid-captcha') ?: 'Antispam invalide, veuillez réessayer.', 'error');
                $this->core->redirect($redirectUrl);
                return null;
            }
        }

        $onReserveMode = (string) $this->pluginCfg('onReserveStatus', 'mark_reserved');
        if (!$item->isAvailable()) {
            Show::msg(Lang::get('annonces.reserve-unavailable') ?: "Cette annonce n'est plus disponible à la réservation.", 'error');
            $this->core->redirect($redirectUrl);
            return null;
        }

        $name = trim((string) ($_POST['requester_name'] ?? ''));
        $email = filter_var(trim((string) ($_POST['requester_email'] ?? '')), FILTER_VALIDATE_EMAIL) ?: '';
        $phone = trim((string) ($_POST['requester_phone'] ?? ''));
        $message = trim((string) ($_POST['message'] ?? ''));

        if ($name === '' || $email === '' || $message === '') {
            Show::msg(Lang::get('annonces.reserve-invalid') ?: 'Veuillez renseigner votre nom, une adresse email valide et un message.', 'error');
            $this->core->redirect($redirectUrl);
            return null;
        }

        $reservation = new AnnonceReservation();
        $reservation->annonce_id = (string) $item->getId();
        $reservation->annonce_title = (string) ($item->attributes['title'] ?? '');
        $reservation->requester_name = $name;
        $reservation->requester_email = $email;
        $reservation->requester_phone = $phone;
        $reservation->message = $message;
        $reservation->status = 'pending';
        $reservation->created_at = date('Y-m-d H:i:s');
        $reservation->save();

        if ($onReserveMode === 'mark_reserved') {
            $item->status = 'reserved';
            $item->updated_at = date('Y-m-d H:i:s');
            $item->save();
        }

        // Notification envoyée au vendeur membre ou aux administrateurs
        $sellerEmail = $item->getAuthorEmail();
        $annonceTitle = (string) ($item->attributes['title'] ?? 'Annonce');
        $siteName = (string) ($this->core->getConfigVal('siteName') ?? 'Site');
        $siteUrl = rtrim((string) ($this->core->getConfigVal('siteUrl') ?? ''), '/');
        $host = parse_url($siteUrl, PHP_URL_HOST) ?: ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $from = 'no-reply@' . preg_replace('/[^a-zA-Z0-9.-]/', '', (string) $host);

        $subject = '[' . $siteName . '] Demande de réservation pour : ' . $annonceTitle;
        $body = "Bonjour,\n\nVous avez reçu une nouvelle demande de réservation pour votre annonce « {$annonceTitle} ».\n\n"
            . "Coordonnées de l'acheteur potentiel :\n"
            . "• Nom / Pseudo : {$name}\n"
            . "• Email : {$email}\n"
            . "• Téléphone : " . ($phone !== '' ? $phone : 'Non renseigné') . "\n\n"
            . "Message de l'acheteur :\n"
            . "--------------------------------------------------\n"
            . "{$message}\n"
            . "--------------------------------------------------\n\n"
            . "Vous pouvez répondre directement à cet email pour convenir d'un rendez-vous ou d'un mode de remise.\n\n"
            . "Retrouvez vos annonces sur : {$siteUrl}/annonces/mes-annonces\n";

        // 1. Email au vendeur s'il s'agit d'un membre
        if ($sellerEmail !== '' && filter_var($sellerEmail, FILTER_VALIDATE_EMAIL)) {
            try {
                Util::sendEmail($from, $email, $sellerEmail, $subject, $body);
            } catch (\Throwable $e) {
                $this->core->getLogger()->error('Annonces email notification to seller failed: ' . $e->getMessage());
            }
        }

        // 2. Email à l'administrateur pour suivi
        $adminRecipients = $this->resolveAdminRecipients();
        foreach ($adminRecipients['emails'] as $adminMail) {
            if ($adminMail !== $sellerEmail) {
                try {
                    Util::sendEmail($from, $email, $adminMail, $subject, $body);
                } catch (\Throwable $e) {
                    $this->core->getLogger()->error('Annonces email notification to admin failed: ' . $e->getMessage());
                }
            }
        }

        // 3. Notification cloche interne si plugin notifications actif
        $notifyUserIds = [];
        $sellerUserId = (int) ($item->attributes['user_id'] ?? 0);
        if ($sellerUserId > 0) {
            $notifyUserIds[] = $sellerUserId;
        } else {
            $notifyUserIds = $adminRecipients['userIds'];
        }

        if (PluginsManager::isActivePlugin('notifications') && !empty($notifyUserIds)) {
            try {
                $this->core->callHook('notifications:send', [
                    'user_id'       => $notifyUserIds,
                    'type'          => 'annonces.reservation',
                    'source_plugin' => 'annonces',
                    'data'          => [
                        'key'            => 'annonces.reservation',
                        'params'         => [$name, $annonceTitle],
                        'fallback_title' => 'Nouvelle demande de réservation pour « %s »',
                        'url'            => ($sellerUserId > 0) ? '/annonces/mes-annonces' : '/admin/annonces?tab=reservations',
                        'actor'          => [
                            'name'  => $name,
                            'email' => $email,
                        ],
                        'entity'         => [
                            'type' => 'annonce',
                            'id'   => (string) $item->getId(),
                        ],
                    ],
                    'external_id' => 'annonces:reservation:' . (string) $reservation->getId(),
                ]);
            } catch (\Throwable $e) {
                $this->core->getLogger()->error('Annonces hook notifications:send failed: ' . $e->getMessage());
            }
        }

        Show::msg(Lang::get('annonces.reserve-success') ?: 'Votre demande de réservation a bien été transmise au vendeur !', 'success');
        $this->core->redirect($redirectUrl);
        return null;
    }

    /**
     * Traite l'upload des photos déposées en public.
     *
     * @param int $maxCount
     * @return string[] Chemins relatifs des images
     */
    private function handlePublicUploads(int $maxCount = 5): array
    {
        $uploadedPaths = [];
        if (empty($_FILES['image_files']) || !is_array($_FILES['image_files']['name'])) {
            return $uploadedPaths;
        }

        $names = $_FILES['image_files']['name'];
        $tmpNames = $_FILES['image_files']['tmp_name'];
        $errors = $_FILES['image_files']['error'];
        $sizes = $_FILES['image_files']['size'];

        $targetDir = UPLOAD . 'annonces' . DS;
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $count = 0;

        foreach ($names as $idx => $rawName) {
            if ($count >= $maxCount) {
                break;
            }
            if (($errors[$idx] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                continue;
            }
            if (($sizes[$idx] ?? 0) > 8 * 1024 * 1024) {
                continue;
            }
            $ext = strtolower(pathinfo((string) $rawName, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) {
                continue;
            }
            $tmp = $tmpNames[$idx] ?? '';
            if (!is_uploaded_file($tmp)) {
                continue;
            }

            $baseName = 'annonce_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3));
            if (class_exists('\\Utils\\ImageOptimizer') && ImageOptimizer::isOptimizableImage($tmp)) {
                $optName = ImageOptimizer::optimizeUpload($tmp, $targetDir, $baseName);
                if ($optName) {
                    $uploadedPaths[] = '/data/upload/annonces/' . $optName;
                    $count++;
                    continue;
                }
            }

            $filename = $baseName . '.' . $ext;
            if (move_uploaded_file($tmp, $targetDir . $filename)) {
                $uploadedPaths[] = '/data/upload/annonces/' . $filename;
                $count++;
            }
        }

        return $uploadedPaths;
    }

    private function notifyAdminsForModeration(Annonce $annonce): void
    {
        $admins = $this->resolveAdminRecipients();
        if (empty($admins['emails'])) {
            return;
        }

        $siteName = (string) ($this->core->getConfigVal('siteName') ?? 'Site');
        $siteUrl = rtrim((string) ($this->core->getConfigVal('siteUrl') ?? ''), '/');
        $host = parse_url($siteUrl, PHP_URL_HOST) ?: ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $from = 'no-reply@' . preg_replace('/[^a-zA-Z0-9.-]/', '', (string) $host);

        $subject = '[' . $siteName . '] Modération requise : ' . ($annonce->attributes['title'] ?? 'Nouvelle annonce');
        $body = "Une nouvelle petite annonce a été déposée par " . $annonce->getAuthorName() . " et attend votre validation.\n\n"
            . "Titre : " . ($annonce->attributes['title'] ?? '') . "\n"
            . "Catégorie : " . ($annonce->attributes['category'] ?? '') . "\n"
            . "Prix : " . ($annonce->attributes['price_label'] ?? '') . "\n\n"
            . "Modérer l'annonce dans l'administration : {$siteUrl}/admin/annonces?tab=moderation\n";

        foreach ($admins['emails'] as $email) {
            try {
                Util::sendEmail($from, $from, $email, $subject, $body);
            } catch (\Throwable $e) {
                // Ignore silently
            }
        }
    }

    private function getExistingCategories(): array
    {
        $cats = ['Véhicules', 'Immobilier', 'Maison & Jardin', 'Mode & Accessoires', 'Multimédia & Informatique', 'Loisirs & Sports', 'Emploi & Services', 'Divers'];
        foreach (Annonce::all() as $item) {
            $c = trim((string) ($item->attributes['category'] ?? ''));
            if ($c !== '' && !in_array($c, $cats, true)) {
                $cats[] = $c;
            }
        }
        return $cats;
    }

    private function slugify(string $text): string
    {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if (is_string($converted) && $converted !== '') {
            $text = $converted;
        }
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
        $text = trim($text, '-');
        return $text !== '' ? $text : 'annonce';
    }

    private function resolveAdminRecipients(): array
    {
        $emails = [];
        $userIds = [];

        $cfgEmail = trim((string) $this->pluginCfg('notifyEmail', ''));
        if ($cfgEmail !== '' && filter_var($cfgEmail, FILTER_VALIDATE_EMAIL)) {
            $emails[$cfgEmail] = $cfgEmail;
        }

        try {
            $evaluator = AccessPolicyEvaluator::getInstance();
            foreach (User::all() as $user) {
                if (!$evaluator->isAdmin(new ConditionContext($user))) {
                    continue;
                }
                $uid = (int) ($user->id ?? ($user->attributes['id'] ?? 0));
                if ($uid > 0) {
                    $userIds[] = $uid;
                }
                $uEmail = trim((string) ($user->email ?? ($user->attributes['email'] ?? '')));
                if (empty($emails) && $uEmail !== '' && filter_var($uEmail, FILTER_VALIDATE_EMAIL)) {
                    $emails[$uEmail] = $uEmail;
                }
            }
        } catch (\Throwable $e) {
        }

        if (empty($emails)) {
            $siteEmail = trim((string) ($this->core->getConfigVal('siteEmail') ?? ''));
            if ($siteEmail !== '' && filter_var($siteEmail, FILTER_VALIDATE_EMAIL)) {
                $emails[$siteEmail] = $siteEmail;
            }
        }

        if (empty($userIds)) {
            $userIds = [1];
        }

        return [
            'emails'  => array_values($emails),
            'userIds' => array_values(array_unique($userIds)),
        ];
    }
}

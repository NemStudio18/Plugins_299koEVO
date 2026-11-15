<?php
namespace SocialFeed\Controllers;

use Core\Auth\UsersManager;
use Core\Responses\PublicResponse;
use Core\Responses\ApiResponse;
use Core\Security\Csrf;
use SocialFeed\Services\SocialFeedService;
use Template\Template;

class SocialFeedController {

    private SocialFeedService $feedService;

    public function __construct() {
        $this->feedService = SocialFeedService::getInstance();
    }

    /**
     * Page principale du Hub Communautaire : /social ou /feed
     */
    public function page() {
        $user = UsersManager::getCurrentUser();
        $userId = $user ? (int)($user->id ?? 0) : null;

        $filter = $_GET['filter'] ?? null;
        $scope = $_GET['scope'] ?? 'all';

        $providers = $this->feedService->getAvailableProviders();
        $feedTimeline = $this->feedService->getTimeline($userId, 1, 15, $filter, $scope);

        $siteName = \Core\Core::getInstance()->getConfigVal('siteName') ?: 'Déclic';
        $response = new PublicResponse();
        $response->setTitle('Hub Social - ' . $siteName);
        $response->hidePageTitle(true);
        Template::addGlobal('HIDE_PAGE_TITLE', true);

        $tpl = new Template(__DIR__ . '/../template/socialfeed_home.tpl');
        $tpl->set('siteName', $siteName);
        $tpl->set('currentUser', $user);
        $tpl->set('userId', $userId);
        $tpl->set('providers', $providers);
        $tpl->set('activeFilter', $filter ?: 'all');
        $tpl->set('activeScope', $scope);
        $tpl->set('feedTimeline', $feedTimeline);
        $tpl->set('csrfToken', Csrf::token());

        $response->addTemplate($tpl);
        return $response;
    }

    /**
     * Endpoint AJAX pour le scroll infini et les filtres d'onglets
     */
    public function feed() {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(30, max(1, (int)$_GET['limit'])) : 15;
        $filter = $_GET['filter'] ?? null;
        $scope = $_GET['scope'] ?? 'all';

        $user = UsersManager::getCurrentUser();
        $userId = $user ? (int)($user->id ?? 0) : null;

        $items = $this->feedService->getTimeline($userId, $page, $limit, $filter, $scope);

        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

        if ($isAjax || isset($_GET['render'])) {
            header('Content-Type: text/html; charset=utf-8');
            $feedTimeline = $items;
            $currentUser = $user;
            include __DIR__ . '/../template/feed_items.tpl';
            exit;
        }

        $resp = new ApiResponse();
        $resp->status = ApiResponse::STATUS_SUCCESS;
        $resp->httpCode = 200;
        $resp->body = ['items' => $items, 'has_more' => count($items) === $limit];
        return $resp;
    }

    /**
     * Publication d'un statut par un membre
     */
    public function publish() {
        header('Content-Type: application/json; charset=utf-8');

        $user = UsersManager::getCurrentUser();
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'Veuillez vous connecter pour publier.']);
            exit;
        }

        $token = $_POST['token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Csrf::checkToken($token)) {
            echo json_encode(['status' => 'error', 'message' => 'Session expirée ou jeton CSRF invalide.']);
            exit;
        }

        $content = trim($_POST['content'] ?? '');
        $mediaUrl = !empty($_POST['media_url']) ? trim($_POST['media_url']) : null;
        $postType = !empty($_POST['post_type']) ? trim($_POST['post_type']) : 'post';

        // Gestion de l'upload d'image directe si présent
        if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
            $uploadedPath = $this->handleMediaUpload($_FILES['media_file']);
            if ($uploadedPath) {
                $mediaUrl = $uploadedPath;
            }
        }

        if (empty($content) && empty($mediaUrl)) {
            echo json_encode(['status' => 'error', 'message' => 'Le message ne peut pas être vide.']);
            exit;
        }

        try {
            $postId = $this->feedService->createPost((int)$user->id, $content, $mediaUrl, $postType);
            echo json_encode([
                'status' => 'success',
                'message' => 'Publication réussie !',
                'post_id' => $postId
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Suppression d'un post
     */
    public function deletePost(int $id) {
        header('Content-Type: application/json; charset=utf-8');

        $user = UsersManager::getCurrentUser();
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'Non authentifié.']);
            exit;
        }

        $token = $_POST['token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Csrf::checkToken($token)) {
            echo json_encode(['status' => 'error', 'message' => 'Jeton de sécurité invalide.']);
            exit;
        }

        $isAdmin = ($user->role ?? '') === 'admin';
        try {
            $res = $this->feedService->deletePost($id, (int)$user->id, $isAdmin);
            echo json_encode(['status' => $res ? 'success' : 'error']);
        } catch (\Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Like / Unlike
     */
    public function toggleLike(int $id) {
        header('Content-Type: application/json; charset=utf-8');

        $user = UsersManager::getCurrentUser();
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'Connexion requise pour aimer ce post.']);
            exit;
        }

        $reactionType = $_POST['reaction'] ?? 'like';
        try {
            $res = $this->feedService->toggleLike($id, (int)$user->id, $reactionType);
            echo json_encode(['status' => 'success'] + $res);
        } catch (\Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Enregistre un partage
     */
    public function recordShare(int $id) {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $db = \Core\Database::getInstance();
            $db->query("UPDATE social_posts SET shares_count = shares_count + 1 WHERE id = :id", [':id' => $id]);
            echo json_encode(['status' => 'success']);
        } catch (\Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Commentaires : liste
     */
    public function getComments(int $id) {
        header('Content-Type: application/json; charset=utf-8');
        $comments = $this->feedService->getComments($id);
        echo json_encode(['status' => 'success', 'comments' => $comments]);
        exit;
    }

    /**
     * Commentaires : ajout
     */
    public function addComment(int $id) {
        header('Content-Type: application/json; charset=utf-8');

        $user = UsersManager::getCurrentUser();
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'Connexion requise.']);
            exit;
        }

        $token = $_POST['token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Csrf::checkToken($token)) {
            echo json_encode(['status' => 'error', 'message' => 'Jeton CSRF invalide.']);
            exit;
        }

        $content = trim($_POST['content'] ?? '');
        $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

        try {
            $comment = $this->feedService->addComment($id, (int)$user->id, $content, $parentId);
            $email = $user->email ?? '';
            $comment['author_name'] = $user->username ?? 'Moi';
            $comment['author_avatar'] = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?d=mp&s=80';

            echo json_encode(['status' => 'success', 'comment' => $comment]);
        } catch (\Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Commentaires : suppression
     */
    public function deleteComment(int $id) {
        header('Content-Type: application/json; charset=utf-8');

        $user = UsersManager::getCurrentUser();
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'Non authentifié.']);
            exit;
        }

        $isAdmin = ($user->role ?? '') === 'admin';
        try {
            $this->feedService->deleteComment($id, (int)$user->id, $isAdmin);
            echo json_encode(['status' => 'success']);
        } catch (\Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Follow / Unfollow
     */
    public function toggleFollow(int $id) {
        header('Content-Type: application/json; charset=utf-8');

        $user = UsersManager::getCurrentUser();
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'Connexion requise.']);
            exit;
        }

        try {
            $isFollowing = $this->feedService->toggleFollow((int)$user->id, $id);
            echo json_encode(['status' => 'success', 'following' => $isFollowing]);
        } catch (\Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Page de profil public d'un membre : /social/user/{id}
     */
    public function userProfile(int $id) {
        $currentUser = UsersManager::getCurrentUser();
        $currentUserId = $currentUser ? (int)($currentUser->id ?? 0) : null;

        $profile = $this->feedService->getUserProfile($id, $currentUserId);
        if (!$profile) {
            header("HTTP/1.0 404 Not Found");
            echo "Membre introuvable.";
            exit;
        }

        $feedTimeline = $this->feedService->getUserTimeline($id, $currentUserId, 1, 20);

        $response = new PublicResponse();
        $response->setTitle($profile['username'] . ' (@' . $profile['username'] . ') - Profil Communauté');
        $response->hidePageTitle(true);
        Template::addGlobal('HIDE_PAGE_TITLE', true);

        $tpl = new Template(__DIR__ . '/../template/user_profile.tpl');
        $tpl->set('profile', $profile);
        $tpl->set('feedTimeline', $feedTimeline);
        $tpl->set('currentUser', $currentUser);
        $tpl->set('csrfToken', Csrf::token());

        $response->addTemplate($tpl);
        return $response;
    }

    /**
     * Traitement de l'upload de média (Photos, Vidéos / Reels)
     */
    private function handleMediaUpload(array $file): ?string {
        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'video/mp4' => 'mp4',
            'video/webm' => 'webm',
            'video/quicktime' => 'mp4'
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset($allowedMimes[$mime])) {
            return null;
        }

        $ext = $allowedMimes[$mime];
        $uploadDir = (defined('DATA') ? DATA : dirname(__DIR__, 4) . '/data/') . 'upload/social/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = 'media_' . uniqid() . '.' . $ext;
        $target = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            return '/data/upload/social/' . $fileName;
        }

        return null;
    }
}

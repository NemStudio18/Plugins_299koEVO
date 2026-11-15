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

    private function isValidToken(?string $token, ?object $user = null): bool {
        if (empty($token)) {
            return false;
        }
        if ($user && !empty($user->token) && hash_equals((string)$user->token, (string)$token)) {
            return true;
        }
        return Csrf::validate($token, false);
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
        if (!$this->isValidToken($token, $user)) {
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

        $groupId = !empty($_POST['group_id']) ? (int)$_POST['group_id'] : null;
        $visibility = !empty($_POST['visibility']) ? trim($_POST['visibility']) : 'public';

        try {
            $postId = $this->feedService->createPost((int)$user->id, $content, $mediaUrl, $postType, null, $groupId, $visibility);
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
        if (!$this->isValidToken($token, $user)) {
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
     * Like / Reactions sur un post (style Facebook : like, love, haha, sad, angry)
     */
    public function toggleLike(int $id) {
        header('Content-Type: application/json; charset=utf-8');

        $user = UsersManager::getCurrentUser();
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'Connexion requise pour réagir.']);
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
     * Like / Reactions sur un commentaire
     */
    public function toggleCommentLike(int $id) {
        header('Content-Type: application/json; charset=utf-8');

        $user = UsersManager::getCurrentUser();
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'Connexion requise.']);
            exit;
        }

        $reactionType = $_POST['reaction'] ?? 'like';
        try {
            $res = $this->feedService->toggleCommentLike($id, (int)$user->id, $reactionType);
            echo json_encode(['status' => 'success'] + $res);
        } catch (\Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Demande d'ami
     */
    public function requestFriend(int $id) {
        header('Content-Type: application/json; charset=utf-8');
        $user = UsersManager::getCurrentUser();
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'Connexion requise.']);
            exit;
        }
        $token = $_POST['token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->isValidToken($token, $user)) {
            echo json_encode(['status' => 'error', 'message' => 'Session expirée.']);
            exit;
        }
        $res = $this->feedService->sendFriendRequest((int)$user->id, $id);
        echo json_encode($res);
        exit;
    }

    /**
     * Accepter une demande d'ami
     */
    public function acceptFriend(int $id) {
        header('Content-Type: application/json; charset=utf-8');
        $user = UsersManager::getCurrentUser();
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'Connexion requise.']);
            exit;
        }
        $token = $_POST['token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->isValidToken($token, $user)) {
            echo json_encode(['status' => 'error', 'message' => 'Session expirée.']);
            exit;
        }
        $res = $this->feedService->acceptFriendRequest((int)$user->id, $id);
        echo json_encode($res);
        exit;
    }

    /**
     * Retirer un ami
     */
    public function removeFriend(int $id) {
        header('Content-Type: application/json; charset=utf-8');
        $user = UsersManager::getCurrentUser();
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'Connexion requise.']);
            exit;
        }
        $token = $_POST['token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->isValidToken($token, $user)) {
            echo json_encode(['status' => 'error', 'message' => 'Session expirée.']);
            exit;
        }
        $res = $this->feedService->removeFriend((int)$user->id, $id);
        echo json_encode($res);
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
        $user = UsersManager::getCurrentUser();
        $currentUserId = $user ? (int)($user->id ?? 0) : null;
        $comments = $this->feedService->getComments($id, $currentUserId);
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
        if (!$this->isValidToken($token, $user)) {
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
     * Liste des groupes et pages : /social/groups
     */
    public function groupsList() {
        $currentUser = UsersManager::getCurrentUser();
        $currentUserId = $currentUser ? (int)($currentUser->id ?? 0) : null;
        $type = $_GET['type'] ?? 'all';

        $groups = $this->feedService->getGroups($type, $currentUserId, 1, 50);

        $response = new PublicResponse();
        $response->setTitle('Groupes & Pages - Communauté');
        $response->hidePageTitle(true);
        Template::addGlobal('HIDE_PAGE_TITLE', true);

        $tpl = new Template(__DIR__ . '/../template/groups_list.tpl');
        $tpl->set('groups', $groups);
        $tpl->set('currentType', $type);
        $tpl->set('currentUser', $currentUser);
        $tpl->set('csrfToken', Csrf::token());

        $response->addTemplate($tpl);
        return $response;
    }

    /**
     * Création d'une page ou d'un groupe (POST /social/groups/create)
     */
    public function groupCreate() {
        $currentUser = UsersManager::getCurrentUser();
        if (!$currentUser) {
            header('Location: /login');
            exit;
        }

        $token = $_POST['token'] ?? '';
        if (!$this->isValidToken($token, $user)) {
            header('Location: /social/groups?error=csrf');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'group';
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? 'Général');
        $visibility = $_POST['visibility'] ?? 'public';

        $avatar = null;
        if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
            $avatar = $this->handleMediaUpload($_FILES['avatar_file']);
        }

        $cover = null;
        if (isset($_FILES['cover_file']) && $_FILES['cover_file']['error'] === UPLOAD_ERR_OK) {
            $cover = $this->handleMediaUpload($_FILES['cover_file']);
        } elseif (!empty($_POST['cover_url'])) {
            $cover = trim($_POST['cover_url']);
        }

        try {
            $group = $this->feedService->createGroup([
                'name' => $name,
                'type' => $type,
                'description' => $description,
                'category' => $category,
                'visibility' => $visibility,
                'avatar' => $avatar,
                'cover_image' => $cover
            ], (int)$currentUser->id);

            header('Location: ' . $group['url']);
            exit;
        } catch (\Throwable $e) {
            header('Location: /social/groups?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Page d'un groupe ou d'une page : /social/group/{slug}
     */
    public function groupView(string $slug) {
        $currentUser = UsersManager::getCurrentUser();
        $currentUserId = $currentUser ? (int)($currentUser->id ?? 0) : null;

        $group = $this->feedService->getGroup($slug, $currentUserId);
        if (!$group) {
            header("HTTP/1.0 404 Not Found");
            echo "Espace communautaire introuvable.";
            exit;
        }

        $timeline = $this->feedService->getGroupTimeline($group['id'], $currentUserId, 1, 20);

        $response = new PublicResponse();
        $response->setTitle($group['name'] . ' (' . ucfirst($group['type']) . ') - Communauté');
        $response->hidePageTitle(true);
        Template::addGlobal('HIDE_PAGE_TITLE', true);

        $tpl = new Template(__DIR__ . '/../template/group_view.tpl');
        $tpl->set('group', $group);
        $tpl->set('feedTimeline', $timeline);
        $tpl->set('currentUser', $currentUser);
        $tpl->set('csrfToken', Csrf::token());

        $response->addTemplate($tpl);
        return $response;
    }

    /**
     * Rejoint ou quitte un groupe / s'abonne à une page (AJAX POST)
     */
    public function groupToggleJoin(int $id) {
        header('Content-Type: application/json; charset=utf-8');

        $user = UsersManager::getCurrentUser();
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'Veuillez vous connecter.']);
            exit;
        }

        $token = $_POST['token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->isValidToken($token, $user)) {
            echo json_encode(['status' => 'error', 'message' => 'Session expirée.']);
            exit;
        }

        try {
            $res = $this->feedService->toggleGroupJoin($id, (int)$user->id);
            echo json_encode(array_merge(['status' => 'success'], $res));
        } catch (\Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Traitement de l'upload de média (Photos optimisées WebP, Vidéos / Reels)
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

        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return null;
        }

        // Limite taille : max 35 Mo pour vidéo, 15 Mo pour photo
        $maxSizeBytes = 35 * 1024 * 1024;
        if (($file['size'] ?? 0) > $maxSizeBytes) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset($allowedMimes[$mime])) {
            return null;
        }

        $uploadDir = (defined('DATA') ? DATA : dirname(__DIR__, 4) . '/data/') . 'upload/social/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Optimisation automatique des images JPEG/PNG/WebP via ImageOptimizer
        if (in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            $fileName = 'img_' . uniqid() . '.webp';
            $target = $uploadDir . $fileName;

            if (class_exists('\\Utils\\ImageOptimizer')) {
                // Redimensionnement max 1200x1200px, compression WebP 82%, correction EXIF
                $optimized = \Utils\ImageOptimizer::optimizeImage($file['tmp_name'], $target, 1200, 1200, 82);
                if ($optimized && file_exists($target)) {
                    @unlink($file['tmp_name']);
                    return '/data/upload/social/' . $fileName;
                }
            }
        }

        // Fallback pour GIF animés et Vidéos MP4/WebM
        $ext = $allowedMimes[$mime];
        $prefix = (strpos($mime, 'video/') === 0) ? 'video_' : 'media_';
        $fileName = $prefix . uniqid() . '.' . $ext;
        $target = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            return '/data/upload/social/' . $fileName;
        }

        return null;
    }
}

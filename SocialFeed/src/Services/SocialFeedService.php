<?php
namespace SocialFeed\Services;

use Core\Database;
use SocialFeed\Services\Providers\FeedProviderInterface;
use SocialFeed\Services\Providers\UserPostProvider;
use SocialFeed\Services\Providers\BlogFeedProvider;
use SocialFeed\Services\Providers\AgendaFeedProvider;
use SocialFeed\Services\Providers\MeteoFeedProvider;

class SocialFeedService {

    private static ?self $instance = null;
    private Database $db;
    /** @var array<string, FeedProviderInterface> */
    private array $providers = [];

    private function __construct() {
        $this->db = Database::getInstance();
        $this->registerDefaultProviders();
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function registerDefaultProviders(): void {
        $this->registerProvider(new UserPostProvider());
        $this->registerProvider(new BlogFeedProvider());
        $this->registerProvider(new AgendaFeedProvider());
        $this->registerProvider(new MeteoFeedProvider());
    }

    public function registerProvider(FeedProviderInterface $provider): void {
        $this->providers[$provider->getId()] = $provider;
    }

    /**
     * Retourne la liste des fournisseurs actuellement installés et actifs dans le CMS.
     * @return array<string, array{id: string, title: string, icon: string}>
     */
    public function getAvailableProviders(): array {
        $available = [];
        foreach ($this->providers as $id => $provider) {
            if ($provider->isAvailable()) {
                $available[$id] = [
                    'id' => $provider->getId(),
                    'title' => $provider->getTitle(),
                    'icon' => $provider->getIcon()
                ];
            }
        }
        return $available;
    }

    /**
     * Timeline unifiée intelligente avec score et tri
     */
    public function getTimeline(?int $userId = null, int $page = 1, int $limit = 20, ?string $filter = null, string $scope = 'all'): array {
        $items = [];
        $options = ['scope' => $scope];

        if (!empty($filter) && $filter !== 'all') {
            if (isset($this->providers[$filter]) && $this->providers[$filter]->isAvailable()) {
                $items = $this->providers[$filter]->fetchItems($userId, $page, $limit, $options);
            }
        } else {
            // Agréger tous les fournisseurs actifs
            foreach ($this->providers as $provider) {
                if ($provider->isAvailable()) {
                    $providerItems = $provider->fetchItems($userId, $page, $limit, $options);
                    $items = array_merge($items, $providerItems);
                }
            }

            // Calcul du score de pertinence et tri
            foreach ($items as &$item) {
                $score = $item['timestamp'];
                if (!empty($item['pinned'])) {
                    $score += 86400 * 30; // Priorité sur 30 jours
                }
                $score += ($item['likes_count'] ?? 0) * 300;
                $score += ($item['comments_count'] ?? 0) * 600;
                $item['score'] = $score;
                $item['time_ago'] = $this->timeElapsedString($item['created_at']);
            }
            unset($item);

            usort($items, fn($a, $b) => ($b['score'] ?? $b['timestamp']) <=> ($a['score'] ?? $a['timestamp']));

            $offset = max(0, ($page - 1) * $limit);
            $items = array_slice($items, $offset, $limit);
        }

        return $items;
    }

    /**
     * Crée une publication membre
     */
    public function createPost(int $userId, string $content, ?string $mediaUrl = null, string $postType = 'post', ?int $parentId = null): int {
        $content = trim($content);
        if (empty($content) && empty($mediaUrl)) {
            throw new \InvalidArgumentException("Le message ne peut pas être vide.");
        }

        $sql = "INSERT INTO social_posts (user_id, post_type, content, media_url, parent_id, status, created_at, updated_at)
                VALUES (:uid, :type, :content, :media, :parent, 'published', :now, :now)";

        $now = date('Y-m-d H:i:s');
        $this->db->query($sql, [
            ':uid' => $userId,
            ':type' => $postType,
            ':content' => $content,
            ':media' => $mediaUrl,
            ':parent' => $parentId,
            ':now' => $now
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Supprime une publication
     */
    public function deletePost(int $postId, int $userId, bool $isAdmin = false): bool {
        $sqlCheck = "SELECT user_id FROM social_posts WHERE id = :id";
        $rows = $this->db->query($sqlCheck, [':id' => $postId]);
        if (empty($rows)) {
            return false;
        }

        $authorId = (int)((array)$rows[0])['user_id'];
        if (!$isAdmin && $authorId !== $userId) {
            throw new \RuntimeException("Accès refusé : vous n'êtes pas l'auteur de ce post.");
        }

        $this->db->query("DELETE FROM social_posts WHERE id = :id", [':id' => $postId]);
        $this->db->query("DELETE FROM social_comments WHERE post_id = :id", [':id' => $postId]);
        $this->db->query("DELETE FROM social_likes WHERE post_id = :id", [':id' => $postId]);

        return true;
    }

    /**
     * Bascule Like / Unlike
     */
    public function toggleLike(int $postId, int $userId, string $reactionType = 'like'): array {
        $sqlCheck = "SELECT id FROM social_likes WHERE post_id = :pid AND user_id = :uid";
        $existing = $this->db->query($sqlCheck, [':pid' => $postId, ':uid' => $userId]);

        $hasLiked = false;
        if (!empty($existing)) {
            $this->db->query("DELETE FROM social_likes WHERE post_id = :pid AND user_id = :uid", [':pid' => $postId, ':uid' => $userId]);
            $this->db->query("UPDATE social_posts SET likes_count = MAX(0, likes_count - 1) WHERE id = :pid", [':pid' => $postId]);
            $hasLiked = false;
        } else {
            $this->db->query(
                "INSERT INTO social_likes (post_id, user_id, reaction_type, created_at) VALUES (:pid, :uid, :type, :now)",
                [':pid' => $postId, ':uid' => $userId, ':type' => $reactionType, ':now' => date('Y-m-d H:i:s')]
            );
            $this->db->query("UPDATE social_posts SET likes_count = likes_count + 1 WHERE id = :pid", [':pid' => $postId]);
            $hasLiked = true;
        }

        // Compteur frais
        $countRows = $this->db->query("SELECT likes_count FROM social_posts WHERE id = :pid", [':pid' => $postId]);
        $likesCount = !empty($countRows) ? (int)((array)$countRows[0])['likes_count'] : 0;

        return ['has_liked' => $hasLiked, 'likes_count' => $likesCount];
    }

    /**
     * Ajoute un commentaire
     */
    public function addComment(int $postId, int $userId, string $content, ?int $parentId = null): array {
        $content = trim($content);
        if (empty($content)) {
            throw new \InvalidArgumentException("Le commentaire ne peut pas être vide.");
        }

        $now = date('Y-m-d H:i:s');
        $sql = "INSERT INTO social_comments (post_id, user_id, parent_id, content, status, created_at)
                VALUES (:pid, :uid, :parent, :content, 'published', :now)";

        $this->db->query($sql, [
            ':pid' => $postId,
            ':uid' => $userId,
            ':parent' => $parentId,
            ':content' => $content,
            ':now' => $now
        ]);

        $commentId = (int)$this->db->lastInsertId();
        $this->db->query("UPDATE social_posts SET comments_count = comments_count + 1 WHERE id = :pid", [':pid' => $postId]);

        return [
            'id' => $commentId,
            'content' => $content,
            'created_at' => $now,
            'time_ago' => 'À l\'instant'
        ];
    }

    /**
     * Récupère les commentaires d'un post
     */
    public function getComments(int $postId): array {
        $sql = "SELECT c.*, u.username, u.email
                FROM social_comments c
                LEFT JOIN users u ON u.id = c.user_id
                WHERE c.post_id = :pid AND c.status = 'published'
                ORDER BY c.created_at ASC";

        $rows = $this->db->query($sql, [':pid' => $postId]);
        if (!is_array($rows)) {
            return [];
        }

        $comments = [];
        foreach ($rows as $row) {
            $r = (array)$row;
            $email = $r['email'] ?? '';
            $avatar = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?d=mp&s=80';

            $comments[] = [
                'id' => (int)$r['id'],
                'user_id' => (int)$r['user_id'],
                'author_name' => !empty($r['username']) ? $r['username'] : 'Membre #' . $r['user_id'],
                'author_avatar' => $avatar,
                'content' => $r['content'],
                'parent_id' => $r['parent_id'] ? (int)$r['parent_id'] : null,
                'created_at' => $r['created_at'],
                'time_ago' => $this->timeElapsedString($r['created_at'])
            ];
        }

        return $comments;
    }

    /**
     * Supprime un commentaire
     */
    public function deleteComment(int $commentId, int $userId, bool $isAdmin = false): bool {
        $sql = "SELECT post_id, user_id FROM social_comments WHERE id = :id";
        $rows = $this->db->query($sql, [':id' => $commentId]);
        if (empty($rows)) {
            return false;
        }

        $c = (array)$rows[0];
        if (!$isAdmin && (int)$c['user_id'] !== $userId) {
            throw new \RuntimeException("Accès refusé.");
        }

        $this->db->query("DELETE FROM social_comments WHERE id = :id", [':id' => $commentId]);
        $this->db->query("UPDATE social_posts SET comments_count = MAX(0, comments_count - 1) WHERE id = :pid", [':pid' => $c['post_id']]);

        return true;
    }

    /**
     * Toggle Follow / Unfollow
     */
    public function toggleFollow(int $followerId, int $followingId): bool {
        if ($followerId === $followingId) {
            throw new \InvalidArgumentException("Impossible de se suivre soi-même.");
        }

        $check = $this->db->query(
            "SELECT id FROM social_follows WHERE follower_id = :fl AND following_id = :fg",
            [':fl' => $followerId, ':fg' => $followingId]
        );

        if (!empty($check)) {
            $this->db->query(
                "DELETE FROM social_follows WHERE follower_id = :fl AND following_id = :fg",
                [':fl' => $followerId, ':fg' => $followingId]
            );
            return false; // Ne suit plus
        } else {
            $this->db->query(
                "INSERT INTO social_follows (follower_id, following_id, created_at) VALUES (:fl, :fg, :now)",
                [':fl' => $followerId, ':fg' => $followingId, ':now' => date('Y-m-d H:i:s')]
            );
            return true; // Suit désormais
        }
    }

    /**
     * Nettoyage lors de la suppression d'un compte utilisateur
     */
    public function onUserDeleted(int $userId): void {
        $this->db->query("DELETE FROM social_posts WHERE user_id = :uid", [':uid' => $userId]);
        $this->db->query("DELETE FROM social_comments WHERE user_id = :uid", [':uid' => $userId]);
        $this->db->query("DELETE FROM social_likes WHERE user_id = :uid", [':uid' => $userId]);
        $this->db->query("DELETE FROM social_follows WHERE follower_id = :uid OR following_id = :uid", [':uid' => $userId]);
    }

    /**
     * Récupère les informations publiques du profil d'un membre avec ses métriques sociales
     */
    public function getUserProfile(int $profileUserId, ?int $currentUserId = null): ?array {
        $sql = "SELECT id, username, email, role, created_at FROM users WHERE id = :id";
        $rows = $this->db->query($sql, [':id' => $profileUserId]);
        if (empty($rows)) {
            return null;
        }

        $u = (array)$rows[0];
        $email = $u['email'] ?? '';
        $avatar = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?d=mp&s=180';

        // Compteurs
        $pCount = $this->db->query("SELECT COUNT(*) as cnt FROM social_posts WHERE user_id = :uid AND status = 'published'", [':uid' => $profileUserId]);
        $postsCount = !empty($pCount) ? (int)((array)$pCount[0])['cnt'] : 0;

        $flCount = $this->db->query("SELECT COUNT(*) as cnt FROM social_follows WHERE following_id = :uid", [':uid' => $profileUserId]);
        $followersCount = !empty($flCount) ? (int)((array)$flCount[0])['cnt'] : 0;

        $fgCount = $this->db->query("SELECT COUNT(*) as cnt FROM social_follows WHERE follower_id = :uid", [':uid' => $profileUserId]);
        $followingCount = !empty($fgCount) ? (int)((array)$fgCount[0])['cnt'] : 0;

        $isFollowing = false;
        if ($currentUserId && $currentUserId !== $profileUserId) {
            $check = $this->db->query(
                "SELECT 1 FROM social_follows WHERE follower_id = :curr AND following_id = :uid",
                [':curr' => $currentUserId, ':uid' => $profileUserId]
            );
            $isFollowing = !empty($check);
        }

        // Récupération des attributs sociaux (bio, localisation, site, couverture)
        $userObj = class_exists('\\Core\\Auth\\User') ? \Core\Auth\User::findPK($profileUserId) : null;
        $attrs = $userObj ? $userObj->attributes : [];
        $bio = $attrs['bio'] ?? ($attrs['social_bio'] ?? '');
        $location = $attrs['location'] ?? ($attrs['social_location'] ?? '');
        $website = $attrs['website'] ?? ($attrs['social_website'] ?? '');
        $coverImage = $attrs['cover_image'] ?? ($attrs['social_cover'] ?? '');

        return [
            'id' => (int)$u['id'],
            'username' => !empty($u['username']) ? $u['username'] : 'Membre #' . $u['id'],
            'avatar' => $avatar,
            'role' => $u['role'] ?? 'member',
            'created_at' => $u['created_at'],
            'joined_date' => date('F Y', strtotime($u['created_at'] ?? 'now')),
            'posts_count' => $postsCount,
            'followers_count' => $followersCount,
            'following_count' => $followingCount,
            'is_following' => $isFollowing,
            'is_self' => $currentUserId === $profileUserId,
            'bio' => $bio,
            'location' => $location,
            'website' => $website,
            'cover_image' => $coverImage
        ];
    }

    /**
     * Récupère les publications d'un profil spécifique
     */
    public function getUserTimeline(int $profileUserId, ?int $currentUserId = null, int $page = 1, int $limit = 20): array {
        $offset = max(0, ($page - 1) * $limit);
        $sql = "SELECT p.*, u.username, u.email, u.role
                FROM social_posts p
                LEFT JOIN users u ON u.id = p.user_id
                WHERE p.user_id = :uid AND p.status = 'published'
                ORDER BY p.pinned DESC, p.created_at DESC
                LIMIT :limit OFFSET :offset";

        $rows = $this->db->query($sql, [':uid' => $profileUserId, ':limit' => $limit, ':offset' => $offset]);
        if (!is_array($rows)) {
            return [];
        }

        // Likes de l'utilisateur courant
        $likedPostIds = [];
        if ($currentUserId && !empty($rows)) {
            $postIds = array_map(fn($r) => (int)((array)$r)['id'], $rows);
            $inClause = implode(',', array_map('intval', $postIds));
            if (!empty($inClause)) {
                $likeRows = $this->db->query(
                    "SELECT post_id FROM social_likes WHERE user_id = :uid AND post_id IN ({$inClause})",
                    [':uid' => $currentUserId]
                );
                if (is_array($likeRows)) {
                    foreach ($likeRows as $lr) {
                        $likedPostIds[] = (int)((array)$lr)['post_id'];
                    }
                }
            }
        }

        $items = [];
        foreach ($rows as $row) {
            $r = (array)$row;
            $postId = (int)$r['id'];
            $email = $r['email'] ?? '';
            $avatar = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?d=mp&s=120';

            $items[] = [
                'type' => 'post',
                'id' => $postId,
                'title' => null,
                'content' => $r['content'],
                'author_id' => (int)$r['user_id'],
                'author_name' => !empty($r['username']) ? $r['username'] : 'Membre #' . $r['user_id'],
                'author_avatar' => $avatar,
                'author_url' => '/social/user/' . (int)$r['user_id'],
                'media_url' => $r['media_url'],
                'permalink' => '/social#post-' . $postId,
                'created_at' => $r['created_at'],
                'timestamp' => strtotime($r['created_at']),
                'likes_count' => (int)($r['likes_count'] ?? 0),
                'comments_count' => (int)($r['comments_count'] ?? 0),
                'shares_count' => (int)($r['shares_count'] ?? 0),
                'has_liked' => in_array($postId, $likedPostIds),
                'pinned' => (bool)($r['pinned'] ?? false),
                'time_ago' => $this->timeElapsedString($r['created_at']),
                'meta' => [
                    'post_type' => $r['post_type'] ?? 'post',
                    'parent_id' => $r['parent_id'] ?? null
                ]
            ];
        }

        return $items;
    }

    /**
     * Formate une date relative (il y a X minutes, heures...)
     */
    public function timeElapsedString(string $datetime): string {
        $ts = strtotime($datetime);
        if (!$ts) return $datetime;
        $diff = time() - $ts;

        if ($diff < 60) return "À l'instant";
        if ($diff < 3600) return "Il y a " . floor($diff / 60) . " min";
        if ($diff < 86400) return "Il y a " . floor($diff / 3600) . " h";
        if ($diff < 604800) return "Il y a " . floor($diff / 86400) . " j";

        return date('d/m/Y', $ts);
    }
}

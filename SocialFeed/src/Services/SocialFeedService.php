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
     * Crée une publication membre (sur fil public ou dans un groupe/page)
     */
    public function createPost(int $userId, string $content, ?string $mediaUrl = null, string $postType = 'post', ?int $parentId = null, ?int $groupId = null): int {
        $content = trim($content);
        if (empty($content) && empty($mediaUrl)) {
            throw new \InvalidArgumentException("Le message ne peut pas être vide.");
        }

        $sql = "INSERT INTO social_posts (user_id, post_type, content, media_url, parent_id, group_id, status, created_at, updated_at)
                VALUES (:uid, :type, :content, :media, :parent, :gid, 'published', :now, :now)";

        $now = date('Y-m-d H:i:s');
        $this->db->query($sql, [
            ':uid' => $userId,
            ':type' => $postType,
            ':content' => $content,
            ':media' => $mediaUrl,
            ':parent' => $parentId,
            ':gid' => $groupId,
            ':now' => $now
        ]);

        $postId = (int)$this->db->lastInsertId();

        if ($groupId) {
            $this->db->query("UPDATE social_groups SET posts_count = posts_count + 1 WHERE id = :gid", [':gid' => $groupId]);
        }

        return $postId;
    }

    /**
     * Supprime une publication
     */
    public function deletePost(int $postId, int $userId, bool $isAdmin = false): bool {
        $sqlCheck = "SELECT user_id, group_id FROM social_posts WHERE id = :id";
        $rows = $this->db->query($sqlCheck, [':id' => $postId]);
        if (empty($rows)) {
            return false;
        }

        $rowPost = (array)$rows[0];
        $authorId = (int)$rowPost['user_id'];
        $groupId = !empty($rowPost['group_id']) ? (int)$rowPost['group_id'] : null;

        if (!$isAdmin && $authorId !== $userId) {
            throw new \RuntimeException("Accès refusé : vous n'êtes pas l'auteur de ce post.");
        }

        $this->db->query("DELETE FROM social_posts WHERE id = :id", [':id' => $postId]);
        $this->db->query("DELETE FROM social_comments WHERE post_id = :id", [':id' => $postId]);
        $this->db->query("DELETE FROM social_likes WHERE post_id = :id", [':id' => $postId]);

        if ($groupId) {
            $this->db->query("UPDATE social_groups SET posts_count = MAX(0, posts_count - 1) WHERE id = :gid", [':gid' => $groupId]);
        }

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

    /**
     * Génère un slug propre pour URL
     */
    public function slugify(string $text): string {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        if (function_exists('iconv')) {
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        }
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return empty($text) ? 'espace-' . uniqid() : $text;
    }

    /**
     * Crée une nouvelle Page ou un Groupe
     */
    public function createGroup(array $data, int $userId): ?array {
        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            throw new \InvalidArgumentException("Le nom ne peut pas être vide.");
        }

        $type = in_array($data['type'] ?? '', ['group', 'page'], true) ? $data['type'] : 'group';
        $description = trim($data['description'] ?? '');
        $category = trim($data['category'] ?? 'Général');
        $visibility = in_array($data['visibility'] ?? '', ['public', 'private'], true) ? $data['visibility'] : 'public';
        $avatar = $data['avatar'] ?? null;
        $cover = $data['cover_image'] ?? null;

        $baseSlug = $this->slugify($name);
        $slug = $baseSlug;
        $counter = 1;
        while (!empty($this->db->query("SELECT 1 FROM social_groups WHERE slug = :s", [':s' => $slug]))) {
            $counter++;
            $slug = $baseSlug . '-' . $counter;
        }

        $now = date('Y-m-d H:i:s');
        $sql = "INSERT INTO social_groups (type, slug, name, description, category, avatar, cover_image, visibility, owner_id, members_count, posts_count, created_at, updated_at)
                VALUES (:type, :slug, :name, :desc, :cat, :avatar, :cover, :vis, :owner, 1, 0, :now, :now)";

        $this->db->query($sql, [
            ':type' => $type,
            ':slug' => $slug,
            ':name' => $name,
            ':desc' => $description,
            ':cat' => $category,
            ':avatar' => $avatar,
            ':cover' => $cover,
            ':vis' => $visibility,
            ':owner' => $userId,
            ':now' => $now
        ]);

        $groupId = (int)$this->db->lastInsertId();

        $this->db->query(
            "INSERT INTO social_group_members (group_id, user_id, role, status, created_at)
             VALUES (:gid, :uid, 'owner', 'active', :now)",
            [':gid' => $groupId, ':uid' => $userId, ':now' => $now]
        );

        return $this->getGroup($groupId, $userId);
    }

    /**
     * Récupère une Page ou un Groupe par son ID ou son Slug
     */
    public function getGroup(string|int $idOrSlug, ?int $currentUserId = null): ?array {
        $field = is_numeric($idOrSlug) ? 'g.id' : 'g.slug';
        $sql = "SELECT g.*, u.username as owner_username, u.email as owner_email
                FROM social_groups g
                LEFT JOIN users u ON u.id = g.owner_id
                WHERE $field = :param";

        $rows = $this->db->query($sql, [':param' => $idOrSlug]);
        if (empty($rows)) return null;

        $g = (array)$rows[0];
        $isMember = false;
        $memberRole = null;

        if ($currentUserId) {
            $check = $this->db->query(
                "SELECT role, status FROM social_group_members WHERE group_id = :gid AND user_id = :uid",
                [':gid' => (int)$g['id'], ':uid' => $currentUserId]
            );
            if (!empty($check)) {
                $isMember = true;
                $memberRole = ((array)$check[0])['role'] ?? 'member';
            }
        }

        $avatar = $g['avatar'];
        if (empty($avatar)) {
            $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($g['name']) . '&background=1d9bf0&color=fff&size=160';
        }

        return [
            'id' => (int)$g['id'],
            'type' => $g['type'],
            'slug' => $g['slug'],
            'name' => $g['name'],
            'description' => $g['description'] ?? '',
            'category' => $g['category'] ?? 'Général',
            'avatar' => $avatar,
            'cover_image' => $g['cover_image'] ?? '',
            'visibility' => $g['visibility'],
            'owner_id' => (int)$g['owner_id'],
            'owner_username' => $g['owner_username'] ?? 'Membre #' . $g['owner_id'],
            'members_count' => (int)($g['members_count'] ?? 1),
            'posts_count' => (int)($g['posts_count'] ?? 0),
            'created_at' => $g['created_at'],
            'is_member' => $isMember,
            'member_role' => $memberRole,
            'is_owner' => $currentUserId && ($currentUserId === (int)$g['owner_id']),
            'url' => '/social/group/' . $g['slug']
        ];
    }

    /**
     * Récupère la liste des Pages et Groupes
     */
    public function getGroups(string $type = 'all', ?int $userId = null, int $page = 1, int $limit = 30): array {
        $offset = max(0, ($page - 1) * $limit);
        $params = [];
        $where = ["1=1"];

        if ($type === 'group' || $type === 'page') {
            $where[] = "g.type = :type";
            $params[':type'] = $type;
        } elseif ($type === 'my' && $userId) {
            $where[] = "g.id IN (SELECT group_id FROM social_group_members WHERE user_id = :my_uid)";
            $params[':my_uid'] = $userId;
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT g.*, u.username as owner_username
                FROM social_groups g
                LEFT JOIN users u ON u.id = g.owner_id
                WHERE $whereClause
                ORDER BY g.members_count DESC, g.created_at DESC
                LIMIT :limit OFFSET :offset";

        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $rows = $this->db->query($sql, $params);
        if (!is_array($rows)) return [];

        $items = [];
        foreach ($rows as $row) {
            $g = (array)$row;
            $items[] = $this->getGroup((int)$g['id'], $userId);
        }
        return $items;
    }

    /**
     * Rejoint ou quitte un groupe / s'abonne à une page
     */
    public function toggleGroupJoin(int $groupId, int $userId): array {
        $check = $this->db->query(
            "SELECT id, role FROM social_group_members WHERE group_id = :gid AND user_id = :uid",
            [':gid' => $groupId, ':uid' => $userId]
        );

        if (!empty($check)) {
            $role = ((array)$check[0])['role'];
            if ($role === 'owner') {
                return ['joined' => true, 'is_owner' => true, 'message' => "Le créateur ne peut pas quitter son propre espace."];
            }

            $this->db->query("DELETE FROM social_group_members WHERE group_id = :gid AND user_id = :uid", [':gid' => $groupId, ':uid' => $userId]);
            $this->db->query("UPDATE social_groups SET members_count = MAX(1, members_count - 1) WHERE id = :gid", [':gid' => $groupId]);

            $cnt = $this->db->query("SELECT members_count FROM social_groups WHERE id = :gid", [':gid' => $groupId]);
            $newCount = !empty($cnt) ? (int)((array)$cnt[0])['members_count'] : 0;
            return ['joined' => false, 'members_count' => $newCount];
        } else {
            $now = date('Y-m-d H:i:s');
            $this->db->query(
                "INSERT INTO social_group_members (group_id, user_id, role, status, created_at) VALUES (:gid, :uid, 'member', 'active', :now)",
                [':gid' => $groupId, ':uid' => $userId, ':now' => $now]
            );
            $this->db->query("UPDATE social_groups SET members_count = members_count + 1 WHERE id = :gid", [':gid' => $groupId]);

            $cnt = $this->db->query("SELECT members_count FROM social_groups WHERE id = :gid", [':gid' => $groupId]);
            $newCount = !empty($cnt) ? (int)((array)$cnt[0])['members_count'] : 1;
            return ['joined' => true, 'members_count' => $newCount];
        }
    }

    /**
     * Récupère les publications d'un groupe ou d'une page
     */
    public function getGroupTimeline(int $groupId, ?int $currentUserId = null, int $page = 1, int $limit = 20): array {
        $offset = max(0, ($page - 1) * $limit);
        $sql = "SELECT p.*, u.username, u.email, u.role
                FROM social_posts p
                LEFT JOIN users u ON u.id = p.user_id
                WHERE p.group_id = :gid AND p.status = 'published'
                ORDER BY p.pinned DESC, p.created_at DESC
                LIMIT :limit OFFSET :offset";

        $rows = $this->db->query($sql, [':gid' => $groupId, ':limit' => $limit, ':offset' => $offset]);
        if (!is_array($rows)) return [];

        $likedPostIds = [];
        if ($currentUserId) {
            $likes = $this->db->query("SELECT post_id FROM social_likes WHERE user_id = :uid", [':uid' => $currentUserId]);
            if (is_array($likes)) {
                $likedPostIds = array_map(fn($r) => (int)((array)$r)['post_id'], $likes);
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
                'group_id' => $groupId,
                'meta' => [
                    'post_type' => $r['post_type'] ?? 'post',
                    'parent_id' => $r['parent_id'] ?? null
                ]
            ];
        }
        return $items;
    }
}

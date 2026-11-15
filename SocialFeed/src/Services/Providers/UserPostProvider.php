<?php
namespace SocialFeed\Services\Providers;

use Core\Database;

class UserPostProvider implements FeedProviderInterface {

    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getId(): string {
        return 'posts';
    }

    public function getTitle(): string {
        return 'Discussions';
    }

    public function getIcon(): string {
        return 'fa-solid fa-comment-dots';
    }

    public function isAvailable(): bool {
        return true;
    }

    public function fetchItems(?int $currentUserId, int $page = 1, int $limit = 20, array $options = []): array {
        $offset = max(0, ($page - 1) * $limit);
        $scope = $options['scope'] ?? 'all'; // 'all' ou 'following'

        $whereClause = "p.status = 'published'";
        $params = [':limit' => $limit, ':offset' => $offset];

        // Filtrage de visibilité style Facebook :
        if ($currentUserId) {
            $whereClause .= " AND (
                p.visibility = 'public' OR p.visibility IS NULL OR p.visibility = ''
                OR p.user_id = :uid_vis
                OR (p.visibility = 'friends' AND (
                    p.user_id IN (SELECT friend_id FROM social_friends WHERE user_id = :uid_f1 AND status = 'accepted')
                    OR p.user_id IN (SELECT user_id FROM social_friends WHERE friend_id = :uid_f2 AND status = 'accepted')
                ))
                OR (p.visibility = 'followers' AND p.user_id IN (SELECT following_id FROM social_follows WHERE follower_id = :uid_fol))
            )";
            $params[':uid_vis'] = $currentUserId;
            $params[':uid_f1'] = $currentUserId;
            $params[':uid_f2'] = $currentUserId;
            $params[':uid_fol'] = $currentUserId;
        } else {
            $whereClause .= " AND (p.visibility = 'public' OR p.visibility IS NULL OR p.visibility = '')";
        }

        if ($scope === 'following' && $currentUserId) {
            $whereClause .= " AND (p.user_id IN (SELECT following_id FROM social_follows WHERE follower_id = :current_user) OR p.user_id = :current_user_self)";
            $params[':current_user'] = $currentUserId;
            $params[':current_user_self'] = $currentUserId;
        } elseif ($scope === 'friends' && $currentUserId) {
            $whereClause .= " AND (
                p.user_id = :current_user_friend_self
                OR p.user_id IN (SELECT friend_id FROM social_friends WHERE user_id = :cur_f1 AND status = 'accepted')
                OR p.user_id IN (SELECT user_id FROM social_friends WHERE friend_id = :cur_f2 AND status = 'accepted')
            )";
            $params[':current_user_friend_self'] = $currentUserId;
            $params[':cur_f1'] = $currentUserId;
            $params[':cur_f2'] = $currentUserId;
        }

        $sql = "SELECT p.*, u.username, u.email, u.role
                FROM social_posts p
                LEFT JOIN users u ON u.id = p.user_id
                WHERE {$whereClause}
                ORDER BY p.pinned DESC, p.created_at DESC
                LIMIT :limit OFFSET :offset";

        try {
            $rows = $this->db->query($sql, $params);
            if (!is_array($rows)) {
                return [];
            }

            // Récupérer les réactions de l'utilisateur courant pour ces posts
            $userReactions = [];
            if ($currentUserId && !empty($rows)) {
                $postIds = array_map(fn($r) => (int)((array)$r)['id'], $rows);
                $inClause = implode(',', array_map('intval', $postIds));
                if (!empty($inClause)) {
                    $likeRows = $this->db->query(
                        "SELECT post_id, reaction_type FROM social_likes WHERE user_id = :uid AND post_id IN ({$inClause})",
                        [':uid' => $currentUserId]
                    );
                    if (is_array($likeRows)) {
                        foreach ($likeRows as $lr) {
                            $rowArray = (array)$lr;
                            $userReactions[(int)$rowArray['post_id']] = $rowArray['reaction_type'] ?? 'like';
                        }
                    }
                }
            }

            $items = [];
            foreach ($rows as $row) {
                $r = (array)$row;
                $postId = (int)$r['id'];
                $authorName = !empty($r['username']) ? $r['username'] : 'Membre #' . $r['user_id'];
                $email = $r['email'] ?? '';
                $avatar = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?d=mp&s=120';
                $userReaction = $userReactions[$postId] ?? null;

                $items[] = [
                    'type' => 'post',
                    'id' => $postId,
                    'title' => null,
                    'content' => $r['content'],
                    'author_id' => (int)$r['user_id'],
                    'author_name' => $authorName,
                    'author_avatar' => $avatar,
                    'author_url' => '/social/user/' . (int)$r['user_id'],
                    'media_url' => $r['media_url'],
                    'visibility' => $r['visibility'] ?? 'public',
                    'permalink' => '/social#post-' . $postId,
                    'created_at' => $r['created_at'],
                    'timestamp' => strtotime($r['created_at']),
                    'likes_count' => (int)($r['likes_count'] ?? 0),
                    'comments_count' => (int)($r['comments_count'] ?? 0),
                    'shares_count' => (int)($r['shares_count'] ?? 0),
                    'has_liked' => !empty($userReaction),
                    'user_reaction' => $userReaction,
                    'pinned' => (bool)($r['pinned'] ?? false),
                    'meta' => [
                        'post_type' => $r['post_type'] ?? 'post',
                        'parent_id' => $r['parent_id'] ?? null
                    ]
                ];
            }

            return $items;
        } catch (\Throwable $e) {
            error_log("SocialFeed UserPostProvider Error: " . $e->getMessage());
            return [];
        }
    }
}


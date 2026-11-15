<?php
namespace SocialFeed\Services\Providers;

use Core\Plugin\PluginsManager;
use Core\Database;
use Utils\Util;

class BlogFeedProvider implements FeedProviderInterface {

    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getId(): string {
        return 'blog';
    }

    public function getTitle(): string {
        return 'Articles';
    }

    public function getIcon(): string {
        return 'fa-solid fa-newspaper';
    }

    public function isAvailable(): bool {
        return PluginsManager::isActivePlugin('blog');
    }

    public function fetchItems(?int $currentUserId, int $page = 1, int $limit = 20, array $options = []): array {
        if (!$this->isAvailable()) {
            return [];
        }

        $blogFile = (defined('DATA') ? DATA : dirname(__DIR__, 5) . '/data/') . 'plugin/blog/blog.json';
        if (!file_exists($blogFile)) {
            return [];
        }

        $raw = @file_get_contents($blogFile);
        $blogData = json_decode($raw, true);
        if (!is_array($blogData)) {
            return [];
        }

        // Likes pour les articles de blog si table existe
        $likesMap = [];
        $userLikedMap = [];
        try {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->query("SELECT post_id, COUNT(*) as cnt FROM blog_likes GROUP BY post_id");
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $likesMap[(int)$row['post_id']] = (int)$row['cnt'];
            }
            if ($currentUserId) {
                $stmtU = $pdo->prepare("SELECT post_id FROM blog_likes WHERE user_id = :uid");
                $stmtU->execute([':uid' => $currentUserId]);
                while ($row = $stmtU->fetch(\PDO::FETCH_ASSOC)) {
                    $userLikedMap[(int)$row['post_id']] = true;
                }
            }
        } catch (\Throwable $e) {
            // Optionnel
        }

        $items = [];
        foreach ($blogData as $bPost) {
            if (!empty($bPost['draft'])) {
                continue;
            }

            $bId = (int)($bPost['id'] ?? 0);
            $bDate = !empty($bPost['date']) ? strtotime($bPost['date']) : time();
            $title = $bPost['name'] ?? 'Actualité';
            $slug = class_exists('\\Utils\\Util') ? Util::strToUrl($title) : preg_replace('/[^a-z0-9]+/i', '-', strtolower($title));
            $url = '/blog/' . $slug . '-' . $bId . '.html';
            $content = !empty($bPost['seoDesc']) ? $bPost['seoDesc'] : strip_tags(html_entity_decode($bPost['intro'] ?? ($bPost['content'] ?? '')));

            $items[] = [
                'type' => 'blog',
                'id' => $bId,
                'title' => $title,
                'content' => $content,
                'author_name' => $bPost['author'] ?? 'Rédaction',
                'author_avatar' => null,
                'author_url' => '/blog',
                'media_url' => $bPost['img'] ?? null,
                'permalink' => $url,
                'created_at' => date('Y-m-d H:i:s', $bDate),
                'timestamp' => $bDate,
                'likes_count' => $likesMap[$bId] ?? 0,
                'comments_count' => (int)($bPost['comments_count'] ?? 0),
                'shares_count' => 0,
                'has_liked' => !empty($userLikedMap[$bId]),
                'pinned' => false,
                'meta' => [
                    'category' => $bPost['category'] ?? null
                ]
            ];
        }

        // Trier par date décroissante
        usort($items, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        $offset = max(0, ($page - 1) * $limit);
        return array_slice($items, $offset, $limit);
    }
}


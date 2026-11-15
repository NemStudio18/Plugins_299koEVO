<?php
namespace SocialFeed\Services\Providers;

use Core\Plugin\PluginsManager;
use Core\Database;

class AgendaFeedProvider implements FeedProviderInterface {

    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getId(): string {
        return 'agenda';
    }

    public function getTitle(): string {
        return 'Événements';
    }

    public function getIcon(): string {
        return 'fa-solid fa-calendar-days';
    }

    public function isAvailable(): bool {
        return PluginsManager::isActivePlugin('agenda');
    }

    public function fetchItems(?int $currentUserId, int $page = 1, int $limit = 20, array $options = []): array {
        if (!$this->isAvailable()) {
            return [];
        }

        try {
            $today = date('Y-m-d');
            $maxDate = date('Y-m-d', strtotime('+30 days'));

            $sql = "SELECT id, title, description, start_date, end_date, location, proof_file 
                    FROM agenda_events 
                    WHERE (status = 'approved' OR status IS NULL)
                      AND start_date >= :today
                      AND start_date <= :max_date
                    ORDER BY start_date ASC 
                    LIMIT :limit";

            $rows = $this->db->query($sql, [':today' => $today, ':max_date' => $maxDate, ':limit' => $limit]);
            if (!is_array($rows)) {
                return [];
            }

            $items = [];
            foreach ($rows as $row) {
                $r = (array)$row;
                $startTs = strtotime($r['start_date'] ?? 'now');
                $items[] = [
                    'type' => 'event',
                    'id' => $r['id'],
                    'title' => $r['title'],
                    'content' => $r['description'] ?? '',
                    'author_name' => 'Agenda Local',
                    'author_avatar' => null,
                    'author_url' => '/agenda',
                    'media_url' => $r['proof_file'] ?? null,
                    'permalink' => '/agenda/event/' . (int)$r['id'],
                    'created_at' => $r['start_date'],
                    'timestamp' => $startTs,
                    'likes_count' => 0,
                    'comments_count' => 0,
                    'shares_count' => 0,
                    'has_liked' => false,
                    'pinned' => false,
                    'meta' => [
                        'start_date' => $r['start_date'],
                        'end_date' => $r['end_date'] ?? null,
                        'location' => $r['location'] ?? ''
                    ]
                ];
            }

            return $items;
        } catch (\Throwable $e) {
            error_log("SocialFeed AgendaFeedProvider Error: " . $e->getMessage());
            return [];
        }
    }
}

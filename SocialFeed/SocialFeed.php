<?php
namespace SocialFeed;

use Core\Plugin\Plugin;
use Core\Database;

// Autoloader PSR-4 pour le plugin SocialFeed
spl_autoload_register(function ($class) {
    if (strpos($class, 'SocialFeed\\') === 0) {
        $relative = substr($class, 11);
        if (strpos($relative, 'Controllers\\') === 0) {
            $path = str_replace('\\', '/', substr($relative, 12));
            $file = __DIR__ . '/controllers/' . $path . '.php';
        } elseif (strpos($relative, 'Services\\') === 0) {
            $path = str_replace('\\', '/', substr($relative, 9));
            $file = __DIR__ . '/src/Services/' . $path . '.php';
        } else {
            $path = str_replace('\\', '/', $relative);
            $file = __DIR__ . '/src/' . $path . '.php';
        }
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

class SocialFeed extends Plugin {

    public static function install() {
        $db = Database::getInstance();
        $driver = method_exists($db, 'getDriverName') ? $db->getDriverName() : 'mysql';

        if ($driver === 'sqlite') {
            $statements = [
                "CREATE TABLE IF NOT EXISTS social_posts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    post_type TEXT DEFAULT 'post',
                    content TEXT NOT NULL,
                    media_url TEXT NULL,
                    parent_id INTEGER NULL,
                    pinned INTEGER DEFAULT 0,
                    likes_count INTEGER DEFAULT 0,
                    shares_count INTEGER DEFAULT 0,
                    comments_count INTEGER DEFAULT 0,
                    status TEXT DEFAULT 'published',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE INDEX IF NOT EXISTS idx_social_posts_user_date ON social_posts(user_id, created_at)",
                "CREATE INDEX IF NOT EXISTS idx_social_posts_feed_status ON social_posts(status, created_at)",

                "CREATE TABLE IF NOT EXISTS social_likes (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    post_id INTEGER NOT NULL,
                    user_id INTEGER NOT NULL,
                    reaction_type TEXT DEFAULT 'like',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE(post_id, user_id)
                )",
                "CREATE INDEX IF NOT EXISTS idx_social_likes_user ON social_likes(user_id)",

                "CREATE TABLE IF NOT EXISTS social_comments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    post_id INTEGER NOT NULL,
                    user_id INTEGER NOT NULL,
                    parent_id INTEGER NULL,
                    author_name TEXT NULL,
                    content TEXT NOT NULL,
                    status TEXT NOT NULL DEFAULT 'published',
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NULL
                )",
                "CREATE INDEX IF NOT EXISTS idx_social_comments_post_id ON social_comments(post_id)",
                "CREATE INDEX IF NOT EXISTS idx_social_comments_user_id ON social_comments(user_id)",

                "CREATE TABLE IF NOT EXISTS social_follows (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    follower_id INTEGER NOT NULL,
                    following_id INTEGER NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE(follower_id, following_id)
                )",
                "CREATE INDEX IF NOT EXISTS idx_social_follows_follower ON social_follows(follower_id)",
                "CREATE INDEX IF NOT EXISTS idx_social_follows_following ON social_follows(following_id)"
            ];
            try {
                foreach ($statements as $sql) {
                    $db->query($sql);
                }
                return true;
            } catch (\Exception $e) {
                error_log("SocialFeed Install Error (SQLite): " . $e->getMessage());
                return false;
            }
        }

        // Mode MySQL / MariaDB
        $statements = [
            "CREATE TABLE IF NOT EXISTS social_posts (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                post_type VARCHAR(32) DEFAULT 'post',
                content TEXT NOT NULL,
                media_url VARCHAR(500) NULL,
                parent_id BIGINT UNSIGNED NULL,
                pinned TINYINT(1) DEFAULT 0,
                likes_count INT DEFAULT 0,
                shares_count INT DEFAULT 0,
                comments_count INT DEFAULT 0,
                status VARCHAR(20) DEFAULT 'published',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user_date (user_id, created_at),
                INDEX idx_feed_status (status, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS social_likes (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                post_id BIGINT UNSIGNED NOT NULL,
                user_id INT NOT NULL,
                reaction_type VARCHAR(20) DEFAULT 'like',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_post_user (post_id, user_id),
                INDEX idx_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS social_comments (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                post_id BIGINT UNSIGNED NOT NULL,
                user_id INT NOT NULL,
                parent_id BIGINT UNSIGNED NULL,
                author_name VARCHAR(100) NULL,
                content TEXT NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'published',
                created_at DATETIME NOT NULL,
                updated_at DATETIME NULL,
                INDEX idx_post_id (post_id),
                INDEX idx_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS social_follows (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                follower_id INT NOT NULL,
                following_id INT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_follow (follower_id, following_id),
                INDEX idx_follower (follower_id),
                INDEX idx_following (following_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        ];

        try {
            foreach ($statements as $sql) {
                $db->query($sql);
            }
            return true;
        } catch (\Exception $e) {
            error_log("SocialFeed Install Error (MySQL): " . $e->getMessage());
            return false;
        }
    }

    public static function uninstall() {
        return true;
    }
}

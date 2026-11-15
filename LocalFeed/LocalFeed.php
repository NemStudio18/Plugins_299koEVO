<?php
namespace LocalFeed;

use Core\Plugin\Plugin;
use Core\Database;

// Autoloader PSR-4 pour le plugin LocalFeed
spl_autoload_register(function ($class) {
    if (strpos($class, 'LocalFeed\\') === 0) {
        $relative = substr($class, 10);
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

class LocalFeed extends Plugin {

    public static function install() {
        $db = Database::getInstance();
        $driver = method_exists($db, 'getDriverName') ? $db->getDriverName() : 'mysql';

        if ($driver === 'sqlite') {
            $statements = [
                "CREATE TABLE IF NOT EXISTS poi_posts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    poi_id INTEGER NOT NULL,
                    author_user_id INTEGER NOT NULL,
                    post_type TEXT DEFAULT 'news',
                    title TEXT NULL,
                    content TEXT NOT NULL,
                    media_url TEXT NULL,
                    pinned INTEGER DEFAULT 0,
                    likes_count INTEGER DEFAULT 0,
                    shares_count INTEGER DEFAULT 0,
                    comments_count INTEGER DEFAULT 0,
                    status TEXT DEFAULT 'published',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE INDEX IF NOT EXISTS idx_poi_posts_poi_date ON poi_posts(poi_id, created_at)",
                "CREATE INDEX IF NOT EXISTS idx_poi_posts_feed_status ON poi_posts(status, created_at)",
                "CREATE TABLE IF NOT EXISTS poi_post_likes (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    post_id INTEGER NOT NULL,
                    user_id INTEGER NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE(post_id, user_id)
                )",
                "CREATE INDEX IF NOT EXISTS idx_poi_post_likes_user ON poi_post_likes(user_id)",
                "CREATE TABLE IF NOT EXISTS poi_post_shares (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    post_id INTEGER NOT NULL,
                    user_id INTEGER NULL,
                    channel TEXT DEFAULT 'native',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE INDEX IF NOT EXISTS idx_poi_post_shares_post ON poi_post_shares(post_id)",
                "CREATE TABLE IF NOT EXISTS poi_post_comments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    post_id INTEGER NOT NULL,
                    user_id INTEGER NOT NULL,
                    author_name TEXT NULL,
                    content TEXT NOT NULL,
                    status TEXT NOT NULL DEFAULT 'published',
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NULL
                )",
                "CREATE INDEX IF NOT EXISTS idx_poi_post_comments_post_id ON poi_post_comments(post_id)",
                "CREATE INDEX IF NOT EXISTS idx_poi_post_comments_user_id ON poi_post_comments(user_id)",
            ];
            try {
                foreach ($statements as $sql) {
                    $db->query($sql);
                }
                return true;
            } catch (\Exception $e) {
                error_log("LocalFeed Install Error (SQLite): " . $e->getMessage());
                return false;
            }
        }
        
        $sqlPosts = "CREATE TABLE IF NOT EXISTS poi_posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            poi_id BIGINT UNSIGNED NOT NULL,
            author_user_id INT NOT NULL,
            post_type ENUM('news', 'special_offer', 'photo', 'event', 'alert') DEFAULT 'news',
            title VARCHAR(255) NULL,
            content TEXT NOT NULL,
            media_url VARCHAR(500) NULL,
            pinned TINYINT(1) DEFAULT 0,
            likes_count INT DEFAULT 0,
            shares_count INT DEFAULT 0,
            comments_count INT DEFAULT 0,
            status ENUM('published', 'draft', 'archived') DEFAULT 'published',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_poi_date (poi_id, created_at DESC),
            INDEX idx_feed_status (status, created_at DESC)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $sqlLikes = "CREATE TABLE IF NOT EXISTS poi_post_likes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            user_id INT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uk_post_user (post_id, user_id),
            INDEX idx_user (user_id),
            FOREIGN KEY (post_id) REFERENCES poi_posts(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $sqlShares = "CREATE TABLE IF NOT EXISTS poi_post_shares (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            user_id INT NULL,
            channel VARCHAR(50) DEFAULT 'native',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_post (post_id),
            FOREIGN KEY (post_id) REFERENCES poi_posts(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $sqlComments = "CREATE TABLE IF NOT EXISTS poi_post_comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            user_id INT NOT NULL,
            author_name VARCHAR(150) NULL,
            content TEXT NOT NULL,
            status ENUM('published', 'pending', 'hidden') NOT NULL DEFAULT 'published',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            INDEX idx_post_id (post_id),
            INDEX idx_user_id (user_id),
            FOREIGN KEY (post_id) REFERENCES poi_posts(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        try {
            $db->query($sqlPosts);
            $db->query($sqlLikes);
            $db->query($sqlShares);
            $db->query($sqlComments);
            return true;
        } catch (\Exception $e) {
            error_log("LocalFeed Install Error: " . $e->getMessage());
            return false;
        }
    }

    public static function onBeforeRun() {
        // Enregistre les routes
    }
}

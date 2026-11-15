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
                    group_id INTEGER NULL,
                    visibility TEXT DEFAULT 'public',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE INDEX IF NOT EXISTS idx_social_posts_user_date ON social_posts(user_id, created_at)",
                "CREATE INDEX IF NOT EXISTS idx_social_posts_feed_status ON social_posts(status, created_at)",
                "CREATE INDEX IF NOT EXISTS idx_social_posts_group_id ON social_posts(group_id)",

                "CREATE TABLE IF NOT EXISTS social_groups (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    type TEXT NOT NULL DEFAULT 'group',
                    slug TEXT NOT NULL UNIQUE,
                    name TEXT NOT NULL,
                    description TEXT NULL,
                    category TEXT NULL,
                    avatar TEXT NULL,
                    cover_image TEXT NULL,
                    visibility TEXT NOT NULL DEFAULT 'public',
                    owner_id INTEGER NOT NULL,
                    members_count INTEGER DEFAULT 1,
                    posts_count INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE INDEX IF NOT EXISTS idx_social_groups_slug ON social_groups(slug)",
                "CREATE INDEX IF NOT EXISTS idx_social_groups_type ON social_groups(type)",
                "CREATE INDEX IF NOT EXISTS idx_social_groups_owner ON social_groups(owner_id)",

                "CREATE TABLE IF NOT EXISTS social_group_members (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    group_id INTEGER NOT NULL,
                    user_id INTEGER NOT NULL,
                    role TEXT NOT NULL DEFAULT 'member',
                    status TEXT NOT NULL DEFAULT 'active',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE(group_id, user_id)
                )",
                "CREATE INDEX IF NOT EXISTS idx_social_group_members_user ON social_group_members(user_id)",
                "CREATE INDEX IF NOT EXISTS idx_social_group_members_group ON social_group_members(group_id)",

                "CREATE TABLE IF NOT EXISTS social_likes (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    post_id INTEGER NOT NULL,
                    user_id INTEGER NOT NULL,
                    reaction_type TEXT DEFAULT 'like',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE(post_id, user_id)
                )",
                "CREATE INDEX IF NOT EXISTS idx_social_likes_user ON social_likes(user_id)",

                "CREATE TABLE IF NOT EXISTS social_comment_likes (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    comment_id INTEGER NOT NULL,
                    user_id INTEGER NOT NULL,
                    reaction_type TEXT DEFAULT 'like',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE(comment_id, user_id)
                )",
                "CREATE INDEX IF NOT EXISTS idx_social_comment_likes_cid ON social_comment_likes(comment_id)",
                "CREATE INDEX IF NOT EXISTS idx_social_comment_likes_user ON social_comment_likes(user_id)",

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
                "CREATE INDEX IF NOT EXISTS idx_social_follows_following ON social_follows(following_id)",

                "CREATE TABLE IF NOT EXISTS social_friends (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    friend_id INTEGER NOT NULL,
                    status TEXT NOT NULL DEFAULT 'pending',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE(user_id, friend_id)
                )",
                "CREATE INDEX IF NOT EXISTS idx_social_friends_user ON social_friends(user_id, status)",
                "CREATE INDEX IF NOT EXISTS idx_social_friends_friend ON social_friends(friend_id, status)"
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
                group_id BIGINT UNSIGNED NULL,
                visibility VARCHAR(20) DEFAULT 'public',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user_date (user_id, created_at),
                INDEX idx_feed_status (status, created_at),
                INDEX idx_group_id (group_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS social_groups (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                type VARCHAR(20) NOT NULL DEFAULT 'group',
                slug VARCHAR(100) NOT NULL UNIQUE,
                name VARCHAR(150) NOT NULL,
                description TEXT NULL,
                category VARCHAR(80) NULL,
                avatar VARCHAR(500) NULL,
                cover_image VARCHAR(500) NULL,
                visibility VARCHAR(20) NOT NULL DEFAULT 'public',
                owner_id INT NOT NULL,
                members_count INT DEFAULT 1,
                posts_count INT DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_slug (slug),
                INDEX idx_type (type),
                INDEX idx_owner (owner_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS social_group_members (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                group_id BIGINT UNSIGNED NOT NULL,
                user_id INT NOT NULL,
                role VARCHAR(20) NOT NULL DEFAULT 'member',
                status VARCHAR(20) NOT NULL DEFAULT 'active',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_group_user (group_id, user_id),
                INDEX idx_user (user_id),
                INDEX idx_group (group_id)
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

            "CREATE TABLE IF NOT EXISTS social_comment_likes (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                comment_id BIGINT UNSIGNED NOT NULL,
                user_id INT NOT NULL,
                reaction_type VARCHAR(20) DEFAULT 'like',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_comment_user (comment_id, user_id),
                INDEX idx_comment (comment_id),
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS social_friends (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                friend_id INT NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_friends (user_id, friend_id),
                INDEX idx_user_status (user_id, status),
                INDEX idx_friend_status (friend_id, status)
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


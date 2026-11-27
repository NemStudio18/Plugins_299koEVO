<?php

namespace Blog\Services;

use Blog\Entities\News;
use Blog\Entities\CategoriesManager;
use Core\Auth\CurrentUser;
use Core\Auth\UsersManager;
use Core\Navigation\AccessPolicyEvaluator;
use Core\Navigation\ConditionContext;

defined('ROOT') OR exit('Access denied!');

class BlogAccessService
{
    /**
     * Check if the current user is an administrator.
     */
    public static function isAdmin(): bool
    {
        $user = UsersManager::getCurrentUser();
        $evaluator = AccessPolicyEvaluator::getInstance();
        $ctx = new ConditionContext($user);
        if ($evaluator->isAdmin($ctx)) {
            return true;
        }
        return CurrentUser::get()->isAdmin();
    }

    /**
     * Get the group/role slugs of the current user.
     */
    public static function getUserGroups(): array
    {
        $currentUser = CurrentUser::get();
        if (!$currentUser->isLogged()) {
            return [];
        }
        $groups = [];
        if ($currentUser->role()) {
            $groups[] = $currentUser->role();
        }
        $user = UsersManager::getCurrentUser();
        if ($user && method_exists($user, 'getGroup') && $user->getGroup()) {
            $groups[] = $user->getGroup()->getSlug();
        }
        return array_values(array_unique(array_filter($groups)));
    }

    /**
     * Check if the current user can access a category.
     */
    public static function canAccessCategory($category): bool
    {
        if (self::isAdmin()) {
            return true;
        }
        $allowed = $category->pluginArgs['allowedGroups'] ?? [];
        if (empty($allowed) || !is_array($allowed)) {
            return true;
        }
        $userGroups = self::getUserGroups();
        return !empty(array_intersect($userGroups, $allowed));
    }

    /**
     * Check if the current user can access a news article.
     * Evaluates both article-level restrictions and category-level restrictions.
     */
    public static function canAccessNews(News $news, ?CategoriesManager $categoriesManager = null): bool
    {
        if (self::isAdmin()) {
            return true;
        }

        $userGroups = self::getUserGroups();

        // 1. Article-level restriction
        $newsAllowed = $news->getAllowedGroups();
        if (!empty($newsAllowed) && is_array($newsAllowed)) {
            if (empty(array_intersect($userGroups, $newsAllowed))) {
                return false;
            }
        }

        // 2. Category-level restriction
        if ($categoriesManager === null) {
            $categoriesManager = new CategoriesManager();
        }
        $itemId = (int) $news->getId();
        foreach ($categoriesManager->getCategories() as $cat) {
            $catItems = array_map('intval', (array)($cat->items ?? []));
            if (in_array($itemId, $catItems, true)) {
                $catAllowed = $cat->pluginArgs['allowedGroups'] ?? [];
                if (!empty($catAllowed) && is_array($catAllowed)) {
                    if (empty(array_intersect($userGroups, $catAllowed))) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Check if an article has any access restriction (on the article or via any parent category).
     */
    public static function isItemRestricted(News $news, ?CategoriesManager $categoriesManager = null): bool
    {
        if ($news->isRestricted()) {
            return true;
        }
        if ($categoriesManager === null) {
            $categoriesManager = new CategoriesManager();
        }
        $itemId = (int) $news->getId();
        foreach ($categoriesManager->getCategories() as $cat) {
            $catItems = array_map('intval', (array)($cat->items ?? []));
            if (in_array($itemId, $catItems, true)) {
                $catAllowed = $cat->pluginArgs['allowedGroups'] ?? [];
                if (!empty($catAllowed) && is_array($catAllowed)) {
                    return true;
                }
            }
        }
        return false;
    }
}


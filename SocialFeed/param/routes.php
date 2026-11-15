<?php
defined("ROOT") or exit("No direct script access allowed");

$router = \Core\Router\Router::getInstance();

// Flux public / Social Wall
$router->map("GET", "/social[/?]", "SocialFeed\\Controllers\\SocialFeedController#page", "socialfeed-page");
$router->map("GET", "/feed[/?]", "SocialFeed\\Controllers\\SocialFeedController#page", "socialfeed-page-feed");
$router->map("GET", "/social/feed", "SocialFeed\\Controllers\\SocialFeedController#feed", "socialfeed-ajax-feed");

// Publications des membres
$router->map("POST", "/social/publish", "SocialFeed\\Controllers\\SocialFeedController#publish", "socialfeed-publish");
$router->map("POST|DELETE", "/social/post/[i:id]/delete", "SocialFeed\\Controllers\\SocialFeedController#deletePost", "socialfeed-post-delete");

// Interactions sociales (Likes, Réactions)
$router->map("POST", "/social/react", "SocialFeed\\Controllers\\SocialFeedController#react", "socialfeed-react");
$router->map("POST", "/social/post/[i:id]/like", "SocialFeed\\Controllers\\SocialFeedController#toggleLike", "socialfeed-post-like");
$router->map("POST", "/social/post/[i:id]/share", "SocialFeed\\Controllers\\SocialFeedController#recordShare", "socialfeed-post-share");

// Commentaires
$router->map("GET", "/social/post/[i:id]/comments", "SocialFeed\\Controllers\\SocialFeedController#getComments", "socialfeed-post-comments");
$router->map("POST", "/social/post/[i:id]/comment", "SocialFeed\\Controllers\\SocialFeedController#addComment", "socialfeed-post-comment-add");
$router->map("POST|DELETE", "/social/comment/[i:id]/delete", "SocialFeed\\Controllers\\SocialFeedController#deleteComment", "socialfeed-comment-delete");

// Abonnements (Follow) & Profils
$router->map("POST", "/social/user/[i:id]/follow", "SocialFeed\\Controllers\\SocialFeedController#toggleFollow", "socialfeed-user-follow");
$router->map("GET", "/social/user/[i:id]", "SocialFeed\\Controllers\\SocialFeedController#userProfile", "socialfeed-user-profile");

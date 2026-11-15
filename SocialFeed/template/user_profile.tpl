<div class="social-profile-wrapper">
    <!-- 1. BANNIÈRE & EN-TÊTE DU PROFIL (FACEBOOK / X STYLE) -->
    <div class="profile-header-card">
        <div class="profile-cover-banner" <?php if (!empty($profile['cover_image'])): ?>style="background-image: url('<?= htmlspecialchars($profile['cover_image']) ?>'); background-size: cover; background-position: center;"<?php endif; ?>>
            <div class="cover-gradient-overlay"></div>
        </div>

        <div class="profile-identity-bar">
            <div class="avatar-container">
                <img src="<?= htmlspecialchars($profile['avatar']) ?>" alt="<?= htmlspecialchars($profile['username']) ?>" class="profile-avatar-large" />
            </div>

            <div class="profile-action-btn-wrap">
                <?php if (!empty($profile['is_self'])): ?>
                    <a href="/profile/edit" class="btn btn-profile-edit">
                        <i class="fa-solid fa-user-pen"></i> Modifier mon profil
                    </a>
                <?php else: ?>
                    <button class="btn btn-follow <?= !empty($profile['is_following']) ? 'following' : '' ?>" id="btn-toggle-profile-follow" data-user-id="<?= (int)$profile['id'] ?>">
                        <span class="text-follow"><i class="fa-solid fa-user-plus"></i> S'abonner</span>
                        <span class="text-following"><i class="fa-solid fa-check"></i> Abonné</span>
                        <span class="text-unfollow"><i class="fa-solid fa-user-xmark"></i> Se désabonner</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="profile-info-content">
            <div class="name-badge-row">
                <h1 class="profile-display-name"><?= htmlspecialchars($profile['username']) ?></h1>
                <?php if ($profile['role'] === 'admin'): ?>
                    <span class="badge-role-admin" title="Administrateur"><i class="fa-solid fa-shield-halved"></i> Admin</span>
                <?php endif; ?>
            </div>
            <span class="profile-handle">@<?= htmlspecialchars($profile['username']) ?></span>

            <?php if (!empty($profile['bio'])): ?>
                <p class="profile-bio-text"><?= nl2br(htmlspecialchars($profile['bio'])) ?></p>
            <?php endif; ?>

            <div class="profile-meta-row">
                <?php if (!empty($profile['location'])): ?>
                    <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($profile['location']) ?></span>
                <?php endif; ?>
                <?php if (!empty($profile['website'])): ?>
                    <span><i class="fa-solid fa-link"></i> <a href="<?= htmlspecialchars($profile['website']) ?>" target="_blank" rel="noopener nofollow"><?= htmlspecialchars(preg_replace('#^https?://#', '', $profile['website'])) ?></a></span>
                <?php endif; ?>
                <span><i class="fa-regular fa-calendar"></i> A rejoint en <?= htmlspecialchars($profile['joined_date']) ?></span>
            </div>

            <!-- COMPTEURS STATS (POSTS, ABONNÉS, ABONNEMENTS) -->
            <div class="profile-stats-counters">
                <div class="stat-pill">
                    <strong><?= (int)$profile['posts_count'] ?></strong>
                    <span>Publications</span>
                </div>
                <div class="stat-pill">
                    <strong id="followers-count-val"><?= (int)$profile['followers_count'] ?></strong>
                    <span>Abonnés</span>
                </div>
                <div class="stat-pill">
                    <strong><?= (int)$profile['following_count'] ?></strong>
                    <span>Abonnements</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. ONGLET DU MUR (PUBLICATIONS DU MEMBRE) -->
    <div class="profile-wall-container">
        <div class="wall-header-title">
            <h3><i class="fa-solid fa-stream text-blue"></i> Mur de <?= htmlspecialchars($profile['username']) ?></h3>
        </div>

        <div id="social-feed-container" class="social-feed-list">
            <?php include (defined('ROOT') ? ROOT : '') . 'plugin/SocialFeed/template/feed_items.tpl'; ?>
        </div>
    </div>
</div>

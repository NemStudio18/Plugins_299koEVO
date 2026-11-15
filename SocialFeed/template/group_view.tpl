<div class="social-group-view-wrapper">
    <!-- 1. EN-TÊTE DE LA PAGE OU DU GROUPE -->
    <div class="profile-header-card group-header-card">
        <div class="profile-cover-banner" <?php if (!empty($group['cover_image'])): ?>style="background-image: url('<?= htmlspecialchars($group['cover_image']) ?>'); background-size: cover; background-position: center;"<?php endif; ?>>
            <div class="cover-gradient-overlay"></div>
            <div class="group-header-tags">
                <span class="badge-group-type badge-<?= htmlspecialchars($group['type']) ?>">
                    <?= ($group['type'] === 'page') ? '<i class="fa-solid fa-flag"></i> Page officielle' : '<i class="fa-solid fa-users"></i> Groupe' ?>
                </span>
                <span class="badge-group-vis badge-<?= htmlspecialchars($group['visibility']) ?>">
                    <?= ($group['visibility'] === 'private') ? '<i class="fa-solid fa-lock"></i> Privé' : '<i class="fa-solid fa-globe"></i> Public' ?>
                </span>
            </div>
        </div>

        <div class="profile-identity-bar">
            <div class="avatar-container">
                <img src="<?= htmlspecialchars($group['avatar']) ?>" alt="<?= htmlspecialchars($group['name']) ?>" class="profile-avatar-large" />
            </div>

            <div class="profile-action-btn-wrap">
                <?php if (!empty($currentUser)): ?>
                    <?php if (!empty($group['is_owner'])): ?>
                        <span class="btn btn-profile-edit" style="cursor: default;">
                            <i class="fa-solid fa-crown text-gold"></i> Fondateur
                        </span>
                    <?php else: ?>
                        <button type="button" class="btn btn-follow <?= !empty($group['is_member']) ? 'following' : '' ?>" id="btn-toggle-group-join" data-group-id="<?= (int)$group['id'] ?>">
                            <span class="text-follow"><i class="fa-solid fa-plus"></i> <?= ($group['type'] === 'page') ? 'S\'abonner' : 'Rejoindre le groupe' ?></span>
                            <span class="text-following"><i class="fa-solid fa-check"></i> <?= ($group['type'] === 'page') ? 'Abonné' : 'Membre' ?></span>
                            <span class="text-unfollow"><i class="fa-solid fa-xmark"></i> <?= ($group['type'] === 'page') ? 'Se désabonner' : 'Quitter' ?></span>
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="/login" class="btn btn-follow"><i class="fa-solid fa-right-to-bracket"></i> Connexion pour rejoindre</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="profile-info-content">
            <div class="name-badge-row">
                <h1 class="profile-display-name"><?= htmlspecialchars($group['name']) ?></h1>
            </div>
            <span class="profile-handle"><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($group['category']) ?> · Fondé par @<?= htmlspecialchars($group['owner_username']) ?></span>

            <?php if (!empty($group['description'])): ?>
                <p class="profile-bio-text"><?= nl2br(htmlspecialchars($group['description'])) ?></p>
            <?php endif; ?>

            <div class="profile-stats-counters">
                <div class="stat-pill">
                    <strong id="group-members-count-val"><?= (int)$group['members_count'] ?></strong>
                    <span><?= ($group['type'] === 'page') ? 'Abonnés' : 'Membres' ?></span>
                </div>
                <div class="stat-pill">
                    <strong><?= (int)$group['posts_count'] ?></strong>
                    <span>Publications</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. COMPOSITEUR DE POST (SI AUTORISÉ) -->
    <?php if (!empty($currentUser) && (!empty($group['is_member']) || $group['visibility'] === 'public')): ?>
        <div class="group-composer-wrap">
            <?php 
                // Injection de group_id dans publish_box
                $targetGroupId = (int)$group['id'];
                include (defined('ROOT') ? ROOT : '') . 'plugin/SocialFeed/template/publish_box.tpl'; 
            ?>
            <script>
                // Injecte automatiquement group_id dans le formulaire
                document.addEventListener('DOMContentLoaded', () => {
                    const form = document.getElementById('social-publish-form');
                    if (form && !form.querySelector('input[name="group_id"]')) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'group_id';
                        input.value = '<?= $targetGroupId ?>';
                        form.appendChild(input);
                    }
                });
            </script>
        </div>
    <?php elseif (empty($currentUser)): ?>
        <div class="social-login-callout">
            <div class="callout-text">
                <h4>Rejoignez cet espace !</h4>
                <p>Connectez-vous pour échanger avec les membres et suivre les actualités de cet espace.</p>
            </div>
            <div class="callout-actions">
                <a href="/login" class="btn btn-primary btn-sm"><i class="fa-solid fa-right-to-bracket"></i> Connexion</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- 3. MUR DE PUBLICATIONS DU GROUPE -->
    <div class="profile-wall-container">
        <div class="wall-header-title">
            <h3><i class="fa-solid fa-stream text-blue"></i> Publications de l'espace</h3>
        </div>

        <div id="social-feed-container" class="social-feed-list">
            <?php include (defined('ROOT') ? ROOT : '') . 'plugin/SocialFeed/template/feed_items.tpl'; ?>
        </div>
    </div>
</div>

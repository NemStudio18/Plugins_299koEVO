<div class="socialfeed-page-wrapper">
    <div class="socialfeed-container">

        <!-- COLONNE PRINCIPALE (TIMELINE) -->
        <main class="socialfeed-main">

            <!-- BARRE DE FILTRES DYNAMIQUES & SCOPE -->
            <div class="social-filters-bar">
                <?php if (!empty($currentUser)): ?>
                    <div class="social-scope-tabs">
                        <a href="/social?scope=all" class="scope-tab <?= ($activeScope ?? 'all') === 'all' ? 'active' : '' ?>">
                            <i class="fa-solid fa-globe"></i> Pour vous
                        </a>
                        <a href="/social?scope=following" class="scope-tab <?= ($activeScope ?? '') === 'following' ? 'active' : '' ?>">
                            <i class="fa-solid fa-user-group"></i> Abonnements
                        </a>
                    </div>
                <?php endif; ?>

                <!-- FILTRES PAR SOURCE DE CONTENU (GÉNÉRÉS DYNAMIQUEMENT SELON PLUGINS ACTIFS) -->
                <div class="social-source-chips">
                    <button class="source-chip <?= empty($activeFilter) || $activeFilter === 'all' ? 'active' : '' ?>" data-filter="all">
                        <i class="fa-solid fa-layer-group"></i> Tout
                    </button>
                    <?php if (!empty($providers)): ?>
                        <?php foreach ($providers as $provId => $prov): ?>
                            <button class="source-chip <?= ($activeFilter ?? '') === $provId ? 'active' : '' ?>" data-filter="<?= htmlspecialchars($provId) ?>">
                                <i class="<?= htmlspecialchars($prov['icon']) ?>"></i> <?= htmlspecialchars($prov['title']) ?>
                            </button>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- BOÎTE DE PUBLICATION -->
            <?php include (defined('ROOT') ? ROOT : '') . 'plugin/SocialFeed/template/publish_box.tpl'; ?>

            <!-- CONTENEUR DU FLUX (TIMELINE) -->
            <div id="social-feed-container" class="social-feed-list" data-page="1" data-filter="<?= htmlspecialchars($activeFilter ?? 'all') ?>" data-scope="<?= htmlspecialchars($activeScope ?? 'all') ?>">
                <?php include (defined('ROOT') ? ROOT : '') . 'plugin/SocialFeed/template/feed_items.tpl'; ?>
            </div>

            <!-- CHARGEMENT INFINI / LOAD MORE -->
            <div class="social-feed-footer">
                <button id="btn-load-more" class="btn-load-more" style="display: none;">
                    <i class="fa-solid fa-spinner fa-spin"></i> Chargement de la suite...
                </button>
                <div id="feed-end-sentinel" class="feed-sentinel"></div>
            </div>
        </main>

        <!-- COLONNE LATÉRALE (SIDEBAR WIDGETS) -->
        <aside class="socialfeed-sidebar">
            <div class="sidebar-widget widget-community-card">
                <div class="widget-header">
                    <i class="fa-solid fa-users text-gold"></i>
                    <h3>Communauté <?= htmlspecialchars($siteName ?? 'Déclic') ?></h3>
                </div>
                <p>Bienvenue sur le fil communautaire ! Partagez vos pensées, réagissez aux publications et découvrez les nouveautés du site en direct.</p>
            </div>

            <div class="sidebar-widget widget-sources-info">
                <h4><i class="fa-solid fa-plug text-cyan"></i> Sources actives</h4>
                <ul class="sources-list">
                    <li><i class="fa-solid fa-check text-emerald"></i> Publications des membres</li>
                    <?php if (!empty($providers['blog'])): ?>
                        <li><i class="fa-solid fa-check text-emerald"></i> Nouveaux articles de Blog</li>
                    <?php endif; ?>
                    <?php if (!empty($providers['agenda'])): ?>
                        <li><i class="fa-solid fa-check text-emerald"></i> Événements de l'Agenda</li>
                    <?php endif; ?>
                    <?php if (!empty($providers['meteo'])): ?>
                        <li><i class="fa-solid fa-check text-emerald"></i> Bulletin Météo du jour</li>
                    <?php endif; ?>
                </ul>
            </div>
        </aside>

    </div>
</div>

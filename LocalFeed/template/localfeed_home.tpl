{% set HIDE_PAGE_TITLE = true %}
<div class="localfeed-home-wrapper">
    <div class="localfeed-container" style="padding-top: 15px; max-width: 960px; margin: 0 auto;">
        <!-- En-tête du flux -->
        <div style="text-align: center; margin-bottom: 16px;">
            <div class="localfeed-title-badge">
                <i class="fa-solid fa-bolt-lightning text-gold"></i> Fil d'actualités en direct
            </div>
        </div>

        <!-- Filtres rapides -->
        <div class="localfeed-filters">
            <button class="feed-filter-btn active" data-filter="all" onclick="filterFeed('all', this)" title="Afficher tout le flux">
                <i class="fa-solid fa-layer-group"></i> Tout
            </button>
            <button class="feed-filter-btn" data-filter="posts" onclick="filterFeed('posts', this)" title="Publications">
                <i class="fa-solid fa-store"></i> Publications
            </button>
            <button class="feed-filter-btn" data-filter="events" onclick="filterFeed('events', this)" title="Événements à venir">
                <i class="fa-solid fa-calendar-check"></i> Événements
            </button>
            <button class="feed-filter-btn" data-filter="blog" onclick="filterFeed('blog', this)" title="Articles de blog">
                <i class="fa-solid fa-newspaper"></i> Blog
            </button>
            <button class="feed-filter-btn" data-filter="offers" onclick="filterFeed('offers', this)" title="Bons plans et offres">
                <i class="fa-solid fa-tag"></i> Bons Plans
            </button>
        </div>

        <!-- Bandeau "nouvelles publications" -->
        <button type="button" id="localfeed-new-banner" class="localfeed-new-banner" hidden onclick="LiveFeed.showFresh()">
            <i class="fa-solid fa-arrow-up"></i> <span class="lnb-text">Nouvelles publications</span>
        </button>

        <!-- Conteneur du Flux Infini -->
        <div id="localfeed-stream" class="localfeed-stream">
            <?php 
                $timeline = $feedTimeline ?? ['items' => [], 'has_more' => false, 'page' => 1];
                $filter = 'all';
                include (is_file(__DIR__ . '/feed_items.tpl') ? __DIR__ . '/feed_items.tpl' : PLUGINS . 'LocalFeed/template/feed_items.tpl');
            ?>
        </div>
    </div>
</div>

<link rel="stylesheet" href="/plugin/LocalFeed/public/css/localfeed.css?v=1.1.0" />
<script src="/plugin/LocalFeed/public/js/localfeed.js?v=1.1.0"></script>

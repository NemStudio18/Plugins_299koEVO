de<?php
if (!function_exists('formatSocialText')) {
    function formatSocialText(string $raw): string {
        $escaped = htmlspecialchars($raw, ENT_QUOTES, 'UTF-8');
        // 1. Gras **texte**
        $escaped = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $escaped);
        // 2. Italique *texte*
        $escaped = preg_replace('/(?<!\*)\*(?!\*)(.*?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $escaped);
        // 3. Souligné &lt;u&gt;texte&lt;/u&gt;
        $escaped = preg_replace('/&lt;u&gt;(.*?)&lt;\/u&gt;/is', '<u>$1</u>', $escaped);
        // 4. Hashtags #mot
        $escaped = preg_replace('/(?<!\w)#([a-zA-Z0-9_\x{00C0}-\x{00FF}]+)/u', '<span class="hashtag">#$1</span>', $escaped);
        // 5. Mentions @pseudo
        $escaped = preg_replace('/(?<!\w)@([a-zA-Z0-9_]+)/', '<span class="mention">@$1</span>', $escaped);
        // 6. Liens automatiques
        $escaped = preg_replace('/(https?:\/\/[^\s<]+)/i', '<a href="$1" target="_blank" rel="noopener noreferrer" class="post-link">$1</a>', $escaped);
        return nl2br($escaped);
    }
}
?>

<?php if (empty($feedTimeline)): ?>
    <div class="social-empty-state">
        <i class="fa-solid fa-wind empty-icon"></i>
        <h3>Le fil est calme pour l'instant</h3>
        <p>Soyez le premier à partager une pensée ou une actualité avec la communauté !</p>
    </div>
<?php else: ?>
    <?php foreach ($feedTimeline as $item): ?>
        <?php $type = $item['type'] ?? 'post'; ?>

        <?php if ($type === 'post'): ?>
            <!-- CARTE POST MEMBRE -->
            <article class="social-card social-post-card" id="post-<?= (int)$item['id'] ?>" data-post-id="<?= (int)$item['id'] ?>">
                <div class="card-header">
                    <a href="<?= htmlspecialchars($item['author_url'] ?? ('/social/user/' . (int)($item['author_id'] ?? 1))) ?>" class="author-avatar-link">
                        <img src="<?= htmlspecialchars($item['author_avatar'] ?? '/common/img/default-avatar.png') ?>" alt="<?= htmlspecialchars($item['author_name'] ?? '') ?>" class="user-avatar" />
                    </a>
                    <div class="header-meta">
                        <div class="author-row">
                            <a href="<?= htmlspecialchars($item['author_url'] ?? ('/social/user/' . (int)($item['author_id'] ?? 1))) ?>" class="author-name-link">
                                <?= htmlspecialchars($item['author_name'] ?? 'Membre') ?>
                            </a>
                            <?php if (!empty($item['pinned'])): ?>
                                <span class="badge-pinned"><i class="fa-solid fa-thumbtack"></i> Épinglé</span>
                            <?php endif; ?>
                        </div>
                        <div class="post-time-row">
                            <time class="post-time" datetime="<?= htmlspecialchars($item['created_at']) ?>">
                                <?= htmlspecialchars($item['time_ago'] ?? $item['created_at']) ?>
                            </time>
                            <span class="post-visibility-sep">·</span>
                            <span class="post-visibility-badge">
                                <?php 
                                    $vis = $item['visibility'] ?? 'public';
                                    if ($vis === 'friends') echo '<i class="fa-solid fa-user-group" title="Amis"></i>';
                                    elseif ($vis === 'followers') echo '<i class="fa-solid fa-bell" title="Abonnés"></i>';
                                    elseif ($vis === 'only_me') echo '<i class="fa-solid fa-lock" title="Moi uniquement"></i>';
                                    else echo '<i class="fa-solid fa-globe" title="Public"></i>';
                                ?>
                            </span>
                        </div>
                    </div>

                    <?php 
                        $canDelete = false;
                        if (!empty($currentUser)) {
                            $isAuthor = (int)($currentUser->id ?? 0) === (int)($item['author_id'] ?? 0);
                            $isAdmin = ($currentUser->role ?? '') === 'admin';
                            $canDelete = $isAuthor || $isAdmin;
                        }
                    ?>
                    <div class="card-options dropdown">
                        <button type="button" class="btn-icon btn-post-options" title="Options">
                            <i class="fa-solid fa-ellipsis"></i>
                        </button>
                        <div class="dropdown-menu">
                            <button type="button" class="dropdown-item btn-copy-link" data-url="<?= htmlspecialchars($item['permalink'] ?? ('/social#post-' . (int)$item['id'])) ?>">
                                <i class="fa-solid fa-link"></i> Copier le lien
                            </button>
                            <?php if (!empty($item['author_url'])): ?>
                                <a href="<?= htmlspecialchars($item['author_url']) ?>" class="dropdown-item">
                                    <i class="fa-solid fa-user"></i> Voir le profil
                                </a>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                                <hr class="dropdown-divider" />
                                <button type="button" class="dropdown-item text-danger btn-delete-post" data-post-id="<?= (int)$item['id'] ?>">
                                    <i class="fa-solid fa-trash"></i> Supprimer
                                </button>
                            <?php else: ?>
                                <button type="button" class="dropdown-item btn-report-post" data-post-id="<?= (int)$item['id'] ?>">
                                    <i class="fa-regular fa-flag"></i> Signaler
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card-content">
                    <p class="post-text"><?= formatSocialText($item['content'] ?? '') ?></p>
                    <?php if (!empty($item['media_url'])): ?>
                        <?php 
                            $mUrl = $item['media_url'];
                            $isVideoFile = (bool)preg_match('/\.(mp4|webm|mov|ogg)(\?.*)?$/i', $mUrl);
                            $isYoutube = (bool)preg_match('/(?:youtube\.com\/(?:watch\?v=|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i', $mUrl, $ytMatches);
                        ?>
                        <div class="post-media-wrap">
                            <?php if ($isVideoFile): ?>
                                <video class="post-video-player" controls preload="metadata" playsinline src="<?= htmlspecialchars($mUrl) ?>"></video>
                            <?php elseif ($isYoutube && !empty($ytMatches[1])): ?>
                                <div class="video-embed-responsive">
                                    <iframe src="https://www.youtube-nocookie.com/embed/<?= htmlspecialchars($ytMatches[1]) ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                                </div>
                            <?php else: ?>
                                <img src="<?= htmlspecialchars($mUrl) ?>" alt="Média partagé" class="post-media-img" loading="lazy" />
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card-actions">
                    <div class="reaction-container" data-post-id="<?= (int)$item['id'] ?>">
                        <div class="reactions-popover">
                            <button type="button" class="btn-react" data-reaction="like" title="J'aime">👍</button>
                            <button type="button" class="btn-react" data-reaction="love" title="J'adore">❤️</button>
                            <button type="button" class="btn-react" data-reaction="haha" title="Haha">😂</button>
                            <button type="button" class="btn-react" data-reaction="sad" title="Triste">😢</button>
                            <button type="button" class="btn-react" data-reaction="angry" title="Grr">😡</button>
                        </div>
                        <button class="action-btn btn-like <?= !empty($item['has_liked']) ? 'liked reaction-' . htmlspecialchars($item['user_reaction'] ?? 'like') : '' ?>" data-post-id="<?= (int)$item['id'] ?>" data-current-reaction="<?= htmlspecialchars($item['user_reaction'] ?? '') ?>" title="Réagir">
                            <?php 
                                $r = $item['user_reaction'] ?? '';
                                $iconMap = [
                                    'love' => '<span class="reaction-emoji">❤️</span> <span class="like-label">J\'adore</span>',
                                    'haha' => '<span class="reaction-emoji">😂</span> <span class="like-label">Haha</span>',
                                    'sad' => '<span class="reaction-emoji">😢</span> <span class="like-label">Triste</span>',
                                    'angry' => '<span class="reaction-emoji">😡</span> <span class="like-label">Grr</span>',
                                    'like' => '<span class="reaction-emoji">👍</span> <span class="like-label">J\'aime</span>'
                                ];
                                if (!empty($item['has_liked']) && isset($iconMap[$r])) {
                                    echo $iconMap[$r];
                                } else {
                                    echo '<i class="fa-regular fa-thumbs-up"></i> <span class="like-label">J\'aime</span>';
                                }
                            ?>
                            <span class="count likes-count"><?= (int)($item['likes_count'] ?? 0) ?></span>
                        </button>
                    </div>

                    <button class="action-btn btn-toggle-comments" data-post-id="<?= (int)$item['id'] ?>" title="Commentaires">
                        <i class="fa-regular fa-comment"></i>
                        <span class="count comments-count"><?= (int)($item['comments_count'] ?? 0) ?></span>
                    </button>

                    <div class="share-dropdown-wrap">
                        <button type="button" class="action-btn btn-share-toggle" data-post-id="<?= (int)$item['id'] ?>" title="Partager">
                            <i class="fa-solid fa-share-nodes"></i>
                            <span class="count shares-count"><?= (int)($item['shares_count'] ?? 0) ?></span>
                        </button>
                        <div class="share-menu-dropdown dropdown-menu" id="share-menu-<?= (int)$item['id'] ?>">
                            <button type="button" class="dropdown-item btn-repost-wall" data-post-id="<?= (int)$item['id'] ?>" data-content="<?= htmlspecialchars(mb_substr($item['content'] ?? '', 0, 140)) ?>" data-author="<?= htmlspecialchars($item['author_name'] ?? '') ?>" data-media="<?= htmlspecialchars($item['media_url'] ?? '') ?>">
                                <i class="fa-solid fa-retweet text-emerald"></i>
                                <span>Partager sur mon mur</span>
                            </button>
                            <a href="/social/groups" class="dropdown-item btn-share-group">
                                <i class="fa-solid fa-users text-purple"></i>
                                <span>Partager dans un groupe</span>
                            </a>
                            <button type="button" class="dropdown-item btn-copy-link" data-url="<?= htmlspecialchars($item['permalink'] ?? '') ?>">
                                <i class="fa-solid fa-link text-blue"></i>
                                <span>Copier le lien</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- SECTION COMMENTAIRES DÉROULANTE -->
                <div class="card-comments-section" id="comments-section-<?= (int)$item['id'] ?>" style="display: none;">
                    <div class="comments-list" id="comments-list-<?= (int)$item['id'] ?>">
                        <!-- Chargé en AJAX -->
                    </div>

                    <?php if (!empty($currentUser)): ?>
                        <div class="comment-reply-indicator" id="reply-indicator-<?= (int)$item['id'] ?>" style="display: none;">
                            <span class="reply-target-text"></span>
                            <button type="button" class="btn-cancel-reply" title="Annuler la réponse">&times;</button>
                        </div>
                        <form class="comment-add-form" data-post-id="<?= (int)$item['id'] ?>">
                            <input type="hidden" name="token" value="<?= htmlspecialchars(\Core\Security\Csrf::token()) ?>" />
                            <input type="text" name="comment_content" class="comment-input" placeholder="Écrire un commentaire..." required autocomplete="off" />
                            <button type="submit" class="btn-comment-submit" title="Envoyer le commentaire"><i class="fa-solid fa-paper-plane"></i></button>
                        </form>
                    <?php else: ?>
                        <div class="comment-login-hint">
                            <a href="/login">Connectez-vous</a> pour laisser un commentaire.
                        </div>
                    <?php endif; ?>
                </div>
            </article>

        <?php elseif ($type === 'blog'): ?>
            <!-- CARTE ARTICLE DE BLOG (Agrégation automatique) -->
            <article class="social-card social-blog-card" id="blog-<?= (int)$item['id'] ?>">
                <div class="card-badge-row">
                    <span class="source-badge badge-blog"><i class="fa-solid fa-newspaper"></i> Article de Blog</span>
                    <time class="post-time"><?= htmlspecialchars($item['time_ago'] ?? $item['created_at']) ?></time>
                </div>

                <div class="card-content">
                    <h3 class="blog-item-title">
                        <a href="<?= htmlspecialchars($item['permalink']) ?>"><?= htmlspecialchars($item['title'] ?? '') ?></a>
                    </h3>
                    <p class="post-text"><?= htmlspecialchars($item['content'] ?? '') ?></p>

                    <?php if (!empty($item['media_url'])): ?>
                        <div class="post-media-wrap">
                            <a href="<?= htmlspecialchars($item['permalink']) ?>">
                                <img src="<?= htmlspecialchars($item['media_url']) ?>" alt="<?= htmlspecialchars($item['title'] ?? '') ?>" class="post-media-img" loading="lazy" />
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card-actions">
                    <a href="<?= htmlspecialchars($item['permalink']) ?>" class="btn-read-more">
                        <span>Lire l'article</span> <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </article>

        <?php elseif ($type === 'event'): ?>
            <!-- CARTE ÉVÉNEMENT AGENDA (Agrégation automatique) -->
            <article class="social-card social-event-card" id="event-<?= htmlspecialchars($item['id']) ?>">
                <div class="card-badge-row">
                    <span class="source-badge badge-agenda"><i class="fa-solid fa-calendar-days"></i> Événement de l'Agenda</span>
                    <?php if (!empty($item['meta']['location'])): ?>
                        <span class="event-loc"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($item['meta']['location']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="card-content">
                    <h3 class="event-item-title">
                        <a href="<?= htmlspecialchars($item['permalink']) ?>"><?= htmlspecialchars($item['title'] ?? '') ?></a>
                    </h3>
                    <div class="event-date-pill">
                        <i class="fa-regular fa-clock"></i> <?= htmlspecialchars($item['created_at']) ?>
                    </div>
                    <p class="post-text"><?= htmlspecialchars($item['content'] ?? '') ?></p>

                    <?php if (!empty($item['media_url'])): ?>
                        <div class="post-media-wrap">
                            <img src="<?= htmlspecialchars($item['media_url']) ?>" alt="<?= htmlspecialchars($item['title'] ?? '') ?>" class="post-media-img" loading="lazy" />
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card-actions">
                    <a href="<?= htmlspecialchars($item['permalink']) ?>" class="btn-read-more">
                        <span>Détails de l'événement</span> <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </article>

        <?php elseif ($type === 'meteo'): ?>
            <!-- CARTE MÉTÉO (Agrégation automatique) -->
            <article class="social-card social-meteo-card">
                <div class="card-badge-row">
                    <span class="source-badge badge-meteo"><i class="fa-solid fa-cloud-sun"></i> Météo du Jour</span>
                </div>
                <div class="card-content meteo-grid">
                    <div class="meteo-temp">
                        <span class="temp-deg"><?= (int)($item['meta']['temp'] ?? 15) ?>°C</span>
                        <span class="temp-city"><?= htmlspecialchars($item['meta']['city'] ?? 'Local') ?></span>
                    </div>
                    <div class="meteo-summary">
                        <h4><?= htmlspecialchars($item['title'] ?? '') ?></h4>
                        <p><?= htmlspecialchars($item['content'] ?? '') ?></p>
                    </div>
                </div>
            </article>

        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

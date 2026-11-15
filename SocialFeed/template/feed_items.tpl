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
                    <img src="<?= htmlspecialchars($item['author_avatar'] ?? '/common/img/default-avatar.png') ?>" alt="<?= htmlspecialchars($item['author_name'] ?? '') ?>" class="user-avatar" />
                    <div class="header-meta">
                        <div class="author-row">
                            <span class="author-name"><?= htmlspecialchars($item['author_name'] ?? 'Membre') ?></span>
                            <?php if (!empty($item['pinned'])): ?>
                                <span class="badge-pinned"><i class="fa-solid fa-thumbtack"></i> Épinglé</span>
                            <?php endif; ?>
                        </div>
                        <time class="post-time" datetime="<?= htmlspecialchars($item['created_at']) ?>">
                            <?= htmlspecialchars($item['time_ago'] ?? $item['created_at']) ?>
                        </time>
                    </div>

                    <?php 
                        $canDelete = false;
                        if (!empty($currentUser)) {
                            $isAuthor = (int)($currentUser->id ?? 0) === (int)($item['author_id'] ?? 0);
                            $isAdmin = ($currentUser->role ?? '') === 'admin';
                            $canDelete = $isAuthor || $isAdmin;
                        }
                    ?>
                    <?php if ($canDelete): ?>
                        <div class="card-options dropdown">
                            <button class="btn-icon btn-post-options" title="Options" onclick="this.nextElementSibling.classList.toggle('show')">
                                <i class="fa-solid fa-ellipsis"></i>
                            </button>
                            <div class="dropdown-menu">
                                <button class="dropdown-item text-danger btn-delete-post" data-post-id="<?= (int)$item['id'] ?>">
                                    <i class="fa-solid fa-trash"></i> Supprimer
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card-content">
                    <p class="post-text"><?= nl2br(htmlspecialchars($item['content'] ?? '')) ?></p>
                    <?php if (!empty($item['media_url'])): ?>
                        <div class="post-media-wrap">
                            <img src="<?= htmlspecialchars($item['media_url']) ?>" alt="Média partagé" class="post-media-img" loading="lazy" />
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card-actions">
                    <button class="action-btn btn-like <?= !empty($item['has_liked']) ? 'liked' : '' ?>" data-post-id="<?= (int)$item['id'] ?>" title="J'aime">
                        <i class="<?= !empty($item['has_liked']) ? 'fa-solid' : 'fa-regular' ?> fa-heart heart-icon"></i>
                        <span class="count likes-count"><?= (int)($item['likes_count'] ?? 0) ?></span>
                    </button>

                    <button class="action-btn btn-toggle-comments" data-post-id="<?= (int)$item['id'] ?>" title="Commentaires">
                        <i class="fa-regular fa-comment"></i>
                        <span class="count comments-count"><?= (int)($item['comments_count'] ?? 0) ?></span>
                    </button>

                    <button class="action-btn btn-share" data-post-id="<?= (int)$item['id'] ?>" data-url="<?= htmlspecialchars($item['permalink'] ?? '') ?>" title="Partager">
                        <i class="fa-solid fa-share-nodes"></i>
                    </button>
                </div>

                <!-- SECTION COMMENTAIRES DÉROULANTE -->
                <div class="card-comments-section" id="comments-section-<?= (int)$item['id'] ?>" style="display: none;">
                    <div class="comments-list" id="comments-list-<?= (int)$item['id'] ?>">
                        <!-- Chargé en AJAX -->
                    </div>

                    <?php if (!empty($currentUser)): ?>
                        <form class="comment-add-form" data-post-id="<?= (int)$item['id'] ?>">
                            <input type="text" name="comment_content" class="comment-input" placeholder="Écrire un commentaire..." required autocomplete="off" />
                            <button type="submit" class="btn-comment-submit"><i class="fa-solid fa-paper-plane"></i></button>
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

<div class="social-publish-card">
    <?php if (!empty($currentUser)): ?>
        <?php 
            $userEmail = $currentUser->email ?? '';
            $avatar = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($userEmail))) . '?d=mp&s=100';
            $userName = $currentUser->username ?? 'Membre';
        ?>
        <div class="publish-header-user">
            <img src="<?= htmlspecialchars($avatar) ?>" alt="<?= htmlspecialchars($userName) ?>" class="user-avatar-sm" />
            <div class="user-info">
                <strong><?= htmlspecialchars($userName) ?></strong>
                <span class="publish-badge-audience"><i class="fa-solid fa-globe"></i> Public</span>
            </div>
        </div>

        <form id="social-publish-form" action="/social/publish" method="POST" enctype="multipart/form-data" class="social-publish-form">
            <input type="hidden" name="token" value="<?= htmlspecialchars($csrfToken ?? '') ?>" />

            <div class="publish-body">
                <textarea name="content" id="social-post-content" rows="3" placeholder="Quoi de neuf ? Exprimez-vous..." required></textarea>
            </div>

            <!-- PRÉVISUALISATION MÉDIA (IMAGE OU VIDÉO / REEL) -->
            <div id="media-preview-container" class="media-preview-container" style="display: none;">
                <img id="media-preview-img" src="" alt="Aperçu média" style="display: none;" />
                <video id="media-preview-video" src="" controls playsinline style="display: none;"></video>
                <button type="button" id="btn-remove-media" class="btn-remove-media" title="Supprimer le média">&times;</button>
            </div>

            <!-- TIROIR D'EMOJIS (FACEBOOK / X STYLE) -->
            <div id="emoji-drawer" class="emoji-drawer" style="display: none;">
                <div class="emoji-tabs">
                    <span class="emoji-category-title"><i class="fa-regular fa-face-smile"></i> Emojis populaires</span>
                    <button type="button" class="btn-close-drawer" id="btn-close-emoji">&times;</button>
                </div>
                <div class="emoji-grid">
                    <?php
                    $emojis = [
                        '😀','😃','😄','😁','😆','😅','🤣','😂','🙂','😉','😊','😇','🥰','😍','🤩','😘','😋','😜','🤪','🤔',
                        '🤫','🤭','🤐','😴','🥳','😎','🥺','😭','😱','🔥','✨','💯','❤️','🧡','💛','💚','💙','💜','🖤','💔',
                        '👍','👎','👏','🙌','🤝','✌️','🤞','🤙','👊','🖐️','👋','💪','🙏','🎉','🎊','🎈','🎂','🥂','🚀','💡',
                        '⭐','⚡','🎯','🏆','☕','🍕','🍻','🍔','🍿','🌈','☀️','⭐','🌸','🐶','🐱'
                    ];
                    foreach ($emojis as $em):
                    ?>
                        <button type="button" class="emoji-item" data-emoji="<?= $em ?>"><?= $em ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- TIROIR DE STICKERS / GIFS -->
            <div id="gif-drawer" class="gif-drawer" style="display: none;">
                <div class="emoji-tabs">
                    <span class="emoji-category-title"><i class="fa-solid fa-film"></i> Autocollants & GIFs</span>
                    <button type="button" class="btn-close-drawer" id="btn-close-gif">&times;</button>
                </div>
                <div class="gif-grid">
                    <?php
                    $stickers = [
                        ['label' => 'Bravo', 'url' => 'https://media.giphy.com/media/artj92V8o75VPL7AeQ/giphy.gif'],
                        ['label' => 'Super', 'url' => 'https://media.giphy.com/media/111ebonMs90YLu/giphy.gif'],
                        ['label' => 'Fête', 'url' => 'https://media.giphy.com/media/blSTtZehjAZ8I/giphy.gif'],
                        ['label' => 'Rire', 'url' => 'https://media.giphy.com/media/3oEjHAUOqG3lSS0f1C/giphy.gif'],
                        ['label' => 'Bisous', 'url' => 'https://media.giphy.com/media/26AHONQ79FdWZhAI0/giphy.gif'],
                        ['label' => 'Top', 'url' => 'https://media.giphy.com/media/g9582DNuQppxC/giphy.gif']
                    ];
                    foreach ($stickers as $st):
                    ?>
                        <button type="button" class="gif-item" data-gif-url="<?= htmlspecialchars($st['url']) ?>" title="<?= htmlspecialchars($st['label']) ?>">
                            <img src="<?= htmlspecialchars($st['url']) ?>" alt="<?= htmlspecialchars($st['label']) ?>" loading="lazy" />
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- BARRE D'OUTILS ET PUBLICATION -->
            <div class="publish-footer">
                <div class="publish-tools">
                    <!-- Photo / Vidéo File -->
                    <label class="btn-tool" title="Ajouter une photo ou vidéo">
                        <i class="fa-solid fa-photo-film text-emerald"></i>
                        <span>Photo/Vidéo</span>
                        <input type="file" name="media_file" id="media-file-input" accept="image/*,video/mp4,video/webm" style="display: none;" />
                    </label>

                    <!-- Emojis Picker -->
                    <button type="button" class="btn-tool" id="btn-toggle-emoji" title="Ajouter un emoji">
                        <i class="fa-regular fa-face-smile text-gold"></i>
                        <span>Emoji</span>
                    </button>

                    <!-- GIF / Stickers -->
                    <button type="button" class="btn-tool" id="btn-toggle-gif" title="Autocollants & GIFs">
                        <i class="fa-solid fa-icons text-purple"></i>
                        <span>GIF</span>
                    </button>

                    <!-- Lien Vidéo / Reel URL -->
                    <button type="button" class="btn-tool btn-toggle-url" id="btn-toggle-media-url" title="Lien YouTube, TikTok, Reel ou image externe">
                        <i class="fa-solid fa-link text-blue"></i>
                        <span>Lien Média</span>
                    </button>
                </div>

                <button type="submit" class="btn-publish-submit" id="btn-submit-post">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Publier</span>
                </button>
            </div>

            <div class="publish-url-input-wrap" id="media-url-wrap" style="display: none;">
                <input type="url" name="media_url" id="media-url-input" placeholder="Coller le lien d'une vidéo (YouTube, TikTok, MP4) ou image externe..." />
            </div>
        </form>
    <?php else: ?>
        <div class="social-login-callout">
            <div class="callout-icon">
                <i class="fa-solid fa-comments"></i>
            </div>
            <div class="callout-text">
                <h4>Rejoignez la communauté !</h4>
                <p>Connectez-vous pour publier des messages, partager des vidéos, réagir et échanger en direct.</p>
            </div>
            <div class="callout-actions">
                <a href="/login" class="btn btn-primary btn-sm"><i class="fa-solid fa-right-to-bracket"></i> Connexion</a>
                <a href="/register" class="btn btn-outline btn-sm"><i class="fa-solid fa-user-plus"></i> Inscription</a>
            </div>
        </div>
    <?php endif; ?>
</div>

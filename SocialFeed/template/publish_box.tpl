<div class="social-publish-card is-collapsed" id="social-publish-card">
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
                <div class="audience-selector-wrap">
                    <select class="audience-select" id="publish-audience-select" onchange="var el = document.getElementById('hidden-visibility-input'); if(el) el.value = this.value;">
                        <option value="public" selected>🌐 Public</option>
                        <option value="friends">👥 Amis</option>
                        <option value="followers">🔔 Abonnés</option>
                        <option value="only_me">🔒 Moi uniquement</option>
                    </select>
                </div>
            </div>
        </div>

        <form id="social-publish-form" action="/social/publish" method="POST" enctype="multipart/form-data" class="social-publish-form">
            <input type="hidden" name="token" value="<?= htmlspecialchars($csrfToken ?? '') ?>" />
            <input type="hidden" name="visibility" id="hidden-visibility-input" value="public" />

            <!-- BARRE DE FORMATAGE RAPIDE (GRAS, ITALIQUE, SOULIGNÉ) : Masquée si non replié -->
            <div class="publish-format-bar">
                <button type="button" class="btn-format" data-format="bold" title="Gras (**texte**)"><strong>B</strong></button>
                <button type="button" class="btn-format" data-format="italic" title="Italique (*texte*)"><em>I</em></button>
                <button type="button" class="btn-format" data-format="underline" title="Souligné (<u>texte</u>)"><u>U</u></button>
            </div>

            <div class="publish-body">
                <textarea name="content" id="social-post-content" rows="1" placeholder="Quoi de neuf ? Exprimez-vous..." required></textarea>
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

            <!-- BARRE D'OUTILS ET PUBLICATION : Masquée tant qu'on n'a pas cliqué sur le champ -->
            <div class="publish-footer">
                <div class="publish-tools">
                    <!-- Photo / Vidéo Fichier -->
                    <label class="btn-tool" title="Importer une photo ou vidéo">
                        <i class="fa-solid fa-photo-film text-emerald"></i>
                        <span>Photo/Vidéo</span>
                        <input type="file" name="media_file" id="media-file-input" accept="image/*,video/mp4,video/webm" style="display: none;" />
                    </label>

                    <!-- Filmer en direct (Webcam / Caméra Smartphone) -->
                    <button type="button" class="btn-tool btn-tool-camera" id="btn-camera-record" title="Filmer directement depuis la caméra">
                        <i class="fa-solid fa-video text-rose"></i>
                        <span>Filmer</span>
                    </button>
                    <input type="file" id="mobile-video-capture" accept="video/*" capture="user" style="display: none;" />

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

        <!-- MODALE CAMERA RECORD (FILMER EN DIRECT) -->
        <div id="camera-recorder-modal" class="sf-modal-overlay" style="display: none;">
            <div class="sf-modal-card camera-recorder-card">
                <div class="sf-modal-header">
                    <h4><i class="fa-solid fa-video text-rose"></i> Filmer un Réel / Vidéo</h4>
                    <button type="button" class="btn-close-modal" id="btn-close-camera">&times;</button>
                </div>

                <div class="camera-viewport-wrap">
                    <video id="camera-stream-video" autoplay playsinline muted></video>
                    <video id="camera-recorded-video" controls playsinline style="display: none;"></video>

                    <div id="camera-recording-status" class="recording-badge" style="display: none;">
                        <span class="recording-dot"></span>
                        <span id="camera-record-timer">00:00</span>
                    </div>
                </div>

                <div class="camera-controls-bar">
                    <div id="camera-controls-idle" class="controls-group">
                        <button type="button" class="btn-record-action btn-start-rec" id="btn-start-record">
                            <span class="record-circle"></span>
                            <span>Démarrer l'enregistrement</span>
                        </button>
                    </div>

                    <div id="camera-controls-recording" class="controls-group" style="display: none;">
                        <button type="button" class="btn-record-action btn-stop-rec" id="btn-stop-record">
                            <span class="stop-square"></span>
                            <span>Arrêter l'enregistrement</span>
                        </button>
                    </div>

                    <div id="camera-controls-preview" class="controls-group" style="display: none;">
                        <button type="button" class="btn-camera-opt btn-rec-retry" id="btn-retry-record">
                            <i class="fa-solid fa-rotate-left"></i> Recommencer
                        </button>
                        <button type="button" class="btn-camera-opt btn-rec-use" id="btn-use-recorded-video">
                            <i class="fa-solid fa-check"></i> Valider et utiliser cette vidéo
                        </button>
                    </div>
                </div>
            </div>
        </div>

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

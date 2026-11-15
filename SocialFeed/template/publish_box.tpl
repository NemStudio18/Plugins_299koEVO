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
                <span class="publish-prompt">Quoi de neuf aujourd'hui ?</span>
            </div>
        </div>

        <form id="social-publish-form" action="/social/publish" method="POST" enctype="multipart/form-data" class="social-publish-form">
            <input type="hidden" name="token" value="<?= htmlspecialchars($csrfToken ?? '') ?>" />

            <div class="publish-body">
                <textarea name="content" id="social-post-content" rows="3" placeholder="Partagez une pensée, une question ou une découverte avec la communauté..." required></textarea>
            </div>

            <!-- Prévisualisation image sélectionnée -->
            <div id="media-preview-container" class="media-preview-container" style="display: none;">
                <img id="media-preview-img" src="" alt="Aperçu média" />
                <button type="button" id="btn-remove-media" class="btn-remove-media" title="Supprimer l'image">&times;</button>
            </div>

            <div class="publish-footer">
                <div class="publish-tools">
                    <label class="btn-tool" title="Ajouter une photo">
                        <i class="fa-solid fa-image text-emerald"></i>
                        <span>Photo</span>
                        <input type="file" name="media_file" id="media-file-input" accept="image/*" style="display: none;" />
                    </label>
                    <button type="button" class="btn-tool btn-toggle-url" id="btn-toggle-media-url" title="Lien d'image externe">
                        <i class="fa-solid fa-link text-blue"></i>
                        <span>Lien URL</span>
                    </button>
                </div>

                <button type="submit" class="btn-publish-submit" id="btn-submit-post">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Publier</span>
                </button>
            </div>

            <div class="publish-url-input-wrap" id="media-url-wrap" style="display: none;">
                <input type="url" name="media_url" id="media-url-input" placeholder="Coller le lien direct d'une image (https://...)" />
            </div>
        </form>
    <?php else: ?>
        <div class="social-login-callout">
            <div class="callout-icon">
                <i class="fa-solid fa-comments"></i>
            </div>
            <div class="callout-text">
                <h4>Rejoignez la conversation !</h4>
                <p>Connectez-vous pour publier des messages, réagir et suivre les membres de la communauté.</p>
            </div>
            <div class="callout-actions">
                <a href="/login" class="btn btn-primary btn-sm"><i class="fa-solid fa-right-to-bracket"></i> Connexion</a>
                <a href="/register" class="btn btn-outline btn-sm"><i class="fa-solid fa-user-plus"></i> Inscription</a>
            </div>
        </div>
    <?php endif; ?>
</div>

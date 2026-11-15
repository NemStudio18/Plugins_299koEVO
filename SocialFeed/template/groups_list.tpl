<div class="social-groups-wrapper">
    <!-- EN-TÊTE DE LA SECTION GROUPES & PAGES -->
    <div class="groups-header-bar">
        <div class="header-left">
            <h2><i class="fa-solid fa-users-rectangle text-blue"></i> Groupes & Pages</h2>
            <p class="text-muted">Explorez les communautés thématiques, suivez vos pages préférées ou lancez votre propre espace.</p>
        </div>
        <?php if (!empty($currentUser)): ?>
            <button type="button" class="btn btn-primary btn-create-group" id="btn-open-create-group">
                <i class="fa-solid fa-plus"></i> Créer un espace
            </button>
        <?php endif; ?>
    </div>

    <!-- ONGLETS DE FILTRAGE -->
    <div class="groups-tabs-nav">
        <a href="/social/groups?type=all" class="groups-tab <?= ($currentType === 'all') ? 'active' : '' ?>">
            <i class="fa-solid fa-border-all"></i> Tout explorer
        </a>
        <a href="/social/groups?type=group" class="groups-tab <?= ($currentType === 'group') ? 'active' : '' ?>">
            <i class="fa-solid fa-users"></i> Groupes
        </a>
        <a href="/social/groups?type=page" class="groups-tab <?= ($currentType === 'page') ? 'active' : '' ?>">
            <i class="fa-solid fa-flag"></i> Pages officielles
        </a>
        <?php if (!empty($currentUser)): ?>
            <a href="/social/groups?type=my" class="groups-tab <?= ($currentType === 'my') ? 'active' : '' ?>">
                <i class="fa-solid fa-user-check"></i> Mes adhésions
            </a>
        <?php endif; ?>
    </div>

    <!-- GRILLE DES ESPACES -->
    <?php if (empty($groups)): ?>
        <div class="social-empty-state">
            <i class="fa-solid fa-users-slash empty-icon"></i>
            <h3>Aucun espace trouvé</h3>
            <p>Soyez le premier membre à créer une page ou un groupe pour rassembler la communauté !</p>
            <?php if (!empty($currentUser)): ?>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('btn-open-create-group').click();" style="margin-top: 1rem;">
                    <i class="fa-solid fa-plus"></i> Créer maintenant
                </button>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="groups-grid">
            <?php foreach ($groups as $g): ?>
                <div class="group-card" id="group-card-<?= (int)$g['id'] ?>">
                    <div class="group-card-cover" <?php if (!empty($g['cover_image'])): ?>style="background-image: url('<?= htmlspecialchars($g['cover_image']) ?>');"<?php endif; ?>>
                        <span class="badge-group-type badge-<?= htmlspecialchars($g['type']) ?>">
                            <?= ($g['type'] === 'page') ? '<i class="fa-solid fa-flag"></i> Page' : '<i class="fa-solid fa-users"></i> Groupe' ?>
                        </span>
                        <span class="badge-group-vis badge-<?= htmlspecialchars($g['visibility']) ?>">
                            <?= ($g['visibility'] === 'private') ? '<i class="fa-solid fa-lock"></i> Privé' : '<i class="fa-solid fa-globe"></i> Public' ?>
                        </span>
                    </div>

                    <div class="group-card-body">
                        <div class="group-card-avatar-wrap">
                            <img src="<?= htmlspecialchars($g['avatar']) ?>" alt="<?= htmlspecialchars($g['name']) ?>" class="group-card-avatar" />
                        </div>

                        <h3 class="group-card-title">
                            <a href="<?= htmlspecialchars($g['url']) ?>"><?= htmlspecialchars($g['name']) ?></a>
                        </h3>
                        <span class="group-card-cat"><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($g['category']) ?></span>

                        <?php if (!empty($g['description'])): ?>
                            <p class="group-card-desc"><?= htmlspecialchars(mb_substr($g['description'], 0, 110)) ?><?= (mb_strlen($g['description']) > 110) ? '...' : '' ?></p>
                        <?php endif; ?>

                        <div class="group-card-meta">
                            <span><i class="fa-solid fa-users"></i> <strong><?= (int)$g['members_count'] ?></strong> <?= ($g['type'] === 'page') ? 'abonnés' : 'membres' ?></span>
                            <span><i class="fa-solid fa-feather-pointed"></i> <strong><?= (int)$g['posts_count'] ?></strong> posts</span>
                        </div>

                        <div class="group-card-actions">
                            <a href="<?= htmlspecialchars($g['url']) ?>" class="btn btn-outline-sm">Voir l'espace</a>
                            <?php if (!empty($currentUser)): ?>
                                <?php if (!empty($g['is_owner'])): ?>
                                    <span class="badge-owner"><i class="fa-solid fa-crown text-gold"></i> Fondateur</span>
                                <?php else: ?>
                                    <button type="button" class="btn btn-join-group <?= !empty($g['is_member']) ? 'joined' : '' ?>" data-group-id="<?= (int)$g['id'] ?>">
                                        <span class="text-join"><i class="fa-solid fa-plus"></i> <?= ($g['type'] === 'page') ? 'Suivre' : 'Rejoindre' ?></span>
                                        <span class="text-joined"><i class="fa-solid fa-check"></i> <?= ($g['type'] === 'page') ? 'Abonné' : 'Membre' ?></span>
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- MODALE DE CRÉATION D'UNE PAGE OU D'UN GROUPE -->
    <?php if (!empty($currentUser)): ?>
        <div id="create-group-modal" class="sf-modal-overlay" style="display: none;">
            <div class="sf-modal-card">
                <div class="sf-modal-header">
                    <h4><i class="fa-solid fa-users-gear text-blue"></i> Créer un nouvel espace</h4>
                    <button type="button" class="btn-close-modal" id="btn-close-create-group">&times;</button>
                </div>

                <form action="/social/groups/create" method="POST" enctype="multipart/form-data" class="sf-modal-form">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($csrfToken ?? '') ?>" />

                    <!-- CHOIX DU TYPE (PAGE OU GROUPE) -->
                    <div class="form-group-type-selector">
                        <label class="type-card">
                            <input type="radio" name="type" value="group" checked />
                            <div class="type-card-content">
                                <i class="fa-solid fa-users"></i>
                                <strong>Groupe</strong>
                                <span>Espace d'échange où chaque membre peut publier et discuter.</span>
                            </div>
                        </label>
                        <label class="type-card">
                            <input type="radio" name="type" value="page" />
                            <div class="type-card-content">
                                <i class="fa-solid fa-flag"></i>
                                <strong>Page officielle</strong>
                                <span>Vitrine pour commerce, asso ou marque avec diffusion officielle.</span>
                            </div>
                        </label>
                    </div>

                    <div class="form-group" style="margin-top: 1rem;">
                        <label for="group_name">Nom de l'espace *</label>
                        <input type="text" id="group_name" name="name" class="form-control" placeholder="Ex: Développeurs Déclic, Club Photo..." required maxlength="120" />
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                        <div class="form-group">
                            <label for="group_cat">Catégorie</label>
                            <select id="group_cat" name="category" class="form-control">
                                <option value="Général">Général</option>
                                <option value="Technologie & Web">Technologie & Web</option>
                                <option value="Loisirs & Passions">Loisirs & Passions</option>
                                <option value="Entraide & Partage">Entraide & Partage</option>
                                <option value="Association & Club">Association & Club</option>
                                <option value="Commerce & Entreprise">Commerce & Entreprise</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="group_vis">Visibilité</label>
                            <select id="group_vis" name="visibility" class="form-control">
                                <option value="public">Public (ouvert à tous)</option>
                                <option value="private">Privé (sur adhésion)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 1rem;">
                        <label for="group_desc">Description / Présentation</label>
                        <textarea id="group_desc" name="description" rows="3" class="form-control" placeholder="De quoi parle cet espace ? Qui peut y participer ?" maxlength="400"></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                        <div class="form-group">
                            <label for="group_avatar">Photo de profil / Logo</label>
                            <input type="file" id="group_avatar" name="avatar_file" accept="image/*" class="form-control" />
                        </div>
                        <div class="form-group">
                            <label for="group_cover">Bannière de couverture</label>
                            <input type="file" id="group_cover" name="cover_file" accept="image/*" class="form-control" />
                        </div>
                    </div>

                    <div class="sf-modal-footer" style="margin-top: 1.5rem; text-align: right;">
                        <button type="button" class="btn btn-outline" id="btn-cancel-create-group">Annuler</button>
                        <button type="submit" class="btn btn-primary" style="margin-left: 0.5rem;">
                            <i class="fa-solid fa-check"></i> Créer l'espace
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

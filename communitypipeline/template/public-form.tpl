<div class="com-pipeline-container">
    <div class="com-pipeline-header">
        <div class="com-badge-tag"><i class="fa-solid fa-code-merge"></i> Écosystème Communautaire 299ko</div>
        <p class="com-subtitle">
            Partagez votre plugin ou thème avec la communauté 299ko EVO. Dès votre envoi, notre pipeline asynchrone lance un audit statique approfondi de sécurité et de conformité.
        </p>
    </div>

    <div class="com-grid">
        <!-- Formulaire de soumission -->
        <div class="com-card com-form-card">
            <form action="{{ submitUrl }}" method="post" enctype="multipart/form-data" id="comSubmitForm">
                {{ tokenField | raw }}

                <!-- Sélecteur de type : Plugin ou Thème -->
                <div class="com-form-group">
                    <label class="com-label">Type d'extension <span class="com-required">*</span></label>
                    <div class="com-type-selector">
                        <label class="com-type-option is-active" id="optTypePlugin">
                            <input type="radio" name="type" value="plugin" checked>
                            <i class="fa-solid fa-puzzle-piece"></i>
                            <div>
                                <strong>Plugin</strong>
                                <small>Extension fonctionnelle (PHP, hooks, routes)</small>
                            </div>
                        </label>
                        <label class="com-type-option" id="optTypeTheme">
                            <input type="radio" name="type" value="theme">
                            <i class="fa-solid fa-palette"></i>
                            <div>
                                <strong>Thème</strong>
                                <small>Gabarit visuel (layout.tpl, CSS, functions.php)</small>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="com-form-row">
                    <div class="com-form-group">
                        <label for="com_name" class="com-label">Nom de l'extension <span class="com-required">*</span></label>
                        <input type="text" id="com_name" name="name" class="com-input" placeholder="ex: Mon Super Plugin" required>
                    </div>

                    <div class="com-form-group">
                        <label for="com_slug" class="com-label">Identifiant technique (Slug)</label>
                        <input type="text" id="com_slug" name="slug" class="com-input" placeholder="ex: MonPlugin">
                        <span class="com-help">Nom du dossier racine ou du fichier principal .php</span>
                    </div>
                </div>

                <div class="com-form-row">
                    <div class="com-form-group">
                        <label for="com_author" class="com-label">Auteur ou Organisation</label>
                        <input type="text" id="com_author" name="author" class="com-input" placeholder="ex: NemStudio">
                    </div>

                    <div class="com-form-group">
                        <label for="com_author_email" class="com-label">Email de contact</label>
                        <input type="email" id="com_author_email" name="author_email" class="com-input" placeholder="auteur@domaine.com">
                        <span class="com-help">Reçoit automatiquement le lien de suivi permanent de l'audit.</span>
                    </div>
                </div>

                <div class="com-form-group">
                    <label for="com_github_repo" class="com-label">Dépôt GitHub (optionnel)</label>
                    <div class="com-input-icon">
                        <i class="fa-brands fa-github"></i>
                        <input type="url" id="com_github_repo" name="github_repo" class="com-input" placeholder="https://github.com/votre-compte/votre-depot">
                    </div>
                </div>

                <div class="com-form-group">
                    <label for="com_description" class="com-label">Courte description</label>
                    <textarea id="com_description" name="description" class="com-textarea" rows="3" placeholder="Présentation rapide des fonctionnalités ou de l'univers visuel..."></textarea>
                </div>

                <!-- Upload ZIP -->
                <div class="com-form-group">
                    <label class="com-label">Archive ZIP de l'extension <span class="com-required">*</span></label>
                    <div class="com-dropzone" id="comDropzone">
                        <i class="fa-solid fa-file-zipper com-drop-icon"></i>
                        <div class="com-drop-text">
                            <strong>Glissez votre archive ZIP ici</strong> ou <span class="com-browse-link">parcourez vos fichiers</span>
                        </div>
                        <span class="com-drop-info">Format .zip uniquement — Taille max: 15 Mo</span>
                        <input type="file" name="file" id="com_file" accept=".zip,application/zip" class="com-file-input" required>
                        <div class="com-file-name" id="comFileName"></div>
                    </div>
                </div>

                <div class="com-actions">
                    <button type="submit" class="com-btn com-btn-primary" id="comSubmitBtn">
                        <i class="fa-solid fa-rocket"></i> Envoyer et lancer l'audit
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar : Règles et exigences -->
        <div class="com-sidebar">
            <div class="com-card com-info-card">
                <h3 class="com-card-title"><i class="fa-solid fa-shield-halved"></i> Exigences de l'Inspecteur</h3>
                <p class="com-text-sm">Chaque archive est analysée en bac à sable statique avant validation :</p>
                
                <div class="com-checklist" id="checklistPlugin">
                    <h4 class="com-check-group-title"><i class="fa-solid fa-puzzle-piece"></i> Pré-requis Plugin</h4>
                    <ul>
                        <li>
                            <i class="fa-solid fa-check text-success"></i>
                            <div class="com-check-text"><code>infos.json</code> avec nom, version et description.</div>
                        </li>
                        <li>
                            <i class="fa-solid fa-check text-success"></i>
                            <div class="com-check-text">Fichier PHP principal <code>{NomPlugin}.php</code>.</div>
                        </li>
                        <li>
                            <i class="fa-solid fa-check text-success"></i>
                            <div class="com-check-text">Formulaires protégés contre le CSRF (<code>tokenField</code>).</div>
                        </li>
                        <li>
                            <i class="fa-solid fa-ban text-danger"></i>
                            <div class="com-check-text"><strong>Interdiction stricte :</strong> <code>eval()</code>, <code>exec()</code>, <code>popen()</code>, traversée <code>../</code>.</div>
                        </li>
                    </ul>
                </div>

                <div class="com-checklist" id="checklistTheme" style="display: none;">
                    <h4 class="com-check-group-title"><i class="fa-solid fa-palette"></i> Pré-requis Thème</h4>
                    <ul>
                        <li>
                            <i class="fa-solid fa-check text-success"></i>
                            <div class="com-check-text"><code>infos.json</code> décrivant le thème.</div>
                        </li>
                        <li>
                            <i class="fa-solid fa-check text-success"></i>
                            <div class="com-check-text">Fichier gabarit principal <code>layout.tpl</code> obligatoire.</div>
                        </li>
                        <li>
                            <i class="fa-solid fa-check text-success"></i>
                            <div class="com-check-text">Feuille de style principale <code>styles.css</code>.</div>
                        </li>
                        <li>
                            <i class="fa-solid fa-check text-success"></i>
                            <div class="com-check-text">Fichier <code>functions.php</code> (optionnel) : audité et garanti sécurisé.</div>
                        </li>
                        <li>
                            <i class="fa-solid fa-ban text-danger"></i>
                            <div class="com-check-text"><strong>Interdiction stricte :</strong> aucune fonction d'exécution système autorisée.</div>
                        </li>
                    </ul>
                </div>

                <div class="com-score-tip">
                    <div class="com-tip-icon"><i class="fa-solid fa-bullseye"></i></div>
                    <div class="com-tip-body">
                        <strong>Score d'éligibilité : 80 / 100</strong>
                        <p>Les extensions obtenant 80% ou plus sans problème critique sont immédiatement validées.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Conteneur principal fluide et parfaitement centré */
.com-pipeline-container {
    width: 100%;
    max-width: 1100px;
    margin: 0 auto;
    padding: 0;
    box-sizing: border-box;
    color: var(--evo-text, var(--font-color, inherit));
}

.com-pipeline-header {
    text-align: center;
    margin: 0 auto 2rem auto;
    max-width: 720px;
}

.com-badge-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 16px;
    background: var(--evo-accent-soft, rgba(184, 62, 34, 0.12));
    color: var(--evo-accent, var(--primary-color, #b83e22));
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 700;
    margin-bottom: 12px;
}

.com-title {
    font-size: clamp(1.6rem, 4vw, 2.3rem);
    font-weight: 800;
    margin: 0 0 10px 0;
    color: var(--evo-text, var(--font-color, inherit));
    line-height: 1.25;
}

.com-subtitle {
    font-size: 1rem;
    color: var(--evo-text-muted, var(--font-muted, #777));
    line-height: 1.6;
    margin: 0 auto;
}

/* Grille principale : formulaire à gauche, sidebar d'aide à droite */
.com-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 360px;
    gap: 24px;
    align-items: start;
    width: 100%;
    box-sizing: border-box;
}

/* Cartes conformes au Design System (liseré supérieur Terre Battue d'AmbientGeek) */
.com-card {
    background: var(--evo-card-bg, var(--ag-surface-card, var(--section-bg-color, #fff)));
    border: 1px solid var(--evo-border, var(--ag-border, #d8ccbd));
    border-top: 3px solid var(--evo-accent, var(--ag-accent, var(--primary-color, #b83e22)));
    border-radius: var(--ag-radius, 8px);
    padding: 24px;
    box-shadow: var(--ag-shadow-sm, 0 1px 3px rgba(0,0,0,0.06));
    box-sizing: border-box;
    width: 100%;
}

.com-card-title {
    font-size: 1.15rem;
    font-weight: 700;
    margin: 0 0 12px 0;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--evo-text, var(--font-color, inherit));
}

.com-card-title i {
    color: var(--evo-accent, var(--ag-accent, var(--primary-color, #b83e22)));
}

.com-text-sm {
    font-size: 0.88rem;
    color: var(--evo-text-muted, var(--font-muted, #777));
    margin: 0 0 16px 0;
    line-height: 1.5;
}

.com-form-group {
    margin-bottom: 20px;
}

.com-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.com-label {
    display: block;
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 8px;
    color: var(--evo-text, var(--font-color, inherit));
}

.com-required {
    color: var(--ag-error, #ef4444);
}

.com-help {
    display: block;
    font-size: 0.78rem;
    color: var(--evo-text-muted, var(--font-muted, #777));
    margin-top: 5px;
}

/* Inputs respectant scrupuleusement les tokens de thème */
.com-input, .com-textarea {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid var(--evo-input-border, var(--evo-border, var(--ag-border, #d8ccbd)));
    border-radius: var(--ag-radius-sm, 6px);
    background: var(--evo-input-bg, var(--ag-surface, var(--form-bg-color, #f5f1eb)));
    color: var(--evo-text, var(--font-color, inherit));
    font-size: 0.95rem;
    font-family: inherit;
    transition: border-color 0.2s, box-shadow 0.2s;
    box-sizing: border-box;
}

.com-input:focus, .com-textarea:focus {
    outline: none;
    border-color: var(--evo-accent, var(--ag-accent, #b83e22));
    box-shadow: 0 0 0 3px var(--evo-accent-soft, rgba(184, 62, 34, 0.15));
}

.com-input-icon {
    position: relative;
}

.com-input-icon i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--evo-text-muted, var(--font-muted, #777));
}

.com-input-icon input {
    padding-left: 42px;
}

/* Sélecteur Plugin / Thème */
.com-type-selector {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    width: 100%;
    box-sizing: border-box;
}

.com-type-option {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    border: 2px solid var(--evo-border, var(--ag-border, #d8ccbd));
    border-radius: var(--ag-radius, 8px);
    cursor: pointer;
    transition: all 0.2s;
    background: var(--evo-surface, var(--ag-surface, #ece4da));
    box-sizing: border-box;
}

.com-type-option input {
    display: none;
}

.com-type-option i {
    font-size: 1.4rem;
    color: var(--evo-text-muted, var(--font-muted, #777));
}

.com-type-option div {
    display: flex;
    flex-direction: column;
}

.com-type-option strong {
    font-size: 0.95rem;
    color: var(--evo-text, var(--font-color, inherit));
}

.com-type-option small {
    font-size: 0.75rem;
    color: var(--evo-text-muted, var(--font-muted, #777));
}

.com-type-option.is-active {
    border-color: var(--evo-accent, var(--ag-accent, #b83e22));
    background: var(--evo-accent-soft, rgba(184, 62, 34, 0.12));
}

.com-type-option.is-active i {
    color: var(--evo-accent, var(--ag-accent, #b83e22));
}

/* Dropzone d'upload */
.com-dropzone {
    border: 2px dashed var(--evo-border-strong, var(--ag-border-strong, #baa995));
    border-radius: var(--ag-radius, 8px);
    padding: 26px 16px;
    text-align: center;
    cursor: pointer;
    position: relative;
    background: var(--evo-surface, var(--ag-surface, #ece4da));
    transition: all 0.2s;
    box-sizing: border-box;
    width: 100%;
}

.com-dropzone:hover {
    border-color: var(--evo-accent, var(--ag-accent, #b83e22));
}

.com-drop-icon {
    font-size: 2.2rem;
    color: var(--evo-accent, var(--ag-accent, #b83e22));
    margin-bottom: 8px;
}

.com-drop-text {
    font-size: 0.95rem;
    margin-bottom: 6px;
    color: var(--evo-text, var(--font-color, inherit));
}

.com-browse-link {
    color: var(--evo-accent, var(--ag-accent, #b83e22));
    text-decoration: underline;
    font-weight: 700;
}

.com-drop-info {
    font-size: 0.78rem;
    color: var(--evo-text-muted, var(--font-muted, #777));
    display: block;
}

.com-file-input {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.com-file-name {
    margin-top: 10px;
    font-weight: 700;
    color: var(--ag-success, #10b981);
    font-size: 0.88rem;
}

/* Bouton d'action */
.com-actions {
    margin-top: 24px;
    text-align: right;
}

.com-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 24px;
    border-radius: var(--ag-radius, 8px);
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
    text-decoration: none;
    box-sizing: border-box;
}

.com-btn-primary {
    background: var(--evo-accent, var(--ag-accent, #b83e22));
    color: #ffffff;
    box-shadow: 0 4px 12px var(--evo-accent-soft, rgba(184, 62, 34, 0.2));
}

.com-btn-primary:hover {
    background: var(--evo-accent-hover, var(--ag-accent-hover, #8e2b14));
    transform: translateY(-1px);
}

/* Checklist et règles dans la sidebar */
.com-checklist ul {
    list-style: none;
    padding: 0;
    margin: 0 0 20px 0;
}

.com-checklist li {
    font-size: 0.88rem;
    padding: 10px 0;
    border-bottom: 1px solid var(--evo-border, var(--ag-border, #d8ccbd));
    display: flex;
    align-items: flex-start;
    gap: 10px;
    line-height: 1.5;
}

.com-checklist li i {
    margin-top: 3px;
    flex-shrink: 0;
    font-size: 0.95rem;
}

.com-check-text {
    flex: 1;
    color: var(--evo-text, var(--font-color, inherit));
}

.com-check-text code {
    display: inline-block;
    padding: 2px 6px;
    font-size: 0.82rem;
    border-radius: 4px;
    background: var(--evo-surface-alt, var(--ag-surface-code, rgba(0, 0, 0, 0.06)));
    color: var(--evo-text, var(--font-color, inherit));
    border: 1px solid var(--evo-border, var(--ag-border, transparent));
    font-family: var(--ag-font-mono, monospace);
}

.com-check-group-title {
    font-size: 0.9rem;
    font-weight: 700;
    margin: 16px 0 8px 0;
    color: var(--evo-accent, var(--ag-accent, #b83e22));
    display: flex;
    align-items: center;
    gap: 8px;
}

.text-success { color: var(--ag-success, #10b981); }
.text-danger { color: var(--ag-error, #ef4444); }

.com-score-tip {
    display: flex;
    gap: 12px;
    padding: 14px;
    border-radius: var(--ag-radius, 8px);
    background: var(--evo-accent-soft, rgba(184, 62, 34, 0.12));
    border-left: 4px solid var(--evo-accent, var(--ag-accent, #b83e22));
    margin-top: 10px;
}

.com-tip-icon {
    font-size: 1.3rem;
    color: var(--evo-accent, var(--ag-accent, #b83e22));
    flex-shrink: 0;
}

.com-tip-body strong {
    display: block;
    font-size: 0.9rem;
    color: var(--evo-text, var(--font-color, inherit));
    margin-bottom: 4px;
}

.com-tip-body p {
    margin: 0;
    font-size: 0.82rem;
    color: var(--evo-text-muted, var(--font-muted, #777));
    line-height: 1.4;
}

/* ==========================================================================
   RESPONSIVE MOBILE : Centrage absolu, 1 colonne, aucune marge brisée
   ========================================================================== */
@media (max-width: 860px) {
    .com-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .com-sidebar {
        order: 2;
    }

    .com-form-card {
        order: 1;
    }
}

@media (max-width: 600px) {
    .com-pipeline-container {
        padding: 0;
        margin: 0 auto;
    }

    .com-pipeline-header {
        margin-bottom: 1.5rem;
        padding: 0 8px;
    }

    .com-card {
        padding: 18px 14px;
    }

    .com-form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .com-type-selector {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .com-actions {
        text-align: center;
    }

    .com-btn-primary {
        width: 100%;
        justify-content: center;
        padding: 13px 20px;
    }

    .com-dropzone {
        padding: 20px 12px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const optPlugin = document.getElementById('optTypePlugin');
    const optTheme = document.getElementById('optTypeTheme');
    const listPlugin = document.getElementById('checklistPlugin');
    const listTheme = document.getElementById('checklistTheme');
    const fileInput = document.getElementById('com_file');
    const fileNameDisplay = document.getElementById('comFileName');

    optPlugin.addEventListener('click', function() {
        optPlugin.classList.add('is-active');
        optTheme.classList.remove('is-active');
        listPlugin.style.display = 'block';
        listTheme.style.display = 'none';
    });

    optTheme.addEventListener('click', function() {
        optTheme.classList.add('is-active');
        optPlugin.classList.remove('is-active');
        listTheme.style.display = 'block';
        listPlugin.style.display = 'none';
    });

    fileInput.addEventListener('change', function(e) {
        if (fileInput.files.length > 0) {
            fileNameDisplay.textContent = 'Fichier sélectionné : ' + fileInput.files[0].name + ' (' + (fileInput.files[0].size / 1024 / 1024).toFixed(2) + ' Mo)';
        } else {
            fileNameDisplay.textContent = '';
        }
    });
});
</script>

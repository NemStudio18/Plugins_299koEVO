<div class="com-pipeline-container">
    <div class="com-pipeline-header">
        <div class="com-badge-tag"><i class="fa-solid fa-code-merge"></i> Écosystème Communautaire 299ko</div>
        <h1 class="com-title">Soumettre une Extension</h1>
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
                        <input type="text" id="com_name" name="name" class="com-input" placeholder="ex: Mon Super Thème" required>
                    </div>

                    <div class="com-form-group">
                        <label for="com_slug" class="com-label">Identifiant technique (Slug)</label>
                        <input type="text" id="com_slug" name="slug" class="com-input" placeholder="ex: mon-super-theme (optionnel)">
                        <span class="com-help">Doit correspondre au dossier racine du ZIP ou au slug dans infos.json.</span>
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
                    </div>
                </div>

                <div class="com-form-group">
                    <label for="com_github_repo" class="com-label">Dépôt GitHub (recommandé)</label>
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
:root {
    --com-primary: var(--primary-color, var(--evo-accent, var(--ag-accent, #b83e22)));
    --com-primary-hover: var(--primary-hover-color, var(--evo-accent-hover, var(--ag-accent-hover, #8e2b14)));
    --com-primary-soft: var(--evo-accent-soft, var(--ag-accent-soft, rgba(184, 62, 34, 0.12)));
    --com-text: var(--font-color, var(--evo-text, var(--ag-text, #26211c)));
    --com-text-muted: var(--font-muted, var(--evo-text-muted, var(--ag-text-muted, #665c52)));
    --com-card-bg: var(--bg-card, var(--section-bg-color, var(--evo-card-bg, #faf7f2)));
    --com-border: var(--border-color, var(--evo-border, #d8ccbd));
    --com-input-bg: var(--form-bg-color, var(--evo-surface, #f5f1eb));
}

html[data-theme="dark"], body.ag-theme-dark {
    --com-text: #f1f5f9;
    --com-text-muted: #94a3b8;
    --com-card-bg: #1e293b;
    --com-border: #334155;
    --com-input-bg: #0f172a;
}

.com-pipeline-container { max-width: 1140px; margin: 30px auto; padding: 0 20px; font-family: inherit; }
.com-pipeline-header { text-align: center; margin-bottom: 35px; }
.com-badge-tag { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; background: var(--com-primary-soft); color: var(--com-primary); border-radius: 999px; font-size: 0.85rem; font-weight: 700; margin-bottom: 12px; }
.com-title { font-size: 2.2rem; font-weight: 800; margin-bottom: 12px; color: var(--com-text); }
.com-subtitle { font-size: 1.05rem; color: var(--com-text-muted); max-width: 680px; margin: 0 auto; line-height: 1.6; }

.com-grid { display: grid; grid-template-columns: 1fr 370px; gap: 30px; align-items: start; }
@media (max-width: 900px) { .com-grid { grid-template-columns: 1fr; } }

.com-card { background: var(--com-card-bg); border: 1px solid var(--com-border); border-radius: 12px; padding: 26px; box-shadow: 0 4px 14px rgba(0,0,0,0.04); }

.com-form-group { margin-bottom: 20px; }
.com-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media (max-width: 600px) { .com-form-row { grid-template-columns: 1fr; } }

.com-label { display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px; color: var(--com-text); }
.com-required { color: #ef4444; }
.com-help { display: block; font-size: 0.8rem; color: var(--com-text-muted); margin-top: 5px; }

.com-input, .com-textarea { width: 100%; padding: 11px 14px; border: 1px solid var(--com-border); border-radius: 8px; background: var(--com-input-bg); color: var(--com-text); font-size: 0.95rem; transition: border-color 0.2s, box-shadow 0.2s; box-sizing: border-box; }
.com-input:focus, .com-textarea:focus { outline: none; border-color: var(--com-primary); box-shadow: 0 0 0 3px var(--com-primary-soft); }

.com-input-icon { position: relative; }
.com-input-icon i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--com-text-muted); }
.com-input-icon input { padding-left: 40px; }

.com-type-selector { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.com-type-option { display: flex; align-items: center; gap: 12px; padding: 14px; border: 2px solid var(--com-border); border-radius: 10px; cursor: pointer; transition: all 0.2s; background: var(--com-card-bg); }
.com-type-option input { display: none; }
.com-type-option i { font-size: 1.4rem; color: var(--com-text-muted); }
.com-type-option div { display: flex; flex-direction: column; }
.com-type-option strong { font-size: 0.95rem; color: var(--com-text); }
.com-type-option small { font-size: 0.75rem; color: var(--com-text-muted); }
.com-type-option.is-active { border-color: var(--com-primary); background: var(--com-primary-soft); }
.com-type-option.is-active i { color: var(--com-primary); }

.com-dropzone { border: 2px dashed var(--com-border); border-radius: 10px; padding: 30px; text-align: center; cursor: pointer; position: relative; background: var(--com-input-bg); transition: all 0.2s; }
.com-dropzone:hover { border-color: var(--com-primary); }
.com-drop-icon { font-size: 2.2rem; color: var(--com-primary); margin-bottom: 10px; }
.com-drop-text { font-size: 0.95rem; margin-bottom: 6px; color: var(--com-text); }
.com-browse-link { color: var(--com-primary); text-decoration: underline; font-weight: 600; }
.com-drop-info { font-size: 0.8rem; color: var(--com-text-muted); display: block; }
.com-file-input { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
.com-file-name { margin-top: 10px; font-weight: 600; color: #10b981; font-size: 0.9rem; }

.com-actions { margin-top: 24px; text-align: right; }
.com-btn { display: inline-flex; align-items: center; gap: 10px; padding: 12px 24px; border-radius: 8px; font-weight: 700; font-size: 0.95rem; cursor: pointer; border: none; transition: all 0.2s; text-decoration: none; }
.com-btn-primary { background: var(--com-primary); color: #fff; box-shadow: 0 4px 12px var(--com-primary-soft); }
.com-btn-primary:hover { background: var(--com-primary-hover); transform: translateY(-1px); }

.com-card-title { font-size: 1.15rem; font-weight: 700; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; color: var(--com-text); }
.com-card-title i { color: var(--com-primary); }
.com-text-sm { font-size: 0.85rem; color: var(--com-text-muted); margin-bottom: 16px; line-height: 1.5; }

.com-checklist ul { list-style: none; padding: 0; margin: 0 0 20px 0; }
.com-checklist li { font-size: 0.85rem; padding: 10px 0; border-bottom: 1px solid var(--com-border); display: flex; align-items: flex-start; gap: 10px; line-height: 1.5; }
.com-checklist li i { margin-top: 3px; flex-shrink: 0; font-size: 0.95rem; }
.com-check-text { flex: 1; color: var(--com-text); }
.com-check-text code { display: inline-block; padding: 2px 6px; font-size: 0.8rem; border-radius: 4px; background: rgba(0, 0, 0, 0.06); color: var(--com-text); font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
html[data-theme="dark"] .com-check-text code { background: rgba(255, 255, 255, 0.1); }

.com-check-group-title { font-size: 0.9rem; font-weight: 700; margin: 16px 0 8px 0; color: var(--com-primary); display: flex; align-items: center; gap: 6px; }
.text-success { color: #10b981; }
.text-danger { color: #ef4444; }

.com-score-tip { display: flex; gap: 12px; padding: 14px; border-radius: 10px; background: var(--com-primary-soft); border-left: 4px solid var(--com-primary); }
.com-tip-icon { font-size: 1.3rem; color: var(--com-primary); flex-shrink: 0; }
.com-tip-body strong { display: block; font-size: 0.9rem; color: var(--com-text); margin-bottom: 4px; }
.com-tip-body p { margin: 0; font-size: 0.8rem; color: var(--com-text-muted); line-height: 1.4; }
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

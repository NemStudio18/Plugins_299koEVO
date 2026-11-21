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
                        <li><i class="fa-solid fa-check text-success"></i> <code>infos.json</code> avec nom, version et description.</li>
                        <li><i class="fa-solid fa-check text-success"></i> Fichier PHP principal <code>{NomPlugin}.php</code>.</li>
                        <li><i class="fa-solid fa-check text-success"></i> Formulaires protégés contre le CSRF (<code>tokenField</code>).</li>
                        <li><i class="fa-solid fa-ban text-danger"></i> <strong>Interdiction stricte</strong> : <code>eval()</code>, <code>exec()</code>, <code>popen()</code>, traversée <code>../</code>.</li>
                    </ul>
                </div>

                <div class="com-checklist" id="checklistTheme" style="display: none;">
                    <h4 class="com-check-group-title"><i class="fa-solid fa-palette"></i> Pré-requis Thème</h4>
                    <ul>
                        <li><i class="fa-solid fa-check text-success"></i> <code>infos.json</code> décrivant le thème.</li>
                        <li><i class="fa-solid fa-check text-success"></i> Fichier gabarit principal <code>layout.tpl</code> obligatoire.</li>
                        <li><i class="fa-solid fa-check text-success"></i> Feuille de style principale <code>styles.css</code>.</li>
                        <li><i class="fa-solid fa-check text-success"></i> Fichier <code>functions.php</code> (optionnel) : audité et garanti sécurisé.</li>
                        <li><i class="fa-solid fa-ban text-danger"></i> Aucune fonction d'exécution système autorisée.</li>
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
.com-pipeline-container { max-width: 1140px; margin: 40px auto; padding: 0 20px; font-family: inherit; }
.com-pipeline-header { text-align: center; margin-bottom: 40px; }
.com-badge-tag { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; border-radius: 999px; font-size: 0.85rem; font-weight: 600; margin-bottom: 12px; }
.com-title { font-size: 2.2rem; font-weight: 700; margin-bottom: 12px; color: var(--text-color, #1e293b); }
.com-subtitle { font-size: 1.05rem; color: var(--text-muted, #64748b); max-width: 680px; margin: 0 auto; line-height: 1.6; }

.com-grid { display: grid; grid-template-columns: 1fr 360px; gap: 30px; align-items: start; }
@media (max-width: 900px) { .com-grid { grid-template-columns: 1fr; } }

.com-card { background: var(--bg-card, #ffffff); border: 1px solid var(--border-color, #e2e8f0); border-radius: 14px; padding: 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
html[data-theme="dark"] .com-card { background: #1e293b; border-color: #334155; }

.com-form-group { margin-bottom: 22px; }
.com-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media (max-width: 600px) { .com-form-row { grid-template-columns: 1fr; } }

.com-label { display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px; color: var(--text-color, #1e293b); }
html[data-theme="dark"] .com-label { color: #f1f5f9; }
.com-required { color: #ef4444; }
.com-help { display: block; font-size: 0.8rem; color: var(--text-muted, #64748b); margin-top: 5px; }

.com-input, .com-textarea { width: 100%; padding: 12px 14px; border: 1px solid var(--border-color, #cbd5e1); border-radius: 8px; background: var(--bg-input, #f8fafc); color: var(--text-color, #1e293b); font-size: 0.95rem; transition: border-color 0.2s, box-shadow 0.2s; box-sizing: border-box; }
html[data-theme="dark"] .com-input, html[data-theme="dark"] .com-textarea { background: #0f172a; border-color: #334155; color: #f8fafc; }
.com-input:focus, .com-textarea:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }

.com-input-icon { position: relative; }
.com-input-icon i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted, #94a3b8); }
.com-input-icon input { padding-left: 40px; }

.com-type-selector { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.com-type-option { display: flex; align-items: center; gap: 12px; padding: 14px; border: 2px solid var(--border-color, #e2e8f0); border-radius: 10px; cursor: pointer; transition: all 0.2s; background: var(--bg-card, #fff); }
html[data-theme="dark"] .com-type-option { background: #0f172a; border-color: #334155; }
.com-type-option input { display: none; }
.com-type-option i { font-size: 1.4rem; color: var(--text-muted, #94a3b8); }
.com-type-option div { display: flex; flex-direction: column; }
.com-type-option strong { font-size: 0.95rem; color: var(--text-color, #1e293b); }
html[data-theme="dark"] .com-type-option strong { color: #f8fafc; }
.com-type-option small { font-size: 0.75rem; color: var(--text-muted, #64748b); }
.com-type-option.is-active { border-color: #3b82f6; background: rgba(59, 130, 246, 0.05); }
.com-type-option.is-active i { color: #3b82f6; }

.com-dropzone { border: 2px dashed var(--border-color, #cbd5e1); border-radius: 10px; padding: 30px; text-align: center; cursor: pointer; position: relative; background: var(--bg-input, #f8fafc); transition: all 0.2s; }
html[data-theme="dark"] .com-dropzone { background: #0f172a; border-color: #334155; }
.com-dropzone:hover { border-color: #3b82f6; }
.com-drop-icon { font-size: 2.2rem; color: #3b82f6; margin-bottom: 10px; }
.com-drop-text { font-size: 0.95rem; margin-bottom: 6px; color: var(--text-color, #1e293b); }
html[data-theme="dark"] .com-drop-text { color: #f8fafc; }
.com-browse-link { color: #3b82f6; text-decoration: underline; }
.com-drop-info { font-size: 0.8rem; color: var(--text-muted, #64748b); display: block; }
.com-file-input { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
.com-file-name { margin-top: 10px; font-weight: 600; color: #10b981; font-size: 0.9rem; }

.com-actions { margin-top: 28px; text-align: right; }
.com-btn { display: inline-flex; align-items: center; gap: 10px; padding: 13px 26px; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; border: none; transition: all 0.2s; }
.com-btn-primary { background: #3b82f6; color: #fff; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }
.com-btn-primary:hover { background: #2563eb; transform: translateY(-1px); }

.com-card-title { font-size: 1.15rem; font-weight: 700; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; color: var(--text-color, #1e293b); }
html[data-theme="dark"] .com-card-title { color: #f8fafc; }
.com-text-sm { font-size: 0.85rem; color: var(--text-muted, #64748b); margin-bottom: 16px; line-height: 1.5; }
.com-checklist ul { list-style: none; padding: 0; margin: 0 0 20px 0; }
.com-checklist li { font-size: 0.85rem; padding: 8px 0; border-bottom: 1px solid var(--border-color, #f1f5f9); display: flex; align-items: flex-start; gap: 10px; line-height: 1.4; color: var(--text-color, #334155); }
html[data-theme="dark"] .com-checklist li { border-color: #334155; color: #cbd5e1; }
.com-check-group-title { font-size: 0.9rem; font-weight: 700; margin: 16px 0 8px 0; color: #3b82f6; display: flex; align-items: center; gap: 6px; }
.text-success { color: #10b981; }
.text-danger { color: #ef4444; }

.com-score-tip { display: flex; gap: 12px; padding: 14px; border-radius: 10px; background: rgba(59, 130, 246, 0.08); border-left: 4px solid #3b82f6; }
.com-tip-icon { font-size: 1.3rem; color: #3b82f6; }
.com-tip-body strong { display: block; font-size: 0.9rem; color: var(--text-color, #1e293b); margin-bottom: 4px; }
html[data-theme="dark"] .com-tip-body strong { color: #f8fafc; }
.com-tip-body p { margin: 0; font-size: 0.8rem; color: var(--text-muted, #64748b); line-height: 1.4; }
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

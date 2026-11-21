<div class="com-pipeline-container">
    <div class="com-status-header">
        <a href="{{ formUrl }}" class="com-back-link"><i class="fa-solid fa-arrow-left"></i> Nouvelle soumission</a>
        <h1 class="com-title">Rapport d'Audit #{{ submission.submission_id }}</h1>
        <p class="com-subtitle">
            Analyse statique et contrôle de conformité pour l'extension <strong>{{ submission.name }}</strong> ({{ submission.type }}).
        </p>
        <div class="com-share-box">
            <i class="fa-solid fa-link"></i>
            <span class="com-share-text">Lien permanent de suivi : <code id="trackingLinkUrl">{{ currentUrl }}</code></span>
            <button type="button" class="com-btn-copy" onclick="navigator.clipboard.writeText(document.getElementById('trackingLinkUrl').innerText); this.innerHTML='<i class=\'fa-solid fa-check\'></i> Copié !'; setTimeout(() => this.innerHTML='<i class=\'fa-solid fa-copy\'></i> Copier', 2000);">
                <i class="fa-solid fa-copy"></i> Copier
            </button>
        </div>
    </div>

    <!-- Carte Statut Global & Score -->
    <div class="com-status-card com-status-{{ submission.status }}">
        <div class="com-status-badge">
            {% if submission.status == 'approved' %}
                <i class="fa-solid fa-circle-check text-success"></i>
                <div>
                    <strong>Extension Approuvée</strong>
                    <small>Conforme aux normes de sécurité et de structure 299ko EVO</small>
                </div>
            {% elseif submission.status == 'rejected' %}
                <i class="fa-solid fa-circle-xmark text-danger"></i>
                <div>
                    <strong>Extension Rejetée</strong>
                    <small>{{ submission.rejection_reason }}</small>
                </div>
            {% else %}
                <i class="fa-solid fa-spinner fa-spin text-warning"></i>
                <div>
                    <strong>Audit en cours d'analyse</strong>
                    <small>Le traitement de votre archive est en file d'attente</small>
                </div>
            {% endif %}
        </div>

        <div class="com-score-dial">
            <div class="com-score-number">{{ submission.score }}/100</div>
            <div class="com-score-label">Score de santé</div>
        </div>
    </div>

    <!-- Grille Métadonnées & Statistiques -->
    <div class="com-status-grid">
        <!-- Informations déclarées -->
        <div class="com-card">
            <h3 class="com-card-title"><i class="fa-solid fa-circle-info"></i> Métadonnées de l'extension</h3>
            <table class="com-info-table">
                <tr><th>Identifiant technique (slug)</th><td><code>{{ submission.slug }}</code></td></tr>
                <tr><th>Type</th><td><span class="com-tag">{{ submission.type }}</span></td></tr>
                <tr><th>Version</th><td>{{ submission.version|default('1.0.0') }}</td></tr>
                <tr><th>Auteur</th><td>{{ submission.author|default('Non renseigné') }}</td></tr>
                {% if submission.github_repo %}
                <tr><th>Dépôt GitHub</th><td><a href="{{ submission.github_repo }}" target="_blank" rel="noopener"><i class="fa-brands fa-github"></i> {{ submission.github_repo }}</a></td></tr>
                {% endif %}
                <tr><th>Date de soumission</th><td>{{ submission.created_at }}</td></tr>
            </table>
        </div>

        <!-- Fichiers analysés -->
        <div class="com-card">
            <h3 class="com-card-title"><i class="fa-solid fa-folder-tree"></i> Métriques du code source</h3>
            {% if report.files_stats %}
            <div class="com-metrics-grid">
                <div class="com-metric">
                    <span class="com-metric-val">{{ report.files_stats.total_files }}</span>
                    <span class="com-metric-lbl">Fichiers totaux</span>
                </div>
                <div class="com-metric">
                    <span class="com-metric-val">{{ report.files_stats.php_files }}</span>
                    <span class="com-metric-lbl">Fichiers PHP</span>
                </div>
                <div class="com-metric">
                    <span class="com-metric-val">{{ report.files_stats.tpl_files }}</span>
                    <span class="com-metric-lbl">Templates TPL</span>
                </div>
                <div class="com-metric">
                    <span class="com-metric-val">{{ report.files_stats.css_files }}</span>
                    <span class="com-metric-lbl">Styles CSS</span>
                </div>
                <div class="com-metric">
                    <span class="com-metric-val">{{ report.files_stats.total_lines }}</span>
                    <span class="com-metric-lbl">Lignes de code</span>
                </div>
                <div class="com-metric">
                    <span class="com-metric-val">{{ report.files_stats.size_formatted }}</span>
                    <span class="com-metric-lbl">Poids ZIP</span>
                </div>
            </div>
            {% else %}
            <p class="com-muted">Aucune statistique disponible.</p>
            {% endif %}
        </div>
    </div>

    <!-- Contrôle de sécurité -->
    <div class="com-card" style="margin-top: 24px;">
        <h3 class="com-card-title"><i class="fa-solid fa-shield-halved"></i> Contrôles de sécurité & Conformité</h3>
        
        <div class="com-checks-row">
            <div class="com-check-pill {% if report.security.safe_functions %}is-pass{% else %}is-fail{% endif %}">
                <i class="fa-solid {% if report.security.safe_functions %}fa-check{% else %}fa-xmark{% endif %}"></i>
                <span>Fonctions système (pas d'eval / exec / popen)</span>
            </div>

            <div class="com-check-pill {% if report.security.clean_syntax %}is-pass{% else %}is-fail{% endif %}">
                <i class="fa-solid {% if report.security.clean_syntax %}fa-check{% else %}fa-xmark{% endif %}"></i>
                <span>Syntaxe PHP & Linter AST</span>
            </div>

            <div class="com-check-pill {% if report.security.csrf_compliant %}is-pass{% else %}is-fail{% endif %}">
                <i class="fa-solid {% if report.security.csrf_compliant %}fa-check{% else %}fa-xmark{% endif %}"></i>
                <span>Conformité CSRF (formulaires protégés)</span>
            </div>
        </div>

        <!-- Liste des anomalies -->
        {% if report.issues.critical %}
        <div class="com-issue-box com-issue-critical">
            <h4><i class="fa-solid fa-triangle-exclamation"></i> Anomalies critiques bloquantes ({{ report.issues.critical|length }})</h4>
            <ul>
                {% for issue in report.issues.critical %}
                <li>{{ issue }}</li>
                {% endfor %}
            </ul>
        </div>
        {% endif %}

        {% if report.issues.warning %}
        <div class="com-issue-box com-issue-warning">
            <h4><i class="fa-solid fa-circle-exclamation"></i> Avertissements de conformité ({{ report.issues.warning|length }})</h4>
            <ul>
                {% for warn in report.issues.warning %}
                <li>{{ warn }}</li>
                {% endfor %}
            </ul>
        </div>
        {% endif %}
    </div>

    <!-- Hooks détectés -->
    {% if report.hooks.registered or report.hooks.called %}
    <div class="com-card" style="margin-top: 24px;">
        <h3 class="com-card-title"><i class="fa-solid fa-anchor"></i> Écosystème de Hooks détectés</h3>
        <div class="com-hooks-grid">
            <div>
                <h4>Hooks déclarés / enregistrés :</h4>
                {% if report.hooks.registered %}
                    <div class="com-tags-wrap">
                        {% for h in report.hooks.registered %}
                        <span class="com-hook-tag com-hook-reg">{{ h }}</span>
                        {% endfor %}
                    </div>
                {% else %}
                    <p class="com-muted">Aucun écouteur de hook déclaré.</p>
                {% endif %}
            </div>

            <div>
                <h4>Hooks consommés / appelés :</h4>
                {% if report.hooks.called %}
                    <div class="com-tags-wrap">
                        {% for h in report.hooks.called %}
                        <span class="com-hook-tag com-hook-call">{{ h }}</span>
                        {% endfor %}
                    </div>
                {% else %}
                    <p class="com-muted">Aucun appel de hook détecté.</p>
                {% endif %}
            </div>
        </div>
    </div>
    {% endif %}

    <div class="com-actions" style="margin-top: 30px; text-align: center;">
        <a href="{{ formUrl }}" class="com-btn com-btn-primary">
            <i class="fa-solid fa-upload"></i> Soumettre une autre archive
        </a>
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

.com-status-header { text-align: center; margin-bottom: 30px; }
.com-share-box { display: inline-flex; align-items: center; gap: 10px; margin-top: 14px; padding: 8px 16px; background: var(--com-card-bg); border: 1px solid var(--com-border); border-radius: 999px; font-size: 0.85rem; color: var(--com-text); }
.com-share-box i { color: var(--com-primary); }
.com-share-box code { font-family: monospace; color: var(--com-primary); background: transparent; padding: 0; }
.com-btn-copy { border: none; background: var(--com-primary-soft); color: var(--com-primary); padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; cursor: pointer; transition: all 0.2s; }
.com-btn-copy:hover { background: var(--com-primary); color: #fff; }
.com-back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--com-primary); text-decoration: none; font-weight: 700; margin-bottom: 12px; }
.com-back-link:hover { color: var(--com-primary-hover); }
.com-status-card { display: flex; align-items: center; justify-content: space-between; padding: 24px 30px; border-radius: 12px; margin-bottom: 24px; border: 1.5px solid; }
.com-status-approved { background: rgba(16, 185, 129, 0.08); border-color: #10b981; }
.com-status-rejected { background: rgba(239, 68, 68, 0.08); border-color: #ef4444; }
.com-status-processing, .com-status-queued { background: rgba(245, 158, 11, 0.08); border-color: #f59e0b; }

.com-status-badge { display: flex; align-items: center; gap: 16px; }
.com-status-badge i { font-size: 2.4rem; }
.com-status-badge strong { font-size: 1.3rem; display: block; color: var(--com-text); }
.com-status-badge small { font-size: 0.9rem; color: var(--com-text-muted); }

.com-score-dial { text-align: right; }
.com-score-number { font-size: 2.2rem; font-weight: 800; color: var(--com-primary); }
.com-score-label { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--com-text-muted); }

.com-status-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
@media (max-width: 800px) { .com-status-grid { grid-template-columns: 1fr; } }

.com-info-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
.com-info-table th { text-align: left; padding: 10px 0; color: var(--com-text-muted); font-weight: 500; width: 45%; border-bottom: 1px solid var(--com-border); }
.com-info-table td { padding: 10px 0; border-bottom: 1px solid var(--com-border); color: var(--com-text); }
.com-info-table a { color: var(--com-primary); text-decoration: none; font-weight: 600; }

.com-tag { display: inline-block; padding: 3px 8px; border-radius: 6px; background: var(--com-primary-soft); color: var(--com-primary); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }

.com-metrics-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
.com-metric { background: var(--com-input-bg); border: 1px solid var(--com-border); border-radius: 10px; padding: 14px; text-align: center; }
.com-metric-val { display: block; font-size: 1.4rem; font-weight: 800; color: var(--com-primary); }
.com-metric-lbl { font-size: 0.75rem; color: var(--com-text-muted); text-transform: uppercase; }

.com-checks-row { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
.com-check-pill { display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; border: 1px solid; }
.com-check-pill.is-pass { background: rgba(16, 185, 129, 0.08); border-color: #10b981; color: #10b981; }
.com-check-pill.is-fail { background: rgba(239, 68, 68, 0.08); border-color: #ef4444; color: #ef4444; }

.com-issue-box { border-radius: 10px; padding: 16px 20px; margin-top: 14px; }
.com-issue-critical { background: rgba(239, 68, 68, 0.08); border-left: 4px solid #ef4444; }
.com-issue-critical h4 { margin: 0 0 10px 0; color: #ef4444; font-size: 0.95rem; }
.com-issue-warning { background: rgba(245, 158, 11, 0.08); border-left: 4px solid #f59e0b; }
.com-issue-warning h4 { margin: 0 0 10px 0; color: #f59e0b; font-size: 0.95rem; }
.com-issue-box ul { margin: 0; padding-left: 20px; font-size: 0.85rem; color: var(--com-text); }
.com-issue-box li { margin-bottom: 6px; }

.com-hooks-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; font-size: 0.9rem; }
.com-hooks-grid h4 { font-size: 0.85rem; margin-bottom: 10px; color: var(--com-text-muted); text-transform: uppercase; }
.com-tags-wrap { display: flex; flex-wrap: wrap; gap: 8px; }
.com-hook-tag { display: inline-block; padding: 4px 10px; border-radius: 6px; font-family: monospace; font-size: 0.8rem; font-weight: 600; }
.com-hook-reg { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.com-hook-call { background: var(--com-primary-soft); color: var(--com-primary); }
.com-muted { color: var(--com-text-muted); font-style: italic; font-size: 0.85rem; }
</style>

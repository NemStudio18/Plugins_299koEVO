<div class="com-pipeline-container">
    <div class="com-status-header">
        <a href="{{ formUrl }}" class="com-back-link"><i class="fa-solid fa-arrow-left"></i> Nouvelle soumission</a>
        <h1 class="com-title">Rapport d'Audit #{{ submission.submission_id }}</h1>
        <p class="com-subtitle">
            Analyse statique et contrôle de conformité pour l'extension <strong>{{ submission.name }}</strong> ({{ submission.type }}).
        </p>
        <div class="com-share-box">
            <i class="fa-solid fa-link"></i>
            <span class="com-share-text">Lien de suivi permanent : <code id="trackingLinkUrl">{{ currentUrl }}</code></span>
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
            <div class="com-score-label">Score de conformité</div>
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
                    <tr><th>Dépôt GitHub</th><td><a href="{{ submission.github_repo }}" target="_blank" rel="noopener"><i class="fa-brands fa-github"></i> Consulter le code</a></td></tr>
                {% endif %}
                <tr><th>Date de soumission</th><td>{{ submission.created_at }}</td></tr>
            </table>
        </div>

        <!-- Métriques du code analysé -->
        <div class="com-card">
            <h3 class="com-card-title"><i class="fa-solid fa-chart-simple"></i> Métriques de l'archive</h3>
            {% if report.files_stats %}
                <div class="com-metrics-grid">
                    <div class="com-metric">
                        <span class="com-metric-val">{{ report.files_stats.total_files|default(0) }}</span>
                        <span class="com-metric-lbl">Fichiers totaux</span>
                    </div>
                    <div class="com-metric">
                        <span class="com-metric-val">{{ report.files_stats.php_files|default(0) }}</span>
                        <span class="com-metric-lbl">Fichiers PHP</span>
                    </div>
                    <div class="com-metric">
                        <span class="com-metric-val">{{ report.files_stats.total_lines|default(0) }}</span>
                        <span class="com-metric-lbl">Lignes de code</span>
                    </div>
                    <div class="com-metric">
                        <span class="com-metric-val">{{ report.files_stats.tpl_files|default(0) }}</span>
                        <span class="com-metric-lbl">Templates .tpl</span>
                    </div>
                    <div class="com-metric">
                        <span class="com-metric-val">{{ report.files_stats.css_files|default(0) }}</span>
                        <span class="com-metric-lbl">Feuilles CSS</span>
                    </div>
                    <div class="com-metric">
                        <span class="com-metric-val">{{ report.files_stats.size_formatted|default('0 B') }}</span>
                        <span class="com-metric-lbl">Poids archive</span>
                    </div>
                </div>
            {% else %}
                <p class="com-muted">Aucune statistique disponible pour le moment.</p>
            {% endif %}
        </div>
    </div>

    <!-- Rapport de sécurité & conformité -->
    <div class="com-card com-audit-details">
        <h3 class="com-card-title"><i class="fa-solid fa-microchip"></i> Détail de l'analyse statique</h3>

        <!-- Piliers de sécurité -->
        <div class="com-checks-row">
            <div class="com-check-pill {% if report.security.safe_functions %}com-check-pass{% else %}com-check-fail{% endif %}">
                <i class="fa-solid {% if report.security.safe_functions %}fa-check{% else %}fa-xmark{% endif %}"></i>
                Fonctions saines (pas de eval/exec)
            </div>
            <div class="com-check-pill {% if report.security.csrf_compliant %}com-check-pass{% else %}com-check-fail{% endif %}">
                <i class="fa-solid {% if report.security.csrf_compliant %}fa-check{% else %}fa-xmark{% endif %}"></i>
                Protection CSRF formulaires
            </div>
            <div class="com-check-pill {% if report.security.clean_syntax %}com-check-pass{% else %}com-check-fail{% endif %}">
                <i class="fa-solid {% if report.security.clean_syntax %}fa-check{% else %}fa-xmark{% endif %}"></i>
                Syntaxe PHP valide
            </div>
        </div>

        <!-- Problèmes critiques relevés -->
        {% if report.issues.critical and report.issues.critical|length > 0 %}
            <div class="com-issue-box com-issue-critical">
                <h4><i class="fa-solid fa-triangle-exclamation"></i> Problèmes bloquants critiques :</h4>
                <ul>
                    {% for issue in report.issues.critical %}
                        <li>{{ issue }}</li>
                    {% endfor %}
                </ul>
            </div>
        {% endif %}

        <!-- Avertissements relevés -->
        {% if report.issues.warning and report.issues.warning|length > 0 %}
            <div class="com-issue-box com-issue-warning">
                <h4><i class="fa-solid fa-circle-exclamation"></i> Points d'attention (non bloquants) :</h4>
                <ul>
                    {% for warn in report.issues.warning %}
                        <li>{{ warn }}</li>
                    {% endfor %}
                </ul>
            </div>
        {% endif %}

        {% if not report.issues.critical and not report.issues.warning %}
            <div class="com-issue-box com-issue-clean">
                <i class="fa-solid fa-circle-check text-success"></i> Aucun avertissement ni anomalie de sécurité détecté. Félicitations pour la qualité du code !
            </div>
        {% endif %}

        <!-- Hooks détectés -->
        {% if report.hooks %}
            <div class="com-hooks-grid">
                <div>
                    <h4>Hooks enregistrés par l'extension :</h4>
                    {% if report.hooks.registered and report.hooks.registered|length > 0 %}
                        <div class="com-tags-wrap">
                            {% for hook in report.hooks.registered %}
                                <span class="com-hook-tag com-hook-reg">{{ hook }}</span>
                            {% endfor %}
                        </div>
                    {% else %}
                        <span class="com-muted">Aucun hook enregistré</span>
                    {% endif %}
                </div>
                <div>
                    <h4>Hooks émis (callHook) :</h4>
                    {% if report.hooks.called and report.hooks.called|length > 0 %}
                        <div class="com-tags-wrap">
                            {% for hook in report.hooks.called %}
                                <span class="com-hook-tag com-hook-call">{{ hook }}</span>
                            {% endfor %}
                        </div>
                    {% else %}
                        <span class="com-muted">Aucun hook émis</span>
                    {% endif %}
                </div>
            </div>
        {% endif %}
    </div>
</div>

<style>
.com-pipeline-container {
    width: 100%;
    max-width: 1100px;
    margin: 0 auto;
    padding: 0;
    box-sizing: border-box;
    color: var(--evo-text, var(--font-color, inherit));
}

.com-status-header {
    text-align: center;
    margin: 0 auto 24px auto;
    max-width: 720px;
}

.com-back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--evo-accent, var(--ag-accent, #b83e22));
    text-decoration: none;
    font-weight: 700;
    margin-bottom: 12px;
    font-size: 0.9rem;
}

.com-back-link:hover {
    color: var(--evo-accent-hover, var(--ag-accent-hover, #8e2b14));
}

.com-share-box {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-top: 14px;
    padding: 8px 18px;
    background: var(--evo-card-bg, var(--ag-surface-card, #faf7f2));
    border: 1px solid var(--evo-border, var(--ag-border, #d8ccbd));
    border-radius: 999px;
    font-size: 0.85rem;
    color: var(--evo-text, inherit);
    box-shadow: var(--ag-shadow-sm, 0 1px 3px rgba(0,0,0,0.05));
    flex-wrap: wrap;
    justify-content: center;
}

.com-share-box i {
    color: var(--evo-accent, var(--ag-accent, #b83e22));
}

.com-share-box code {
    font-family: var(--ag-font-mono, monospace);
    color: var(--evo-accent, var(--ag-accent, #b83e22));
    background: transparent;
    padding: 0;
    font-size: 0.85rem;
}

.com-btn-copy {
    border: none;
    background: var(--evo-accent-soft, rgba(184, 62, 34, 0.12));
    color: var(--evo-accent, var(--ag-accent, #b83e22));
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
}

.com-btn-copy:hover {
    background: var(--evo-accent, var(--ag-accent, #b83e22));
    color: #fff;
}

/* Carte de statut principale */
.com-status-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 22px 28px;
    border-radius: var(--ag-radius, 8px);
    margin-bottom: 24px;
    border: 1.5px solid;
    box-sizing: border-box;
    width: 100%;
}

.com-status-approved {
    background: rgba(16, 185, 129, 0.08);
    border-color: var(--ag-success, #10b981);
}

.com-status-rejected {
    background: rgba(239, 68, 68, 0.08);
    border-color: var(--ag-error, #ef4444);
}

.com-status-processing, .com-status-queued {
    background: rgba(245, 158, 11, 0.08);
    border-color: var(--ag-warning, #f59e0b);
}

.com-status-badge {
    display: flex;
    align-items: center;
    gap: 16px;
}

.com-status-badge i {
    font-size: 2.2rem;
}

.com-status-badge strong {
    font-size: 1.25rem;
    display: block;
    color: var(--evo-text, var(--font-color, inherit));
}

.com-status-badge small {
    font-size: 0.88rem;
    color: var(--evo-text-muted, var(--font-muted, #777));
}

.com-score-dial {
    text-align: right;
}

.com-score-number {
    font-size: 2.2rem;
    font-weight: 800;
    color: var(--evo-accent, var(--ag-accent, #b83e22));
    line-height: 1;
}

.com-score-label {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--evo-text-muted, var(--font-muted, #777));
    margin-top: 4px;
}

/* Cartes conformes aux tokens */
.com-card {
    background: var(--evo-card-bg, var(--ag-surface-card, var(--section-bg-color, #fff)));
    border: 1px solid var(--evo-border, var(--ag-border, #d8ccbd));
    border-top: 3px solid var(--evo-accent, var(--ag-accent, #b83e22));
    border-radius: var(--ag-radius, 8px);
    padding: 24px;
    box-shadow: var(--ag-shadow-sm, 0 1px 3px rgba(0,0,0,0.06));
    box-sizing: border-box;
    width: 100%;
}

.com-status-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 24px;
    width: 100%;
    box-sizing: border-box;
}

.com-info-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.com-info-table th {
    text-align: left;
    padding: 10px 0;
    color: var(--evo-text-muted, var(--font-muted, #777));
    font-weight: 500;
    width: 45%;
    border-bottom: 1px solid var(--evo-border, var(--ag-border, #d8ccbd));
}

.com-info-table td {
    padding: 10px 0;
    border-bottom: 1px solid var(--evo-border, var(--ag-border, #d8ccbd));
    color: var(--evo-text, var(--font-color, inherit));
}

.com-info-table a {
    color: var(--evo-accent, var(--ag-accent, #b83e22));
    text-decoration: none;
    font-weight: 600;
}

.com-tag {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 6px;
    background: var(--evo-accent-soft, rgba(184, 62, 34, 0.12));
    color: var(--evo-accent, var(--ag-accent, #b83e22));
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}

.com-metrics-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.com-metric {
    background: var(--evo-surface, var(--ag-surface, #ece4da));
    border: 1px solid var(--evo-border, var(--ag-border, #d8ccbd));
    border-radius: var(--ag-radius-sm, 6px);
    padding: 12px 8px;
    text-align: center;
}

.com-metric-val {
    display: block;
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--evo-accent, var(--ag-accent, #b83e22));
}

.com-metric-lbl {
    font-size: 0.72rem;
    color: var(--evo-text-muted, var(--font-muted, #777));
    text-transform: uppercase;
    font-weight: 600;
}

.com-checks-row {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}

.com-check-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    border: 1px solid;
}

.com-check-pass {
    background: rgba(16, 185, 129, 0.08);
    border-color: var(--ag-success, #10b981);
    color: var(--ag-success, #10b981);
}

.com-check-fail {
    background: rgba(239, 68, 68, 0.08);
    border-color: var(--ag-error, #ef4444);
    color: var(--ag-error, #ef4444);
}

.com-issue-box {
    padding: 16px;
    border-radius: var(--ag-radius, 8px);
    margin-bottom: 18px;
}

.com-issue-critical {
    background: rgba(239, 68, 68, 0.08);
    border-left: 4px solid var(--ag-error, #ef4444);
}

.com-issue-critical h4 {
    margin: 0 0 10px 0;
    color: var(--ag-error, #ef4444);
    font-size: 0.95rem;
}

.com-issue-warning {
    background: rgba(245, 158, 11, 0.08);
    border-left: 4px solid var(--ag-warning, #f59e0b);
}

.com-issue-warning h4 {
    margin: 0 0 10px 0;
    color: var(--ag-warning, #f59e0b);
    font-size: 0.95rem;
}

.com-issue-clean {
    background: rgba(16, 185, 129, 0.08);
    border-left: 4px solid var(--ag-success, #10b981);
    color: var(--evo-text, inherit);
    font-size: 0.9rem;
}

.com-issue-box ul {
    margin: 0;
    padding-left: 20px;
    font-size: 0.88rem;
    color: var(--evo-text, inherit);
}

.com-issue-box li {
    margin-bottom: 6px;
}

.com-hooks-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    font-size: 0.9rem;
}

.com-hooks-grid h4 {
    font-size: 0.85rem;
    margin: 0 0 10px 0;
    color: var(--evo-text-muted, var(--font-muted, #777));
    text-transform: uppercase;
}

.com-tags-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.com-hook-tag {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 6px;
    font-family: var(--ag-font-mono, monospace);
    font-size: 0.8rem;
    font-weight: 600;
}

.com-hook-reg {
    background: rgba(16, 185, 129, 0.12);
    color: var(--ag-success, #10b981);
}

.com-hook-call {
    background: var(--evo-accent-soft, rgba(184, 62, 34, 0.12));
    color: var(--evo-accent, var(--ag-accent, #b83e22));
}

.com-muted {
    color: var(--evo-text-muted, var(--font-muted, #777));
    font-style: italic;
    font-size: 0.85rem;
}

/* ==========================================================================
   RESPONSIVE MOBILE
   ========================================================================== */
@media (max-width: 800px) {
    .com-status-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .com-status-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
        padding: 18px 16px;
    }

    .com-score-dial {
        text-align: left;
    }

    .com-hooks-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
}

@media (max-width: 600px) {
    .com-card {
        padding: 18px 14px;
    }

    .com-metrics-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

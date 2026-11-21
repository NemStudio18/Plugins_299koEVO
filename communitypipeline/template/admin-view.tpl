<div class="adm-card-panel">
    <div class="adm-card-header">
        <div class="adm-card-header-title">
            <i class="fa-solid fa-file-shield"></i>
            <h3>Détail de la soumission #{{ submission.submission_id }}</h3>
        </div>
        <div class="adm-card-header-actions">
            <a href="{{ backUrl }}" class="adm-btn adm-btn-sm" style="background:var(--bg-input, #f1f5f9); color:var(--text-color, #1e293b); text-decoration:none; padding:6px 14px; border-radius:6px; font-weight:600;">
                <i class="fa-solid fa-arrow-left"></i> Retour à la liste
            </a>
            <a href="/contribuer/suivi/{{ submission.submission_id }}" target="_blank" class="adm-btn adm-btn-sm" style="background:#3b82f6; color:#fff; text-decoration:none; padding:6px 14px; border-radius:6px; font-weight:600;">
                <i class="fa-solid fa-external-link"></i> Vue publique
            </a>
        </div>
    </div>

    <div class="adm-card-body" style="padding: 24px;">
        <div style="display: flex; gap: 24px; margin-bottom: 24px; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-color, #e2e8f0); padding-bottom: 20px;">
            <div>
                <h2 style="margin: 0 0 6px 0; color: var(--text-color, #1e293b);">{{ submission.name }} <span style="font-size: 0.9rem; font-weight: normal; color: var(--text-muted, #64748b);">({{ submission.type }})</span></h2>
                <div style="font-size: 0.85rem; color: var(--text-muted, #64748b);">
                    Slug : <code>{{ submission.slug }}</code> • Version : <code>{{ submission.version|default('1.0.0') }}</code> • Auteur : {{ submission.author|default('—') }} ({{ submission.author_email|default('') }})
                </div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 2.2rem; font-weight: 800; color: {% if submission.score >= 80 %}#10b981{% elseif submission.score >= 50 %}#f59e0b{% else %}#ef4444{% endif %};">
                    {{ submission.score }}/100
                </div>
                <span class="adm-badge {% if submission.status == 'approved' %}adm-badge-green{% elseif submission.status == 'rejected' %}adm-badge-red{% else %}adm-badge-gold{% endif %}">
                    Statut : {{ submission.status }}
                </span>
            </div>
        </div>

        {% if submission.rejection_reason %}
        <div style="padding: 14px 18px; border-radius: 8px; background: rgba(239,68,68,0.1); border-left: 4px solid #ef4444; margin-bottom: 20px; color: #ef4444;">
            <strong>Motif du rejet :</strong> {{ submission.rejection_reason }}
        </div>
        {% endif %}

        {% if report %}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <h4 style="margin: 0 0 12px 0;">Contrôles de sécurité AST</h4>
                <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.9rem;">
                    <li style="padding: 8px 0; border-bottom: 1px solid var(--border-color, #f1f5f9);">
                        {% if report.security.safe_functions %}
                            <i class="fa-solid fa-check text-success"></i> Fonctions système sécurisées (pas de eval/exec/popen)
                        {% else %}
                            <i class="fa-solid fa-xmark text-danger"></i> Fonctions proscrites détectées
                        {% endif %}
                    </li>
                    <li style="padding: 8px 0; border-bottom: 1px solid var(--border-color, #f1f5f9);">
                        {% if report.security.clean_syntax %}
                            <i class="fa-solid fa-check text-success"></i> Syntaxe PHP valide (Tokenizer sans erreur)
                        {% else %}
                            <i class="fa-solid fa-xmark text-danger"></i> Erreur de syntaxe PHP détectée
                        {% endif %}
                    </li>
                    <li style="padding: 8px 0; border-bottom: 1px solid var(--border-color, #f1f5f9);">
                        {% if report.security.csrf_compliant %}
                            <i class="fa-solid fa-check text-success"></i> Protection CSRF conforme sur les formulaires
                        {% else %}
                            <i class="fa-solid fa-triangle-exclamation text-warning"></i> Formulaire POST sans tokenField visible
                        {% endif %}
                    </li>
                </ul>
            </div>

            <div>
                <h4 style="margin: 0 0 12px 0;">Statistiques des fichiers</h4>
                <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.9rem;">
                    <li style="padding: 6px 0;">Fichiers totaux : <strong>{{ report.files_stats.total_files }}</strong></li>
                    <li style="padding: 6px 0;">Fichiers PHP : <strong>{{ report.files_stats.php_files }}</strong></li>
                    <li style="padding: 6px 0;">Templates TPL : <strong>{{ report.files_stats.tpl_files }}</strong></li>
                    <li style="padding: 6px 0;">Feuilles CSS : <strong>{{ report.files_stats.css_files }}</strong></li>
                    <li style="padding: 6px 0;">Lignes de code : <strong>{{ report.files_stats.total_lines }}</strong></li>
                    <li style="padding: 6px 0;">Poids archive : <strong>{{ report.files_stats.size_formatted }}</strong></li>
                </ul>
            </div>
        </div>

        {% if report.issues.critical %}
        <div style="margin-top: 20px; padding: 14px 18px; border-radius: 8px; background: rgba(239,68,68,0.08); border-left: 4px solid #ef4444;">
            <h4 style="color: #ef4444; margin: 0 0 8px 0;"><i class="fa-solid fa-ban"></i> Anomalies critiques :</h4>
            <ul style="margin: 0; padding-left: 20px; font-size: 0.85rem;">
                {% for iss in report.issues.critical %}
                <li>{{ iss }}</li>
                {% endfor %}
            </ul>
        </div>
        {% endif %}

        {% if report.issues.warning %}
        <div style="margin-top: 14px; padding: 14px 18px; border-radius: 8px; background: rgba(245,158,11,0.08); border-left: 4px solid #f59e0b;">
            <h4 style="color: #f59e0b; margin: 0 0 8px 0;"><i class="fa-solid fa-circle-exclamation"></i> Avertissements :</h4>
            <ul style="margin: 0; padding-left: 20px; font-size: 0.85rem;">
                {% for warn in report.issues.warning %}
                <li>{{ warn }}</li>
                {% endfor %}
            </ul>
        </div>
        {% endif %}
        {% endif %}
    </div>
</div>

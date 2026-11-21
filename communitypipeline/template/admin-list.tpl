<div class="adm-card-panel">
    <div class="adm-card-header">
        <div class="adm-card-header-title">
            <i class="fa-solid fa-code-merge"></i>
            <h3>Pipeline Communautaire — Soumissions d'extensions ({{ total }})</h3>
        </div>
        <div class="adm-card-header-actions">
            <a href="/contribuer" target="_blank" class="adm-btn adm-btn-sm" style="background:#3b82f6; color:#fff; text-decoration:none; padding:6px 14px; border-radius:6px; font-weight:600;">
                <i class="fa-solid fa-plus"></i> Formulaire public
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div style="padding: 14px 20px; border-bottom: 1px solid var(--border-color, #e2e8f0); display: flex; gap: 10px; align-items: center; background: var(--bg-card, #fff);">
        <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted, #64748b);">Filtrer par statut :</span>
        <a href="/admin/communitypipeline" class="adm-btn adm-btn-sm" style="{% if not statusFilter %}background:#3b82f6; color:#fff;{% else %}background:var(--bg-input, #f1f5f9); color:var(--text-color, #1e293b);{% endif %} padding:4px 10px; border-radius:6px; text-decoration:none; font-size:0.8rem;">Tous</a>
        <a href="/admin/communitypipeline?status=approved" class="adm-btn adm-btn-sm" style="{% if statusFilter == 'approved' %}background:#10b981; color:#fff;{% else %}background:var(--bg-input, #f1f5f9); color:var(--text-color, #1e293b);{% endif %} padding:4px 10px; border-radius:6px; text-decoration:none; font-size:0.8rem;">Approuvés</a>
        <a href="/admin/communitypipeline?status=rejected" class="adm-btn adm-btn-sm" style="{% if statusFilter == 'rejected' %}background:#ef4444; color:#fff;{% else %}background:var(--bg-input, #f1f5f9); color:var(--text-color, #1e293b);{% endif %} padding:4px 10px; border-radius:6px; text-decoration:none; font-size:0.8rem;">Rejetés</a>
        <a href="/admin/communitypipeline?status=queued" class="adm-btn adm-btn-sm" style="{% if statusFilter == 'queued' %}background:#f59e0b; color:#fff;{% else %}background:var(--bg-input, #f1f5f9); color:var(--text-color, #1e293b);{% endif %} padding:4px 10px; border-radius:6px; text-decoration:none; font-size:0.8rem;">En attente</a>
    </div>

    <div class="adm-card-body" style="padding: 0;">
        {% if submissions %}
        <table class="adm-table-simple">
            <thead>
                <tr>
                    <th>Extension</th>
                    <th>Type</th>
                    <th>Auteur / Email</th>
                    <th>Score</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                {% for sub in submissions %}
                <tr>
                    <td>
                        <strong style="color: var(--text-color, #1e293b); display: block;">{{ sub.name }}</strong>
                        <code style="font-size: 0.8rem; color: var(--text-muted, #64748b);">{{ sub.slug }} v{{ sub.version|default('1.0.0') }}</code>
                    </td>
                    <td>
                        <span class="adm-badge" style="text-transform: uppercase; font-size: 0.75rem;">{{ sub.type }}</span>
                    </td>
                    <td>
                        <div>{{ sub.author|default('—') }}</div>
                        <small style="color: var(--text-muted, #64748b);">{{ sub.author_email|default('') }}</small>
                    </td>
                    <td>
                        <strong style="font-size: 1.1rem; color: {% if sub.score >= 80 %}#10b981{% elseif sub.score >= 50 %}#f59e0b{% else %}#ef4444{% endif %};">
                            {{ sub.score }}/100
                        </strong>
                    </td>
                    <td>
                        {% if sub.status == 'approved' %}
                            <span class="adm-badge adm-badge-green"><i class="fa-solid fa-check"></i> Approuvé</span>
                        {% elseif sub.status == 'rejected' %}
                            <span class="adm-badge adm-badge-red" title="{{ sub.rejection_reason }}"><i class="fa-solid fa-xmark"></i> Rejeté</span>
                        {% else %}
                            <span class="adm-badge adm-badge-gold"><i class="fa-solid fa-clock"></i> {{ sub.status }}</span>
                        {% endif %}
                    </td>
                    <td>
                        <span style="font-size: 0.85rem; color: var(--text-muted, #64748b);">{{ sub.created_at }}</span>
                    </td>
                    <td style="text-align: right;">
                        <a href="/admin/communitypipeline/view/{{ sub.submission_id }}" class="adm-table-btn" title="Voir le rapport d'audit">
                            <i class="fa-solid fa-eye"></i> Examiner
                        </a>
                        <a href="/contribuer/suivi/{{ sub.submission_id }}" target="_blank" class="adm-table-btn" title="Vue publique">
                            <i class="fa-solid fa-external-link"></i>
                        </a>
                    </td>
                </tr>
                {% endfor %}
            </tbody>
        </table>
        {% else %}
        <div style="padding: 40px; text-align: center; color: var(--text-muted, #64748b);">
            <i class="fa-solid fa-inbox" style="font-size: 2.5rem; margin-bottom: 12px; opacity: 0.5;"></i>
            <p>Aucune soumission communautaire trouvée.</p>
        </div>
        {% endif %}
    </div>
</div>

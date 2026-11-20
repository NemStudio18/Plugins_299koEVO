{# Template détail notification #}

<div class="notifications-container notif-detail-container">
    <div class="notifications-header notif-detail-header" style="display: flex; flex-direction: column; align-items: flex-start; gap: 12px;">
        <a href="/notifications" class="notif-back-link" style="display: inline-flex; align-items: center; gap: 8px; color: var(--evo-accent, #2563eb); text-decoration: none; font-weight: 600; font-size: 0.95rem;">
            <i class="fa-solid fa-arrow-left"></i> Retour aux notifications
        </a>
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 12px;">
            <h1 style="margin: 0;">{{notification.title}}</h1>
            <div class="notif-header-meta" style="display: flex; align-items: center; gap: 10px;">
                <span class="notif-source">{{notification.source_plugin}}</span>
                {% if notification.created_at %}
                    <span class="notif-time" style="color: var(--evo-text-muted, #64748b); font-size: 0.9rem;">{{notification.created_at|date("d/m/Y H:i")}}</span>
                {% endif %}
            </div>
        </div>
    </div>

    <div class="notification notif-detail-card" style="display: block; padding: 2rem;">
        <div class="notif-detail-body">
            {% if notification.message %}
                <div class="notif-full-message" style="white-space: pre-wrap; line-height: 1.7; font-size: 1.05rem; color: var(--evo-text, inherit);">{{notification.message}}</div>
            {% else %}
                <p style="color: var(--evo-text-muted, #64748b); font-style: italic;">Aucun message textuel supplémentaire pour cette notification.</p>
            {% endif %}

            {% if notification.link_url %}
                <div class="notif-detail-actions" style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--evo-border, #e2e8f0); display: flex; gap: 12px; align-items: center;">
                    <a href="{{notification.link_url}}" class="evo-btn evo-btn-primary" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: var(--evo-accent, #2563eb); color: #fff;">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        {% if notification.link_url == '/admin' or notification.link_url == '/admin/' %}
                            Accéder à l'Administration
                        {% elseif notification.link_url starts with '/admin' %}
                            Accéder à l'espace concerné
                        {% elseif notification.link_url starts with '/profile' %}
                            Voir mon profil
                        {% else %}
                            Ouvrir le lien associé
                        {% endif %}
                    </a>
                </div>
            {% endif %}
        </div>
    </div>
</div>

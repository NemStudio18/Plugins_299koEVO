<div class="prordv-admin-wrapper" style="padding:20px;font-family:system-ui,-apple-system,sans-serif;">
    <div style="background:#fff;border-radius:12px;padding:24px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.05);margin-bottom:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h1 style="font-size:1.5rem;font-weight:800;color:#0f172a;margin:0 0 6px;">
                    <i class="fa-solid fa-calendar-check" style="color:#10b981;"></i> Supervision des Rendez-vous Pro
                </h1>
                <p style="color:#64748b;margin:0;font-size:0.95rem;">
                    Vue d'ensemble de tous les commerces, artisans et thérapeutes proposant la prise de rendez-vous en ligne.
                </p>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <a href="/pro/rdv" target="_blank" class="btn" style="background:#0f766e;color:#fff;border:none;padding:8px 14px;border-radius:8px;text-decoration:none;font-weight:600;font-size:0.9rem;">
                    <i class="fa-solid fa-calendar-days"></i> Vue Espace Pro (/pro/rdv)
                </a>
                <a href="/admin" class="btn" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;padding:8px 14px;border-radius:8px;text-decoration:none;font-weight:600;font-size:0.9rem;">
                    Retour administration
                </a>
            </div>
        </div>
    </div>

    <div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <table style="width:100%;border-collapse:collapse;text-align:left;font-size:0.95rem;">
            <thead>
                <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;color:#475569;font-size:0.85rem;text-transform:uppercase;">
                    <th style="padding:14px 18px;">Commerce / Établissement</th>
                    <th style="padding:14px 18px;">Type</th>
                    <th style="padding:14px 18px;">Contact</th>
                    <th style="padding:14px 18px;text-align:center;">RDV Confirmés</th>
                    <th style="padding:14px 18px;text-align:center;">Statut</th>
                    <th style="padding:14px 18px;text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                {% for c in commerces %}
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:14px 18px;font-weight:700;color:#0f172a;">
                        {{ c.nom }}
                        <div style="font-size:0.8rem;font-weight:400;color:#64748b;">
                            Slug : <a href="/rdv/{{ c.slug }}" target="_blank" style="color:#2563eb;">/rdv/{{ c.slug }}</a>
                        </div>
                    </td>
                    <td style="padding:14px 18px;color:#475569;">
                        <span style="background:#f1f5f9;padding:3px 8px;border-radius:6px;font-size:0.85rem;text-transform:capitalize;">
                            {{ c.type }}
                        </span>
                    </td>
                    <td style="padding:14px 18px;color:#475569;font-size:0.9rem;">
                        <div><i class="fa-solid fa-envelope"></i> {{ c.email }}</div>
                        {% if c.telephone %}
                        <div><i class="fa-solid fa-phone"></i> {{ c.telephone }}</div>
                        {% endif %}
                    </td>
                    <td style="padding:14px 18px;text-align:center;">
                        <span style="font-weight:800;color:#059669;font-size:1.05rem;">{{ c.confirmed_count }}</span>
                        <span style="color:#94a3b8;font-size:0.8rem;">/ {{ c.total_count }}</span>
                    </td>
                    <td style="padding:14px 18px;text-align:center;">
                        {% if c.is_active %}
                        <span style="background:#ecfdf5;color:#059669;font-weight:700;padding:4px 10px;border-radius:12px;font-size:0.8rem;">
                            Actif
                        </span>
                        {% else %}
                        <span style="background:#fef2f2;color:#b91c1c;font-weight:700;padding:4px 10px;border-radius:12px;font-size:0.8rem;">
                            Inactif
                        </span>
                        {% endif %}
                    </td>
                    <td style="padding:14px 18px;text-align:right;">
                        <div style="display:flex;justify-content:flex-end;gap:8px;align-items:center;">
                            <a href="/admin/prordv/commerce/{{ c.id }}" class="btn" style="background:#0f766e;color:#fff;padding:6px 12px;border-radius:6px;text-decoration:none;font-size:0.85rem;font-weight:600;">
                                <i class="fa-solid fa-calendar-days"></i> Agenda
                            </a>
                            <a href="/pro/rdv/config?commerce_id={{ c.id }}" target="_blank" class="btn" style="background:#f8fafc;color:#334155;border:1px solid #cbd5e1;padding:6px 10px;border-radius:6px;text-decoration:none;font-size:0.85rem;font-weight:600;" title="Configurer les prestations, équipe et horaires">
                                <i class="fa-solid fa-gear"></i> Paramètres
                            </a>
                            <form action="/admin/prordv/toggle/{{ c.id }}" method="post" style="margin:0;display:inline;">
                                <button type="submit" style="background:#fff;border:1px solid #cbd5e1;color:{% if c.is_active %}#d97706{% else %}#059669{% endif %};padding:6px 10px;border-radius:6px;font-size:0.85rem;font-weight:600;cursor:pointer;">
                                    {% if c.is_active %}Mettre en pause{% else %}Activer{% endif %}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                {% endfor %}
            </tbody>
        </table>
    </div>
</div>

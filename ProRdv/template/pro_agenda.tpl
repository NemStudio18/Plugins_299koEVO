<link rel="stylesheet" href="/plugin/ProRdv/public/css/prordv.css">

<div class="prordv-agenda-wrapper">
    <div class="prordv-card">
        
        <!-- En-tête Espace Pro -->
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;border-bottom:1px solid var(--prordv-border);padding-bottom:18px;">
            <div>
                <span class="prordv-badge-pro">
                    <i class="fa-solid fa-crown"></i> Espace Professionnel
                </span>
                <h1 style="font-size:1.6rem;font-weight:800;color:var(--prordv-text);margin:0;">
                    Agenda : {{ commerce.nom }}
                </h1>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="/rdv/{{ commerce.slug }}" target="_blank" class="btn" style="display:inline-flex;align-items:center;gap:6px;background:var(--prordv-surface-alt);color:var(--prordv-text);border:1px solid var(--prordv-border);padding:8px 14px;border-radius:8px;text-decoration:none;font-weight:600;font-size:0.9rem;">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Page publique
                </a>
                <a href="/pro/rdv/config?commerce_id={{ commerce.id }}" class="btn" style="display:inline-flex;align-items:center;gap:6px;background:var(--prordv-primary);color:var(--evo-accent-text, #fff);padding:8px 14px;border-radius:8px;text-decoration:none;font-weight:600;font-size:0.9rem;">
                    <i class="fa-solid fa-gear"></i> Paramètres
                </a>
            </div>
        </div>

        <!-- Navigation par date -->
        <div class="prordv-agenda-nav">
            <a href="/pro/rdv?d={{ prev_date }}" style="display:inline-flex;align-items:center;gap:6px;background:var(--prordv-card-bg);border:1px solid var(--prordv-border);padding:8px 14px;border-radius:8px;color:var(--prordv-text);text-decoration:none;font-weight:600;font-size:0.9rem;">
                <i class="fa-solid fa-chevron-left"></i> Jour précédent
            </a>
            <div style="text-align:center;">
                <div style="font-size:1.15rem;font-weight:800;color:var(--prordv-text);">
                    <i class="fa-regular fa-calendar"></i> {{ d|date('d/m/Y') }}
                </div>
                {% if d == 'now'|date('Y-m-d') %}
                <span style="font-size:0.8rem;color:var(--prordv-success);font-weight:700;">(Aujourd'hui)</span>
                {% else %}
                <a href="/pro/rdv?d={{ 'now'|date('Y-m-d') }}" style="font-size:0.8rem;color:var(--prordv-primary);font-weight:600;text-decoration:underline;">Revenir à aujourd'hui</a>
                {% endif %}
            </div>
            <a href="/pro/rdv?d={{ next_date }}" style="display:inline-flex;align-items:center;gap:6px;background:var(--prordv-card-bg);border:1px solid var(--prordv-border);padding:8px 14px;border-radius:8px;color:var(--prordv-text);text-decoration:none;font-weight:600;font-size:0.9rem;">
                Jour suivant <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>

        <!-- Liste des rendez-vous -->
        {% if liste|length > 0 %}
        <div style="display:flex;flex-direction:column;gap:14px;">
            {% for rdv in liste %}
            <div class="prordv-rdv-card {% if rdv.statut == 'confirme' %}confirme{% elseif rdv.statut == 'attente' %}attente{% endif %}">
                <div>
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                        <span style="font-size:1.15rem;font-weight:800;color:var(--prordv-text);">
                            {{ rdv.debut|date('H:i') }} - {{ rdv.fin|date('H:i') }}
                        </span>
                        {% if rdv.statut == 'confirme' %}
                        <span class="badge-statut-confirme">
                            <i class="fa-solid fa-circle-check"></i> Confirmé
                        </span>
                        {% elseif rdv.statut == 'attente' %}
                        <span class="badge-statut-attente">
                            <i class="fa-solid fa-clock"></i> En attente de validation
                        </span>
                        {% endif %}
                        {% if rdv.collab_nom %}
                        <span style="background:var(--prordv-surface-alt);color:var(--prordv-text-muted);font-size:0.75rem;font-weight:600;padding:3px 8px;border-radius:6px;">
                            <i class="fa-solid fa-user"></i> {{ rdv.collab_nom }}
                        </span>
                        {% endif %}
                    </div>
                    <div style="font-weight:700;color:var(--prordv-text);font-size:1.05rem;margin-bottom:4px;">
                        {{ rdv.nom }}
                    </div>
                    <div style="color:var(--prordv-text-muted);font-size:0.9rem;display:flex;flex-wrap:wrap;gap:14px;">
                        <span><i class="fa-solid fa-scissors"></i> {{ rdv.presta_nom }}</span>
                        <span><a href="tel:{{ rdv.tel }}" style="color:var(--prordv-primary);text-decoration:none;"><i class="fa-solid fa-phone"></i> {{ rdv.tel }}</a></span>
                        <span><i class="fa-solid fa-envelope"></i> {{ rdv.email }}</span>
                    </div>
                </div>

                <!-- Actions sur le rendez-vous -->
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <!-- Bouton SMS Magique de rappel -->
                    {% if rdv.sms_url %}
                    <a href="{{ rdv.sms_url }}" class="btn" style="display:inline-flex;align-items:center;gap:6px;background:var(--evo-info, #3b82f6);color:#fff;padding:8px 12px;border-radius:8px;text-decoration:none;font-weight:600;font-size:0.85rem;" title="Envoyer un rappel par SMS en 1 clic">
                        <i class="fa-solid fa-comment-sms"></i> SMS Rappel
                    </a>
                    {% endif %}

                    {% if rdv.statut == 'attente' %}
                    <form action="/pro/rdv/action" method="post" style="display:inline;">
    {{SHOW.tokenField}}
                        <input type="hidden" name="id" value="{{ rdv.id }}">
                        <input type="hidden" name="act" value="confirme">
                        <input type="hidden" name="d" value="{{ d }}">
                        <button type="submit" style="display:inline-flex;align-items:center;gap:6px;background:var(--prordv-success);color:var(--evo-accent-text, #fff);border:none;padding:8px 12px;border-radius:8px;font-weight:600;font-size:0.85rem;cursor:pointer;">
                            <i class="fa-solid fa-check"></i> Valider
                        </button>
                    </form>
                    {% endif %}

                    <form action="/pro/rdv/action" method="post" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?');">
    {{SHOW.tokenField}}
                        <input type="hidden" name="id" value="{{ rdv.id }}">
                        <input type="hidden" name="act" value="annule">
                        <input type="hidden" name="d" value="{{ d }}">
                        <button type="submit" style="display:inline-flex;align-items:center;gap:6px;background:var(--evo-danger-soft, rgba(239,68,68,0.15));color:var(--prordv-danger);border:1px solid var(--prordv-danger);padding:8px 12px;border-radius:8px;font-weight:600;font-size:0.85rem;cursor:pointer;">
                            <i class="fa-solid fa-xmark"></i> Annuler
                        </button>
                    </form>
                </div>
            </div>
            {% endfor %}
        </div>
        {% else %}
        <div style="text-align:center;padding:48px 20px;background:var(--prordv-surface);border-radius:12px;border:1px dashed var(--prordv-border);">
            <i class="fa-regular fa-calendar-check" style="font-size:2.5rem;color:var(--prordv-text-muted);margin-bottom:12px;display:block;"></i>
            <h3 style="font-size:1.15rem;font-weight:700;color:var(--prordv-text);margin:0 0 6px;">Aucun rendez-vous pour ce jour</h3>
            <p style="color:var(--prordv-text-muted);font-size:0.9rem;margin:0;">Vos créneaux libres restent ouverts à la réservation en ligne.</p>
        </div>
        {% endif %}
    </div>
</div>

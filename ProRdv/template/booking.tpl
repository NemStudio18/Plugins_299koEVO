<link rel="stylesheet" href="/plugin/ProRdv/public/css/prordv.css">

<div class="prordv-booking-wrapper">
    <div class="prordv-card">
        <div class="prordv-header">
            <span class="prordv-badge-online">
                <i class="fa-solid fa-calendar-check"></i> Prise de Rendez-vous en Ligne
            </span>
            <h1 class="prordv-title">{{ commerce.nom }}</h1>
            <p class="prordv-subtitle">Alternative locale &amp; souveraine berruyère</p>
        </div>

        {% if success %}
        <div class="prordv-alert prordv-alert-success">
            <i class="fa-solid fa-envelope-circle-check" style="font-size:2.5rem;margin-bottom:12px;display:block;"></i>
            <h2 style="font-size:1.25rem;font-weight:700;margin:0 0 8px;">Dernière étape : Confirmez votre demande</h2>
            <p style="margin:0 0 14px;font-size:0.95rem;">
                Un email de validation vient de vous être envoyé. 
                Veuillez cliquer sur le lien reçu dans les <strong>{{ resData.hold_minutes|default(15) }} minutes</strong> pour bloquer votre créneau du <strong>{{ resData.quand }}</strong>.
            </p>
            {% if cfg.mode_validation == 'manuel' %}
            <div class="prordv-alert prordv-alert-warning" style="text-align:left;font-size:0.88rem;margin-bottom:14px;">
                <i class="fa-solid fa-clock"></i> <strong>Validation sur demande :</strong> Ce professionnel valide manuellement ses interventions. Dès votre confirmation email effectuée, il vérifiera sa disponibilité et vous confirmera définitivement le rendez-vous.
            </div>
            {% endif %}
            <p style="color:var(--prordv-text-muted);font-size:0.85rem;margin:0;">Pensez à vérifier votre dossier de courriers indésirables (spams) si vous ne le recevez pas d'ici quelques instants.</p>
        </div>
        <div style="text-align:center;">
            <a href="/rdv/{{ commerce.slug }}" class="btn" style="display:inline-block;background:var(--prordv-success);color:var(--evo-accent-text, #fff);padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:600;">Réserver un autre créneau</a>
        </div>
        {% else %}

        {% if error %}
        <div class="prordv-alert prordv-alert-danger">
            <i class="fa-solid fa-triangle-exclamation"></i> {{ error }}
        </div>
        {% endif %}

        {% if cfg.notice %}
        <div class="prordv-alert-info">
            <i class="fa-solid fa-circle-info"></i> <strong>Consigne du professionnel :</strong> {{ cfg.notice }}
        </div>
        {% endif %}

        <form action="/rdv/reserver" method="post" id="prordv-form">
    {{SHOW.tokenField}}
            <input type="hidden" name="commerce_id" value="{{ commerce.id }}">
            <!-- Honeypot anti-bot -->
            <input type="text" name="website_hp" style="display:none;" tabindex="-1" autocomplete="off">

            <!-- Étape 1 : Choix de la Prestation -->
            <div class="prordv-form-group">
                <label class="prordv-form-label">
                    1. Choisissez votre prestation
                </label>
                <div style="display:grid;grid-template-columns:1fr;gap:10px;">
                    {% for p in prestas %}
                    <label class="prordv-presta-card {% if selectedPresta.id == p.id %}selected{% endif %}">
                        <span style="display:flex;align-items:center;gap:12px;">
                            <input type="radio" name="presta_id" value="{{ p.id }}" {% if selectedPresta.id == p.id %}checked{% endif %} onchange="window.location.href='/rdv/{{ commerce.slug }}?p=' + this.value + '&d={{ selectedDate }}'">
                            <div>
                                <strong style="color:var(--prordv-text);font-size:0.95rem;display:block;">{{ p.nom }}</strong>
                                {% if p.description %}
                                <span style="font-size:0.8rem;color:var(--prordv-text-muted);">{{ p.description }}</span>
                                {% endif %}
                            </div>
                        </span>
                        <span style="display:flex;align-items:center;gap:8px;">
                            <span class="prordv-presta-duree">
                                <i class="fa-regular fa-clock"></i> {{ p.duree_min }} min
                            </span>
                            {% if p.prix %}
                            <span class="prordv-presta-prix">{{ p.prix }} €</span>
                            {% else %}
                            <span style="color:var(--prordv-text-muted);font-size:0.85rem;">Devis</span>
                            {% endif %}
                        </span>
                    </label>
                    {% endfor %}
                </div>
            </div>

            <!-- Étape 2 : Choix du Collaborateur (si activé) -->
            {% if collabs|length > 0 %}
            <div class="prordv-form-group">
                <label class="prordv-form-label">
                    2. Intervenant / Collaborateur (facultatif)
                </label>
                <select name="collab_id" class="prordv-select" onchange="window.location.href='/rdv/{{ commerce.slug }}?p={{ selectedPresta.id }}&d={{ selectedDate }}&k=' + this.value">
                    <option value="">Sans préférence (premier disponible)</option>
                    {% for c in collabs %}
                    <option value="{{ c.id }}" {% if selectedCollabId == c.id %}selected{% endif %}>{{ c.nom }}</option>
                    {% endfor %}
                </select>
            </div>
            {% endif %}

            <!-- Étape 3 : Date et Sélection du Créneau -->
            <div class="prordv-form-group">
                <label class="prordv-form-label">
                    3. Date et heure du rendez-vous
                </label>
                <div style="margin-bottom:12px;">
                    <input type="date" name="date" value="{{ selectedDate }}" min="{{ 'now'|date('Y-m-d') }}" class="prordv-input" style="font-weight:600;" onchange="window.location.href='/rdv/{{ commerce.slug }}?p={{ selectedPresta.id }}&k={{ selectedCollabId }}&d=' + this.value">
                </div>

                <div class="prordv-slots-box">
                    <div style="font-size:0.85rem;font-weight:600;color:var(--prordv-text-muted);margin-bottom:10px;">
                        Créneaux disponibles pour le {{ selectedDate|date('d/m/Y') }} :
                    </div>
                    {% if slots|length > 0 %}
                    <div class="prordv-slots-grid">
                        {% for s in slots %}
                        <label style="display:block;text-align:center;">
                            <input type="radio" name="heure" value="{{ s }}" required style="display:none;" id="slot-{{ s }}">
                            <span class="slot-pill" onclick="document.querySelectorAll('.slot-pill').forEach(el=>el.classList.remove('selected'));this.classList.add('selected');">
                                {{ s }}
                            </span>
                        </label>
                        {% endfor %}
                    </div>
                    {% else %}
                    <p style="color:var(--prordv-text-muted);font-size:0.9rem;margin:8px 0;text-align:center;">
                        <i class="fa-regular fa-calendar-xmark"></i> Aucun créneau libre ce jour. Choisissez une autre date.
                    </p>
                    {% endif %}
                </div>
            </div>

            <!-- Étape 4 : Coordonnées du Client & Champs Métiers -->
            <div class="prordv-form-group">
                <label class="prordv-form-label">
                    4. Vos coordonnées &amp; informations
                </label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                    <div>
                        <input type="text" name="nom" placeholder="Votre nom complet *" required class="prordv-input">
                    </div>
                    <div>
                        <input type="tel" name="tel" placeholder="N° de téléphone (SMS) *" required class="prordv-input">
                    </div>
                </div>
                <div style="margin-bottom:12px;">
                    <input type="email" name="email" placeholder="Adresse email pour la confirmation *" required class="prordv-input">
                </div>

                <!-- Champs Personnalisés selon le Profil Métier -->
                {% if cfg.champs_requis.adresse %}
                <div style="margin-bottom:12px;">
                    <input type="text" name="adresse" placeholder="Adresse complète de l'intervention (Rue, CP, Ville) *" required class="prordv-input">
                </div>
                {% endif %}

                {% if cfg.champs_requis.marque_modele %}
                <div style="margin-bottom:12px;">
                    <input type="text" name="marque_modele" placeholder="Marque et modèle de l'appareil / véhicule (ex: Renault Clio, iPhone 14...) *" required class="prordv-input">
                </div>
                {% endif %}

                {% if cfg.champs_requis.lieu_rdv %}
                <div style="margin-bottom:12px;">
                    <select name="lieu_rdv" class="prordv-select">
                        <option value="Au bureau / En agence">Au bureau / En agence</option>
                        <option value="À mon domicile">À mon domicile</option>
                        <option value="Par visio / téléphone">Par téléphone / visio</option>
                    </select>
                </div>
                {% endif %}

                {% if cfg.champs_requis.objet %}
                <div style="margin-bottom:12px;">
                    <input type="text" name="objet" placeholder="Motif ou objet de l'échange *" required class="prordv-input">
                </div>
                {% endif %}

                {% if cfg.champs_requis.details %}
                <div style="margin-bottom:12px;">
                    <textarea name="details" placeholder="Description des travaux souhaités ou du problème rencontré..." rows="2" class="prordv-textarea"></textarea>
                </div>
                {% endif %}

                {% if cfg.champs_requis.remarque or not cfg.champs_requis %}
                <div>
                    <textarea name="remarque" placeholder="Remarque ou précision particulière pour le commerçant (facultatif)" rows="2" class="prordv-textarea"></textarea>
                </div>
                {% endif %}
            </div>

            <button type="submit" class="btn-prordv-submit">
                <i class="fa-solid fa-check"></i> Valider et Réserver ce Créneau
            </button>
        </form>
        {% endif %}
    </div>
</div>

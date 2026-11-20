<section class="annonces-wrap">
    <div class="annonces-hero-bar">
        <div>
            {% if introText %}
                <div class="annonces-intro">{{introText}}</div>
            {% endif %}
        </div>
        <div class="annonces-top-actions">
            {% if allowPublicSubmission %}
                <a href="{{deposerUrl}}" class="button success"><i class="fa-solid fa-plus-circle"></i> Déposer une annonce</a>
            {% endif %}
            {% if isLogged %}
                <a href="{{mesAnnoncesUrl}}" class="button"><i class="fa-solid fa-user-tag"></i> Mes annonces{% if myAnnoncesCount > 0 %} ({{myAnnoncesCount}}){% endif %}</a>
            {% endif %}
        </div>
    </div>

    <form method="get" action="{{listUrl}}" class="annonces-filters">
        <input type="text" name="q" value="{{queryFilter}}" placeholder="Rechercher une annonce (titre, lieu…)" aria-label="Rechercher" />
        <select name="cat" aria-label="Catégorie">
            <option value="">Toutes catégories</option>
            {% for cat in categories %}
                <option value="{{cat}}" {% if categoryFilter == cat %}selected{% endif %}>{{cat}}</option>
            {% endfor %}
        </select>
        <select name="status" aria-label="Disponibilité">
            <option value="">Tous statuts</option>
            <option value="available" {% if statusFilter == 'available' %}selected{% endif %}>Disponible</option>
            <option value="reserved" {% if statusFilter == 'reserved' %}selected{% endif %}>Réservée</option>
            <option value="sold" {% if statusFilter == 'sold' %}selected{% endif %}>Vendue</option>
        </select>
        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Filtrer</button>
    </form>

    {% if items %}
        <div class="annonces-grid">
            {% for item in items %}
                <article class="annonce-card">
                    <div class="annonce-card-media">
                        {% if item.image %}
                            <img src="{{item.image}}" alt="{{item.title}}" loading="lazy" />
                        {% else %}
                            <span class="annonce-card-placeholder"><i class="fa-solid fa-tags"></i></span>
                        {% endif %}
                        <span class="annonce-badge annonce-badge-{{item.status}}">{{item.status_label}}</span>
                        {% if item.images_count > 1 %}
                            <span class="annonce-photo-count-badge"><i class="fa-solid fa-camera"></i> {{item.images_count}}</span>
                        {% endif %}
                    </div>
                    <div class="annonce-card-body">
                        <div class="annonce-card-meta">
                            {% if item.category %}<span><i class="fa-solid fa-folder"></i> {{item.category}}</span>{% endif %}
                            {% if item.location %}<span><i class="fa-solid fa-location-dot"></i> {{item.location}}</span>{% endif %}
                        </div>
                        <h3 class="annonce-card-title">{{item.title}}</h3>
                        <div class="annonce-card-price">{{item.price_label}}</div>
                        {% if item.excerpt %}
                            <p class="annonce-card-excerpt">{{item.excerpt}}</p>
                        {% endif %}
                        <div class="annonce-card-footer">
                            <span class="annonce-card-author"><i class="fa-regular fa-user"></i> {{item.author_name}}</span>
                            <a href="{{item.url}}" class="button primary annonce-card-btn">
                                <i class="fa-solid fa-eye"></i>
                                {% if item.is_available %}Voir &amp; réserver{% else %}Voir l'annonce{% endif %}
                            </a>
                        </div>
                    </div>
                </article>
            {% endfor %}
        </div>
    {% else %}
        <div class="annonces-empty">
            <i class="fa-solid fa-tags"></i>
            {{Lang.annonces.empty}}
        </div>
    {% endif %}
</section>

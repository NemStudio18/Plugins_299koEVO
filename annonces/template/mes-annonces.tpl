<section class="annonces-wrap annonces-dashboard-wrap">
    <div class="annonces-page-header">
        <div>
            <h2><i class="fa-solid fa-user-tag"></i> Mes Petites Annonces</h2>
            <p class="annonces-subtitle">Gérez vos publications, modifiez vos fiches et suivez les demandes de réservation reçues.</p>
        </div>
        <div class="annonces-header-btns">
            <a href="{{deposerUrl}}" class="button success"><i class="fa-solid fa-plus-circle"></i> Déposer une annonce</a>
            <a href="{{homeUrl}}" class="button"><i class="fa-solid fa-store"></i> Voir toutes les annonces</a>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="annonces-stats-row">
        <div class="annonces-stat-box">
            <span class="stat-number">{{totalCount}}</span>
            <span class="stat-desc">Annonce(s) déposée(s)</span>
        </div>
        <div class="annonces-stat-box">
            <span class="stat-number">{{reservationsCount}}</span>
            <span class="stat-desc">Demande(s) de réservation</span>
        </div>
    </div>

    <!-- Onglets Mes Annonces / Réservations -->
    <div class="annonces-tabs">
        <button type="button" class="annonces-tab-btn active" onclick="switchAnnoncesTab('annonces', this)">
            <i class="fa-solid fa-tags"></i> Mes annonces ({{totalCount}})
        </button>
        <button type="button" class="annonces-tab-btn" onclick="switchAnnoncesTab('reservations', this)">
            <i class="fa-solid fa-inbox"></i> Demandes reçues ({{reservationsCount}})
        </button>
    </div>

    <!-- Panneau 1 : Mes Annonces -->
    <div id="tab-annonces" class="annonces-tab-panel active">
        {% if annonces %}
            <div class="annonces-user-list">
                {% for item in annonces %}
                    <div class="annonce-user-card">
                        <div class="annonce-user-media">
                            {% if item.getMainImage() %}
                                <img src="{{item.getMainImage()}}" alt="{{item.attributes.title}}">
                            {% else %}
                                <span class="annonce-placeholder"><i class="fa-solid fa-tags"></i></span>
                            {% endif %}
                        </div>

                        <div class="annonce-user-details">
                            <div class="annonce-user-meta">
                                <span class="annonce-badge annonce-badge-{{item.attributes.status}}">{{item.getStatusLabel()}}</span>
                                {% if item.isPendingModeration() %}
                                    <span class="annonce-badge annonce-badge-pending"><i class="fa-solid fa-hourglass-half"></i> En attente de validation</span>
                                {% elseif item.isRejected() %}
                                    <span class="annonce-badge annonce-badge-rejected" title="{{item.attributes.rejection_reason}}"><i class="fa-solid fa-circle-xmark"></i> Refusée</span>
                                {% endif %}
                                <span class="annonce-date"><i class="fa-regular fa-clock"></i> {{item.attributes.created_at}}</span>
                            </div>

                            <h3 class="annonce-user-title">
                                {% if item.isPublic() %}
                                    <a href="{{item.getPublicUrl()}}" target="_blank">{{item.attributes.title}}</a>
                                {% else %}
                                    {{item.attributes.title}}
                                {% endif %}
                            </h3>

                            <div class="annonce-user-info-row">
                                <strong class="annonce-user-price">{{item.attributes.price_label}}</strong>
                                <span><i class="fa-solid fa-folder"></i> {{item.attributes.category}}</span>
                                <span><i class="fa-solid fa-location-dot"></i> {{item.getCity()}}</span>
                            </div>

                            {% if item.isRejected() and item.attributes.rejection_reason %}
                                <div class="annonce-rejection-box">
                                    <strong>Motif du refus :</strong> {{item.attributes.rejection_reason}}
                                </div>
                            {% endif %}
                        </div>

                        <div class="annonce-user-actions">
                            {% if item.isPublic() %}
                                <a href="{{item.getPublicUrl()}}" class="button small" target="_blank" title="Voir l'annonce en ligne"><i class="fa-solid fa-eye"></i></a>
                            {% endif %}
                            <a href="/annonces/modifier/{{item.getId()}}" class="button small primary" title="Modifier l'annonce"><i class="fa-solid fa-pen"></i> Modifier</a>

                            {% if item.isAvailable() %}
                                <a href="/annonces/statut/{{item.getId()}}/sold/{{userToken}}" class="button small success" title="Marquer comme vendue" onclick="return confirm('Marquer cette annonce comme vendue ?');">
                                    <i class="fa-solid fa-check"></i> Vendue
                                </a>
                            {% elseif item.isSold() %}
                                <a href="/annonces/statut/{{item.getId()}}/available/{{userToken}}" class="button small secondary" title="Remettre en vente">
                                    <i class="fa-solid fa-rotate-left"></i> Remettre en vente
                                </a>
                            {% endif %}

                            <a href="/annonces/supprimer/{{item.getId()}}/{{userToken}}" class="button small danger" title="Supprimer" onclick="return confirm('Supprimer définitivement cette annonce ?');">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </div>
                    </div>
                {% endfor %}
            </div>
        {% else %}
            <div class="annonces-empty-dashboard">
                <i class="fa-solid fa-tags fa-3x"></i>
                <p>Vous n'avez pas encore publié de petite annonce.</p>
                <a href="{{deposerUrl}}" class="button success"><i class="fa-solid fa-plus-circle"></i> Déposer ma première annonce</a>
            </div>
        {% endif %}
    </div>

    <!-- Panneau 2 : Demandes de réservation -->
    <div id="tab-reservations" class="annonces-tab-panel">
        {% if reservations %}
            <div class="annonces-reservations-list">
                {% for res in reservations %}
                    <div class="annonce-reservation-card">
                        <div class="reservation-card-header">
                            <div>
                                <strong>Pour l'annonce :</strong> <em>« {{res.annonce_title}} »</em>
                            </div>
                            <span class="reservation-date"><i class="fa-regular fa-clock"></i> {{res.created_at}}</span>
                        </div>

                        <div class="reservation-buyer-info">
                            <div><i class="fa-solid fa-user"></i> <strong>Acheteur :</strong> {{res.requester_name}}</div>
                            <div><i class="fa-solid fa-envelope"></i> <strong>Email :</strong> <a href="mailto:{{res.requester_email}}">{{res.requester_email}}</a></div>
                            {% if res.requester_phone %}
                                <div><i class="fa-solid fa-phone"></i> <strong>Téléphone :</strong> <a href="tel:{{res.requester_phone}}">{{res.requester_phone}}</a></div>
                            {% endif %}
                        </div>

                        <div class="reservation-message-box">
                            <strong>Message de l'acheteur :</strong>
                            <p>{{res.message}}</p>
                        </div>

                        <div class="reservation-actions">
                            <a href="mailto:{{res.requester_email}}?subject=Concernant votre demande pour « {{res.annonce_title}} »" class="button small success">
                                <i class="fa-solid fa-reply"></i> Répondre par email
                            </a>
                            {% if res.requester_phone %}
                                <a href="tel:{{res.requester_phone}}" class="button small">
                                    <i class="fa-solid fa-phone"></i> Appeler
                                </a>
                            {% endif %}
                        </div>
                    </div>
                {% endfor %}
            </div>
        {% else %}
            <div class="annonces-empty-dashboard">
                <i class="fa-solid fa-inbox fa-3x"></i>
                <p>Aucune demande de réservation reçue pour le moment.</p>
            </div>
        {% endif %}
    </div>
</section>

<script>
function switchAnnoncesTab(tabId, btn) {
    document.querySelectorAll('.annonces-tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.annonces-tab-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    const panel = document.getElementById('tab-' + tabId);
    if (panel) panel.classList.add('active');
}
</script>


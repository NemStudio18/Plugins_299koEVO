<section class="annonces-wrap annonces-deposer-wrap">
    <div class="annonces-page-header">
        <div>
            <h2>
                {% if isEdit %}
                    <i class="fa-solid fa-pen-to-square"></i> Modifier mon annonce
                {% else %}
                    <i class="fa-solid fa-plus-circle"></i> Déposer une petite annonce
                {% endif %}
            </h2>
            <p class="annonces-subtitle">
                {% if isEdit %}
                    Mettez à jour les informations et les photos de votre annonce.
                {% else %}
                    Remplissez ce formulaire pour publier votre annonce gratuitement sur la plateforme.
                {% endif %}
            </p>
        </div>
        <div>
            <a href="{{mesAnnoncesUrl}}" class="button"><i class="fa-solid fa-list-check"></i> Mes annonces</a>
        </div>
    </div>

    {% if moderationMode == 'pre' %}
        <div class="annonce-notice annonce-notice-info">
            <i class="fa-solid fa-shield-halved"></i> <strong>Modération active :</strong> Votre annonce sera vérifiée par notre équipe avant d'être visible publiquement.
        </div>
    {% endif %}

    <form method="post" action="{{saveUrl}}" enctype="multipart/form-data" class="annonces-form">
        <input type="hidden" name="csrf_token" value="{{csrf_token}}">

        <fieldset class="annonce-fieldset">
            <legend><i class="fa-solid fa-circle-info"></i> Informations principales</legend>

            <div class="annonce-form-row">
                <label for="annonce_title">Titre de l'annonce <span class="required">*</span></label>
                <input type="text" id="annonce_title" name="title" value="{{item.title}}" required maxlength="120" placeholder="Ex: Vélo tout-terrain Décathlon 26 pouces">
            </div>

            <div class="annonce-form-grid">
                <div class="annonce-form-row">
                    <label for="annonce_category">Catégorie <span class="required">*</span></label>
                    <select id="annonce_category" name="category" required>
                        <option value="">Sélectionnez une catégorie</option>
                        {% for cat in categories %}
                            <option value="{{cat}}" {% if item.category == cat %}selected{% endif %}>{{cat}}</option>
                        {% endfor %}
                    </select>
                </div>

                <div class="annonce-form-row">
                    <label for="annonce_price">Prix ou modalités <span class="required">*</span></label>
                    <input type="text" id="annonce_price" name="price_label" value="{{item.price_label}}" required placeholder="Ex: 85 €, Gratuit, À débattre">
                </div>

                <div class="annonce-form-row">
                    <label for="annonce_condition">État du bien <span class="required">*</span></label>
                    <select id="annonce_condition" name="item_condition">
                        <option value="Neuf avec étiquette" {% if item.item_condition == 'Neuf avec étiquette' %}selected{% endif %}>Neuf avec étiquette</option>
                        <option value="Comme neuf" {% if item.item_condition == 'Comme neuf' %}selected{% endif %}>Comme neuf</option>
                        <option value="Très bon état" {% if item.item_condition == 'Très bon état' %}selected{% endif %}>Très bon état</option>
                        <option value="Bon état" {% if item.item_condition == 'Bon état' %}selected{% endif %}>Bon état</option>
                        <option value="État satisfaisant" {% if item.item_condition == 'État satisfaisant' %}selected{% endif %}>État satisfaisant</option>
                        <option value="Pour pièces" {% if item.item_condition == 'Pour pièces' %}selected{% endif %}>Pour pièces</option>
                    </select>
                </div>
            </div>

            <div class="annonce-form-row">
                <label for="annonce_desc">Description détaillée <span class="required">*</span></label>
                <textarea id="annonce_desc" name="description" rows="6" required placeholder="Détaillez les caractéristiques, l'état précis, les dimensions et les modalités de remise...">{{item.description}}</textarea>
            </div>
        </fieldset>

        <fieldset class="annonce-fieldset">
            <legend><i class="fa-solid fa-camera"></i> Photos de l'annonce (jusqu'à {{maxPhotos}} photos)</legend>
            <p class="annonce-form-hint">Une annonce avec de belles photos a 5 fois plus de chances de trouver preneur. Formats acceptés : JPG, PNG, WebP (max 8 Mo par photo).</p>

            {% if isEdit and count(item.images) > 0 %}
                <div class="annonce-current-photos">
                    <p><strong>Photos actuelles conservées :</strong> (décochez pour supprimer)</p>
                    <div class="annonce-thumbs-edit-grid">
                        {% for img in item.images %}
                            <label class="annonce-thumb-edit-card">
                                <img src="{{img}}" alt="Photo">
                                <div class="annonce-thumb-edit-checkbox">
                                    <input type="checkbox" name="kept_images[]" value="{{img}}" checked>
                                    <span>Conserver</span>
                                </div>
                            </label>
                        {% endfor %}
                    </div>
                </div>
            {% endif %}

            <div class="annonce-dropzone-box" onclick="document.getElementById('annonce_photos_input').click()">
                <i class="fa-solid fa-cloud-arrow-up fa-2x"></i>
                <p>Cliquez ou glissez-déposez vos photos ici</p>
                <span class="button small">Choisir les photos</span>
                <input type="file" id="annonce_photos_input" name="image_files[]" multiple accept="image/*" style="display: none;" onchange="annoncePreviewPhotos(this)">
            </div>
            <div id="annonce_preview_container" class="annonce-thumbs-edit-grid" style="margin-top: 1rem;"></div>
        </fieldset>

        <fieldset class="annonce-fieldset">
            <legend><i class="fa-solid fa-location-dot"></i> Localisation & Contact</legend>
            <div class="annonce-form-grid">
                <div class="annonce-form-row">
                    <label for="annonce_city">Ville ou commune <span class="required">*</span></label>
                    <input type="text" id="annonce_city" name="city" value="{{item.city}}" required placeholder="Ex: Bourges, Saint-Doulchard...">
                </div>

                <div class="annonce-form-row">
                    <label for="annonce_postal">Code postal</label>
                    <input type="text" id="annonce_postal" name="postal_code" value="{{item.postal_code}}" placeholder="Ex: 18000">
                </div>

                <div class="annonce-form-row">
                    <label for="annonce_phone">Numéro de téléphone</label>
                    <input type="tel" id="annonce_phone" name="phone" value="{{item.phone}}" placeholder="Ex: 06 12 34 56 78">
                </div>
            </div>

            <div class="annonce-form-row annonce-checkbox-row">
                <label>
                    <input type="checkbox" name="show_phone" value="1" {% if item.show_phone %}checked{% endif %}>
                    <span>Afficher mon numéro de téléphone sur l'annonce publique (sinon contact uniquement via le formulaire de réservation)</span>
                </label>
            </div>
        </fieldset>

        <div class="annonce-form-actions">
            <a href="{{mesAnnoncesUrl}}" class="button secondary">Annuler</a>
            <button type="submit" class="button success">
                <i class="fa-solid fa-check"></i>
                {% if isEdit %}Enregistrer les modifications{% else %}Publier mon annonce{% endif %}
            </button>
        </div>
    </form>
</section>

<script>
function annoncePreviewPhotos(input) {
    const container = document.getElementById('annonce_preview_container');
    container.innerHTML = '';
    if (input.files) {
        Array.from(input.files).forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'annonce-thumb-edit-card';
                    div.innerHTML = `<img src="${e.target.result}" alt="Aperçu"><div class="annonce-thumb-label">${file.name}</div>`;
                    container.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        });
    }
}
</script>


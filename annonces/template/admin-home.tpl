<section class="module-card">
    <header><i class="fa-solid fa-tags"></i> {{Lang.annonces.admin-title}}</header>

    <div class="module-actions" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 1rem;">
        <a href="{{createUrl}}" class="button success">
            <i class="fa-solid fa-plus"></i> {{Lang.annonces.add}}
        </a>
        <a href="{{publicListUrl}}" target="_blank" class="button">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> Voir la page publique
        </a>
    </div>

    <div class="tabs-container" data-tab-key="annonces">
        <ul class="tabs-header">
            <li class="default-tab" data-tab="list">
                <i class="fa-solid fa-list"></i> {{Lang.annonces.tab-list}}
            </li>
            <li data-tab="moderation">
                <i class="fa-solid fa-shield-halved"></i> Modération
                {% if pendingModerationCount %}<span class="ann-counter-badge ann-counter-alert">{{pendingModerationCount}}</span>{% endif %}
            </li>
            <li data-tab="reservations">
                <i class="fa-solid fa-bookmark"></i> {{Lang.annonces.tab-reservations}}
                {% if pendingCount %}<span class="ann-counter-badge">{{pendingCount}}</span>{% endif %}
            </li>
            <li data-tab="settings">
                <i class="fa-solid fa-sliders"></i> {{Lang.annonces.tab-settings}}
            </li>
            <li data-tab="info">
                <i class="fa-solid fa-circle-info"></i> {{Lang.annonces.tab-info}}
            </li>
        </ul>

        <ul class="tabs">
            <!-- TAB 1 : Liste des annonces -->
            <li class="tab">
                {% if annonces %}
                    <table>
                        <thead>
                            <tr>
                                <th>Annonce</th>
                                <th>Catégorie</th>
                                <th>Auteur</th>
                                <th>Prix</th>
                                <th>Statut</th>
                                <th>Réservations</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {% for item in annonces %}
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            {% if item.image %}
                                                <img src="{{item.image}}" alt="" style="width: 44px; height: 44px; object-fit: cover; border-radius: 6px;" />
                                            {% endif %}
                                            <div>
                                                <strong>{{item.title}}</strong>
                                                {% if item.location %}<br><small style="color: #64748b;"><i class="fa-solid fa-location-dot"></i> {{item.location}}</small>{% endif %}
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{item.category}}</td>
                                    <td>{{item.author_name}}</td>
                                    <td>{{item.price_label}}</td>
                                    <td>
                                        <span class="ann-badge ann-badge-{{item.status}}">{{item.status_label}}</span>
                                        {% if item.moderation_status == 'pending' %}
                                            <span class="ann-badge ann-badge-pending" title="En attente de modération">En attente</span>
                                        {% elseif item.moderation_status == 'rejected' %}
                                            <span class="ann-badge ann-badge-rejected" title="Refusée">Refusée</span>
                                        {% endif %}
                                    </td>
                                    <td>{{item.reservations_count}}</td>
                                    <td style="white-space: nowrap;">
                                        <a href="{{item.public_url}}" target="_blank" class="button small" title="Voir"><i class="fa-solid fa-eye"></i></a>
                                        <a href="{{item.edit_url}}" class="button small primary" title="Modifier"><i class="fa-solid fa-pen"></i></a>
                                        <a href="{{item.delete_url}}" class="button small alert" onclick="return confirm('Supprimer cette annonce et ses réservations ?');" title="Supprimer"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                            {% endfor %}
                        </tbody>
                    </table>
                {% else %}
                    <div class="ann-empty-box">
                        Aucune annonce créée pour le moment. Cliquez sur <strong>{{Lang.annonces.add}}</strong> pour publier une petite annonce.
                    </div>
                {% endif %}
            </li>

            <!-- TAB 2 : Modération des annonces membres -->
            <li class="tab">
                {% if moderationAnnonces %}
                    <table>
                        <thead>
                            <tr>
                                <th>Annonce</th>
                                <th>Auteur</th>
                                <th>Catégorie / Prix</th>
                                <th>Description</th>
                                <th>Date</th>
                                <th>Décision</th>
                            </tr>
                        </thead>
                        <tbody>
                            {% for mod in moderationAnnonces %}
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            {% if mod.image %}
                                                <img src="{{mod.image}}" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;" />
                                            {% endif %}
                                            <div>
                                                <strong>{{mod.title}}</strong>
                                                {% if mod.location %}<br><small><i class="fa-solid fa-location-dot"></i> {{mod.location}}</small>{% endif %}
                                                {% if mod.images_count > 1 %}<br><small><i class="fa-solid fa-camera"></i> {{mod.images_count}} photos</small>{% endif %}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{mod.author_name}}</strong><br>
                                        <a href="mailto:{{mod.author_email}}">{{mod.author_email}}</a>
                                    </td>
                                    <td>
                                        <strong>{{mod.price_label}}</strong><br>
                                        <span class="ann-badge">{{mod.category}}</span>
                                    </td>
                                    <td style="max-width: 300px; font-size: 0.88rem;">{{mod.description}}</td>
                                    <td style="white-space: nowrap; font-size: 0.85rem;">{{mod.created_at}}</td>
                                    <td style="white-space: nowrap;">
                                        <form method="post" action="{{mod.moderate_url}}" style="display: inline-flex; gap: 5px; align-items: center;">
                                            <input type="hidden" name="token" value="{{token}}">
                                            <input type="hidden" name="_csrf" value="{{_csrfToken}}">
                                            <button type="submit" name="action" value="approve" class="button small success" title="Valider et publier">
                                                <i class="fa-solid fa-check"></i> Valider
                                            </button>
                                            <button type="button" class="button small alert" onclick="toggleRejectBox('{{mod.id}}')" title="Refuser">
                                                <i class="fa-solid fa-xmark"></i> Refuser
                                            </button>
                                        </form>

                                        <div id="reject-box-{{mod.id}}" style="display: none; margin-top: 8px; padding: 8px; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 6px;">
                                            <form method="post" action="{{mod.moderate_url}}">
                                                <input type="hidden" name="token" value="{{token}}">
                                                <input type="hidden" name="_csrf" value="{{_csrfToken}}">
                                                <input type="hidden" name="action" value="reject">
                                                <input type="text" name="rejection_reason" placeholder="Motif du refus (optionnel)" style="width: 100%; margin-bottom: 5px; font-size: 0.85rem;">
                                                <button type="submit" class="button small alert">Confirmer le refus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            {% endfor %}
                        </tbody>
                    </table>
                {% else %}
                    <div class="ann-empty-box">
                        <i class="fa-solid fa-circle-check" style="color: #22c55e;"></i> Aucune annonce en attente de validation. Toutes les soumissions sont à jour !
                    </div>
                {% endif %}
            </li>

            <!-- TAB 3 : Demandes de réservation -->
            <li class="tab">
                {% if reservations %}
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Annonce</th>
                                <th>Demandeur</th>
                                <th>Message</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {% for res in reservations %}
                                <tr>
                                    <td style="white-space: nowrap; font-size: 0.85rem;">{{res.created_at}}</td>
                                    <td><strong>{{res.annonce_title}}</strong></td>
                                    <td>
                                        <strong>{{res.requester_name}}</strong><br>
                                        <a href="mailto:{{res.requester_email}}">{{res.requester_email}}</a>
                                        {% if res.requester_phone %}<br><small>{{res.requester_phone}}</small>{% endif %}
                                    </td>
                                    <td style="max-width: 280px; font-size: 0.88rem;">{{res.message}}</td>
                                    <td><span class="ann-badge ann-res-{{res.status}}">{{res.status_label}}</span></td>
                                    <td style="white-space: nowrap;">
                                        <form method="post" action="{{res.status_url}}" style="display: inline-flex; gap: 4px; align-items: center;">
                                            <input type="hidden" name="token" value="{{token}}" />
                                            <input type="hidden" name="_csrf" value="{{_csrfToken}}" />
                                            {% if not res.is_confirmed %}
                                                <button type="submit" name="status" value="confirmed" class="button small success" title="Confirmer et marquer l'annonce comme réservée">
                                                    <i class="fa-solid fa-check"></i> Confirmer
                                                </button>
                                            {% endif %}
                                            {% if res.is_pending or res.is_confirmed %}
                                                <button type="submit" name="status" value="cancelled" class="button small" title="Annuler la réservation">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            {% endif %}
                                        </form>
                                        <a href="{{res.delete_url}}" class="button small alert" onclick="return confirm('Supprimer cette demande ?');" title="Supprimer"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                            {% endfor %}
                        </tbody>
                    </table>
                {% else %}
                    <div class="ann-empty-box">
                        {{Lang.annonces.reservations-empty}}
                    </div>
                {% endif %}
            </li>

            <!-- TAB 4 : Paramètres -->
            <li class="tab">
                <form method="post" action="{{saveConfUrl}}">
                    <input type="hidden" name="token" value="{{token}}" />
                    <input type="hidden" name="_csrf" value="{{_csrfToken}}" />

                    <h4><i class="fa-solid fa-display"></i> Affichage & Textes</h4>
                    <p>
                        <label for="cfg_label">Titre / libellé public</label><br>
                        <input type="text" id="cfg_label" name="label" value="{{cfgLabel}}" required />
                    </p>
                    <p>
                        <label for="cfg_intro">Texte d'introduction (en haut de la liste publique)</label><br>
                        <textarea id="cfg_intro" name="introText" class="editor" rows="3">{{cfgIntroText}}</textarea>
                    </p>
                    <p>
                        <label for="cfg_notice">Mention affichée dans le bloc de réservation</label><br>
                        <textarea id="cfg_notice" name="reservationNotice" rows="2">{{cfgReservationNotice}}</textarea>
                    </p>

                    <h4><i class="fa-solid fa-users"></i> Dépôt d'annonces par les membres (C2C)</h4>
                    <p>
                        <input type="checkbox" id="cfg_allow_submission" name="allowPublicSubmission" {% if cfgAllowPublicSubmission %}checked{% endif %} />
                        <label for="cfg_allow_submission"><strong>Autoriser les membres à déposer des petites annonces</strong></label>
                    </p>
                    <p>
                        <input type="checkbox" id="cfg_require_login" name="requireLoginToPost" {% if cfgRequireLoginToPost %}checked{% endif %} />
                        <label for="cfg_require_login">Exiger un compte membre connecté pour déposer (recommandé pour la modération et la traçabilité)</label>
                    </p>
                    <p>
                        <label for="cfg_moderation_mode">Workflow de modération des annonces déposées</label><br>
                        <select id="cfg_moderation_mode" name="moderationMode">
                            <option value="pre" {% if cfgModerationMode == 'pre' %}selected{% endif %}>Modération a priori : validation obligatoire par l'administrateur avant mise en ligne</option>
                            <option value="post" {% if cfgModerationMode == 'post' %}selected{% endif %}>Publication directe : l'annonce est immédiatement visible</option>
                        </select>
                    </p>
                    <p>
                        <label for="cfg_max_photos">Nombre maximum de photos par annonce</label><br>
                        <input type="number" id="cfg_max_photos" name="maxPhotos" value="{{cfgMaxPhotos}}" min="1" max="10" style="max-width: 120px;" />
                    </p>

                    <h4><i class="fa-solid fa-bell"></i> Réservations & Notifications</h4>
                    <p>
                        <label for="cfg_notify">Email de notification administrateur (copie des réservations)</label><br>
                        <input type="email" id="cfg_notify" name="notifyEmail" value="{{cfgNotifyEmail}}" placeholder="admin@exemple.fr" />
                    </p>
                    <p>
                        <label for="cfg_on_reserve">Comportement dès réception d'une demande de réservation</label><br>
                        <select id="cfg_on_reserve" name="onReserveStatus">
                            <option value="keep_available" {% if cfgOnReserveStatus == 'keep_available' %}selected{% endif %}>Laisser l'annonce « Disponible » jusqu'à confirmation manuelle</option>
                            <option value="mark_reserved" {% if cfgOnReserveStatus == 'mark_reserved' %}selected{% endif %}>Passer immédiatement l'annonce en « Réservée »</option>
                        </select>
                    </p>
                    <p>
                        <input type="checkbox" id="cfg_antispam" name="requireAntispam" {% if cfgRequireAntispam %}checked{% endif %} />
                        <label for="cfg_antispam">Activer l'antispam sur le formulaire de réservation (si le plugin Antispam est actif)</label>
                    </p>

                    <p style="margin-top: 1.5rem;">
                        <button type="submit" class="button primary"><i class="fa-solid fa-save"></i> Enregistrer les paramètres</button>
                    </p>
                </form>
            </li>

            <!-- TAB 5 : Informations -->
            <li class="tab">
                <h4>Fonctionnement de la plateforme Petites Annonces</h4>
                <p>Ce module permet de faire vivre une communauté d'échange et de vente entre particuliers sans intermédiaire financier.</p>
                <ul style="line-height: 1.7; padding-left: 1.25rem;">
                    <li><strong>Dépôt C2C :</strong> les membres connectés peuvent soumettre des annonces avec multi-photos via <code>/annonces/deposer</code>.</li>
                    <li><strong>Modération intégrée :</strong> l'administrateur peut valider ou refuser les annonces en attente avec motif par email.</li>
                    <li><strong>Espace membre « Mes annonces » :</strong> chaque membre retrouve ses fiches, modifie ses prix, marque ses objets comme vendus et consulte les réservations reçues.</li>
                    <li><strong>Zéro frais / Zéro commission :</strong> la plateforme met en relation vendeurs et acheteurs en direct.</li>
                </ul>
            </li>
        </ul>
    </div>
</section>

<script>
function toggleRejectBox(id) {
    const box = document.getElementById('reject-box-' + id);
    if (box) {
        box.style.display = box.style.display === 'none' ? 'block' : 'none';
    }
}
</script>

<style>
.ann-counter-badge { background: #0284c7; color: #fff; font-size: 0.72rem; padding: 1px 7px; border-radius: 999px; margin-left: 4px; font-weight: 700; }
.ann-counter-alert { background: #f59e0b; }
.ann-empty-box { padding: 1.5rem; text-align: center; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px; color: #64748b; }
.ann-badge { display: inline-block; padding: 3px 9px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; }
.ann-badge-available { background: #dcfce7; color: #166534; }
.ann-badge-reserved  { background: #fef3c7; color: #92400e; }
.ann-badge-sold      { background: #e0f2fe; color: #075985; }
.ann-badge-closed    { background: #e2e8f0; color: #475569; }
.ann-badge-draft     { background: #f1f5f9; color: #64748b; }
.ann-badge-pending   { background: #fef3c7; color: #92400e; }
.ann-badge-rejected  { background: #fee2e2; color: #991b1b; }
.ann-res-pending     { background: #fef3c7; color: #92400e; }
.ann-res-confirmed   { background: #dcfce7; color: #166534; }
.ann-res-cancelled   { background: #fee2e2; color: #991b1b; }

:root[data-theme="dark"] .ann-empty-box { background: rgba(255,255,255,0.04); border-color: rgba(255,255,255,0.12); color: #94a3b8; }
:root[data-theme="dark"] .ann-badge-available { background: rgba(34, 197, 94, 0.2); color: #86efac; }
:root[data-theme="dark"] .ann-badge-reserved  { background: rgba(245, 158, 11, 0.2); color: #fde68a; }
:root[data-theme="dark"] .ann-badge-sold      { background: rgba(2, 132, 199, 0.2); color: #bae6fd; }
:root[data-theme="dark"] .ann-badge-closed    { background: rgba(255,255,255,0.1); color: #cbd5e1; }
:root[data-theme="dark"] .ann-badge-pending   { background: rgba(245, 158, 11, 0.2); color: #fde68a; }
:root[data-theme="dark"] .ann-badge-rejected  { background: rgba(239, 68, 68, 0.2); color: #fca5a5; }
</style>

<div class="tabs-container" data-tab-key="livechat">
    <ul class="tabs-header">
        <li data-tab="conversations" class="default-tab">
            <i class="fa-solid fa-comments"></i> Discussions
            <span class="tab-unread-badge" id="tabUnreadBadge" style="{% if not stats.unread %}display:none;{% endif %}background:var(--lc-accent, #1f62a9);color:#fff;border-radius:10px;padding:2px 8px;font-size:0.75rem;margin-left:6px;font-weight:700;">{{stats.unread|default(0)}}</span>
        </li>
        <li data-tab="config">
            <i class="fa-solid fa-gear"></i> Configuration & Apparence
        </li>
    </ul>
    <ul class="tabs">
        <!-- ONGLET 1 : DISCUSSIONS EN DIRECT -->
        <li class="tab" style="padding: 12px 14px;">
            <div class="livechat-admin-container">

    <div class="livechat-main-layout {% if mobileList or not activeConversation %}mobile-show-list{% endif %}"
         id="livechatMainLayout"
         data-active-conv="{{activeConvId|default(0)}}"
         data-filter-status="{{filterStatus|default('all')}}"
         data-total-matching="{{totalMatching|default(0)}}"
         data-page-limit="{{pageLimit|default(50)}}">
        <!-- Colonne Gauche : Liste des Conversations -->
        <aside class="livechat-sidebar" id="livechatSidebar">
            <div class="livechat-sidebar-header">
                <div class="livechat-sidebar-title-row">
                    <h3>Discussions</h3>
                    <span class="livechat-badge-count" id="convTotalBadge" title="Total des discussions">
                        {{stats.all|default(0)}}
                    </span>
                </div>
                <span class="livechat-unread-pill {% if not stats.unread %}is-hidden{% endif %}" id="convUnreadPill">
                    <span id="convUnreadCount">{{stats.unread|default(0)}}</span> non lue(s)
                </span>
            </div>

            <div class="livechat-sidebar-tools">
                <div class="livechat-search-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search"
                           id="convSearchInput"
                           placeholder="Rechercher nom, contact, message..."
                           value="{{searchQuery|default('')}}"
                           autocomplete="off"
                           aria-label="Rechercher dans les discussions">
                    <button type="button"
                            id="convSearchClear"
                            class="livechat-search-clear {% if not searchQuery %}is-hidden{% endif %}"
                            title="Effacer la recherche"
                            onclick="clearConversationSearch()">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="livechat-filter-tabs" role="tablist" aria-label="Filtrer les discussions">
                    <button type="button"
                            class="livechat-filter-tab {% if filterStatus == 'all' or not filterStatus %}is-active{% endif %}"
                            data-status="all"
                            onclick="setStatusFilter('all')">
                        Toutes <span class="tab-count" id="tabCountAll">{{stats.all|default(0)}}</span>
                    </button>
                    <button type="button"
                            class="livechat-filter-tab {% if filterStatus == 'unread' %}is-active{% endif %}"
                            data-status="unread"
                            onclick="setStatusFilter('unread')">
                        Non lues <span class="tab-count" id="tabCountUnread">{{stats.unread|default(0)}}</span>
                    </button>
                    <button type="button"
                            class="livechat-filter-tab {% if filterStatus == 'open' %}is-active{% endif %}"
                            data-status="open"
                            onclick="setStatusFilter('open')">
                        Ouvertes <span class="tab-count" id="tabCountOpen">{{stats.open|default(0)}}</span>
                    </button>
                    <button type="button"
                            class="livechat-filter-tab {% if filterStatus == 'closed' %}is-active{% endif %}"
                            data-status="closed"
                            onclick="setStatusFilter('closed')">
                        Clôturées <span class="tab-count" id="tabCountClosed">{{stats.closed|default(0)}}</span>
                    </button>
                </div>
            </div>

            <div class="livechat-conv-list" id="convListContainer">
                {% if conversations %}
                    {% for conv in conversations %}
                    <a href="/admin/livechat?conv={{conv.id}}{% if filterStatus and filterStatus != 'all' %}&status={{filterStatus}}{% endif %}{% if searchQueryEncoded %}&q={{searchQueryEncoded}}{% endif %}"
                       class="livechat-conv-item {% if conv.id == activeConvId %}active{% endif %} {% if conv.unread_admin > 0 %}has-unread{% endif %} {% if conv.status == 'closed' %}is-closed{% endif %}"
                       data-conv-id="{{conv.id}}">
                        <div class="conv-avatar">
                            <i class="fa-solid fa-user"></i>
                            {% if conv.unread_admin > 0 %}
                            <span class="conv-unread-dot">{{conv.unread_admin}}</span>
                            {% endif %}
                        </div>
                        <div class="conv-meta">
                            <div class="conv-top-line">
                                <span class="conv-name">{{conv.visitor_name|default('Visiteur')}}</span>
                                <span class="conv-time">{{conv.updated_at|date('d/m H:i')}}</span>
                            </div>
                            <div class="conv-preview-row">
                                <div class="conv-preview">
                                    {{conv.last_message|default('Nouvelle discussion...')|slice(0, 60)}}
                                </div>
                                {% if conv.status == 'closed' %}
                                <span class="conv-status-pill">Clôturée</span>
                                {% endif %}
                            </div>
                            {% if conv.visitor_contact %}
                            <div class="conv-contact-badge">
                                <i class="fa-solid fa-address-book"></i> {{conv.visitor_contact}}
                            </div>
                            {% endif %}
                        </div>
                    </a>
                    {% endfor %}
                {% else %}
                    <div class="livechat-empty-sidebar">
                        <i class="fa-solid fa-inbox"></i>
                        <p>Aucune discussion trouvée.<br>Modifiez votre filtre ou attendez de nouveaux messages.</p>
                    </div>
                {% endif %}
            </div>

            <div class="livechat-sidebar-footer" id="sidebarFooter">
                <span class="sidebar-count-summary" id="convShowingSummary">
                    {{loadedCount|default(0)}} / {{totalMatching|default(0)}} affichée(s)
                </span>
                <button type="button"
                        id="btnLoadMoreConvs"
                        class="btn-load-more-convs {% if not hasMore %}is-hidden{% endif %}"
                        onclick="loadMoreConversations()">
                    <i class="fa-solid fa-angles-down"></i> Charger plus
                </button>
            </div>
        </aside>

        <!-- Colonne Droite : Fil de discussion actif -->
        <section class="livechat-chatbox" id="livechatChatbox">
            {% if activeConversation %}
            <!-- En-tête de la discussion -->
            <header class="chatbox-header">
                <button type="button" class="chatbox-back-btn" onclick="showSidebarOnMobile()">
                    <i class="fa-solid fa-arrow-left"></i> <span>Discussions</span>
                </button>
                <div class="chatbox-user-info">
                    <div class="chatbox-user-avatar">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div class="chatbox-user-text">
                        <h2 class="chatbox-username">{{activeConversation.visitor_name|default('Visiteur')}}</h2>
                        {% if activeConversation.visitor_contact %}
                        <div class="chatbox-contact-links">
                            <span>Contact :</span>
                            <strong>{{activeConversation.visitor_contact}}</strong>
                        </div>
                        {% endif %}
                    </div>
                </div>
                <div class="chatbox-actions">
                    <button type="button" class="btn-toggle-status {% if activeConversation.status == 'closed' %}is-closed{% endif %}"
                            onclick="toggleConvStatus({{activeConversation.id}})">
                        <i class="fa-solid {% if activeConversation.status == 'closed' %}fa-box-archive{% else %}fa-check{% endif %}"></i>
                        <span>{% if activeConversation.status == 'closed' %}Rouvrir{% else %}Clôturer{% endif %}</span>
                    </button>
                </div>
            </header>

            <!-- Corps des messages -->
            <div class="chatbox-messages" id="chatboxMessages">
                {% if activeMessages %}
                    {% for msg in activeMessages %}
                    <div class="chat-msg-row {% if msg.sender == 'admin' %}msg-admin{% else %}msg-visitor{% endif %}" data-msg-id="{{msg.id}}">
                        <div class="chat-bubble">
                            <div class="bubble-sender">{{msg.sender_name|default(msg.sender == 'admin' ? 'Moi' : 'Visiteur')}}</div>
                            <div class="bubble-text">{{msg.message|nl2br}}</div>
                            <div class="bubble-time">{{msg.created_at|date('d/m H:i')}}</div>
                        </div>
                    </div>
                    {% endfor %}
                {% else %}
                    <div class="chatbox-placeholder">
                        <i class="fa-solid fa-comments"></i>
                        <p>Aucun message dans cette conversation.</p>
                    </div>
                {% endif %}
            </div>

            <!-- Barre de saisie de réponse -->
            <footer class="chatbox-footer">
                <form id="adminReplyForm" onsubmit="sendAdminReply(event)">
                    <input type="hidden" name="_csrf" id="livechatCsrf" value="{{CSRF_TOKEN}}">
                    <input type="hidden" id="replyConvId" value="{{activeConversation.id}}">
                    <div class="reply-input-wrap">
                        <textarea id="replyMessage"
                                  placeholder="Répondre à {{activeConversation.visitor_name|default('cet interlocuteur')}}... (Entrée pour envoyer)"
                                  rows="2"
                                  required
                                  onkeydown="handleReplyKeydown(event)"></textarea>
                        <button type="submit" id="btnSendReply" class="btn-send-reply">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span>Envoyer</span>
                        </button>
                    </div>
                </form>
            </footer>
            {% else %}
            <div class="livechat-no-active-conv">
                <div class="no-conv-icon">
                    <i class="fa-regular fa-comment-dots"></i>
                </div>
                <h2>Sélectionnez une discussion</h2>
                <p>Choisissez une conversation dans la liste de gauche pour échanger en direct avec le commerçant ou l'internaute.</p>
            </div>
            {% endif %}
        </section>
    </div>
</div>
        </li>

        <!-- ONGLET 2 : CONFIGURATION & APPARENCE -->
        <li class="tab" style="padding: 24px;">
            <div class="livechat-config-view">
                <form id="liveChatConfigForm" onsubmit="saveLiveChatConfig(event)">
                    <input type="hidden" name="_csrf" value="{{CSRF_TOKEN}}">

                    <div class="lc-config-grid">
                        <!-- Carte Notifications & Expéditeur -->
                        <div class="lc-config-card">
                            <div class="lc-config-card-header">
                                <i class="fa-brands fa-discord"></i>
                                <div>
                                    <h3>Notifications Discord & Expéditeur</h3>
                                    <p>Alertes en temps réel et signature des messages.</p>
                                </div>
                            </div>
                            <div class="lc-config-card-body">
                                <div class="form-group-chat">
                                    <label for="cfg_webhook"><strong>URL de Webhook Discord</strong></label>
                                    <input type="url" id="cfg_webhook" name="discord_webhook" value="{{config.discord_webhook}}"
                                           placeholder="https://discord.com/api/webhooks/..." class="chat-input-full">
                                    <small>Dès qu'un internaute pose une question, une alerte détaillée avec le message et un lien direct pour répondre sur smartphone est envoyée sur ce salon Discord.</small>
                                </div>

                                <div class="form-group-chat" style="margin-top: 14px;">
                                    <label for="cfg_name"><strong>Nom de l'administrateur / Expéditeur</strong></label>
                                    <input type="text" id="cfg_name" name="admin_name" value="{{config.admin_name|default('Équipe Déclic')}}"
                                           class="chat-input-full">
                                    <small>Ce nom apparaît sous vos bulles de réponse pour rassurer l'internaute.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Carte Message de bienvenue -->
                        <div class="lc-config-card">
                            <div class="lc-config-card-header">
                                <i class="fa-solid fa-comment-dots"></i>
                                <div>
                                    <h3>Message d'accueil automatique</h3>
                                    <p>Premier contact chaleureux dès l'ouverture du widget.</p>
                                </div>
                            </div>
                            <div class="lc-config-card-body">
                                <div class="form-group-chat">
                                    <label for="cfg_welcome"><strong>Texte de bienvenue</strong></label>
                                    <textarea id="cfg_welcome" name="welcome_message" rows="5" class="chat-input-full" placeholder="Bonjour ! Comment pouvons-nous vous aider aujourd'hui ?">{{config.welcome_message}}</textarea>
                                    <small>Ce message s'affiche immédiatement au démarrage du tchat chez le visiteur pour l'inviter à échanger.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Carte Apparence 2D Color Picker -->
                        <div class="lc-config-card lc-card-wide">
                            <div class="lc-config-card-header">
                                <i class="fa-solid fa-palette"></i>
                                <div>
                                    <h3>Couleur de la bulle (Palette visuelle 2D & Nuancier)</h3>
                                    <p>Personnalisez la couleur d'accentuation de la bulle publique et de votre panneau d'administration.</p>
                                </div>
                            </div>
                            <div class="lc-config-card-body">
                                <div class="lc-picker-container">
                                    <!-- Grand carré de saturation & luminosité -->
                                    <div class="lc-sat-val-box" id="lcSatValBox">
                                        <div class="lc-sat-white"></div>
                                        <div class="lc-val-black"></div>
                                        <div class="lc-picker-handle" id="lcSatValHandle"></div>
                                    </div>

                                    <!-- Glissière Teinte Arc-en-ciel (Hue) -->
                                    <div class="lc-hue-slider-wrap">
                                        <input type="range" min="0" max="360" value="210" class="lc-hue-slider" id="lcHueSlider" oninput="onHueSliderChange(this.value)">
                                    </div>

                                    <!-- Barre inférieure : Aperçu, Code Hexadécimal & Nuances rapides -->
                                    <div class="lc-picker-bottom-bar">
                                        <div class="lc-preview-box" id="lcColorPreview" title="Aperçu en direct"></div>
                                        <div class="lc-hex-field-wrap">
                                            <span class="lc-hash">#</span>
                                            <input type="text" id="cfg_bubble_color_hex" value="{{config.bubble_color|default('#1f62a9')|replace({'#': ''})}}" class="lc-hex-input" maxlength="6" oninput="onManualHexChange(this.value)">
                                        </div>
                                        <input type="hidden" id="cfg_bubble_color" name="bubble_color" value="{{config.bubble_color|default('#1f62a9')}}">

                                        <div class="lc-swatches-row">
                                            <button type="button" class="lc-swatch" style="background:#1f62a9" title="Bleu Déclic (#1f62a9)" onclick="applyColor('#1f62a9')"></button>
                                            <button type="button" class="lc-swatch" style="background:#ea580c" title="Orange Bourges (#ea580c)" onclick="applyColor('#ea580c')"></button>
                                            <button type="button" class="lc-swatch" style="background:#059669" title="Vert Émeraude (#059669)" onclick="applyColor('#059669')"></button>
                                            <button type="button" class="lc-swatch" style="background:#7c3aed" title="Violet Électrique (#7c3aed)" onclick="applyColor('#7c3aed')"></button>
                                            <button type="button" class="lc-swatch" style="background:#e11d48" title="Rose Framboise (#e11d48)" onclick="applyColor('#e11d48')"></button>
                                            <button type="button" class="lc-swatch" style="background:#0f172a" title="Nuit Profonde (#0f172a)" onclick="applyColor('#0f172a')"></button>
                                        </div>
                                    </div>
                                </div>
                                <small style="display:block; margin-top:10px; color:#64748b;">
                                    <i class="fa-solid fa-circle-info"></i> Glissez ou cliquez dans le grand carré pour ajuster précisément la nuance et la clarté. L'interface s'ajuste en direct.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="lc-config-actions-bar">
                        <button type="submit" id="btnSaveConfig" class="button">
                            <i class="fa-solid fa-floppy-disk"></i> Enregistrer les paramètres
                        </button>
                        <div id="cfgFeedback" style="display:none; padding:8px 16px; border-radius:8px; font-weight:600;"></div>
                    </div>
                </form>
            </div>
        </li>
    </ul>
</div>

<style>
/* --- STYLES DU TABLEAU DE BORD LIVECHAT ADMIN --- */
.livechat-admin-container {
    --lc-accent: {% if config.bubble_color %}{{config.bubble_color}}{% else %}var(--primary-color, #1f62a9){% endif %};
    --lc-accent-dark: {% if config.bubble_color %}{{config.bubble_color}}{% else %}var(--primary-hover-color, #164980){% endif %};
    --lc-accent-soft: rgba(31, 98, 169, 0.08);
    --lc-accent-border: rgba(31, 98, 169, 0.25);
    max-width: 1400px;
    margin: 0 auto;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}
.lc-picker-container {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 14px;
    padding: 14px;
    margin-top: 8px;
    user-select: none;
}
.lc-sat-val-box {
    position: relative;
    width: 100%;
    height: 160px;
    border-radius: 10px;
    cursor: crosshair;
    overflow: hidden;
    background-color: #2563eb;
    box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.08);
}
.lc-sat-white {
    position: absolute;
    inset: 0;
    background: linear-gradient(to right, #ffffff, rgba(255, 255, 255, 0));
}
.lc-val-black {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, #000000, rgba(0, 0, 0, 0));
}
.lc-picker-handle {
    position: absolute;
    width: 18px;
    height: 18px;
    border: 2.5px solid #ffffff;
    border-radius: 50%;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.65), inset 0 0 2px rgba(0, 0, 0, 0.4);
    pointer-events: none;
    transform: translate(-50%, -50%);
    top: 50%;
    left: 50%;
}
.lc-hue-slider-wrap {
    margin-top: 12px;
}
.lc-hue-slider {
    -webkit-appearance: none;
    appearance: none;
    width: 100%;
    height: 14px;
    border-radius: 7px;
    background: linear-gradient(to right, #f00 0%, #ff0 17%, #0f0 33%, #0ff 50%, #00f 67%, #f0f 83%, #f00 100%);
    outline: none;
    cursor: pointer;
    box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.15);
}
.lc-hue-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #ffffff;
    border: 2.5px solid #0f172a;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.35);
    cursor: pointer;
}
.lc-hue-slider::-moz-range-thumb {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #ffffff;
    border: 2.5px solid #0f172a;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.35);
    cursor: pointer;
}
.lc-picker-bottom-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 12px;
    flex-wrap: wrap;
}
.lc-preview-box {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    border: 2px solid #ffffff;
    box-shadow: 0 0 0 1px #cbd5e1;
    background: #1f62a9;
    flex-shrink: 0;
}
.lc-hex-field-wrap {
    display: flex;
    align-items: center;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    padding: 0 8px;
    height: 38px;
}
.lc-hex-field-wrap:focus-within {
    border-color: var(--lc-accent, #1f62a9);
    box-shadow: 0 0 0 3px rgba(31, 98, 169, 0.12);
}
.lc-hash {
    color: #94a3b8;
    font-weight: 700;
    font-size: 1rem;
    margin-right: 4px;
}
.lc-hex-input {
    border: none;
    outline: none;
    font-family: monospace;
    font-size: 0.95rem;
    font-weight: 700;
    width: 75px;
    color: #0f172a;
    background: transparent;
    text-transform: uppercase;
}
.lc-swatches-row {
    display: flex;
    gap: 7px;
    align-items: center;
    margin-left: auto;
}
.lc-swatch {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    border: 2px solid #ffffff;
    box-shadow: 0 0 0 1px #cbd5e1;
    cursor: pointer;
    transition: transform 0.15s, box-shadow 0.15s;
    padding: 0;
}
.lc-swatch:hover {
    transform: scale(1.18);
    box-shadow: 0 0 0 2px var(--lc-accent, #1f62a9);
}
.livechat-admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    flex-wrap: wrap;
    gap: 12px;
}
.livechat-title {
    font-size: 1.55rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.livechat-title i {
    color: var(--lc-accent);
}
.livechat-subtitle {
    font-size: 0.9rem;
    color: #64748b;
}
.livechat-btn-settings {
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    color: #334155;
    padding: 8px 16px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.88rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.15s ease;
}
.livechat-btn-settings:hover {
    background: #f8fafc;
    border-color: var(--lc-accent);
    color: var(--lc-accent);
}

/* Layout 2 colonnes avec contrainte stricte de hauteur pour scroller indépendamment */
.livechat-main-layout {
    display: grid;
    grid-template-columns: 370px minmax(0, 1fr);
    grid-template-rows: minmax(0, 1fr);
    gap: 0;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    height: calc(100vh - 185px);
    min-height: 540px;
    max-height: 840px;
    box-shadow: 0 4px 20px rgba(15, 23, 42, 0.05);
}

/* Colonne Gauche (Sidebar) */
.livechat-sidebar {
    min-height: 0;
    height: 100%;
    overflow: hidden;
    border-right: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    background: #f8fafc;
}
.livechat-sidebar-header {
    padding: 14px 16px 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    background: #ffffff;
    flex-shrink: 0;
}
.livechat-sidebar-title-row {
    display: flex;
    align-items: center;
    gap: 8px;
}
.livechat-sidebar-header h3 {
    margin: 0;
    font-size: 1.02rem;
    font-weight: 800;
    color: #0f172a;
}
.livechat-badge-count {
    background: #e2e8f0;
    color: #334155;
    padding: 2px 9px;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 700;
}
.livechat-unread-pill {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 0.74rem;
    font-weight: 700;
}
.livechat-sidebar-tools {
    padding: 8px 12px 10px;
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex-shrink: 0;
}
.livechat-search-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.livechat-search-wrap > i {
    position: absolute;
    left: 11px;
    color: #94a3b8;
    font-size: 0.85rem;
    pointer-events: none;
}
.livechat-search-wrap input {
    width: 100%;
    padding: 7px 30px 7px 32px;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    font-size: 0.86rem;
    background: #f8fafc;
    color: #0f172a;
    transition: border-color 0.15s, background 0.15s;
}
.livechat-search-wrap input:focus {
    outline: none;
    background: #ffffff;
    border-color: var(--lc-accent);
    box-shadow: 0 0 0 3px rgba(31, 98, 169, 0.12);
}
.livechat-search-clear {
    position: absolute;
    right: 6px;
    border: none;
    background: transparent;
    color: #64748b;
    cursor: pointer;
    padding: 4px 6px;
    border-radius: 6px;
    font-size: 0.85rem;
}
.livechat-search-clear:hover {
    color: #0f172a;
    background: #e2e8f0;
}
.livechat-filter-tabs {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 4px;
}
.livechat-filter-tab {
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #475569;
    border-radius: 8px;
    padding: 5px 4px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    white-space: nowrap;
    transition: all 0.15s ease;
}
.livechat-filter-tab:hover {
    background: #f1f5f9;
    color: #0f172a;
}
.livechat-filter-tab.is-active {
    background: var(--lc-accent);
    border-color: var(--lc-accent);
    color: #ffffff;
}
.livechat-filter-tab .tab-count {
    font-size: 0.7rem;
    opacity: 0.85;
    font-weight: 700;
}

.livechat-conv-list {
    flex: 1 1 0;
    min-height: 0;
    overflow-y: auto;
    overscroll-behavior: contain;
    padding: 8px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}
.livechat-conv-list::-webkit-scrollbar,
.chatbox-messages::-webkit-scrollbar {
    width: 7px;
}
.livechat-conv-list::-webkit-scrollbar-thumb,
.chatbox-messages::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 999px;
}
.livechat-conv-list::-webkit-scrollbar-thumb:hover,
.chatbox-messages::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.livechat-conv-item {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 10px 12px;
    border-radius: 11px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    text-decoration: none;
    color: inherit;
    transition: all 0.15s ease;
}
.livechat-conv-item:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}
.livechat-conv-item.active {
    background: var(--lc-accent-soft);
    border-color: var(--lc-accent-border);
    box-shadow: 0 2px 8px rgba(31, 98, 169, 0.12);
}
.livechat-conv-item.has-unread {
    border-left: 4px solid var(--lc-accent);
}
.livechat-conv-item.is-closed {
    opacity: 0.78;
}
.livechat-conv-item.is-closed.active {
    opacity: 1;
}
.conv-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #dbeafe;
    color: var(--lc-accent);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    position: relative;
    flex-shrink: 0;
}
.conv-unread-dot {
    position: absolute;
    top: -3px;
    right: -3px;
    background: #ef4444;
    color: #ffffff;
    border-radius: 50%;
    min-width: 18px;
    height: 18px;
    padding: 0 4px;
    font-size: 0.68rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #ffffff;
}
.conv-meta {
    flex: 1;
    min-width: 0;
}
.conv-top-line {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 6px;
    margin-bottom: 2px;
}
.conv-name {
    font-weight: 700;
    color: #0f172a;
    font-size: 0.9rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.conv-time {
    font-size: 0.72rem;
    color: #64748b;
    flex-shrink: 0;
}
.conv-preview-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
}
.conv-preview {
    font-size: 0.8rem;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 0;
    flex: 1;
}
.conv-status-pill {
    flex-shrink: 0;
    font-size: 0.66rem;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 999px;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
}
.conv-contact-badge {
    margin-top: 3px;
    font-size: 0.72rem;
    color: var(--lc-accent);
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.livechat-sidebar-footer {
    padding: 9px 12px;
    border-top: 1px solid #e2e8f0;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    flex-shrink: 0;
}
.sidebar-count-summary {
    font-size: 0.76rem;
    color: #64748b;
    font-weight: 600;
}
.btn-load-more-convs {
    background: var(--lc-accent-soft);
    color: var(--lc-accent);
    border: 1px solid var(--lc-accent-border);
    border-radius: 8px;
    padding: 5px 10px;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.btn-load-more-convs:hover {
    background: #dbeafe;
}
.btn-load-more-convs:disabled {
    opacity: 0.6;
    cursor: wait;
}

.livechat-empty-sidebar,
.chatbox-placeholder,
.livechat-no-active-conv {
    margin: auto;
    text-align: center;
    padding: 32px 20px;
    color: #64748b;
}
.livechat-empty-sidebar i,
.chatbox-placeholder i,
.no-conv-icon {
    font-size: 2.2rem;
    color: #94a3b8;
    margin-bottom: 10px;
    display: block;
}
.livechat-no-active-conv h2 {
    margin: 0 0 8px;
    font-size: 1.2rem;
    color: #1e293b;
}
.livechat-no-active-conv p,
.livechat-empty-sidebar p {
    margin: 0;
    font-size: 0.9rem;
    line-height: 1.5;
}

/* Colonne Droite (Chatbox) */
.livechat-chatbox {
    min-height: 0;
    height: 100%;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    background: #ffffff;
}
.chatbox-header {
    padding: 13px 18px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    background: #ffffff;
    flex-shrink: 0;
}
.chatbox-back-btn {
    display: none;
    background: var(--lc-accent-soft);
    border: 1px solid var(--lc-accent-border);
    border-radius: 8px;
    color: var(--lc-accent);
    font-weight: 700;
    font-size: 0.86rem;
    cursor: pointer;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    flex-shrink: 0;
}
.chatbox-user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    flex: 1;
}
.chatbox-user-text {
    min-width: 0;
}
.chatbox-user-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #dbeafe;
    color: var(--lc-accent);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}
.chatbox-username {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 800;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.chatbox-contact-links {
    font-size: 0.82rem;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.chatbox-contact-links strong {
    color: #0f172a;
}
.btn-toggle-status {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #334155;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}
.btn-toggle-status.is-closed {
    background: #fef2f2;
    border-color: #fecaca;
    color: #b91c1c;
}

/* Messages */
.chatbox-messages {
    flex: 1 1 0;
    min-height: 0;
    overflow-y: auto;
    overscroll-behavior: contain;
    padding: 18px 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    background: #fdfefe;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}
.chat-msg-row {
    display: flex;
    width: 100%;
    flex-shrink: 0;
}
.chat-msg-row.msg-visitor {
    justify-content: flex-start;
}
.chat-msg-row.msg-admin {
    justify-content: flex-end;
}
.chat-bubble {
    max-width: 75%;
    padding: 10px 14px;
    border-radius: 14px;
    position: relative;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
.msg-visitor .chat-bubble {
    background: #f1f5f9;
    color: #0f172a;
    border-bottom-left-radius: 3px;
    border: 1px solid #e2e8f0;
}
.msg-admin .chat-bubble {
    background: linear-gradient(135deg, var(--lc-accent) 0%, var(--lc-accent-dark) 100%);
    color: #ffffff;
    border-bottom-right-radius: 3px;
}
.bubble-sender {
    font-size: 0.72rem;
    font-weight: 700;
    margin-bottom: 2px;
    opacity: 0.82;
}
.bubble-text {
    font-size: 0.94rem;
    line-height: 1.45;
    word-break: break-word;
}
.bubble-time {
    font-size: 0.68rem;
    text-align: right;
    margin-top: 4px;
    opacity: 0.75;
}

/* Footer Saisie */
.chatbox-footer {
    padding: 12px 18px;
    border-top: 1px solid #e2e8f0;
    background: #ffffff;
    flex-shrink: 0;
}
.reply-input-wrap {
    display: flex;
    gap: 10px;
    align-items: flex-end;
}
.reply-input-wrap textarea {
    flex: 1;
    border: 1.5px solid #cbd5e1;
    border-radius: 12px;
    padding: 10px 14px;
    font-size: 0.95rem;
    font-family: inherit;
    resize: none;
    box-sizing: border-box;
    transition: border-color 0.2s;
}
.reply-input-wrap textarea:focus {
    outline: none;
    border-color: var(--lc-accent);
    box-shadow: 0 0 0 3px rgba(31, 98, 169, 0.15);
}
.btn-send-reply {
    background: linear-gradient(135deg, var(--lc-accent) 0%, var(--lc-accent-dark) 100%);
    color: #ffffff;
    border: none;
    border-radius: 12px;
    padding: 12px 20px;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 2px 8px rgba(31, 98, 169, 0.25);
    transition: transform 0.15s ease;
    height: 48px;
}
.btn-send-reply:hover {
    transform: translateY(-1px);
}

/* Configuration Page View (Tabs) */
.livechat-config-view {
    max-width: 1000px;
    margin: 0 auto;
}
.lc-config-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}
.lc-config-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
}
.lc-card-wide {
    grid-column: 1 / -1;
}
.lc-config-card-header {
    padding: 14px 18px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 12px;
}
.lc-config-card-header i {
    font-size: 1.25rem;
    color: var(--lc-accent, #1f62a9);
}
.lc-config-card-header h3 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
}
.lc-config-card-header p {
    margin: 2px 0 0;
    font-size: 0.8rem;
    color: #64748b;
}
.lc-config-card-body {
    padding: 18px;
}
.lc-config-actions-bar {
    display: flex;
    align-items: center;
    gap: 16px;
    padding-top: 10px;
}
.lc-config-actions-bar .button {
    background: var(--lc-accent, #1f62a9);
    color: #ffffff;
    border: none;
    padding: 10px 22px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 2px 8px rgba(31, 98, 169, 0.25);
    transition: transform 0.15s ease, opacity 0.15s ease;
}
.lc-config-actions-bar .button:hover {
    transform: translateY(-1px);
    opacity: 0.95;
}
.is-hidden {
    display: none;
}

/* RESPONSIVE MOBILE */
@media (max-width: 850px) {
    .livechat-main-layout {
        grid-template-columns: 1fr;
        height: calc(100vh - 165px);
        min-height: 460px;
        max-height: none;
    }
    .livechat-main-layout:not(.mobile-show-list) .livechat-sidebar {
        display: none;
    }
    .livechat-main-layout:not(.mobile-show-list) .chatbox-back-btn {
        display: inline-flex;
    }
    .livechat-main-layout.mobile-show-list .livechat-chatbox {
        display: none;
    }
}
</style>

<script>
const mainLayout = document.getElementById('livechatMainLayout');
const activeConvId = parseInt(mainLayout ? mainLayout.dataset.activeConv : '0', 10) || 0;
let lastMessageId = 0;
let currentFilterStatus = (mainLayout && mainLayout.dataset.filterStatus) || 'all';
let currentLimit = parseInt((mainLayout && mainLayout.dataset.pageLimit) || '50', 10) || 50;
let loadedCount = document.querySelectorAll('#convListContainer .livechat-conv-item').length;
let totalMatching = parseInt((mainLayout && mainLayout.dataset.totalMatching) || '0', 10) || loadedCount;
let searchDebounceTimer = null;

document.addEventListener('DOMContentLoaded', () => {
    updateLastMessageId();
    scrollMessagesToBottom();

    const searchInput = document.getElementById('convSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const clearBtn = document.getElementById('convSearchClear');
            if (clearBtn) {
                clearBtn.classList.toggle('is-hidden', searchInput.value.trim() === '');
            }
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(() => {
                fetchConversationList(false);
            }, 250);
        });
    }

    setInterval(pollAdminMessages, 3500);
});

function getCurrentSearch() {
    const input = document.getElementById('convSearchInput');
    return input ? input.value.trim() : '';
}

function setStatusFilter(status) {
    if (currentFilterStatus === status) return;
    currentFilterStatus = status;
    currentLimit = 50;

    document.querySelectorAll('.livechat-filter-tab').forEach(btn => {
        btn.classList.toggle('is-active', btn.dataset.status === status);
    });

    fetchConversationList(false);
}

function clearConversationSearch() {
    const input = document.getElementById('convSearchInput');
    const clearBtn = document.getElementById('convSearchClear');
    if (input) {
        input.value = '';
        input.focus();
    }
    if (clearBtn) clearBtn.classList.add('is-hidden');
    fetchConversationList(false);
}

function loadMoreConversations() {
    const btn = document.getElementById('btnLoadMoreConvs');
    if (btn) btn.disabled = true;
    fetchConversationList(true).finally(() => {
        if (btn) btn.disabled = false;
    });
}

function fetchConversationList(append) {
    const offset = append ? loadedCount : 0;
    const pageSize = 50;
    const q = getCurrentSearch();
    const params = new URLSearchParams({
        status: currentFilterStatus,
        q: q,
        limit: String(pageSize),
        offset: String(offset),
    });

    return fetch(`/admin/livechat/conversations?${params.toString()}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            if (append) {
                appendConversationItems(data.conversations || []);
                currentLimit = Math.max(currentLimit, loadedCount);
            } else {
                renderConversationList(data.conversations || [], false);
                currentLimit = pageSize;
            }
            totalMatching = parseInt(data.total || 0, 10);
            updateSidebarStats(data.stats || null);
            updateSidebarFooter(Boolean(data.has_more));
        })
        .catch(() => {});
}

function buildConversationHref(convId) {
    const params = new URLSearchParams({ conv: String(convId) });
    if (currentFilterStatus && currentFilterStatus !== 'all') {
        params.set('status', currentFilterStatus);
    }
    const q = getCurrentSearch();
    if (q) {
        params.set('q', q);
    }
    return `/admin/livechat?${params.toString()}`;
}

function formatConvDate(dateStr) {
    if (!dateStr) return '';
    const cleaned = String(dateStr).replace(' ', 'T');
    const match = cleaned.match(/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})/);
    if (match) {
        return `${match[3]}/${match[2]} ${match[4]}:${match[5]}`;
    }
    return String(dateStr).substring(11, 16);
}

function createConversationElement(conv) {
    const a = document.createElement('a');
    const unread = parseInt(conv.unread_admin || 0, 10);
    const isClosed = conv.status === 'closed';
    const isActive = parseInt(conv.id, 10) === activeConvId;

    a.href = buildConversationHref(conv.id);
    a.className = 'livechat-conv-item'
        + (isActive ? ' active' : '')
        + (unread > 0 ? ' has-unread' : '')
        + (isClosed ? ' is-closed' : '');
    a.setAttribute('data-conv-id', conv.id);

    const previewRaw = conv.last_message ? String(conv.last_message) : 'Nouvelle discussion...';
    const preview = previewRaw.length > 60 ? previewRaw.slice(0, 60) : previewRaw;
    const contactHtml = conv.visitor_contact
        ? `<div class="conv-contact-badge"><i class="fa-solid fa-address-book"></i> ${escapeHtml(String(conv.visitor_contact))}</div>`
        : '';
    const unreadHtml = unread > 0 ? `<span class="conv-unread-dot">${unread}</span>` : '';
    const statusPillHtml = isClosed ? '<span class="conv-status-pill">Clôturée</span>' : '';

    a.innerHTML = `
        <div class="conv-avatar">
            <i class="fa-solid fa-user"></i>
            ${unreadHtml}
        </div>
        <div class="conv-meta">
            <div class="conv-top-line">
                <span class="conv-name">${escapeHtml(conv.visitor_name || 'Visiteur')}</span>
                <span class="conv-time">${escapeHtml(formatConvDate(conv.updated_at))}</span>
            </div>
            <div class="conv-preview-row">
                <div class="conv-preview">${escapeHtml(preview)}</div>
                ${statusPillHtml}
            </div>
            ${contactHtml}
        </div>
    `;
    return a;
}

function renderConversationList(conversations, preserveScroll) {
    const container = document.getElementById('convListContainer');
    if (!container) return;
    const prevScroll = preserveScroll ? container.scrollTop : 0;

    container.innerHTML = '';
    if (!conversations || conversations.length === 0) {
        container.innerHTML = `
            <div class="livechat-empty-sidebar">
                <i class="fa-solid fa-inbox"></i>
                <p>Aucune discussion trouvée.<br>Modifiez votre filtre ou attendez de nouveaux messages.</p>
            </div>
        `;
        loadedCount = 0;
    } else {
        conversations.forEach(conv => container.appendChild(createConversationElement(conv)));
        loadedCount = conversations.length;
    }

    if (preserveScroll) {
        container.scrollTop = prevScroll;
    } else {
        container.scrollTop = 0;
    }
}

function appendConversationItems(conversations) {
    const container = document.getElementById('convListContainer');
    if (!container || !conversations.length) return;

    conversations.forEach(conv => {
        if (!container.querySelector(`.livechat-conv-item[data-conv-id="${conv.id}"]`)) {
            container.appendChild(createConversationElement(conv));
        }
    });
    loadedCount = container.querySelectorAll('.livechat-conv-item').length;
}

function updateSidebarStats(stats) {
    if (!stats) return;
    const setTxt = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = String(val ?? 0);
    };
    setTxt('convTotalBadge', stats.all);
    setTxt('tabCountAll', stats.all);
    setTxt('tabCountUnread', stats.unread);
    setTxt('tabCountOpen', stats.open);
    setTxt('tabCountClosed', stats.closed);
    setTxt('convUnreadCount', stats.unread);

    const unreadPill = document.getElementById('convUnreadPill');
    if (unreadPill) {
        unreadPill.classList.toggle('is-hidden', !(stats.unread > 0));
    }
}

function updateSidebarFooter(hasMore) {
    const summary = document.getElementById('convShowingSummary');
    if (summary) {
        summary.textContent = `${loadedCount} / ${totalMatching} affichée(s)`;
    }
    const btn = document.getElementById('btnLoadMoreConvs');
    if (btn) {
        btn.classList.toggle('is-hidden', !hasMore);
    }
}

function updateLastMessageId() {
    const rows = document.querySelectorAll('.chat-msg-row');
    if (rows.length > 0) {
        const last = rows[rows.length - 1];
        lastMessageId = parseInt(last.getAttribute('data-msg-id') || 0, 10);
    }
}

function scrollMessagesToBottom() {
    const container = document.getElementById('chatboxMessages');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}

function showSidebarOnMobile() {
    if (mainLayout) {
        mainLayout.classList.add('mobile-show-list');
        if (window.history && window.history.replaceState) {
            const url = new URL(window.location.href);
            url.searchParams.set('list', '1');
            window.history.replaceState({}, '', url.toString());
        }
    } else {
        window.location.href = '/admin/livechat?list=1';
    }
}

function handleReplyKeydown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('adminReplyForm').requestSubmit();
    }
}

function sendAdminReply(e) {
    e.preventDefault();
    const textarea = document.getElementById('replyMessage');
    const msg = textarea.value.trim();
    if (!msg) return;

    const btn = document.getElementById('btnSendReply');
    btn.disabled = true;

    const formData = new FormData();
    formData.append('conversation_id', activeConvId);
    formData.append('message', msg);
    const csrfEl = document.getElementById('livechatCsrf');
    if (csrfEl && csrfEl.value) {
        formData.append('_csrf', csrfEl.value);
    }

    fetch('/admin/livechat/reply', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': (csrfEl && csrfEl.value) ? csrfEl.value : ''
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.message) {
            appendMessageToChat(data.message, true);
            textarea.value = '';
            scrollMessagesToBottom();
            pollAdminMessages();
        } else {
            alert(data.error || 'Erreur lors de l\'envoi.');
        }
    })
    .catch(() => alert('Erreur réseau lors de l\'envoi'))
    .finally(() => {
        btn.disabled = false;
        textarea.focus();
    });
}

function pollAdminMessages() {
    const params = new URLSearchParams({
        conversation_id: String(activeConvId),
        after_id: String(lastMessageId),
        status: currentFilterStatus,
        q: getCurrentSearch(),
        limit: String(Math.max(50, loadedCount, currentLimit)),
    });

    fetch(`/admin/livechat/poll?${params.toString()}`)
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        if (data.messages && data.messages.length > 0) {
            data.messages.forEach(msg => {
                appendMessageToChat(msg, false);
            });
            scrollMessagesToBottom();
        }
        if (Array.isArray(data.conversations)) {
            renderConversationList(data.conversations, true);
            totalMatching = parseInt(data.total || 0, 10);
            updateSidebarStats(data.stats || null);
            updateSidebarFooter(Boolean(data.has_more));
        }
    })
    .catch(() => {});
}

function appendMessageToChat(msg, isMine) {
    const container = document.getElementById('chatboxMessages');
    if (!container) return;

    if (document.querySelector(`.chat-msg-row[data-msg-id="${msg.id}"]`)) {
        return;
    }

    const row = document.createElement('div');
    row.className = `chat-msg-row ${msg.sender === 'admin' ? 'msg-admin' : 'msg-visitor'}`;
    row.setAttribute('data-msg-id', msg.id);

    const timeStr = formatConvDate(msg.created_at);
    const senderTitle = msg.sender_name || (msg.sender === 'admin' ? 'Moi' : 'Visiteur');

    row.innerHTML = `
        <div class="chat-bubble">
            <div class="bubble-sender">${escapeHtml(senderTitle)}</div>
            <div class="bubble-text">${escapeHtml(msg.message).replace(/\n/g, '<br>')}</div>
            <div class="bubble-time">${escapeHtml(timeStr)}</div>
        </div>
    `;

    container.appendChild(row);
    lastMessageId = Math.max(lastMessageId, parseInt(msg.id, 10));
}

function toggleConvStatus(convId) {
    const formData = new FormData();
    formData.append('conversation_id', convId);
    const csrfEl = document.getElementById('livechatCsrf');
    if (csrfEl && csrfEl.value) {
        formData.append('_csrf', csrfEl.value);
    }

    fetch('/admin/livechat/toggle-status', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': (csrfEl && csrfEl.value) ? csrfEl.value : ''
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        }
    });
}

// --- 2D COLOR PICKER ENGINE ---
let currentH = 210;
let currentS = 0.8;
let currentV = 0.65;
let isDraggingSquare = false;
let colorPickerInitialized = false;

function hsvToRgb(h, s, v) {
    let f = (n, k = (n + h / 60) % 6) => v - v * s * Math.max(Math.min(k, 4 - k, 1), 0);
    let r = Math.round(f(5) * 255);
    let g = Math.round(f(3) * 255);
    let b = Math.round(f(1) * 255);
    return { r, g, b };
}

function rgbToHex(r, g, b) {
    return '#' + [r, g, b].map(x => x.toString(16).padStart(2, '0')).join('');
}

function hexToHsv(hex) {
    let r = 0, g = 0, b = 0;
    hex = String(hex).replace('#', '').trim();
    if (hex.length === 3) {
        r = parseInt(hex[0] + hex[0], 16);
        g = parseInt(hex[1] + hex[1], 16);
        b = parseInt(hex[2] + hex[2], 16);
    } else if (hex.length >= 6) {
        r = parseInt(hex.substring(0, 2), 16);
        g = parseInt(hex.substring(2, 4), 16);
        b = parseInt(hex.substring(4, 6), 16);
    }
    if (isNaN(r) || isNaN(g) || isNaN(b)) {
        return { h: 210, s: 0.8, v: 0.65 };
    }
    r /= 255; g /= 255; b /= 255;
    let max = Math.max(r, g, b), min = Math.min(r, g, b);
    let d = max - min;
    let h = 0, s = max === 0 ? 0 : d / max, v = max;
    if (max !== min) {
        switch (max) {
            case r: h = (g - b) / d + (g < b ? 6 : 0); break;
            case g: h = (b - r) / d + 2; break;
            case b: h = (r - g) / d + 4; break;
        }
        h *= 60;
    }
    return { h: Math.round(h), s, v };
}

function applyColor(hex, updateInputs = true) {
    const hsv = hexToHsv(hex);
    currentH = hsv.h;
    currentS = hsv.s;
    currentV = hsv.v;
    updateFromHSV(updateInputs);
}

function onHueSliderChange(hue) {
    currentH = parseFloat(hue);
    updateFromHSV(true);
}

function onManualHexChange(hexVal) {
    let clean = hexVal.replace('#', '').trim();
    if (/^[0-9a-fA-F]{6}$/.test(clean)) {
        applyColor('#' + clean, false);
        const hiddenInput = document.getElementById('cfg_bubble_color');
        if (hiddenInput) hiddenInput.value = '#' + clean;
    }
}

function updateFromHSV(updateInputs = true) {
    const box = document.getElementById('lcSatValBox');
    const handle = document.getElementById('lcSatValHandle');
    const slider = document.getElementById('lcHueSlider');
    const preview = document.getElementById('lcColorPreview');
    const hexInput = document.getElementById('cfg_bubble_color_hex');
    const hiddenInput = document.getElementById('cfg_bubble_color');

    if (box) {
        box.style.backgroundColor = `hsl(${currentH}, 100%, 50%)`;
    }
    if (slider) {
        slider.value = currentH;
    }
    if (box && handle) {
        const x = currentS * box.offsetWidth;
        const y = (1 - currentV) * box.offsetHeight;
        handle.style.left = `${x}px`;
        handle.style.top = `${y}px`;
    }

    const { r, g, b } = hsvToRgb(currentH, currentS, currentV);
    const hex = rgbToHex(r, g, b);

    if (preview) {
        preview.style.backgroundColor = hex;
    }
    if (updateInputs) {
        if (hexInput) hexInput.value = hex.replace('#', '').toUpperCase();
        if (hiddenInput) hiddenInput.value = hex;
    }

    const container = document.querySelector('.livechat-admin-container');
    if (container) {
        container.style.setProperty('--lc-accent', hex);
        container.style.setProperty('--lc-accent-dark', hex);
    }
}

function initColorPicker() {
    const hidden = document.getElementById('cfg_bubble_color');
    const initialHex = hidden && hidden.value ? hidden.value : '#1f62a9';
    applyColor(initialHex, true);

    const box = document.getElementById('lcSatValBox');
    if (!box || colorPickerInitialized) return;
    colorPickerInitialized = true;

    function handleBoxDrag(e) {
        const rect = box.getBoundingClientRect();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        let x = Math.max(0, Math.min(rect.width, clientX - rect.left));
        let y = Math.max(0, Math.min(rect.height, clientY - rect.top));

        currentS = rect.width > 0 ? (x / rect.width) : 0;
        currentV = rect.height > 0 ? (1 - (y / rect.height)) : 0;
        updateFromHSV(true);
    }

    box.addEventListener('mousedown', function(e) {
        isDraggingSquare = true;
        handleBoxDrag(e);
    });

    window.addEventListener('mousemove', function(e) {
        if (isDraggingSquare) {
            handleBoxDrag(e);
        }
    });

    window.addEventListener('mouseup', function() {
        isDraggingSquare = false;
    });

    box.addEventListener('touchstart', function(e) {
        isDraggingSquare = true;
        handleBoxDrag(e);
    }, { passive: true });

    window.addEventListener('touchmove', function(e) {
        if (isDraggingSquare) {
            handleBoxDrag(e);
        }
    }, { passive: true });

    window.addEventListener('touchend', function() {
        isDraggingSquare = false;
    });
}

// Initialisation automatique du sélecteur de couleurs lors du basculement d'onglet
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(initColorPicker, 100);
    const tabsCont = document.querySelector('[data-tab-key="livechat"]');
    if (tabsCont) {
        tabsCont.addEventListener('adm:tab-changed', function(e) {
            if (e.detail && e.detail.tabName === 'config') {
                setTimeout(initColorPicker, 50);
            }
        });
    }
});

function saveLiveChatConfig(e) {
    e.preventDefault();
    const form = document.getElementById('liveChatConfigForm');
    const btn = document.getElementById('btnSaveConfig');
    const feedback = document.getElementById('cfgFeedback');
    const csrfEl = document.getElementById('livechatCsrf');

    btn.disabled = true;
    const formData = new FormData(form);

    fetch('/admin/livechat/config', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': (csrfEl && csrfEl.value) ? csrfEl.value : ''
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        feedback.style.display = 'block';
        if (data.success) {
            feedback.style.background = '#ecfdf5';
            feedback.style.color = '#065f46';
            feedback.innerHTML = '<i class="fa-solid fa-check"></i> Configuration enregistrée avec succès !';
            if (typeof Toastify === 'function') {
                Toastify({ text: 'Configuration LiveChat enregistrée !', duration: 3000, style: { background: 'var(--lc-accent, #1f62a9)' } }).showToast();
            }
        } else {
            feedback.style.background = '#fef2f2';
            feedback.style.color = '#991b1b';
            feedback.innerHTML = escapeHtml(data.error || 'Erreur lors de l\'enregistrement');
        }
    })
    .catch(() => {
        feedback.style.display = 'block';
        feedback.style.background = '#fef2f2';
        feedback.style.color = '#991b1b';
        feedback.innerHTML = 'Erreur réseau.';
    })
    .finally(() => btn.disabled = false);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text == null ? '' : String(text);
    return div.innerHTML;
}
</script>

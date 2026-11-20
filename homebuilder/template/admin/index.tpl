<section class="homebuilder-dashboard">
    <!-- En-tête avec barre d'actions -->
    <header class="hb-topbar">
        <div class="hb-title-group">
            <h2>
                <i class="fa-solid fa-wand-magic-sparkles" style="color: var(--evo-primary, #0ea5e9);"></i>
                Constructeur de Page d'Accueil
            </h2>
            <p class="hb-subtitle">
                Composez et organisez visuellement les sections de votre landing page par simple glisser-déposer.
            </p>
        </div>
        <div class="hb-actions-group">
            {% IF hasBlocks %}
            <span class="hb-stats-pill">
                <i class="fa-solid fa-circle-check" style="color: #10b981;"></i>
                <span id="hbActiveCount">{{ activeCount }}</span> / {{ totalBlocks }} sections actives
            </span>
            {% ENDIF %}
            <a href="{{ siteHomeUrl }}" target="_blank" class="button secondary hb-btn-preview" title="Ouvrir la page d'accueil dans un nouvel onglet">
                <i class="fa-regular fa-eye"></i> Voir le site
            </a>
            <button type="button" id="hbOpenCatalogBtn" class="button primary hb-btn-add">
                <i class="fa-solid fa-plus"></i> Ajouter une section
            </button>
        </div>
    </header>

    {% IF hasBlocks %}
        <!-- Canvas visuel des sections réordonnables -->
        <div id="sortable-blocks" class="hb-canvas-stack">
            {% FOR block IN blocks %}
            <div class="hb-section-card {% IF !block.isActive %}is-disabled{% ENDIF %}" data-id="{{ block.id }}" id="block-card-{{ block.id }}">
                <!-- Poignée de déplacement et numéro d'ordre -->
                <div class="hb-card-handle" title="Glisser pour réorganiser l'ordre">
                    <i class="fa-solid fa-grip-vertical"></i>
                    <span class="hb-order-badge">#<span class="order-num">{{ block.order }}</span></span>
                </div>

                <!-- Icône thématique du bloc -->
                <div class="hb-card-icon" style="background: {{ block.typeColor }}18; color: {{ block.typeColor }}; border-color: {{ block.typeColor }}35;">
                    <i class="{{ block.typeIcon }}"></i>
                </div>

                <!-- Informations principales et aperçu -->
                <div class="hb-card-content">
                    <div class="hb-card-meta">
                        <span class="hb-type-tag" style="background: {{ block.typeColor }}20; color: {{ block.typeColor }};">
                            {{ block.typeLabel }}
                        </span>
                        <span class="hb-category-muted">{{ block.typeCategory }}</span>
                    </div>
                    <h3 class="hb-card-title">{{ block.title }}</h3>
                    <p class="hb-card-preview">{{ block.contentPreview }}</p>
                </div>

                <!-- Interrupteur d'activation rapide On/Off -->
                <div class="hb-card-toggle">
                    <label class="hb-toggle-switch" title="Activer / Désactiver cette section en direct">
                        <input type="checkbox" class="hb-toggle-checkbox" data-url="{{ block.toggleUrl }}" data-id="{{ block.id }}" {% IF block.isActive %}checked{% ENDIF %}>
                        <span class="hb-toggle-slider"></span>
                    </label>
                    <span class="hb-toggle-status" id="status-label-{{ block.id }}">
                        {% IF block.isActive %}
                            <span class="status-text-on">Visible</span>
                        {% ELSE %}
                            <span class="status-text-off">Masqué</span>
                        {% ENDIF %}
                    </span>
                </div>

                <!-- Actions du bloc -->
                <div class="hb-card-actions">
                    <a href="{{ block.editUrl }}" class="button small" title="Modifier le contenu de ce bloc">
                        <i class="fa-solid fa-pen"></i> <span>Éditer</span>
                    </a>
                    <a href="{{ block.stylesUrl }}" class="button small secondary" title="Personnaliser les styles et couleurs">
                        <i class="fa-solid fa-palette"></i> <span>Design</span>
                    </a>
                    <form method="POST" action="{{ block.deleteUrl }}" style="display: inline; margin: 0;">
    {{SHOW.tokenField}}
                        <button type="submit" class="button small alert hb-btn-del" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette section ?')" title="Supprimer ce bloc">
                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    </form>
                </div>
            </div>
            {% ENDFOR %}
        </div>

        <div class="hb-footer-hint">
            <i class="fa-solid fa-arrows-up-down"></i>
            <span>Attrapez et glissez les sections pour changer leur ordre d'apparition sur la page d'accueil. Les changements sont enregistrés automatiquement.</span>
        </div>
    {% ELSE %}
        <!-- État vide invitant à la création -->
        <div class="hb-empty-state">
            <div class="hb-empty-icon">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <h3>Votre page d'accueil n'a pas encore de sections personnalisées</h3>
            <p>Commencez par ajouter votre première section (Bannière Hero, Articles de blog, Carte des commerces...) pour composer votre landing page.</p>
            <button type="button" class="button primary hb-btn-hero-add" onclick="document.getElementById('hbOpenCatalogBtn').click();">
                <i class="fa-solid fa-plus"></i> Choisir une première section
            </button>
        </div>
    {% ENDIF %}

    <!-- Modale Bibliothèque de Sections (Catalogue Visuel) -->
    <div id="hbCatalogModal" class="hb-modal-overlay" style="display: none;">
        <div class="hb-modal-dialog">
            <div class="hb-modal-header">
                <div class="hb-modal-title">
                    <i class="fa-solid fa-cubes" style="color: var(--evo-primary, #0ea5e9);"></i>
                    <h3>Choisir une nouvelle section à ajouter</h3>
                </div>
                <button type="button" id="hbCloseCatalogBtn" class="hb-modal-close" aria-label="Fermer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="hb-modal-body">
                <p class="hb-modal-desc">Sélectionnez le modèle de section souhaité pour votre page d'accueil :</p>
                <div class="hb-catalog-grid">
                    {% FOR key, meta IN blockMeta %}
                    <a href="{{ ROUTER.generate('admin-homebuilder-add') }}?type={{ key }}" class="hb-catalog-card">
                        <div class="hb-cat-icon" style="background: {{ meta.color }}18; color: {{ meta.color }}; border-color: {{ meta.color }}35;">
                            <i class="{{ meta.icon }}"></i>
                        </div>
                        <div class="hb-cat-info">
                            <div class="hb-cat-badge-wrap">
                                <span class="hb-cat-badge" style="background: {{ meta.color }}20; color: {{ meta.color }};">{{ meta.category }}</span>
                            </div>
                            <h4 class="hb-cat-title">{{ meta.label }}</h4>
                            <p class="hb-cat-desc">{{ meta.desc }}</p>
                        </div>
                        <div class="hb-cat-arrow">
                            <i class="fa-solid fa-chevron-right"></i>
                        </div>
                    </a>
                    {% ENDFOR %}
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Styles Scannés & Scannables pour le Builder -->
<style>
.homebuilder-dashboard {
    max-width: 1100px;
    margin: 0 auto;
    padding-bottom: 40px;
}

.hb-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    padding: 20px 24px;
    background: var(--evo-card-bg, #ffffff);
    border: 1px solid var(--evo-border, #e2e8f0);
    border-radius: var(--evo-radius, 12px);
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}

.hb-title-group h2 {
    margin: 0 0 6px;
    font-size: 1.45rem;
    font-weight: 700;
    color: var(--evo-text, #0f172a);
    display: flex;
    align-items: center;
    gap: 10px;
}

.hb-subtitle {
    margin: 0;
    font-size: 0.9rem;
    color: var(--evo-text-muted, #64748b);
}

.hb-actions-group {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.hb-stats-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--evo-surface-alt, #f8fafc);
    border: 1px solid var(--evo-border, #e2e8f0);
    padding: 8px 14px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--evo-text, #334155);
}

.hb-canvas-stack {
    display: flex;
    flex-direction: column;
    gap: 14px;
    margin-bottom: 20px;
}

.hb-section-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    background: var(--evo-card-bg, #ffffff);
    border: 1px solid var(--evo-border, #e2e8f0);
    border-radius: var(--evo-radius, 12px);
    box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease, opacity 0.2s ease;
}

.hb-section-card:hover {
    border-color: var(--evo-primary, #0ea5e9);
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
}

.hb-section-card.is-disabled {
    opacity: 0.65;
    background: var(--evo-surface-alt, #f8fafc);
}

.hb-card-handle {
    cursor: grab;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    color: var(--evo-text-muted, #94a3b8);
    padding: 8px;
    border-radius: 6px;
    user-select: none;
}

.hb-card-handle:active {
    cursor: grabbing;
}

.hb-order-badge {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--evo-text-muted, #64748b);
}

.hb-card-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    border: 1px solid transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}

.hb-card-content {
    flex: 1;
    min-width: 0;
}

.hb-card-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
}

.hb-type-tag {
    font-size: 0.75rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 6px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.hb-category-muted {
    font-size: 0.8rem;
    color: var(--evo-text-muted, #94a3b8);
}

.hb-card-title {
    margin: 0 0 4px;
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--evo-text, #0f172a);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.hb-card-preview {
    margin: 0;
    font-size: 0.85rem;
    color: var(--evo-text-muted, #64748b);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.hb-card-toggle {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 0 10px;
}

.hb-toggle-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    cursor: pointer;
}

.hb-toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.hb-toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: var(--evo-border, #cbd5e1);
    transition: 0.25s;
    border-radius: 24px;
}

.hb-toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.25s;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.hb-toggle-switch input:checked + .hb-toggle-slider {
    background-color: #10b981;
}

.hb-toggle-switch input:checked + .hb-toggle-slider:before {
    transform: translateX(20px);
}

.hb-toggle-status {
    font-size: 0.75rem;
    font-weight: 600;
}

.status-text-on { color: #10b981; }
.status-text-off { color: var(--evo-text-muted, #94a3b8); }

.hb-card-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.hb-card-actions .button {
    margin: 0;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
}

.hb-btn-del {
    padding: 6px 10px !important;
}

.hb-footer-hint {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    background: var(--evo-surface-alt, #f8fafc);
    border: 1px solid var(--evo-border, #e2e8f0);
    border-radius: var(--evo-radius, 10px);
    font-size: 0.85rem;
    color: var(--evo-text-muted, #64748b);
}

.hb-empty-state {
    text-align: center;
    padding: 60px 24px;
    background: var(--evo-card-bg, #ffffff);
    border: 2px dashed var(--evo-border, #cbd5e1);
    border-radius: var(--evo-radius, 16px);
    margin-bottom: 24px;
}

.hb-empty-icon {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: rgba(14, 165, 233, 0.1);
    color: var(--evo-primary, #0ea5e9);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin-bottom: 18px;
}

.hb-empty-state h3 {
    margin: 0 0 8px;
    font-size: 1.25rem;
    color: var(--evo-text, #0f172a);
}

.hb-empty-state p {
    margin: 0 auto 24px;
    max-width: 520px;
    color: var(--evo-text-muted, #64748b);
    line-height: 1.5;
}

.hb-modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    padding: 20px;
    animation: hbFadeIn 0.2s ease-out;
}

.hb-modal-dialog {
    background: var(--evo-card-bg, #ffffff);
    border: 1px solid var(--evo-border, #e2e8f0);
    border-radius: 16px;
    width: 100%;
    max-width: 860px;
    max-height: 85vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 40px rgba(0,0,0,0.18);
    overflow: hidden;
    animation: hbZoomIn 0.2s ease-out;
}

.hb-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid var(--evo-border, #e2e8f0);
}

.hb-modal-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.hb-modal-title h3 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--evo-text, #0f172a);
}

.hb-modal-close {
    background: none;
    border: none;
    font-size: 1.3rem;
    color: var(--evo-text-muted, #94a3b8);
    cursor: pointer;
    padding: 6px;
    border-radius: 6px;
}

.hb-modal-close:hover {
    color: var(--evo-text, #0f172a);
    background: var(--evo-surface-alt, #f1f5f9);
}

.hb-modal-body {
    padding: 24px;
    overflow-y: auto;
}

.hb-modal-desc {
    margin: 0 0 18px;
    font-size: 0.95rem;
    color: var(--evo-text-muted, #64748b);
}

.hb-catalog-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 14px;
}

.hb-catalog-card {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 16px;
    background: var(--evo-card-bg, #ffffff);
    border: 1px solid var(--evo-border, #e2e8f0);
    border-radius: 12px;
    text-decoration: none;
    color: inherit;
    transition: transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
}

.hb-catalog-card:hover {
    border-color: var(--evo-primary, #0ea5e9);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.06);
}

.hb-cat-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    border: 1px solid transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.hb-cat-info {
    flex: 1;
    min-width: 0;
}

.hb-cat-badge-wrap {
    margin-bottom: 4px;
}

.hb-cat-badge {
    font-size: 0.68rem;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 4px;
    text-transform: uppercase;
}

.hb-cat-title {
    margin: 0 0 4px;
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--evo-text, #0f172a);
}

.hb-cat-desc {
    margin: 0;
    font-size: 0.8rem;
    line-height: 1.4;
    color: var(--evo-text-muted, #64748b);
}

.hb-cat-arrow {
    color: var(--evo-text-muted, #cbd5e1);
    align-self: center;
    font-size: 0.85rem;
}

.hb-catalog-card:hover .hb-cat-arrow {
    color: var(--evo-primary, #0ea5e9);
}

@keyframes hbFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes hbZoomIn {
    from { transform: scale(0.96); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

@media (max-width: 768px) {
    .hb-topbar { flex-direction: column; align-items: flex-start; }
    .hb-section-card { flex-wrap: wrap; }
    .hb-card-actions { width: 100%; justify-content: flex-end; }
}
</style>

<!-- Scripts d'interaction : SortableJS & Toggle AJAX Instantané -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Réordonnancement Glisser-Déposer avec SortableJS
    const container = document.getElementById('sortable-blocks');
    if (container) {
        new Sortable(container, {
            animation: 180,
            handle: '.hb-card-handle',
            ghostClass: 'hb-sortable-ghost',
            onEnd: function() {
                const blocks = [];
                const cards = container.querySelectorAll('.hb-section-card');
                cards.forEach(function(card, index) {
                    const newOrder = index + 1;
                    card.querySelector('.order-num').textContent = newOrder;
                    blocks.push({ id: card.dataset.id, order: newOrder });
                });

                fetch('{{ ROUTER.generate("admin-homebuilder-reorder") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ blocks: blocks })
                })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Erreur réordonnancement:', data.error);
                    }
                })
                .catch(err => console.error('Erreur réseau:', err));
            }
        });
    }

    // 2. Bascule On/Off Directe en AJAX
    document.querySelectorAll('.hb-toggle-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const url = this.dataset.url;
            const blockId = this.dataset.id;
            const card = document.getElementById('block-card-{{ block.id }}') || this.closest('.hb-section-card');
            const statusLabel = document.getElementById('status-label-' + blockId);
            const activeCounter = document.getElementById('hbActiveCount');

            fetch(url, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (data.active) {
                        card.classList.remove('is-disabled');
                        if (statusLabel) statusLabel.innerHTML = '<span class="status-text-on">Visible</span>';
                    } else {
                        card.classList.add('is-disabled');
                        if (statusLabel) statusLabel.innerHTML = '<span class="status-text-off">Masqué</span>';
                    }
                    // Mettre à jour le compteur global
                    if (activeCounter) {
                        const count = document.querySelectorAll('.hb-section-card:not(.is-disabled)').length;
                        activeCounter.textContent = count;
                    }
                } else {
                    alert('Erreur: ' + (data.error || 'Impossible de changer l\'état du bloc'));
                    checkbox.checked = !checkbox.checked; // Annuler
                }
            })
            .catch(err => {
                console.error(err);
                checkbox.checked = !checkbox.checked;
            });
        });
    });

    // 3. Gestion de la modale "Catalogue de sections"
    const openBtn = document.getElementById('hbOpenCatalogBtn');
    const closeBtn = document.getElementById('hbCloseCatalogBtn');
    const modal = document.getElementById('hbCatalogModal');

    if (openBtn && modal) {
        openBtn.addEventListener('click', function() {
            modal.style.display = 'flex';
        });
    }

    if (closeBtn && modal) {
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }

    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }
});
</script>
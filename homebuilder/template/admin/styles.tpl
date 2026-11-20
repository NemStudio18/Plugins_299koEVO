<section class="overflow-auto">
    <header class="hb-studio-header">
        <div class="hb-header-title">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
            <div>
                <h2>Studio de Design : {{ block_title }}</h2>
                <span class="hb-badge-type">Section {{ block_type }} • #block-{{ block_id }}</span>
            </div>
        </div>
        <div class="hb-header-actions">
            <a href="{{ back_link }}" class="button secondary">
                <i class="fa-solid fa-arrow-left"></i> Retour au HomeBuilder
            </a>
            <button type="submit" form="styles-form" class="button">
                <i class="fa-solid fa-floppy-disk"></i> Enregistrer les styles
            </button>
        </div>
    </header>

    <div class="hb-studio-layout">
        <!-- Colonne GAUCHE : Contrôles de style -->
        <div class="hb-studio-controls">
            <form id="styles-form" method="POST" action="{{ edit_link }}">
    {{SHOW.tokenField}}

                <!-- PANNEAU 1 : ARRIÈRE-PLAN -->
                <div class="hb-control-panel">
                    <div class="hb-panel-title">
                        <i class="fa-solid fa-palette"></i> Arrière-plan
                    </div>
                    <div class="hb-panel-body">
                        <div class="hb-form-group">
                            <label for="container_backgroundType">Type d'arrière-plan</label>
                            <select id="container_backgroundType" name="container_backgroundType" class="hb-select">
                                <option value="solid" {% IF isSolid %}selected{% ENDIF %}>Couleur unie</option>
                                <option value="gradient" {% IF isGradient %}selected{% ENDIF %}>Dégradé moderne</option>
                            </select>
                        </div>

                        <!-- Fond uni -->
                        <div id="solid-controls" class="hb-form-group" {% IF isGradient %}style="display:none;"{% ENDIF %}>
                            <label for="container_backgroundColor">Couleur de fond</label>
                            <div class="hb-color-picker-wrap">
                                <input type="color" id="container_backgroundColor" name="container_backgroundColor" value="{{ styles.container.backgroundColor }}" class="hb-color-input">
                                <input type="text" id="container_backgroundColor_hex" value="{{ styles.container.backgroundColor }}" class="hb-hex-input" placeholder="#ffffff">
                            </div>
                        </div>

                        <!-- Fond dégradé -->
                        <div id="gradient-controls" class="hb-gradient-grid" {% IF isSolid %}style="display:none;"{% ENDIF %}>
                            <div class="hb-form-group">
                                <label for="container_gradientStart">Couleur départ</label>
                                <div class="hb-color-picker-wrap">
                                    <input type="color" id="container_gradientStart" name="container_gradientStart" value="{{ styles.container.gradientStart }}" class="hb-color-input">
                                    <input type="text" id="container_gradientStart_hex" value="{{ styles.container.gradientStart }}" class="hb-hex-input" placeholder="#ffffff">
                                </div>
                            </div>
                            <div class="hb-form-group">
                                <label for="container_gradientEnd">Couleur arrivée</label>
                                <div class="hb-color-picker-wrap">
                                    <input type="color" id="container_gradientEnd" name="container_gradientEnd" value="{{ styles.container.gradientEnd }}" class="hb-color-input">
                                    <input type="text" id="container_gradientEnd_hex" value="{{ styles.container.gradientEnd }}" class="hb-hex-input" placeholder="#f0f0f0">
                                </div>
                            </div>
                            <div class="hb-form-group" style="grid-column: 1 / -1;">
                                <label for="container_gradientDirection">Direction</label>
                                <select id="container_gradientDirection" name="container_gradientDirection" class="hb-select">
                                    <option value="to bottom" {% IF gradToBottom %}selected{% ENDIF %}>Vers le bas ↓</option>
                                    <option value="to right" {% IF gradToRight %}selected{% ENDIF %}>Vers la droite →</option>
                                    <option value="to top" {% IF gradToTop %}selected{% ENDIF %}>Vers le haut ↑</option>
                                    <option value="to left" {% IF gradToLeft %}selected{% ENDIF %}>Vers la gauche ←</option>
                                    <option value="45deg" {% IF grad45deg %}selected{% ENDIF %}>Diagonale 45° ↗</option>
                                    <option value="135deg" {% IF grad135deg %}selected{% ENDIF %}>Diagonale 135° ↘</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PANNEAU 2 : ESPACEMENTS & GÉOMÉTRIE -->
                <div class="hb-control-panel">
                    <div class="hb-panel-title">
                        <i class="fa-solid fa-maximize"></i> Espacements & Forme
                    </div>
                    <div class="hb-panel-body">
                        <div class="hb-form-group">
                            <div class="hb-label-with-presets">
                                <label for="container_padding">Padding intérieur</label>
                                <div class="hb-preset-chips">
                                    <button type="button" class="hb-chip" data-set-input="container_padding" data-val="15px">15px</button>
                                    <button type="button" class="hb-chip" data-set-input="container_padding" data-val="30px 20px">30px</button>
                                    <button type="button" class="hb-chip" data-set-input="container_padding" data-val="50px 24px">50px</button>
                                    <button type="button" class="hb-chip" data-set-input="container_padding" data-val="80px 30px">80px</button>
                                </div>
                            </div>
                            <input type="text" id="container_padding" name="container_padding" value="{{ styles.container.padding }}" placeholder="30px 20px" class="hb-text-input">
                        </div>

                        <div class="hb-form-group">
                            <div class="hb-label-with-presets">
                                <label for="container_borderRadius">Rayon d'arrondi (coins)</label>
                                <div class="hb-preset-chips">
                                    <button type="button" class="hb-chip" data-set-input="container_borderRadius" data-val="0px">0</button>
                                    <button type="button" class="hb-chip" data-set-input="container_borderRadius" data-val="8px">8px</button>
                                    <button type="button" class="hb-chip" data-set-input="container_borderRadius" data-val="16px">16px</button>
                                    <button type="button" class="hb-chip" data-set-input="container_borderRadius" data-val="28px">28px</button>
                                </div>
                            </div>
                            <input type="text" id="container_borderRadius" name="container_borderRadius" value="{{ styles.container.borderRadius }}" placeholder="12px" class="hb-text-input">
                        </div>

                        <div class="hb-form-group">
                            <div class="hb-label-with-presets">
                                <label for="container_margin">Marge extérieure</label>
                                <div class="hb-preset-chips">
                                    <button type="button" class="hb-chip" data-set-input="container_margin" data-val="0px">0</button>
                                    <button type="button" class="hb-chip" data-set-input="container_margin" data-val="20px 0">20px</button>
                                    <button type="button" class="hb-chip" data-set-input="container_margin" data-val="40px 0">40px</button>
                                </div>
                            </div>
                            <input type="text" id="container_margin" name="container_margin" value="{{ styles.container.margin }}" placeholder="20px 0" class="hb-text-input">
                        </div>

                        <div class="hb-form-group">
                            <div class="hb-label-with-presets">
                                <label for="container_border">Bordure</label>
                                <div class="hb-preset-chips">
                                    <button type="button" class="hb-chip" data-set-input="container_border" data-val="none">Aucune</button>
                                    <button type="button" class="hb-chip" data-set-input="container_border" data-val="1px solid rgba(0,0,0,0.08)">Fine</button>
                                    <button type="button" class="hb-chip" data-set-input="container_border" data-val="2px solid var(--evo-primary,#0ea5e9)">Accent</button>
                                </div>
                            </div>
                            <input type="text" id="container_border" name="container_border" value="{{ styles.container.border }}" placeholder="1px solid rgba(0,0,0,0.08)" class="hb-text-input">
                        </div>

                        <div class="hb-form-group">
                            <div class="hb-label-with-presets">
                                <label for="container_boxShadow">Ombre portée</label>
                                <div class="hb-preset-chips">
                                    <button type="button" class="hb-chip" data-set-input="container_boxShadow" data-val="none">Aucune</button>
                                    <button type="button" class="hb-chip" data-set-input="container_boxShadow" data-val="0 4px 15px rgba(0,0,0,0.05)">Douce</button>
                                    <button type="button" class="hb-chip" data-set-input="container_boxShadow" data-val="0 10px 30px rgba(0,0,0,0.12)">Prononcée</button>
                                </div>
                            </div>
                            <input type="text" id="container_boxShadow" name="container_boxShadow" value="{{ styles.container.boxShadow }}" placeholder="0 4px 15px rgba(0,0,0,0.05)" class="hb-text-input">
                        </div>

                        <div class="hb-form-group">
                            <label for="container_maxWidth">Largeur maximale</label>
                            <input type="text" id="container_maxWidth" name="container_maxWidth" value="{{ styles.container.maxWidth }}" placeholder="100% ou 1200px" class="hb-text-input">
                        </div>
                    </div>
                </div>

                <!-- PANNEAU 3 : TYPOGRAPHIE -->
                <div class="hb-control-panel">
                    <div class="hb-panel-title">
                        <i class="fa-solid fa-font"></i> Typographie
                    </div>
                    <div class="hb-panel-body">
                        <!-- Titre -->
                        <div class="hb-subgroup-title">Titres (H1, H2, H3)</div>
                        <div class="hb-form-group">
                            <label for="title_color">Couleur du titre</label>
                            <div class="hb-color-picker-wrap">
                                <input type="color" id="title_color" name="title_color" value="{{ styles.title.color }}" class="hb-color-input">
                                <input type="text" id="title_color_hex" value="{{ styles.title.color }}" class="hb-hex-input" placeholder="#333333">
                            </div>
                        </div>

                        <div class="hb-field-pair">
                            <div class="hb-form-group">
                                <label for="title_fontSize">Taille</label>
                                <input type="text" id="title_fontSize" name="title_fontSize" value="{{ styles.title.fontSize }}" placeholder="28px" class="hb-text-input">
                            </div>
                            <div class="hb-form-group">
                                <label for="title_fontWeight">Graisse</label>
                                <select id="title_fontWeight" name="title_fontWeight" class="hb-select">
                                    <option value="normal" {% IF titleFontWeightNormal %}selected{% ENDIF %}>Normal (400)</option>
                                    <option value="bold" {% IF titleFontWeightBold %}selected{% ENDIF %}>Gras (700)</option>
                                    <option value="lighter" {% IF titleFontWeightLighter %}selected{% ENDIF %}>Fin (300)</option>
                                </select>
                            </div>
                        </div>

                        <div class="hb-field-pair">
                            <div class="hb-form-group">
                                <label for="title_textAlign">Alignement</label>
                                <select id="title_textAlign" name="title_textAlign" class="hb-select">
                                    <option value="left" {% IF titleTextAlignLeft %}selected{% ENDIF %}>Gauche</option>
                                    <option value="center" {% IF titleTextAlignCenter %}selected{% ENDIF %}>Centré</option>
                                    <option value="right" {% IF titleTextAlignRight %}selected{% ENDIF %}>Droite</option>
                                </select>
                            </div>
                            <div class="hb-form-group">
                                <label for="title_marginBottom">Marge basse</label>
                                <input type="text" id="title_marginBottom" name="title_marginBottom" value="{{ styles.title.marginBottom }}" placeholder="16px" class="hb-text-input">
                            </div>
                        </div>

                        <!-- Contenu texte -->
                        <div class="hb-subgroup-title" style="margin-top: 16px;">Texte et paragraphes</div>
                        <div class="hb-form-group">
                            <label for="content_color">Couleur du texte</label>
                            <div class="hb-color-picker-wrap">
                                <input type="color" id="content_color" name="content_color" value="{{ styles.content.color }}" class="hb-color-input">
                                <input type="text" id="content_color_hex" value="{{ styles.content.color }}" class="hb-hex-input" placeholder="#666666">
                            </div>
                        </div>

                        <div class="hb-field-pair">
                            <div class="hb-form-group">
                                <label for="content_fontSize">Taille texte</label>
                                <input type="text" id="content_fontSize" name="content_fontSize" value="{{ styles.content.fontSize }}" placeholder="16px" class="hb-text-input">
                            </div>
                            <div class="hb-form-group">
                                <label for="content_lineHeight">Hauteur de ligne</label>
                                <input type="text" id="content_lineHeight" name="content_lineHeight" value="{{ styles.content.lineHeight }}" placeholder="1.6" class="hb-text-input">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PANNEAU 4 : BOUTONS & LIENS -->
                <div class="hb-control-panel">
                    <div class="hb-panel-title">
                        <i class="fa-solid fa-square-arrow-up-right"></i> Boutons & Liens
                    </div>
                    <div class="hb-panel-body">
                        <div class="hb-field-pair">
                            <div class="hb-form-group">
                                <label for="button_backgroundColor">Fond bouton</label>
                                <div class="hb-color-picker-wrap">
                                    <input type="color" id="button_backgroundColor" name="button_backgroundColor" value="{{ styles.button.backgroundColor }}" class="hb-color-input">
                                    <input type="text" id="button_backgroundColor_hex" value="{{ styles.button.backgroundColor }}" class="hb-hex-input" placeholder="#0ea5e9">
                                </div>
                            </div>
                            <div class="hb-form-group">
                                <label for="button_color">Texte bouton</label>
                                <div class="hb-color-picker-wrap">
                                    <input type="color" id="button_color" name="button_color" value="{{ styles.button.color }}" class="hb-color-input">
                                    <input type="text" id="button_color_hex" value="{{ styles.button.color }}" class="hb-hex-input" placeholder="#ffffff">
                                </div>
                            </div>
                        </div>

                        <div class="hb-field-pair">
                            <div class="hb-form-group">
                                <label for="button_borderRadius">Arrondi bouton</label>
                                <input type="text" id="button_borderRadius" name="button_borderRadius" value="{{ styles.button.borderRadius }}" placeholder="8px" class="hb-text-input">
                            </div>
                            <div class="hb-form-group">
                                <label for="button_padding">Padding bouton</label>
                                <input type="text" id="button_padding" name="button_padding" value="{{ styles.button.padding }}" placeholder="10px 20px" class="hb-text-input">
                            </div>
                        </div>

                        <div class="hb-form-group">
                            <label for="links_color">Couleur des liens</label>
                            <div class="hb-color-picker-wrap">
                                <input type="color" id="links_color" name="links_color" value="{{ styles.links.color }}" class="hb-color-input">
                                <input type="text" id="links_color_hex" value="{{ styles.links.color }}" class="hb-hex-input" placeholder="#0ea5e9">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PANNEAU 5 : PRESETS EN 1 CLIC -->
                <div class="hb-control-panel">
                    <div class="hb-panel-title">
                        <i class="fa-solid fa-sparkles"></i> Thèmes pré-configurés (1 clic)
                    </div>
                    <div class="hb-panel-body">
                        <div class="hb-presets-grid">
                            <button type="button" class="hb-preset-card" data-preset="clean-white">
                                <span class="hb-preset-badge" style="background:#f8fafc; border:1px solid #cbd5e1;"></span>
                                <div><strong>Clair Épuré</strong><small>Fond blanc, texte sombre, ombre douce</small></div>
                            </button>
                            <button type="button" class="hb-preset-card" data-preset="dark-navy">
                                <span class="hb-preset-badge" style="background:#0f172a;"></span>
                                <div><strong>Dark Nuit</strong><small>Bleu profond, texte blanc, contrasté</small></div>
                            </button>
                            <button type="button" class="hb-preset-card" data-preset="gradient-sunset">
                                <span class="hb-preset-badge" style="background:linear-gradient(135deg, #4f46e5, #ec4899);"></span>
                                <div><strong>Vibrant Sunset</strong><small>Dégradé violet-rose moderne</small></div>
                            </button>
                            <button type="button" class="hb-preset-card" data-preset="gradient-ocean">
                                <span class="hb-preset-badge" style="background:linear-gradient(135deg, #0284c7, #0d9488);"></span>
                                <div><strong>Océan & Émeraude</strong><small>Dégradé bleu azur vers vert d'eau</small></div>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="hb-submit-box">
                    <button type="submit" class="button" style="width: 100%; justify-content: center; padding: 12px;">
                        <i class="fa-solid fa-floppy-disk"></i> Enregistrer les styles
                    </button>
                </div>
            </form>
        </div>

        <!-- Colonne DROITE : Prévisualisation en direct (Sticky) -->
        <div class="hb-studio-preview-col">
            <div class="hb-preview-card">
                <div class="hb-preview-toolbar">
                    <div class="hb-preview-title">
                        <span class="hb-live-dot"></span> Aperçu en direct
                    </div>
                    <div class="hb-device-switcher">
                        <button type="button" class="hb-device-btn active" data-width="100%" title="Ordinateur">
                            <i class="fa-solid fa-desktop"></i>
                        </button>
                        <button type="button" class="hb-device-btn" data-width="768px" title="Tablette">
                            <i class="fa-solid fa-tablet-screen-button"></i>
                        </button>
                        <button type="button" class="hb-device-btn" data-width="375px" title="Mobile">
                            <i class="fa-solid fa-mobile-screen"></i>
                        </button>
                    </div>
                </div>

                <div class="hb-preview-viewport">
                    <div id="hb-device-canvas" style="width: 100%; transition: width 0.3s ease; margin: 0 auto;">
                        <div id="hb-rendered-block-container">
                            {{ block_html | raw }}
                        </div>
                    </div>
                </div>

                <div class="hb-preview-footer">
                    <small><i class="fa-solid fa-circle-info"></i> L'aperçu reflète fidèlement le rendu sur votre page d'accueil.</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Balise CSS dynamique pour la mise à jour en direct -->
<style id="hb-dynamic-live-css"></style>

<style>
.hb-studio-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 24px;
}
.hb-header-title {
    display: flex;
    align-items: center;
    gap: 14px;
}
.hb-header-title i {
    font-size: 1.8rem;
    color: var(--evo-primary, #0ea5e9);
}
.hb-header-title h2 {
    margin: 0;
    font-size: 1.4rem;
}
.hb-badge-type {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 999px;
    background: var(--evo-surface-alt, #f1f5f9);
    color: var(--evo-text-muted, #64748b);
    margin-top: 4px;
}
.hb-header-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

/* Grille Studio */
.hb-studio-layout {
    display: grid;
    grid-template-columns: 460px 1fr;
    gap: 24px;
    align-items: start;
}
@media (max-width: 1080px) {
    .hb-studio-layout {
        grid-template-columns: 1fr;
    }
}

/* Panneaux de contrôle */
.hb-control-panel {
    background: var(--evo-card-bg, #ffffff);
    border: 1px solid var(--evo-border, #e2e8f0);
    border-radius: 12px;
    margin-bottom: 16px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
.hb-panel-title {
    padding: 12px 18px;
    background: var(--evo-surface-alt, #f8fafc);
    border-bottom: 1px solid var(--evo-border, #e2e8f0);
    font-weight: 600;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--evo-text, #1e293b);
}
.hb-panel-title i {
    color: var(--evo-primary, #0ea5e9);
}
.hb-panel-body {
    padding: 18px;
}
.hb-subgroup-title {
    font-size: 0.82rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--evo-text-muted, #64748b);
    margin-bottom: 12px;
    padding-bottom: 4px;
    border-bottom: 1px dashed var(--evo-border, #e2e8f0);
}
.hb-form-group {
    margin-bottom: 14px;
}
.hb-form-group:last-child {
    margin-bottom: 0;
}
.hb-form-group label {
    display: block;
    font-size: 0.85rem;
    font-weight: 500;
    margin-bottom: 6px;
    color: var(--evo-text, #334155);
}
.hb-field-pair {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.hb-gradient-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

/* Inputs & Sélecteurs */
.hb-select, .hb-text-input {
    width: 100%;
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid var(--evo-border, #cbd5e1);
    background: var(--evo-card-bg, #ffffff);
    color: var(--evo-text, #1e293b);
    font-size: 0.9rem;
    box-sizing: border-box;
}
.hb-select:focus, .hb-text-input:focus {
    outline: none;
    border-color: var(--evo-primary, #0ea5e9);
    box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15);
}

/* Color Picker */
.hb-color-picker-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}
.hb-color-input {
    width: 38px;
    height: 38px;
    padding: 0;
    border: 1px solid var(--evo-border, #cbd5e1);
    border-radius: 8px;
    cursor: pointer;
    background: transparent;
}
.hb-hex-input {
    flex: 1;
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid var(--evo-border, #cbd5e1);
    font-family: monospace;
    font-size: 0.9rem;
    color: var(--evo-text, #1e293b);
    background: var(--evo-card-bg, #ffffff);
}

/* Presets Chips */
.hb-label-with-presets {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}
.hb-label-with-presets label {
    margin-bottom: 0;
}
.hb-preset-chips {
    display: flex;
    gap: 4px;
}
.hb-chip {
    background: var(--evo-surface-alt, #f1f5f9);
    border: 1px solid var(--evo-border, #cbd5e1);
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 0.72rem;
    cursor: pointer;
    color: var(--evo-text-muted, #475569);
    transition: all 0.15s;
}
.hb-chip:hover {
    background: var(--evo-primary-soft, #e0f2fe);
    color: var(--evo-primary, #0284c7);
    border-color: var(--evo-primary, #0ea5e9);
}

/* Presets 1 clic */
.hb-presets-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.hb-preset-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: var(--evo-surface-alt, #f8fafc);
    border: 1px solid var(--evo-border, #e2e8f0);
    border-radius: 8px;
    text-align: left;
    cursor: pointer;
    transition: all 0.2s;
}
.hb-preset-card:hover {
    border-color: var(--evo-primary, #0ea5e9);
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0,0,0,0.05);
}
.hb-preset-badge {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    flex-shrink: 0;
}
.hb-preset-card strong {
    display: block;
    font-size: 0.85rem;
    color: var(--evo-text, #1e293b);
}
.hb-preset-card small {
    display: block;
    font-size: 0.72rem;
    color: var(--evo-text-muted, #64748b);
    line-height: 1.2;
}

/* Bouton submit bas */
.hb-submit-box {
    margin-top: 16px;
    position: sticky;
    bottom: 20px;
    z-index: 10;
}

/* Colonne Sticky Preview */
.hb-studio-preview-col {
    position: sticky;
    top: 20px;
}
.hb-preview-card {
    background: var(--evo-card-bg, #ffffff);
    border: 1px solid var(--evo-border, #e2e8f0);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}
.hb-preview-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 18px;
    background: var(--evo-surface-alt, #f8fafc);
    border-bottom: 1px solid var(--evo-border, #e2e8f0);
}
.hb-preview-title {
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 8px;
}
.hb-live-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}
.hb-device-switcher {
    display: flex;
    gap: 4px;
    background: var(--evo-card-bg, #ffffff);
    padding: 2px;
    border-radius: 6px;
    border: 1px solid var(--evo-border, #cbd5e1);
}
.hb-device-btn {
    border: none;
    background: transparent;
    padding: 6px 10px;
    border-radius: 4px;
    cursor: pointer;
    color: var(--evo-text-muted, #64748b);
    transition: all 0.15s;
}
.hb-device-btn.active, .hb-device-btn:hover {
    background: var(--evo-primary-soft, #e0f2fe);
    color: var(--evo-primary, #0284c7);
}
.hb-preview-viewport {
    padding: 24px;
    background: repeating-conic-gradient(rgba(0,0,0,0.02) 0% 25%, transparent 0% 50%) 50% / 20px 20px, var(--evo-surface-alt, #f8fafc);
    min-height: 380px;
    max-height: calc(100vh - 240px);
    overflow-y: auto;
    display: flex;
    justify-content: center;
}
.hb-preview-footer {
    padding: 8px 16px;
    background: var(--evo-surface-alt, #f8fafc);
    border-top: 1px solid var(--evo-border, #e2e8f0);
    color: var(--evo-text-muted, #64748b);
    font-size: 0.8rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var blockId = '{{ block_id }}';
    var selector = '#block-' + blockId;
    var styleTag = document.getElementById('hb-dynamic-live-css');

    // Synchronisation color picker <-> hex text input
    function syncColor(pickerId, hexId) {
        var picker = document.getElementById(pickerId);
        var hex = document.getElementById(hexId);
        if (!picker || !hex) return;
        picker.addEventListener('input', function() {
            hex.value = picker.value;
            updateLivePreview();
        });
        hex.addEventListener('input', function() {
            if (/^#[0-9A-Fa-f]{6}$/.test(hex.value)) {
                picker.value = hex.value;
            }
            updateLivePreview();
        });
    }

    syncColor('container_backgroundColor', 'container_backgroundColor_hex');
    syncColor('container_gradientStart', 'container_gradientStart_hex');
    syncColor('container_gradientEnd', 'container_gradientEnd_hex');
    syncColor('title_color', 'title_color_hex');
    syncColor('content_color', 'content_color_hex');
    syncColor('button_backgroundColor', 'button_backgroundColor_hex');
    syncColor('button_color', 'button_color_hex');
    syncColor('links_color', 'links_color_hex');

    // Basculement Type de fond (Solid vs Gradient)
    var bgTypeSelect = document.getElementById('container_backgroundType');
    var solidControls = document.getElementById('solid-controls');
    var gradientControls = document.getElementById('gradient-controls');

    bgTypeSelect.addEventListener('change', function() {
        if (this.value === 'gradient') {
            solidControls.style.display = 'none';
            gradientControls.style.display = 'grid';
        } else {
            solidControls.style.display = 'block';
            gradientControls.style.display = 'none';
        }
        updateLivePreview();
    });

    // Chips boutons de presets (padding, border-radius, etc.)
    document.querySelectorAll('[data-set-input]').forEach(function(chip) {
        chip.addEventListener('click', function() {
            var inputId = this.getAttribute('data-set-input');
            var val = this.getAttribute('data-val');
            var targetInput = document.getElementById(inputId);
            if (targetInput) {
                targetInput.value = val;
                updateLivePreview();
            }
        });
    });

    // Sélecteur d'appareil (Desktop, Tablet, Mobile)
    var canvas = document.getElementById('hb-device-canvas');
    document.querySelectorAll('.hb-device-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.hb-device-btn').forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            var width = this.getAttribute('data-width');
            canvas.style.maxWidth = width;
        });
    });

    // Moteur de rendu en direct (CSS Live Generator)
    function updateLivePreview() {
        var bgType = bgTypeSelect.value;
        var css = '';

        // Container
        var containerRules = [];
        if (bgType === 'gradient') {
            var start = document.getElementById('container_gradientStart').value;
            var end = document.getElementById('container_gradientEnd').value;
            var dir = document.getElementById('container_gradientDirection').value;
            containerRules.push('background: linear-gradient(' + dir + ', ' + start + ', ' + end + ') !important');
        } else {
            var bgCol = document.getElementById('container_backgroundColor').value;
            if (bgCol) containerRules.push('background: ' + bgCol + ' !important');
        }

        var padding = document.getElementById('container_padding').value;
        if (padding) containerRules.push('padding: ' + padding + ' !important');

        var margin = document.getElementById('container_margin').value;
        if (margin) containerRules.push('margin: ' + margin + ' !important');

        var radius = document.getElementById('container_borderRadius').value;
        if (radius) containerRules.push('border-radius: ' + radius + ' !important');

        var border = document.getElementById('container_border').value;
        if (border) containerRules.push('border: ' + border + ' !important');

        var shadow = document.getElementById('container_boxShadow').value;
        if (shadow) containerRules.push('box-shadow: ' + shadow + ' !important');

        var maxWidth = document.getElementById('container_maxWidth').value;
        if (maxWidth) containerRules.push('max-width: ' + maxWidth + ' !important');

        css += selector + ' { ' + containerRules.join('; ') + ' }\n';

        // Titres
        var titleRules = [];
        var titleCol = document.getElementById('title_color').value;
        if (titleCol) titleRules.push('color: ' + titleCol + ' !important');
        var titleSize = document.getElementById('title_fontSize').value;
        if (titleSize) titleRules.push('font-size: ' + titleSize + ' !important');
        var titleWeight = document.getElementById('title_fontWeight').value;
        if (titleWeight) titleRules.push('font-weight: ' + titleWeight + ' !important');
        var titleAlign = document.getElementById('title_textAlign').value;
        if (titleAlign) titleRules.push('text-align: ' + titleAlign + ' !important');
        var titleMb = document.getElementById('title_marginBottom').value;
        if (titleMb) titleRules.push('margin-bottom: ' + titleMb + ' !important');

        css += selector + ' h1, ' + selector + ' h2, ' + selector + ' h3, ' + selector + ' .hero-title, ' + selector + ' .section-title { ' + titleRules.join('; ') + ' }\n';

        // Contenu
        var contentRules = [];
        var contentCol = document.getElementById('content_color').value;
        if (contentCol) contentRules.push('color: ' + contentCol + ' !important');
        var contentSize = document.getElementById('content_fontSize').value;
        if (contentSize) contentRules.push('font-size: ' + contentSize + ' !important');
        var contentLh = document.getElementById('content_lineHeight').value;
        if (contentLh) contentRules.push('line-height: ' + contentLh + ' !important');

        css += selector + ' .content, ' + selector + ' .hero-subtitle, ' + selector + ' .section-desc, ' + selector + ' p, ' + selector + ' li { ' + contentRules.join('; ') + ' }\n';

        // Boutons
        var btnRules = [];
        var btnBg = document.getElementById('button_backgroundColor').value;
        if (btnBg) btnRules.push('background: ' + btnBg + ' !important');
        var btnCol = document.getElementById('button_color').value;
        if (btnCol) btnRules.push('color: ' + btnCol + ' !important');
        var btnRadius = document.getElementById('button_borderRadius').value;
        if (btnRadius) btnRules.push('border-radius: ' + btnRadius + ' !important');
        var btnPad = document.getElementById('button_padding').value;
        if (btnPad) btnRules.push('padding: ' + btnPad + ' !important');

        css += selector + ' .btn, ' + selector + ' button, ' + selector + ' .hero-btn { ' + btnRules.join('; ') + ' }\n';

        // Liens
        var linkCol = document.getElementById('links_color').value;
        if (linkCol) {
            css += selector + ' a { color: ' + linkCol + ' !important; }\n';
        }

        styleTag.textContent = css;
    }

    // Écoute de tous les champs de saisie pour live update instantané
    document.querySelectorAll('#styles-form input, #styles-form select').forEach(function(el) {
        el.addEventListener('input', updateLivePreview);
        el.addEventListener('change', updateLivePreview);
    });

    // Thèmes pré-configurés en 1 clic
    document.querySelectorAll('.hb-preset-card').forEach(function(card) {
        card.addEventListener('click', function() {
            var preset = this.getAttribute('data-preset');
            if (preset === 'clean-white') {
                bgTypeSelect.value = 'solid';
                document.getElementById('container_backgroundColor').value = '#ffffff';
                document.getElementById('container_backgroundColor_hex').value = '#ffffff';
                document.getElementById('container_borderRadius').value = '16px';
                document.getElementById('container_border').value = '1px solid rgba(0,0,0,0.06)';
                document.getElementById('container_boxShadow').value = '0 4px 20px rgba(0,0,0,0.04)';
                document.getElementById('title_color').value = '#0f172a';
                document.getElementById('title_color_hex').value = '#0f172a';
                document.getElementById('content_color').value = '#475569';
                document.getElementById('content_color_hex').value = '#475569';
                document.getElementById('button_backgroundColor').value = '#0ea5e9';
                document.getElementById('button_backgroundColor_hex').value = '#0ea5e9';
                document.getElementById('button_color').value = '#ffffff';
                document.getElementById('button_color_hex').value = '#ffffff';
            } else if (preset === 'dark-navy') {
                bgTypeSelect.value = 'solid';
                document.getElementById('container_backgroundColor').value = '#0f172a';
                document.getElementById('container_backgroundColor_hex').value = '#0f172a';
                document.getElementById('container_borderRadius').value = '16px';
                document.getElementById('container_border').value = '1px solid rgba(255,255,255,0.1)';
                document.getElementById('container_boxShadow').value = '0 10px 30px rgba(0,0,0,0.25)';
                document.getElementById('title_color').value = '#ffffff';
                document.getElementById('title_color_hex').value = '#ffffff';
                document.getElementById('content_color').value = '#94a3b8';
                document.getElementById('content_color_hex').value = '#94a3b8';
                document.getElementById('button_backgroundColor').value = '#38bdf8';
                document.getElementById('button_backgroundColor_hex').value = '#38bdf8';
                document.getElementById('button_color').value = '#0f172a';
                document.getElementById('button_color_hex').value = '#0f172a';
            } else if (preset === 'gradient-sunset') {
                bgTypeSelect.value = 'gradient';
                document.getElementById('container_gradientStart').value = '#4f46e5';
                document.getElementById('container_gradientStart_hex').value = '#4f46e5';
                document.getElementById('container_gradientEnd').value = '#ec4899';
                document.getElementById('container_gradientEnd_hex').value = '#ec4899';
                document.getElementById('container_gradientDirection').value = '135deg';
                document.getElementById('container_borderRadius').value = '20px';
                document.getElementById('container_border').value = 'none';
                document.getElementById('container_boxShadow').value = '0 12px 35px rgba(79, 70, 229, 0.25)';
                document.getElementById('title_color').value = '#ffffff';
                document.getElementById('title_color_hex').value = '#ffffff';
                document.getElementById('content_color').value = '#fdf2f8';
                document.getElementById('content_color_hex').value = '#fdf2f8';
                document.getElementById('button_backgroundColor').value = '#ffffff';
                document.getElementById('button_backgroundColor_hex').value = '#ffffff';
                document.getElementById('button_color').value = '#4f46e5';
                document.getElementById('button_color_hex').value = '#4f46e5';
            } else if (preset === 'gradient-ocean') {
                bgTypeSelect.value = 'gradient';
                document.getElementById('container_gradientStart').value = '#0284c7';
                document.getElementById('container_gradientStart_hex').value = '#0284c7';
                document.getElementById('container_gradientEnd').value = '#0d9488';
                document.getElementById('container_gradientEnd_hex').value = '#0d9488';
                document.getElementById('container_gradientDirection').value = '135deg';
                document.getElementById('container_borderRadius').value = '20px';
                document.getElementById('container_border').value = 'none';
                document.getElementById('container_boxShadow').value = '0 12px 35px rgba(2, 132, 199, 0.25)';
                document.getElementById('title_color').value = '#ffffff';
                document.getElementById('title_color_hex').value = '#ffffff';
                document.getElementById('content_color').value = '#f0fdfa';
                document.getElementById('content_color_hex').value = '#f0fdfa';
                document.getElementById('button_backgroundColor').value = '#ffffff';
                document.getElementById('button_backgroundColor_hex').value = '#ffffff';
                document.getElementById('button_color').value = '#0284c7';
                document.getElementById('button_color_hex').value = '#0284c7';
            }
            bgTypeSelect.dispatchEvent(new Event('change'));
            updateLivePreview();
        });
    });

    // Initialisation
    updateLivePreview();
});
</script>

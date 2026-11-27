<div class="evo-game-wrapper" id="gb-stage" data-palette="{{ defaultPalette }}">
    <!-- Main Game Screen Area -->
    <div class="evo-gb-main-screen-area">
        <!-- Top Toolbar: Palettes & Sound -->
        <div class="evo-gb-screen-toolbar">
            <div class="evo-gb-mode-selector">
                <button type="button" class="evo-gb-tool-btn is-active" data-palette-btn="gbc_color" title="Game Boy Color (Palette couleur vibrante)">
                    <i class="fa-solid fa-palette" aria-hidden="true"></i> Color (GBC)
                </button>
                <button type="button" class="evo-gb-tool-btn" data-palette-btn="gb_classic" title="Game Boy Classic (Palette originale 1989)">
                    <i class="fa-solid fa-gamepad" aria-hidden="true"></i> Classic DMG
                </button>
                <button type="button" class="evo-gb-tool-btn" data-palette-btn="gb_pocket" title="Game Boy Pocket (Monochrome Silver)">
                    <i class="fa-solid fa-circle-half-stroke" aria-hidden="true"></i> Pocket
                </button>
            </div>
            <div class="evo-gb-audio-actions">
                <button type="button" class="evo-gb-tool-btn" id="btn-toggle-sound" title="Activer / Désactiver le son 8-bit">
                    <i class="fa-solid fa-volume-high" aria-hidden="true"></i> Son : Activé
                </button>
            </div>
        </div>

        <!-- The LCD Game Display Unit (Generous Size) -->
        <div class="evo-gb-display-cabinet">
            <!-- Screen Header Bar -->
            <div class="evo-gb-display-header">
                <div class="evo-gb-led-indicator">
                    <span class="evo-gb-battery-led"></span>
                    <span class="evo-gb-led-text">POWER</span>
                </div>
                <div class="evo-gb-title-badge">RETRO TETRIS • GAME BOY EDITION</div>
                <div class="evo-gb-fps-counter">59.73 Hz</div>
            </div>

            <!-- The Matrix & HUD -->
            <div class="evo-gb-screen-viewport">
                <!-- 10x18 Matrix -->
                <div class="evo-gb-board-frame">
                    <div class="evo-gb-board" id="gb-board"></div>
                </div>

                <!-- Side Info HUD -->
                <div class="evo-gb-hud">
                    <!-- Next Piece -->
                    <div class="evo-gb-hud-box">
                        <span class="evo-gb-hud-label">SUIVANT</span>
                        <div class="evo-gb-next-grid" id="gb-next"></div>
                    </div>

                    <!-- Score -->
                    <div class="evo-gb-hud-box">
                        <span class="evo-gb-hud-label">SCORE</span>
                        <span class="evo-gb-hud-val" id="gb-sc">0</span>
                    </div>

                    <!-- Lines -->
                    <div class="evo-gb-hud-box">
                        <span class="evo-gb-hud-label">LIGNES</span>
                        <span class="evo-gb-hud-val" id="gb-li">0</span>
                    </div>

                    <!-- Level -->
                    <div class="evo-gb-hud-box">
                        <span class="evo-gb-hud-label">NIVEAU</span>
                        <span class="evo-gb-hud-val" id="gb-lv">0</span>
                    </div>

                    <!-- Quick Action -->
                    <button type="button" class="evo-gb-play-trigger" data-gb-k="Enter">
                        <i class="fa-solid fa-play" aria-hidden="true"></i> JOUER
                    </button>
                </div>
            </div>

            <!-- Bottom Status Strip -->
            <div class="evo-gb-status-strip">
                <span id="gb-status">Appuyez sur Entrée ou START pour lancer la partie</span>
            </div>
        </div>

        <!-- Touch Controls Bar (visible on touch / mobile devices) -->
        <div class="evo-gb-touch-bar">
            <div class="evo-gb-touch-group">
                <button type="button" class="evo-gb-touch-btn" data-gb-k="ArrowLeft" aria-label="Gauche">←</button>
                <button type="button" class="evo-gb-touch-btn" data-gb-k="ArrowDown" aria-label="Bas">↓</button>
                <button type="button" class="evo-gb-touch-btn" data-gb-k="ArrowRight" aria-label="Droite">→</button>
            </div>
            <div class="evo-gb-touch-group">
                <button type="button" class="evo-gb-touch-btn evo-btn-rotate" data-gb-k="z" aria-label="Rotation anti-horaire">↺ B</button>
                <button type="button" class="evo-gb-touch-btn evo-btn-rotate" data-gb-k="x" aria-label="Rotation horaire">↻ A</button>
                <button type="button" class="evo-gb-touch-btn" data-gb-k="p" aria-label="Pause">⏸</button>
            </div>
        </div>
    </div>

    <!-- Right Sidebar: Player Profile, Leaderboard & Controls Guide -->
    <div class="evo-game-sidebar">
        <!-- Player Card -->
        <div class="evo-player-card">
            <div class="evo-player-header">
                <div class="evo-player-name">
                    <i class="fa-solid fa-user-astronaut" aria-hidden="true"></i>
                    <span id="player-name-display">JOUEUR</span>
                </div>
                <button type="button" class="evo-player-edit-btn" id="btn-edit-pseudo">
                    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i> Modifier
                </button>
            </div>
            <div class="evo-player-stats">
                <div class="evo-player-stat-box">
                    <div class="evo-player-stat-val" id="player-best-score">0</div>
                    <div class="evo-player-stat-lbl">Record Personnel</div>
                </div>
                <div class="evo-player-stat-box">
                    <div class="evo-player-stat-val" id="player-total-games">0</div>
                    <div class="evo-player-stat-lbl">Parties Jouées</div>
                </div>
            </div>
        </div>

        <!-- Leaderboard Card with Tabs -->
        <div class="evo-lead-card">
            <div class="evo-lead-tabs">
                <button type="button" class="evo-lead-tab is-active" data-tab="top">🏆 Hall of Fame</button>
                <button type="button" class="evo-lead-tab" data-tab="today">📅 Aujourd'hui</button>
                <button type="button" class="evo-lead-tab" data-tab="player">👤 Mes Scores</button>
            </div>
            <ul class="evo-lead-list" id="lead-list">
                <!-- Dynamically loaded -->
            </ul>
        </div>

        <!-- Instructions Card -->
        <div class="evo-guide-card">
            <p style="margin: 0 0 10px 0; font-weight: 700; color: var(--evo-text, #1e293b);">
                <i class="fa-solid fa-keyboard" aria-hidden="true"></i> Raccourcis Clavier
            </p>
            <ul style="margin: 0; padding-left: 18px; display: flex; flex-direction: column; gap: 6px;">
                <li><span class="evo-guide-kbd">←</span> <span class="evo-guide-kbd">→</span> : Déplacements latéraux</li>
                <li><span class="evo-guide-kbd">↓</span> : Descente rapide (Soft drop)</li>
                <li><span class="evo-guide-kbd">Z</span> ou <span class="evo-guide-kbd">A</span> : Rotation anti-horaire (↺)</li>
                <li><span class="evo-guide-kbd">X</span> ou <span class="evo-guide-kbd">B</span> ou <span class="evo-guide-kbd">↑</span> : Rotation horaire (↻)</li>
                <li><span class="evo-guide-kbd">P</span> : Mettre en pause</li>
                <li><span class="evo-guide-kbd">Entrée</span> : Démarrer / Rejouer</li>
            </ul>
        </div>
    </div>
</div>

<script src="/plugin/game/assets/tetris.js?v=1.0.1" defer></script>

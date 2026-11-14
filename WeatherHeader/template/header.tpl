<div class="weather-living-scene ws-time-day weather-state-sunny" id="meteo_living_scene" data-city="{{defaultCity}}" data-country="{{defaultCountry}}" data-lat="{{latitude}}" data-lon="{{longitude}}" data-auto-geo="{% if autoGeolocate %}1{% else %}0{% endif %}">
    <!-- 1. Couche du Ciel dynamique -->
    <div class="ws-sky-layer" aria-hidden="true"></div>

    <!-- 2. Scène Vectorielle Vivante Unique -->
    <svg class="ws-svg-canvas" viewBox="0 0 1440 320" preserveAspectRatio="none" aria-hidden="true">
        <defs>
            <radialGradient id="wsSunGlow" cx="50%" cy="50%" r="50%">
                <stop offset="0%" stop-color="#fffbeb" stop-opacity="1"/>
                <stop offset="40%" stop-color="#fde047" stop-opacity="0.9"/>
                <stop offset="70%" stop-color="#f59e0b" stop-opacity="0.5"/>
                <stop offset="100%" stop-color="#ea580c" stop-opacity="0"/>
            </radialGradient>
            <radialGradient id="wsMoonGlow" cx="50%" cy="50%" r="50%">
                <stop offset="0%" stop-color="#ffffff" stop-opacity="1"/>
                <stop offset="60%" stop-color="#e0e7ff" stop-opacity="0.75"/>
                <stop offset="100%" stop-color="#818cf8" stop-opacity="0"/>
            </radialGradient>
            <linearGradient id="wsTerrainBack" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="var(--ws-hill-back)" stop-opacity="0.85"/>
                <stop offset="100%" stop-color="var(--ws-hill-back)" stop-opacity="1"/>
            </linearGradient>
            <linearGradient id="wsTerrainFront" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="var(--ws-hill-front)" stop-opacity="0.95"/>
                <stop offset="100%" stop-color="var(--ws-hill-front)" stop-opacity="1"/>
            </linearGradient>
        </defs>

        <!-- Nuit : Constellation d'étoiles scintillantes -->
        <g class="ws-stars-group">
            <circle cx="120" cy="40" r="1.5" class="ws-star ws-star-1"/>
            <circle cx="280" cy="70" r="1.2" class="ws-star ws-star-2"/>
            <circle cx="450" cy="30" r="2.0" class="ws-star ws-star-3"/>
            <circle cx="620" cy="80" r="1.4" class="ws-star ws-star-4"/>
            <circle cx="850" cy="45" r="1.8" class="ws-star ws-star-1"/>
            <circle cx="1020" cy="65" r="1.3" class="ws-star ws-star-2"/>
            <circle cx="1180" cy="35" r="2.2" class="ws-star ws-star-3"/>
            <circle cx="1340" cy="75" r="1.5" class="ws-star ws-star-4"/>
        </g>

        <!-- Astre Solaire (Jour) - Placé à gauche -->
        <g class="ws-sun-group">
            <circle cx="220" cy="90" r="80" fill="url(#wsSunGlow)" class="ws-sun-halo"/>
            <circle cx="220" cy="90" r="32" fill="#fef08a" class="ws-sun-disc"/>
            <g class="ws-sun-rays">
                <line x1="220" y1="40" x2="220" y2="20" stroke="#fef08a" stroke-width="3" stroke-linecap="round"/>
                <line x1="220" y1="140" x2="220" y2="160" stroke="#fef08a" stroke-width="3" stroke-linecap="round"/>
                <line x1="170" y1="90" x2="150" y2="90" stroke="#fef08a" stroke-width="3" stroke-linecap="round"/>
                <line x1="270" y1="90" x2="290" y2="90" stroke="#fef08a" stroke-width="3" stroke-linecap="round"/>
                <line x1="185" y1="55" x2="170" y2="40" stroke="#fef08a" stroke-width="3" stroke-linecap="round"/>
                <line x1="255" y1="125" x2="270" y2="140" stroke="#fef08a" stroke-width="3" stroke-linecap="round"/>
                <line x1="185" y1="125" x2="170" y2="140" stroke="#fef08a" stroke-width="3" stroke-linecap="round"/>
                <line x1="255" y1="55" x2="270" y2="40" stroke="#fef08a" stroke-width="3" stroke-linecap="round"/>
            </g>
        </g>

        <!-- Astre Lunaire (Nuit) - Placé à gauche -->
        <g class="ws-moon-group">
            <circle cx="220" cy="90" r="60" fill="url(#wsMoonGlow)" class="ws-moon-halo"/>
            <path d="M210,65 C228,65 242,79 242,97 C242,115 228,129 210,129 C205,129 200,128 195,126 C206,121 214,110 214,97 C214,84 206,73 195,68 C200,66 205,65 210,65 Z" fill="#ffffff" class="ws-moon-crescent"/>
        </g>

        <!-- Nuages en dérive Parallaxe arrière -->
        <g class="ws-clouds-back">
            <path class="ws-cloud" d="M100,110 Q140,80 180,105 Q220,75 270,105 Q300,95 320,115 L100,115 Z"/>
            <path class="ws-cloud" d="M680,100 Q720,70 760,95 Q800,65 850,95 Q880,85 900,105 L680,105 Z"/>
            <path class="ws-cloud" d="M1200,105 Q1240,75 1280,100 Q1320,70 1370,100 Q1400,90 1420,110 L1200,110 Z"/>
        </g>

        <!-- LA SCÈNE FIXE UNIQUE (Collines, silhouette intemporelle de village & arbres) -->
        <g class="ws-permanent-landscape">
            <path class="ws-hill-back" d="M0,320 L0,210 Q280,140 560,195 Q840,250 1120,175 Q1280,135 1440,180 L1440,320 Z" fill="url(#wsTerrainBack)"/>
            <path class="ws-hill-front" d="M0,320 L0,240 Q220,190 440,225 Q660,260 880,210 Q1100,160 1320,230 Q1380,250 1440,235 L1440,320 Z" fill="url(#wsTerrainFront)"/>
            
            <g class="ws-skyline-accents">
                <circle cx="380" cy="215" r="14" class="ws-tree-crown"/>
                <rect x="378" y="215" width="4" height="15" class="ws-tree-trunk"/>
                <circle cx="410" cy="220" r="11" class="ws-tree-crown"/>
                <rect x="408" y="220" width="4" height="14" class="ws-tree-trunk"/>
                
                <!-- Clocher village lointain -->
                <polygon points="760,185 765,160 770,185" class="ws-spire"/>
                <rect x="760" y="185" width="10" height="30" class="ws-spire-tower"/>
                <rect x="772" y="195" width="22" height="20" class="ws-house"/>
                <polygon points="770,195 783,185 796,195" class="ws-roof"/>
                <rect x="740" y="198" width="18" height="17" class="ws-house"/>
                <polygon points="738,198 749,188 760,198" class="ws-roof"/>
                
                <circle cx="820" cy="212" r="13" class="ws-tree-crown"/>
                <rect x="818" y="212" width="4" height="15" class="ws-tree-trunk"/>
                <circle cx="1050" cy="195" r="16" class="ws-tree-crown"/>
                <rect x="1048" y="195" width="4" height="18" class="ws-tree-trunk"/>
            </g>
        </g>

        <!-- Nuages en dérive Parallaxe avant -->
        <g class="ws-clouds-front">
            <path class="ws-cloud" d="M-50,140 Q0,105 50,135 Q100,95 160,135 Q200,120 230,150 L-50,150 Z"/>
            <path class="ws-cloud" d="M480,130 Q530,95 580,125 Q640,85 710,125 Q750,110 780,140 L480,140 Z"/>
            <path class="ws-cloud" d="M1020,135 Q1070,100 1120,130 Q1180,90 1250,130 Q1290,115 1320,145 L1020,145 Z"/>
        </g>

        <!-- Éclair et Foudre (Orage) -->
        <g class="ws-lightning-group">
            <polygon class="ws-bolt" points="750,20 730,90 745,90 720,165 770,85 750,85 765,20"/>
            <circle cx="730" cy="100" r="60" class="ws-lightning-glow"/>
        </g>

        <!-- Rideau de Pluie -->
        <g class="ws-rain-group">
            <line x1="80" y1="20" x2="60" y2="80" class="ws-drop ws-drop-1"/>
            <line x1="240" y1="50" x2="220" y2="110" class="ws-drop ws-drop-2"/>
            <line x1="420" y1="10" x2="400" y2="70" class="ws-drop ws-drop-3"/>
            <line x1="580" y1="40" x2="560" y2="100" class="ws-drop ws-drop-4"/>
            <line x1="740" y1="15" x2="720" y2="75" class="ws-drop ws-drop-1"/>
            <line x1="900" y1="60" x2="880" y2="120" class="ws-drop ws-drop-2"/>
            <line x1="1100" y1="25" x2="1080" y2="85" class="ws-drop ws-drop-3"/>
            <line x1="1280" y1="45" x2="1260" y2="105" class="ws-drop ws-drop-4"/>
            <line x1="1400" y1="10" x2="1380" y2="70" class="ws-drop ws-drop-1"/>
        </g>

        <!-- Chute de Flocons de Neige -->
        <g class="ws-snow-group">
            <circle cx="160" cy="50" r="3.2" class="ws-flake ws-flake-1"/>
            <circle cx="340" cy="70" r="2.5" class="ws-flake ws-flake-2"/>
            <circle cx="520" cy="40" r="4.0" class="ws-flake ws-flake-3"/>
            <circle cx="700" cy="80" r="3.0" class="ws-flake ws-flake-4"/>
            <circle cx="880" cy="60" r="2.8" class="ws-flake ws-flake-1"/>
            <circle cx="1060" cy="75" r="3.5" class="ws-flake ws-flake-2"/>
            <circle cx="1240" cy="45" r="4.2" class="ws-flake ws-flake-3"/>
            <circle cx="1380" cy="65" r="2.5" class="ws-flake ws-flake-4"/>
        </g>

        <!-- Nappe de Brouillard -->
        <g class="ws-mist-group">
            <path class="ws-mist-wave ws-mist-1" d="M-100,280 Q300,260 700,285 Q1100,310 1540,280 L1540,320 L-100,320 Z"/>
            <path class="ws-mist-wave ws-mist-2" d="M-50,295 Q350,315 750,290 Q1150,265 1500,295 L1500,320 L-50,320 Z"/>
        </g>
    </svg>

    <!-- Superposition Carte Météo Frosted Glass (Positionnée à droite pour dégager le Soleil et la Lune) -->
    <div class="ws-overlay-card">
        <div class="ws-card-badge">
            <span class="ws-live-dot" aria-hidden="true"></span>
            <span class="ws-card-city" data-role="ws-city">{{defaultCity}}</span>
        </div>
        <div class="ws-card-main">
            <div class="ws-temp-wrap">
                <span class="ws-temp-value" data-role="ws-temp">--</span>
                <span class="ws-temp-unit">°C</span>
            </div>
            <div class="ws-condition-wrap">
                <span class="ws-condition-text" data-role="ws-condition">Chargement...</span>
                <div class="ws-meta-row">
                    <span class="ws-condition-sub" data-role="ws-sub">En direct</span>
                    <span class="ws-meta-sep" aria-hidden="true">·</span>
                    <span class="ws-wind-wrap" data-role="ws-wind-wrap" title="Sens et vitesse du vent">
                        <span class="ws-wind-arrow" data-role="ws-wind-arrow" aria-hidden="true">↑</span>
                        <span class="ws-wind-text" data-role="ws-wind">Vent --</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Sélecteur d'ambiances de simulation (RÉSERVÉ À L'ADMINISTRATEUR) -->
        {% if isAdmin %}
        <div class="ws-scene-picker" title="Simulation météo (Panneau Administrateur)">
            <button type="button" class="ws-pick-btn is-active" data-scene="auto" title="Météo réelle en direct">⚡ Réel</button>
            <span class="ws-pick-sep"></span>
            <button type="button" class="ws-pick-btn" data-scene="sunny" title="Ciel clair / Soleil">☀️</button>
            <button type="button" class="ws-pick-btn" data-scene="cloudy" title="Nuageux">⛅</button>
            <button type="button" class="ws-pick-btn" data-scene="rain" title="Pluie">🌧️</button>
            <button type="button" class="ws-pick-btn" data-scene="storm" title="Orage">⛈️</button>
            <button type="button" class="ws-pick-btn" data-scene="snow" title="Neige">❄️</button>
            <button type="button" class="ws-pick-btn" data-scene="fog" title="Brume / Brouillard">🌫️</button>
            <span class="ws-pick-sep"></span>
            <button type="button" class="ws-pick-time-btn" id="ws_time_toggle" title="Basculer entre Jour et Nuit">🌙 Nuit</button>
        </div>
        {% endif %}
    </div>
</div>

<div class="weather-living-scene ws-time-day weather-state-sunny" id="meteo_living_scene" data-city="{{defaultCity}}" data-country="{{defaultCountry}}" data-lat="{{latitude}}" data-lon="{{longitude}}" data-auto-geo="{% if autoGeolocate %}1{% else %}0{% endif %}">
    <!-- 1. Couche du Ciel dynamique -->
    <div class="ws-sky-layer" aria-hidden="true"></div>

    <!-- 2. Scène Vectorielle Vivante Unique (Proportions 1:1 parfaites pour garantir des astres et halos 100% ronds) -->
    <svg class="ws-svg-canvas" viewBox="0 0 1440 320" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
        <defs>
            <radialGradient id="wsSunGlow" cx="50%" cy="50%" r="50%">
                <stop offset="0%" stop-color="#fffbeb" stop-opacity="0.95"/>
                <stop offset="30%" stop-color="#fde047" stop-opacity="0.75"/>
                <stop offset="65%" stop-color="#f59e0b" stop-opacity="0.3"/>
                <stop offset="100%" stop-color="#ea580c" stop-opacity="0"/>
            </radialGradient>
            <radialGradient id="wsMoonGlow" cx="50%" cy="50%" r="50%">
                <stop offset="0%" stop-color="#ffffff" stop-opacity="0.95"/>
                <stop offset="40%" stop-color="#e0e7ff" stop-opacity="0.5"/>
                <stop offset="75%" stop-color="#93c5fd" stop-opacity="0.18"/>
                <stop offset="100%" stop-color="#818cf8" stop-opacity="0"/>
            </radialGradient>
            <radialGradient id="wsMoonGlowOuter" cx="50%" cy="50%" r="50%">
                <stop offset="0%" stop-color="#ffffff" stop-opacity="0.35"/>
                <stop offset="50%" stop-color="#c7d2fe" stop-opacity="0.12"/>
                <stop offset="100%" stop-color="#818cf8" stop-opacity="0"/>
            </radialGradient>
            <linearGradient id="wsTerrainBack" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="var(--ws-hill-back)" stop-opacity="0.88"/>
                <stop offset="60%" stop-color="var(--ws-hill-back)" stop-opacity="0.96"/>
                <stop offset="100%" stop-color="var(--ws-hill-front)" stop-opacity="1"/>
            </linearGradient>
            <linearGradient id="wsTerrainFront" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="var(--ws-hill-front)" stop-opacity="0.95"/>
                <stop offset="100%" stop-color="var(--ws-hill-front)" stop-opacity="1"/>
            </linearGradient>
        </defs>

        <!-- Nuit : Voûte céleste & constellation d'étoiles réalistes -->
        <g class="ws-stars-group">
            <circle cx="80" cy="55" r="1.2" class="ws-star ws-star-1"/>
            <circle cx="160" cy="35" r="1.8" class="ws-star ws-star-3"/>
            <circle cx="280" cy="70" r="1.1" class="ws-star ws-star-2"/>
            <circle cx="360" cy="25" r="2.2" class="ws-star ws-star-1"/>
            <circle cx="450" cy="45" r="1.6" class="ws-star ws-star-4"/>
            <circle cx="560" cy="65" r="1.2" class="ws-star ws-star-2"/>
            <circle cx="680" cy="30" r="2.0" class="ws-star ws-star-3"/>
            <circle cx="790" cy="75" r="1.4" class="ws-star ws-star-4"/>
            <circle cx="910" cy="40" r="1.9" class="ws-star ws-star-1"/>
            <circle cx="1020" cy="65" r="1.3" class="ws-star ws-star-2"/>
            <circle cx="1140" cy="25" r="2.4" class="ws-star ws-star-3"/>
            <circle cx="1260" cy="50" r="1.5" class="ws-star ws-star-4"/>
            <circle cx="1380" cy="35" r="1.7" class="ws-star ws-star-1"/>
        </g>

        <!-- Astre Solaire (Jour) - Parfaitement circulaire -->
        <g class="ws-sun-group">
            <circle cx="220" cy="90" r="95" fill="url(#wsSunGlow)" class="ws-sun-halo"/>
            <circle cx="220" cy="90" r="32" fill="#fef08a" class="ws-sun-disc"/>
            <g class="ws-sun-rays">
                <line x1="220" y1="45" x2="220" y2="25" stroke="#fef08a" stroke-width="2.5" stroke-linecap="round"/>
                <line x1="220" y1="135" x2="220" y2="155" stroke="#fef08a" stroke-width="2.5" stroke-linecap="round"/>
                <line x1="175" y1="90" x2="155" y2="90" stroke="#fef08a" stroke-width="2.5" stroke-linecap="round"/>
                <line x1="265" y1="90" x2="285" y2="90" stroke="#fef08a" stroke-width="2.5" stroke-linecap="round"/>
                <line x1="188" y1="58" x2="174" y2="44" stroke="#fef08a" stroke-width="2.5" stroke-linecap="round"/>
                <line x1="252" y1="122" x2="266" y2="136" stroke="#fef08a" stroke-width="2.5" stroke-linecap="round"/>
                <line x1="188" y1="122" x2="174" y2="136" stroke="#fef08a" stroke-width="2.5" stroke-linecap="round"/>
                <line x1="252" y1="58" x2="266" y2="44" stroke="#fef08a" stroke-width="2.5" stroke-linecap="round"/>
            </g>
        </g>

        <!-- Astre Lunaire (Nuit) - Halo circulaire pur et croissant fin -->
        <g class="ws-moon-group">
            <circle cx="220" cy="90" r="95" fill="url(#wsMoonGlowOuter)" class="ws-moon-halo-outer"/>
            <circle cx="220" cy="90" r="55" fill="url(#wsMoonGlow)" class="ws-moon-halo"/>
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
                <rect x="777" y="201" width="4" height="4" class="ws-window-glow"/>
                <rect x="785" y="201" width="4" height="4" class="ws-window-glow"/>
                <rect x="740" y="198" width="18" height="17" class="ws-house"/>
                <polygon points="738,198 749,188 760,198" class="ws-roof"/>
                <rect x="745" y="203" width="3" height="3" class="ws-window-glow"/>
                
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

        <!-- Éclair et Foudre (Orage uniquement) -->
        <g class="ws-lightning-group" aria-hidden="true">
            <polygon class="ws-bolt" points="750,20 730,90 745,90 720,165 770,85 750,85 765,20" fill="#fef08a"/>
            <circle cx="730" cy="100" r="60" class="ws-lightning-glow" fill="rgba(254, 240, 138, 0.35)"/>
        </g>

        <!-- Rideau de Pluie (Pluie & Orage uniquement) -->
        <g class="ws-rain-group" aria-hidden="true">
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

        <!-- Chute de Flocons de Neige (Neige uniquement) -->
        <g class="ws-snow-group" aria-hidden="true">
            <circle cx="160" cy="50" r="3.2" class="ws-flake ws-flake-1"/>
            <circle cx="340" cy="70" r="2.5" class="ws-flake ws-flake-2"/>
            <circle cx="520" cy="40" r="4.0" class="ws-flake ws-flake-3"/>
            <circle cx="700" cy="80" r="3.0" class="ws-flake ws-flake-4"/>
            <circle cx="880" cy="60" r="2.8" class="ws-flake ws-flake-1"/>
            <circle cx="1060" cy="75" r="3.5" class="ws-flake ws-flake-2"/>
            <circle cx="1240" cy="45" r="4.2" class="ws-flake ws-flake-3"/>
            <circle cx="1380" cy="65" r="2.5" class="ws-flake ws-flake-4"/>
        </g>

        <!-- Nappe de Brouillard (Brouillard uniquement) -->
        <g class="ws-mist-group" aria-hidden="true">
            <path class="ws-mist-wave ws-mist-1" d="M-100,280 Q300,260 700,285 Q1100,310 1540,280 L1540,320 L-100,320 Z"/>
            <path class="ws-mist-wave ws-mist-2" d="M-50,295 Q350,315 750,290 Q1150,265 1500,295 L1500,320 L-50,320 Z"/>
        </g>
    </svg>

    <!-- Carte météo compacte et discrète en haut à droite (dégage complètement soleil, lune et village) -->
    <div class="ws-overlay-card">
        <div class="ws-card-header">
            <div class="ws-card-badge">
                <span class="ws-live-dot" aria-hidden="true"></span>
                <span class="ws-card-city" data-role="ws-city">{{defaultCity}}</span>
            </div>
            <span class="ws-condition-sub" data-role="ws-sub">En direct</span>
        </div>

        <div class="ws-card-body">
            <div class="ws-temp-wrap">
                <span class="ws-temp-value" data-role="ws-temp">--</span>
                <span class="ws-temp-unit">°C</span>
            </div>
            <div class="ws-condition-text" data-role="ws-condition">Chargement...</div>
        </div>

        <!-- Micro-indicateurs météorologiques précis -->
        <div class="ws-metrics-grid">
            <div class="ws-metric-item" title="Vitesse et sens du vent">
                <span class="ws-metric-icon ws-wind-arrow" data-role="ws-wind-arrow" aria-hidden="true">↑</span>
                <span class="ws-metric-val" data-role="ws-wind">-- km/h</span>
            </div>
            <div class="ws-metric-item" title="Humidité relative de l'air">
                <span class="ws-metric-icon">💧</span>
                <span class="ws-metric-val" data-role="ws-humidity">--%</span>
            </div>
            <div class="ws-metric-item" title="Probabilité de précipitations">
                <span class="ws-metric-icon">🌧️</span>
                <span class="ws-metric-val" data-role="ws-precip">0%</span>
            </div>
            <div class="ws-metric-item" title="Pression atmosphérique">
                <span class="ws-metric-icon">🧭</span>
                <span class="ws-metric-val" data-role="ws-pressure">-- hPa</span>
            </div>
        </div>
    </div>
</div>

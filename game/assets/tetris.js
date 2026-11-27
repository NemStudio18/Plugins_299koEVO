/**
 * Authentic Game Boy & Game Boy Color Tetris Engine for 299ko EVO
 * Strictly Zero External Dependencies - Fixed Timestep Loop at 59.73 Hz
 */

(function () {
    'use strict';

    // Game Boy Original Constants
    const W = 10, H = 18, FPS = 59.7275;
    const GRAV = [53, 49, 45, 41, 37, 33, 28, 22, 17, 11, 10, 9, 8, 7, 6, 6, 5, 5, 4, 4, 3];
    const PTS = [0, 40, 100, 300, 1200];
    const DAS_WAIT = 24, DAS_REP = 9, CLEAR_FR = 20, SOFT = 3;

    // Tetromino definitions (0: I, 1: J, 2: L, 3: O, 4: S, 5: T, 6: Z)
    const PIECE_NAMES = ['i', 'j', 'l', 'o', 's', 't', 'z'];
    const SHAPES = [
        [[0, 0, 0, 0], [1, 1, 1, 1], [0, 0, 0, 0], [0, 0, 0, 0]], // I
        [[1, 0, 0], [1, 1, 1], [0, 0, 0]],                         // J
        [[0, 0, 1], [1, 1, 1], [0, 0, 0]],                         // L
        [[1, 1], [1, 1]],                                         // O
        [[0, 1, 1], [1, 1, 0], [0, 0, 0]],                         // S
        [[0, 1, 0], [1, 1, 1], [0, 0, 0]],                         // T
        [[1, 1, 0], [0, 1, 1], [0, 0, 0]]                          // Z
    ];

    const $ = id => document.getElementById(id);
    const boardEl = $('gb-board');
    const nextEl = $('gb-next');
    if (!boardEl || !nextEl) return;

    // Build Board Matrix Cells
    const cells = [], nextCells = [];
    boardEl.innerHTML = '';
    for (let i = 0; i < W * H; i++) {
        const d = document.createElement('div');
        d.className = 'evo-gb-cell';
        boardEl.appendChild(d);
        cells.push(d);
    }
    nextEl.innerHTML = '';
    for (let i = 0; i < 16; i++) {
        const d = document.createElement('div');
        d.className = 'evo-gb-cell';
        nextEl.appendChild(d);
        nextCells.push(d);
    }

    // State Variables
    let g = null;
    let state = 'title'; // 'title', 'play', 'pause', 'clear', 'over'
    let keys = {};
    let acc = 0, lastTime = 0;
    let currentPalette = localStorage.getItem('game_tetris_palette') || 'gbc_color';
    let soundEnabled = localStorage.getItem('game_tetris_sound') !== '0';

    // Player Identification & Token
    let playerToken = localStorage.getItem('game_tetris_token');
    if (!playerToken) {
        playerToken = 'p_' + Math.random().toString(36).substring(2, 10) + Date.now().toString(36);
        localStorage.setItem('game_tetris_token', playerToken);
    }

    let playerName = localStorage.getItem('game_tetris_pseudo') || 'JOUEUR1';
    let personalBest = parseInt(localStorage.getItem('game_tetris_best') || '0', 10);
    let totalGames = parseInt(localStorage.getItem('game_tetris_games') || '0', 10);

    // Audio Synthesizer (Web Audio API)
    let audioCtx = null;
    function playBeep(freq, type, duration, gainVal) {
        if (!soundEnabled) return;
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (audioCtx.state === 'suspended') audioCtx.resume();
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = type || 'square';
            osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
            gain.gain.setValueAtTime(gainVal || 0.12, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration);
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            osc.stop(audioCtx.currentTime + duration);
        } catch (e) {}
    }

    const sound = {
        move: () => playBeep(140, 'square', 0.03, 0.08),
        turn: () => playBeep(260, 'square', 0.04, 0.1),
        drop: () => playBeep(90, 'triangle', 0.02, 0.06),
        lock: () => playBeep(100, 'square', 0.06, 0.14),
        clear: () => {
            playBeep(440, 'square', 0.08, 0.12);
            setTimeout(() => playBeep(660, 'square', 0.09, 0.12), 70);
            setTimeout(() => playBeep(880, 'square', 0.12, 0.14), 140);
        },
        tetris: () => {
            playBeep(523, 'square', 0.08, 0.15);
            setTimeout(() => playBeep(659, 'square', 0.08, 0.15), 80);
            setTimeout(() => playBeep(784, 'square', 0.08, 0.15), 160);
            setTimeout(() => playBeep(1046, 'square', 0.22, 0.18), 240);
        },
        over: () => {
            playBeep(440, 'sawtooth', 0.1, 0.15);
            setTimeout(() => playBeep(370, 'sawtooth', 0.12, 0.15), 100);
            setTimeout(() => playBeep(311, 'sawtooth', 0.14, 0.15), 220);
            setTimeout(() => playBeep(260, 'sawtooth', 0.3, 0.18), 360);
        }
    };

    // Matrix Rotation without wall-kick (Original Game Boy)
    function rot(m, cw) {
        const n = m.length;
        return m.map((_, y) => m.map((_, x) => cw ? m[n - 1 - x][y] : m[x][n - 1 - y]));
    }

    // Collision detection
    function hit(m, px, py) {
        for (let y = 0; y < m.length; y++) {
            for (let x = 0; x < m.length; x++) {
                if (m[y][x]) {
                    const X = px + x, Y = py + y;
                    if (X < 0 || X >= W || Y >= H || (Y >= 0 && g.f[Y][X])) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    // Authentic Game Boy Randomizer: rerolls once on duplicate
    function rnd() {
        let r = Math.floor(Math.random() * 7);
        if (r === g.prev) {
            r = Math.floor(Math.random() * 7);
        }
        g.prev = r;
        return r;
    }

    // Spawn new falling piece
    function spawn() {
        g.type = g.n;
        g.m = SHAPES[g.type].map(r => [...r]);
        g.x = Math.floor((W - g.m.length) / 2);
        g.y = g.m.length === 4 ? -1 : 0;
        g.n = rnd();
        g.t = 0;
        g.dir = 0;
        g.das = 0;

        if (hit(g.m, g.x, g.y)) {
            gameOver();
        }
    }

    // Start a new game
    function start() {
        g = {
            f: Array.from({ length: H }, () => Array(W).fill(0)), // field
            fc: Array.from({ length: H }, () => Array(W).fill('')), // field colors
            s: 0,
            l: 0,
            lv: 0,
            prev: -1,
            n: 0,
            type: 0,
            t: 0,
            dir: 0,
            das: 0,
            clr: null,
            ct: 0
        };
        g.n = rnd();
        spawn();
        state = 'play';
        $('gb-status').textContent = '';
        draw();
    }

    // Game Over Handler
    function gameOver() {
        state = 'over';
        sound.over();
        $('gb-status').textContent = 'GAME OVER • START pour rejouer';

        totalGames++;
        localStorage.setItem('game_tetris_games', totalGames);
        $('player-total-games').textContent = totalGames;

        if (g.s > personalBest) {
            personalBest = g.s;
            localStorage.setItem('game_tetris_best', personalBest);
            $('player-best-score').textContent = personalBest;
        }

        if (g.s > 0) {
            const finalScore = g.s;
            const finalLines = g.l;
            const finalLevel = g.lv;

            setTimeout(() => {
                const asked = prompt('Partie terminée ! Score : ' + finalScore + ' pts\nTon pseudo pour le classement :', playerName);
                if (asked !== null) {
                    const clean = asked.trim().toUpperCase().replace(/[^A-Z0-9_\-\s]/g, '').substring(0, 12);
                    if (clean.length > 0) {
                        playerName = clean;
                        localStorage.setItem('game_tetris_pseudo', playerName);
                        $('player-name-display').textContent = playerName;
                    }
                    submitScore(playerName, finalScore, finalLines, finalLevel);
                }
            }, 100);
        }
    }

    // Submit score to 299ko API
    function submitScore(name, score, lines, level) {
        fetch('/game/api/score', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                name: name,
                score: score,
                lines: lines,
                level: level,
                palette: currentPalette,
                player_token: playerToken
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.ok) {
                loadLeaderboards();
            }
        })
        .catch(() => {});
    }

    // Lock piece on board
    function lock() {
        sound.lock();
        g.m.forEach((r, y) => r.forEach((v, x) => {
            if (v && g.y + y >= 0) {
                g.f[g.y + y][g.x + x] = 1;
                g.fc[g.y + y][g.x + x] = PIECE_NAMES[g.type];
            }
        }));

        const full = [];
        g.f.forEach((r, y) => {
            if (r.every(v => v)) full.push(y);
        });

        if (full.length) {
            g.clr = full;
            g.ct = CLEAR_FR;
            state = 'clear';
            g.m = null;
            if (full.length === 4) {
                sound.tetris();
            } else {
                sound.clear();
            }
        } else {
            spawn();
        }
    }

    // Finish row clear animation and award points
    function finishClear() {
        const n = g.clr.length;
        g.f = g.f.filter((_, y) => !g.clr.includes(y));
        g.fc = g.fc.filter((_, y) => !g.clr.includes(y));
        while (g.f.length < H) {
            g.f.unshift(Array(W).fill(0));
            g.fc.unshift(Array(W).fill(''));
        }

        g.s += PTS[n] * (g.lv + 1);
        g.l += n;
        g.lv = Math.max(g.lv, Math.floor(g.l / 10));

        g.clr = null;
        state = 'play';
        spawn();
    }

    // Move left/right
    function move(dx) {
        if (!hit(g.m, g.x + dx, g.y)) {
            g.x += dx;
            sound.move();
        }
    }

    // Turn piece
    function turn(cw) {
        if (state !== 'play') return;
        const r = rot(g.m, cw);
        if (!hit(r, g.x, g.y)) {
            g.m = r;
            sound.turn();
        }
    }

    // Fixed timestep frame tick (runs at ~59.73 Hz)
    function tick() {
        if (state === 'clear') {
            if (--g.ct <= 0) finishClear();
            return;
        }
        if (state !== 'play') return;

        // Delayed Auto Shift (DAS)
        const d = (keys.ArrowLeft ? -1 : 0) + (keys.ArrowRight ? 1 : 0);
        if (d !== g.dir) {
            g.dir = d;
            g.das = 0;
            if (d) move(d);
        } else if (d) {
            g.das++;
            if (g.das >= DAS_WAIT && (g.das - DAS_WAIT) % DAS_REP === 0) {
                move(d);
            }
        }

        // Gravity Drop
        g.t++;
        const speedIndex = Math.min(g.lv, 20);
        const lim = keys.ArrowDown ? Math.min(SOFT, GRAV[speedIndex]) : GRAV[speedIndex];

        if (g.t >= lim) {
            g.t = 0;
            if (!hit(g.m, g.x, g.y + 1)) {
                g.y++;
                if (keys.ArrowDown) {
                    g.s++;
                    sound.drop();
                }
            } else {
                lock();
            }
        }
    }

    // Render loop
    function draw() {
        const v = g.f.map(r => [...r]);
        const vc = g.fc.map(r => [...r]);

        if (g.m) {
            g.m.forEach((r, y) => r.forEach((c, x) => {
                if (c && g.y + y >= 0 && g.y + y < H) {
                    v[g.y + y][g.x + x] = 1;
                    vc[g.y + y][g.x + x] = PIECE_NAMES[g.type];
                }
            }));
        }

        const blink = g.clr && Math.floor(g.ct / 4) % 2;

        v.forEach((r, y) => r.forEach((c, x) => {
            const e = cells[y * W + x];
            if (c) {
                if (g.clr && g.clr.includes(y) && blink) {
                    e.className = 'evo-gb-cell flash';
                } else {
                    const pieceClass = vc[y][x] ? ' piece-' + vc[y][x] : '';
                    e.className = 'evo-gb-cell on' + pieceClass;
                }
            } else {
                e.className = 'evo-gb-cell';
            }
        }));

        // Next Piece Preview
        const nm = SHAPES[g.n];
        nextCells.forEach((e, i) => {
            const y = i >> 2, x = i & 3;
            if (nm[y] && nm[y][x]) {
                const pieceClass = ' piece-' + PIECE_NAMES[g.n];
                e.className = 'evo-gb-cell on' + pieceClass;
            } else {
                e.className = 'evo-gb-cell';
            }
        });

        $('gb-lv').textContent = g.lv;
        $('gb-li').textContent = g.l;
        $('gb-sc').textContent = g.s;
    }

    // Master game loop with fixed-timestep accumulator
    function loop(t) {
        if (!lastTime) lastTime = t;
        acc += (t - lastTime) / 1000;
        lastTime = t;
        if (acc > 0.25) acc = 0.25;

        while (acc >= 1 / FPS) {
            tick();
            acc -= 1 / FPS;
        }

        if (g) draw();
        requestAnimationFrame(loop);
    }

    // Keyboard controls
    window.addEventListener('keydown', e => {
        if (['ArrowLeft', 'ArrowRight', 'ArrowDown', 'ArrowUp', ' '].includes(e.key)) {
            e.preventDefault();
        }
        if (e.repeat) return;
        keys[e.key] = true;

        if (e.key === 'Enter') {
            if (state === 'title' || state === 'over') {
                start();
            }
        } else if (e.key === 'p' || e.key === 'P') {
            if (state === 'play') {
                state = 'pause';
                $('gb-status').textContent = 'PAUSE';
            } else if (state === 'pause') {
                state = 'play';
                $('gb-status').textContent = '';
            }
        } else if (e.key === 'z' || e.key === 'Z' || e.key === 'a' || e.key === 'A') {
            turn(false); // Counter-Clockwise (B button)
        } else if (e.key === 'x' || e.key === 'X' || e.key === 'b' || e.key === 'B' || e.key === 'ArrowUp') {
            turn(true); // Clockwise (A button)
        }
    });

    window.addEventListener('keyup', e => {
        keys[e.key] = false;
    });

    // Touch & Game Boy Buttons Listeners
    document.querySelectorAll('[data-gb-k]').forEach(btn => {
        const k = btn.dataset.gbK;
        btn.addEventListener('pointerdown', e => {
            e.preventDefault();
            window.dispatchEvent(new KeyboardEvent('keydown', { key: k }));
        });
        btn.addEventListener('pointerup', () => {
            window.dispatchEvent(new KeyboardEvent('keyup', { key: k }));
        });
        btn.addEventListener('pointerleave', () => {
            window.dispatchEvent(new KeyboardEvent('keyup', { key: k }));
        });
    });

    // Palette Switcher Handlers
    function setPalette(pal) {
        currentPalette = pal;
        localStorage.setItem('game_tetris_palette', pal);
        document.documentElement.setAttribute('data-palette', pal);
        const stage = $('gb-stage');
        if (stage) stage.setAttribute('data-palette', pal);

        document.querySelectorAll('.evo-gb-tool-btn[data-palette-btn]').forEach(b => {
            b.classList.toggle('is-active', b.dataset.paletteBtn === pal);
        });
        if (g) draw();
    }

    document.querySelectorAll('.evo-gb-tool-btn[data-palette-btn]').forEach(b => {
        b.addEventListener('click', () => setPalette(b.dataset.paletteBtn));
    });

    // Sound Toggle Handler
    const soundBtn = $('btn-toggle-sound');
    if (soundBtn) {
        soundBtn.addEventListener('click', () => {
            soundEnabled = !soundEnabled;
            localStorage.setItem('game_tetris_sound', soundEnabled ? '1' : '0');
            soundBtn.innerHTML = soundEnabled 
                ? '<i class="fa-solid fa-volume-high" aria-hidden="true"></i> Son : Activé'
                : '<i class="fa-solid fa-volume-xmark" aria-hidden="true"></i> Son : Muet';
        });
    }

    // Player Pseudo Edit Handler
    const editPseudoBtn = $('btn-edit-pseudo');
    if (editPseudoBtn) {
        editPseudoBtn.addEventListener('click', () => {
            const p = prompt('Entre ton pseudo arcade (max 12 car.) :', playerName);
            if (p !== null) {
                const clean = p.trim().toUpperCase().replace(/[^A-Z0-9_\-\s]/g, '').substring(0, 12);
                if (clean) {
                    playerName = clean;
                    localStorage.setItem('game_tetris_pseudo', playerName);
                    $('player-name-display').textContent = playerName;
                }
            }
        });
    }

    // Leaderboard Tabs Handler
    let currentTab = 'top';
    document.querySelectorAll('.evo-lead-tab').forEach(t => {
        t.addEventListener('click', () => {
            document.querySelectorAll('.evo-lead-tab').forEach(x => x.classList.remove('is-active'));
            t.classList.add('is-active');
            currentTab = t.dataset.tab;
            renderLeaderboard();
        });
    });

    let cachedScores = { top: [], today: [], player: [] };

    function renderLeaderboard() {
        const listEl = $('lead-list');
        if (!listEl) return;
        const items = cachedScores[currentTab] || [];

        if (items.length === 0) {
            listEl.innerHTML = '<li class="evo-lead-empty"><i class="fa-solid fa-trophy" style="font-size: 24px; opacity: 0.4; margin-bottom: 8px; display: block;"></i>Aucun score enregistré dans cette catégorie.</li>';
            return;
        }

        listEl.innerHTML = items.map((item, idx) => {
            const rankClass = idx === 0 ? 'top-1' : (idx === 1 ? 'top-2' : (idx === 2 ? 'top-3' : ''));
            return `<li class="evo-lead-item">
                <span class="evo-lead-rank ${rankClass}">#${idx + 1}</span>
                <span class="evo-lead-user">${item.name}</span>
                <div>
                    <span class="evo-lead-pts">${item.score.toLocaleString()}</span>
                    <span class="evo-lead-sub">Niv. ${item.level} • ${item.lines} lig.</span>
                </div>
            </li>`;
        }).join('');
    }

    function loadLeaderboards() {
        fetch('/game/api/scores?token=' + encodeURIComponent(playerToken))
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    cachedScores = data;
                    renderLeaderboard();
                }
            })
            .catch(() => {});
    }

    // Initialize UI
    setPalette(currentPalette);
    $('player-name-display').textContent = playerName;
    $('player-best-score').textContent = personalBest;
    $('player-total-games').textContent = totalGames;

    // Start initial board state and animation loop
    g = {
        f: Array.from({ length: H }, () => Array(W).fill(0)),
        fc: Array.from({ length: H }, () => Array(W).fill('')),
        s: 0, l: 0, lv: 0, n: 0, m: null, clr: null
    };
    draw();
    loadLeaderboards();
    requestAnimationFrame(t => {
        lastTime = t;
        loop(t);
    });

})();

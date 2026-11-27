<div class="evo-admin-header">
    <div class="evo-admin-header-title">
        <h1><i class="fa-solid fa-gamepad"></i> {{ runPlugin.getMainTitle() }}</h1>
        <p class="evo-text-muted">Gestion du jeu Tetris, leaderboard multijoueur et personnalisation de l'affichage.</p>
    </div>
    <div class="evo-admin-header-actions">
        <a href="/game" target="_blank" rel="noopener" class="button">
            <i class="fa-solid fa-play"></i> Tester le jeu en direct
        </a>
    </div>
</div>

<!-- Stats Row -->
<div class="evo-grid-4" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
    <div class="evo-stat-card" style="background: var(--evo-card-bg, #fff); border: 1px solid var(--evo-border, #e2e8f0); border-radius: 10px; padding: 18px; text-align: center;">
        <span style="font-size: 0.8rem; color: var(--evo-text-muted, #64748b); text-transform: uppercase; font-weight: 600;">Parties Jouées</span>
        <div style="font-size: 1.8rem; font-weight: 800; color: var(--evo-accent, #6366f1); margin-top: 6px;">{{ totalGames }}</div>
    </div>
    <div class="evo-stat-card" style="background: var(--evo-card-bg, #fff); border: 1px solid var(--evo-border, #e2e8f0); border-radius: 10px; padding: 18px; text-align: center;">
        <span style="font-size: 0.8rem; color: var(--evo-text-muted, #64748b); text-transform: uppercase; font-weight: 600;">High Score</span>
        <div style="font-size: 1.8rem; font-weight: 800; color: #16a34a; margin-top: 6px;">{{ highScore }}</div>
    </div>
    <div class="evo-stat-card" style="background: var(--evo-card-bg, #fff); border: 1px solid var(--evo-border, #e2e8f0); border-radius: 10px; padding: 18px; text-align: center;">
        <span style="font-size: 0.8rem; color: var(--evo-text-muted, #64748b); text-transform: uppercase; font-weight: 600;">Lignes Complétées</span>
        <div style="font-size: 1.8rem; font-weight: 800; color: var(--evo-text, #1e293b); margin-top: 6px;">{{ totalLines }}</div>
    </div>
    <div class="evo-stat-card" style="background: var(--evo-card-bg, #fff); border: 1px solid var(--evo-border, #e2e8f0); border-radius: 10px; padding: 18px; text-align: center;">
        <span style="font-size: 0.8rem; color: var(--evo-text-muted, #64748b); text-transform: uppercase; font-weight: 600;">Joueurs Uniques</span>
        <div style="font-size: 1.8rem; font-weight: 800; color: var(--evo-accent, #6366f1); margin-top: 6px;">{{ uniquePlayers }}</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
    <!-- Leaderboard Table -->
    <div class="evo-card" style="background: var(--evo-card-bg, #fff); border: 1px solid var(--evo-border, #e2e8f0); border-radius: 10px; padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h2 style="font-size: 1.15rem; margin: 0;"><i class="fa-solid fa-trophy" style="color: #eab308;"></i> Meilleurs Scores Enregistrés</h2>
            {% IF scores %}
            <a href="/admin/game/reset/{{ csrfToken }}" class="button danger small" onclick="return confirm('Êtes-vous sûr de vouloir réinitialiser tout le classement ?');">
                <i class="fa-solid fa-trash"></i> Vider le classement
            </a>
            {% ENDIF %}
        </div>

        {% IF scores %}
        <div style="overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--evo-border, #e2e8f0); text-align: left;">
                        <th style="padding: 10px 8px;">#</th>
                        <th style="padding: 10px 8px;">Joueur</th>
                        <th style="padding: 10px 8px;">Score</th>
                        <th style="padding: 10px 8px;">Lignes</th>
                        <th style="padding: 10px 8px;">Niveau</th>
                        <th style="padding: 10px 8px;">Date</th>
                        <th style="padding: 10px 8px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    {% FOR score IN scores %}
                    <tr style="border-bottom: 1px solid var(--evo-border, #e2e8f0);">
                        <td style="padding: 10px 8px; font-weight: 700;">{{ score.index + 1 }}</td>
                        <td style="padding: 10px 8px; font-weight: 600;">{{ score.getPlayerName() }}</td>
                        <td style="padding: 10px 8px; font-family: monospace; font-size: 1.05rem; font-weight: 700; color: var(--evo-accent, #6366f1);">
                            {{ score.getScore() }}
                        </td>
                        <td style="padding: 10px 8px;">{{ score.getLines() }}</td>
                        <td style="padding: 10px 8px;">{{ score.getLevel() }}</td>
                        <td style="padding: 10px 8px; font-size: 0.85rem; color: var(--evo-text-muted, #64748b);">
                            {{ score.getCreatedAt() }}
                        </td>
                        <td style="padding: 10px 8px; text-align: right;">
                            <a href="/admin/game/delete/{{ score.getId() }}/{{ csrfToken }}" class="button small danger" onclick="return confirm('Supprimer ce score ?');" title="Supprimer">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    {% ENDFOR %}
                </tbody>
            </table>
        </div>
        {% ELSE %}
        <p style="text-align: center; padding: 40px; color: var(--evo-text-muted, #64748b);">
            Aucun score enregistré pour l'instant. Les scores apparaîtront dès la première partie jouée !
        </p>
        {% ENDIF %}
    </div>

    <!-- Settings Form -->
    <div class="evo-card" style="background: var(--evo-card-bg, #fff); border: 1px solid var(--evo-border, #e2e8f0); border-radius: 10px; padding: 20px;">
        <h2 style="font-size: 1.15rem; margin-top: 0; margin-bottom: 16px;"><i class="fa-solid fa-sliders"></i> Paramètres du Jeu</h2>
        <form method="post" action="/admin/game/config">
            <input type="hidden" name="_csrf" value="{{ csrfToken }}">

            <p>
                <label for="nav_label" style="display: block; font-weight: 600; margin-bottom: 6px;">Libellé dans le menu :</label>
                <input type="text" id="nav_label" name="nav_label" value="{{ config.nav_label }}" style="width: 100%; box-sizing: border-box;" required>
            </p>

            <p>
                <label for="default_palette" style="display: block; font-weight: 600; margin-bottom: 6px;">Palette par défaut :</label>
                <select id="default_palette" name="default_palette" style="width: 100%; box-sizing: border-box;">
                    <option value="gbc_color" {% IF config.default_palette == 'gbc_color' %}selected{% ENDIF %}>Game Boy Color (Vibrante & Colorée)</option>
                    <option value="gb_classic" {% IF config.default_palette == 'gb_classic' %}selected{% ENDIF %}>Game Boy Originale (DMG-01 Vert/Olive)</option>
                    <option value="gb_pocket" {% IF config.default_palette == 'gb_pocket' %}selected{% ENDIF %}>Game Boy Pocket (Monochrome Silver)</option>
                </select>
            </p>

            <p>
                <label for="max_leaderboard" style="display: block; font-weight: 600; margin-bottom: 6px;">Taille du Top affiché :</label>
                <input type="number" id="max_leaderboard" name="max_leaderboard" value="{{ config.max_leaderboard }}" min="5" max="50" style="width: 100%; box-sizing: border-box;">
            </p>

            <p style="margin-top: 16px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="sound_enabled" value="1" {% IF config.sound_enabled == 1 %}checked{% ENDIF %}>
                    <span>Activer les effets sonores 8-bit par défaut</span>
                </label>
            </p>

            <p style="margin-top: 24px;">
                <button type="submit" class="button primary" style="width: 100%;">
                    <i class="fa-solid fa-floppy-disk"></i> Enregistrer les paramètres
                </button>
            </p>
        </form>
    </div>
</div>

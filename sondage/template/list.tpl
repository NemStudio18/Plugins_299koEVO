<section>
    {% if count(sondages) > 0 %}
        <div class="sondages-list">
            {% for sondage in sondages %}
                <div class="sondage-item">
                    <h3><a href="{{ sondage.url }}">{{ sondage.title }}</a></h3>
                    <p>{{ sondage.votesCountText }}</p>
                    {% if sondage.hasVoted %}
                        <div class="already-voted-notice">
                            <span class="already-voted-badge">{{ Lang.sondage.already-voted }}</span>
                            {% if sondage.optionsStats|length > 0 %}
                                <div class="sondage-stats-preview">
                                    <strong>{{ Lang.sondage.current-stats }}:</strong>
                                    <div class="stats-visualization">
                                        {% for stat in sondage.optionsStats %}
                                            <div class="stat-bar-item">
                                                <div class="stat-bar-label">{{ stat.text }}</div>
                                                <div class="stat-bar-container">
                                                    <div class="stat-bar-fill" style="width: {{ stat.percentage }}%">
                                                        <span class="stat-bar-value">{{ stat.votes }} ({{ stat.percentage }}%)</span>
                                                    </div>
                                                </div>
                                            </div>
                                        {% endfor %}
                                    </div>
                                </div>
                            {% endif %}
                        </div>
                    {% endif %}
                </div>
            {% endfor %}
        </div>
    {% else %}
        <p>{{ Lang.sondage.no-sondage }}</p>
    {% endif %}
</section>

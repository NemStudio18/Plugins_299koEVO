<section>
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ listUrl }}" class="button" style="display: inline-flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i>
            <span>{{ Lang.sondage.back-to-list }}</span>
        </a>
    </div>
    
    {% if hasVoted %}
        <div class="sondage-results">
            <h3>{{ Lang.sondage.results }}</h3>
            <div class="sondage-total">{{ totalVotesText }}</div>
            {% for option in optionsData %}
                <div class="sondage-option-result">
                    <div class="option-header">
                        <span class="option-text">{{ option.text }}</span>
                        <span class="option-stats">{{ option.votes }} vote(s) - {{ option.percentage }}%</span>
                    </div>
                    <div class="option-bar">
                        <div class="option-progress" style="width: {{ option.percentage }}%"></div>
                    </div>
                </div>
            {% endfor %}
        </div>
    {% else %}
        <form method="post" action="{{ voteUrl }}">
            {{ SHOW.tokenField }}
            
            <label for="email">{{ Lang.sondage.email }} <span class="required">*</span></label>
            <input type="email" name="email" id="email" required placeholder="{{ Lang.sondage.email-placeholder }}" />
            <small>{{ Lang.sondage.email-help }}</small>
            
            <div class="email-consent-checkbox" style="margin: 1rem 0; padding: 1rem; background: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 4px;">
                <label style="display: flex; align-items: flex-start; gap: 0.5rem; cursor: pointer; font-weight: normal;">
                    <input type="checkbox" name="email_consent_stats" value="1" style="margin-top: 0.2rem; flex-shrink: 0;" />
                    <span>{{ Lang.sondage.email-consent-stats }}</span>
                </label>
                <small class="rgpd-text" style="display: block; margin-top: 0.5rem; margin-left: 1.75rem; font-size: 0.85rem; line-height: 1.5; color: #555;">{{ Lang.sondage.email-consent-stats-help }}</small>
            </div>
            
            <div class="sondage-options">
                <label class="sondage-options-label">{{ Lang.sondage.options }}:</label>
                {% for option in optionsData %}
                    <label class="sondage-option">
                        <input type="radio" name="option" value="{{ option.index }}" required />
                        <span>{{ option.text }}</span>
                    </label>
                {% endfor %}
            </div>
            
            <div class="rgpd-checkbox">
                <label>
                    <input type="checkbox" name="rgpd_accept" value="1" required />
                    <span>{{ Lang.sondage.rgpd-accept }}</span>
                </label>
                <small class="rgpd-text">{{ Lang.sondage.rgpd-text }}</small>
            </div>
            
            <button type="submit" class="button">{{ Lang.sondage.vote }}</button>
        </form>
    {% endif %}
</section>

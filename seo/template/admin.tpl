<form method="post" action="{{ ROUTER.generate("seo-admin-save")}}">
    {{ show.tokenField() }}
    <section>
        <header>{{ Lang.seo.display }}</header>
        <p>
            <label for="position">{{ Lang.seo.menu-position }}</label><br>
            <select name="position" id="position">
                <option value="menu" {% if position == "menu" %}selected{% endif %}>{{ Lang.seo.nav-menu }}</option>
                <option value="footer" {% if position == "footer" %}selected{% endif %}>{{ Lang.seo.top-footer-page }}</option>
                <option value="endfooter" {% if position == "endfooter" %}selected{% endif %}>{{ Lang.seo.bottom-footer-page }}</option>
                <option value="float" {% if position == "float" %}selected{% endif %}>{{ Lang.seo.float }}</option>
            </select>
        </p>
    </section>
    <section>
        <header>{{ Lang.seo.google }}</header>
        <p>
            <label for="trackingId">{{ Lang.seo.analytics.id }}</label><br>
            <input type="text" name="trackingId" id="trackingId" value="{{ runPlugin.getConfigVal("trackingId") }}" />
        </p>
        <p>
            <label for="wt">{{ Lang.seo.analytics.meta }}</label><br>
            <input type="text" name="wt" id="wt" value="{{ runPlugin.getConfigVal("wt") }}" />
        </p>
    </section>
    <section>
        <header>{{ Lang.seo.socials-links }}</header>
        {% set social = seoGetSocialVars() %}
        {% for k, v in social %}
        <p>
            <label for="{{ v }}"><i class="fa-brands fa-{{ v }}"></i>&nbsp;{{ k }}</label><br>
            <input placeholder="" type="text" name="{{ v }}" id="{{ v }}" value="{{ runPlugin.getConfigVal(v) }}" />
        </p>
        {% endfor %}
    </section>

    <section>
        <header>{{ Lang.seo.share-settings }}</header>
        <p>{{ Lang.seo.share-settings.help }}</p>
        <div class="seo-network">
            <label>
                <input type="checkbox" name="facebook_enabled" {% if socialConfig.facebook.enabled %}checked{% endif %}>
                {{ Lang.seo.facebook.enabled }}
            </label>
            <div class="seo-network-fields">
                <input type="text" name="facebook_appId" value="{{ socialConfig.facebook.appId }}" placeholder="{{ Lang.seo.facebook.appId }}">
                <input type="text" name="facebook_appSecret" value="{{ socialConfig.facebook.appSecret }}" placeholder="{{ Lang.seo.facebook.appSecret }}">
                <input type="text" name="facebook_accessToken" value="{{ socialConfig.facebook.accessToken }}" placeholder="{{ Lang.seo.facebook.accessToken }}">
            </div>
        </div>
        <div class="seo-network">
            <label>
                <input type="checkbox" name="x_enabled" {% if socialConfig.x.enabled %}checked{% endif %}>
                {{ Lang.seo.x.enabled }}
            </label>
            <div class="seo-network-fields">
                <input type="text" name="x_bearerToken" value="{{ socialConfig.x.bearerToken }}" placeholder="{{ Lang.seo.x.bearerToken }}">
            </div>
        </div>
        <div class="seo-network">
            <label>
                <input type="checkbox" name="linkedin_enabled" {% if socialConfig.linkedin.enabled %}checked{% endif %}>
                {{ Lang.seo.linkedin.enabled }}
            </label>
            <div class="seo-network-fields">
                <input type="text" name="linkedin_clientId" value="{{ socialConfig.linkedin.clientId }}" placeholder="{{ Lang.seo.linkedin.clientId }}">
                <input type="text" name="linkedin_clientSecret" value="{{ socialConfig.linkedin.clientSecret }}" placeholder="{{ Lang.seo.linkedin.clientSecret }}">
                <input type="text" name="linkedin_accessToken" value="{{ socialConfig.linkedin.accessToken }}" placeholder="{{ Lang.seo.linkedin.accessToken }}">
            </div>
        </div>
        <p>
            <label for="languages">{{ Lang.seo.languages }}</label><br>
            <input type="text" name="languages" id="languages" value="{{ socialConfig.languages|join(', ') }}" placeholder="fr,en,es">
            <small>{{ Lang.seo.languages.help }}</small>
        </p>
    </section>

    <section class="seo-actions">
        <header>{{ Lang.seo.sitemap.title }}</header>
        <p>{{ Lang.seo.sitemap.help }}</p>
        <div class="seo-actions-buttons">
            <button type="submit" class="button success">{{ Lang.submit }}</button>
            <button type="submit" formaction="{{ ROUTER.generate("seo-admin-generate-sitemap") }}" class="button">{{ Lang.seo.sitemap.generate }}</button>
        </div>
    </section>
</form>

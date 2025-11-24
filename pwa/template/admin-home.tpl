<section class="content">
    <header>
        <h2>{{ lang_pwa_name }}</h2>
    </header>
    
    <div class="card">
        <h3>Clés VAPID</h3>
        {% IF hasKeys %}
            <p><strong>{{ lang_pwa_public_key }}:</strong></p>
            <pre style="word-break: break-all; padding: 1rem; background: #f5f5f5; border-radius: 4px;">{{ publicKey }}</pre>
            <form method="post" style="margin-top: 1rem;">
                <input type="hidden" name="token" value="{{ token }}">
                <button type="submit" name="regenerate_keys" class="button">{{ lang_pwa_regenerate_keys }}</button>
            </form>
        {% ELSE %}
            <p class="msg warning">{{ keysNotGeneratedText }}</p>
            <form method="post" action="" style="margin-top: 1rem;">
                <input type="hidden" name="token" value="{{ token }}">
                <button type="submit" name="generate_keys" class="button">{{ generateKeysText }}</button>
            </form>
        {% ENDIF %}
    </div>
    
    <div class="card" style="margin-top: 1.5rem;">
        <h3>Abonnements</h3>
        <p><strong>Nombre d'abonnements:</strong> {{ subscriptionCount }}</p>
    </div>
    
    <div class="card" style="margin-top: 1.5rem;">
        <h3>{{ lang_pwa_send_notification }}</h3>
        <form method="post" action="{{ sendNotificationUrl }}">
            <input type="hidden" name="token" value="{{ token }}">
            <div style="margin-bottom: 1rem;">
                <label for="notification-title"><strong>{{ lang_pwa_notification_title }}:</strong></label>
                <input type="text" id="notification-title" name="title" required style="width: 100%; padding: 0.5rem; margin-top: 0.5rem;">
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="notification-message"><strong>{{ lang_pwa_notification_message }}:</strong></label>
                <textarea id="notification-message" name="message" required style="width: 100%; padding: 0.5rem; margin-top: 0.5rem; min-height: 100px;"></textarea>
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="notification-url"><strong>{{ lang_pwa_notification_url }}:</strong></label>
                <input type="url" id="notification-url" name="url" value="/" style="width: 100%; padding: 0.5rem; margin-top: 0.5rem;">
            </div>
            <button type="submit" class="button">{{ lang_pwa_send }}</button>
        </form>
    </div>
</section>




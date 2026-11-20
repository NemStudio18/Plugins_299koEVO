<section class="module-card">
    <header>{% if don.id %}Modifier le don #{{ don.id }}{% else %}Ajouter un don manuel{% endif %}</header>
    <p><a href="{{ backUrl }}" class="button secondary small">&larr; Retour à la liste des dons</a></p>
    <form method="post" action="{{ saveUrl }}" class="admin-form">
    {{SHOW.tokenField}}
        <input type="hidden" name="token" value="{{ token }}" />
        {% if don.id %}<input type="hidden" name="id" value="{{ don.id }}" />{% endif %}
        <div class="form-group">
            <label for="amount">{{ Lang.dons.amount }}</label>
            <input type="number" step="0.01" min="0.01" name="amount" id="amount" value="{{ don.amount }}" required />
        </div>
        <div class="form-group">
            <label for="firstName">{{ Lang.dons.firstName }}</label>
            <input type="text" name="firstName" id="firstName" value="{{ don.firstName }}" />
        </div>
        <div class="form-group">
            <label for="lastName">{{ Lang.dons.lastName }}</label>
            <input type="text" name="lastName" id="lastName" value="{{ don.lastName }}" />
        </div>
        <div class="form-group">
            <label for="email">{{ Lang.dons.email }}</label>
            <input type="email" name="email" id="email" value="{{ don.email }}" />
        </div>
        <div class="form-group">
            <label for="gateway">{{ Lang.dons-admin.gateway }}</label>
            <select name="gateway" id="gateway">
                <option value="manual" {% if don.gateway == 'manual' %}selected{% endif %}>Manuel / Espèces / Chèque</option>
                <option value="paypal" {% if don.gateway == 'paypal' %}selected{% endif %}>PayPal</option>
                <option value="stripe" {% if don.gateway == 'stripe' %}selected{% endif %}>Stripe</option>
            </select>
        </div>
        <div class="form-group">
            <label for="status">{{ Lang.dons-admin.status }}</label>
            <select name="status" id="status">
                <option value="completed" {% if don.status == 'completed' %}selected{% endif %}>{{ Lang.dons-admin.completed }}</option>
                <option value="pending" {% if don.status == 'pending' %}selected{% endif %}>{{ Lang.dons-admin.pending }}</option>
                <option value="failed" {% if don.status == 'failed' %}selected{% endif %}>{{ Lang.dons-admin.failed }}</option>
            </select>
        </div>
        <div class="form-group">
            <label for="transactionId">Référence transaction</label>
            <input type="text" name="transactionId" id="transactionId" value="{{ don.transactionId }}" />
        </div>
        <div class="form-group">
            <label for="message">{{ Lang.dons.message }}</label>
            <textarea name="message" id="message" rows="3">{{ don.message }}</textarea>
        </div>
        <div class="form-group">
            <label class="checkbox-container">
                <input type="checkbox" name="anonymous" value="1" {% if don.anonymous == '1' %}checked{% endif %} />
                {{ Lang.dons.anonymous }}
            </label>
        </div>
        <p><button type="submit" class="button success">Enregistrer</button></p>
    </form>
</section>


<section class="module-card">
    <header>
        <h1><i class="fa-solid fa-credit-card"></i> {{Lang.ecommerce.checkout}}</h1>
    </header>

    <form id="checkout-form" class="checkout-form">
        <div class="checkout-grid">
            <div class="checkout-details">
                <h3><i class="fa-solid fa-address-card"></i> Informations de livraison & Contact</h3>
                
                <div class="form-group">
                    <label for="customer_name">Nom complet <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="customer_name" name="customer_name" value="{% if user %}{{user.username()}}{% endif %}" required placeholder="Ex: Jean Dupont">
                </div>

                <div class="form-group">
                    <label for="customer_email">Adresse email <span style="color: #ef4444;">*</span></label>
                    <input type="email" id="customer_email" name="customer_email" value="{% if user %}{{user.email}}{% endif %}" required placeholder="Ex: jean.dupont@exemple.fr">
                </div>

                <div class="form-group">
                    <label for="customer_address">Adresse complète de livraison <span style="color: #ef4444;">*</span></label>
                    <textarea id="customer_address" name="customer_address" rows="3" required placeholder="Numéro, rue, code postal et ville..."></textarea>
                </div>

                <div class="form-group">
                    <label for="customer_phone">Numéro de téléphone</label>
                    <input type="tel" id="customer_phone" name="customer_phone" placeholder="Ex: 06 12 34 56 78">
                </div>
                
                <div class="form-group">
                    <label for="payment_method">Moyen de règlement <span style="color: #ef4444;">*</span></label>
                    <select id="payment_method" name="payment_method">
                        {% if hasStripe %}
                            <option value="stripe">💳 Carte bancaire (Paiement sécurisé Stripe)</option>
                        {% endif %}
                        <option value="bank_transfer">🏦 Virement bancaire</option>
                        <option value="check">✉️ Chèque</option>
                        <option value="cash_on_delivery">📦 Paiement à la livraison / Retrait</option>
                    </select>
                </div>
            </div>

            <div class="checkout-summary">
                <div class="cart-summary">
                    <h3><i class="fa-solid fa-basket-shopping"></i> Résumé de la commande</h3>
                    {% FOR item IN items %}
                    <div class="cart-summary-row" style="font-size: 0.9em; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 0.5rem; margin-bottom: 0.5rem;">
                        <span>{{item.quantity}}x {{item.product.getName()}}</span>
                        <strong>{{item.subtotal}}€</strong>
                    </div>
                    {% ENDFOR %}
                    
                    <div class="cart-summary-row" style="margin-top: 1rem;">
                        <span>{{Lang.ecommerce.subtotal}}</span>
                        <strong>{{subtotal}}€</strong>
                    </div>
                    <div class="cart-summary-row">
                        <span>{{Lang.ecommerce.shipping}}</span>
                        <strong>{% IF shipping == 0 %}<span style="color: #22c55e;">{{Lang.ecommerce.free-shipping}}</span>{% ELSE %}{{shipping}}€{% ENDIF %}</strong>
                    </div>
                    <div class="cart-summary-row cart-total" style="font-size: 1.25rem; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 2px solid var(--evo-border, #cbd5e1);">
                        <span>{{Lang.ecommerce.total}} TTC</span>
                        <strong style="color: var(--evo-primary, #0284c7);">{{total}}€</strong>
                    </div>

                    {{token}}
                    
                    <button type="submit" class="button success full-width" style="margin-top: 1.25rem; padding: 12px; font-weight: 700;">
                        <i class="fa-solid fa-lock"></i> {{Lang.ecommerce.confirm-order}}
                    </button>
                    <div id="checkout-error" class="alert alert-danger" style="display:none; margin-top: 1rem;"></div>
                </div>
            </div>
        </div>
    </form>
</section>

<script>
document.getElementById('checkout-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const errorDiv = document.getElementById('checkout-error');
    const submitBtn = this.querySelector('button[type="submit"]');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Traitement de votre commande...';
    errorDiv.style.display = 'none';

    fetch('/checkout/process', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            window.location.href = data.redirect || '/shop';
        } else {
            errorDiv.textContent = data.message || 'Une erreur est survenue lors de la commande.';
            errorDiv.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-lock"></i> {{Lang.ecommerce.confirm-order}}';
        }
    })
    .catch(error => {
        errorDiv.textContent = 'Erreur de connexion avec le serveur.';
        errorDiv.style.display = 'block';
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-lock"></i> {{Lang.ecommerce.confirm-order}}';
    });
});
</script>

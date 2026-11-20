<section class="overflow-auto">
    <header><i class="fa-solid fa-receipt"></i> Commande {{ order.getOrderNumber() }}</header>

    <div style="margin-bottom: 16px;">
        <a href="{{ ROUTER.generate('admin-ecommerce-orders') }}" class="button secondary">
            <i class="fa-solid fa-arrow-left"></i> Retour aux commandes
        </a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 24px;">
        <div style="background: var(--evo-card-bg,#fff); border: 1px solid var(--adm-border, #cbd5e1); border-radius: 8px; padding: 16px;">
            <h4><i class="fa-solid fa-user"></i> Informations client</h4>
            <p><strong>Nom :</strong> {{ order.getCustomerName() }}</p>
            <p><strong>Email :</strong> <a href="mailto:{{ order.getCustomerEmail() }}">{{ order.getCustomerEmail() }}</a></p>
            <p><strong>Téléphone :</strong> {% if order.getCustomerPhone() %}<a href="tel:{{ order.getCustomerPhone() }}">{{ order.getCustomerPhone() }}</a>{% else %}Non renseigné{% endif %}</p>
            <p><strong>Adresse de livraison :</strong><br>{{ order.getCustomerAddress() }}</p>
        </div>

        <div style="background: var(--evo-card-bg,#fff); border: 1px solid var(--adm-border, #cbd5e1); border-radius: 8px; padding: 16px;">
            <h4><i class="fa-solid fa-sliders"></i> Gestion du statut &amp; Expédition</h4>
            <form id="order-status-form" style="margin-top: 10px;">
                <input type="hidden" name="token" value="{{ token }}">
                
                <p>
                    <label for="status_select"><strong>Statut de la commande :</strong></label><br>
                    <select id="status_select" name="status" style="width: 100%; margin-top: 4px;">
                        <option value="pending_payment" {% if order.getStatus() == 'pending_payment' or order.getStatus() == 'pending' %}selected{% endif %}>⏳ En attente de paiement</option>
                        <option value="paid" {% if order.getStatus() == 'paid' %}selected{% endif %}>✅ Payée (Paiement validé)</option>
                        <option value="processing" {% if order.getStatus() == 'processing' %}selected{% endif %}>📦 En cours de préparation</option>
                        <option value="shipped" {% if order.getStatus() == 'shipped' %}selected{% endif %}>🚚 Expédiée (Colis envoyé)</option>
                        <option value="delivered" {% if order.getStatus() == 'delivered' %}selected{% endif %}>📫 Livrée</option>
                        <option value="completed" {% if order.getStatus() == 'completed' %}selected{% endif %}>🎉 Terminée</option>
                        <option value="cancelled" {% if order.getStatus() == 'cancelled' %}selected{% endif %}>❌ Annulée</option>
                    </select>
                </p>

                <p id="tracking_row" style="margin-top: 10px;">
                    <label for="tracking_number"><strong>Numéro de suivi colis (optionnel) :</strong></label><br>
                    <input type="text" id="tracking_number" name="tracking_number" value="{{ order.getTrackingNumber() }}" placeholder="Ex: 6A1234567890 (Colissimo)">
                </p>

                <p style="margin-top: 12px;">
                    <button type="submit" class="button primary small"><i class="fa-solid fa-save"></i> Mettre à jour et notifier le client</button>
                </p>
                <div id="status-feedback" style="display: none; margin-top: 8px; font-weight: 600;"></div>
            </form>
            
            <hr style="margin: 12px 0; border: none; border-top: 1px solid var(--adm-border, #e2e8f0);">
            <p><strong>Moyen de règlement :</strong> {{ order.getPaymentMethodLabel() }}</p>
            <p><strong>Montant Total TTC :</strong> <strong style="font-size: 1.2rem; color: var(--evo-primary, #0284c7);">{{ order.getTotal() }} €</strong></p>
        </div>
    </div>

    <h3><i class="fa-solid fa-boxes-stacked"></i> Articles commandés</h3>
    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th>Prix unitaire</th>
                <th>Quantité</th>
                <th>Sous-total</th>
            </tr>
        </thead>
        <tbody>
            {% FOR item IN order.getItems() %}
            <tr>
                <td><strong>{{ item.getProductName() }}</strong></td>
                <td>{{ item.getPrice() }} €</td>
                <td>{{ item.getQuantity() }}</td>
                <td><strong>{{ item.getPrice() * item.getQuantity() }} €</strong></td>
            </tr>
            {% ENDFOR %}
        </tbody>
    </table>
</section>

<script>
document.getElementById('order-status-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    const feedback = document.getElementById('status-feedback');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mise à jour...';
    feedback.style.display = 'none';

    const formData = new FormData(this);

    fetch('/admin/ecommerce/orders/update-status/{{ order.getId() }}', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-save"></i> Mettre à jour et notifier le client';
        feedback.style.display = 'block';
        if (data.status === 'success') {
            feedback.style.color = '#22c55e';
            feedback.textContent = 'Statut mis à jour et email envoyé au client avec succès !';
        } else {
            feedback.style.color = '#ef4444';
            feedback.textContent = data.body && data.body.message ? data.body.message : 'Erreur lors de la mise à jour.';
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-save"></i> Mettre à jour et notifier le client';
        feedback.style.display = 'block';
        feedback.style.color = '#ef4444';
        feedback.textContent = 'Erreur réseau.';
    });
});
</script>

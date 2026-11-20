<section class="module-card">
    <header style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <h1><i class="fa-solid fa-box-archive"></i> Mes commandes</h1>
        <a href="{{shopUrl}}" class="button"><i class="fa-solid fa-store"></i> Retour à la boutique</a>
    </header>

    {% if orders %}
        <div class="my-orders-list" style="margin-top: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
            {% for order in orders %}
                <div class="my-order-card" style="background: var(--evo-card-bg, #ffffff); border: 1px solid var(--evo-border, #e2e8f0); border-radius: 8px; padding: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.75rem; border-bottom: 1px solid var(--evo-border, #f1f5f9); padding-bottom: 0.5rem;">
                        <div>
                            <strong style="font-size: 1.1rem; color: var(--evo-primary, #0284c7);">Commande #{{order.getOrderNumber()}}</strong>
                            <span style="font-size: 0.85rem; color: var(--evo-text-muted, #64748b); margin-left: 0.5rem;">du {{order.attributes.created_at}}</span>
                        </div>
                        <div>
                            <span class="order-badge order-badge-{{order.getStatus()}}" style="padding: 4px 10px; border-radius: 999px; font-size: 0.8rem; font-weight: 700;">
                                {{order.getStatusLabel()}}
                            </span>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; font-size: 0.9rem; margin-bottom: 0.75rem;">
                        <div>
                            <span style="color: var(--evo-text-muted, #64748b); display: block;">Moyen de paiement :</span>
                            <strong>{{order.getPaymentMethodLabel()}}</strong>
                        </div>
                        <div>
                            <span style="color: var(--evo-text-muted, #64748b); display: block;">Montant total TTC :</span>
                            <strong style="font-size: 1.1rem; color: var(--evo-primary, #0284c7);">{{order.getTotal()}} {{currency}}</strong>
                        </div>
                        <div>
                            <span style="color: var(--evo-text-muted, #64748b); display: block;">Adresse de livraison :</span>
                            <span>{{order.getCustomerAddress()}}</span>
                        </div>
                        {% if order.getTrackingNumber() %}
                            <div>
                                <span style="color: var(--evo-text-muted, #64748b); display: block;">Suivi de colis :</span>
                                <strong style="color: #22c55e;"><i class="fa-solid fa-truck-fast"></i> {{order.getTrackingNumber()}}</strong>
                            </div>
                        {% endif %}
                    </div>

                    <div style="margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px dashed var(--evo-border, #e2e8f0);">
                        <strong style="font-size: 0.85rem; color: var(--evo-text-muted, #64748b);">Articles commandés :</strong>
                        <ul style="margin: 0.35rem 0 0; padding-left: 1.25rem; font-size: 0.88rem;">
                            {% for it in order.getItems() %}
                                <li>{{it.getQuantity()}}x {{it.getProductName()}} — {{it.getPrice()}} {{currency}}</li>
                            {% endfor %}
                        </ul>
                    </div>
                </div>
            {% endfor %}
        </div>
    {% else %}
        <div style="text-align: center; padding: 3rem 1rem; color: var(--evo-text-muted, #64748b);">
            <i class="fa-solid fa-box-open fa-3x" style="margin-bottom: 1rem; display: block; opacity: 0.5;"></i>
            <p>Vous n'avez pas encore passé de commande sur notre boutique.</p>
            <a href="{{shopUrl}}" class="button primary" style="margin-top: 1rem;"><i class="fa-solid fa-store"></i> Découvrir nos produits</a>
        </div>
    {% endif %}
</section>

<style>
.order-badge-pending_payment,
.order-badge-pending     { background: #fef3c7; color: #92400e; }
.order-badge-paid        { background: #dcfce7; color: #166534; }
.order-badge-processing  { background: #e0f2fe; color: #075985; }
.order-badge-shipped     { background: #ede9fe; color: #5b21b6; }
.order-badge-delivered,
.order-badge-completed   { background: #dcfce7; color: #166534; }
.order-badge-cancelled   { background: #fee2e2; color: #991b1b; }

:root[data-theme="dark"] .my-order-card { background: #1e293b !important; border-color: #334155 !important; }
:root[data-theme="dark"] .order-badge-pending_payment,
:root[data-theme="dark"] .order-badge-pending     { background: rgba(245, 158, 11, 0.2); color: #fde68a; }
:root[data-theme="dark"] .order-badge-paid        { background: rgba(34, 197, 94, 0.2); color: #86efac; }
:root[data-theme="dark"] .order-badge-processing  { background: rgba(2, 132, 199, 0.2); color: #bae6fd; }
:root[data-theme="dark"] .order-badge-shipped     { background: rgba(139, 92, 246, 0.2); color: #ddd6fe; }
:root[data-theme="dark"] .order-badge-completed   { background: rgba(34, 197, 94, 0.2); color: #86efac; }
:root[data-theme="dark"] .order-badge-cancelled   { background: rgba(239, 68, 68, 0.2); color: #fca5a5; }
</style>

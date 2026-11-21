<section class="overflow-auto">
    <header><i class="fa-solid fa-receipt"></i> {{ Lang.ecommerce.orders }}</header>

    <div style="margin-bottom: 16px;">
        <a href="{{ ROUTER.generate('admin-ecommerce-home') }}" class="button secondary">
            <i class="fa-solid fa-arrow-left"></i> Tableau de bord
        </a>
    </div>

    <table>
        <thead>
            <tr>
                <th>N° Commande</th>
                <th>Client</th>
                <th>Total TTC</th>
                <th>Moyen de paiement</th>
                <th>Statut</th>
                <th>Date</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            {% FOR order IN orders %}
            <tr>
                <td>
                    <a href="{{ ROUTER.generate('admin-ecommerce-orders-view', {'id': order.getId()}) }}">
                        <strong>{{ order.getOrderNumber() }}</strong>
                    </a>
                </td>
                <td>
                    <div><strong>{{ order.getCustomerName() }}</strong></div>
                    <small style="color: var(--adm-text-muted, #64748b);">{{ order.getCustomerEmail() }}</small>
                </td>
                <td><strong style="color: var(--evo-accent, #2563eb);">{{ order.getTotal() }} €</strong></td>
                <td><span style="font-size: 0.85rem;">{{ order.getPaymentMethodLabel() }}</span></td>
                <td>
                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 4px; font-size: 0.82rem; font-weight: 600; background: rgba(37, 99, 235, 0.12); color: #2563eb;">
                        {{ order.getStatusLabel() }}
                    </span>
                    {% IF order.getTrackingNumber() %}
                    <div style="font-size: 0.78rem; color: var(--adm-text-muted, #64748b); margin-top: 2px;">
                        <i class="fa-solid fa-truck-fast"></i> {{ order.getTrackingNumber() }}
                    </div>
                    {% ENDIF %}
                </td>
                <td style="font-size: 0.85rem; color: var(--adm-text-muted, #64748b);">{{ order.getCreatedAt() }}</td>
                <td style="text-align: right;">
                    <a href="{{ ROUTER.generate('admin-ecommerce-orders-view', {'id': order.getId()}) }}" class="button small">
                        <i class="fa-solid fa-eye"></i> Gérer
                    </a>
                </td>
            </tr>
            {% ENDFOR %}
            {% IF not orders %}
            <tr>
                <td colspan="7" style="text-align: center; padding: 24px; color: var(--adm-text-muted, #64748b);">
                    <i class="fa-solid fa-receipt fa-2x" style="opacity: 0.3; display: block; margin-bottom: 8px;"></i>
                    Aucune commande enregistrée pour le moment.
                </td>
            </tr>
            {% ENDIF %}
        </tbody>
    </table>
</section>


<section class="overflow-auto">
    <header><i class="fa-solid fa-box-open"></i> {{ Lang.ecommerce.products }}</header>

    <div style="margin-bottom: 16px;">
        <a href="{{ ROUTER.generate('admin-ecommerce-products-add') }}" class="button">
            <i class="fa-solid fa-plus"></i> Ajouter un produit
        </a>
        <a href="{{ ROUTER.generate('admin-ecommerce-home') }}" class="button secondary">
            <i class="fa-solid fa-arrow-left"></i> Tableau de bord
        </a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>{{ Lang.ecommerce.price }}</th>
                <th>{{ Lang.ecommerce.stock }}</th>
                <th>Statut</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            {% FOR product IN products %}
            <tr>
                <td>{{ product.getId() }}</td>
                <td><strong>{{ product.getName() }}</strong></td>
                <td>{{ product.getPrice() }} €</td>
                <td>{{ product.getStock() }}</td>
                <td>
                    {% IF product.isActive() %}
                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 4px; font-size: 0.82rem; background: rgba(34, 197, 94, 0.15); color: #16a34a; font-weight: 600;">
                        <i class="fa-solid fa-check"></i> Actif
                    </span>
                    {% ELSE %}
                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 4px; font-size: 0.82rem; background: rgba(239, 68, 68, 0.15); color: #dc2626; font-weight: 600;">
                        <i class="fa-solid fa-eye-slash"></i> Inactif
                    </span>
                    {% ENDIF %}
                </td>
                <td style="text-align: right;">
                    <a href="{{ ROUTER.generate('admin-ecommerce-products-edit', {'id': product.getId()}) }}" class="button small">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                    <a href="{{ ROUTER.generate('admin-ecommerce-products-delete', {'id': product.getId()}) }}?token={{ token }}" class="button small alert" onclick="return confirm('Supprimer ce produit ?')">
                        <i class="fa-regular fa-trash-can"></i>
                    </a>
                </td>
            </tr>
            {% ENDFOR %}
        </tbody>
    </table>
</section>

<div class="ecommerce-shop">
    {% FOR product IN products %}
    <div class="ecommerce-product-card">
        {% IF product.getImage() %}
        <a href="{{ product.getUrl() }}">
            <img src="{{ product.getImage() }}" alt="{{ product.getName() }}" class="ecommerce-product-image">
        </a>
        {% ENDIF %}
        <h3><a href="{{ product.getUrl() }}">{{ product.getName() }}</a></h3>
        <p class="ecommerce-product-price">{{ product.getPrice() }}€</p>
        {% IF product.isInStock() %}
        <p class="ecommerce-stock-badge in-stock">{{ Lang.ecommerce.in-stock }}</p>
        <a href="{{ product.getUrl() }}" class="button primary">{{ Lang.ecommerce.add-to-cart }}</a>
        {% ELSE %}
        <p class="ecommerce-stock-badge out-of-stock">{{ Lang.ecommerce.out-of-stock }}</p>
        <a href="{{ product.getUrl() }}" class="button secondary">Voir le produit</a>
        {% ENDIF %}
    </div>
    {% ENDFOR %}
</div>

{% IF not products %}
<p style="text-align: center; padding: 40px 0; color: var(--evo-text-muted, #64748b);">
    <i class="fa-solid fa-box-open fa-3x" style="opacity: 0.3; display: block; margin-bottom: 12px;"></i>
    Aucun produit disponible dans la boutique pour le moment.
</p>
{% ENDIF %}


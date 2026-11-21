<div class="ecommerce-breadcrumb" style="margin-bottom: 20px;">
    <a href="{{ ROUTER.generate('ecommerce-shop') }}" class="button secondary">
        <i class="fa-solid fa-arrow-left"></i> {{ Lang.ecommerce.shop }}
    </a>
</div>

{% IF not product.isActive() %}
<div class="alert alert-warning" style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; background: rgba(245, 158, 11, 0.15); border: 1px solid #f59e0b; color: #f59e0b;">
    <i class="fa-solid fa-triangle-exclamation"></i> <strong>Produit inactif :</strong> Ce produit est actuellement masqué pour les visiteurs publics (mode prévisualisation administrateur).
</div>
{% ENDIF %}

<div class="ecommerce-product-detail">
    <div class="ecommerce-product-detail-media">
        {% IF product.getImage() %}
        <img src="{{ product.getImage() }}" alt="{{ product.getName() }}" class="ecommerce-product-detail-img">
        {% ELSE %}
        <div class="ecommerce-product-detail-placeholder">
            <i class="fa-solid fa-box-open fa-4x" style="opacity: 0.3;"></i>
        </div>
        {% ENDIF %}
    </div>

    <div class="ecommerce-product-detail-info">
        <h1 class="ecommerce-product-detail-title">{{ product.getName() }}</h1>

        <div class="ecommerce-product-detail-price">{{ product.getPrice() }} €</div>

        <div class="ecommerce-product-detail-stock">
            {% IF product.isInStock() %}
            <span class="ecommerce-stock-badge in-stock">
                <i class="fa-solid fa-check"></i> {{ Lang.ecommerce.in-stock }} ({{ product.getStock() }} en stock)
            </span>
            {% ELSE %}
            <span class="ecommerce-stock-badge out-of-stock">
                <i class="fa-solid fa-xmark"></i> {{ Lang.ecommerce.out-of-stock }}
            </span>
            {% ENDIF %}
        </div>

        {% IF product.getDescription() %}
        <div class="ecommerce-product-detail-desc">
            {{ product.getDescription() }}
        </div>
        {% ENDIF %}

        {% IF product.isInStock() %}
        <form method="post" action="{{ ROUTER.generate('ecommerce-cart-add') }}" class="ecommerce-add-cart-form">
            {{ token }}
            <input type="hidden" name="product_id" value="{{ product.getId() }}" />
            <div class="ecommerce-add-cart-controls">
                <div class="ecommerce-qty-wrapper">
                    <label for="prod_qty">Quantité</label>
                    <input type="number" id="prod_qty" name="quantity" value="1" min="1" max="{{ product.getStock() }}" class="ecommerce-qty-input" />
                </div>
                <button type="submit" class="button primary ecommerce-add-cart-btn">
                    <i class="fa-solid fa-cart-plus"></i> {{ Lang.ecommerce.add-to-cart }}
                </button>
                <a href="{{ ROUTER.generate('ecommerce-cart') }}" class="button secondary ecommerce-view-cart-btn">
                    <i class="fa-solid fa-basket-shopping"></i> {{ Lang.ecommerce.cart }}
                </a>
            </div>
        </form>
        {% ELSE %}
        <div class="ecommerce-out-notice">
            <p>Ce produit est actuellement en rupture de stock.</p>
        </div>
        {% ENDIF %}
    </div>
</div>

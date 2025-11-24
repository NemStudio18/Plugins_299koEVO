<section>
    <header>{{ Lang.dons.name }}</header>
    
    {{ SHOW.displayMsg }}
    
    {% if description %}
        <p>{{ description }}</p>
    {% endif %}
    
    {% if targetAmount > 0 %}
        <div class="dons-progress">
            <div class="dons-progress-bar-container">
                <div class="dons-progress-bar" style="width: {{ progress }}%"></div>
            </div>
            <div class="dons-progress-info">
                <strong>{{ totalAmountFormatted }} €</strong> / <strong>{{ targetAmountFormatted }} €</strong>
                <span class="dons-progress-percent">({{ progressFormatted }}%)</span>
            </div>
        </div>
    {% endif %}
    
    <form id="donation-form" class="dons-form">
        <label for="amount">{{ Lang.dons.amount }}</label>
        <input type="number" name="amount" id="amount" min="1" step="0.01" required placeholder="{{ Lang.dons.amount-placeholder }}" />
        
        <label for="firstName">{{ Lang.dons.firstName }}</label>
        <input type="text" name="firstName" id="firstName" required />
        
        <label for="lastName">{{ Lang.dons.lastName }}</label>
        <input type="text" name="lastName" id="lastName" required />
        
        <label for="email">{{ Lang.dons.email }}</label>
        <input type="email" name="email" id="email" required />
        
        <label for="message">{{ Lang.dons.message }} ({{ Lang.dons.optional }})</label>
        <textarea name="message" id="message" rows="3"></textarea>
        
        <p>
            <input type="checkbox" name="anonymous" id="anonymous" />
            <label for="anonymous">{{ Lang.dons.anonymous }}</label>
        </p>
        
        <div class="dons-gateways">
            {% if hasPayPal %}
                <button type="button" class="button paypal-btn" onclick="donatePayPal()">
                    <i class="fa-brands fa-paypal"></i> {{ Lang.dons.pay-with-paypal }}
                </button>
            {% endif %}
            {% if hasStripe %}
                <button type="button" class="button stripe-btn" onclick="donateStripe()">
                    <i class="fa-brands fa-stripe"></i> {{ Lang.dons.pay-with-stripe }}
                </button>
            {% endif %}
        </div>
    </form>
</section>

<script>
{% if hasPayPal %}
function donatePayPal() {
    const form = document.getElementById('donation-form');
    const formData = new FormData(form);
    
    fetch('{{ paypalCreateUrl }}', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.approvalUrl) {
            window.location.href = data.approvalUrl;
        } else {
            alert(data.error || '{{ Lang.dons.error }}');
        }
    })
    .catch(error => {
        alert('{{ Lang.dons.error }}');
    });
}
{% endif %}

{% if hasStripe %}
function donateStripe() {
    const form = document.getElementById('donation-form');
    const formData = new FormData(form);
    
    fetch('{{ stripeCreateUrl }}', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.sessionId) {
            const stripe = Stripe('{{ stripePublishableKey }}');
            stripe.redirectToCheckout({ sessionId: data.sessionId });
        } else {
            alert(data.error || '{{ Lang.dons.error }}');
        }
    })
    .catch(error => {
        alert('{{ Lang.dons.error }}');
    });
}
{% endif %}
</script>
{% if hasStripe %}
<script src="https://js.stripe.com/v3/"></script>
{% endif %}


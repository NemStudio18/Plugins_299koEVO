<section>
    <header>{{ Lang.dons-admin.params-title }}</header>
    
    <form method="post" action="{{ ROUTER.generate("dons-admin-save-params") }}">
        {{ SHOW.tokenField }}
        
        <label for="pageTitle">{{ Lang.dons-admin.pageTitle }}</label>
        <input type="text" name="pageTitle" id="pageTitle" value="{{ runPlugin.getConfigVal("pageTitle") }}" />
        
        <label for="description">{{ Lang.dons-admin.description }}</label>
        <textarea name="description" id="description" rows="3">{{ runPlugin.getConfigVal("description") }}</textarea>
        
        <label for="targetAmount">{{ Lang.dons-admin.targetAmount }}</label>
        <input type="number" name="targetAmount" id="targetAmount" step="0.01" value="{{ runPlugin.getConfigVal("targetAmount") }}" />
        
        <h3>{{ Lang.dons-admin.paypal-config }}</h3>
        
        <label for="paypalClientId">{{ Lang.dons-admin.paypalClientId }}</label>
        <input type="text" name="paypalClientId" id="paypalClientId" value="{{ runPlugin.getConfigVal("paypalClientId") }}" />
        
        <label for="paypalSecret">{{ Lang.dons-admin.paypalSecret }}</label>
        <input type="password" name="paypalSecret" id="paypalSecret" value="{{ runPlugin.getConfigVal("paypalSecret") }}" />
        
        <label for="paypalMode">{{ Lang.dons-admin.paypalMode }}</label>
        <select name="paypalMode" id="paypalMode">
            <option value="sandbox"{% if runPlugin.getConfigVal("paypalMode") == "sandbox" %} selected{% endif %}>{{ Lang.dons-admin.sandbox }}</option>
            <option value="live"{% if runPlugin.getConfigVal("paypalMode") == "live" %} selected{% endif %}>{{ Lang.dons-admin.live }}</option>
        </select>
        
        <h3>{{ Lang.dons-admin.stripe-config }}</h3>
        
        <label for="stripePublishableKey">{{ Lang.dons-admin.stripePublishableKey }}</label>
        <input type="text" name="stripePublishableKey" id="stripePublishableKey" value="{{ runPlugin.getConfigVal("stripePublishableKey") }}" />
        
        <label for="stripeSecretKey">{{ Lang.dons-admin.stripeSecretKey }}</label>
        <input type="password" name="stripeSecretKey" id="stripeSecretKey" value="{{ runPlugin.getConfigVal("stripeSecretKey") }}" />
        
        <label for="stripeMode">{{ Lang.dons-admin.stripeMode }}</label>
        <select name="stripeMode" id="stripeMode">
            <option value="test"{% if runPlugin.getConfigVal("stripeMode") == "test" %} selected{% endif %}>{{ Lang.dons-admin.test }}</option>
            <option value="live"{% if runPlugin.getConfigVal("stripeMode") == "live" %} selected{% endif %}>{{ Lang.dons-admin.live }}</option>
        </select>
        
        <button type="submit" class="button">{{ Lang.save }}</button>
    </form>
</section>




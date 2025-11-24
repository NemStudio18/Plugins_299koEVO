<section>
    <header>{{ Lang.newsletter.name }}</header>
    
    {{ SHOW.displayMsg }}
    
    <form method="post" action="{{ subscribeUrl }}">
        <label for="email">{{ Lang.newsletter.email }}</label>
        <input type="email" name="email" id="email" placeholder="{{ Lang.newsletter.email-placeholder }}" required />
        
        <button type="submit" class="button">{{ Lang.newsletter.subscribe }}</button>
    </form>
</section>


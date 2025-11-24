<section>
    <header>{{ Lang.newsletter.unsubscribe-title }}</header>
    
    {{ SHOW.displayMsg }}
    
    <p>{{ Lang.newsletter.email }}: <strong>{{ email }}</strong></p>
    
    <form method="post" action="{{ unsubscribeUrl }}">
        <input type="hidden" name="token" value="{{ token }}" />
        
        <button type="submit" class="button alert">{{ Lang.newsletter.unsubscribe }}</button>
    </form>
</section>

